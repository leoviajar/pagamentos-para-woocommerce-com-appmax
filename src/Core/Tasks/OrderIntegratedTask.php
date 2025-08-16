<?php

namespace AppMax\WooCommerce\Gateway\Core\Tasks;

use DateTimeImmutable;
use AppMax\WooCommerce\Gateway\Core\Helpers\OrderBuilder;
use AppMax\WooCommerce\Gateway\Core\Repositories\PaymentsRepo;
use AppMax\WooCommerce\Gateway\Core\Tasks\Enums\AppMaxOrderOriginEnum;
use AppMax\WooCommerce\Gateway\Core\Tasks\Enums\AppMaxOrderStatusEnum;
use AppMax\WooCommerce\Gateway\Core\Tasks\Interfaces\RunnableTask;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\GetOrderPayload;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Settings\KeyingBucket;

/**
 * Order integrated task.
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
class OrderIntegratedTask implements RunnableTask
{
	/**
	 * AppMax order.
	 *
	 * @var GetOrderPayload
	 * @since 0.1.0
	 */
	protected $_appmax_order;

	/**
	 * Constructor.
	 *
	 * @param GetOrderPayload $payment
	 * @since 0.1.0
	 * @return void
	 */
	public function __construct(GetOrderPayload $appmax_order)
	{
		$this->_appmax_order = $appmax_order;
	}

	/**
	 * Run task.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function run()
	{
		$appmax_order = $this->_appmax_order;
		$payment = PaymentsRepo::byExternalOrderId($appmax_order->getId());

		if (empty($payment)) {
			return;
		}

		/** @var KeyingBucket $settings */
		$settings = Connector::settings()->get('gateway', new KeyingBucket());

		$call_center = $settings->get('call_center', 'on_integrated');
		$paid_status = $settings->get('paid_status', 'processing');

		if (empty($payment)) {
			if (\in_array($appmax_order->getOrigin(), AppMaxOrderOriginEnum::callCenter()) && $call_center === 'on_integrated') {
				OrderBuilder::create($this->_appmax_order);
			}

			return;
		}

		/** @var WC_Order $order */
		$order = $payment->order();

		if ($payment->isPaid() || $order->has_status($paid_status)) {
			return;
		}

		if ($appmax_order->getOrigin() === AppMaxOrderOriginEnum::ORIGIN_API
					|| (\in_array($appmax_order->getOrigin(), AppMaxOrderOriginEnum::callCenter()) && $call_center === 'on_integrated')) {
			if ($appmax_order->getStatus() === AppMaxOrderStatusEnum::STATUS_INTEGRATED) {
				$payment->markAsIntegrated($appmax_order->getTotal(), $appmax_order->getIntegratedAt() ?? new DateTimeImmutable('now', \wp_timezone()));
				PaymentsRepo::save($payment);
				\do_action('pagamentos_para_woocommerce_com_appmax_paid', $payment, $order);

				$order->add_order_note('Notificação externa: O pedido foi aprovado na AppMax.');
				$order->payment_complete($payment->payReference());
				$order->update_status($paid_status);

				$order->save();
				Connector::debugger()->debug(\sprintf('OrderIntegratedTask -> O pedido %s foi integrado', $appmax_order->getId()));
			}
		}
	}
}
