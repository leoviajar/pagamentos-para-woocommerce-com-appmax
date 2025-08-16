<?php

namespace AppMax\WooCommerce\Gateway\Core;

use AppMax\WooCommerce\Gateway\ApiTools;
use AppMax\WooCommerce\Gateway\Core\Tasks\SyncProductTask;
use AppMax\WooCommerce\Gateway\Core\Tasks\UnsyncProductTask;
use AppMax\WooCommerce\Gateway\Plugin;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Core\Scaffold\Initiable;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Core\WP;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Settings\KeyingBucket;
use WC_Product;

/**
 * Manages all woocommerce products actions and filters.
 *
 * @package \AppMax\WooCommerce\Gateway
 * @subpackage \AppMax\WooCommerce\Gateway\Core
 * @version 0.1.0
 * @since 0.1.0
 * @category Core
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license piggly
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class ProductAdmin extends Initiable
{
	/**
	 * Startup method with all actions and
	 * filter to run.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function startup()
	{
		WP::add_filter(
			'woocommerce_product_data_tabs',
			$this,
			'add_product_tab'
		);

		WP::add_action(
			'woocommerce_product_data_panels',
			$this,
			'add_product_panel'
		);

		WP::add_action(
			'woocommerce_admin_process_product_object',
			$this,
			'save_product'
		);

		WP::add_action(
			'woocommerce_process_product_meta_simple',
			$this,
			'sync_product'
		);

		WP::add_action(
			'woocommerce_process_product_meta_variable',
			$this,
			'sync_product'
		);
	}

	/**
	 * Add product tab.
	 *
	 * @since 0.1.0
	 * @param array $tabs
	 * @return array
	 */
	public function add_product_tab($tabs)
	{
		$tabs['appmax'] = [
			'label' => 'AppMax',
			'target' => 'appmax_product_data',
			'priority' => 90, // After the inventory tab
			'class' => ['show_if_simple', 'show_if_variable'],
		];

		return $tabs;
	}

	/**
	 * Add product panel.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function add_product_panel()
	{
		global $product_object;
		$value = $product_object->get_meta('appmax_product_sync');

		?>
<div id="appmax_product_data" class="panel woocommerce_options_panel">
	<div class="options_group">
		<?php
		\woocommerce_wp_checkbox([
			'id' => 'appmax_product_sync',
			'label' => 'Sincronizar produto',
			'value' => empty($value) ? 'yes' : $value,
			'description' => 'Habilita a sincronização do produto, a AppMax receberá todas as informações do produto.',
			'desc_tip' => true,
		]);
		?>
	</div>
</div>
<?php
	}

	/**
	 * Save product.
	 *
	 * @since 0.1.0
	 * @param WC_Product $product
	 * @return void
	 */
	public function save_product($product)
	{
		$sync = isset($_POST['appmax_product_sync']) ? 'yes' : 'no';
		$product->update_meta_data('appmax_product_sync', $sync);

		if ($sync === 'yes') {
			$product->update_meta_data('appmax_product_last_synced_at', null);
		} else {
			$product->delete_meta_data('appmax_product_last_synced_at', null);
		}

		$product->save_meta_data();
	}

	/**
	 * Sync product.
	 *
	 * @since 0.1.0
	 * @param int $product_id
	 * @return void
	 */
	public function sync_product($product_id)
	{
		if (Connector::settings()->get('processing', new KeyingBucket())->get('sync_while_editing', true) === false) {
			return;
		}

		if (ApiTools::hasCredentials() === false) {
			return;
		}

		$product = \wc_get_product($product_id);

		if ($product) {
			if ($product->get_status() !== 'publish') {
				Connector::debugger()->debug(\sprintf('O produto %s não está publicado para ser sincronizado.', $product->get_sku()));
				return;
			}

			if ($product->get_meta('appmax_product_sync') === 'yes') {
				(new SyncProductTask($product))->run();
			} else {
				(new UnsyncProductTask($product))->run();
			}
		}
	}

	/**
	 * Get sync meta.
	 *
	 * @param WC_Product $product
	 * @since 0.1.0
	 * @return array
	 */
	public static function prepare_sync_data(WC_Product $product)
	{
		$sync_status  = $product->get_meta('appmax_product_sync');
		$last_sync_at = $product->get_meta('appmax_product_last_synced_at');

		return [
			'sync_status' => empty($sync_status) ? true : ($sync_status === 'yes'),
			'last_sync_at' => empty($last_sync_at) ? null : $last_sync_at,
		];
	}
}
?>