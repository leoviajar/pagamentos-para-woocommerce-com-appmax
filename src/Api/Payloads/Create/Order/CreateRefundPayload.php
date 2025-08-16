<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Create\Order;

use InvalidArgumentException;
use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;
use AppMax\WooCommerce\Gateway\Api\Validations\NotEmptyValidation;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Payloads\Rules\AllowedValuesRule;

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
class CreateRefundPayload extends AbstractPayload
{
	/**
	 * Refund type as total.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	public const REFUND_TYPE_TOTAL = 'total';

	/**
	 * Refund type as partial.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	public const REFUND_TYPE_PARTIAL = 'partial';

	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 0.1.0
	 */
	protected $_fields = [
		'order_id' => null,
		'refund_type' => null,
		'refund_amount' => null,
	];

	/**
	 * Construct object.
	 *
	 * @param int $order_id
	 * @param string $type
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct($order_id, $type)
	{
		(new AllowedValuesRule(['total', 'partial']))->assert('refund_type', $type);

		$this->_fields['order_id'] = Parser::anyToInteger(NotEmptyValidation::validate($order_id, 'ID do Pedido'));
		$this->_fields['refund_type'] = $type;
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
	 * Get type field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getType(): string
	{
		return $this->_fields['refund_type'];
	}

	/**
	 * Get amount field.
	 *
	 * @since 1.0.0
	 * @return float|null
	 */
	public function getAmount(): ?float
	{
		return $this->_fields['refund_amount'];
	}

	/**
	 * Change amount field.
	 *
	 * @param mixed $number
	 * @since 1.0.0
	 * @return self
	 */
	public function setAmount($amount)
	{
		$this->_fields['refund_amount'] = Parser::anyToInteger($amount);
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
			'order_id' => $this->getOrderId(),
			'refund_type' => $this->getType(),
		];

		if ($this->getType() === self::REFUND_TYPE_PARTIAL) {
			if ($this->getAmount() === null) {
				throw new InvalidArgumentException('O valor do reembolso é obrigatório quando o tipo é parcial.');
			}

			$arr['refund_amount'] = $this->getAmount();
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
	 * @return CreateRefundPayload
	 */
	public static function import(array $data = []): CreateRefundPayload
	{
		$payload = new CreateRefundPayload(
			$data['order_id'],
			$data['refund_type']
		);

		if (isset($data['refund_amount'])) {
			$payload->setAmount($data['refund_amount']);
		}

		return $payload;
	}
}
