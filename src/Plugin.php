<?php

namespace AppMax\WooCommerce\Gateway;

use DateTime;
use AppMax\WooCommerce\Gateway\Core\Configuration;
use AppMax\WooCommerce\Gateway\Core\Cron;
use AppMax\WooCommerce\Gateway\Core\Front;
use AppMax\WooCommerce\Gateway\Core\Metabox;
use AppMax\WooCommerce\Gateway\Core\Webhook;
use AppMax\WooCommerce\Gateway\Core\Woocommerce;
use AppMax\WooCommerce\Gateway\Vendor\Monolog\Handler\StreamHandler;
use AppMax\WooCommerce\Gateway\Vendor\Monolog\Logger;
use AppMax\WooCommerce\Gateway\Api\ApiWrapper;
use AppMax\WooCommerce\Gateway\Core\ProductAdmin;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Core;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Core\WP;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Settings\KeyingBucket;

/**
 * Deactivate plugin.
 *
 * @package \AppMax\WooCommerce\Gateway
 * @subpackage \AppMax\WooCommerce\Gateway\WP
 * @version 0.1.0
 * @since 0.1.0
 * @category WP
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license piggly
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class Plugin
{
	/**
	 * Method to run all business logic.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public static function startup(Core $core)
	{
		// Set timezone for API
		ApiWrapper::timezone(\wp_timezone());

		static::_logger();

		$initiables = [
			Configuration::class,
		];

		if (ApiTools::hasCredentials()) {
			$initiables[] = Front::class;
			$initiables[] = Metabox::class;
			$initiables[] = Webhook::class;
			$initiables[] = Woocommerce::class;
			$initiables[] = Cron::class;
			$initiables[] = ProductAdmin::class;
		}

		$core->initiables($initiables);

		// Debug notice
		WP::add_action(
			'admin_notices',
			new Plugin(),
			'debug_notice'
		);

		// Cron notice
		WP::add_action(
			'admin_notices',
			new Plugin(),
			'cron_notice'
		);

		// Access token notice
		WP::add_action(
			'admin_notices',
			new Plugin(),
			'access_token_notice'
		);
	}

	/**
	 * Prepare plugin logger.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	protected static function _logger()
	{
		$domain = Connector::domain();
		$path = \WP_CONTENT_DIR.'/'.$domain;

		if (!\is_dir($path)) {
			\wp_mkdir_p($path);
		}

		if (!\file_exists($path.'/.htaccess')) {
			\file_put_contents($path.'/.htaccess', 'Options -Indexes');
		}

		$now = (new DateTime('now', wp_timezone()))->format('Y-m-d');

		$hash = \sprintf(
			'/log-%s-%s.log',
			$now,
			\md5($now.(\NONCE_KEY??''))
		);

		if (!\file_exists($path.$hash)) {
			touch($path.$hash);
		}

		// create a log channel
		$log = new Logger($domain);
		$log->pushHandler(new StreamHandler($path.$hash, Logger::DEBUG, true, 0666));

		$log->setTimezone(\wp_timezone());

		Connector::debugger()->changeState(Connector::settings()->get('gateway', new KeyingBucket())->get('debug_mode', false));
		Connector::debugger()->setLogger($log);
	}

	/**
	 * Debug notice.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function debug_notice()
	{
		if (!Connector::debugger()->isDebugging()) {
			return;
		}

		$url = admin_url('admin.php?page='.\PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_SLUG);

		echo '<div class="notice notice-error">';
		echo '<p>';
		echo 'O <strong>Modo Debug</strong> do plugin <strong>Pagamentos com AppMax para WooCommerce</strong> está ativado, só mantenha este modo ativado para testes ou detecções de erros.';
		echo " Seus logs podem ficar enormes. <a href=\"{$url}\">Clique aqui</a> para ir para as configurações do plugin e desativar o modo debug.";
		echo '</p>';
		echo '</div>';
	}

	/**
	 * Cron notice.
	 *
	 * @since 1.1.0-beta
	 * @return void
	 */
	public function cron_notice()
	{
		if (Cron::exists()) {
			return;
		}

		$url = admin_url('admin.php?page='.\PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_SLUG.'-processing&fix_cron=1');

		echo '<div class="notice notice-error">';
		echo '<p>';
		echo 'Os <strong>eventos agendados</strong> do plugin <strong>Pagamentos com AppMax para WooCommerce</strong> não estão ativos. A verificação otimizada de pagamento em segundo plano não funcionará.';
		echo " <a href=\"{$url}\">Clique aqui</a> para corrigir isso.";
		echo '</p>';
		echo '</div>';
	}

	/**
	 * Access token notice.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function access_token_notice()
	{
		if (ApiTools::hasCredentials()) {
			return;
		}

		$url = admin_url('admin.php?page='.\PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_SLUG);

		echo '<div class="notice notice-error">';
		echo '<p>';
		echo 'Antes de começar a utilizar o plugin <strong>Pagamentos com AppMax para WooCommerce</strong>, você deve configurar o token de acesso.';
		echo " No arquivo <code>wp-config.php</code> da sua instalação do WordPress, adicione a seguinte linha <code>define('APPMAX_GATEWAY_ACCESS_TOKEN', 'seu_token');</code> acima de <code>/* Isto é tudo, pode parar de editar! :) */</code>.";
		echo " Substitua <code>seu_token</code> pelo seu token de acesso compartilhado pela AppMax.";
		echo '</p>';
		echo '</div>';
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public static function admin_enqueue_scripts()
	{
		$version = '0.2.9';
		$name = \sprintf('pgly-wps-settings-%s', $version);

		\wp_enqueue_script(
			'axios',
			Connector::plugin()->getUrl() . 'assets/vendor/js/axios.min.js',
			null,
			['1.3.5'],
			true
		);

		\wp_enqueue_script(
			$name,
			Connector::plugin()->getUrl() . 'assets/vendor/js/pgly-wps-settings.js',
			['axios'],
			$version,
			true
		);

		\wp_enqueue_style(
			$name,
			Connector::plugin()->getUrl() . 'assets/vendor/css/pgly-wps-settings.min.css',
			null,
			'0.3.0',
			'all'
		);

		\wp_enqueue_script(
			Connector::domain(),
			Connector::plugin()->getUrl() . 'assets/js/admin-engine.js',
			[$name],
			'0.1.0-beta.15',
			\true
		);

		\wp_localize_script(
			$name,
			Connector::plugin()->getName(),
			[
				'ajax_url' => admin_url('admin-ajax.php'),
				'x_security' => \wp_create_nonce(\PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_ACTION.'_nonce'),
				'plugin_url' => admin_url('admin.php?page='.Connector::domain()),
				'assets_url' => Connector::plugin()->getUrl()
			]
		);
	}
}
