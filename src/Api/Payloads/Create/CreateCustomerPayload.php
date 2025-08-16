<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Create;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\Customer\CreateAddressPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\Customer\CreateProductOfInterestPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\Customer\CreateTrackingPayload;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;
use AppMax\WooCommerce\Gateway\Api\Validations\EmailValidation;
use AppMax\WooCommerce\Gateway\Api\Validations\NotEmptyValidation;

/**
 * Customer payload.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Payloads\Create
 * @category Payloads
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class CreateCustomerPayload extends AbstractPayload
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $_fields = [
		'firstname' => null,
		'lastname' => null,
		'email' => null,
		'telephone' => null,
		'ip' => null,
	];

	/**
	 * Address payload.
	 *
	 * @var CreateAddressPayload
	 * @since 1.0.0
	 */
	protected $_address;

	/**
	 * Tracking payload.
	 *
	 * @var CreateTrackingPayload
	 * @since 1.0.0
	 */
	protected $_tracking;

	/**
	 * Product payload.
	 *
	 * @var CreateProductOfInterestPayload[]
	 * @since 1.0.0
	 */
	protected $_products = [];

	/**
	 * Construct object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct(
		$firstName,
		$lastName,
		$email,
		$phone,
		$ip
	) {
		$this->_fields['firstname'] = Parser::anyToString(NotEmptyValidation::validate($firstName, 'Primeiro Nome'));
		$this->_fields['lastname'] = Parser::anyToString(NotEmptyValidation::validate($lastName, 'Último Nome'));
		$this->_fields['email'] = Parser::anyToString(EmailValidation::validate($email));
		$this->_fields['telephone'] = NotEmptyValidation::validate(Parser::digits($phone), 'Telefone');
		$this->_fields['ip'] = Parser::anyToString(NotEmptyValidation::validate($ip, 'IP de Origem'));
	}

	/**
	 * Apply address.
	 *
	 * @param AddressPayload $address
	 * @since 1.0.0
	 * @return self
	 */
	public function applyAddress(CreateAddressPayload $address)
	{
		$this->_address = $address;
		return $this;
	}

	/**
	 * Apply UTM tracking.
	 *
	 * @param TrackingPayload $tracking
	 * @since 1.0.0
	 * @return self
	 */
	public function applyTracking(CreateTrackingPayload $tracking)
	{
		$this->_tracking = $tracking;
		return $this;
	}

	/**
	 * Get firstname field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getFirstName(): string
	{
		return $this->_fields['firstname'];
	}

	/**
	 * Get lastname field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getLastName(): string
	{
		return $this->_fields['lastname'];
	}

	/**
	 * Get email field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getEmail(): string
	{
		return $this->_fields['email'];
	}


	/**
	 * Get telephone field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getPhone(): string
	{
		return Parser::formattedPhone($this->_fields['telephone']);
	}

	/**
	 * Get ip field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getIp(): string
	{
		return $this->_fields['ip'];
	}

	/**
	 * Get address field.
	 *
	 * @since 1.0.0
	 * @return CreateAddressPayload|null
	 */
	public function getAddress(): ?CreateAddressPayload
	{
		return $this->_address;
	}

	/**
	 * Get tracking field.
	 *
	 * @since 1.0.0
	 * @return CreateTrackingPayload|null
	 */
	public function getTracking(): ?CreateTrackingPayload
	{
		return $this->_tracking;
	}

	/**
	 * Get products of interest field.
	 *
	 * @since 1.0.0
	 * @return CreateProductOfInterestPayload[]
	 */
	public function getProductsOfInterest(): array
	{
		return $this->_products;
	}

	/**
	 * Add a product of interest.
	 *
	 * @param CreateProductOfInterestPayload $product
	 * @return self
	 */
	public function addProductOfInterest(CreateProductOfInterestPayload $product)
	{
		$this->_products[] = $product;
		return $this;
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
			'firstname' => $this->getFirstName(),
			'lastname' => $this->getLastName(),
			'email' => $this->getEmail(),
			'telephone' => $this->getPhone(),
			'ip' => $this->getIp(),
		];

		if (!empty($this->_address)) {
			$arr = array_merge($arr, $this->_address->toArray());
		}

		if (!empty($this->_tracking)) {
			$arr['tracking'] = $this->_tracking->toArray();
		}

		if (!empty($this->_products)) {
			$arr['products'] = \array_map(function ($product) {
				return $product->toArray();
			}, $this->_products);
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
			'firstname' => $this->getFirstName(),
			'lastname' => $this->getLastName(),
			'email' => $this->getEmail(),
		];
	}

	/**
	 * Import and return the object.
	 *
	 * @param array $body
	 * @since 1.0.0
	 * @return CreateCustomerPayload
	 */
	public static function import(array $data = []): CreateCustomerPayload
	{
		$payload = new CreateCustomerPayload(
			$data['firstname'],
			$data['lastname'],
			$data['email'],
			$data['telephone'],
			$data['ip']
		);

		if (isset($data['address'])) {
			$payload->applyAddress(CreateAddressPayload::import($data['address']));
		}

		if (isset($data['tracking'])) {
			$payload->applyTracking(CreateTrackingPayload::import($data['tracking']));
		}

		if (isset($data['products']) && \is_array($data['products'])) {
			foreach ($data['products'] as $product) {
				$payload->addProductOfInterest(CreateProductOfInterestPayload::import($product));
			}
		}

		return $payload;
	}
}
