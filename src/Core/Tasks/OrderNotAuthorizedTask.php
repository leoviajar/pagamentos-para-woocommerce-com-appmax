<?php

namespace AppMax\WooCommerce\Gateway\Core\Tasks;

use AppMax\WooCommerce\Gateway\Core\Records\Enums\PaymentStatusEnum;
use AppMax\WooCommerce\Gateway\Core\Records\PaymentRecord;
use AppMax\WooCommerce\Gateway\Core\Repositories\PaymentsRepo;
use AppMax\WooCommerce\Gateway\Core\Tasks\Enums\AppMaxOrderStatusEnum;
use AppMax\WooCommerce\Gateway\Core\Tasks\Interfaces\RunnableTask;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\GetOrderPayload;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;

/**
 * Order not authorized task.
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
class OrderNotAuthorizedTask implements RunnableTask
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

		$order = $payment->order();

		if ($payment->isStatus(PaymentStatusEnum::STATUS_CANCELLED) === false) {
			$payment->markAsCanceled();
			PaymentsRepo::save($payment);
			\do_action('pagamentos_para_woocommerce_com_appmax_cancelled', $payment, $appmax_order, $order);

			if ($order) {
				$order->add_order_note('Notificação externa: O pagamento para o pedido não foi aprovado na AppMax.');
				$order->update_status('cancelled');
				$order->save();
			}

			Connector::debugger()->debug(\sprintf('OrderRefundedTask -> O pedido %s foi reembolsado', $appmax_order->getId()));
		}
	}
}
