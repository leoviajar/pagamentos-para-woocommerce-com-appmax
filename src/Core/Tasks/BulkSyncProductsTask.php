<?php

namespace AppMax\WooCommerce\Gateway\Core\Tasks;

use AppMax\WooCommerce\Gateway\ApiTools;
use AppMax\WooCommerce\Gateway\Core\ProductAdmin;
use AppMax\WooCommerce\Gateway\Core\Tasks\Interfaces\RunnableTask;
use AppMax\WooCommerce\Gateway\Plugin;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Settings\KeyingBucket;
use WC_Product;

/**
 * Bulk sync products task.
 *
 * @package \AppMax\WooCommerce\Gateway
 * @subpackage \AppMax\WooCommerce\Gateway\Core\Tasks
 * @version 0.1.0
 * @since 0.1.0
 * @category Task
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license piggly
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class BulkSyncProductsTask implements RunnableTask
{
	/**
	 * Run task.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function run()
	{
		if (ApiTools::hasCredentials() === false) {
			return;
		}

		/** @var KeyingBucket $settings */
		$settings = Connector::settings()->get('gateway', new KeyingBucket());

		$page = $this->getPage();
		$products = [];

		$args = [
			'status' => 'publish',
			'type' => ['simple', 'variable'],
			'limit' => $settings->get('cron_limit', 10),
			'page' => $page,
			'paginate' => false,
		];

		/** @var WC_Product[] $products */
		$products = \wc_get_products($args);

		foreach ($products as $product) {
			$sync_data = ProductAdmin::prepare_sync_data($product);

			if ($sync_data['sync_status'] === true) {
				(new SyncProductTask($product))->run();
			} else {
				(new UnsyncProductTask($product))->run();
			}
		}

		if (empty($products)) {
			\delete_transient('pagamentos_para_woocommerce_com_appmax_bulk_sync_products_page');
		} else {
			\set_transient('pagamentos_para_woocommerce_com_appmax_bulk_sync_products_page', $page++);
		}
	}

	/**
	 * Get cached page.
	 *
	 * @since 0.1.0
	 * @return integer
	 */
	public function getPage(): int
	{
		$cached_page = \get_transient('pagamentos_para_woocommerce_com_appmax_bulk_sync_products_page');

		if ($cached_page === false) {
			$cached_page = 1;
		}

		return \intval($cached_page);
	}
}
