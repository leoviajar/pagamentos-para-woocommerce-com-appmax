<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Create;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\Order\CreateOrderItemPayload;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;
use AppMax\WooCommerce\Gateway\Api\Validations\NotEmptyValidation;

/**
 * Order payload.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Payloads\Create
 * @category Payloads
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class CreateOrderPayload extends AbstractPayload
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $_fields = [
		'customer_id' => null,
		'total' => null,
		'shipping' => null,
		'discount' => null,
		'freight_type' => null,
		'products' => null,
		'ip' => null,
	];

	/**
	 * Order items.
	 *
	 * @var CreateOrderItemPayload[]
	 * @since 1.0.0
	 */
	protected $_items = [];

	/**
	 * Construct object.
	 *
	 * @param int $customer_id
	 * @param float $total
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct(
		$customer_id,
		$total,
		$ip
	) {
		$this->_fields['customer_id'] = Parser::anyToInteger(NotEmptyValidation::validate($customer_id, 'ID do Cliente'));
		$this->_fields['total'] = Parser::anyToFloat(NotEmptyValidation::validate($total, 'Total do Pedido'));
		$this->_fields['ip'] = Parser::anyToString(NotEmptyValidation::validate($ip, 'IP de Origem'));
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
	 * Get ip field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getIp(): string
	{
		return $this->_fields['ip'];
	}

	/**
	 * Get total field.
	 *
	 * @since 1.0.0
	 * @return float
	 */
	public function getTotal(): float
	{
		return $this->_fields['total'];
	}

	/**
	 * Get shipping field.
	 *
	 * @since 1.0.0
	 * @return float|null
	 */
	public function getShippingCost(): ?float
	{
		return $this->_fields['shipping'];
	}

	/**
	 * Get discount field.
	 *
	 * @since 1.0.0
	 * @return float|null
	 */
	public function getDiscountAmount(): ?float
	{
		return $this->_fields['discount'];
	}

	/**
	 * Get freight_type field.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function getFreightType(): ?string
	{
		return $this->_fields['freight_type'];
	}

	/**
	 * Set amount.
	 *
	 * @param float $amount
	 * @return self
	 * @since 1.0.0
	 */
	public function setShippingCost($amount)
	{
		$this->_fields['shipping'] = Parser::anyToFloat($amount);
		return $this;
	}

	/**
	 * Set amount.
	 *
	 * @param float $amount
	 * @return self
	 * @since 1.0.0
	 */
	public function setDiscountAmount($amount)
	{
		$this->_fields['discount'] = Parser::anyToFloat($amount);
		return $this;
	}

	/**
	 * Set freight type.
	 *
	 * @param string $type
	 * @return self
	 * @since 1.0.0
	 */
	public function setFreightType($type)
	{
		$this->_fields['freight_type'] = Parser::anyToString($type);
		return $this;
	}

	/**
	 * Get items field.
	 *
	 * @since 1.0.0
	 * @return CreateOrderItemPayload[]
	 */
	public function getItems(): array
	{
		return $this->_items;
	}

	/**
	 * Add an item.
	 *
	 * @param CreateOrderItemPayload $product
	 * @return self
	 */
	public function addItem(CreateOrderItemPayload $product)
	{
		$this->_items[] = $product;
		return $this;
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
			'customer_id' => $this->getCustomerId(),
			'total' => $this->getTotal(),
			'ip' => $this->getIp(),
		];

		if (!empty($this->getShippingCost())) {
			$arr['shipping'] = $this->getShippingCost();
		}

		if (!empty($this->getDiscountAmount())) {
			$arr['discount'] = $this->getDiscountAmount();
		}

		if (!empty($this->getFreightType())) {
			$arr['freight_type'] = $this->getFreightType();
		}

		if (!empty($this->_items)) {
			$arr['products'] = \array_map(function ($product) {
				return $product->toArray();
			}, $this->_items);
		}

		return $arr;
	}

	/**
	 * Get all fields converting payloads
	 * to an array and removing null values.
	 *
	 * @since 1.0.0
	 * @since 1.1.0-beta Add more data to censored array.
	 * @return array
	 */
	public function toCensoredArray(): array
	{
		$arr = [
			'customer_id' => $this->getCustomerId(),
			'total' => $this->getTotal(),
		];

		if (!empty($this->getShippingCost())) {
			$arr['shipping'] = $this->getShippingCost();
		}

		if (!empty($this->getDiscountAmount())) {
			$arr['discount'] = $this->getDiscountAmount();
		}

		if (!empty($this->getFreightType())) {
			$arr['freight_type'] = $this->getFreightType();
		}

		if (!empty($this->_items)) {
			$arr['products'] = \array_map(function ($product) {
				return $product->toArray();
			}, $this->_items);
		}

		return $arr;
	}

	/**
	 * Import and return the object.
	 *
	 * @param array $body
	 * @since 1.0.0
	 * @return CreateOrderPayload
	 */
	public static function import(array $data = []): CreateOrderPayload
	{
		$payload = new CreateOrderPayload(
			$data['customer_id'],
			$data['total'],
			$data['ip'] ?? '127.0.0.1'
		);

		if (isset($data['shipping'])) {
			$payload->setShippingCost($data['shipping']);
		}

		if (isset($data['discount'])) {
			$payload->setDiscountAmount($data['discount']);
		}

		if (isset($data['freight_type'])) {
			$payload->setFreightType($data['freight_type']);
		}

		if (isset($data['products']) && \is_array($data['products'])) {
			foreach ($data['products'] as $product) {
				$payload->addItem(CreateOrderItemPayload::import($product));
			}
		}

		return $payload;
	}
}
