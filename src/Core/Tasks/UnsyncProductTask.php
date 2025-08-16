<?php

namespace AppMax\WooCommerce\Gateway\Core\Tasks;

use AppMax\WooCommerce\Gateway\ApiTools;
use Exception;
use AppMax\WooCommerce\Gateway\Core\ProductAdmin;
use AppMax\WooCommerce\Gateway\Core\Tasks\Interfaces\RunnableTask;
use AppMax\WooCommerce\Gateway\Plugin;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use WC_Product;
use WC_Product_Simple;
use WC_Product_Variable;

/**
 * Unsync product task.
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
class UnsyncProductTask implements RunnableTask
{
	/**
	 * WooCommerce product.
	 *
	 * @var WC_Product
	 * @since 0.1.0
	 */
	protected $_product;

	/**
	 * Constructor.
	 *
	 * @param WC_Product|int $product
	 * @since 0.1.0
	 * @return void
	 */
	public function __construct($product)
	{
		$this->_product = $product instanceof WC_Product
			? $product
			: \wc_get_product($product);
	}

	/**
	 * Run the task.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function run()
	{
		if (ApiTools::hasCredentials() === false) {
			return;
		}

		$type = $this->_product->get_type();

		try {
			switch($type) {
				case 'simple':
					$this->unsyncSimple($this->_product);
					break;
				case 'variable':
					$this->unsyncVariable($this->_product);
					break;
				default:
					throw new Exception('Tipo de produto não suportado.');
			}
		} catch (Exception $e) {
			Connector::debugger()->force()->error(sprintf('Não foi possível interromper a sincronização do produto %s: %s', $this->_product->get_sku(), $e->getMessage()));
		}
	}

	/**
	 * Sync a simple product.
	 *
	 * @param WC_Product_Simple $product
	 * @since 0.1.0
	 * @return void
	 */
	protected function unsyncSimple(WC_Product_Simple $product)
	{
		$this->unsync($product, ProductAdmin::prepare_sync_data($product));
	}

	/**
	 * Sync a variable product.
	 *
	 * @param WC_Product_Simple $product
	 * @since 0.1.0
	 * @return void
	 */
	protected function unsyncVariable(WC_Product_Variable $product)
	{
		$variations = $product->get_children();

		foreach ($variations as $variation_id) {
			$variation = \wc_get_product($variation_id);
			$this->unsync($variation, ProductAdmin::prepare_sync_data($product));
		}
	}

	/**
	 * Unsync product.
	 *
	 * @param WC_Product $product
	 * @param array $meta
	 * @since 0.1.0
	 * @return void
	 */
	protected function unsync(WC_Product $product, array $meta)
	{
		if (empty($meta['last_sync_at'])) {
			return;
		}

		try {
			$sku = empty($product->get_sku()) ? "PRODUCT_{$product->get_id()}" : $product->get_sku();
			$response = ApiTools::getApi()->product()->delete($product->get_sku());

			if ($response) {
				Connector::debugger()->debug(sprintf('Produto %s desincronizado com sucesso.', $sku));
				$product->delete_meta_data('appmax_product_last_synced_at');
				$product->save_meta_data();
			} else {
				Connector::debugger()->force()->error(sprintf('Não foi possível interromper a sincronização do produto %s.', $sku));
			}
		} catch (Exception $e) {
			Connector::debugger()->force()->error(sprintf('Não foi possível sincronizar o produto %s: %s', $product->get_sku(), $e->getMessage()));
		}
	}
}
