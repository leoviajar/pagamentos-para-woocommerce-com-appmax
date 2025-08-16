<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Create\PaymentMethod;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Interfaces\PaymentTypeInterface;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;
use AppMax\WooCommerce\Gateway\Api\Validations\CardNumberValidation;
use AppMax\WooCommerce\Gateway\Api\Validations\CvvValidation;
use AppMax\WooCommerce\Gateway\Api\Validations\DocumentValidation;
use AppMax\WooCommerce\Gateway\Api\Validations\ExpirationDateValidation;
use AppMax\WooCommerce\Gateway\Api\Validations\ExpirationMonthValidation;
use AppMax\WooCommerce\Gateway\Api\Validations\ExpirationYearValidation;
use AppMax\WooCommerce\Gateway\Api\Validations\NotEmptyValidation;
use AppMax\WooCommerce\Gateway\Api\Values\InstallmentValue;
use AppMax\WooCommerce\Gateway\Core\Records\Enums\PaymentMethodEnum;

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
class CreateCreditCardPayload extends AbstractPayload implements PaymentTypeInterface
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
		'document_number' => null,
		'name' => null,
		'installment' => null,
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
		$name,
		$number,
		$exp_month,
		$exp_year,
		$cvv,
		$document_number
	) {
		ExpirationDateValidation::validate($exp_month, $exp_year);
		$this->_fields['name'] = Parser::anyToString(NotEmptyValidation::validate($name, 'Nome'));
		$this->_fields['number'] = CardNumberValidation::validate($number);
		$this->_fields['month'] = ExpirationMonthValidation::validate($exp_month);
		$this->_fields['year'] = ExpirationYearValidation::validate($exp_year);
		$this->_fields['cvv'] = CvvValidation::validate($cvv);
		$this->_fields['document_number'] = DocumentValidation::validate($document_number);
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
	 * @since 1.1.0-beta
	 * @return InstallmentValue|null
	 */
	public function getInstallment(): ?InstallmentValue
	{
		return $this->_fields['installment'];
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
	 * @since 1.1.0-beta
	 * @return self
	 */
	public function setInstallment(InstallmentValue $installment)
	{
		$this->_fields['installment'] = $installment;
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
	 * @since 1.1.0-beta Add installments.
	 * @return array
	 */
	public function toArray(): array
	{
		$arr = [
			'name' => $this->getName(),
			'number' => $this->_fields['number'],
			'month' => $this->getMonth(),
			'year' => $this->getYear(),
			'cvv' => $this->getCVV(),
			'document_number' => $this->getDocumentNumber(),
			'installments' => !empty($this->getInstallment()) ? $this->getInstallment()->getInstallment() : 1
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
	 * @since 1.1.0-beta Add installments.
	 * @return array
	 */
	public function toCensoredArray(): array
	{
		$number = $this->_fields['number'];
		$length = \strlen($number);

		return [
			'name' => '***',
			'number' => '**** **** **** '.\substr($number, $length-4, 4),
			'month' => '**',
			'year' => '****',
			'cvv' => '***',
			'document_number' => '***',
			'installments' => !empty($this->getInstallment()) ? $this->getInstallment()->getInstallment() : 1
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
	 * Get payment type name.
	 *
	 * @since 1.1.0-beta
	 * @return string
	 */
	public static function getPaymentMethod(): string
	{
		return PaymentMethodEnum::PAYMENT_METHOD_CREDIT_CARD;
	}

	/**
	 * Import and return the object.
	 *
	 * @param array $body
	 * @since 1.0.0
	 * @since 1.1.0-beta Add installments.
	 * @return CreateCreditCardPayload
	 */
	public static function import(array $data = []): CreateCreditCardPayload
	{
		$payload = new CreateCreditCardPayload(
			$data['name'],
			$data['number'],
			$data['month'],
			$data['year'],
			$data['cvv'],
			$data['document_number']
		);

		if (isset($data['installments'])) {
			$payload->setInstallment(new InstallmentValue(
				isset($data['total']) ? $data['total'] : 0,
				$data['installments']
			));
		}

		if (isset($data['soft_descriptor'])) {
			$payload->setSoftDescriptor($data['soft_descriptor']);
		}

		return $payload;
	}
}
