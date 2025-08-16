<?php

namespace AppMax\WooCommerce\Gateway\Core;

use Exception;
use AppMax\WooCommerce\Gateway\Core\Records\Enums\PaymentMethodEnum;
use AppMax\WooCommerce\Gateway\Core\Records\Enums\PaymentStatusEnum;
use AppMax\WooCommerce\Gateway\Core\Repositories\PaymentsRepo;
use AppMax\WooCommerce\Gateway\Core\Tasks\BulkOrderProcessingTask;
use AppMax\WooCommerce\Gateway\Core\Tasks\OrderProcessingTask;
use AppMax\WooCommerce\Gateway\Plugin;
use AppMax\WooCommerce\Gateway\Api\Models\ApplicationModel;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Helpers\BodyValidator;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Settings\KeyingBucket;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Core\Scaffold\JSONable;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Core\WP;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Helpers\RequestBodyParser;
use AppMax\WooCommerce\Gateway\WP\PluginSettings;

/**
 * Manage the custom post type to class.
 *
 * @package \AppMax\WooCommerce\Gateway
 * @subpackage \AppMax\WooCommerce\Gateway\Core\Posts
 * @version 0.1.0
 * @since 0.1.0
 * @category PostType
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license piggly
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class Configuration extends JSONable
{
	/**
	 * Startup method with all actions and
	 * filter to run.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function startup()
	{
		WP::add_action('admin_menu', $this, 'add_menu', 99);
		$this->handlers();
	}

	/**
	 * Create a new menu at Wordpress admin menu bar.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function add_menu()
	{
		$slug = Connector::domain();

		\add_menu_page(
			'AppMax -- Configurações',
			'AppMax Pagamentos',
			'edit_users',
			$slug,
			[$this, 'page'],
			'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48c3ZnIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCAxMjggMTI4IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCAxMjggMTI4OyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+PHBhdGggZD0iTTc5LjksNzAuN3YzNC41YzAsNy4yLTMuMywxMy42LTguNSwxNy44Yy0zLjksMy4xLTguOSw1LTE0LjMsNUgyMi42QzEwLDEyOC0wLjIsMTE3LjgtMC4yLDEwNS4yVjcwLjcgYzAtNS41LDItMTAuNiw1LjItMTQuNWM0LjItNS4xLDEwLjUtOC4zLDE3LjYtOC4zaDM0LjVjMi4zLDAsNC41LDAuMyw2LjUsMUwzMi45LDc5LjZjMi4yLDcuNyw4LjMsMTMuNiwxNi4xLDE1LjdMNzkuMiw2NSBDNzkuNyw2Ni44LDc5LjksNjguNyw3OS45LDcwLjd6IiBmaWxsPSIjRkZGIi8+PHBhdGggZD0iTTEyOCwyMi44djUwLjRjMCwxMi42LTEwLjIsMjIuOC0yMi44LDIyLjhIMTAxVjQ5LjhDMTAxLDM3LjIsOTAuOCwyNyw3OC4yLDI3SDMydi00LjJDMzIsMTAuMiw0Mi4yLDAsNTQuOCwwaDUwLjQgQzExNy44LDAsMTI4LDEwLjIsMTI4LDIyLjh6IiBmaWxsPSIjRkZGIi8+PC9zdmc+'
		);

		\add_submenu_page(
			$slug,
			'AppMax: Configurações Gerais',
			'Configurações Gerais',
			'manage_options',
			$slug,
		);

		\add_submenu_page(
			$slug,
			'AppMax: Configurações do Boleto',
			'Boleto',
			'manage_options',
			$slug.'-boleto',
			[$this, 'boleto'],
		);

		\add_submenu_page(
			$slug,
			'AppMax: Configurações do Pix',
			'Pix',
			'manage_options',
			$slug.'-pix',
			[$this, 'pix'],
		);

		\add_submenu_page(
			$slug,
			'AppMax: Configurações do Cartão de Crédito',
			'Cartão de Crédito',
			'manage_options',
			$slug.'-credit-card',
			[$this, 'credit_card'],
		);

		\add_submenu_page(
			$slug,
			'AppMax: Configurações de Processamento',
			'Processamento',
			'manage_options',
			$slug.'-processing',
			[$this, 'processing'],
		);


		\add_submenu_page(
			$slug,
			'AppMax: Cobranças',
			'Ver Cobranças',
			'manage_options',
			$slug.'-explore',
			[$this, 'explore'],
		);

		\add_submenu_page(
			$slug,
			'AppMax: Logs / Status',
			'Status / Logs',
			'manage_options',
			$slug.'-logs',
			[$this, 'logs'],
		);
	}

	/**
	 * Handle all endpoints.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function handlers()
	{
		$name = Connector::plugin()->getName();
		WP::add_action('wp_ajax_'.$name.'_save_global', $this, 'save_gateway');
		WP::add_action('wp_ajax_'.$name.'_save_boleto', $this, 'save_boleto');
		WP::add_action('wp_ajax_'.$name.'_save_pix', $this, 'save_pix');
		WP::add_action('wp_ajax_'.$name.'_save_credit_card', $this, 'save_credit_card');
		WP::add_action('wp_ajax_'.$name.'_save_processing', $this, 'save_processing');

		WP::add_action('wp_ajax_'.$name.'_get_env', $this, 'get_env');
		WP::add_action('wp_ajax_'.$name.'_get_call_center', $this, 'get_call_center');
		WP::add_action('wp_ajax_'.$name.'_get_status', $this, 'get_status');

		WP::add_action('wp_ajax_'.$name.'_all_process', $this, 'process_all');
		WP::add_action('wp_ajax_'.$name.'_process', $this, 'process');
	}

	/**
	 * Page with all configurations.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function page()
	{
		Plugin::admin_enqueue_scripts();

		echo '<div id="pgly-wps-plugin" class="pgly-wps--settings">';
		require_once Connector::plugin()->getTemplatePath() . 'admin/settings.php';
		echo '</div>';
	}

	/**
	 * Page with all configurations.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function boleto()
	{
		Plugin::admin_enqueue_scripts();

		echo '<div id="pgly-wps-plugin" class="pgly-wps--settings">';
		require_once Connector::plugin()->getTemplatePath() . 'admin/boleto-settings.php';
		echo '</div>';
	}

	/**
	 * Page with all configurations.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function pix()
	{
		Plugin::admin_enqueue_scripts();

		echo '<div id="pgly-wps-plugin" class="pgly-wps--settings">';
		require_once Connector::plugin()->getTemplatePath() . 'admin/pix-settings.php';
		echo '</div>';
	}

	/**
	 * Page with all configurations.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function credit_card()
	{
		Plugin::admin_enqueue_scripts();

		echo '<div id="pgly-wps-plugin" class="pgly-wps--settings">';
		require_once Connector::plugin()->getTemplatePath() . 'admin/credit-card-settings.php';
		echo '</div>';
	}

	/**
	 * Page with all configurations.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function processing()
	{
		Plugin::admin_enqueue_scripts();

		echo '<div id="pgly-wps-plugin" class="pgly-wps--settings">';
		require_once Connector::plugin()->getTemplatePath() . 'admin/processing-settings.php';
		echo '</div>';
	}

	/**
	 * Page with all configurations.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function explore()
	{
		Plugin::admin_enqueue_scripts();

		echo '<div id="pgly-wps-plugin" class="pgly-wps--settings">';
		require_once Connector::plugin()->getTemplatePath() . 'admin/explore.php';
		echo '</div>';
	}

	/**
	 * Page with all configurations.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function logs()
	{
		Plugin::admin_enqueue_scripts();

		echo '<div id="pgly-wps-plugin" class="pgly-wps--settings">';
		require_once Connector::plugin()->getTemplatePath() . 'admin/logs.php';
		echo '</div>';
	}

	/**
	 * Process transaction.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function process()
	{
		WC()->mailer();

		try {
			$requestBody = new RequestBodyParser();

			if (!$requestBody->isPOST()) {
				throw new Exception('Método HTTP não disponível.', 405);
			}

			$body = $requestBody->body();
			$this->authorizationCheck($body);

			if (empty($body['id'])) {
				$this->success(['message' => 'Nada para processar.']);
			}

			$payment = PaymentsRepo::byId($body['id']);

			if (empty($payment)) {
				$this->success(['message' => 'Nada para processar.']);
			}

			$old_status = $payment->status();
			(new OrderProcessingTask($payment))->run();
			$new_status = $payment->status();

			if ($old_status === $new_status) {
				$this->success(['message' => 'Processamento realizado com sucesso, não houve nenhuma mudança no pagamento']);
			}

			$this->success([
				'message' => \sprintf(
					'Processamento realizado com sucesso, o pagamento migrou do status %s para %s',
					PaymentStatusEnum::label($old_status),
					PaymentStatusEnum::label($new_status)
				)
			]);
		} catch (Exception $e) {
			$this->error($e);
		}
	}

	/**
	 * Process all transactions.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function process_all()
	{
		WC()->mailer();

		try {
			$requestBody = new RequestBodyParser();

			if (!$requestBody->isPOST()) {
				throw new Exception('Método HTTP não disponível.', 405);
			}

			$body = $requestBody->body();
			$this->authorizationCheck($body);

			(new BulkOrderProcessingTask(PaymentMethodEnum::PAYMENT_METHOD_BOLETO))->run();
			(new BulkOrderProcessingTask(PaymentMethodEnum::PAYMENT_METHOD_CREDIT_CARD))->run();
			(new BulkOrderProcessingTask(PaymentMethodEnum::PAYMENT_METHOD_PIX))->run();
			$this->success(['message' => 'Processamento realizado com sucesso']);
		} catch (Exception $e) {
			$this->error($e);
		}
	}

	/**
	 * Get environment list.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function get_env()
	{
		$this->success([
			[ 'value' => ApplicationModel::ENV_HOMOL, 'label' => 'Homologação' ],
			[ 'value' => ApplicationModel::ENV_PRODUCTION, 'label' => 'Produção' ]
		]);
	}

	/**
	 * Get call_center list.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function get_call_center()
	{
		$this->success([
			[ 'value' => 'on_integrated', 'label' => 'Receber pedido do call center quando integrado' ],
			[ 'value' => 'on_paid', 'label' => 'Receber pedido do call center quando pago' ],
			[ 'value' => 'disabled', 'label' => 'Não receber pedidos do call center' ]
		]);
	}

	/**
	 * Woocommerce status list.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function get_status()
	{
		$woo_statuses = \wc_get_order_statuses();
		$statuses = [];

		foreach ($woo_statuses as $key => $label) {
			$key = \str_replace('wc-', '', $key);

			$statuses[] = [
				'value' => $key,
				'label' => $label.' ('.$key.')'
			];
		}

		$this->success($statuses);
	}

	/**
	 * Convert settings to form array.
	 *
	 * @since 0.1.0
	 * @return array
	 */
	public function globalToFormArray(): array
	{
		/** @var KeyingBucket $settings */
		$settings = Connector::settings()->get('gateway');

		$envs = [
			[ 'value' => ApplicationModel::ENV_HOMOL, 'label' => 'Homologação' ],
			[ 'value' => ApplicationModel::ENV_PRODUCTION, 'label' => 'Produção' ]
		];

		$call_center = [
			[ 'value' => 'on_integrated', 'label' => 'Receber pedido do call center quando integrado' ],
			[ 'value' => 'on_paid', 'label' => 'Receber pedido do call center quando pago' ],
			[ 'value' => 'disabled', 'label' => 'Não receber pedidos do call center' ]
		];

		$env_label = '';
		$call_center_label = '';
		$waiting_status_label = '';
		$processing_status_label = '';
		$paid_status_label = '';

		foreach ($envs as $item) {
			if ($item['value'] === $settings->get('environment')) {
				$env_label = $item['label'];
				break;
			}
		}

		foreach ($call_center as $item) {
			if ($item['value'] === $settings->get('call_center')) {
				$call_center_label = $item['label'];
				break;
			}
		}

		foreach (\wc_get_order_statuses() as $key => $label) {
			$key = \str_replace('wc-', '', $key);

			if ($key === $settings->get('waiting_status')) {
				$waiting_status_label = $label;
			}

			if ($key === $settings->get('processing_status')) {
				$processing_status_label = $label;
			}

			if ($key === $settings->get('paid_status')) {
				$paid_status_label = $label;
			}
		}

		return [
			'debug_mode' => [$settings->get('debug_mode', false)],
			'hidden_mode' => [$settings->get('hidden_mode', false)],
			'environment' => [$settings->get('environment'), $env_label],
			'store_name' => [$settings->get('store_name')],
			'call_center' => [$settings->get('call_center'), $call_center_label],
			'waiting_message' => [$settings->get('waiting_message')],
			'paid_message' => [$settings->get('paid_message')],
			'waiting_status' => [$settings->get('waiting_status'), $waiting_status_label],
			'processing_status' => [$settings->get('processing_status'), $processing_status_label],
			'paid_status' => [$settings->get('paid_status'), $paid_status_label],
		];
	}

	/**
	 * Convert settings to form array.
	 *
	 * @since 0.1.0
	 * @return array
	 */
	public function boletoToFormArray(): array
	{
		/** @var KeyingBucket $settings */
		$settings = Connector::settings()->get('boleto');

		return [
			'enabled' => [$settings->get('enabled', 'no') === 'no' ? false : true],
			'title' => [$settings->get('title')],
			'description' => [$settings->get('description')],
			'instructions' => [$settings->get('instructions')],
			'due_date' => [$settings->get('due_date')],
			'expires_after' => [$settings->get('expires_after')],
			'decrease_stock' => [$settings->get('decrease_stock')],
			'must_expires' => [$settings->get('must_expires')],
		];
	}

	/**
	 * Convert settings to form array.
	 *
	 * @since 0.1.0
	 * @return array
	 */
	public function pixToFormArray(): array
	{
		/** @var KeyingBucket $settings */
		$settings = Connector::settings()->get('pix');

		return [
			'enabled' => [$settings->get('enabled', 'no') === 'no' ? false : true],
			'title' => [$settings->get('title')],
			'description' => [$settings->get('description')],
			'expires_in' => [$settings->get('expires_in')],
			'expires_after' => [$settings->get('expires_after')],
			'decrease_stock' => [$settings->get('decrease_stock')],
			'must_expires' => [$settings->get('must_expires')],
		];
	}

	/**
	 * Convert settings to form array.
	 *
	 * @since 0.1.0
	 * @return array
	 */
	public function creditCardToFormArray(): array
	{
		/** @var KeyingBucket $settings */
		$settings = Connector::settings()->get('credit_card');

		return [
			'enabled' => [$settings->get('enabled', 'no') === 'no' ? false : true],
			'title' => [$settings->get('title')],
			'description' => [$settings->get('description')],
			'max_installments' => [$settings->get('max_installments', 12)],
			'min_installment_amount' => [$settings->get('min_installment_amount', 5)],
			'interest_rate' => [$settings->get('interest_rate', 0.0)],
			'interest_incremental_rate' => [$settings->get('interest_incremental_rate', 0.0)],
			'interest_free_installments' => [$settings->get('interest_free_installments', 3)],
			'statement_descriptor' => [$settings->get('statement_descriptor', '')],
			'show_total' => [$settings->get('show_total', false)],
			'decrease_stock' => [$settings->get('decrease_stock', false)],
		];
	}

	/**
	 * Convert settings to form array.
	 *
	 * @since 0.1.0
	 * @return array
	 */
	public function processingToFormArray(): array
	{
		/** @var KeyingBucket $settings */
		$settings = Connector::settings()->get('processing');

		return [
			'cron_limit' => [$settings->get('cron_limit')],
			'sync_while_editing' => [$settings->get('cron_limit', true)],
		];
	}

	/**
	 * Save plugin settings.
	 *
	 *	@since 0.1.0
	 * @return void
	 */
	public function save_gateway()
	{
		try {
			$requestBody = new RequestBodyParser();

			if (!$requestBody->isPOST()) {
				throw new Exception('Método HTTP não disponível.', 405);
			}

			$body = $requestBody->body();
			$this->authorizationCheck($body);

			$parsed = BodyValidator::validate($body, [
				'environment' => ['required' => true],
				'debug_mode' => ['required' => false, 'default' => false, 'transform' => function ($value) {
					return \boolval($value);
				}],
				'hidden_mode' => ['required' => false, 'default' => false, 'transform' => function ($value) {
					return \boolval($value);
				}],
				'waiting_message' => ['required' => true],
				'paid_message' => ['required' => true],
				'store_name' => ['required' => true],
				'call_center' => ['required' => true],
				'waiting_status' => ['required' => true],
				'processing_status' => ['required' => true],
				'paid_status' => ['required' => true],
			]);

			(new PluginSettings())->save('gateway', $parsed);
			$this->success(['message' => 'Configurações salvas com sucesso...']);
		} catch (Exception $e) {
			$this->error($e);
		}
	}

	/**
	 * Save plugin settings.
	 *
	 *	@since 0.1.0
	 * @return void
	 */
	public function save_boleto()
	{
		try {
			$requestBody = new RequestBodyParser();

			if (!$requestBody->isPOST()) {
				throw new Exception('Método HTTP não disponível.', 405);
			}

			$body = $requestBody->body();
			$this->authorizationCheck($body);

			$parsed = BodyValidator::validate($body, [
				'enabled' => ['required' => false, 'default' => 'no', 'transform' => function ($value) {
					return $value ? 'yes' : 'no';
				}],
				'title' => ['required' => true],
				'description' => ['required' => true],
				'instructions' => ['required' => false],
				'due_date' => ['required' => true, 'transform' => function ($value) {
					return \intval($value);
				}],
				'expires_after' => ['required' => true, 'transform' => function ($value) {
					return \intval($value);
				}],
				'decrease_stock' => ['required' => false, 'default' => false, 'transform' => function ($value) {
					return \boolval($value);
				}],
				'must_expires' => ['required' => false, 'default' => false, 'transform' => function ($value) {
					return \boolval($value);
				}],
			]);

			(new PluginSettings())->save('boleto', $parsed);
			$this->success(['message' => 'Configurações salvas com sucesso...']);
		} catch (Exception $e) {
			$this->error($e);
		}
	}

	/**
	 * Save plugin settings.
	 *
	 *	@since 0.1.0
	 * @return void
	 */
	public function save_pix()
	{
		try {
			$requestBody = new RequestBodyParser();

			if (!$requestBody->isPOST()) {
				throw new Exception('Método HTTP não disponível.', 405);
			}

			$body = $requestBody->body();
			$this->authorizationCheck($body);

			$parsed = BodyValidator::validate($body, [
				'enabled' => ['required' => false, 'default' => 'no', 'transform' => function ($value) {
					return $value ? 'yes' : 'no';
				}],
				'title' => ['required' => true],
				'description' => ['required' => true],
				'expires_in' => ['required' => true, 'transform' => function ($value) {
					return \intval($value);
				}],
				'expires_after' => ['required' => true, 'transform' => function ($value) {
					return \intval($value);
				}],
				'decrease_stock' => ['required' => false, 'default' => false, 'transform' => function ($value) {
					return \boolval($value);
				}],
				'must_expires' => ['required' => false, 'default' => false, 'transform' => function ($value) {
					return \boolval($value);
				}],
			]);

			(new PluginSettings())->save('pix', $parsed);
			$this->success(['message' => 'Configurações salvas com sucesso...']);
		} catch (Exception $e) {
			$this->error($e);
		}
	}

	/**
	 * Save plugin settings.
	 *
	 *	@since 0.1.0
	 * @return void
	 */
	public function save_credit_card()
	{
		try {
			$requestBody = new RequestBodyParser();

			if (!$requestBody->isPOST()) {
				throw new Exception('Método HTTP não disponível.', 405);
			}

			$body = $requestBody->body();
			$this->authorizationCheck($body);

			$parsed = BodyValidator::validate($body, [
				'enabled' => ['required' => false, 'default' => 'no', 'transform' => function ($value) {
					return $value ? 'yes' : 'no';
				}],
				'title' => ['required' => true],
				'description' => ['required' => true],
				'max_installments' => ['required' => true, 'transform' => function ($value) {
					return \intval($value);
				}],
				'min_installment_amount' => ['required' => false, 'default' => 5, 'transform' => function ($value) {
					return \floatval($value);
				}],
				'interest_rate' => ['required' => false, 'default' => 0.00, 'transform' => function ($value) {
					return \floatval($value);
				}],
				'interest_incremental_rate' => ['required' => false, 'default' => 0.00, 'transform' => function ($value) {
					return \floatval($value);
				}],
				'interest_free_installments' => ['required' => false, 'default' => 3, 'transform' => function ($value) {
					return \intval($value);
				}],
				'statement_descriptor' => ['required' => false],
				'show_total' => ['required' => false, 'default' => false, 'transform' => function ($value) {
					return \boolval($value);
				}],
				'decrease_stock' => ['required' => false, 'default' => false, 'transform' => function ($value) {
					return \boolval($value);
				}],
			]);

			(new PluginSettings())->save('credit_card', $parsed);
			$this->success(['message' => 'Configurações salvas com sucesso...']);
		} catch (Exception $e) {
			$this->error($e);
		}
	}

	/**
	 * Save plugin settings.
	 *
	 *	@since 0.1.0
	 * @return void
	 */
	public function save_processing()
	{
		try {
			$requestBody = new RequestBodyParser();

			if (!$requestBody->isPOST()) {
				throw new Exception('Método HTTP não disponível.', 405);
			}

			$body = $requestBody->body();
			$this->authorizationCheck($body);

			$parsed = BodyValidator::validate($body, [
				'cron_limit' => ['required' => true, 'transform' => function ($value) {
					return \intval($value);
				}],
				'sync_while_editing' => ['required' => false, 'default' => true, 'transform' => function ($value) {
					return \boolval($value);
				}],
			]);

			Cron::destroy();
			Cron::create();

			(new PluginSettings())->save('processing', $parsed);
			$this->success(['message' => 'Configurações salvas com sucesso...']);
		} catch (Exception $e) {
			$this->error($e);
		}
	}

	/**
	 * Get nonce action name.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public static function nonceAction(): string
	{
		return Connector::plugin()->getName().'_nonce';
	}
}
