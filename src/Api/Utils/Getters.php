<?php

namespace AppMax\WooCommerce\Gateway\Api\Utils;

/**
 * Get any data available.
 *
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Helpers
 * @version 1.0.0
 * @since 1.0.0
 * @category Helpers
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license PGLY
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class Getters
{
	/**
	 * Get current IP.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function ip(): string
	{
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			return (string) rest_is_ip_address(trim(current(preg_split('/,/', sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']))))));
		}

		if (isset($_SERVER['HTTP_CLIENT_IP'])) {
			return $_SERVER['HTTP_CLIENT_IP'];
		}

		if (isset($_SERVER['HTTP_X_REAL_IP'])) {
			return sanitize_text_field(wp_unslash($_SERVER['HTTP_X_REAL_IP']));
		}

		if (isset($_SERVER['REMOTE_ADDR'])) {
			return sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
		}

		return '';
	}
}
