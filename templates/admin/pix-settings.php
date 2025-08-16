<?php

use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\ExtendedCheckboxInputField;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\Form;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\IntegerInputField;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\TextInputField;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\TitleComponent;

$settings = Connector::settings()->get('pix');

$form = new Form(
	[
		'name' => Connector::domain(),
		'id' => Connector::domain(),
		'action' => \admin_url('admin-ajax.php') . '?action=' . Connector::plugin()->getName() . '_save_pix',
		'attrs' => [
			'data-x-security="'.wp_create_nonce(static::nonceAction()).'"'
		],
		'method' => 'POST',
		'submit' => 'Salvar Configurações',
	],
	[
		[
			new TitleComponent(['content' => 'Configurações Gerais', 'level' => 2, 'size' => 4]),
		],
		[
			new ExtendedCheckboxInputField([
				'name' => 'enabled',
				'label' => 'Habilitar método de pagamento',
				'placeholder' => 'Ativar',
				'description' => 'Habilite para que o método esteja disponível para pagamento',
				'default' => $settings->get('enabled'),
				'required' => false,
				'column_size' => 12,
			]),
		],
		[
			new TextInputField([
				'name' => 'title',
				'label' => 'Título do método de pagamento',
				'description' => 'Título que será exibido na loja',
				'default' => $settings->get('title', 'Pix'),
				'required' => true,
				'column_size' => 6,
			]),
			new TextInputField([
				'name' => 'description',
				'label' => 'Breve descrição de pagamento',
				'description' => 'A descrição será exibida junto com o título',
				'default' => $settings->get('description', 'Pague o seu Pix e receba o pedido assim que o pagamento for compensado.'),
				'required' => true,
				'column_size' => 6,
			]),
		],
		[
			new TitleComponent(['content' => 'Vencimento / Expiração / Estoque', 'level' => 2, 'size' => 4]),
		],
		[
			new IntegerInputField([
				'name' => 'expires_in',
				'label' => 'Período de Expiração',
				'description' => 'Informe o número de segundos a partir da data de emissão que o pix será expirado',
				'default' => $settings->get('expires_in', '3'),
				'required' => true,
				'column_size' => 6,
			]),
			new IntegerInputField([
				'name' => 'expires_after',
				'label' => 'Cancelamento após Vencimento (em dias)',
				'description' => 'Informe o número de dias a partir da data de vencimento que o pedido será cancelado internamente caso o pagamento não seja concluído',
				'default' => $settings->get('expires_after', '1'),
				'required' => true,
				'column_size' => 6,
			]),
		],
		[
			new ExtendedCheckboxInputField([
				'name' => 'must_expires',
				'label' => 'Habilitar a expiração do pix',
				'placeholder' => 'Ativar',
				'description' => 'Quando desabilitado, um pix nunca será expirado e aguardará a notificação da AppMax para expiração',
				'default' => $settings->get('must_expires'),
				'required' => false,
				'column_size' => 6,
			]),
			new ExtendedCheckboxInputField([
				'name' => 'decrease_stock',
				'label' => 'Habilitar decréscimo no estoque',
				'placeholder' => 'Ativar',
				'description' => 'Quando habilitado, mesmo que o pix não tenha sido pago, o estoque será retido até que ele seja pago ou cancelado',
				'default' => $settings->get('decrease_stock'),
				'required' => false,
				'column_size' => 6,
			]),
		],
	]
);

?>

<div id="main-toaster" class="pgly-wps--toaster pgly-wps-in-s"></div>
<img
	src="<?php echo esc_url(Connector::plugin()->getUrl().'assets/images/appmax-logo.png');?>"
	alt="Logo"
	style="width: 100%; max-width: 160px; margin: 0; display: block;"
>

<h1 class="pgly-wps--title">Configurações do Pix</h1>
<?=$form->render($this->pixToFormArray());?>

<script>
	document.addEventListener('DOMContentLoaded', () => {
		(new appMaxGateway.App()).pixSettings();
	});
</script>

<style>
	.pgly-wps--title {
		margin: 24px 0 32px !important;
	}
</style>