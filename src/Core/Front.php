<?php

namespace AppMax\WooCommerce\Gateway\Core;

use AppMax\WooCommerce\Gateway\Core\Repositories\PaymentsRepo;
use AppMax\WooCommerce\Gateway\Core\Tasks\OrderProcessingTask;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Core\Scaffold\Initiable;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Core\WP;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Helpers\RequestBodyParser;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Settings\KeyingBucket;
use Exception;
use WC_Order;

/**
 * Manages all front-end actions and filters.
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
class Front extends Initiable
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
		$gateways = [
			'_boleto' => 'boleto_page',
			'_pix' => 'pix_page',
			'_credit_card' => 'credit_page'
		];

		WP::add_action(
			'wp_enqueue_scripts',
			$this,
			'enqueue_scripts'
		);

		WP::add_action(
			'woocommerce_before_checkout_form',
			$this,
			'notice'
		);

		foreach ($gateways as $gateway => $method) {
			WP::add_action(
				'woocommerce_receipt_'.Connector::plugin()->getName().$gateway,
				$this,
				$method,
				10,
				1
			);

			WP::add_action(
				'woocommerce_thankyou_'.Connector::plugin()->getName().$gateway,
				$this,
				$method,
				10,
				1
			);
		}

		WP::add_action('wp_ajax_nopriv_'.Connector::plugin()->getName().'_check_payment', $this, 'check_payment');
		WP::add_action('wp_ajax_'.Connector::plugin()->getName().'_check_payment', $this, 'check_payment');
	}

	/**
	 * Open the payment page.
	 *
	 * @param WC_Order|integer $order_id
	 * @param boolean $echo
	 * @since 0.1.0
	 * @return void
	 */
	public function credit_page($order_id)
	{
		\wp_enqueue_style(
			'pagamentos-para-woocommerce-com-appmax-global',
			Connector::plugin()->getUrl() . 'assets/css/styles.css',
			[],
			'0.1.0'
		);

		$order = $order_id instanceof WC_Order ? $order_id : \wc_get_order($order_id);

		if (empty($order)) {
			return;
		}

		$payment = PaymentsRepo::byOrderId($order);

		if (empty($payment)) {
			return;
		}

		\wc_get_template(
			'html-pagamentos-para-woocommerce-com-appmax-credit_card-thankyou.php',
			[
				'order' => $order,
				'payment' => $payment
			],
			WC()->template_path().\dirname(Connector::plugin()->getBasename()).'/',
			Connector::plugin()->getTemplatePath().'woocommerce/'
		);
	}

	/**
	 * Open the payment page.
	 *
	 * @param WC_Order|integer $order_id
	 * @param boolean $echo
	 * @since 0.1.0
	 * @return void
	 */
	public function boleto_page($order_id)
	{
		\wp_enqueue_style(
			'pagamentos-para-woocommerce-com-appmax-global',
			Connector::plugin()->getUrl() . 'assets/css/styles.css',
			[],
			'1.0.0'
		);

		$order = $order_id instanceof WC_Order ? $order_id : \wc_get_order($order_id);

		if (empty($order)) {
			return;
		}

		$payment = PaymentsRepo::byOrderId($order);

		if (empty($payment)) {
			return;
		}

		\wc_get_template(
			'html-pagamentos-para-woocommerce-com-appmax-boleto-thankyou.php',
			[
				'order' => $order,
				'payment' => $payment
			],
			WC()->template_path().\dirname(Connector::plugin()->getBasename()).'/',
			Connector::plugin()->getTemplatePath().'woocommerce/'
		);
	}

	/**
	 * Open the payment page.
	 *
	 * @param WC_Order|integer $order_id
	 * @param boolean $echo
	 * @since 0.1.0
	 * @return void
	 */
	public function pix_page($order_id)
	{
		\wp_enqueue_style(
			'pagamentos-para-woocommerce-com-appmax-global',
			Connector::plugin()->getUrl() . 'assets/css/styles.css',
			[],
			'1.0.0'
		);

		$order = $order_id instanceof WC_Order ? $order_id : \wc_get_order($order_id);

		if (empty($order)) {
			return;
		}

		$payment = PaymentsRepo::byOrderId($order);

		if (empty($payment)) {
			return;
		}

		\wc_get_template(
			'html-pagamentos-para-woocommerce-com-appmax-pix-thankyou.php',
			[
				'order' => $order,
				'payment' => $payment
			],
			WC()->template_path().\dirname(Connector::plugin()->getBasename()).'/',
			Connector::plugin()->getTemplatePath().'woocommerce/'
		);
	}

	/**
	 * Check payment processing transaction.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function check_payment()
	{
		WC()->mailer();

		try {
			$requestBody = new RequestBodyParser();

			if (!$requestBody->isPOST()) {
				throw new Exception('Método HTTP não disponível.', 405);
			}

			$body = $requestBody->body();

			if (\wp_verify_nonce($body['x_security'], \PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_ACTION.'_front_nonce') === false) {
				\wp_send_json_success(['status' => false], 200);
				exit;
			}

			if (empty($body['payment_id'])) {
				\wp_send_json_success(['status' => false], 200);
				exit;
			}

			$payment = PaymentsRepo::byOrderId($body['payment_id']);

			if (empty($payment)) {
				\wp_send_json_success(['status' => false], 200);
				exit;
			}

			(new OrderProcessingTask($payment))->run();

			if ($payment->isWaiting() === false) {
				\wp_send_json_success(['status' => true], 200);
				exit;
			}

			\wp_send_json_success(['status' => false], 200);
			exit;
		} catch (Exception $e) {
			\wp_send_json_success(['status' => false], 200);
			exit;
		}
	}

	/**
	 * Notice on checkout.
	 *
	 * @since 0.2.0
	 * @return void
	 */
	public function notice()
	{
		if (is_checkout() && $this->is_available() && !get_query_var('order-received')) {
			echo '<ul id="pagamentos-para-woocommerce-com-appmax-notice" class="woocommerce-error" role="alert" style="display: none">';
			echo '</ul>';
		}
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 0.2.0
	 * @return array
	 */
	public function enqueue_scripts()
	{
		$is_order_received = get_query_var('order-received') || get_query_var('order-pay');

		if (is_checkout() && $this->is_available()) {
			$deps = [];
			if ($is_order_received === false) {
				$deps = ['wc-checkout'];
			}

			\wp_enqueue_script(
				'maska',
				Connector::plugin()->getUrl() . 'assets/vendor/js/maska.js',
				$deps,
				'0.1.0',
				true
			);

			\wp_enqueue_script(
				'axios',
				Connector::plugin()->getUrl() . 'assets/vendor/js/axios.min.js',
				null,
				['1.3.5'],
				true
			);

			\wp_enqueue_script(
				'woocommerce-appmax-pagamentos-por-piggly-global',
				Connector::plugin()->getUrl() . 'assets/js/engine.js',
				\array_merge($deps, ['maska', 'axios']),
				'1.0.0',
				true
			);

			\wp_enqueue_style(
				'woocommerce-appmax-pagamentos-por-piggly-global',
				Connector::plugin()->getUrl() . 'assets/css/styles.css',
				[],
				'1.0.0'
			);

			\wp_localize_script(
				'woocommerce-appmax-pagamentos-por-piggly-global',
				'pagamentos_para_woocommerce_com_appmax_front',
				[
					'ajax_url' => \admin_url('admin-ajax.php'),
					'x_security' => \wp_create_nonce(\PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_ACTION.'_front_nonce'),
				]
			);
		}
	}


	/**
	 * Check if gateway is available.
	 *
	 * @since 0.2.0
	 * @return boolean
	 */
	public function is_available(): bool
	{
		$empty = new KeyingBucket();

		return Connector::settings()->get('credit_card', $empty)->get('enabled', 'no') === 'yes'
			|| Connector::settings()->get('boleto', $empty)->get('enabled', 'no') === 'yes'
			|| Connector::settings()->get('pix', $empty)->get('enabled', 'no') === 'yes';
	}

	/**
	 * Get global settings.
	 *
	 * @since 0.2.0
	 * @return KeyingBucket
	 */
	protected function globalSettings(): KeyingBucket
	{
		return Connector::settings()->get('gateway', new KeyingBucket());
	}
}
