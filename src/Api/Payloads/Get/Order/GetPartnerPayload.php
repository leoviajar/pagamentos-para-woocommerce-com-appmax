<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Get\Order;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;

/**
 * Partner payload.
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
class GetPartnerPayload extends AbstractPayload
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 0.1.0
	 */
	protected $_fields = [
		'total' => null,
		'affiliate_total' => null,
	];

	/**
	 * Construct object.
	 *
	 * @param float $total
	 * @param float $affiliate_total
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct($total, $affiliate_total)
	{
		$this->_fields['total'] = Parser::anyToFloat($total);
		$this->_fields['affiliate_total'] = Parser::anyToFloat($affiliate_total);
	}

	/**
	 * Get type field.
	 *
	 * @since 1.0.0
	 * @return float
	 */
	public function getTotal(): float
	{
		return $this->_fields['total'];
	}

	/**
	 * Get affiliate_total field.
	 *
	 * @since 1.0.0
	 * @return float
	 */
	public function getAffiliateTotal(): float
	{
		return $this->_fields['affiliate_total'];
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
			'total' => $this->getTotal(),
			'affiliate_total' => $this->getAffiliateTotal()
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
	 * @return GetPartnerPayload
	 */
	public static function import(array $data = []): GetPartnerPayload
	{
		return  new GetPartnerPayload(
			$data['total'],
			$data['affiliate_total']
		);
	}
}
