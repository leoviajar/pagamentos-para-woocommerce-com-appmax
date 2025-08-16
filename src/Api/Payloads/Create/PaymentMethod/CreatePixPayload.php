<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Create\PaymentMethod;

use DateInterval;
use DateTimeImmutable;
use AppMax\WooCommerce\Gateway\Api\ApiWrapper;
use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Interfaces\PaymentTypeInterface;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;
use AppMax\WooCommerce\Gateway\Api\Validations\DocumentValidation;
use AppMax\WooCommerce\Gateway\Core\Records\Enums\PaymentMethodEnum;
use DateTimeZone;

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
class CreatePixPayload extends AbstractPayload implements PaymentTypeInterface
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 0.1.0
	 */
	protected $_fields = [
		'document_number' => null,
		'expiration_date' => null,
	];

	/**
	 * Construct object.
	 *
	 * @param string $document_number
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct($document_number)
	{
		$this->_fields['document_number'] = DocumentValidation::validate($document_number);
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
	 * Get expiration date field.
	 *
	 * @since 1.0.0
	 * @return DateTimeImmutable|null
	 */
	public function getExpirationDate(): ?DateTimeImmutable
	{
		return $this->_fields['expiration_date'];
	}

	/**
	 * Set expiration in seconds.
	 *
	 * @param integer $seconds
	 * @return self
	 * @since 0.1.0
	 */
	public function expiresIn(int $seconds)
	{
		$now = new DateTimeImmutable('now', ApiWrapper::getTimezone());
		$this->_fields['expiration_date'] = $now->add(new DateInterval('PT'.$seconds.'S'));
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
			'document_number' => $this->getDocumentNumber(),
		];

		if (!is_null($this->getExpirationDate())) {
			$arr['expiration_date'] = $this->getExpirationDate()->setTimezone(new DateTimeZone('America/Sao_Paulo'))->format('Y-m-d H:i:s');
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
			'document_number' => '***',
			'expiration_date' => $this->getExpirationDate() ? $this->getExpirationDate()->setTimezone(new DateTimeZone('America/Sao_Paulo'))->format('Y-m-d H:i:s') : null,
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
		return 'pix';
	}

	/**
	 * Get payment type name.
	 *
	 * @since 1.1.0-beta
	 * @return string
	 */
	public static function getPaymentMethod(): string
	{
		return PaymentMethodEnum::PAYMENT_METHOD_PIX;
	}

	/**
	 * Import and return the object.
	 *
	 * @param array $body
	 * @since 1.0.0
	 * @return CreatePixPayload
	 */
	public static function import(array $data = []): CreatePixPayload
	{
		$payload = new CreatePixPayload(
			$data['document_number']
		);

		if (isset($data['expiration_date'])) {
			$payload->_fields['expiration_date'] = (new DateTimeImmutable(
				$data['expiration_date'],
				new DateTimeZone('America/Sao_Paulo')
			))->setTimezone(ApiWrapper::getTimezone());
		}

		return $payload;
	}
}
