<?php

namespace AppMax\WooCommerce\Gateway\Core\Tasks;

use DateTimeImmutable;
use Exception;
use AppMax\WooCommerce\Gateway\Core\Records\Enums\PaymentStatusEnum;
use AppMax\WooCommerce\Gateway\Core\Records\PaymentRecord;
use AppMax\WooCommerce\Gateway\Core\Repositories\PaymentsRepo;
use AppMax\WooCommerce\Gateway\Core\Tasks\Enums\AppMaxOrderStatusEnum;
use AppMax\WooCommerce\Gateway\Core\Tasks\Interfaces\RunnableTask;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\GetOrderPayload;
use AppMax\WooCommerce\Gateway\ApiTools;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Settings\KeyingBucket;

/**
 * Order processing task.
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
class OrderProcessingTask implements RunnableTask
{
	/**
	 * Payment record.
	 *
	 * @var PaymentRecord
	 * @since 0.1.0
	 */
	protected $_payment;

	/**
	 * Constructor.
	 *
	 * @param PaymentRecord $payment
	 * @since 0.1.0
	 * @return void
	 */
	public function __construct(PaymentRecord $payment)
	{
		$this->_payment = $payment;
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

		try {
			$appmax_order = ApiTools::getApi()->order()->get($this->_payment->appMaxOrderId());

			if (empty($appmax_order)) {
				return;
			}
		} catch (Exception $e) {
			return;
		}

		/** @var WC_Order $order */
		$order = $this->_payment->order();

		if (empty($order)) {
			if ($appmax_order->getStatus() === AppMaxOrderStatusEnum::STATUS_APPROVED) {
				$paid_at = $appmax_order->getPaidAt() ?? new DateTimeImmutable('now', \wp_timezone());

				$this->_payment
					->changeStatus(PaymentStatusEnum::STATUS_APPROVED)
					->markAsPaid($appmax_order->getTotal(), $paid_at);

				PaymentsRepo::save($this->_payment);
				Connector::debugger()->debug(\sprintf('OrderProcessingTask -> A transação %d foi aprovada', $this->_payment->appMaxOrderId()));
				return;
			}

			if ($appmax_order->getStatus() === AppMaxOrderStatusEnum::STATUS_INTEGRATED) {
				$integrated_at = $appmax_order->getIntegratedAt() ?? new DateTimeImmutable('now', \wp_timezone());

				$this->_payment
					->changeStatus(PaymentStatusEnum::STATUS_APPROVED)
					->markAsIntegrated($appmax_order->getTotal(), $integrated_at);

				PaymentsRepo::save($this->_payment);
				Connector::debugger()->debug(\sprintf('OrderProcessingTask -> A transação %d foi integrada', $this->_payment->appMaxOrderId()));
				return;
			}

			if ($appmax_order->getStatus() === AppMaxOrderStatusEnum::STATUS_AUTHORIZED) {
				$this->_payment->changeStatus(PaymentStatusEnum::STATUS_IN_ANALYSIS);
				PaymentsRepo::save($this->_payment);
				Connector::debugger()->force()->info(\sprintf('OrderProcessingTask -> A transação %d foi autorizada e aguarda aprovação', $this->_payment->appMaxOrderId()));
				return;
			}

			if ($appmax_order->getStatus() === AppMaxOrderStatusEnum::STATUS_CANCELLED) {
				$this->_payment->markAsCanceled();
				PaymentsRepo::save($this->_payment);
				Connector::debugger()->force()->info(\sprintf('OrderProcessingTask -> A transação %d foi cancelada', $this->_payment->appMaxOrderId()));
				return;
			}

			if ($appmax_order->getStatus() === AppMaxOrderStatusEnum::STATUS_REFUNDED) {
				$this->_payment->markAsRefunded();
				PaymentsRepo::save($this->_payment);
				Connector::debugger()->force()->info(\sprintf('OrderProcessingTask -> A transação %d foi estornada', $this->_payment->appMaxOrderId()));
				return;
			}

			if ($this->_payment->isExpired()) {
				$this->_payment->markAsExpired();
				PaymentsRepo::save($this->_payment);
				Connector::debugger()->force()->info(\sprintf('OrderProcessingTask -> A transação %d foi expirada', $this->_payment->appMaxOrderId()));
				return;
			}

			return;
		}

		if ($order->has_status(['cancelled', 'refunded'])) {
			if (!$this->_payment->isStatus(PaymentStatusEnum::STATUS_CANCELLED) && $order->has_status('cancelled')) {
				$this->_payment->markAsCanceled();
				PaymentsRepo::save($this->_payment);
				return;
			}

			if (!$this->_payment->isStatus(PaymentStatusEnum::STATUS_REFUNDED) && $order->has_status('refunded')) {
				$this->_payment->markAsRefunded();
				PaymentsRepo::save($this->_payment);
				return;
			}
		}

		if ($appmax_order->getStatus() === AppMaxOrderStatusEnum::STATUS_PENDING) {
			if ($this->_payment->isExpired() === true) {
				return $this->expires($appmax_order);
			}

			return;
		}

		if ($appmax_order->getStatus() === AppMaxOrderStatusEnum::STATUS_APPROVED
				|| $appmax_order->getStatus() === AppMaxOrderStatusEnum::STATUS_INTEGRATED) {
			return $this->paid($appmax_order);
		}

		if ($appmax_order->getStatus() === AppMaxOrderStatusEnum::STATUS_AUTHORIZED) {
			return $this->in_analysis($appmax_order);
		}

		if ($appmax_order->getStatus() === AppMaxOrderStatusEnum::STATUS_CANCELLED) {
			return $this->cancel($appmax_order);
		}

		if ($appmax_order->getStatus() === AppMaxOrderStatusEnum::STATUS_REFUNDED) {
			return $this->refund($appmax_order);
		}

		if ($this->_payment->isExpired() === true) {
			return $this->expires($appmax_order);
		}
	}

	/**
	 * In analysis order when needed.
	 *
	 * @param GetOrderPayload $appmax_order
	 * @return void
	 */
	protected function paid(GetOrderPayload $appmax_order)
	{
		$payment = $this->_payment;
		$order = $this->_payment->order();

		if ($appmax_order->getStatus() === AppMaxOrderStatusEnum::STATUS_APPROVED) {
			$payment->markAsPaid($appmax_order->getTotal(), $appmax_order->getPaidAt() ?? new DateTimeImmutable('now', \wp_timezone()));
		} elseif ($appmax_order->getStatus() === AppMaxOrderStatusEnum::STATUS_INTEGRATED) {
			$payment->markAsIntegrated($appmax_order->getTotal(), $appmax_order->getIntegratedAt() ?? new DateTimeImmutable('now', \wp_timezone()));
		}

		PaymentsRepo::save($payment);
		\do_action('pagamentos_para_woocommerce_com_appmax_paid', $payment, $appmax_order, $order);

		if ($order) {
			$order->payment_complete($payment->payReference());
			$order->add_order_note('O pedido foi aprovado na plataforma AppMax');
			$order->update_status(Connector::settings()->get('gateway', new KeyingBucket())->get('paid_status', 'processing'));
			$order->save();
		}

		Connector::debugger()->force()->info(\sprintf('OrderProcessingTask -> O pedido %d foi aprovado', $order->get_id()));
		return;
	}

	/**
	 * In analysis order when needed.
	 *
	 * @param GetOrderPayload $appmax_order
	 * @since 0.1.0
	 * @return void
	 */
	protected function in_analysis(GetOrderPayload $appmax_order)
	{
		$payment = $this->_payment;
		$order = $this->_payment->order();

		$payment->changeStatus(PaymentStatusEnum::STATUS_IN_ANALYSIS);
		PaymentsRepo::save($this->_payment);
		\do_action('pagamentos_para_woocommerce_com_appmax_processing', $payment, $appmax_order, $order);

		if ($order) {
			$order->add_order_note('O pedido foi autorizado e aguarda aprovação na plataforma AppMax');
			$order->update_status(Connector::settings()->get('gateway', new KeyingBucket())->get('processing_status', 'on-hold'));
			$order->save();
		}

		Connector::debugger()->force()->info(\sprintf('OrderProcessingTask -> O pedido %d foi autorizado e aguarda aprovação', $order->get_id()));
		return;
	}

	/**
	 * Cancel order when needed.
	 *
	 * @param GetOrderPayload $appmax_order
	 * @since 0.1.0
	 * @return void
	 */
	protected function cancel(GetOrderPayload $appmax_order)
	{
		$payment = $this->_payment;
		$order = $this->_payment->order();

		$payment->markAsCanceled();
		PaymentsRepo::save($payment);
		\do_action('pagamentos_para_woocommerce_com_appmax_cancelled', $payment, $appmax_order, $order);

		if ($order) {
			$order->add_order_note('O pedido foi cancelado na plataforma AppMax');
			$order->update_status('cancelled');
			$order->save();
		}

		Connector::debugger()->force()->info(\sprintf('OrderProcessingTask -> O pedido %d foi cancelado', $order->get_id()));
	}

	/**
	 * Refund order when needed.
	 *
	 * @param GetOrderPayload $appmax_order
	 * @since 0.1.0
	 * @return void
	 */
	protected function refund(GetOrderPayload $appmax_order)
	{
		$payment = $this->_payment;
		$order = $this->_payment->order();

		$payment->markAsRefunded();
		PaymentsRepo::save($payment);
		\do_action('pagamentos_para_woocommerce_com_appmax_refunded', $payment, $appmax_order, $order);

		if ($order) {
			$order->add_order_note('O pedido foi estornado na plataforma AppMax');
			$order->update_status('refunded');
			$order->save();
		}

		Connector::debugger()->force()->info(\sprintf('OrderProcessingTask -> O pedido %d foi estornado', $order->get_id()));
	}

	/**
	 * Expires order when needed.
	 *
	 * @param GetOrderPayload $appmax_order
	 * @since 0.1.0
	 * @return void
	 */
	protected function expires(GetOrderPayload $appmax_order)
	{
		$payment = $this->_payment;
		$order = $this->_payment->order();

		$payment->markAsExpired();
		PaymentsRepo::save($payment);
		\do_action('pagamentos_para_woocommerce_com_appmax_expired', $payment, $appmax_order, $order);

		if ($order) {
			$order->add_order_note('O pedido foi expirado localmente');
			$order->update_status('cancelled');
			$order->save();
		}

		Connector::debugger()->force()->info(\sprintf('OrderProcessingTask -> O pedido %d foi expirado', $order->get_id()));
	}
}
