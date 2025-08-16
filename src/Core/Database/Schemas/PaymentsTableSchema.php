<?php

namespace AppMax\WooCommerce\Gateway\Core\Database\Schemas;

/**
 * Payments table schema.
 *
 * @package \AppMax\WooCommerce\Gateway
 * @subpackage \AppMax\WooCommerce\Gateway\Core\Database
 * @version 0.1.0
 * @since 0.1.0
 * @category Database
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license piggly
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class PaymentsTableSchema extends AbstractTableSchema
{
	/**
	 * Get table schema name.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public static function getSchemaName(): string
	{
		return 'payments';
	}
}
