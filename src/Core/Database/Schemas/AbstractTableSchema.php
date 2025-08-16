<?php

namespace AppMax\WooCommerce\Gateway\Core\Database\Schemas;

/**
 * Abstract table schema.
 *
 * @package \AppMax\WooCommerce\Gateway
 * @subpackage \AppMax\WooCommerce\Gateway\Core\Database\Schemas
 * @version 0.1.0
 * @since 0.1.0
 * @category Database
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license piggly
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
abstract class AbstractTableSchema
{
	/**
	 * Get table schema name.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	abstract public static function getSchemaName(): string;

	/**
	 * Return if table exists.
	 *
	 * @since 0.1.0
	 * @return boolean
	 */
	public static function tableExists(): bool
	{
		global $wpdb;
		$table = static::tableName();
		return $wpdb->get_var("SHOW TABLES LIKE '{$table}'") == $table;
	}

	/**
	 * Get SQL to table name.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public static function tableName(): string
	{
		global $wpdb;
		return $wpdb->prefix . \PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_ACTION . '_'.static::getSchemaName();
	}
}
