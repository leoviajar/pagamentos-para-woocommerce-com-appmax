<?php

use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\ExtendedCheckboxInputField;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\FloatInputField;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\Form;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\IntegerInputField;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\TextInputField;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\TitleComponent;

$settings = Connector::settings()->get('credit_card');

$form = new Form(
	[
		'name' => Connector::domain(),
		'id' => Connector::domain(),
		'action' => \admin_url('admin-ajax.php') . '?action=' . Connector::plugin()->getName() . '_save_credit_card',
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
				'column_size' => 4,
			]),
			new TextInputField([
				'name' => 'description',
				'label' => 'Breve descrição de pagamento',
				'description' => 'A descrição será exibida junto com o título',
				'default' => $settings->get('description', 'Pague o seu boleto e receba o pedido assim que o pagamento for compensado.'),
				'required' => true,
				'column_size' => 4,
			]),
			new TextInputField([
				'name' => 'statement_descriptor',
				'label' => 'Soft Descriptor',
				'description' => 'Descrição que aparecerá na fatura do cartão depois do nome de sua empresa. Máximo de 13 caracteres.',
				'default' => $settings->get('statement_descriptor'),
				'required' => true,
				'column_size' => 4,
			]),
		],
		[
			new TitleComponent(['content' => 'Parcelamento', 'level' => 2, 'size' => 4]),
		],
		[
			new IntegerInputField([
				'name' => 'max_installments',
				'label' => 'Número de Parcelas',
				'description' => 'Informe o número máximo de parcelas que podem ser aplicadas',
				'default' => $settings->get('max_installments', 12),
				'required' => true,
				'column_size' => 12,
			]),
		],
		[
			new ExtendedCheckboxInputField([
				'name' => 'show_total',
				'label' => 'Habilitar exibição do valor total em parcelas',
				'placeholder' => 'Ativar',
				'description' => 'Quando habilitado, irá exibir o valor do total do pedido após o parcelamento em cada parcela selecionável',
				'default' => $settings->get('show_total'),
				'required' => false,
				'column_size' => 12,
			]),
		],
		[
			new TitleComponent(['content' => 'Estoque', 'level' => 2, 'size' => 4]),
		],
		[
			new ExtendedCheckboxInputField([
				'name' => 'decrease_stock',
				'label' => 'Habilitar decréscimo no estoque',
				'placeholder' => 'Ativar',
				'description' => 'Quando habilitado, mesmo que o boleto não tenha sido pago, o estoque será retido até que ele seja pago ou cancelado',
				'default' => $settings->get('decrease_stock'),
				'required' => false,
				'column_size' => 12,
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

<h1 class="pgly-wps--title">Configurações do Cartão de Crédito</h1>
<?=$form->render($this->creditCardToFormArray());?>

<script>
	document.addEventListener('DOMContentLoaded', () => {
		(new appMaxGateway.App()).creditCardSettings();
	});
</script>

<style>
	.pgly-wps--title {
		margin: 24px 0 32px !important;
	}
</style>