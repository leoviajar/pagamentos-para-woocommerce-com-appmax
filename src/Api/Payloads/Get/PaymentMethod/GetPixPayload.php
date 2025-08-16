<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Get\PaymentMethod;

use DateTimeImmutable;
use DateTimeZone;
use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Interfaces\PaymentTypeInterface;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;
use AppMax\WooCommerce\Gateway\Core\Records\Enums\PaymentMethodEnum;

/**
 * Payment method payload.
 * Generally used in payment payload.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Payloads\Get\PaymentMethod
 * @category Payloads
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class GetPixPayload extends AbstractPayload implements PaymentTypeInterface
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 0.1.0
	 */
	protected $_fields = [
		'qrcode' => null,
		'emv' => null,
		'creation_date' => null,
		'expiration_date' => null
	];

	/**
	 * Construct object.
	 *
	 * @param string $qrcode
	 * @param string $emv
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct($qrcode, $emv)
	{
		$this->_fields['qrcode'] = Parser::anyToString($qrcode);
		$this->_fields['emv'] = Parser::anyToString($emv);
	}

	/**
	 * Get qrcode field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getQrCode(): string
	{
		return $this->_fields['qrcode'];
	}

	/**
	 * Get emv field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getEmv(): string
	{
		return $this->_fields['emv'];
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
	 * Get creation_date.
	 *
	 * @since 0.1.0
	 * @return DateTimeImmutable|null
	 */
	public function getCreationDate(): ?DateTimeImmutable
	{
		return $this->_fields['creation_date'];
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
			'qrcode' => $this->getQrCode(),
			'emv' => $this->getEmv(),
		];

		if (!\is_null($this->getExpirationDate())) {
			$arr['expiration_date'] = $this->getExpirationDate()->format('Y-m-d H:i:s');
		}

		if (!\is_null($this->getCreationDate())) {
			$arr['creation_date'] = $this->getCreationDate()->format('Y-m-d H:i:s');
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
		return $this->toArray();
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
	 * @return GetPixPayload
	 */
	public static function import(array $data = []): GetPixPayload
	{
		$payload = new GetPixPayload($data['pix_qrcode'], $data['pix_emv']);

		if (!empty($body['pix_creation_date'])) {
			$payload->_fields['creation_date'] = new DateTimeImmutable($data['pix_creation_date'], new DateTimeZone('America/Sao_Paulo'));
		}

		if (!empty($body['pix_expiration_date'])) {
			$payload->_fields['expiration_date'] = new DateTimeImmutable($data['pix_expiration_date'], new DateTimeZone('America/Sao_Paulo'));
		}

		return $payload;
	}
}
