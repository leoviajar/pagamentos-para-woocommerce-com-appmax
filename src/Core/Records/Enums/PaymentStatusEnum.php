<?php

namespace AppMax\WooCommerce\Gateway\Core\Records\Enums;

/**
 * Payment status enumarations.
 *
 * @since 0.1.0
 * @package AppMax\WooCommerce\Gateway
 * @subpackage AppMax\WooCommerce\Gateway\Core\Records\Enums
 * @category Enums
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class PaymentStatusEnum
{
	/**
	 * Created status.
	 *
	 * @var int
	 * @since 0.1.0
	 */
	public const STATUS_CREATED = 0;

	/**
	 * Pending status.
	 *
	 * @var int
	 * @since 0.1.0
	 */
	public const STATUS_PENDING = 1;

	/**
	 * In analysis status.
	 *
	 * @var int
	 * @since 0.1.0
	 */
	public const STATUS_IN_ANALYSIS = 4;

	/**
	 * Approved status.
	 *
	 * @var int
	 * @since 0.1.0
	 */
	public const STATUS_APPROVED = 2;

	/**
	 * Cancelled status.
	 *
	 * @var int
	 * @since 0.1.0
	 */
	public const STATUS_CANCELLED = 3;

	/**
	 * Pending integration status.
	 *
	 * @var int
	 * @since 0.1.0
	 */
	public const STATUS_PENDING_INTEGRATION = 6;

	/**
	 * Integrated status.
	 *
	 * @var int
	 * @since 0.1.0
	 */
	public const STATUS_INTEGRATED = 5;

	/**
	 * Refunded status.
	 *
	 * @var int
	 * @since 0.1.0
	 */
	public const STATUS_REFUNDED = 7;

	/**
	 * Chargeback lost status.
	 *
	 * @var int
	 * @since 0.1.0
	 */
	public const STATUS_CHARGEBACK_LOST = 8;

	/**
	 * Chargeback waiting status.
	 *
	 * @var int
	 * @since 0.1.0
	 */
	public const STATUS_CHARGEBACK_WAITING = 9;

	/**
	 * Chargeback win status.
	 *
	 * @var int
	 * @since 0.1.0
	 */
	public const STATUS_CHARGEBACK_WIN = 10;

	/**
	 * Expired status.
	 *
	 * @var int
	 * @since 0.1.0
	 */
	public const STATUS_EXPIRED = 11;

	/**
	 * Get status label.
	 *
	 * @param integer $status
	 * @since 0.1.0
	 * @return string
	 */
	public static function label(int $status): string
	{
		switch ($status) {
			case static::STATUS_CANCELLED:
				return 'Cancelado';
			case static::STATUS_CREATED:
				return 'Criado';
			case static::STATUS_APPROVED:
				return 'Aprovado';
			case static::STATUS_EXPIRED:
				return 'Expirado';
			case static::STATUS_INTEGRATED:
				return 'Integrado';
			case static::STATUS_PENDING_INTEGRATION:
				return 'Pendente de Integração';
			case static::STATUS_REFUNDED:
				return 'Reembolsado';
			case static::STATUS_CHARGEBACK_LOST:
				return 'Chargeback Perdido';
			case static::STATUS_CHARGEBACK_WIN:
				return 'Chargeback Ganho';
			case static::STATUS_CHARGEBACK_WAITING:
				return 'Chargeback em Tratativa';
			case static::STATUS_IN_ANALYSIS:
				return 'Análise Antifraude';
			case static::STATUS_PENDING:
				return 'Pendente';
		}

		return 'Desconhecido';
	}

	/**
	 * Check if can update from $from to $to.
	 *
	 * @param integer $from
	 * @param integer $to
	 * @since 0.1.0
	 * @return boolean
	 */
	public static function canUpdateTo(int $from, int $to): bool
	{
		switch ($from) {
			case static::STATUS_CREATED:
				return $to === static::STATUS_PENDING
							|| $to === static::STATUS_CANCELLED
							|| $to === static::STATUS_EXPIRED;
			case static::STATUS_PENDING:
				return $to === static::STATUS_APPROVED
							|| $to === static::STATUS_INTEGRATED
							|| $to === static::STATUS_CANCELLED
							|| $to === static::STATUS_EXPIRED
							|| $to === static::STATUS_IN_ANALYSIS
							|| $to === static::STATUS_CHARGEBACK_WAITING;
			case static::STATUS_IN_ANALYSIS:
				return $to === static::STATUS_APPROVED;
			case static::STATUS_PENDING_INTEGRATION:
				return $to === static::STATUS_INTEGRATED;
			case static::STATUS_APPROVED:
			case static::STATUS_INTEGRATED:
				return $to === static::STATUS_REFUNDED || $to === static::STATUS_CHARGEBACK_WAITING;
			case static::STATUS_CHARGEBACK_WAITING:
				return $to === static::STATUS_CHARGEBACK_LOST || $to === static::STATUS_CHARGEBACK_WIN;
			case static::STATUS_CHARGEBACK_WIN:
			case static::STATUS_CHARGEBACK_LOST:
			case static::STATUS_REFUNDED:
			case static::STATUS_CANCELLED:
			case static::STATUS_EXPIRED:
				return false;
		}

		return false;
	}

	/**
	 * Check if status is valid.
	 *
	 * @param string $status
	 * @return bool
	 * @since 0.1.0
	 */
	public static function isValid(string $status): bool
	{
		return \in_array($status, self::statuses());
	}

	/**
	 * Get all statuses.
	 *
	 * @return array
	 * @since 0.1.0
	 */
	public static function statuses(): array
	{
		return [
			static::STATUS_CANCELLED,
			static::STATUS_CREATED,
			static::STATUS_APPROVED,
			static::STATUS_EXPIRED,
			static::STATUS_INTEGRATED,
			static::STATUS_PENDING_INTEGRATION,
			static::STATUS_REFUNDED,
			static::STATUS_CHARGEBACK_LOST,
			static::STATUS_CHARGEBACK_WIN,
			static::STATUS_CHARGEBACK_WAITING,
			static::STATUS_IN_ANALYSIS,
			static::STATUS_PENDING
		];
	}
}
