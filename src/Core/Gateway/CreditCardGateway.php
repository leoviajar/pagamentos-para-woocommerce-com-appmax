<?php

namespace AppMax\WooCommerce\Gateway\Core\Gateway;

use Exception;
use AppMax\WooCommerce\Gateway\Core\Helpers\OrderExtractor;
use AppMax\WooCommerce\Gateway\Core\Records\PaymentRecord;
use AppMax\WooCommerce\Gateway\Core\Services\CreditCardService;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\PaymentMethod\CreateCreditCardPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Interfaces\PaymentTypeInterface;
use AppMax\WooCommerce\Gateway\Api\Validations\CardNumberValidation;
use AppMax\WooCommerce\Gateway\Api\Validations\CvvValidation;
use AppMax\WooCommerce\Gateway\Api\Validations\ExpirationDateValidation;
use AppMax\WooCommerce\Gateway\Api\Validations\ExpirationMonthValidation;
use AppMax\WooCommerce\Gateway\Api\Validations\ExpirationYearValidation;
use AppMax\WooCommerce\Gateway\Api\Validations\NotEmptyValidation;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Exceptions\ApiResponseException;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use WC_Order_Item_Fee;

/**
 * CreditCard gateway manipulation.
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
class CreditCardGateway extends BaseGateway
{
	/**
	 * Gateway slug.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	protected $_slug = 'credit_card';

	/**
	 * Startup payment gateway method.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function __construct()
	{
		$this->id                 = Connector::plugin()->getName().'_credit_card';
		$this->method_title       = Connector::__translate('Cartão de Crédito -- AppMax');
		$this->method_description = Connector::__translate('Habilite o pagamento via cartão de crédito e gerencie os pagamentos através da AppMax.');
		$this->supports           = ['products', 'refunds'];
		$this->has_fields         = false;

		$this->init_settings();
	}

	/**
	 * Process Payment.
	 *
	 * @param int $order_id Order ID.
	 * @since 0.1.0
	 * @since 1.1.0-beta Change installments model.
	 * @return array
	 */
	public function process_payment($order_id)
	{
		try {
			$card_fields = $this->get_card_fields();
			WC()->mailer();

			$settings  = $this->settings();

			$order = \wc_get_order($order_id);
			$document_number = $this->solveDocument($order);

			$method = new CreateCreditCardPayload(
				$card_fields['name'],
				$card_fields['number'],
				$card_fields['month'],
				$card_fields['year'],
				$card_fields['cvv'],
				$document_number
			);

			$method
				->setSoftDescriptor($settings->get('soft_descriptor'));

			$installment = CreditCardService::installmentsToOrder(
				$order,
				$card_fields['installments'],
				$settings->get('max_installments', 12)
			);

			if (empty($installment) === false) {
				$method->setInstallment($installment);

				// Apply interest to order
				$interest_item = new WC_Order_Item_Fee();

				$interest_item->set_name('Juros');
				$interest_item->set_amount($installment->getInterest($order->get_total()));
				$interest_item->set_total($installment->getInterest($order->get_total()));
				$interest_item->calculate_taxes([
					'country' => $order->get_shipping_country(),
					'state' => '',
					'postcode' => '',
					'city' => ''
				]);

				$order->add_item($interest_item);
				$order->calculate_totals();
			}

			return $this->post_process_payment(
				$order,
				$this->service()->payWith($this, $method, $order)
			);
		} catch (Exception $e) {
			$this->produceError($e);
			\do_action('pagamentos_para_woocommerce_com_appmax_pix_payment_error', $e, $order);
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
		return $payment;
	}

	/**
	 * Get card fields.
	 *
	 * @since 0.1.0
	 * @return array
	 */
	public function get_card_fields(): array
	{
		$card = [
			'number' => \esc_attr($_POST['pagamentos_para_woocommerce_com_appmax_credit_card_number']),
			'name' => \esc_attr($_POST['pagamentos_para_woocommerce_com_appmax_credit_card_name']),
			'cvv' => \esc_attr($_POST['pagamentos_para_woocommerce_com_appmax_credit_card_cvv']),
			'month' => \esc_attr($_POST['pagamentos_para_woocommerce_com_appmax_credit_card_month']),
			'year' => \esc_attr($_POST['pagamentos_para_woocommerce_com_appmax_credit_card_year']),
			'installments' => \esc_attr($_POST['pagamentos_para_woocommerce_com_appmax_credit_card_installments'] ?? 1),
		];

		$card['installments'] = \intval($card['installments']);
		$card['month'] = \intval($card['month']);
		$card['year'] = \intval($card['year']);

		return $card;
	}

	/**
	 * Validate fields.
	 *
	 * @since 0.1.0
	 * @return bool
	 */
	public function validate_fields(): bool
	{
		$card = $this->get_card_fields();

		$validations = [
			'number' => 'O Número do Cartão é obrigatório e deve ser preenchido',
			'name' => 'O Nome do Titular é obrigatório e deve ser preenchido',
			'cvv' => 'O Código de segurança é obrigatório e deve ser preenchido',
			'month' => 'O mês de expiração é obrigatório e deve ser preenchido',
			'year' => 'O ano de expiração é obrigatório e deve ser preenchido',
			'installments' => 'Selecione o número de parcelas',
		];

		foreach ($validations as $key => $message) {
			if (empty($card[$key])) {
				wc_add_notice($message, 'error');
				return false;
			}
		}

		try {
			ExpirationDateValidation::validate($card['month'], $card['year']);
			NotEmptyValidation::validate($card['name'], 'Nome');
			CardNumberValidation::validate($card['number']);
			ExpirationMonthValidation::validate($card['month']);
			ExpirationYearValidation::validate($card['year']);
			CvvValidation::validate($card['cvv']);
		} catch (Exception $e) {
			wc_add_notice($e->getMessage(), 'error');
			return false;
		}

		return true;
	}
}
