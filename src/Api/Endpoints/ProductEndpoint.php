<?php

namespace AppMax\WooCommerce\Gateway\Api\Endpoints;

use Exception;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\CreateProductPayload;
use AppMax\WooCommerce\Gateway\ApiTools;

/**
 * Product endpoint.
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
class ProductEndpoint extends AbstractEndpoint
{
	/**
	 * Create a new product.
	 *
	 * @param CreateProductPayload $payload
	 * @since 1.0.0
	 * @return boolean
	 */
	public function create(CreateProductPayload $payload): bool
	{
		try {
			$data = $payload->toArray();
			$data['access-token'] = ApiTools::getCredential();

			if ($this->isDebugging()) {
				$this->_log('product', 'create.request', 'POST', \json_encode($payload->toCensoredArray()));
			}

			$response = $this->_request->post('/product', $data)->call();

			if ($this->isDebugging()) {
				$this->_log('product', 'create.response', $response->getStatus(), \json_encode($response->getBody()));
			}

			$this->_throwOnError($response);
			return true;
		} catch (Exception $e) {
			$this->_inspectException($e, 'product', 'create.response');
			return false;
		}
	}

	/**
	 * Update a product.
	 *
	 * @param string $sku
	 * @param CreateProductPayload $payload
	 * @since 1.0.0
	 * @return boolean
	 */
	public function update(string $sku, CreateProductPayload $payload): bool
	{
		try {
			$data = $payload->toArray();
			$data['access-token'] = ApiTools::getCredential();

			if ($this->isDebugging()) {
				$this->_log('product', 'update.request', 'POST', \json_encode(\array_merge($payload->toCensoredArray(), ['sku' => $sku])));
			}

			$response = $this->_request->post('/product/{sku}', $data)->params(['sku' => $sku])->call();

			if ($this->isDebugging()) {
				$this->_log('product', 'update.response', $response->getStatus(), \json_encode($response->getBody()));
			}

			$this->_throwOnError($response);
			return true;
		} catch (Exception $e) {
			$this->_inspectException($e, 'product', 'update.response');
			return false;
		}
	}

	/**
	 * Delete a product.
	 *
	 * @param string $sku
	 * @param CreateProductPayload $payload
	 * @since 1.0.0
	 * @return boolean
	 */
	public function delete(string $sku): bool
	{
		try {
			if ($this->isDebugging()) {
				$this->_log('product', 'delete.request', 'DELETE', \json_encode(['sku' => $sku]));
			}

			$response = $this->_request->delete('/product/{sku}', ['access-token' => ApiTools::getCredential()])->params(['sku' => $sku])->call();

			if ($this->isDebugging()) {
				$this->_log('product', 'delete.response', $response->getStatus(), \json_encode($response->getBody()));
			}

			$this->_throwOnError($response);
			return true;
		} catch (Exception $e) {
			$this->_inspectException($e, 'product', 'delete.response');
			return false;
		}
	}
}
