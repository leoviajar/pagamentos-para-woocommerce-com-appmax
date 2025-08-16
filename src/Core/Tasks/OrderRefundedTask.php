<?php

namespace AppMax\WooCommerce\Gateway\Core\Tasks;

use AppMax\WooCommerce\Gateway\Core\Records\Enums\PaymentStatusEnum;
use AppMax\WooCommerce\Gateway\Core\Repositories\PaymentsRepo;
use AppMax\WooCommerce\Gateway\Core\Tasks\Interfaces\RunnableTask;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\GetOrderPayload;
use AppMax\WooCommerce\Gateway\ApiTools;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;

/**
 * Order refunded task.
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
class OrderRefundedTask implements RunnableTask
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
		if (ApiTools::hasCredentials() === false) {
			return;
		}

		$appmax_order = $this->_appmax_order;
		$payment = PaymentsRepo::byExternalOrderId($appmax_order->getId());

		if (empty($payment)) {
			return;
		}

		$order = $payment->order();

		if ($payment->isStatus(PaymentStatusEnum::STATUS_REFUNDED) === false) {
			$payment->markAsRefunded();
			PaymentsRepo::save($payment);
			\do_action('pagamentos_para_woocommerce_com_appmax_refunded', $payment, $appmax_order, $order);

			if ($order) {
				$order->add_order_note('Notificação externa: O pedido foi reembolsado na AppMax.');
				$order->update_status('refunded');
				$order->save();
			}

			Connector::debugger()->debug(\sprintf('OrderRefundedTask -> O pedido %s foi reembolsado', $payment->appMaxOrderId()));
		}
	}
}
