<?php

namespace AppMax\WooCommerce\Gateway\Core\Helpers;

use AppMax\WooCommerce\Gateway\Core\Tasks\Enums\AppMaxOrderStatusEnum;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\GetOrderPayload;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Settings\KeyingBucket;
use WC_Product_Simple;

/**
 * Order builder.
 *
 * @package \AppMax\WooCommerce\Gateway
 * @subpackage \AppMax\WooCommerce\Gateway\Core\Helpers
 * @version 0.1.0
 * @since 0.1.0
 * @category Helpers
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license piggly
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class OrderBuilder
{
	public static function create(GetOrderPayload $appmax_order)
	{
		$address_street = $appmax_order->getCustomer()->getAddress()->getStreet();
		$address_street_number = $appmax_order->getCustomer()->getAddress()->getNumber();
		$address_street_district = $appmax_order->getCustomer()->getAddress()->getDistrict();

		$address = array(
			"first_name" =>  $appmax_order->getCustomer()->getFirstName(),
			"last_name" => $appmax_order->getCustomer()->getLastName(),
			"email" => $appmax_order->getCustomer()->getEmail(),
			"phone" => $appmax_order->getCustomer()->getPhone(),
			"address_1" => sprintf("%s, %s - %s", $address_street, $address_street_number, $address_street_district),
			"address_2" => $appmax_order->getCustomer()->getAddress()->getComplement(),
			"city" => $appmax_order->getCustomer()->getAddress()->getCity(),
			"state" => $appmax_order->getCustomer()->getAddress()->getState(),
			"postcode" => $appmax_order->getCustomer()->getAddress()->getPostCode(),
			"country" => "BR",
		);

		$order = \wc_create_order();

		$order_note = \sprintf("Pedido processado por Appmax") . PHP_EOL;

		if (empty($appmax_order->getUpsellOrderId())) {
			$order_note .= \sprintf("Pedido #%d", $appmax_order->getId()) . PHP_EOL;
		} else {
			$order_note .= \sprintf("Pedido de Upsell #%d para o pedido #%d", $appmax_order->getId(), $appmax_order->getUpsellOrderId()) . PHP_EOL;
		}

		$order->add_order_note($order_note);

		$order->set_address($address, "billing");
		$order->set_address($address, "shipping");
		$order->set_created_via('Call Center / AppMax');

		// $order->update_meta_data("_billing_cpf" );

		$order->set_total(number_format($appmax_order->getTotal(), wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator()));
		$order->set_billing_phone($appmax_order->getCustomer()->getPhone());

		/** @var KeyingBucket $settings */
		$settings = Connector::settings()->get('gateway', new KeyingBucket());

		$status = $settings->get('waiting_status', 'pending');

		if ($appmax_order->getStatus() === AppMaxOrderStatusEnum::STATUS_APPROVED
				|| $appmax_order->getStatus() === AppMaxOrderStatusEnum::STATUS_INTEGRATED) {
			$status = $settings->get('paid_status', 'processing');
		} elseif ($appmax_order->getStatus() === AppMaxOrderStatusEnum::STATUS_AUTHORIZED) {
			$status = $settings->get('processing_status', 'on-hold');
		}

		$order_note = sprintf("Valor total em Produtos: %s", \wc_price($appmax_order->getTotalProducts())) . PHP_EOL;
		$order_note .= sprintf("Frete: %s", \wc_price($appmax_order->getFreight()->getValue())) . PHP_EOL;
		$order_note .= sprintf("Desconto: %s", \wc_price($appmax_order->getDiscount())) . PHP_EOL;
		$order_note .= sprintf("Juros: %s", \wc_price($appmax_order->getInterest())) . PHP_EOL;
		$order_note .= sprintf("Total do Pedido: %s", \wc_price($appmax_order->getTotal())) . PHP_EOL;

		$order->add_order_note($order_note);
		$order->update_status($status);

		$log_content = sprintf("* Adicionando produtos ao pedido #%d", $order->get_id()) . PHP_EOL;

		foreach ($appmax_order->getBundles() as $bundle) {
			$log_content .= sprintf("* Produtos do pacote %s", $bundle->getName()) . PHP_EOL;

			foreach ($bundle->getProducts() as $product) {
				$product_woo_commerce = static::get_product_by_sku(static::verify_sku_variation($product->getSku()));

				if ($product_woo_commerce) {
					$order->add_product($product_woo_commerce, $product->getQuantity());
					$log_content .= sprintf("* %d x \"%s\" adicionado ao pedido #%d", $product->getQuantity(), $product->getName(), $order->get_id()) . PHP_EOL;
				}

				if (! $product_woo_commerce) {
					$log_content .= sprintf("* ATENÇÃO! Produto %s não foi encontrado.", $product->getName()) . PHP_EOL;
					$log_content .= sprintf("* Cadastrando o produto %s no WooCommerce.", $product->getName()) . PHP_EOL;

					$new_product = new WC_Product_Simple();
					$new_product->set_sku($product->getSku());
					$new_product->set_name($product->getName());
					$new_product->set_description($product->getDescription());
					$new_product->set_price($product->getPrice());
					$new_product->set_regular_price($product->getPrice());
					$new_product->set_status('pending');
					$new_product->save();

					$log_content .= sprintf("* Produto %s salvo com sucesso.", $product->getName()) . PHP_EOL;

					$order->add_product($new_product, $product->getQuantity());
					$log_content .= sprintf("* %d x \"%s\" adicionado ao pedido #%d", $product->getQuantity(), $product->getName(), $order->get_id()) . PHP_EOL;
				}
			}
		}

		$log_content .= sprintf("* Pedido #%d salvo com sucesso.", $order->get_id()) . PHP_EOL;

		// Pega o ID do pedido criado
		$order_raw_data = \wc_get_order($order);
		$order_data = \maybe_serialize($order_raw_data, true);
		$order_id = $order_data['id'];

		// Salva alguns campos personalizados para facilitar a busca por pedidos Appmax, caso necessário no futuro
		\update_post_meta($order_data, '_pagamentos_para_woocommerce_com_appmax_order_id', $appmax_order->getId());
		\update_post_meta($order_id, '_pagamentos_para_woocommerce_com_appmax_upsell_parent_id', $appmax_order->getUpsellOrderId());

		$order->save();
	}

	/**
	 * Get product by sku.
	 *
	 * @param string $sku
	 * @since 0.1.0
	 * @return WC_Product
	 */
	protected static function get_product_by_sku($sku)
	{
		global $wpdb;

		$sql = 'select * from %s where meta_key = \'_sku\' and meta_value = \'%s\' limit 1';
		$stmt = sprintf($sql, $wpdb->postmeta, $sku);
		$result = $wpdb->get_row($stmt, OBJECT);

		return \wc_get_product($result->post_id);
	}

	/**
	 * Get product variation by sku.
	 *
	 * @param string $sku
	 * @since 0.1.0
	 * @return WC_Product
	 */
	protected static function verify_sku_variation($sku)
	{
		if (preg_match("/__/i", $sku)) {
			list($parent, $children) = explode("__", $sku);
			return $children;
		}

		return $sku;
	}
}
