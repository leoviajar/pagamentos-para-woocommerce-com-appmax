<?php

namespace AppMax\WooCommerce\Gateway\Core;

use Exception;
use AppMax\WooCommerce\Gateway\Core\Database\Schemas\PaymentsTableSchema;
use AppMax\WooCommerce\Gateway\Core\Records\Enums\PaymentMethodEnum;
use AppMax\WooCommerce\Gateway\Core\Tasks\BulkOrderProcessingTask;
use AppMax\WooCommerce\Gateway\Core\Tasks\BulkSyncProductsTask;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Core\Scaffold\Initiable;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Core\WP;

/**
 * Manages all cron actions.
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
class Cron extends Initiable
{
	/**
	 * Available frequencies.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	public const AVAILABLE_FREQUENCIES = [
		'everyhour',
		'everytwominutes',
		'evetythirtyminutes',
	];

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
			'cron_schedules',
			$this,
			'schedules',
			99
		);

		WP::add_action(
			'cron_pagamentos_para_woocommerce_com_appmax_processing_credit_card',
			$this,
			'processing_credit_card'
		);

		WP::add_action(
			'cron_pagamentos_para_woocommerce_com_appmax_processing_boleto',
			$this,
			'processing_boleto'
		);

		WP::add_action(
			'cron_pagamentos_para_woocommerce_com_appmax_processing_pix',
			$this,
			'processing_pix'
		);

		WP::add_action(
			'cron_pagamentos_para_woocommerce_com_appmax_sync_products',
			$this,
			'sync_products'
		);
	}

	/**
	 * Get all orders paid with Pix, and run action
	 * pgly_wc_piggly_pix_process to processing
	 * payment data and update pix.
	 *
	 * @action cron_pagamentos_para_woocommerce_com_appmax_processing
	 * @param bool $in_loop
	 * @since 0.1.0
	 * @return void
	 */
	public function cleaning()
	{
		global $wpdb;
		$tableName = PaymentsTableSchema::tableName();
		$wpdb->query("DELETE FROM {$tableName} WHERE `order_id` IS NULL");
	}

	/**
	 * Get all orders paid with Boleto, and run action
	 * cron_pagamentos_para_woocommerce_com_appmax_processing_credit_card to processing
	 * payment data and update credit_card.
	 *
	 * @action cron_pagamentos_para_woocommerce_com_appmax_processing_credit_card
	 * @param bool $in_loop
	 * @since 0.1.0
	 * @return void
	 */
	public function processing_credit_card(bool $in_loop = true)
	{
		try {
			(new BulkOrderProcessingTask(PaymentMethodEnum::PAYMENT_METHOD_CREDIT_CARD))->run();
		} catch (Exception $e) {
			\do_action('pagamentos_para_woocommerce_com_appmax_processing_failed', $e->getMessage(), $e->getTraceAsString());
		}
	}

	/**
	 * Get all orders paid with Boleto, and run action
	 * cron_pagamentos_para_woocommerce_com_appmax_processing_boleto to processing
	 * payment data and update boleto.
	 *
	 * @action cron_pagamentos_para_woocommerce_com_appmax_processing_boleto
	 * @param bool $in_loop
	 * @since 0.1.0
	 * @return void
	 */
	public function processing_boleto(bool $in_loop = true)
	{
		try {
			(new BulkOrderProcessingTask(PaymentMethodEnum::PAYMENT_METHOD_BOLETO))->run();
			$this->cleaning();
		} catch (Exception $e) {
			\do_action('pagamentos_para_woocommerce_com_appmax_processing_failed', $e->getMessage(), $e->getTraceAsString());
		}
	}

	/**
	 * Get all orders paid with Pix, and run action
	 * cron_pagamentos_para_woocommerce_com_appmax_processing_pix to processing
	 * payment data and update pix.
	 *
	 * @action cron_pagamentos_para_woocommerce_com_appmax_processing_pix
	 * @param bool $in_loop
	 * @since 0.1.0
	 * @return void
	 */
	public function processing_pix(bool $in_loop = true)
	{
		try {
			(new BulkOrderProcessingTask(PaymentMethodEnum::PAYMENT_METHOD_PIX))->run();
		} catch (Exception $e) {
			\do_action('pagamentos_para_woocommerce_com_appmax_processing_failed', $e->getMessage(), $e->getTraceAsString());
		}
	}

	/**
	 * Sync all products.
	 *
	 * @action cron_pagamentos_para_woocommerce_com_appmax_sync_products
	 * @since 0.1.0
	 * @return void
	 */
	public function sync_products()
	{
		(new BulkSyncProductsTask())->run();
	}

	/**
	 * All schedules available to current cron jobs.
	 *
	 * @param array $schedules
	 * @since 0.1.0
	 * @return array
	 */
	public function schedules(array $schedules): array
	{
		$schedules['everyhour'] = [
			'interval' => 3600,
			'display' => 'Uma vez a cada hora'
		];

		$schedules['everytwominutes'] = [
			'interval' => 120,
			'display' => 'Uma vez a cada dois minutos'
		];

		$schedules['evetythirtyminutes'] = [
			'interval' => 1800,
			'display' => 'Uma vez a cada trinta minutos'
		];

		return $schedules;
	}

	/**
	 * Create cron jobs.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public static function create()
	{
		if (!\wp_next_scheduled('cron_pagamentos_para_woocommerce_com_appmax_processing_credit_card')) {
			\wp_schedule_event(
				\time(),
				'everytwominutes',
				'cron_pagamentos_para_woocommerce_com_appmax_processing_credit_card'
			);
		}

		if (!\wp_next_scheduled('cron_pagamentos_para_woocommerce_com_appmax_processing_boleto')) {
			\wp_schedule_event(
				\time(),
				'evetythirtyminutes',
				'cron_pagamentos_para_woocommerce_com_appmax_processing_boleto'
			);
		}

		if (!\wp_next_scheduled('cron_pagamentos_para_woocommerce_com_appmax_processing_pix')) {
			\wp_schedule_event(
				\time(),
				'everytwominutes',
				'cron_pagamentos_para_woocommerce_com_appmax_processing_pix'
			);
		}

		if (!\wp_next_scheduled('cron_pagamentos_para_woocommerce_com_appmax_sync_products')) {
			\wp_schedule_event(
				\time(),
				'everyhour',
				'cron_pagamentos_para_woocommerce_com_appmax_sync_products'
			);
		}
	}

	/**
	 * Check if cronjob exists.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public static function exists()
	{
		return (!\wp_next_scheduled('cron_pagamentos_para_woocommerce_com_appmax_processing_credit_card')
		|| !\wp_next_scheduled('cron_pagamentos_para_woocommerce_com_appmax_processing_boleto')
		|| !\wp_next_scheduled('cron_pagamentos_para_woocommerce_com_appmax_processing_pix')
		|| !\wp_next_scheduled('cron_pagamentos_para_woocommerce_com_appmax_sync_products')) === false;
	}

	/**
	 * Destroy cron jobs.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public static function destroy()
	{
		\wp_clear_scheduled_hook('cron_pagamentos_para_woocommerce_com_appmax_processing_credit_card');
		\wp_clear_scheduled_hook('cron_pagamentos_para_woocommerce_com_appmax_processing_boleto');
		\wp_clear_scheduled_hook('cron_pagamentos_para_woocommerce_com_appmax_processing_pix');
		\wp_clear_scheduled_hook('cron_pagamentos_para_woocommerce_com_appmax_sync_products');
	}
}
