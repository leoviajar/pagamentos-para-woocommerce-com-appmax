<?php

namespace AppMax\WooCommerce\Gateway\Core\Tasks;

use DateTimeImmutable;
use Exception;
use AppMax\WooCommerce\Gateway\Core\ProductAdmin;
use AppMax\WooCommerce\Gateway\Core\Tasks\Interfaces\RunnableTask;
use AppMax\WooCommerce\Gateway\Plugin;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\CreateProductPayload;
use AppMax\WooCommerce\Gateway\ApiTools;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use WC_Product;
use WC_Product_Simple;
use WC_Product_Variable;

/**
 * Sync product task.
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
class SyncProductTask implements RunnableTask
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

		if ($this->_product->get_status() !== 'publish') {
			Connector::debugger()->debug(\sprintf('O produto %s não está publicado para ser sincronizado.', $this->_product->get_sku()));
			return;
		}

		$type = $this->_product->get_type();

		try {
			switch($type) {
				case 'simple':
					$this->syncSimple($this->_product);
					break;
				case 'variable':
					$this->syncVariable($this->_product);
					break;
				default:
					throw new Exception('Tipo de produto não suportado.');
			}
		} catch (Exception $e) {
			Connector::debugger()->force()->error(sprintf('Não foi possível sincronizar o produto %s: %s', $this->_product->get_sku(), $e->getMessage()));
		}
	}

	/**
	 * Sync a simple product.
	 *
	 * @param WC_Product_Simple $product
	 * @since 0.1.0
	 * @return void
	 */
	protected function syncSimple(WC_Product_Simple $product)
	{
		$this->sync($product, ProductAdmin::prepare_sync_data($product));
	}

	/**
	 * Sync a variable product.
	 *
	 * @param WC_Product_Simple $product
	 * @since 0.1.0
	 * @return void
	 */
	protected function syncVariable(WC_Product_Variable $product)
	{
		$variations = $product->get_children();

		foreach ($variations as $variation_id) {
			$variation = \wc_get_product($variation_id);
			$this->sync($variation, ProductAdmin::prepare_sync_data($product));
		}
	}

	/**
	 * Sync product.
	 *
	 * @param WC_Product $product
	 * @param array $meta
	 * @since 0.1.0
	 * @return void
	 */
	protected function sync(WC_Product $product, array $meta)
	{
		// Skip recent products
		if (!empty($meta['last_sync_at'])) {
			if (strtotime($meta['last_sync_at'])
					>= strtotime($product->get_date_modified()->format('Y-m-d H:i:s'))) {
				Connector::debugger()->debug(sprintf('Produto %s sincronizado recentemente.', $product->get_sku()));
				return;
			}
		}

		$raw = $this->getData($product);

		$payload = new CreateProductPayload(
			$raw['sku'],
			$raw['name'],
			$raw['price'],
		);

		if (!empty($raw['description'])) {
			$payload->setDescription($raw['description']);
		}

		if (!empty($raw['external_id'])) {
			$payload->setExternalId($raw['external_id']);
		}

		if (!empty($raw['height'])) {
			$payload->setHeight($raw['height']);
		}

		if (!empty($raw['length'])) {
			$payload->setLength($raw['length']);
		}

		if (!empty($raw['weight'])) {
			$payload->setWeight($raw['weight']);
		}

		if (!empty($raw['width'])) {
			$payload->setWidth($raw['width']);
		}

		if (!empty($raw['image'])) {
			$payload->setImage($raw['image']);
		}

		try {
			$response = ApiTools::getApi()->product()->update($raw['sku'], $payload);
		} catch(Exception $e) {
			$response = false;
		}

		if ($response === false) {
			try {
				$response = ApiTools::getApi()->product()->create($payload);
			} catch(Exception $e) {
				$response = false;
			}
		}

		if ($response) {
			Connector::debugger()->debug(sprintf('Produto %s sincronizado.', $raw['sku']));
			$product->update_meta_data('appmax_product_last_synced_at', (new DateTimeImmutable('now', \wp_timezone()))->format('Y-m-d H:i:s'));
			$product->save_meta_data();
		} else {
			Connector::debugger()->force()->error(sprintf('Não foi possível sincronizar o produto %s.', $raw['sku']));
		}
	}

	/**
	 * Get product data.
	 *
	 * @param WC_Product $product
	 * @since 0.1.0
	 * @return array
	 */
	protected function getData(WC_Product $product): array
	{
		return [
			'sku' => empty($product->get_sku()) ? "PRODUCT_{$product->get_id()}" : $product->get_sku(),
			'name' => $product->get_name(),
			'price' => \floatval($product->get_price()),
			'description' => \esc_html($product->get_description() ?? ''),
			'external_id' => $product->get_id(),
			'height' => $product->get_height() ?? 0,
			'length' => $product->get_length() ?? 0,
			'weight' => $product->get_weight() ?? 0,
			'width' => $product->get_width() ?? 0,
			'image' => empty($product->get_image_id()) ? null : (\wp_get_attachment_image_src($product->get_image_id(), 'full')[0] ?? null)
		];
	}
}
