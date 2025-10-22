<?php

namespace AppMax\WooCommerce\Gateway\Core\Gateway;

use Exception;
use AppMax\WooCommerce\Gateway\Core\Helpers\OrderExtractor;
use AppMax\WooCommerce\Gateway\Core\Models\TransactionModel;
use AppMax\WooCommerce\Gateway\Core\Records\Enums\PaymentStatusEnum;
use AppMax\WooCommerce\Gateway\Core\Records\PaymentRecord;
use AppMax\WooCommerce\Gateway\Core\Repositories\PaymentsRepo;
use AppMax\WooCommerce\Gateway\Core\Services\AppMaxService;
use AppMax\WooCommerce\Gateway\Plugin;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\Order\CreateRefundPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Interfaces\PaymentTypeInterface;
use AppMax\WooCommerce\Gateway\ApiTools;
use AppMax\WooCommerce\Gateway\Core\Records\Enums\PaymentMethodEnum;
use AppMax\WooCommerce\Gateway\Core\Tasks\OrderProcessingTask;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Exceptions\ApiResponseException;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Settings\KeyingBucket;
use WC_Order;
use WC_Payment_Gateway;
use WP_Error;

/**
 * Base gateway manipulation.
 *
 * @package \AppMax\WooCommerce\Gateway
 * @subpackage \AppMax\WooCommerce\Gateway\Core\Gateway
 * @version 0.1.0
 * @since 0.1.0
 * @category Gateway
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license piggly
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
abstract class BaseGateway extends WC_Payment_Gateway
{
	/**
	 * Gateway slug.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	protected $_slug = '';

	/**
	 * Do after process payment.
	 *
	 * @param WC_Order $order
	 * @param PaymentRecord $payment
	 * @since 0.1.0
	 * @since 1.1.0-beta Check if is paid right now.
	 * @return array
	 */
	protected function post_process_payment(WC_Order $order, PaymentRecord $payment): array
	{
		global $woocommerce;

		// Mark as pending, we're awaiting the payment
		$order->update_status(
			apply_filters(
				'pagamentos_para_woocommerce_com_appmax_pending_status',
				$this->globalSettings()->get('waiting_status', 'pending'),
				$order,
				$order->get_id()
			)
		);

		$meta = [
			'order_id' => $payment->appMaxOrderId(),
			'customer_id' => $payment->appMaxCustomerId(),
			'site_id' => $payment->appMaxSiteId(),
			'cnpj' => $payment->cnpj(),
			'pay_reference' => $payment->payReference(),
		];

		// Notes
		$order->add_order_note("Metadados da AppMax\n\nOrder ID: {$meta['order_id']}\nCustomer ID: {$meta['customer_id']}\nSite ID: {$meta['site_id']}\nCNPJ: {$meta['cnpj']}\nPay Reference: {$meta['pay_reference']}\n");
		$order->add_order_note('Pagamento via AppMax emitido com sucesso, veja as informações detalhadas na Metabox do Pedido.');
		$order->add_meta_data('_pagamentos_para_woocommerce_com_appmax_paymentid', $payment->id());

		// Decrease Stock
		if ($this->settings()->get('decrease_stock', false)) {
			\wc_maybe_reduce_stock_levels($order->get_id());
		}

		// Remove cart
		if ($woocommerce->cart) {
			$woocommerce->cart->empty_cart();
		}

		PaymentsRepo::save($payment);

		$order->add_meta_data('_pagamentos_para_woocommerce_com_appmax_media', $payment->media() ?? '');
		$order->add_meta_data('_pagamentos_para_woocommerce_com_appmax_payment_code', $payment->paymentCode() ?? '');
		$order->add_meta_data('_pagamentos_para_woocommerce_com_appmax_label', PaymentMethodEnum::label($payment->paymentMethod()));
		$order->save();

		try {
			// evaluate if is paid right now
			if ($this instanceof CreditCardGateway || $this instanceof PixGateway) {
				(new OrderProcessingTask($payment))->run();
			}
		} catch (Exception $e) {
			// do nothing
		}

		// Return checkout payment url
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url($order),
			'payment'   => $payment
		);
	}

	/**
	 * Process refund.
	 *
	 * @param int $order_id Order ID.
	 * @param float|null $amount Refund amount.
	 * @param string $reason Refund reason.
	 * @since 0.1.0
	 * @return boolean True or false based on success, or a WP_Error object.
	 */
	public function process_refund($order_id, $amount = null, $reason = '')
	{
		$order   = \wc_get_order($order_id);
		$payment = PaymentsRepo::byOrderId($order->get_id());

		if (empty($payment)) {
			return new WP_Error(1, 'Pagamento não localizado');
		}

		if (empty($amount)) {
			return new WP_Error(1, 'O valor do reembolso é obrigatório');
		}

		if ($amount !== $order->get_total()) {
			return new WP_Error(1, 'O valor do reembolso deve ser igual ao valor total do pedido');
		}

		if (PaymentStatusEnum::canUpdateTo($payment->status(), PaymentStatusEnum::STATUS_REFUNDED)) {
			return new WP_Error(1, 'O pedido não pode ser reembolado com o status atual.');
		}

		$payload = new CreateRefundPayload(
			$payment->appMaxOrderId(),
			CreateRefundPayload::REFUND_TYPE_TOTAL
		);

		$payload->setAmount($amount);

		$refunded = ApiTools::getApi()->order()->refund($payload);

		if ($refunded) {
			$payment->markAsRefunded();
			PaymentsRepo::save($payment);
		}

		return $refunded;
	}

	/**
	 * Produce WooCommerce error.
	 *
	 * @param Exception $e
	 * @since 0.1.0
	 * @return void
	 */
	public function produceError(Exception $e)
	{
		Connector::debugger()->force()->error($e->getMessage());

		$message = 'Transação não autorizada pela operadora do cartão, confira seus dados ou tente um novo cartão';

		if ($e instanceof ApiResponseException) {
			if (isset($e->getResponse()->getBody()['text'])) {
				$message = \sprintf('Transação não autorizada pela operadora do cartão, confira seus dados ou tente um novo cartão', \esc_html($e->getResponse()->getBody()['text']));
			}
		}

		if (Connector::debugger()->isDebugging()) {
			$message .= \sprintf(
				'<br>===DEBUG===<br>%s',
				\esc_html($e->getMessage())
			);
		}

		\wc_add_notice(
			$message,
			'error'
		);
	}

	/**
	 * Form to payment fields.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function payment_fields()
	{
		\wc_get_template(
			'html-pagamentos-para-woocommerce-com-appmax-'.$this->_slug.'-instructions.php',
			[],
			WC()->template_path().\dirname(Connector::plugin()->getBasename()).'/',
			Connector::plugin()->getTemplatePath().'woocommerce/'
		);
	}

	/**
	 * Initialise settings for gateways.
	 * It ignores the WC_Settings_API behavior.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function init_settings()
	{
		$settings = $this->settings();

		$this->enabled = !ApiTools::hasCredentials() ? 'no' : ($settings->get('enabled', 'no') === 'no' || $settings->get('enabled', 'no') === false ? 'no' : 'yes');
		$this->title = $settings->get('title');
		$this->description = $settings->get('description');
		$this->icon = apply_filters('woocommerce_gateway_icon', Connector::plugin()->getUrl().'assets/images/'.$this->_slug.'-icon.png');
	}

	/**
	 * Initialise settings form fields.
	 * It ignores the WC_Settings_API behavior.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function init_form_fields()
	{
		return;
	}

	/**
	 * Output the gateway settings screen.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function admin_options()
	{
		require_once(Connector::plugin()->getTemplatePath().'/admin/redirection.php');
	}

	/**
	 * Processes and saves options.
	 * If there is an error thrown, will continue to save
	 * and validate fields, but will leave the erroring field out.
	 * It ignores the WC_Settings_API behavior.
	 *
	 * @since 0.1.0
	 * @return bool was anything saved?
	 */
	public function process_admin_options()
	{
		return false;
	}

	/**
	 * Update a single option.
	 *
	 * @since 0.1.0
	 * @param string $key Option key.
	 * @param mixed  $value Value to set.
	 * @return bool was anything saved?
	 */
	public function update_option($key, $value = '')
	{
		$settings = $this->settings();

		if ($key === 'enabled') {
			$value = \filter_var($value, \FILTER_VALIDATE_BOOL);
			$value = $value ? 'yes' : 'no';
		}

		$settings->set($key, $value);

		Connector::settingsManager()->save();
		return true;
	}

	/**
	 * Get option from DB.
	 * Gets an option from the settings API, using defaults if necessary to prevent undefined notices.
	 *
	 * @param  string $key Option key.
	 * @param  mixed  $empty_value Value when empty.
	 * @return string The value specified for the option or a default value for the option.
	 */
	public function get_option($key, $empty_value = null)
	{
		$settings = $this->settings();
		$value = $settings->get($key, $empty_value);

		if (\is_bool($value)) {
			$value = $value ? 'yes' : 'no';
		}

		return $value;
	}

	/**
	 * Return the name of the option in the WP DB.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public function get_option_key()
	{
		return \PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_ACTION;
	}

	/**
	 * Get settings.
	 *
	 * @since 0.1.0
	 * @return KeyingBucket
	 */
	public function settings(): KeyingBucket
	{
		return Connector::settings()->get($this->_slug, new KeyingBucket());
	}

	/**
	 * Get global settings.
	 *
	 * @since 0.1.0
	 * @return KeyingBucket
	 */
	public function globalSettings(): KeyingBucket
	{
		return Connector::settings()->get('gateway', new KeyingBucket());
	}

	/**
	 * Solve document from order.
	 *
	 * @param WC_Order $order Order instance.
	 * @since 0.1.0
	 * @return string
	 */
	public function solveDocument(WC_Order $order): string
	{
		$document = OrderExtractor::solveDocument($order);

		if (empty($document)) {
			throw new Exception(Connector::__translate('O CPF/CNPJ é obrigatório para emissão do pagamento.'));
		}

		return $document;
	}

	/**
	 * Get the AppMax service.
	 *
	 * @since 0.1.0
	 * @return AppMaxService
	 */
	public function service(): AppMaxService
	{
		return new AppMaxService();
	}

	/**
	 * Get gateway slug.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public function get_slug(): string
	{
		return $this->_slug;
	}

	/**
	 * Fill payment record with payment data.
	 *
	 * @param PaymentRecord $payment
	 * @param PaymentTypeInterface $method
	 * @since 0.1.0
	 * @return PaymentRecord
	 */
	abstract public function fillWith(PaymentRecord $payment, PaymentTypeInterface $method): PaymentRecord;
}
