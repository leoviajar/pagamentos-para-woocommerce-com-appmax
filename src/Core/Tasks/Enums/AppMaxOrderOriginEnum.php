<?php

namespace AppMax\WooCommerce\Gateway\Core\Tasks\Enums;

/**
 * Order Origin Enum.
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
class AppMaxOrderOriginEnum
{
	/**
	 * Origin from api.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	public const ORIGIN_API = 'API';

	/**
	 * Origin from site.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	public const ORIGIN_SITE = 'Site';

	/**
	 * Origin from recuperation.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	public const ORIGIN_RECUPERATION = 'Recuperação';

	/**
	 * Origin from team producer.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	public const ORIGIN_TEAM_PRODUCER = 'Equipe Parceiro';

	/**
	 * Origin from call center.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	public const ORIGIN_CALL_CENTER = 'Call Center';

	/**
	 * Origin from none.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	public const ORIGIN_NONE = null;

	/**
	 * Call center data.
	 *
	 * @since 0.1.0
	 * @return array
	 */
	public static function callCenter(): array
	{
		return [
			self::ORIGIN_RECUPERATION => self::ORIGIN_RECUPERATION,
			self::ORIGIN_TEAM_PRODUCER => self::ORIGIN_TEAM_PRODUCER,
			self::ORIGIN_CALL_CENTER => self::ORIGIN_CALL_CENTER
		];
	}
}
