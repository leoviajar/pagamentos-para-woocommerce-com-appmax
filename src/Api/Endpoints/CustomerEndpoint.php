<?php

namespace AppMax\WooCommerce\Gateway\Api\Endpoints;

use Exception;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\CreateCustomerPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\GetCustomerPayload;
use AppMax\WooCommerce\Gateway\ApiTools;

/**
 * Customer endpoint.
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
class CustomerEndpoint extends AbstractEndpoint
{
	/**
	 * Create a new customer.
	 *
	 * @param CreateCustomerPayload $payload
	 * @since 1.0.0
	 * @return GetCustomerPayload|null
	 */
	public function create(CreateCustomerPayload $payload): ?GetCustomerPayload
	{
		try {
			$data = $payload->toArray();
			$data['access-token'] = ApiTools::getCredential();

			if ($this->isDebugging()) {
				$this->_log('customer', 'create.request', 'POST', \json_encode($payload->toCensoredArray()));
			}

			$response = $this->_request->post('/customer', $data)->call();

			if ($this->isDebugging()) {
				$this->_log('customer', 'create.response', $response->getStatus(), \json_encode($response->getBody()));
			}

			$this->_throwOnError($response);
			return GetCustomerPayload::import($response->getBody()['data']);
		} catch (Exception $e) {
			$this->_inspectException($e, 'customer', 'create.response');
			return null;
		}
	}

	/**
	 * Get a customer.
	 *
	 * @param integer $id
	 * @since 1.0.0
	 * @return GetCustomerPayload|null
	 */
	public function get(int $id): ?GetCustomerPayload
	{
		try {
			if ($this->isDebugging()) {
				$this->_log('customer', 'get.request', 'GET', $id);
			}

			$response = $this->_request->get('/customer/{id}')->headers(['Access-Token' => ApiTools::getCredential()])->params(['id' => $id])->call();

			if ($this->isDebugging()) {
				$this->_log('customer', 'get.response', $response->getStatus(), \json_encode($response->getBody()));
			}

			if ($response->getStatus() >= 400) {
				return null;
			}

			return GetCustomerPayload::import($response->getBody()['data']);
		} catch (Exception $e) {
			$this->_inspectException($e, 'customer', 'get.response');
			return null;
		}
	}

	/**
	 * Update a customer.
	 *
	 * @param GetCustomerPayload $model
	 * @param CreateCustomerPayload $payload
	 * @since 1.0.0
	 * @return GetCustomerPayload|null
	 */
	public function update(GetCustomerPayload $model, CreateCustomerPayload $payload): ?GetCustomerPayload
	{
		try {
			$data = $payload->toArray();
			$data['access-token'] = ApiTools::getCredential();

			if ($this->isDebugging()) {
				$this->_log('customer', 'update.request', 'POST', \json_encode(\array_merge($payload->toCensoredArray(), ['id' => $model->getId()])));
			}

			$response = $this->_request->put('/customer/{id}', $data)->params(['id' => $model->getId()])->call();

			if ($this->isDebugging()) {
				$this->_log('customer', 'update.response', $response->getStatus(), \json_encode($response->getBody()));
			}

			$this->_throwOnError($response);
			return $model;
		} catch (Exception $e) {
			$this->_inspectException($e, 'customer', 'update.response');
			return null;
		}
	}
}
