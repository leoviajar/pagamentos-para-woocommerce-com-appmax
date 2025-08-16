<?php

use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\ExtendedCheckboxInputField;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\Form;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\IntegerInputField;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\TextInputField;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\TitleComponent;

$settings = Connector::settings()->get('boleto');

$form = new Form(
	[
		'name' => Connector::domain(),
		'id' => Connector::domain(),
		'action' => \admin_url('admin-ajax.php') . '?action=' . Connector::plugin()->getName() . '_save_boleto',
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
				'default' => $settings->get('title', 'Boleto Bancário'),
				'required' => true,
				'column_size' => 6,
			]),
			new TextInputField([
				'name' => 'description',
				'label' => 'Breve descrição de pagamento',
				'description' => 'A descrição será exibida junto com o título',
				'default' => $settings->get('description', 'Pague o seu boleto e receba o pedido assim que o pagamento for compensado.'),
				'required' => true,
				'column_size' => 6,
			]),
		],
		[
			new TextInputField([
				'name' => 'instructions',
				'label' => 'Instruções de pagamento',
				'description' => 'As instruções ficam logo abaixo da descrição e podem detalhar mais o processo de pagamento',
				'default' => $settings->get('instructions', ''),
				'required' => false,
				'column_size' => 12,
			]),
		],
		[
			new TitleComponent(['content' => 'Vencimento / Expiração / Estoque', 'level' => 2, 'size' => 4]),
		],
		[
			new IntegerInputField([
				'name' => 'due_date',
				'label' => 'Período de Vencimento',
				'description' => 'Informe o número de dias a partir da data de emissão que o boleto será vencido',
				'default' => $settings->get('due_date', '3'),
				'required' => true,
				'column_size' => 12,
			]),
			new IntegerInputField([
				'name' => 'expires_after',
				'label' => 'Cancelamento após Vencimento (em dias)',
				'description' => 'Informe o número de dias a partir da data de vencimento que o pedido será cancelado internamente caso o pagamento não seja concluído',
				'default' => $settings->get('expires_after', '3'),
				'required' => true,
				'column_size' => 6,
			]),
		],
		[
			new ExtendedCheckboxInputField([
				'name' => 'must_expires',
				'label' => 'Habilitar a expiração do boleto',
				'placeholder' => 'Ativar',
				'description' => 'Quando desabilitado, um boleto nunca será expirado e aguardará a notificação da AppMax para expiração',
				'default' => $settings->get('must_expires'),
				'required' => false,
				'column_size' => 6,
			]),
			new ExtendedCheckboxInputField([
				'name' => 'decrease_stock',
				'label' => 'Habilitar decréscimo no estoque',
				'placeholder' => 'Ativar',
				'description' => 'Quando habilitado, mesmo que o boleto não tenha sido pago, o estoque será retido até que ele seja pago ou cancelado',
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

<h1 class="pgly-wps--title">Configurações do Boleto</h1>
<?=$form->render($this->boletoToFormArray());?>

<script>
	document.addEventListener('DOMContentLoaded', () => {
		(new appMaxGateway.App()).boletoSettings();
	});
</script>

<style>
	.pgly-wps--title {
		margin: 24px 0 32px !important;
	}
</style>