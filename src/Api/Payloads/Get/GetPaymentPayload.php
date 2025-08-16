<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Get;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\Payment\GetAddressPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\PaymentMethod\GetBoletoPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\PaymentMethod\GetCreditCardPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\PaymentMethod\GetPixPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Interfaces\PaymentTypeInterface;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;

/**
 * Payment payload.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Payloads\Get
 * @category Payloads
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class GetPaymentPayload extends AbstractPayload
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $_fields = [
		'pay_reference' => null,
		'type' => null,
	];

	/**
	 * Payment method data.
	 *
	 * @var PaymentMethodTypeInterface
	 * @since 0.1.0
	 */
	protected $_method;

	/**
	 * Construct object.
	 *
	 * @param string $pay_reference
	 * @param string $type
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct(
		$pay_reference,
		$type
	) {
		$this->_fields['pay_reference'] = Parser::anyToString($pay_reference);
		$this->_fields['type'] = Parser::anyToString($type);
	}

	/**
	 * Get pay_reference.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public function getReference(): string
	{
		return $this->_fields['pay_reference'];
	}

	/**
	 * Get type.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public function getType(): string
	{
		return $this->_fields['type'];
	}

	/**
	 * Get method.
	 *
	 * @since 0.1.0
	 * @return PaymentTypeInterface
	 */
	public function getMethod()
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
		return [
			'pay_reference' => $this->getReference(),
			'type' => $this->getType(),
			'method' => $this->getMethod()->toArray()
		];
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
		return [
			'pay_reference' => $this->getReference(),
			'type' => $this->getType(),
			'method' => $this->getMethod()->toCensoredArray()
		];
	}

	/**
	 * Import and return the object.
	 *
	 * @param array $body
	 * @since 1.0.0
	 * @return GetPaymentPayload
	 */
	public static function import(array $data = []): GetPaymentPayload
	{
		$payload = new GetPaymentPayload(
			$data['pay_reference'],
			$data['type']
		);

		switch (\strtolower($data['type'])) {
			case 'pix':
				$payload->_method = GetPixPayload::import($data);
				break;
			case 'boleto':
				$payload->_method = GetBoletoPayload::import($data);
				break;
			case 'creditcard':
				$payload->_method = GetCreditCardPayload::import($data);
				break;
		}

		return $payload;
	}
}
