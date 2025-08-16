<?php

namespace AppMax\WooCommerce\Gateway\Api\Validations;

use InvalidArgumentException;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;

/**
 * Document validation.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Validations
 * @category Validations
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class DocumentValidation
{
	/**
	 * Validate if $value is not empty.
	 *
	 * @param mixed $value
	 * @return mixed
	 * @since 1.0.0
	 */
	public static function validate($value)
	{
		$document = Parser::digits($value);
		$valid = false;

		switch (\strlen($document)) {
			case 11:
				$valid = static::cpf($document);
				break;
			case 14:
				$valid = static::cnpj($document);
				break;
			default:
				$valid = false;
				break;
		}

		$type = \strlen($document) === 11 ? 'CPF' : 'CNPJ';

		if ($valid === false) {
			throw new InvalidArgumentException("O {$type} informado é inválido.");
		}

		return $document;
	}

	/**
	 * Check if a CPF is valid.
	 *
	 * @param string $cpf CPF.
	 * @return bool
	 * @since 1.0.0
	 */
	protected static function cpf(string $cpf): bool
	{
		$cpf = preg_replace('/[^0-9]/is', '', $cpf);

		if (strlen($cpf) != 11) {
			return false;
		}

		if (preg_match('/(\d)\1{10}/', $cpf)) {
			return false;
		}

		for ($t = 9; $t < 11; $t++) {
			for ($d = 0, $c = 0; $c < $t; $c++) {
				$d += $cpf[$c] * (($t + 1) - $c);
			}
			$d = ((10 * $d) % 11) % 10;
			if ($cpf[$c] != $d) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Check if a CNPJ is valid.
	 *
	 * @param string $cnpj CNPJ.
	 * @return bool
	 * @since 1.0.0
	 */
	protected static function cnpj(string $cnpj): bool
	{
		$cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);

		if (strlen($cnpj) != 14) {
			return false;
		}

		if (preg_match('/(\d)\1{13}/', $cnpj)) {
			return false;
		}

		for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
			$soma += $cnpj[$i] * $j;
			$j = ($j == 2) ? 9 : $j - 1;
		}

		$resto = $soma % 11;

		if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) {
			return false;
		}

		for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
			$soma += $cnpj[$i] * $j;
			$j = ($j == 2) ? 9 : $j - 1;
		}

		$resto = $soma % 11;

		return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
	}
}
