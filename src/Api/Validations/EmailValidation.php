<?php

namespace AppMax\WooCommerce\Gateway\Api\Validations;

use InvalidArgumentException;

/**
 * E-mail validation.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Validations
 * @category Validations
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class EmailValidation
{
	/**
	 * Validate if $value is a valid e-mail.
	 *
	 * @param mixed $value
	 * @param string $name
	 * @return mixed
	 * @since 1.0.0
	 */
	public static function validate($value)
	{
		if (filter_var($value, \FILTER_VALIDATE_EMAIL) === false) {
			throw new InvalidArgumentException("O valor `{$value}` não é um e-mail válido.");
		}

		return $value;
	}
}
