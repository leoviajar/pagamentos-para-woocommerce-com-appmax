<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Create\Customer;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;
use AppMax\WooCommerce\Gateway\Api\Validations\NotEmptyValidation;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Interfaces\SensitiveDataArrayable;

/**
 * Address payload.
 * Generally used in customer payload.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Payloads\Create\Customer
 * @category Payloads
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class CreateAddressPayload extends AbstractPayload implements SensitiveDataArrayable
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
		$this->_fields['street'] = Parser::anyToString(NotEmptyValidation::validate($street, 'Endereço'));
		$this->_fields['number'] = Parser::anyToString(Parser::anyOrDefault($number, 'S/N'));
		$this->_fields['district'] = Parser::anyToString(Parser::anyOrDefault($neighborhood, 'Não informado'));
		$this->_fields['city'] = Parser::anyToString(NotEmptyValidation::validate($city, 'Cidade'));
		$this->_fields['state'] = NotEmptyValidation::validate(Parser::stateToISO33662($state), 'Estado');
		$this->_fields['postcode'] = NotEmptyValidation::validate(Parser::digits($postcode), 'CEP');
	}

	/**
	 * Set complement.
	 *
	 * @param string $complement Complement.
	 * @return self
	 * @since 1.0.0
	 */
	public function setComplement($complement)
	{
		$this->_fields['complement'] = Parser::anyToString($complement);
		return $this;
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
	 * @return CreateAddressPayload
	 */
	public static function import(array $data = []): CreateAddressPayload
	{
		$street = $data['street'] ?? 'Sem rua';
		$number = $data['number'] ?? 'S/N';
		$district = $data['district'] ?? 'Não informado';

		if (!isset($data['street']) && isset($data['line_1'])) {
			$address = \explode(', ', $data['line_1'] ?? 'S\N, Sem rua, Não informado');

			$street = empty($address[1]) ? 'Sem rua' : $address[1];
			$number = empty($address[0]) ? 'S/N' : $address[0];
			$district = empty($address[2]) ? 'Não informado' : $address[2];
		}

		$payload = new CreateAddressPayload(
			$street,
			$number,
			$district,
			$data['city'],
			$data['state'],
			$data['post_code'] ?? $data['postcode'] ?? $data['zip_code'] ?? $data['zipcode']
		);

		if (isset($data['complement'], $data['line_2'])) {
			$payload->setComplement($data['complement'] ?? $data['line_2']);
		}

		return $payload;
	}
}
