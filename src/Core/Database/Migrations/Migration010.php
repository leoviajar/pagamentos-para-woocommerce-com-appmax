<?php

namespace AppMax\WooCommerce\Gateway\Core\Database\Migrations;

use Exception;
use AppMax\WooCommerce\Gateway\Core\Database\Manager;

/**
 * Migration 010.
 *
 * Create payments table.
 *
 * @package \AppMax\WooCommerce\Gateway
 * @subpackage \AppMax\WooCommerce\Gateway\Core\Database\Migrations
 * @version 0.2.0
 * @since 0.2.0
 * @category Runners
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license MIT
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class Migration010 extends AbstractMigration
{
	/**
	 * Run the migrations.
	 *
	 * @since 0.2.0
	 * @return void
	 */
	public function up(): void
	{
		if (\version_compare(Manager::installedVersion(), $this->version(), '>=') === true) {
			return;
		}

		if (Manager::tableExists(Manager::tableName('payments')) === true) {
			Manager::updateVersion($this->version());
			return;
		}

		if (!\function_exists('dbDelta')) {
			require_once \ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		$table = Manager::tableName('payments');

		$sql = "CREATE TABLE {$table} (
			`id` BIGINT NOT NULL AUTO_INCREMENT,
			`env` ENUM('test','homol','prod') NOT NULL DEFAULT 'prod',
			`order_id` BIGINT NULL,
			`customer_id` BIGINT NULL,
			`ext_order_id` BIGINT NULL,
			`ext_customer_id` BIGINT NULL,
			`ext_site_id` BIGINT NULL,
			`cnpj` VARCHAR(255) NULL,
			`payment_method` ENUM('boleto','credit_card','pix') NOT NULL,
			`status` TINYINT(2) NOT NULL DEFAULT 0 COMMENT '0:CREATED 1:PENDING 2:APPROVED 3:CANCELLED 4:PROCESSING 5:INTEGRATED 6:PENDING_INTEGRATION 7:REFUNDED 8:CHARGEBACK_LOST 9:CHARGEBACK_WAITING 10:CHARGEBACK_WIN 11:EXPIRED',
			`amount` DECIMAL(10,2) NOT NULL,
			`amount_paid` DECIMAL(10,2) NULL,
			`tracking_code` TEXT NULL,
			`pay_reference` VARCHAR(255) NULL,
			`media` TEXT NULL,
			`url` TEXT NULL,
			`path` TEXT NULL,
			`payment_code` VARCHAR(255) NULL,
			`paid_at` TIMESTAMP NULL,
			`integrated_at` TIMESTAMP NULL,
			`refunded_at` TIMESTAMP NULL,
			`expires_at` TIMESTAMP NULL,
			`updated_at` TIMESTAMP NULL,
			`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY (`id`),
			INDEX `env` (`env`),
			INDEX `order_id` (`order_id`),
			INDEX `customer_id` (`customer_id`),
			INDEX `ext_order_id` (`ext_order_id`),
			INDEX `ext_customer_id` (`ext_customer_id`),
			INDEX `ext_site_id` (`ext_site_id`),
			INDEX `payment_method` (`payment_method`),
			INDEX `status` (`status`),
			INDEX `amount` (`amount`),
			INDEX `paid_at` (`paid_at`),
			INDEX `integrated_at` (`integrated_at`),
			INDEX `refunded_at` (`refunded_at`),
			INDEX `expires_at` (`expires_at`),
			INDEX `updated_at` (`updated_at`),
			INDEX `created_at` (`created_at`)
		);";

		\dbDelta($sql);

		if (Manager::tableExists(Manager::tableName('payments')) === false) {
			throw new Exception('Não foi possível criar a tabela de pagamentos no banco de dados...');
		}

		Manager::updateVersion($this->version());
	}

	/**
	 * Reverse the migrations.
	 *
	 * @since 0.2.0
	 * @return void
	 */
	public function down(): void
	{
		if (\version_compare(Manager::installedVersion(), $this->version(), '>') === true) {
			return;
		}

		if (Manager::tableExists(Manager::tableName('payments')) === false) {
			Manager::updateVersion('0'); // Reset version to previous
			return;
		}

		$table = Manager::tableName('payments');
		$sql = "DROP TABLE {$table};";

		global $wpdb;
		$wpdb->query($sql);

		if (Manager::tableExists(Manager::tableName('payments')) === true) {
			throw new Exception('Não foi possível reverter o banco de dados...');
		}

		Manager::updateVersion('0'); // Reset version to previous
	}

	/**
	 * Get migration version in semver format.
	 *
	 * @since 0.2.0
	 * @return string
	 */
	public function version(): string
	{
		return '0.1.0';
	}
}
