<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Get\PaymentMethod;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Interfaces\PaymentTypeInterface;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;
use AppMax\WooCommerce\Gateway\Core\Records\Enums\PaymentMethodEnum;

/**
 * Payment method payload.
 * Generally used in payment payload.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Payloads\Get\PaymentMethod
 * @category Payloads
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class GetCreditCardPayload extends AbstractPayload implements PaymentTypeInterface
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 0.1.0
	 */
	protected $_fields = [
		'upsell_hash' => null,
	];

	/**
	 * Construct object.
	 *
	 * @param string $upsell_hash
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct($upsell_hash)
	{
		$this->_fields['upsell_hash'] = Parser::anyToString($upsell_hash);
	}

	/**
	 * Get pdf field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getUpsellHash(): string
	{
		return $this->_fields['upsell_hash'];
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
			'upsell_hash' => $this->getUpsellHash(),
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
		return [ 'upsell_hash' => '***' ];
	}

	/**
	 * Get payment type name.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function getPaymentType(): string
	{
		return 'CreditCard';
	}

	/**
	 * Get payment type name.
	 *
	 * @since 1.1.0-beta
	 * @return string
	 */
	public static function getPaymentMethod(): string
	{
		return PaymentMethodEnum::PAYMENT_METHOD_CREDIT_CARD;
	}

	/**
	 * Import and return the object.
	 *
	 * @param array $body
	 * @since 1.0.0
	 * @return GetCreditCardPayload
	 */
	public static function import(array $data = []): GetCreditCardPayload
	{
		return new GetCreditCardPayload($data['upsell_hash']);
	}
}
