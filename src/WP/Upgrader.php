<?php

namespace AppMax\WooCommerce\Gateway\WP;

use AppMax\WooCommerce\Gateway\Core\Cron;
use AppMax\WooCommerce\Gateway\Core\Database\MigrationManager;
use AppMax\WooCommerce\Gateway\Core\Database\Migrations\Migration010;
use AppMax\WooCommerce\Gateway\Core\Database\Migrations\Migration020;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Core\Interfaces\Runnable;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Core\Scaffold\Pluggable;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Core\WP;

/**
 * Upgrade plugin.
 *
 * @package \AppMax\WooCommerce\Gateway
 * @subpackage \AppMax\WooCommerce\Gateway\WP
 * @version 0.1.0
 * @since 0.1.0
 * @category WP
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license piggly
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class Upgrader extends Pluggable implements Runnable
{
	/**
	 * Method to run all business logic.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function run()
	{
		if (!WP::is_admin()) {
			return;
		}

		// Current version
		$version = \get_option(\PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_ACTION.'_version', '0');

		// If version greater than plugin version, ignore
		if (\version_compare($version, $this->_plugin->getVersion(), '>=')) {
			return;
		}

		$this->migrate();

		// Recreate cron events
		Cron::destroy();
		Cron::create();

		// New version
		\update_option(
			\PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_ACTION.'_version',
			$this->_plugin->getVersion()
		);
	}

	/**
	 * Migrate database.
	 *
	 * @since 0.2.0
	 * @return void
	 */
	public function migrate()
	{
		$manager = new MigrationManager();
		$manager->register(new Migration010());
		$manager->run();
	}
}
