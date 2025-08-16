<?php

namespace AppMax\WooCommerce\Gateway\Api\Environments;

/**
 * Homologation Environment mutator.
 *
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Environments
 * @version 0.1.0
 * @since 0.1.0
 * @category Environment
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license PGLY
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class HomologationEnvironment extends ProductionEnvironment
{
	/**
	 * Base URL.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	protected $_url = 'https://homolog.sandboxappmax.com.br/api/v3/wordpress';

	/**
	 * Get base URL.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function getUrl(): string
	{
		if (\defined('APPMAX_GATEWAY_HOMOLOG_BASEURL')) {
			return \constant('APPMAX_GATEWAY_HOMOLOG_BASEURL');
		}

		return $this->_url;
	}
}
