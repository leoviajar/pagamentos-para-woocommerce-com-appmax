<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Get\Order;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;

/**
 * Bundle payload.
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
class GetBundlePayload extends AbstractPayload
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 0.1.0
	 */
	protected $_fields = [
		'id' => null,
		'name' => null,
		'description' => null,
	];

	/**
	 * Products.
	 *
	 * @var GetProductForBundlePayload[]
	 * @since 1.0.0
	 */
	protected $_products = [];

	/**
	 * Construct object.
	 *
	 * @param int $id
	 * @param string $name
	 * @param string $description
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct($id, $name, $description)
	{
		$this->_fields['id'] = Parser::anyToInteger($id);
		$this->_fields['name'] = Parser::anyToString($name);
		$this->_fields['description'] = Parser::anyToString($description);
	}

	/**
	 * Get id field.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function getId(): int
	{
		return $this->_fields['id'];
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
	 * Get description field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getDescription(): string
	{
		return $this->_fields['description'];
	}

	/**
	 * Get products field.
	 *
	 * @since 1.0.0
	 * @return GetProductForBundlePayload[]
	 */
	public function getProducts(): array
	{
		return $this->_products;
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
			'id' => $this->getId(),
			'name' => $this->getName(),
			'description' => $this->getDescription(),
			'products' => \array_map(function ($product) {
				return $product->toArray();
			}, $this->_products)
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
	 * @return GetBundlePayload
	 */
	public static function import(array $data = []): GetBundlePayload
	{
		$payload = new GetBundlePayload(
			$data['id'] ?? null,
			$data['name'] ?? null,
			$data['description'] ?? null
		);

		if (isset($data['products']) && \is_array($data['products'])) {
			$payload->_products = \array_map(function ($product) {
				return GetProductForBundlePayload::import($product);
			}, $data['products']);
		}

		return $payload;
	}
}
