<?php

namespace AppMax\WooCommerce\Gateway\Api\Validations;

use InvalidArgumentException;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;

/**
 * Expiration date validation.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Validations
 * @category Validations
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class ExpirationDateValidation
{
	/**
	 * Validate if $value is a valid expiration date.
	 *
	 * @param mixed $value
	 * @param string $name
	 * @return mixed
	 * @since 1.0.0
	 */
	public static function validate($month, $year)
	{
		$month = Parser::anyToInteger($month);
		$nyear = \intval(\strlen(\strval($year)) === 4 ? date('Y') : date('y'));
		$nmonth = \intval(date('m'));

		if (($year < $nyear || $year > $nyear+30) || ($year === $nyear && $month < $nmonth)) {
			throw new InvalidArgumentException("O mês/ano de expiração do cartão é inválido.");
		}

		return true;
	}
}
