<?php

namespace AppMax\WooCommerce\Gateway\WP;

use AppMax\WooCommerce\Gateway\Core\Cron;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Core\Interfaces\Runnable;

/**
 * Deactivate plugin.
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
class Deactivator implements Runnable
{
	/**
	 * Method to run all business logic.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function run()
	{
		Cron::destroy();
	}
}
