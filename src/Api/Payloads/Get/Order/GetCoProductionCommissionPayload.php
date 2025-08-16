<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Get\Order;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;

/**
 * CoProductionCommission payload.
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
class GetCoProductionCommissionPayload extends AbstractPayload
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 0.1.0
	 */
	protected $_fields = [
		'user_id' => null,
		'value' => null,
	];

	/**
	 * Construct object.
	 *
	 * @param integer $user_id
	 * @param float $value
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct($user_id, $value)
	{
		$this->_fields['user_id'] = Parser::anyToInteger($user_id);
		$this->_fields['value'] = Parser::anyToFloat($value);
	}

	/**
	 * Get user_id field.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function getUserId(): int
	{
		return $this->_fields['user_id'];
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
			'user_id' => $this->getUserId(),
			'value' => $this->getValue(),
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
	 * @return GetCoProductionCommissionPayload
	 */
	public static function import(array $data = []): GetCoProductionCommissionPayload
	{
		return  new GetCoProductionCommissionPayload(
			$data['user_id'],
			$data['value']
		);
	}
}
