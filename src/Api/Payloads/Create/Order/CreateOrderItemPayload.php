<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Create\Order;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;
use AppMax\WooCommerce\Gateway\Api\Validations\NotEmptyValidation;

/**
 * Product payload.
 * Generally used in order payload.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Payloads\Create\Order
 * @category Payloads
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class CreateOrderItemPayload extends AbstractPayload
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $_fields = [
		'sku' => null,
		'name' => null,
		'price' => null,
	];

	/**
	 * Construct object.
	 *
	 * @param string $sku
	 * @param string $name
	 * @param integer $qty
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct($sku, $name, $qty)
	{
		$this->_fields['sku'] = Parser::anyToString(NotEmptyValidation::validate($sku, 'SKU'));
		$this->_fields['name'] = Parser::anyToString(NotEmptyValidation::validate($name, 'Nome'));
		$this->_fields['qty'] = Parser::anyToInteger(NotEmptyValidation::validate($qty, 'Quantidade'));
	}

	/**
	 * Mark it as digital product.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function digitalProduct()
	{
		$this->_fields['digital_product'] = 1;
		return $this;
	}

	/**
	 * Mark it as physical product.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function physicalProduct()
	{
		unset($this->_fields['digital_product']);
		return $this;
	}

	/**
	 * Get SKU field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getSku(): string
	{
		return $this->_fields['sku'];
	}

	/**
	 * Get name field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getName(): string
	{
		return $this->_fields['name'];
	}

	/**
	 * Get quantity field.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function getQuantity(): int
	{
		return $this->_fields['qty'];
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
		return $this->_fields;
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
	 * @return CreateOrderItemPayload
	 */
	public static function import(array $data = []): CreateOrderItemPayload
	{
		$payload = new CreateOrderItemPayload($data['sku'], $data['name'], $data['qty']);
		return $payload;
	}
}
