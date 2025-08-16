<?php

namespace AppMax\WooCommerce\Gateway\Core\Gateway;

use Exception;
use AppMax\WooCommerce\Gateway\Core\Helpers\OrderExtractor;
use AppMax\WooCommerce\Gateway\Core\Records\PaymentRecord;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\PaymentMethod\CreateBoletoPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\PaymentMethod\GetBoletoPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Interfaces\PaymentTypeInterface;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;

/**
 * Boleto gateway manipulation.
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
class BoletoGateway extends BaseGateway
{
	/**
	 * Gateway slug.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	protected $_slug = 'boleto';

	/**
	 * Startup payment gateway method.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function __construct()
	{
		$this->id                 = Connector::plugin()->getName().'_boleto';
		$this->method_title       = Connector::__translate('Boleto Bancário / AppMax');
		$this->method_description = Connector::__translate('Habilite o pagamento via boleto bancário e gerencie os pagamentos através da AppMax.');
		$this->supports           = ['products'];
		$this->has_fields         = false;

		$this->init_settings();
	}

	/**
	 * Process Payment.
	 *
	 * @param int $order_id Order ID.
	 * @since 0.1.0
	 * @return array
	 */
	public function process_payment($order_id)
	{
		try {
			WC()->mailer();

			$order = \wc_get_order($order_id);
			$document_number = $this->solveDocument($order);

			$method = new CreateBoletoPayload($document_number);
			$method->expiresIn($this->settings()->get('due_date', 3));

			return $this->post_process_payment(
				$order,
				$this->service()->payWith($this, $method, $order)
			);
		} catch (Exception $e) {
			$this->produceError($e);
			\do_action('pagamentos_para_woocommerce_com_appmax_boleto_payment_error', $e, $order);
			$order->update_status('failed');

			return [
				'result'   => 'fail',
				'redirect' => '',
			];
		}
	}

	/**
	 * Fill payment record with payment data.
	 *
	 * @param PaymentRecord $payment
	 * @param PaymentTypeInterface $method
	 * @since 0.1.0
	 * @return PaymentRecord
	 */
	public function fillWith(PaymentRecord $payment, PaymentTypeInterface $method): PaymentRecord
	{
		if (!($method instanceof GetBoletoPayload)) {
			return $payment;
		}

		$payment
			->setMedia($method->getPdf())
			->setPaymentCode($method->getDigitableLine());

		if ($method->getDueDate()) {
			$payment->willExpiresAt($method->getDueDate());
		}

		return $payment;
	}
}
