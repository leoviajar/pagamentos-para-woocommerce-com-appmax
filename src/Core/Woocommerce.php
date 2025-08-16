<?php

namespace AppMax\WooCommerce\Gateway\Core;

use Exception;
use AppMax\WooCommerce\Gateway\Core\Gateway\BoletoGateway;
use AppMax\WooCommerce\Gateway\Core\Gateway\CreditCardGateway;
use AppMax\WooCommerce\Gateway\Core\Gateway\PixGateway;
use AppMax\WooCommerce\Gateway\Core\Records\Enums\PaymentMethodEnum;
use AppMax\WooCommerce\Gateway\Core\Repositories\PaymentsRepo;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Core\Scaffold\Initiable;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Core\WP;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Settings\KeyingBucket;
use WC_Order;

/**
 * Manages all woocommerce actions and filters.
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
class Woocommerce extends Initiable
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
			'woocommerce_payment_gateways',
			$this,
			'add_gateway'
		);

		WP::add_filter(
			'woocommerce_cancel_unpaid_order',
			$this,
			'unpaid_orders',
			99,
			2
		);

		/** @var KeyingBucket $settings */
		$settings = Connector::settings()->get('gateway', new KeyingBucket());

		$processing_actions = [
			'woocommerce_order_status_'.$settings->get('paid_status', 'processing'),
			'woocommerce_order_status_completed',
			'woocommerce_payment_complete'
		];

		foreach ($processing_actions as $actions) {
			WP::add_action(
				$actions,
				$this,
				'payment_complete'
			);
		}

		WP::add_action(
			'woocommerce_order_status_cancelled',
			$this,
			'payment_cancelled'
		);

		WP::add_action(
			'before_delete_post',
			$this,
			'order_deleted'
		);

		WP::add_action(
			'manage_shop_order_posts_custom_column',
			$this,
			'transaction_column',
			20,
			2
		);
	}

	/**
	 * Add column to Woocommerce Orders.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function transaction_column($column, $post_id)
	{
		if ($column !== 'order_status') {
			return;
		}

		$order = \wc_get_order($post_id);

		if (empty($order) || empty($order->get_meta('_pagamentos_para_woocommerce_com_appmax_type_label', true))) {
			return;
		}

		\printf('<mark class="order-status" style="margin-left: 8px; color: blue; background: #e6e6ff;"><span>%s</span></mark>', $order->get_meta('_pagamentos_para_woocommerce_com_appmax_type_label', true));
	}

	/**
	 * Add gateway to Woocommerce.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function add_gateway(array $gateways)
	{
		$hidden = Connector::settings()->get('gateway')->get('hidden_mode', false);

		if ($hidden === true && \current_user_can('manage_woocommerce') === false) {
			return $gateways;
		}

		array_push($gateways, BoletoGateway::class);
		array_push($gateways, CreditCardGateway::class);
		array_push($gateways, PixGateway::class);
		return $gateways;
	}

	/**
	 * Update pix to paid status when order is complete.
	 *
	 * @param integer $order_id
	 * @since 0.1.0
	 * @return void
	 */
	public function payment_complete($order_id)
	{
		$order = \wc_get_order($order_id);

		if (empty($order) || empty($order->get_meta('_pagamentos_para_woocommerce_com_appmax_paymentid', true))) {
			return;
		}

		$payment = PaymentsRepo::byOrderId($order);

		if (empty($payment)) {
			return;
		}

		if ($payment->isPaymentMethod(PaymentMethodEnum::PAYMENT_METHOD_CREDIT_CARD)) {
			return;
		}

		if ($payment->isPaid()) {
			return;
		}

		try {
			$payment->markAsPaid($order->get_total());
			\do_action('pagamentos_para_woocommerce_com_appmax_manual_paid', $payment);
		} catch (Exception $e) {
			\do_action('pagamentos_para_woocommerce_com_appmax_manual_paid_error', $e, $payment);
		}
	}

	/**
	 * Remove links to order when it was removed.
	 *
	 * @param integer $id
	 * @since 2.0.14
	 * @return void
	 */
	public function order_deleted($id)
	{
		$post_type = \get_post_type($id);

		if ($post_type !== 'shop_order') {
			return;
		}

		PaymentsRepo::unlinkOrder($id);
	}

	/**
	 * Update pix to cancelled status when order is cancelled.
	 *
	 * @param integer $order_id
	 * @since 0.1.0
	 * @return void
	 */
	public function payment_cancelled($order_id)
	{
		$order = \wc_get_order($order_id);

		if (empty($order) || empty($order->get_meta('_pagamentos_para_woocommerce_com_appmax_paymentid', true))) {
			return;
		}

		$payment = PaymentsRepo::byOrderId($order);

		if (empty($payment)) {
			return;
		}

		try {
			$payment->markAsCanceled();
			\do_action('pagamentos_para_woocommerce_com_appmax_manual_cancelled', $payment);
		} catch (Exception $e) {
			\do_action('pagamentos_para_woocommerce_com_appmax_manual_cancelled_error', $e, $payment);
		}
	}

	/**
	 * Return if must cancel order when unpaid.
	 * Order will always have the pending status.
	 *
	 * @param boolean $must_cancel
	 * @param WC_Order $order
	 * @since 0.1.0
	 * @return boolean
	 */
	public function unpaid_orders($must_cancel, $order): bool
	{
		if ($order->get_payment_method() !== Connector::plugin()->getName()) {
			return $must_cancel;
		}

		$payment = PaymentsRepo::byOrderId($order);

		if (empty($payment)) {
			return true;
		}

		if (!$payment->isExpired()) {
			return false;
		}

		try {
			$payment->markAsCanceled();
			\do_action('pagamentos_para_woocommerce_com_appmax_manual_cancelled', $payment);
			return true;
		} catch (Exception $e) {
			\do_action('pagamentos_para_woocommerce_com_appmax_manual_cancelled_error', $e, $payment);
			return false;
		}

		return false;
	}
}
