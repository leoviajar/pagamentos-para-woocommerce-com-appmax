<?php

namespace AppMax\WooCommerce\Gateway;

use AppMax\WooCommerce\Gateway\Api\ApiWrapper;
use AppMax\WooCommerce\Gateway\Api\Models\ApplicationModel;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Settings\KeyingBucket;
use RuntimeException;

/**
 * API connection tools.
 *
 * @package \AppMax\WooCommerce\Gateway
 * @subpackage \AppMax\WooCommerce\Gateway
 * @version 0.1.0
 * @since 0.1.0
 * @category WP
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license piggly
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class ApiTools
{
	/**
	 * Method to run all business logic.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public static function startup()
	{
		ApiWrapper::timezone(\wp_timezone());
	}

	/**
	 * Get API application.
	 *
	 * @since 0.1.0
	 * @return ApplicationModel
	 * @throws RuntimeException
	 */
	public static function getApplication(): ApplicationModel
	{
		/** @var KeyingBucket $settings */
		$settings = Connector::settings()->get('gateway', new KeyingBucket());

		if (static::hasCredentials() === false) {
			throw new RuntimeException('Você deve definir o token de acesso antes de utilizar a API da AppMax.');
		}

		$app = new ApplicationModel();

		$app->set('environment', $settings->get('environment', ApplicationModel::ENV_PRODUCTION));
		$app->set('access_token', static::getCredential());
		$app->set('debug_mode', $settings->get('debug_mode', false));

		return $app;
	}

	/**
	 * Get API wrapper.
	 *
	 * @since 0.1.0
	 * @return ApiWrapper
	 * @throws RuntimeException
	 */
	public static function getApi(): ApiWrapper
	{
		return new ApiWrapper(static::getApplication(), Connector::debugger()->getLogger());
	}

	/**
	 * Return if access_token is set.
	 *
	 * @since 0.1.0
	 * @return boolean
	 */
	public static function hasCredentials(): bool
	{
		return !empty(static::credentialConstant());
	}

	/**
	 * Get current access token.
	 *
	 * @since 0.1.0
	 * @return string|null
	 */
	public static function getCredential(): ?string
	{
		return static::credentialConstant();
	}

	/**
	 * Get credential on constant.
	 *
	 * @since 0.1.0
	 * @return string|null
	 */
	public static function credentialConstant(): ?string
	{
		if (\defined('APPMAX_GATEWAY_ACCESS_TOKEN')) {
			return \constant('APPMAX_GATEWAY_ACCESS_TOKEN');
		}

		return null;
	}
}
