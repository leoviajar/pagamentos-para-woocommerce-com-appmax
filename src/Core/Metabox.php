<?php

namespace AppMax\WooCommerce\Gateway\Core;

use AppMax\WooCommerce\Gateway\Core\Repositories\PaymentsRepo;
use AppMax\WooCommerce\Gateway\Core\Tasks\TrackingOrderTask;
use AppMax\WooCommerce\Gateway\Plugin;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Core\Scaffold\Initiable;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Core\WP;

/**
 * Manages all metabox actions and filters.
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
class Metabox extends Initiable
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
		if (!WP::is_pure_admin()) {
			return;
		}

		WP::add_action(
			'add_meta_boxes',
			$this,
			'add',
			10,
			2
		);

		WP::add_action(
			'woocommerce_process_shop_order_meta',
			$this,
			'save_data'
		);
	}

	/**
	 * Add metabox to order edit page only when
	 * payment method is equal to pix.
	 *
	 * @param string $post_type
	 * @param WP_Post $post
	 * @since 0.1.0
	 * @return void
	 */
	public function add($post_type, $post)
	{
		if ($post_type !== 'shop_order') {
			return;
		}

		$order = \wc_get_order($post->ID);

		if (\strpos($order->get_payment_method(), Connector::plugin()->getName()) === false) {
			return;
		}

		$payment = PaymentsRepo::byOrderId($order);

		if (!empty($payment)) {
			Plugin::admin_enqueue_scripts();

			add_meta_box(
				Connector::domain().'-metabox-gateway',
				'Pagamento via AppMax',
				array( $this, 'display' ),
				'shop_order',
				'side',
				'high'
			);
		}
	}

	/**
	 * Display the metabox.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function display()
	{
		Plugin::admin_enqueue_scripts();

		echo '<div id="pgly-wps-plugin" class="pgly-wps--settings" style="padding: 0">';
		require_once($this->_plugin->getTemplatePath().'admin/metabox.php');
		echo '</div>';
	}

	/**
	 * Save data from metabox.
	 *
	 * @param int $post_id
	 * @since 0.1.0
	 * @return void
	 */
	public function save_data($post_id)
	{
		$tracking_code = \sanitize_text_field($_POST['appmax_tracking_code'] ?? null);

		if (empty($tracking_code)) {
			return;
		}

		$payment = PaymentsRepo::byOrderId($post_id);
		$order_tracking_code = $payment->order()->get_meta('_appmax_tracking_code');

		if (empty($order_tracking_code) || $order_tracking_code !== $tracking_code) {
			(new TrackingOrderTask(
				$payment,
				$tracking_code
			))->run();
		}
	}
}
