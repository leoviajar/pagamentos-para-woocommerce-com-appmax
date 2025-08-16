<?php

namespace AppMax\WooCommerce\Gateway\Api\Validations;

use InvalidArgumentException;

/**
 * Not empty validation.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Validations
 * @category Validations
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class NotEmptyValidation
{
	/**
	 * Validate if $value is not empty.
	 *
	 * @param mixed $value
	 * @param string $name
	 * @return mixed
	 * @since 1.0.0
	 */
	public static function validate($value, string $name)
	{
		if (empty($value)) {
			throw new InvalidArgumentException("O campo `{$name}` não pode ser vazio.");
		}

		return $value;
	}
}
