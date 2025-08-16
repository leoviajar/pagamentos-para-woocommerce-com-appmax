<?php

namespace AppMax\WooCommerce\Gateway\Api\Utils;

use DateTimeImmutable;
use AppMax\WooCommerce\Gateway\Api\ApiWrapper;

/**
 * Parser functions.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Utils
 * @category Utils
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class Parser
{
	/**
	 * Parse mixed to a valid document string.
	 *
	 * @param mixed $document
	 * @since 1.0.0
	 * @return string
	 */
	public static function document($document): string
	{
		return \str_pad(static::digits($document), 11, '0', STR_PAD_LEFT);
	}

	/**
	 * Get formatted document.
	 *
	 * @param mixed $document
	 * @since 1.0.0
	 * @return string
	 */
	public static function formattedDocument($document): string
	{
		return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", static::document($document));
	}

	/**
	 * Get formatted phone.
	 *
	 * @param mixed $phone
	 * @since 1.0.0
	 * @return string
	 */
	public static function formattedPhone($phone): string
	{
		return preg_replace("/(\d{3})(\d{4,5})(\d{4})/", "(\$1) \$2-\$3", static::digits($phone));
	}

	/**
	 * Get formatted zipcode.
	 *
	 * @param mixed $zipcode
	 * @since 1.0.0
	 * @return string
	 */
	public static function formattedZipCode($zipcode): string
	{
		return preg_replace("/(\d{5})(\d{3})/", "\$1-\$2", static::digits($zipcode));
	}

	/**
	 * Get formatted credit card.
	 *
	 * @param mixed $number
	 * @since 1.0.0
	 * @return string
	 */
	public static function formattedCreditCard($number): string
	{
		return preg_replace("/(\d{4})(\d{4})(\d{4})(\d{4})/", "\$1 \$2 \$3 \$4", static::digits($number));
	}

	/**
	 * Get query string from URL.
	 *
	 * @param string $url URL.
	 * @return array<string,mixed>
	 * @since 1.0.0
	 */
	public static function getQueryString($url): array
	{
		$query = \parse_url(\strval($url), \PHP_URL_QUERY);
		\parse_str($query, $query);
		return $query;
	}

	/**
	 * Convert any value to DateTimeImmutable.
	 *
	 * @param DateTimeImmutable|string $value Any value.
	 * @return DateTimeImmutable|null
	 * @since 1.0.0
	 */
	public static function anyToDateTime($value): ?DateTimeImmutable
	{
		if ($value instanceof DateTimeImmutable) {
			return $value;
		}

		if (\is_string($value)) {
			return new DateTimeImmutable($value, ApiWrapper::getTimezone());
		}

		if (\is_integer($value)) {
			return new DateTimeImmutable('@'.$value, ApiWrapper::getTimezone());
		}

		if (\is_null($value)) {
			return null;
		}

		return new DateTimeImmutable('now', ApiWrapper::getTimezone());
	}

	/**
	 * Remove all non-numeric characters.
	 *
	 * @param string $any Any string.
	 * @return string
	 * @since 1.0.0
	 */
	public static function digits($any): string
	{
		return \preg_replace('/[^0-9]/', '', \strval($any));
	}

	/**
	 * Get any when not empty or default when empty.
	 *
	 * @param mixed $value
	 * @return string
	 * @since 1.0.0
	 */
	public static function anyOrDefault($value, $default = null)
	{
		return empty($value) ? $default : $value;
	}


	/**
	 * Sanitize any to a float.
	 *
	 * @param mixed $value
	 * @return float
	 * @since 1.0.0
	 */
	public static function anyToFloat($value): float
	{
		return \floatval($value);
	}

	/**
	 * Sanitize any to an integer.
	 *
	 * @param mixed $value
	 * @return int
	 * @since 1.0.0
	 */
	public static function anyToInteger($value): int
	{
		return \intval($value);
	}

	/**
	 * Sanitize any to a boolean.
	 *
	 * @param mixed $value
	 * @return bool
	 * @since 1.0.0
	 */
	public static function anyToBoolean($value): bool
	{
		return \boolval($value);
	}

	/**
	 * Sanitize any to an url.
	 *
	 * @param mixed $value
	 * @return string
	 * @since 1.0.0
	 */
	public static function anyToUrl($value): string
	{
		if (!\is_string($value)) {
			$value = \strval($value);
		}

		return \filter_var($value, \FILTER_VALIDATE_URL) === false ? '' : \filter_var($value, \FILTER_SANITIZE_URL);
	}

	/**
	 * Sanitize any to a string.
	 *
	 * @param mixed $value
	 * @return string
	 * @since 1.0.0
	 */
	public static function anyToString($value): string
	{
		if (!\is_string($value)) {
			$value = \strval($value);
		}

		return \htmlspecialchars($value);
	}

	/**
	 * Sanitize any to a lowercase string.
	 *
	 * @param mixed $value
	 * @return string
	 * @since 1.0.0
	 */
	public static function anyToUpperString($value): string
	{
		return \mb_strtoupper(static::anyToString($value));
	}

	/**
	 * Sanitize any to a lowercase string.
	 *
	 * @param mixed $value
	 * @return string
	 * @since 1.0.0
	 */
	public static function anyToLowerString($value): string
	{
		return \mb_strtolower(static::anyToString($value));
	}

	/**
	 * Sanitize any to a cutted string.
	 *
	 * @param mixed $value
	 * @param int $length
	 * @return string
	 * @since 1.0.0
	 */
	public static function anyToCuttedString($value, int $length): string
	{
		$value = static::anyToString($value);

		if (\mb_strlen($value) > $length) {
			$value = \mb_substr($value, 0, $length);
		}

		return $value;
	}

	/**
	 * Convert state name to ISO 3166-2.
	 *
	 * @param string $state State name.
	 * @return string
	 * @since 1.0.0
	 */
	public static function stateToISO33662($state): string
	{
		$original = \strval($state);
		$state = \mb_strtolower($original);

		switch ($state) {
			case 'acre':
				return 'AC';
			case 'alagoas':
				return 'AL';
			case 'amapá':
			case 'amapa':
				return 'AP';
			case 'amazonas':
				return 'AM';
			case 'bahia':
				return 'BA';
			case 'ceará':
			case 'ceara':
				return 'CE';
			case 'distrito federal':
				return 'DF';
			case 'espírito santo':
			case 'espirito santo':
				return 'ES';
			case 'goiás':
			case 'goias':
				return 'GO';
			case 'maranhão':
			case 'maranhao':
				return 'MA';
			case 'mato grosso':
				return 'MT';
			case 'mato grosso do sul':
				return 'MS';
			case 'minas gerais':
				return 'MG';
			case 'pará':
			case 'para':
				return 'PA';
			case 'paraíba':
			case 'paraiba':
				return 'PB';
			case 'paraná':
			case 'parana':
				return 'PR';
			case 'pernambuco':
				return 'PE';
			case 'piauí':
			case 'piaui':
				return 'PI';
			case 'rio de janeiro':
				return 'RJ';
			case 'rio grande do norte':
				return 'RN';
			case 'rio grande do sul':
				return 'RS';
			case 'rondônia':
			case 'rondonia':
				return 'RO';
			case 'roraima':
				return 'RR';
			case 'santa catarina':
				return 'SC';
			case 'são paulo':
			case 'sao paulo':
				return 'SP';
			case 'sergipe':
				return 'SE';
			case 'tocantins':
				return 'TO';
			default:
				return $original;
		}
	}
}
