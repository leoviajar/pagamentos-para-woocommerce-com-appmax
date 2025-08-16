<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Get\Order;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;

/**
 * AffiliateCommission payload.
 * Generally used in order payload.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Payloads\Get\Order
 * @category Payloads
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class GetAffiliateCommissionPayload extends AbstractPayload
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 0.1.0
	 */
	protected $_fields = [
		'affiliate_id' => null,
		'value' => null,
	];

	/**
	 * Construct object.
	 *
	 * @param integer $affiliate_id
	 * @param float $value
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct($affiliate_id, $value)
	{
		$this->_fields['affiliate_id'] = Parser::anyToInteger($affiliate_id);
		$this->_fields['value'] = Parser::anyToFloat($value);
	}

	/**
	 * Get affiliate_id field.
	 *
	 * @since 1.0.0
	 * @return float
	 */
	public function getAffiliateId(): float
	{
		return $this->_fields['affiliate_id'];
	}

	/**
	 * Get name field.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->_fields['name'];
	}

	/**
	 * Get email field.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function getEmail(): ?string
	{
		return $this->_fields['email'];
	}

	/**
	 * Get value field.
	 *
	 * @since 1.0.0
	 * @return float
	 */
	public function getValue(): float
	{
		return $this->_fields['value'];
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
			'affiliate_id' => $this->getAffiliateId(),
			'value' => $this->getValue(),
			'name' => $this->getName(),
			'email' => $this->getEmail()
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
		return $this->toArray();
	}

	/**
	 * Import and return the object.
	 *
	 * @param array $body
	 * @since 1.0.0
	 * @return GetAffiliateCommissionPayload
	 */
	public static function import(array $data = []): GetAffiliateCommissionPayload
	{
		$payload = new GetAffiliateCommissionPayload(
			$data['affiliate_id'],
			$data['value']
		);

		if (!empty($data['name'])) {
			$payload->_fields['name'] = Parser::anyToString($data['name']);
		}

		if (!empty($data['email'])) {
			$payload->_fields['email'] = Parser::anyToString($data['email']);
		}

		return $payload;
	}
}
