<?php

namespace AppMax\WooCommerce\Gateway\Core\Database;

/**
 * Database manager.
 *
 * @package \AppMax\WooCommerce\Gateway
 * @subpackage \AppMax\WooCommerce\Gateway\Core\Database
 * @version 0.2.0
 * @since 0.2.0
 * @category Database
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license piggly
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class Manager
{
	/**
	 * Return if column exists on table.
	 *
	 * @param string $table_name
	 * @param string $column_name
	 * @since 0.2.0
	 * @return boolean
	 */
	public static function columnExists(string $table_name, string $column_name): bool
	{
		global $wpdb;
		return $wpdb->get_var("SHOW COLUMNS FROM {$table_name} LIKE '{$column_name}'") == $column_name;
	}

	/**
	 * Return if table exists.
	 *
	 * @param string $table_name
	 * @since 0.2.0
	 * @return boolean
	 */
	public static function tableExists(string $table_name): bool
	{
		global $wpdb;
		return $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name;
	}

	/**
	 * Get table name with schema.
	 *
	 * @param string $schema_name
	 * @since 0.2.0
	 * @return string
	 */
	public static function tableName(string $schema_name): string
	{
		global $wpdb;
		return $wpdb->prefix . \PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_ACTION . '_' . $schema_name;
	}

	/**
	 * Get installed database version.
	 *
	 * @since 0.2.0
	 * @return string
	 */
	public static function installedVersion(): string
	{
		return get_option(\PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_ACTION.'_migrations', '0');
	}

	/**
	 * Update installed database version.
	 *
	 * @param string $version
	 * @since 0.2.0
	 * @return bool
	 */
	public static function updateVersion(string $version): bool
	{
		return \update_option(
			\PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_ACTION.'_migrations',
			$version
		);
	}
}
