<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Interfaces;

use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Interfaces\Importable;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Interfaces\Arrayable;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Interfaces\SensitiveDataArrayable;

/**
 * Payment type interface.
 *
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Payloads\Interfaces
 * @version 1.0.0
 * @since 1.0.0
 * @category Interfaces
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license PGLY
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
interface PaymentTypeInterface extends Arrayable, Importable, SensitiveDataArrayable
{
	/**
	 * Get payment type name.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function getPaymentType(): string;

	/**
	 * Get payment type name.
	 *
	 * @since 1.1.0-beta
	 * @return string
	 */
	public static function getPaymentMethod(): string;
}
