<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Get\Order;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;

/**
 * Company payload.
 * Generally used in order payload.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Payloads\Get\Order
 * @category Payloads
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class GetCompanyPayload extends AbstractPayload
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 0.1.0
	 */
	protected $_fields = [
		'name' => null,
		'cnpj' => null,
		'email' => null,
	];

	/**
	 * Construct object.
	 *
	 * @param string $name
	 * @param string $cnpj
	 * @param string $email
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct($name, $cnpj, $email)
	{
		$this->_fields['name'] = Parser::anyToString($name);
		$this->_fields['cnpj'] = Parser::anyToString($cnpj);
		$this->_fields['email'] = Parser::anyToString($email);
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
	 * Get cnpj field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getCnpj(): string
	{
		return $this->_fields['cnpj'];
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
	 * Get all fields converting payloads
	 * to an array and removing null values.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function toArray(): array
	{
		return [
			'name' => $this->getName(),
			'cnpj' => $this->getCnpj(),
			'email' => $this->getEmail(),
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
		return $this->toArray();
	}

	/**
	 * Import and return the object.
	 *
	 * @param array $body
	 * @since 1.0.0
	 * @return GetCompanyPayload
	 */
	public static function import(array $data = []): GetCompanyPayload
	{
		return  new GetCompanyPayload(
			$data['name'],
			$data['cnpj'],
			$data['email']
		);
	}
}
