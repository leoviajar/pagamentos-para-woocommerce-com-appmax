<?php

use AppMax\WooCommerce\Gateway\Core\Records\PaymentRecord;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Settings\KeyingBucket;

if (! defined('ABSPATH')) {
	exit;
}

/** @var KeyingBucket $settings */
$settings = Connector::settings()->get('gateway');
/** @var PaymentRecord $payment */

?>
<div id="pagamentos-para-woocommerce-com-appmax-credit_card">
<?php if ($payment->isWaiting()) : ?>
	<div class="pagamentos-para-woocommerce-com-appmax--row pagamentos-para-woocommerce-com-appmax--qrcode">
		<div class="pagamentos-para-woocommerce-com-appmax--column">
			<p><?php echo esc_html($settings->get('waiting_message')); ?></p>
		</div>
	</div>
<?php elseif ($payment->isPaid()) : ?>
	<h3 style="text-align: center">
		Seu pagamento via Cartão de Crédito foi recebido com sucesso!
	</h3>

	<div class="pagamentos-para-woocommerce-com-appmax--row pagamentos-para-woocommerce-com-appmax--qrcode">
		<div class="pagamentos-para-woocommerce-com-appmax--column">
			<p><?php echo esc_html($settings->get('paid_message')); ?></p>
		</div>
	</div>
<?php else : ?>
	<h3 style="text-align: center">
		Esse pedido foi <strong>cancelado</strong>!
	</h3>

	<div class="pagamentos-para-woocommerce-com-appmax--row pagamentos-para-woocommerce-com-appmax--qrcode">
		<div class="pagamentos-para-woocommerce-com-appmax--column">
			<p>
				Esse pedido não está mais disponível para pagamento, ele foi cancelado, realize um novo pedido para continuar com a sua compra.
			</p>
		</div>
	</div>
<?php endif; ?>
</div>