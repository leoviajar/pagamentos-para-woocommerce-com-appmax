<?php

namespace AppMax\WooCommerce\Gateway\Core\Records\Enums;

/**
 * Payment method enumarations.
 *
 * @since 0.1.0
 * @package AppMax\WooCommerce\Gateway
 * @subpackage AppMax\WooCommerce\Gateway\Core\Records\Enums
 * @category Enums
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class PaymentMethodEnum
{
	/**
	 * Credit card type.
	 *
	 * @var int
	 * @since 0.1.0
	 */
	public const PAYMENT_METHOD_CREDIT_CARD = 'credit_card';

	/**
	 * Transaction type.
	 *
	 * @var int
	 * @since 0.1.0
	 */
	public const PAYMENT_METHOD_BOLETO = 'boleto';

	/**
	 * Pix type.
	 *
	 * @var int
	 * @since 0.1.0
	 */
	public const PAYMENT_METHOD_PIX = 'pix';

	/**
	 * Get method label.
	 *
	 * @param string $method
	 * @since 0.1.0
	 * @return string
	 */
	public static function label(string $method): string
	{
		switch ($method) {
			case static::PAYMENT_METHOD_BOLETO:
				return 'Boleto';
			case static::PAYMENT_METHOD_CREDIT_CARD:
				return 'Cartão de Crédito';
			case static::PAYMENT_METHOD_PIX:
				return 'Pix';
		}

		return 'Desconhecido';
	}

	/**
	 * Check if status is valid.
	 *
	 * @param string $method
	 * @return bool
	 * @since 0.1.0
	 */
	public static function isValid(string $method): bool
	{
		return \in_array($method, self::methods());
	}

	/**
	 * Get all methods.
	 *
	 * @return array
	 * @since 0.1.0
	 */
	public static function methods(): array
	{
		return [
			static::PAYMENT_METHOD_BOLETO,
			static::PAYMENT_METHOD_CREDIT_CARD,
			static::PAYMENT_METHOD_PIX
		];
	}
}
