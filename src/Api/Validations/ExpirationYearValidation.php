<?php

namespace AppMax\WooCommerce\Gateway\Api\Validations;

use InvalidArgumentException;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;

/**
 * Expiration year validation.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Validations
 * @category Validations
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class ExpirationYearValidation
{
	/**
	 * Validate if $value is a valid year.
	 *
	 * @param mixed $value
	 * @param string $name
	 * @return mixed
	 * @since 1.0.0
	 */
	public static function validate($value)
	{
		$value = Parser::anyToInteger($value);
		$now = \intval(\strlen(\strval($value)) === 4 ? date('Y') : date('y'));

		if ($value < $now || $value > $now+30) {
			throw new InvalidArgumentException("O ano de expiração do cartão é inválido.");
		}

		return $value > 2000 ? $value - 2000 : $value;
	}
}
