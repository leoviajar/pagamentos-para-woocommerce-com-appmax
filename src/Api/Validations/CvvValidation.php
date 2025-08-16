<?php

namespace AppMax\WooCommerce\Gateway\Api\Validations;

use InvalidArgumentException;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;

/**
 * CVV validation.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Validations
 * @category Validations
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class CvvValidation
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
		$value = Parser::anyToString($value);

		if ((\strlen($value) < 3 || \strlen($value) > 4) || !\ctype_digit($value)) {
			throw new InvalidArgumentException("O CVV do cartão é inválido.");
		}

		return $value;
	}
}
