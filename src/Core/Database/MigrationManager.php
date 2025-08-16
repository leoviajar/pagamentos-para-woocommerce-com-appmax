<?php

namespace AppMax\WooCommerce\Gateway\Core\Database;

use AppMax\WooCommerce\Gateway\Core\Database\Migrations\AbstractMigration;

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
class MigrationManager
{
	/**
	 * Migrations.
	 *
	 * @var AbstractMigration[]
	 * @since 0.2.0
	 */
	protected $_migrations;

	/**
	 * Register a new migration.
	 *
	 * @param AbstractMigration $migration
	 * @since 0.2.0
	 * @return self
	 */
	public function register(AbstractMigration $migration)
	{
		$this->_migrations[$migration->version()] = $migration;
		return $this;
	}

	/**
	 * Run all migrations.
	 *
	 * @since 0.2.0
	 * @return void
	 */
	public function run()
	{
		/** @var AbstractMigration[] $versions */
		$versions = [];

		foreach ($this->_migrations as $migration) {
			$versions[$migration->version()] = $migration;
		}

		\ksort($versions, \SORT_NATURAL);

		foreach ($versions as $migration) {
			$migration->up();
		}
	}
}
