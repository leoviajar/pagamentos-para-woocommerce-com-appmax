<?php

namespace AppMax\WooCommerce\Gateway\Core\Services;

use Exception;
use AppMax\WooCommerce\Gateway\Core\Gateway\BaseGateway;
use AppMax\WooCommerce\Gateway\Core\Helpers\OrderExtractor;
use AppMax\WooCommerce\Gateway\Core\Records\PaymentRecord;
use AppMax\WooCommerce\Gateway\Core\Repositories\PaymentsRepo;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\CreateCustomerPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\CreateOrderPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\CreatePaymentPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\GetCustomerPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\GetOrderPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\GetPaymentPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\PaymentMethod\GetBoletoPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Interfaces\PaymentTypeInterface;
use AppMax\WooCommerce\Gateway\Api\Values\InstallmentValue;
use AppMax\WooCommerce\Gateway\ApiTools;
use AppMax\WooCommerce\Gateway\Vendor\chillerlan\QRCode\QRCode;
use AppMax\WooCommerce\Gateway\Vendor\chillerlan\QRCode\QROptions;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use WC_Order;

/**
 * Services for AppMax.
 *
 * @package \AppMax\WooCommerce\Gateway
 * @subpackage \AppMax\WooCommerce\Gateway\Core\Services
 * @version 0.1.0
 * @since 0.1.0
 * @category Service
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license piggly
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class AppMaxService
{
	/**
	 * Get installments from API.
	 *
	 * @param float $total
	 * @param int $max_installments
	 * @since 0.1.0
	 * @since 1.1.0-beta Return installments.
	 * @return InstallmentValue[]
	 */
	public function getInstallments(float $total, int $max_installments): array
	{
		try {
			$_installments = ApiTools::getApi()->payment()->installments($total, $max_installments);

			if (empty($_installments)) {
				throw new Exception('A API não retornou as parcelas, contate a equipe da loja e informe o código APPMAX_GATEWAY_INSTALLMENTS');
				return [];
			}

			return $_installments;
		} catch (Exception $e) {
			Connector::debugger()->force()->error(\sprintf('Não foi possível retornar as parcelas para o cliente: %s', $e->getMessage()));
			return [];
		}
	}

	/**
	 * Create a new boleto as payment.
	 *
	 * @param BaseGateway $gateway
	 * @param PaymentTypeInterface $payment_data
	 * @param WC_Order $wooOrder
	 * @return PaymentRecord
	 * @since 0.1.0
	 * @since 1.1.0-beta Change installments model.
	 */
	public function payWith(BaseGateway $gateway, PaymentTypeInterface $payment_data, WC_Order $wooOrder): PaymentRecord
	{
		$customer = $this->publishCustomer(OrderExtractor::customer($wooOrder));
		$order = $this->publishOrder(OrderExtractor::order($customer->getId(), $wooOrder, $payment_data));
		$payment = $this->publishPayment(new CreatePaymentPayload($order->getId(), $customer->getId(), $payment_data));

		/** @var GetBoletoPayload $method */
		$method = $payment->getMethod();

		$record =  PaymentRecord::create(
			$wooOrder,
			$payment_data->getPaymentMethod()
		);

		$record
			->applyAppMaxId(
				$order->getId(),
				$customer->getId(),
				$customer->getSiteId()
			)
			->setCnpj($order->getCompany()->getCnpj())
			->setPayReference($payment->getReference());

		PaymentsRepo::save($gateway->fillWith($record, $method));
		return $record;
	}

	/**
	 * Publish customer on API.
	 *
	 * @param CreateCustomerPayload $customer
	 * @since 0.1.0
	 * @return GetCustomerPayload
	 */
	public function publishCustomer(CreateCustomerPayload $customer): GetCustomerPayload
	{
		try {
			$_customer = ApiTools::getApi()->customer()->create($customer);

			if (empty($_customer)) {
				throw new Exception('Ocorreu um erro inesperado, contate a equipe da loja e informe o código APPMAX_GATEWAY_CUSTOMER_CREATE');
			}

			return $_customer;
		} catch (Exception $e) {
			Connector::debugger()->force()->error(\sprintf('Não foi possível salvar o cliente na API: %s', $e->getMessage()));
			throw $e;
		}
	}

	/**
	 * Publish order on API.
	 *
	 * @param CreateOrderPayload $order
	 * @since 0.1.0
	 * @return GetOrderPayload
	 */
	public function publishOrder(CreateOrderPayload $order): GetOrderPayload
	{
		try {
			$_order = ApiTools::getApi()->order()->create($order);

			if (empty($_order)) {
				throw new Exception('Ocorreu um erro inesperado, contate a equipe da loja e informe o código APPMAX_GATEWAY_ORDER_CREATE');
			}

			return $_order;
		} catch (Exception $e) {
			Connector::debugger()->force()->error(\sprintf('Não foi possível salvar o pedido na API: %s', $e->getMessage()));
			throw $e;
		}
	}

	/**
	 * Publish payment on API.
	 *
	 * @param CreatePaymentPayload $payment
	 * @since 0.1.0
	 * @return GetPaymentPayload
	 */
	public function publishPayment(CreatePaymentPayload $payment): GetPaymentPayload
	{
		try {
			$_payment = ApiTools::getApi()->payment()->create($payment);

			if (empty($_payment)) {
				throw new Exception('Ocorreu um erro inesperado, contate a equipe da loja e informe o código APPMAX_GATEWAY_PAYMENT_CREATE');
			}

			return $_payment;
		} catch (Exception $e) {
			Connector::debugger()->force()->error(\sprintf('Não foi possível salvar o pagamento na API: %s', $e->getMessage()));
			throw $e;
		}
	}

	/**
	 * Return the qr code based in current pix code.
	 * The qr code format is a base64 image/png.
	 *
	 * @param string $text
	 * @param string $imageType Type of output image.
	 * @param string $ecc QrCode ECC.
	 * @since 0.1.0
	 * @return string
	 * @throws Exception When something went wrong.
	 */
	public function getQRCode(string $text, string $fallback, string $imageType = QRCode::OUTPUT_MARKUP_SVG, int $ecc = QRCode::ECC_M): string
	{
		if (!self::supportQrCode()) {
			return $fallback;
		}

		$options = new QROptions([
			'outputLevel' => $ecc,
			'outputType' => $imageType
		]);

		return (new QRCode($options))->render($text);
	}

	/**
	 * Return if php supports QR Code.
	 *
	 * @since 0.1.0
	 * @return bool
	 */
	public static function supportQrCode(): bool
	{
		return ((float)phpversion('Core') >= 7.2) && (extension_loaded('gd') && function_exists('gd_info'));
	}
}
