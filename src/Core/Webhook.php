<?php

namespace AppMax\WooCommerce\Gateway\Core;

use Exception;
use AppMax\WooCommerce\Gateway\Core\Tasks\OrderIntegratedTask;
use AppMax\WooCommerce\Gateway\Core\Tasks\OrderNotAuthorizedTask;
use AppMax\WooCommerce\Gateway\Core\Tasks\OrderPaidTask;
use AppMax\WooCommerce\Gateway\Core\Tasks\OrderRefundedTask;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\GetOrderPayload;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Core\Scaffold\Initiable;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Core\WP;
use WP_REST_Request;

/**
 * Manages all webhook actions and filters.
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
class Webhook extends Initiable
{
	public const EVENT_ORDER_APPROVED = 'OrderApproved';

	public const EVENT_ORDER_AUTHORIZED = 'OrderAuthorized';

	public const EVENT_ORDER_AUTHORIZED_DELAY = 'OrderAuthorizedWithDelay';

	public const EVENT_ORDER_BILLET_CREATED = 'OrderBilletCreated';

	public const EVENT_ORDER_BILLET_OVERDUE = 'OrderBilletOverdue';

	public const EVENT_ORDER_INTEGRATED = 'OrderIntegrated';

	public const EVENT_ORDER_PAID = 'OrderPaid';

	public const EVENT_ORDER_PENDING_INTEGRATION = 'OrderPendingIntegration';

	public const EVENT_ORDER_REFUND = 'OrderRefund';

	public const EVENT_PAYMENT_NOT_AUTHORIZED = 'PaymentNotAuthorized';

	public const EVENT_PAYMENT_NOT_AUTHORIZED_WITH_DELAY = 'PaymentNotAuthorizedWithDelay';

	/**
	 * Startup method with all actions and
	 * filter to run.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function startup()
	{
		WP::add_action(
			'rest_api_init',
			$this,
			'endpoint'
		);
	}

	/**
	 * Register route.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function endpoint()
	{
		\register_rest_route(
			'appmax',
			'/webhooks/v1',
			array(
				'methods' => ['POST'],
				'callback' => array( $this, 'webhook' ),
			)
		);
	}

	/**
	 * Webhook filter.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function webhook(WP_REST_Request $request)
	{
		$data = $request->get_params();

		if (empty($data)) {
			Connector::debugger()->force()->error('appmax.webhook.request -> Nenhum evento válido foi recebido', $data);
			\wp_send_json_success(['message' => 'Nenhum corpo de requisição foi recebido.']);
		}

		$event = trim(explode("|", $data['event'])[0]);

		if (empty($event) || empty($data['data'])) {
			Connector::debugger()->force()->error('appmax.webhook.request -> Nenhum evento válido foi recebido', $data);
			\wp_send_json_success(['message' => 'Nenhum evento válido recebido.']);
		}

		Connector::debugger()->debug('appmax.webhook.request -> '.$event);
		$appmax_order = GetOrderPayload::import($data['data']);

		try {
			switch ($event) {
				case static::EVENT_ORDER_PAID:
					(new OrderPaidTask($appmax_order))->run();
					break;
				case static::EVENT_ORDER_INTEGRATED:
					(new OrderIntegratedTask($appmax_order))->run();
					break;
				case static::EVENT_ORDER_REFUND:
					(new OrderRefundedTask($appmax_order))->run();
					break;
				case static::EVENT_PAYMENT_NOT_AUTHORIZED:
					(new OrderNotAuthorizedTask($appmax_order))->run();
					break;
			}

			\wp_send_json_success(['message' => 'Resposta recebida com sucesso.']);
		} catch (Exception $e) {
			Connector::debugger()->force()->error('appmax.webhook.request [ERROR] -> '.$e->getMessage());
			\wp_send_json_error(['message' => 'A resposta apresentou erros.']);
		}
	}
}
