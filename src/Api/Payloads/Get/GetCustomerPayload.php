<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Get;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\Customer\GetAddressPayload;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;

/**
 * Customer payload.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Payloads\Get
 * @category Payloads
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class GetCustomerPayload extends AbstractPayload
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $_fields = [
		'id' => null,
		'site_id' => null,
		'hash' => null,
		'firstname' => null,
		'lastname' => null,
		'email' => null,
		'telephone' => null,
	];

	/**
	 * Address payload.
	 *
	 * @var GetAddressPayload
	 * @since 1.0.0
	 */
	protected $_address;

	/**
	 * Construct object.
	 *
	 * @param int $id Customer ID.
	 * @param int $siteId Site ID.
	 * @param string $hash Hash.
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct(
		$id,
		$siteId,
		$hash
	) {
		$this->_fields['id'] = Parser::anyToInteger($id);
		$this->_fields['site_id'] = Parser::anyToInteger($siteId);
		$this->_fields['hash'] = Parser::anyToString($hash);
	}

	/**
	 * Get id field.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function getId(): int
	{
		return $this->_fields['id'];
	}

	/**
	 * Get site id field.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function getSiteId(): int
	{
		return $this->_fields['site_id'];
	}

	/**
	 * Get hash field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getHash(): string
	{
		return $this->_fields['hash'];
	}

	/**
	 * Get firstname field.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function getFirstName(): ?string
	{
		return $this->_fields['firstname'];
	}

	/**
	 * Get lastname field.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function getLastName(): ?string
	{
		return $this->_fields['lastname'];
	}

	/**
	 * Get email field.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function getEmail(): ?string
	{
		return $this->_fields['email'];
	}


	/**
	 * Get telephone field.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function getPhone(): ?string
	{
		return empty($this->_fields['telephone']) ? $this->_fields['telephone'] : Parser::formattedPhone($this->_fields['telephone']);
	}

	/**
	 * Get address field.
	 *
	 * @since 1.0.0
	 * @return GetAddressPayload|null
	 */
	public function getAddress(): ?GetAddressPayload
	{
		return $this->_address;
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
		$arr = [
			'id' => $this->getId(),
			'site_id' => $this->getSiteId(),
			'hash' => $this->getHash(),
		];

		if (!empty($this->_fields['firstname'])) {
			$arr['firstname'] = $this->getFirstName();
		}

		if (!empty($this->_fields['lastname'])) {
			$arr['lastname'] = $this->getLastName();
		}

		if (!empty($this->_fields['email'])) {
			$arr['email'] = $this->getEmail();
		}

		if (!empty($this->_fields['telephone'])) {
			$arr['telephone'] = $this->getPhone();
		}

		if (!empty($this->_address)) {
			$arr = array_merge($arr, $this->_address->toArray());
		}

		return $arr;
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
			'id' => $this->getId(),
			'site_id' => $this->getSiteId(),
			'hash' => $this->getHash(),
		];
	}

	/**
	 * Import and return the object.
	 *
	 * @param array $body
	 * @since 1.0.0
	 * @return GetCustomerPayload
	 */
	public static function import(array $data = []): GetCustomerPayload
	{
		$payload = new GetCustomerPayload(
			$data['id'],
			$data['site_id'],
			$data['hash']
		);

		$payload->_fields['firstname'] = !isset($data['firstname']) ? null : Parser::anyToString($data['firstname']);
		$payload->_fields['lastname'] = !isset($data['lastname']) ? null : Parser::anyToString($data['lastname']);
		$payload->_fields['email'] = !isset($data['email']) ? null : Parser::anyToString($data['email']);
		$payload->_fields['telephone'] = !isset($data['telephone']) ? null : Parser::digits($data['telephone']);
		$payload->_address = !isset($data['address_street']) ? null : GetAddressPayload::import($data);

		return $payload;
	}
}
