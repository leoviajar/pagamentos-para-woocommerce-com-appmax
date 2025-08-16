<?php

namespace AppMax\WooCommerce\Gateway\Api\Validations;

use InvalidArgumentException;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;

/**
 * Expiration month validation.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Validations
 * @category Validations
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class ExpirationMonthValidation
{
	/**
	 * Validate if $value is a valid month.
	 *
	 * @param mixed $value
	 * @param string $name
	 * @return mixed
	 * @since 1.0.0
	 */
	public static function validate($value)
	{
		$value = Parser::anyToInteger($value);

		if ($value < 1 || $value > 12) {
			throw new InvalidArgumentException("O mês de expiração do cartão é inválido.");
		}

		return $value;
	}
}
