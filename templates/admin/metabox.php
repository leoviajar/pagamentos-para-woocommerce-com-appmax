<?php

use AppMax\WooCommerce\Gateway\Core\Configuration;
use AppMax\WooCommerce\Gateway\Core\Helpers\OrderExtractor;
use AppMax\WooCommerce\Gateway\Core\Records\Enums\PaymentMethodEnum;
use AppMax\WooCommerce\Gateway\Core\Records\Enums\PaymentStatusEnum;
use AppMax\WooCommerce\Gateway\Core\Records\PaymentRecord;
use AppMax\WooCommerce\Gateway\Core\Repositories\PaymentsRepo;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;

if (! defined('ABSPATH')) {
	exit;
}

global $post;
/** @var PaymentRecord $payment */
$payment = !empty($payment) ? $payment : PaymentsRepo::byOrderId(\wc_get_order($post->ID));

if (empty($payment)) : ?>
<h3 style="text-align: center" class="pgly-wps--title pgly-wps-is-7">Pagamento Indisponível</h3>
<div class="pgly-wps--notification pgly-wps-is-warning">
	Nenhum pagamento está associado ao pedido.
</div>
<?php else : ?>
<img src="<?php echo esc_url(Connector::plugin()->getUrl().'assets/images/appmax-logo.png');?>"
	alt="Logo" style="width: 100%; max-width: 120px; margin: 32px auto; display: block;">

<h3 style="text-align: center" class="pgly-wps--title pgly-wps-is-7">
	<?php echo PaymentMethodEnum::label($payment->paymentMethod());?>
</h3>

<?php if ($payment->isPaid()) : ?>
<div class="pgly-wps--explorer pgly-wps-is-compact pgly-wps-is-success">
	<strong>Valor Pago</strong>
	<span><?php echo 'R$ '. \number_format($payment->amount(), 2, ',', '.');?></span>
</div>
<?php endif; ?>

<div class="pgly-wps--explorer pgly-wps-is-compact">
	<strong>CNPJ da Loja</strong>
	<span><?php echo esc_html($payment->cnpj()); ?></span>
</div>
<div class="pgly-wps--explorer pgly-wps-is-compact">
	<strong>Valor da Cobrança</strong>
	<span><?php echo 'R$ '. \number_format($payment->amount(), 2, ',', '.');?></span>
</div>
<div class="pgly-wps--explorer pgly-wps-is-compact">
	<strong>Status</strong>
	<span><?php echo PaymentStatusEnum::label($payment->status());?></span>
</div>

<h4 style="text-align: center" class="pgly-wps--title pgly-wps-is-8">Dados na AppMax</h4>

<div class="pgly-wps--explorer pgly-wps-is-compact">
	<strong>ID do Pedido</strong>
	<span><?php echo esc_html($payment->appMaxOrderId());?></span>
</div>
<div class="pgly-wps--explorer pgly-wps-is-compact">
	<strong>ID do Cliente</strong>
	<span><?php echo esc_html($payment->appMaxCustomerId());?></span>
</div>
<div class="pgly-wps--explorer pgly-wps-is-compact">
	<strong>ID do Site</strong>
	<span><?php echo esc_html($payment->appMaxSiteId());?></span>
</div>
<div class="pgly-wps--explorer pgly-wps-is-compact">
	<strong>ID do Pagamento</strong>
	<span><?php echo esc_html($payment->payReference());?></span>
</div>

<?php if ($payment->isWaiting()) : ?>
<p style="margin: 16px auto; text-align: center;">Verifique o status do pagamento na AppMax com o botão abaixo:</p>
<button id="pagamentos-para-woocommerce-com-appmax-payment-check" class="pgly-wps--button pgly-async--behaviour pgly-wps-is-primary"
	data-id="<?php echo $payment->id();?>"
	data-action="<?php echo \admin_url('admin-ajax.php') . '?action=' . Connector::plugin()->getName() . '_process';?>"
	data-x-security="<?php echo wp_create_nonce(Configuration::nonceAction())?>">
	Verificar Pagamento
	<svg class="pgly-wps--spinner pgly-wps-is-white" viewBox="0 0 50 50">
		<circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
	</svg>
</button>
<?php else : ?>
<h3 style="text-align: center" class="pgly-wps--title pgly-wps-is-7">
	Código de Rastreamento
</h3>

<div class="pgly-wps--notification pgly-wps-is-warning">
	Mantenha seu pedido atualizado informando o código de rastreio que deve ser enviado para a AppMax.
</div>

<?php
woocommerce_wp_text_input(array(
	'id' => 'appmax_tracking_code',
	'label' => 'Código de Rastreio:',
	'value' => OrderExtractor::solveTrackingCode($payment->order()),
	'wrapper_class' => 'form-field-wide'
));
	?>
<?php endif; ?>


<div id="pagamentos-para-woocommerce-com-appmax-notification"></div>

<script>
	document.addEventListener('DOMContentLoaded', () => {
		(new appMaxGateway.App()).metabox();
	});
</script>
<?php
	endif;
?>