<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Create\PaymentMethod;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Interfaces\PaymentTypeInterface;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;
use AppMax\WooCommerce\Gateway\Api\Validations\DocumentValidation;
use AppMax\WooCommerce\Gateway\Api\Validations\NotEmptyValidation;

/**
 * Payment method payload.
 * Generally used in payment payload.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Payloads\Create\PaymentMethod
 * @category Payloads
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class CreateTokenizedCreditCardPayload extends AbstractPayload implements PaymentTypeInterface
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 0.1.0
	 */
	protected $_fields = [
		'token' => null,
		'document_number' => null,
		'installments' => 1,
		'soft_descriptor' => null
	];

	/**
	 * Construct object.
	 *
	 * @param string $document_number
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct(
		$token,
		$document_number
	) {
		$this->_fields['token'] = Parser::anyToString(NotEmptyValidation::validate($token, 'Token'));
		$this->_fields['document_number'] = DocumentValidation::validate($document_number);
	}

	/**
	 * Get token field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getToken(): string
	{
		return $this->_fields['token'];
	}

	/**
	 * Get document number field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getDocumentNumber(): string
	{
		return Parser::formattedDocument($this->_fields['document_number']);
	}

	/**
	 * Get installments field.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function getInstallments(): int
	{
		return $this->_fields['installments'];
	}

	/**
	 * Get soft_descriptor field.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function getSoftDescriptor(): ?string
	{
		return $this->_fields['soft_descriptor'];
	}

	/**
	 * Change installments field.
	 *
	 * @param mixed $number
	 * @since 1.0.0
	 * @return self
	 */
	public function setInstallments($installments)
	{
		$this->_fields['installments'] = Parser::anyToInteger($installments);
		return $this;
	}

	/**
	 * Change soft_descriptor field.
	 *
	 * @param mixed $soft_descriptor
	 * @since 1.0.0
	 * @return self
	 */
	public function setSoftDescriptor($soft_descriptor)
	{
		$this->_fields['soft_descriptor'] = Parser::anyToString($soft_descriptor);
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
			'token' => $this->getToken(),
			'document_number' => $this->getDocumentNumber(),
			'installments' => $this->getInstallments()
		];

		if (!empty($this->getSoftDescriptor())) {
			$arr['soft_descriptor'] = $this->getSoftDescriptor();
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
			'token' => '***',
			'document_number' => '***',
			'installments' => $this->getInstallments()
		];
	}

	/**
	 * Get payment type name.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function getPaymentType(): string
	{
		return 'CreditCard';
	}

	/**
	 * Import and return the object.
	 *
	 * @param array $body
	 * @since 1.0.0
	 * @return CreateTokenizedCreditCardPayload
	 */
	public static function import(array $data = []): CreateTokenizedCreditCardPayload
	{
		$payload = new CreateTokenizedCreditCardPayload(
			$data['token'],
			$data['document_number']
		);

		if (isset($data['installments'])) {
			$payload->setInstallments($data['installments']);
		}

		if (isset($data['soft_descriptor'])) {
			$payload->setSoftDescriptor($data['soft_descriptor']);
		}

		return $payload;
	}
}
