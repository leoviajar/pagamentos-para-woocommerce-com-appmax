<?php

namespace AppMax\WooCommerce\Gateway\Core\Gateway;

use Exception;
use AppMax\WooCommerce\Gateway\Core\Helpers\OrderExtractor;
use AppMax\WooCommerce\Gateway\Core\Records\PaymentRecord;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\PaymentMethod\CreatePixPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\PaymentMethod\GetPixPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Interfaces\PaymentTypeInterface;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;

/**
 * Pix gateway manipulation.
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
class PixGateway extends BaseGateway
{
	/**
	 * Gateway slug.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	protected $_slug = 'pix';

	/**
	 * Startup payment gateway method.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function __construct()
	{
		$this->id                 = Connector::plugin()->getName().'_pix';
		$this->method_title       = Connector::__translate('Pix -- AppMax');
		$this->method_description = Connector::__translate('Habilite o pagamento via pix e gerencie os pagamentos através da AppMax.');
		$this->supports           = ['products', 'refunds'];
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

			$method = new CreatePixPayload($document_number);
			$method->expiresIn($this->settings()->get('expires_in', 3600));

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
		if (!($method instanceof GetPixPayload)) {
			return $payment;
		}

		$payment
			->setMedia($this->service()->getQRCode($method->getEmv(), $method->getQrCode()))
			->setPaymentCode($method->getEmv());

		if ($method->getExpirationDate()) {
			$payment->willExpiresAt($method->getExpirationDate());
		}

		return $payment;
	}
}
