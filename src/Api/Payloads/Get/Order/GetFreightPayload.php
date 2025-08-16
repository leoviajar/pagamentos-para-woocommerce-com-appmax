<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Get\Order;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;

/**
 * Delivery tracking code payload.
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
class GetFreightPayload extends AbstractPayload
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 0.1.0
	 */
	protected $_fields = [
		'type' => null,
		'value' => null,
	];

	/**
	 * Construct object.
	 *
	 * @param string $type
	 * @param float $value
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct($type, $value)
	{
		$this->_fields['type'] = Parser::anyToString($type);
		$this->_fields['value'] = Parser::anyToFloat($value);
	}

	/**
	 * Get type field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getType(): string
	{
		return $this->_fields['type'];
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
			'type' => $this->getType(),
			'value' => $this->getValue()
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
	 * @return GetFreightPayload
	 */
	public static function import(array $data = []): GetFreightPayload
	{
		return  new GetFreightPayload(
			$data['type'],
			$data['value']
		);
	}
}
