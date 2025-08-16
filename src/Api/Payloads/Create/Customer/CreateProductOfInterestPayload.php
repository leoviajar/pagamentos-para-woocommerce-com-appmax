<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Create\Customer;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;
use AppMax\WooCommerce\Gateway\Api\Validations\NotEmptyValidation;

/**
 * Product of interest payload.
 * Generally used in customer payload.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Payloads\Create\Customer
 * @category Payloads
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class CreateProductOfInterestPayload extends AbstractPayload
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $_fields = [
		'sku' => null,
		'qty' => null,
	];

	/**
	 * Construct object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct($sku, $qty)
	{
		$this->_fields['sku'] = Parser::anyToString(NotEmptyValidation::validate($sku, 'SKU'));
		$this->_fields['qty'] = Parser::anyToInteger(NotEmptyValidation::validate($qty, 'Quantidade'));
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
	 * Get quantity field.
	 *
	 * @since 1.0.0
	 * @return integer
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
	 * @return CreateProductOfInterestPayload*/
	public static function import(array $data = []): CreateProductOfInterestPayload
	{
		return new CreateProductOfInterestPayload(
			$data['sku'] ?? null,
			$data['qty'] ?? null
		);
	}
}
