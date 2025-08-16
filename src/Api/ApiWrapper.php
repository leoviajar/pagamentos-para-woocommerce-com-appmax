<?php

namespace AppMax\WooCommerce\Gateway\Api;

use DateTimeZone;
use AppMax\WooCommerce\Gateway\Api\Endpoints\CustomerEndpoint;
use AppMax\WooCommerce\Gateway\Api\Endpoints\OrderEndpoint;
use AppMax\WooCommerce\Gateway\Api\Endpoints\PaymentEndpoint;
use AppMax\WooCommerce\Gateway\Api\Endpoints\ProductEndpoint;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Wrapper;

/**
 * Api Wrapper
 *
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api
 * @version 0.1.0
 * @since 0.1.0
 * @category Api
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license PGLY
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class ApiWrapper extends Wrapper
{
	/**
	 * Customer endpoints.
	 *
	 * @since 0.1.0
	 * @return CustomerEndpoint
	 */
	public function customer(): CustomerEndpoint
	{
		return $this->endpoint('customer');
	}

	/**
	 * Order endpoints.
	 *
	 * @since 0.1.0
	 * @return OrderEndpoint
	 */
	public function order(): OrderEndpoint
	{
		return $this->endpoint('order');
	}

	/**
	 * Payment endpoints.
	 *
	 * @since 0.1.0
	 * @return PaymentEndpoint
	 */
	public function payment(): PaymentEndpoint
	{
		return $this->endpoint('payment');
	}

	/**
	 * Product endpoints.
	 *
	 * @since 0.1.0
	 * @return ProductEndpoint
	 */
	public function product(): ProductEndpoint
	{
		return $this->endpoint('product');
	}

	/**
	 * Get all endpoint classes name.
	 *
	 * @since 0.1.0
	 * @return array
	 */
	public static function endpointClasses(): array
	{
		return [
			'customer' => CustomerEndpoint::class,
			'order' => OrderEndpoint::class,
			'payment' => PaymentEndpoint::class,
			'product' => ProductEndpoint::class
		];
	}

	/**
	 * Get timezone.
	 *
	 * @since 0.1.0
	 * @return DateTimeZone
	 */
	public static function getTimezone(): DateTimeZone
	{
		return static::$_timezone;
	}
}
