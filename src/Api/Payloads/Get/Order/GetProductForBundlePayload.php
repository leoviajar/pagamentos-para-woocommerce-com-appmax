<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Get\Order;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;

/**
 * Product for bundle payload.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Payloads\Get\Order
 * @category Payloads
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class GetProductForBundlePayload extends AbstractPayload
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $_fields = [
		'id' => null,
		'sku' => null,
		'name' => null,
		'price' => null,
		'description' => null, // string
		'quantity' => 1, // integer
		'image' => null, // string
		'external_id' => null, // integer
	];

	/**
	 * Construct object.
	 *
	 * @param string $sku
	 * @param string $name
	 * @param float $price
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct($sku, $name, $price)
	{
		$this->_fields['sku'] = Parser::anyToString($sku);
		$this->_fields['name'] = Parser::anyToString($name);
		$this->_fields['price'] = Parser::anyToFloat($price);
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
	 * Get price field.
	 *
	 * @since 1.0.0
	 * @return float
	 */
	public function getPrice(): float
	{
		return $this->_fields['price'];
	}

	/**
	 * Get description field.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function getDescription(): ?string
	{
		return $this->_fields['description'];
	}

	/**
	 * Get weight field.
	 *
	 * @since 1.0.0
	 * @return int|null
	 */
	public function getQuantity(): int
	{
		return $this->_fields['quantity'];
	}

	/**
	 * Get external id field.
	 *
	 * @since 1.0.0
	 * @return int|null
	 */
	public function getExternalId(): ?int
	{
		return $this->_fields['external_id'];
	}

	/**
	 * Get image field.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function getImage(): ?string
	{
		return $this->_fields['image'];
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
		$arr = [
			'sku' => $this->getSku(),
			'name' => $this->getName(),
			'price' => $this->getPrice(),
			'quantity' => $this->getQuantity()
		];

		if (!empty($this->getDescription())) {
			$arr['description'] = $this->getDescription();
		}

		if (!empty($this->getExternalId())) {
			$arr['external_id'] = $this->getExternalId();
		}

		if (!empty($this->getImage())) {
			$arr['image'] = $this->getImage();
		}

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
		return $this->toArray();
	}

	/**
	 * Import and return the object.
	 *
	 * @param array $body
	 * @since 1.0.0
	 * @return GetProductForBundlePayload
	 */
	public static function import(array $data = []): GetProductForBundlePayload
	{
		$payload = new GetProductForBundlePayload($data['sku'], $data['name'], $data['price']);

		if (isset($data['description'])) {
			$payload->_fields['description'] = Parser::anyToString($data['description']);
		}

		if (isset($data['quantity'])) {
			$payload->_fields['quantity'] = Parser::anyToInteger($data['quantity']);
		}

		if (isset($data['external_id'])) {
			$payload->_fields['external_id'] = Parser::anyToString($data['external_id']);
		}

		if (isset($data['image'])) {
			$payload->_fields['image'] = Parser::anyToString($data['image']);
		}

		return $payload;
	}
}
