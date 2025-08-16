<?php

namespace AppMax\WooCommerce\Gateway\Api\Endpoints;

use Exception;
use AppMax\WooCommerce\Gateway\Vendor\Monolog\Logger;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Endpoint;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Exceptions\ApiResponseException;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Response;
use RuntimeException;

/**
 * Abstract endpoint methods.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Endpoints
 * @category Endpoints
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
abstract class AbstractEndpoint extends Endpoint
{
	/**
	 * Throw an exception if response is not valid.
	 *
	 * @param Response $response
	 * @return void
	 * @throws ApiResponseException
	 * @since 1.0.0
	 */
	protected function _throwOnError(Response $response)
	{
		$body = $response->getBody();
		$code = $response->getStatus();

		if (isset($body['success']) && $body['success'] === false) {
			throw new ApiResponseException(
				'Não foi possível processar a requisição. Notifique o administrador.',
				$code,
				$response
			);
		}

		if ($code < 400) {
			return;
		}
	}

	/**
	 * Inspect an exception logging it and throwing a new one.
	 *
	 * @param Exception $e
	 * @param string $endpoint
	 * @param string $operation
	 * @throws Exception
	 * @since 1.0.0
	 * @return void
	 */
	protected function _inspectException(Exception $e, string $endpoint, string $operation)
	{
		if ($e instanceof ApiResponseException) {
			$this->_log($endpoint, $operation.'.error', $e->getCode(), \json_encode($e->getResponse()->getBody()), Logger::ERROR);
			throw $e;
		}

		$this->_log($endpoint, $operation.'.error', $e->getCode(), $e->getMessage(), Logger::ERROR);

		if ($e instanceof RuntimeException) {
			throw $e;
		}
	}

	/**
	 * Do a log.
	 *
	 * @param string $endpoint
	 * @param string $operation
	 * @param string $details
	 * @param string $message
	 * @param int $level
	 * @since 1.0.0
	 * @return void
	 */
	protected function _log(
		string $endpoint,
		string $operation,
		string $details,
		string $message,
		int $level = Logger::DEBUG
	) {
		$this->_request->getConfig()->log(
			$level,
			'appmax.api.v3.wordpress.'.$endpoint.'.'.$operation.' ['.$details.'] -> '.$message
		);
	}

	/**
	 * Check if is debugging.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	protected function isDebugging(): bool
	{
		return $this->_request->getConfig()->isDebugging();
	}
}
