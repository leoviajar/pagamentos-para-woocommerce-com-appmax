<?php

/**
 * @since 1.0.0-beta
 * @version 1.1.4-beta
 * @package \AppMax\WooCommerce\Gateway
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 *
 * @wordpress-plugin
 * Plugin Name:       Pagamentos com AppMax para WooCommerce
 * Plugin URI:        https://appmax.com.br/
 * Description:       Habilita os métodos de pagamentos do gateway de pagamentos da AppMax.
 * Requires at least: 4.0
 * Requires PHP:      7.2
 * Version:           1.6.0
 * Author:            AppMax
 * Author URI:        https://appmax.com.br/
 * License:           GPLv3 or later
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       pagamentos-para-woocommerce-com-appmax
 * Domain Path:       /languages
 * Network:           false
 */

require 'plugin-update-checker/plugin-update-checker.php'; 
use YahnisElsts\PluginUpdateChecker\v5\PucFactory; 

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/leoviajar/pagamentos-para-woocommerce-com-appmax',
    __FILE__,
    'pagamentos-para-woocommerce-com-appmax.php'
);

use AppMax\WooCommerce\Gateway\Plugin;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Core;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Requirements\PHPVersionRequirement;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Requirements\WoocommerceRequirement;
use AppMax\WooCommerce\Gateway\WP\Activator;
use AppMax\WooCommerce\Gateway\WP\Deactivator;
use AppMax\WooCommerce\Gateway\WP\PluginSettings;
use AppMax\WooCommerce\Gateway\WP\Upgrader;

// If this file is called directly, abort.
if (!defined('WPINC')) {
	exit();
}

/** @var string Currently plugin slug. */
if (!defined('PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_SLUG')) {
	define('PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_SLUG', 'pagamentos-para-woocommerce-com-appmax');
}

/** @var string Currently plugin action. */
if (!defined('PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_ACTION')) {
	define('PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_ACTION', 'pagamentos_para_woocommerce_com_appmax');
}

/** @var string Currently plugin version. Start at version 1.0.0 and use SemVer - https://semver.org */
if (!defined('PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_VERSION')) {
	define('PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_VERSION', '1.1.1-beta');
}

/** @var string Currently plugin version. Start at version 1.0.0 and use SemVer - https://semver.org */
if (!defined('PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_DBVERSION')) {
	define('PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_DBVERSION', '1.1.1-beta');
}

/** @var string Minimum php version required. */
if (!defined('PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_PHPVERSION')) {
	define('PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_PHPVERSION', '7.4');
}

require __DIR__ . '/vendor/autoload.php';

global $wpdb;

if (Core::requirements(
	'O plugin <strong>Pagamentos com AppMax para WooCommerce</strong> não pode ser habilitado.',
	[
		PHPVersionRequirement::class => [
			'required_version' => PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_PHPVERSION,
			'custom_response' => 'A versão do PHP não atende os requisitos...'
		],
		WoocommerceRequirement::class => [
			'custom_response' => 'O WooCommerce precisa estar ativado para este plugin funcionar...'
		]
	]
)) {
	Plugin::startup(Core::create(
		PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_SLUG,
		PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_ACTION,
		PluginSettings::defaults(),
		[
			'plugin_file' => __FILE__,
			'plugin_version' => PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_VERSION,
			'activator' => Activator::class,
			'deactivator' => Deactivator::class,
			'upgrader' => Upgrader::class,
		]
	));
}
