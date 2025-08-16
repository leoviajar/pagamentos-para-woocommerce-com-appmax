<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Create;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;
use AppMax\WooCommerce\Gateway\Api\Validations\CardNumberValidation;
use AppMax\WooCommerce\Gateway\Api\Validations\CvvValidation;
use AppMax\WooCommerce\Gateway\Api\Validations\ExpirationDateValidation;
use AppMax\WooCommerce\Gateway\Api\Validations\ExpirationMonthValidation;
use AppMax\WooCommerce\Gateway\Api\Validations\ExpirationYearValidation;
use AppMax\WooCommerce\Gateway\Api\Validations\NotEmptyValidation;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Interfaces\SensitiveDataArrayable;

/**
 * Tokenization for credit card payload.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Payloads\Create
 * @category Payloads
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class CreateTokenForCreditCardPayload extends AbstractPayload implements SensitiveDataArrayable
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 0.1.0
	 */
	protected $_fields = [
		'number' => null,
		'cvv' => null,
		'month' => null,
		'year' => null,
		'name' => null,
	];

	/**
	 * Construct object.
	 *
	 * @param string $document_number
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct(
		$name,
		$number,
		$exp_month,
		$exp_year,
		$cvv
	) {
		ExpirationDateValidation::validate($exp_month, $exp_year);
		$this->_fields['name'] = Parser::anyToString(NotEmptyValidation::validate($name, 'Nome'));
		$this->_fields['number'] = CardNumberValidation::validate($number);
		$this->_fields['month'] = ExpirationMonthValidation::validate($exp_month);
		$this->_fields['year'] = ExpirationYearValidation::validate($exp_year);
		$this->_fields['cvv'] = CvvValidation::validate($cvv);
	}

	/**
	 * Get name field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getName(): string
	{
		return $this->_fields['name'];
	}

	/**
	 * Get number field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getNumber(): string
	{
		return Parser::formattedCreditCard($this->_fields['number']);
	}

	/**
	 * Get month field.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function getMonth(): int
	{
		return $this->_fields['month'];
	}

	/**
	 * Get year field.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function getYear(): int
	{
		return $this->_fields['year'];
	}

	/**
	 * Get cvv field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getCVV(): string
	{
		return $this->_fields['cvv'];
	}

	/**
	 * Get all fields converting payloads
	 * to an array and removing null values.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function toArray(): array
	{
		return [
			'card' => [
				'name' => $this->getName(),
				'number' => $this->_fields['number'],
				'month' => $this->getMonth(),
				'year' => $this->getYear(),
				'cvv' => $this->getCVV()
			]
		];
	}

	/**
	 * Get all fields converting payloads
	 * to an array and removing null values.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function toCensoredArray(): array
	{
		$number = $this->_fields['number'];
		$length = \strlen($number);

		return [
			'card' => [
				'name' => '***',
				'number' => '**** **** **** '.\substr($number, $length-4, 4),
				'month' => '**',
				'year' => '****',
				'cvv' => '***'
			]
		];
	}

	/**
	 * Import and return the object.
	 *
	 * @param array $body
	 * @since 1.0.0
	 * @return CreateTokenForCreditCardPayload
	 */
	public static function import(array $data = []): CreateTokenForCreditCardPayload
	{
		return new CreateTokenForCreditCardPayload(
			$data['name'],
			$data['number'],
			$data['month'],
			$data['year'],
			$data['cvv']
		);
	}
}
