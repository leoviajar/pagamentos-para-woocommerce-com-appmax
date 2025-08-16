<?php

use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\ExtendedCheckboxInputField;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\ExtendedSelectInputField;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\Form;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\NoticeComponent;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\TextInputField;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\TitleComponent;

$settings = Connector::settings()->get('gateway');

$form = new Form(
	[
		'name' => Connector::domain(),
		'id' => Connector::domain(),
		'action' => \admin_url('admin-ajax.php') . '?action=' . Connector::plugin()->getName() . '_save_global',
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
			new NoticeComponent([
				'content' => 'O modo debug, quando ativado, irá retornar mensagens de erros brutas e produzirá mensagens em excesso nos logs do plugin. Mantenha habilitado somente quando precisar testar e validar algum problema. Você também pode utilizar o <strong>Modo Oculto</strong> esse modo permite que apenas você veja a opção de pagamento da AppMax, impedindo que erros sejam provocados para os clientes até que você teste tudo.',
				'color' => 'warning',
			]),
		],
		[
			new ExtendedCheckboxInputField([
				'name' => 'debug_mode',
				'label' => 'Modo Debug',
				'placeholder' => 'Ativar',
				'description' => 'Habilite para mapear erros e investigar bugs, mantenha desabilitado dentro do comportamento normal do plugin para evitar que mensagens de log em excesso sejam produzidas e erros sem tratamento sejam exibidos para os clientes',
				'default' => $settings->get('debug_mode'),
				'required' => false,
				'column_size' => 6,
			]),
			new ExtendedCheckboxInputField([
				'name' => 'hidden_mode',
				'label' => 'Modo Oculto',
				'placeholder' => 'Ativar',
				'description' => 'O modo oculto permite que você teste o plugin sem que os clientes vejam a opção de pagamento da AppMax, impedindo que erros sejam provocados para os clientes até que você teste tudo',
				'default' => $settings->get('hidden_mode'),
				'required' => false,
				'column_size' => 6,
			]),
		],
		[
			new TitleComponent(['content' => 'Integração', 'level' => 2, 'size' => 4]),
		],
		[
			new ExtendedSelectInputField([
				'name' => 'environment',
				'label' => 'Ambiente de Integração',
				'placeholder' => 'Selecione um ambiente',
				'description' => 'Escolha o ambiente de integração desejado, associe as credenciais de acordo com o ambiente selecionado',
				'default' => $settings->get('environment'),
				'required' => true,
				'column_size' => 12,
			]),
		],
		[
			new TitleComponent(['content' => 'Mensagens de Resposta', 'level' => 2, 'size' => 4]),
		],
		[
			new TextInputField([
				'name' => 'waiting_message',
				'label' => 'Mensagem para "Aguardando Pagamento"',
				'description' => 'Mensagem exibida na página de obrigado quando o pagamento ainda estiver em processamento e ainda não tiver sido confirmado.',
				'default' => $settings->get('waiting_message'),
				'required' => true,
				'column_size' => 12,
			]),
		],
		[
			new TextInputField([
				'name' => 'paid_message',
				'label' => 'Mensagem para "Pagamento Concluído"',
				'description' => 'Mensagem exibida na página de obrigado quando o pagamento já tiver sido confirmado.',
				'default' => $settings->get('paid_message'),
				'required' => true,
				'column_size' => 12,
			]),
		],
		[
			new TitleComponent(['content' => 'Dados da Loja', 'level' => 2, 'size' => 4]),
		],
		[
			new TextInputField([
				'name' => 'store_name',
				'label' => 'Nome da Loja',
				'description' => 'Nome da loja vinculada a AppMax',
				'default' => $settings->get('store_name'),
				'required' => true,
				'column_size' => 6,
			]),
			new ExtendedSelectInputField([
				'name' => 'call_center',
				'label' => 'Sincronização do Call Center',
				'placeholder' => 'Selecione um método',
				'description' => 'Selecione o método de sincronização com o call center que será utilizado',
				'default' => $settings->get('call_center'),
				'required' => true,
				'column_size' => 6,
			]),
		],
		[
			new TitleComponent(['content' => 'Comportamento do Pedido', 'level' => 2, 'size' => 4]),
		],
		[
			new ExtendedSelectInputField([
				'name' => 'waiting_status',
				'label' => 'Status para "Aguardando Pagamento"',
				'placeholder' => 'Selecione um status',
				'description' => 'Selecione o status que será definido ao aguardar pagamento',
				'default' => $settings->get('waiting_status'),
				'required' => true,
				'column_size' => 4,
			]),
			new ExtendedSelectInputField([
				'name' => 'processing_status',
				'label' => 'Status para "Processando o Pagamento"',
				'placeholder' => 'Selecione um status',
				'description' => 'Selecione o status que será definido enquanto o pagamento está em processamento',
				'default' => $settings->get('processing_status'),
				'required' => true,
				'column_size' => 4,
			]),
			new ExtendedSelectInputField([
				'name' => 'paid_status',
				'label' => 'Status para "Pago"',
				'placeholder' => 'Selecione um status',
				'description' => 'Selecione o status que será definido quando o pagamento for concluído',
				'default' => $settings->get('paid_status'),
				'required' => true,
				'column_size' => 4,
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

<h1 class="pgly-wps--title">Configurações do Plugin</h1>

<?=$form->render($this->globalToFormArray());?>

<script>
	document.addEventListener('DOMContentLoaded', () => {
		(new appMaxGateway.App()).settings();
	});
</script>

<style>
	.pgly-wps--title {
		margin: 24px 0 32px !important;
	}
</style>