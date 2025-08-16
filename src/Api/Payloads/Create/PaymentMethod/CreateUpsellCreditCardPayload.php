<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Create\PaymentMethod;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Interfaces\PaymentTypeInterface;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;
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
class CreateUpsellCreditCardPayload extends AbstractPayload implements PaymentTypeInterface
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
	 * @param string $upsell_hash
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct(
		$upsell_hash
	) {
		$this->_fields['upsell_hash'] = Parser::anyToString(NotEmptyValidation::validate($upsell_hash, 'Hash de Upsell'));
	}

	/**
	 * Get token field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getHash(): string
	{
		return $this->_fields['upsell_hash'];
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
			'upsell_hash' => $this->getHash(),
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
			'upsell_hash' => '***',
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
	 * @return CreateUpsellCreditCardPayload
	 */
	public static function import(array $data = []): CreateUpsellCreditCardPayload
	{
		$payload = new CreateUpsellCreditCardPayload(
			$data['upsell_hash']
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
