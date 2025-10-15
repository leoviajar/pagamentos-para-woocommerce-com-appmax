<?php

namespace AppMax\WooCommerce\Gateway\Core\Helpers;

use AppMax\WooCommerce\Gateway\Api\Payloads\Create\CreateCustomerPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\CreateOrderPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\Customer\CreateAddressPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\Customer\CreateTrackingPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\Order\CreateOrderItemPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\PaymentMethod\CreateCreditCardPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Interfaces\PaymentTypeInterface;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;
use WC_Order;
use WC_Order_Item_Fee;
use WC_Order_Item_Product;

/**
 * Order Extractor.
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
class OrderExtractor
{
	/**
	 * Build customer document.
	 *
	 * @param WC_Order $order
	 * @param string $default
	 * @since 0.1.0
	 * @return string|null
	 */
	public static function solveDocument(WC_Order $order, $default = null): ?string
	{
		return self::documentFromOrder($order, $default);
	}

	/**
	 * Solve tracking code for order.
	 *
	 * @param WC_Order $order
	 * @since 0.1.0
	 * @return string
	 */
	public static function solveTrackingCode(WC_Order $order): string
	{
		$tracking_code = $order->get_meta('_appmax_tracking_code');

		if (!empty($tracking_code)) {
			return $tracking_code;
		}

		$tracking_code = $order->get_meta('_correios_tracking_code');

		if (!empty($tracking_code)) {
			return $tracking_code;
		}

		return \apply_filters('pagamentos_para_woocommerce_com_appmax_get_tracking_code', '', $order);
	}

	/**
	 * Get document from order.
	 *
	 * @param WC_Order $order
	 * @param string $default
	 * @return string|null
	 * @since 0.1.0
	 */
	public static function documentFromOrder(WC_Order $order, $default = null): ?string
	{
		$cpf = $order->get_meta('_billing_cpf', true);
		$cnpj = $order->get_meta('_billing_cnpj', true);

		if (!empty($cpf)) {
			return $cpf;
		}

		if (!empty($cnpj)) {
			return $cnpj;
		}

		return $default;
	}

	/**
	 * Create the order payload.
	 *
	 * @param int $customer_id AppMax customer id.
	 * @param WC_Order|integer $order
	 * @param PaymentTypeInterface $method
	 * @since 0.1.0
	 * @since 1.1.0-beta Apply total when installment is set.
	 * @return CreateOrderPayload
	 */
	public static function order(int $customer_id, $order, PaymentTypeInterface $method): CreateOrderPayload
	{
		$order = $order instanceof WC_Order ? $order : \wc_get_order($order);
		$total = $order->get_subtotal();

		if ($method instanceof CreateCreditCardPayload) {
			if (!empty($method->getInstallment())) {
				$total = $method->getInstallment()->getTotal() - Parser::anyToFloat($order->get_shipping_total()) + Parser::anyToFloat($order->get_total_discount());
			}
		}

		$_order = new CreateOrderPayload(
			$customer_id,
			$total,
			static::ip()
		);

		$calculated_total = round($total - $order->get_total_discount() + $order->get_shipping_total(), 2);
		$order_total = round(Parser::anyToFloat($order->get_total()), 2);
		$extra_discount = $calculated_total - $order_total;

		$_order
			->setDiscountAmount($order->get_total_discount() + $extra_discount)
			->setShippingCost($order->get_shipping_total())
			->setFreightType($order->get_shipping_method());

		$products = static::orderItems($order);

		foreach ($products as $product) {
			$_order->addItem($product);
		}

		return $_order;
	}

	/**
	 * Get order items from order.
	 *
	 * @param WC_Order $order
	 * @since 0.1.0
	 * @return array
	 */
	public static function orderItems(WC_Order $order): array
	{
		$items = $order->get_items();
		$collection = [];

		if (\count($items) === 0) {
			return $collection;
		}

		foreach ($items as $item) {
			if ($item instanceof WC_Order_Item_Product) {
				$_item = $item->get_data();
				$product = $item->get_product();
				$quantity = \intval($_item['quantity']);
				$name = \method_exists($product, 'get_name') ? $product->get_name() : \sprintf('PRODUCT_%s', $product->get_id());
				$sku = \sprintf('PRODUCT_%s', \strval($product->get_id()));

				if (\method_exists($product, 'get_sku')) {
					if (!empty($product->get_sku())) {
						$sku = $product->get_sku();
					}
				}

				$_item = new CreateOrderItemPayload(
					$sku,
					$name,
					$quantity,
				);

				if ($product->is_virtual()) {
					$_item->digitalProduct();
				}

				$collection[] = $_item;
			}
		}

		return $collection;
	}

	/**
	 * Get order items from order.
	 *
	 * @param WC_Order $order
	 * @since 0.1.0
	 * @return float
	 */
	public static function orderDiscountFees(WC_Order $order): float
	{
		$items = $order->get_items();

		if (\count($items) === 0) {
			return 0;
		}

		$fees = 0;

		foreach ($items as $item) {
			if ($item instanceof WC_Order_Item_Fee) {
				$curr_total = \floatval($item->get_total());

				if ($curr_total < 0) {
					$fees += $curr_total*-1;
				}
			}
		}

		return $fees;
	}

	/**
	 * Get customer data.
	 *
	 * @param WC_Order $order
	 * @param string $document_number
	 * @param string $document_type
	 * @return CreateCustomerPayload
	 */
	public static function customer(WC_Order $order): CreateCustomerPayload
	{
		$name = static::solveName($order);

		$customer = new CreateCustomerPayload(
			$name['first_name'],
			$name['last_name'],
			$order->get_billing_email(),
			static::solvePhone($order),
			static::ip()
		);

		$customer
			->applyAddress(static::address($order));

		$tracking = static::tracking();

		if ($tracking) {
			$customer->applyTracking($tracking);
		}

		return $customer;
	}

	/**
	 * Get address data from order.
	 *
	 * @param WC_Order $order
	 * @since 0.1.0
	 * @return CreateAddressPayload
	 */
	public static function address(WC_Order $order): CreateAddressPayload
	{
		// Acessa os campos extras usando get_meta() com as chaves COM o underline,
		$number = $order->get_meta('_billing_number', true) ?? 'S/N';
		$neighborhood = $order->get_meta('_billing_neighborhood', true) ?? 'Não informado';

		$address = new CreateAddressPayload(
			$order->get_billing_address_1(),
			$number,
			$neighborhood,
			$order->get_billing_city(),
			$order->get_billing_state(),
			$order->get_billing_postcode(),
		);

		if (!empty($order->get_billing_address_2())) {
			$address->setComplement($order->get_billing_address_2());
		}

		return $address;
	}

	/**
	 * Get UTM tracking data.
	 *
	 * @since 0.1.0
	 * @return CreateTrackingPayload|null
	 */
	public static function tracking(): ?CreateTrackingPayload
	{
		$utm_source = \sanitize_text_field(\wp_unslash($_GET['utm_source'] ?? ''));
		$utm_medium = \sanitize_text_field(\wp_unslash($_GET['utm_medium'] ?? ''));
		$utm_campaign = \sanitize_text_field(\wp_unslash($_GET['utm_campaign'] ?? ''));
		$utm_term = \sanitize_text_field(\wp_unslash($_GET['utm_term'] ?? ''));
		$utm_content = \sanitize_text_field(\wp_unslash($_GET['utm_content'] ?? ''));

		if (empty($utm_source)) {
			return null;
		}

		$tracking = new CreateTrackingPayload();
		$tracking->setSource($utm_source);

		if (!empty($utm_medium)) {
			$tracking->setMedium($utm_medium);
		}

		if (!empty($utm_campaign)) {
			$tracking->setCampaign($utm_campaign);
		}

		if (!empty($utm_term)) {
			$tracking->setTerm($utm_term);
		}

		if (!empty($utm_content)) {
			$tracking->setContent($utm_content);
		}

		return $tracking;
	}

	/**
	 * Build customer name.
	 *
	 * @param WC_Order $order
	 * @param string $default
	 * @since 0.1.0
	 * @return array|null
	 */
	public static function solveName(WC_Order $order): array
	{
		$name = $order->get_formatted_billing_full_name();
		$name = explode(' ', $name, 2);

		if (empty($name[0])) {
			return [
				'first_name' => null,
				'last_name' => null
			];
		}

		return [
			'first_name' => $name[0],
			'last_name' => $name[1] ?? 'Sobrenome'
		];
	}

	/**
	 * Build customer phone.
	 *
	 * @param WC_Order $order
	 * @param string $default
	 * @since 0.1.0
	 * @return string|null
	 */
	public static function solvePhone(WC_Order $order, $default = null): ?string
	{
		$phone = $order->get_meta('_billing_cellphone', true);

		if (!empty($phone)) {
			return $phone;
		}

		$phone = $order->get_meta('billing_cellphone', true);

		if (!empty($phone)) {
			return $phone;
		}

		return $order->get_billing_phone();
	}

	/**
	 * Get current IP.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function ip(): string
	{
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			return (string) \rest_is_ip_address(\trim(\current(\preg_split('/,/', \sanitize_text_field(\wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']))))));
		}

		if (isset($_SERVER['REMOTE_ADDR'])) {
			return \sanitize_text_field(\wp_unslash($_SERVER['REMOTE_ADDR']));
		}

		if (isset($_SERVER['HTTP_CLIENT_IP'])) {
			return $_SERVER['HTTP_CLIENT_IP'];
		}

		if (isset($_SERVER['HTTP_X_REAL_IP'])) {
			return \sanitize_text_field(\wp_unslash($_SERVER['HTTP_X_REAL_IP']));
		}

		return '127.0.0.1';
	}
}
