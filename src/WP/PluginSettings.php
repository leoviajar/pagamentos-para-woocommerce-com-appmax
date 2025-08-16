<?php

namespace AppMax\WooCommerce\Gateway\WP;

use AppMax\WooCommerce\Gateway\Api\Models\ApplicationModel;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Settings\KeyingBucket;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Settings\Settings;

/**
 * Engine for parse settings data and save it.
 *
 * @package \AppMax\WooCommerce\Gateway
 * @subpackage \AppMax\WooCommerce\Gateway\WP
 * @version 0.1.0
 * @since 0.1.0
 * @category Settings
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license piggly
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class PluginSettings extends Settings
{
	/**
	 * All allowed sections for settings.
	 *
	 * @since 0.1.0
	 * @var array
	 */
	protected $_sections = [
		'gateway',
		'boleto',
		'pix',
		'credit_card'
	];

	/**
	 * Get all settings defaults.
	 *
	 * @since 0.1.0
	 * @return KeyingBucket
	 */
	public static function defaults(): KeyingBucket
	{
		$_defs = [
			'gateway' => [
				'debug_mode' => false,
				'hidden_mode' => false,
				'environment' => ApplicationModel::ENV_PRODUCTION,
				'store_name' => '',
				'call_center' => 'disabled',
				'waiting_message' => 'Aguardando pagamento.',
				'paid_message' => 'Pagamento recebido com sucesso.',
				'waiting_status' => 'pending',
				'processing_status' => 'on-hold',
				'paid_status' => 'processing',
			],
			'boleto' => [
				'enabled' => 'no',
				'title' => 'Boleto Bancário',
				'description' => 'Pague o seu boleto e receba o pedido assim que o pagamento for compensado.',
				'instructions' => '',
				'due_date' => 3,
				'expires_after' => 3,
				'decrease_stock' => false,
				'must_expires' => false,
			],
			'pix' => [
				'enabled' => 'no',
				'title' => 'Pix',
				'description' => 'Pague com o Pix e receba o pedido assim que o pagamento for compensado.',
				'instructions' => '',
				'expires_in' => 3600,
				'expires_after' => 3,
				'decrease_stock' => false,
				'must_expires' => false,
			],
			'credit_card' => [
				'enabled' => 'no',
				'title' => 'Cartão de Crédito',
				'description' => 'Pague com o cartão de crédito.',
				'instructions' => '',
				'max_installments' => 12,
				'min_installment_amount' => 0.00,
				'interest_rate' => 0.00,
				'interest_incremental_rate' => 0.00,
				'interest_free_installments' => 3,
				'statement_descriptor' => '',
				'show_total' => false,
				'decrease_stock' => false,
			],
			'processing' => [
				'cron_limit' => 10,
				'sync_while_editing' => true
			],
		];

		return (new KeyingBucket())->import($_defs);
	}
}
