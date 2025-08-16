<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Create;

use InvalidArgumentException;
use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Interfaces\PaymentTypeInterface;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\PaymentMethod\CreateBoletoPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\PaymentMethod\CreateCreditCardPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\PaymentMethod\CreatePixPayload;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;
use AppMax\WooCommerce\Gateway\Api\Validations\NotEmptyValidation;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Interfaces\SensitiveDataArrayable;

/**
 * Payment payload.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Payloads\Create
 * @category Payloads
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class CreatePaymentPayload extends AbstractPayload implements SensitiveDataArrayable
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 0.1.0
	 */
	protected $_fields = [
		'cart' => [ 'order_id' => null ],
		'customer' => [ 'customer_id' => null ],
		'payment' => [ ],
	];

	/**
	 * Payment method.
	 *
	 * @var PaymentTypeInterface
	 * @since 1.0.0
	 */
	protected $_method;


	/**
	 * Construct object.
	 *
	 * @param int $order_id
	 * @param int $customer_id
	 * @param PaymentTypeInterface $payment
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct(
		$order_id,
		$customer_id,
		PaymentTypeInterface $payment
	) {
		$this->_fields['cart']['order_id'] = Parser::anyToInteger(NotEmptyValidation::validate($order_id, 'ID do Pedido'));
		$this->_fields['customer']['customer_id'] = Parser::anyToInteger(NotEmptyValidation::validate($customer_id, 'ID do Cliente'));
		$this->_method = $payment;
	}

	/**
	 * Get order_id field.
	 *
	 * @since 1.0.0
	 * @return integer
	 */
	public function getOrderId(): int
	{
		return $this->_fields['order_id'];
	}

	/**
	 * Get customer_id field.
	 *
	 * @since 1.0.0
	 * @return integer
	 */
	public function getCustomerId(): int
	{
		return $this->_fields['customer_id'];
	}

	/**
	 * Get payment method.
	 *
	 * @since 1.0.0
	 * @return PaymentTypeInterface
	 */
	public function getMethod(): PaymentTypeInterface
	{
		return $this->_method;
	}

	/**
	 * Get all fields converting payloads
	 * to an array and removing null values.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function toArray(): array
	{
		$arr = $this->_fields;
		$arr['payment'] = [$this->_method->getPaymentType() => $this->_method->toArray()];
		return $arr;
	}

	/**
	 * Get all fields converting payloads
	 * to an array and removing null values.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function toCensoredArray(): array
	{
		$arr = $this->_fields;
		$arr['payment'] = [$this->_method::getPaymentType() => $this->_method->toCensoredArray()];
		return $arr;
	}

	/**
	 * Solve the payment method.
	 *
	 * @param array $data
	 * @since 1.0.0
	 * @return PaymentTypeInterface
	 * @throws InvalidArgumentException
	 */
	public static function solveMethod(array $data = []): PaymentTypeInterface
	{
		if (isset($data[CreateCreditCardPayload::getPaymentType()])) {
			return CreateCreditCardPayload::import($data[CreateCreditCardPayload::getPaymentType()]);
		}

		if (isset($data[CreateBoletoPayload::getPaymentType()])) {
			return CreateBoletoPayload::import($data[CreateBoletoPayload::getPaymentType()]);
		}

		if (isset($data[CreatePixPayload::getPaymentType()])) {
			return CreatePixPayload::import($data[CreatePixPayload::getPaymentType()]);
		}

		throw new InvalidArgumentException('Método de pagamento não suportado.');
	}

	/**
	 * Import and return the object.
	 *
	 * @param array $body
	 * @since 1.0.0
	 * @return CreatePaymentPayload
	 */
	public static function import(array $data = []): CreatePaymentPayload
	{
		return new CreatePaymentPayload(
			$data['order_id'],
			$data['customer_id'],
			static::solveMethod($data['payment'])
		);
	}
}
