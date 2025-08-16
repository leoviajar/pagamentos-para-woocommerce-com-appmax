<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Get\Customer;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Interfaces\SensitiveDataArrayable;

/**
 * Address payload.
 * Generally used in customer payload.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Payloads\Get\Customer
 * @category Payloads
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class GetAddressPayload extends AbstractPayload implements SensitiveDataArrayable
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $_fields = [
		'postcode' => null,
		'street' => null,
		'number' => null,
		'complement' => '',
		'district' => null,
		'city' => null,
		'state' => null,
	];

	/**
	 * Constructor.
	 *
	 * @param string $street Street name.
	 * @param string $number Number.
	 * @param string $district Neighborhood.
	 * @param string $city City.
	 * @param string $state State.
	 * @param string $postcode Zipcode.
	 * @since 1.0.0
	 */
	public function __construct(
		$street,
		$number,
		$neighborhood,
		$city,
		$state,
		$postcode
	) {
		$this->_fields['street'] = Parser::anyToString($street);
		$this->_fields['number'] = Parser::anyToString(Parser::anyOrDefault($number, 'S/N'));
		$this->_fields['district'] = Parser::anyToString(Parser::anyOrDefault($neighborhood, 'Não informado'));
		$this->_fields['city'] = Parser::anyToString($city);
		$this->_fields['state'] = Parser::stateToISO33662($state);
		$this->_fields['postcode'] = Parser::digits($postcode);
	}

	/**
	 * Get street name.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getStreet(): string
	{
		return $this->_fields['street'];
	}

	/**
	 * Get number.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getNumber(): string
	{
		return $this->_fields['number'];
	}

	/**
	 * Get complement.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getComplement(): string
	{
		return $this->_fields['complement'];
	}

	/**
	 * Get district.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getDistrict(): string
	{
		return $this->_fields['district'];
	}

	/**
	 * Get city.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getCity(): string
	{
		return $this->_fields['city'];
	}

	/**
	 * Get state.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getState(): string
	{
		return $this->_fields['state'];
	}

	/**
	 * Get postcode.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getPostCode(): string
	{
		return Parser::formattedZipCode($this->_fields['postcode']);
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
			'postcode' => $this->getPostCode(),
			'address_street' => $this->getStreet(),
			'address_street_number' => $this->getNumber(),
			'address_street_complement' => $this->getComplement(),
			'address_street_district' => $this->getDistrict(),
			'address_city' => $this->getCity(),
			'address_state' => $this->getState(),
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
		return [
			'postcode' => $this->getPostCode(),
			'address_city' => $this->getCity(),
			'address_state' => $this->getState(),
		];
	}

	/**
	 * Import and return the object.
	 *
	 * @param array $body
	 * @since 1.0.0
	 * @return GetAddressPayload
	 */
	public static function import(array $data = []): GetAddressPayload
	{
		$payload = new GetAddressPayload(
			$data['address_street'] ?? 'S/R',
			$data['address_street_number'] ?? 'S/N',
			$data['address_street_district'] ?? 'Não informado',
			$data['address_city'] ?? '',
			$data['address_state'] ?? '',
			$data['postcode'] ?? ''
		);

		if (isset($data['address_street_complement'])) {
			$payload->_fields['complement'] = $data['address_street_complement'];
		}

		return $payload;
	}
}
