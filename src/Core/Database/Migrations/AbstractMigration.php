<?php

namespace AppMax\WooCommerce\Gateway\Core\Database\Migrations;

/**
 * Abstract migration.
 *
 * @package \AppMax\WooCommerce\Gateway
 * @subpackage \AppMax\WooCommerce\Gateway\Core\Database\Migrations
 * @version 0.2.0
 * @since 0.2.0
 * @category Database
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license piggly
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
abstract class AbstractMigration
{
	/**
	 * Run the migrations.
	 *
	 * @since 0.2.0
	 * @return void
	 */
	abstract public function up(): void;

	/**
	 * Reverse the migrations.
	 *
	 * @since 0.2.0
	 * @return void
	 */
	abstract public function down(): void;

	/**
	 * Get migration version in semver format.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	abstract public function version(): string;

	/**
	 * Get installed database version.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public static function installedVersion(): string
	{
		return \get_option(\PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_ACTION.'_migrations', '0');
	}

	/**
	 * Update installed database version.
	 *
	 * @since 0.1.0
	 * @return bool
	 */
	public static function updateVersion(): bool
	{
		return \update_option(
			\PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_ACTION.'_migrations',
			static::version()
		);
	}
}
