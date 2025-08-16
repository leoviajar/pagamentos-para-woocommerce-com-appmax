<?php

namespace AppMax\WooCommerce\Gateway\Core\Tasks\Interfaces;

/**
 * Interface for a task.
 *
 * @package \AppMax\WooCommerce\Gateway
 * @subpackage \AppMax\WooCommerce\Gateway\Core\Tasks\Interfaces
 * @version 0.1.0
 * @since 0.1.0
 * @category Interface
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license piggly
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
interface RunnableTask
{
	/**
	 * Run the task.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function run();
}
