<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Create\Order;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;
use AppMax\WooCommerce\Gateway\Api\Validations\NotEmptyValidation;

/**
 * Delivery tracking code payload.
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
class CreateDeliveryTrackingCodePayload extends AbstractPayload
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 0.1.0
	 */
	protected $_fields = [
		'order_id' => null,
		'delivery_tracking_code' => null,
	];

	/**
	 * Construct object.
	 *
	 * @param int $order_id
	 * @param string $delivery_tracking_code
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct($order_id, $delivery_tracking_code)
	{
		$this->_fields['order_id'] = Parser::anyToInteger(NotEmptyValidation::validate($order_id, 'ID do Pedido'));
		$this->_fields['delivery_tracking_code'] = Parser::anyToString(NotEmptyValidation::validate($delivery_tracking_code, 'Código de Rastreio'));
	}

	/**
	 * Get order id field.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function getOrderId(): int
	{
		return $this->_fields['order_id'];
	}

	/**
	 * Get tracking code field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getTrackingCode(): string
	{
		return $this->_fields['delivery_tracking_code'];
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
			'order_id' => $this->getOrderId(),
			'delivery_tracking_code' => $this->getTrackingCode(),
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
	 * @return CreateDeliveryTrackingCodePayload
	 */
	public static function import(array $data = []): CreateDeliveryTrackingCodePayload
	{
		return  new CreateDeliveryTrackingCodePayload(
			$data['order_id'],
			$data['delivery_tracking_code']
		);
	}
}
