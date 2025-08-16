<?php

namespace AppMax\WooCommerce\Gateway\Api\Endpoints;

use Exception;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\CreateOrderPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\Order\CreateDeliveryTrackingCodePayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\Order\CreateRefundPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\GetOrderPayload;
use AppMax\WooCommerce\Gateway\ApiTools;

/**
 * Order endpoint.
 *
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Endpoints
 * @version 1.0.0
 * @since 1.0.0
 * @category Endpoint
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license PGLY
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class OrderEndpoint extends AbstractEndpoint
{
	/**
	 * Create a new Order.
	 *
	 * @param CreateOrderPayload $payload
	 * @since 1.0.0
	 * @return GetOrderPayload|null
	 */
	public function create(CreateOrderPayload $payload): ?GetOrderPayload
	{
		try {
			$data = $payload->toArray();
			$data['access-token'] = ApiTools::getCredential();

			if ($this->isDebugging()) {
				$this->_log('order', 'create.request', 'POST', \json_encode($payload->toCensoredArray()));
			}

			$response = $this->_request->post('/order', $data)->call();

			if ($this->isDebugging()) {
				$this->_log('order', 'create.response', $response->getStatus(), \json_encode($response->getBody()));
			}

			$this->_throwOnError($response);
			return GetOrderPayload::import($response->getBody()['data']);
		} catch (Exception $e) {
			$this->_inspectException($e, 'order', 'create.response');
			return null;
		}
	}

	/**
	 * Refund a order.
	 *
	 * @param CreateRefundPayload $refund
	 * @since 1.0.0
	 * @return boolean
	 */
	public function refund(CreateRefundPayload $refund): bool
	{
		try {
			$data = $refund->toArray();
			$data['access-token'] = ApiTools::getCredential();

			if ($this->isDebugging()) {
				$this->_log('order', 'refund.request', 'POST', \json_encode($refund->toCensoredArray()));
			}

			$response = $this->_request->post('/refund', $data)->call();

			if ($this->isDebugging()) {
				$this->_log('order', 'refund.response', $response->getStatus(), \json_encode($response->getBody()));
			}

			$this->_throwOnError($response);
			return true;
		} catch (Exception $e) {
			$this->_inspectException($e, 'order', 'refund.response');
			return false;
		}
	}

	/**
	 * Set the delivery tracking code to an order.
	 *
	 * @param CreateDeliveryTrackingCodePayload $refund
	 * @since 1.0.0
	 * @return boolean
	 */
	public function deliveryCode(CreateDeliveryTrackingCodePayload $payload): bool
	{
		try {
			$data = $payload->toArray();
			$data['access-token'] = ApiTools::getCredential();

			if ($this->isDebugging()) {
				$this->_log('order', 'delivery-tracking-code.request', 'POST', \json_encode($payload->toCensoredArray()));
			}

			$response = $this->_request->post('/order/delivery-tracking-code', $data)->call();

			if ($this->isDebugging()) {
				$this->_log('order', 'delivery-tracking-code.response', $response->getStatus(), \json_encode($response->getBody()));
			}

			$this->_throwOnError($response);
			return true;
		} catch (Exception $e) {
			$this->_inspectException($e, 'order', 'delivery-tracking-code.response');
			return false;
		}
	}

	/**
	 * Get a Order.
	 *
	 * @param integer $id
	 * @since 1.0.0
	 * @return GetOrderPayload|null
	 */
	public function get(int $id): ?GetOrderPayload
	{
		try {
			if ($this->isDebugging()) {
				$this->_log('order', 'get.request', 'GET', $id);
			}

			$response = $this->_request->get('/order/{id}')->headers(['Access-Token' => ApiTools::getCredential()])->params(['id' => $id])->call();

			if ($this->isDebugging()) {
				$this->_log('order', 'get.response', $response->getStatus(), \json_encode($response->getBody()));
			}

			if ($response->getStatus() >= 400) {
				return null;
			}

			return GetOrderPayload::import($response->getBody()['data']);
		} catch (Exception $e) {
			$this->_inspectException($e, 'order', 'get.response');
			return null;
		}
	}
}
