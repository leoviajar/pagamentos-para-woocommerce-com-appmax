<?php

namespace AppMax\WooCommerce\Gateway\Api\Validations;

use InvalidArgumentException;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;

/**
 * Card number validation.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Validations
 * @category Validations
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class CardNumberValidation
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
		$value = Parser::digits($value);

		if (static::isCardNumberValid($value) === false) {
			throw new InvalidArgumentException("O número do cartão não é válido.");
		}

		return $value;
	}

	/**
	 * Check if a card number is valid.
	 *
	 * @param string $number Card number.
	 * @return bool
	 * @since 1.0.0
	 */
	protected static function isCardNumberValid(string $number): bool
	{
		if (empty($number)) {
			return false;
		}

		// Strip any non-digits (useful for credit card numbers with spaces and hyphens)
		$number = \preg_replace('/\D/', '', $number);

		// Set the string length and parity
		$number_length= \strlen($number);
		$parity = $number_length % 2;

		// Loop through each digit and do the maths
		$total=0;
		for ($i=0; $i<$number_length; $i++) {
			$digit=$number[$i];
			// Multiply alternate digits by two
			if ($i % 2 == $parity) {
				$digit*=2;
				// If the sum is two digits, add them together (in effect)
				if ($digit > 9) {
					$digit-=9;
				}
			}
			// Total up the digits
			$total+=$digit;
		}

		// If the total mod 10 equals 0, the number is valid
		return ($total % 10 == 0) ? true : false;
	}
}
