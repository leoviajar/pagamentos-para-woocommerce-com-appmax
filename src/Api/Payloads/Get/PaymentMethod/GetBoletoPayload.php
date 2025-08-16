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
class GetBoletoPayload extends AbstractPayload implements PaymentTypeInterface
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 0.1.0
	 */
	protected $_fields = [
		'pdf' => null,
		'digitable_line' => null,
		'due_date' => null,
	];

	/**
	 * Construct object.
	 *
	 * @param string $pdf
	 * @param string $digitable_line
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct($pdf, $digitable_line)
	{
		$this->_fields['pdf'] = Parser::anyToString($pdf);
		$this->_fields['digitable_line'] = Parser::anyToString($digitable_line);
	}

	/**
	 * Get pdf field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getPdf(): string
	{
		return $this->_fields['pdf'];
	}

	/**
	 * Get digitable line field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getDigitableLine(): string
	{
		return $this->_fields['digitable_line'];
	}

	/**
	 * Get due date field.
	 *
	 * @since 1.0.0
	 * @return DateTimeImmutable|null
	 */
	public function getDueDate(): ?DateTimeImmutable
	{
		return $this->_fields['due_date'];
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
			'pdf' => $this->getPdf(),
			'digitable_line' => $this->getDigitableLine()
		];

		if (!\is_null($this->getDueDate())) {
			$arr['due_date'] = $this->getDueDate()->format('Y-m-d H:i:s');
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
		return 'Boleto';
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
	 * @return GetBoletoPayload
	 */
	public static function import(array $data = []): GetBoletoPayload
	{
		$payload = new GetBoletoPayload($data['pdf'], $data['digitable_line']);

		if (!empty($body['due_date'])) {
			$payload->_fields['due_date'] = new DateTimeImmutable($data['due_date'], new DateTimeZone('America/Sao_Paulo'));
		}

		return $payload;
	}
}
