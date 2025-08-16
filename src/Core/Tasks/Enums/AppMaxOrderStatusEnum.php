<?php

namespace AppMax\WooCommerce\Gateway\Core\Tasks\Enums;

/**
 * Order status Enum.
 *
 * @package \AppMax\WooCommerce\Gateway
 * @subpackage \AppMax\WooCommerce\Gateway\Core\Tasks\Enums
 * @version 0.1.0
 * @since 0.1.0
 * @category Interface
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license piggly
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class AppMaxOrderStatusEnum
{
	/**
	 * Status authorized.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	public const STATUS_PENDING = 'pendente';

	/**
	 * Status authorized.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	public const STATUS_AUTHORIZED = 'autorizado';

	/**
	 * Status approved.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	public const STATUS_APPROVED = 'aprovado';

	/**
	 * Status integrated.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	public const STATUS_INTEGRATED = 'integrado';

	/**
	 * Status cancelled.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	public const STATUS_CANCELLED = 'cancelado';

	/**
	 * Status refunded.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	public const STATUS_REFUNDED = 'estornado';
}
