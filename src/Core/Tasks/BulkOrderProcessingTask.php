<?php

namespace AppMax\WooCommerce\Gateway\Core\Tasks;

use AppMax\WooCommerce\Gateway\ApiTools;
use DateTimeImmutable;
use AppMax\WooCommerce\Gateway\Core\Database\Schemas\PaymentsTableSchema;
use AppMax\WooCommerce\Gateway\Core\Records\Enums\PaymentStatusEnum;
use AppMax\WooCommerce\Gateway\Core\Records\PaymentRecord;
use AppMax\WooCommerce\Gateway\Core\Tasks\Interfaces\RunnableTask;
use AppMax\WooCommerce\Gateway\Plugin;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Collection\RecordCollection;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Settings\KeyingBucket;

/**
 * Bulk processing task.
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
class BulkOrderProcessingTask implements RunnableTask
{
	/**
	 * Payment method.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	protected $_method;

	/**
	 * Constructor.
	 *
	 * @param string $payment_method
	 * @since 0.1.0
	 * @return void
	 */
	public function __construct(string $payment_method)
	{
		$this->_method = $payment_method;
	}

	/**
	 * Run task.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function run()
	{
		if (ApiTools::hasCredentials() === false) {
			return;
		}

		/** @var KeyingBucket $settings */
		$settings = Connector::settings()->get('gateway', new KeyingBucket());

		$tableName = PaymentsTableSchema::tableName();
		$before = (new DateTimeImmutable('now', \wp_timezone()))->format('Y-m-d H:i:s');

		$env = $settings->get('environment', 'homol');

		$status = implode(
			"', '",
			[
				PaymentStatusEnum::STATUS_PENDING,
				PaymentStatusEnum::STATUS_PENDING_INTEGRATION,
				PaymentStatusEnum::STATUS_CREATED,
				PaymentStatusEnum::STATUS_INTEGRATED,
				PaymentStatusEnum::STATUS_IN_ANALYSIS
			]
		);

		$query = "SELECT * FROM {$tableName} WHERE `env` = '{$env}' AND `status` IN ('{$status}') AND `order_id` IS NOT NULL AND `payment_method` = {$this->_method} AND `created_at` < '{$before}' AND `updated_at` < '{$before}' ORDER BY `updated_at` ASC";
		$collection = new RecordCollection($query);
		$page = 1;

		do {
			$collection->paginate($settings->get('cron_limit', 10), $page);
			$records = $collection->get();

			if (!empty($records)) {
				foreach ($records as $rec) {
					(new OrderProcessingTask(PaymentRecord::fromRecord($rec)))->run();
				}
			}

			$page++;
		} while (!empty($records));
	}
}
