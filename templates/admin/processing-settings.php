<?php

use AppMax\WooCommerce\Gateway\Core\Cron;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\ExtendedCheckboxInputField;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\Form;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Post\Fields\IntegerInputField;

$settings = Connector::settings()->get('processing');

$form = new Form(
	[
		'name' => Connector::domain(),
		'id' => Connector::domain(),
		'action' => \admin_url('admin-ajax.php') . '?action=' . Connector::plugin()->getName() . '_save_processing',
		'attrs' => [
			'data-x-security="'.wp_create_nonce(static::nonceAction()).'"'
		],
		'method' => 'POST',
		'submit' => 'Salvar Configurações',
	],
	[
		[
			new IntegerInputField([
				'name' => 'cron_limit',
				'label' => 'Tarefas por Ciclo',
				'description' => 'Quantidade máxima de tarefas agendadas que serão executadas por ciclo.',
				'default' => $settings->get('cron_limit', 10),
				'required' => true,
				'column_size' => 12,
			]),
		],
		[
			new ExtendedCheckboxInputField([
				'name' => 'sync_while_editing',
				'label' => 'Sincronização de Produto ao Editar',
				'placeholder' => 'Ativar',
				'description' => 'Habilite para sincronizar o produto imediatamente ao publicá-lo ou atualizá-lo. Caso o sistema apresente lentidão, desabilite esse recurso.',
				'default' => $settings->get('sync_while_editing', true),
				'required' => false,
				'column_size' => 12,
			]),
		]
	]
);

?>

<?php
$fix_cron = filter_input(INPUT_GET, 'fix_cron', FILTER_VALIDATE_INT);

if (!empty($fix_cron) && $fix_cron === 1) {
	Cron::create();
	?>
	<div class="pgly-wps--row">
		<div class="pgly-wps--column">
			<p class="pgly-wps--notification pgly-wps-is-success">
				Os <strong>eventos agendados</strong> foram recriados corretamente. Atualize a página para validar.
			</p>
		</div>
	</div>
	<?php
}
?>

<div id="main-toaster" class="pgly-wps--toaster pgly-wps-in-s"></div>
<img
	src="<?php echo esc_url(Connector::plugin()->getUrl().'assets/images/appmax-logo.png');?>"
	alt="Logo"
	style="width: 100%; max-width: 160px; margin: 0; display: block;"
>

<h1 class="pgly-wps--title">Configurações de Processamento</h1>
<div class="pgly-wps--row">
	<div class="pgly-wps--column">
		<p class="pgly-wps--notification pgly-wps-is-info">
			Para processar os pagamentos, crie uma tarefa agendada em seu servidor com o comando
			<code>wget <?php echo(esc_url(home_url('wp-cron.php?doing_cron=true'))); ?> &gt; /dev/null 2&gt;&amp;1</code>
			para ser executado a cada 1 minutos <code>*/1 * * * *</code>. Não recomendamos manter esse trabalho no
			Wordpress, pois poderá causar lentidão nos períodos de verificação ou demorar demasiadamente para atualizar
			pedidos.
		</p>
	</div>
</div>

<div class="pgly-wps--row">
	<div class="pgly-wps--column">
		<p class="pgly-wps--notification pgly-wps-is-danger">
			Esse plugin suporta a leitura de Webhooks da AppMax. Para receber webhooks, configure um novo webhook
			marcando todos os eventos de cobrança na AppMax apontando para a URL:
			<code><?php echo rest_url('appmax/webhooks/v1'); ?></code>
		</p>
	</div>
</div>

<h2>Pagamentos</h2>
<div class="pgly-wps--row">
	<div class="pgly-wps--column">
		<p class="pgly-wps--notification pgly-wps-is-info" style="width: 100%">
			Para otimização do processamento, os webhooks serão prioritários. Se o webhook for recebido, o pedido será
			processado automaticamente. Caso contrário, o pedido será processado de acordo com a configuração abaixo.
			<br><br>
			<strong>Boleto</strong>: A cada 30 minutos.
			<br>
			<strong>Pix</strong>: A cada 2 minutos.
			<br>
			<strong>Cartão de Crédito</strong>: A cada 2 minutos.
		</p>
	</div>
</div>

<h2>Recriar Cronjobs</h2>
<div class="pgly-wps--row">
	<div class="pgly-wps--column">
		<p class="pgly-wps--notification pgly-wps-is-info" style="width: 100%">
			<strong>Como saber se as cronjobs estão funcionando?</strong>
			<br><br>
			<strong>1.</strong>: Ative o modo debug temporariamente e verifique a linha <code>Cronjob iniciando a execução</code> nos arquivos de log.
		</p>
	</div>
</div>

<div class="pgly-wps--row">
	<div class="pgly-wps--column">
		<p class="pgly-wps--notification pgly-wps-is-warning" style="width: 100%">
			Se você estiver enfrentando problemas com o processamento de pedidos, tente recriar os cronjobs.
			Siga os seguintes procedimentos:
			<br><br>
			<strong>1.</strong>: Clique em "Salvar Configurações" para salvar as configurações atuais.
			<br>
			<strong>2.</strong>: instale temporariamente o plugin <strong>WP Crontrol</strong> e ative-o. Depois de
			ativado, acesse o menu <strong>Ferramentas > Eventos Cron</strong> do Wordpress e siga as instruções abaixo:
			<br>
			<strong>3.</strong>: Localize os eventos <code>cron_pagamentos_para_woocommerce_com_appmax_order_processing_</code>, você pode
			inserí-lo na caixa de pesquisa para localizar mais facilmente.
			<br>
			<strong>4.</strong>: Depois de localizá-lo, passe o mouse sobre todos eles e clique em “Excluir”. Quando
			você excluir, este e todos os eventos filhos serão removidos.
			<br>
			<strong>5.</strong>: Salve novamente as configurações abaixo.
			<br>
			<strong>6.</strong>: Volte para <strong>Ferramentas > Eventos Cron</strong> e verifique se o evento
			<code>cron_pagamentos_para_woocommerce_com_appmax_order_processing_</code> está agora com a frequência correta.
			<br>
			<strong>7.</strong>: Desinstale o plugin <strong>WP Crontrol</strong> e desative-o.
		</p>
	</div>
</div>

<?=$form->render($this->processingToFormArray());?>

<script>
	document.addEventListener('DOMContentLoaded', () => {
		(new appMaxGateway.App()).processingSettings();
	});
</script>

<style>
	.pgly-wps--title {
		margin: 24px 0 32px !important;
	}
</style>