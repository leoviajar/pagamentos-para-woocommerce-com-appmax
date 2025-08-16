<?php

namespace AppMax\WooCommerce\Gateway\Api\Environments;

use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Configuration;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Interfaces\EnvInterface;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Models\ApplicationModel;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Models\CredentialModel;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Request;
use RuntimeException;

/**
 * Production Environment mutator.
 *
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Environments
 * @version 1.0.0
 * @since 1.0.0
 * @category Environment
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license PGLY
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class ProductionEnvironment implements EnvInterface
{
	/**
	 * Base URL.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $_url = 'https://admin.appmax.com.br/api/v3/wordpress';

	/**
	 * Init enviroment at client configuration.
	 *
	 * @param Configuration $client
	 * @param ApplicationInterface $app
	 * @since 1.0.0
	 * @return void
	 */
	public function init(Configuration $client, $app)
	{
		// Base host for requests
		$client->host($this->getUrl());
	}

	/**
	 * Do an OAuth connection to get the access token.
	 * Must fill application model with credential returned
	 * and return the credential model created.
	 *
	 * @param Configuration $client
	 * @param ApplicationModel $app
	 * @since 1.0.0
	 * @return CredentialModel
	 * @throws RuntimeException
	 */
	public function token(Configuration $client, $app): CredentialModel
	{
		throw new RuntimeException('O token de acesso é uma chave permanente e não deve ser definido pela API.');
	}

	/**
	 * Prepare request authenticated requests, filling its headers,
	 * access token, setting host, and anything else.
	 * Must return request created.
	 *
	 * @param Configuration $client
	 * @param ApplicationModel $app
	 * @since 1.0.0
	 * @return Request
	 */
	public function prepare(Configuration $client, $app): Request
	{
		// Must clone to keep original setup
		$_client = $client->clone();

		// Ensure host is as expected
		$_client->host($this->getUrl());
		$request  = new Request($_client);

		$request->headers([
			'Content-Type' => 'application/json; charset=utf-8',
		]);

		return $request;
	}

	/**
	 * Get base URL.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function getUrl(): string
	{
		if (\defined('APPMAX_GATEWAY_PRODUCTION_BASEURL')) {
			return \constant('APPMAX_GATEWAY_PRODUCTION_BASEURL');
		}

		return $this->_url;
	}
}
