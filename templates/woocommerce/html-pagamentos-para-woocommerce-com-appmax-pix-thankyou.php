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
<style>
	.pgly-item .pix-code { word-break: break-all; display: table; font-size: 12px; }
	.pgly-item .qr-code { display: table; width: 160px; }
</style>
<style>
	.pagamentos-para-woocommerce-com-appmax--code { word-break: break-all; display: table; font-size: 12px; }
</style>
<div id="pagamentos-para-woocommerce-com-appmax-pix">
<?php if ($payment->isWaiting()) : ?>
<div class="pagamentos-para-woocommerce-com-appmax--row pagamentos-para-woocommerce-com-appmax--qrcode">
		<div class="pagamentos-para-woocommerce-com-appmax--column pagamentos-para-woocommerce-com-appmax--col-2">
			<p>
				Leia o <strong>QRCode</strong> abaixo com o aplicativo
				<strong>do seu banco</strong> e realize o pagamento do Pix para continuar:
			</p>
			<img src="<?php echo $payment->media(); ?>"/>
		</div>
		<div class="pagamentos-para-woocommerce-com-appmax--column pagamentos-para-woocommerce-com-appmax--col-2">
			<div class="pagamentos-para-woocommerce-com-appmax--waiting-bar">
				<svg
					id="pagamentos-para-woocommerce-com-appmax-payment-loader"
					class="pgly-wps--spinner pgly-wps-is-black"
					viewBox="0 0 50 50">
					<circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
				</svg>
				<p>Aguardando o pagamento...</p>
			</div>
			<p>O QRCode irá expirar em:</p>
			<div id="pagamentos-para-woocommerce-com-appmax-countdown" data-order-id="<?php echo $payment->orderId();?>" data-redirect-to="<?php echo $payment->order()->get_checkout_order_received_url(); ?>" data-seconds="<?php echo Connector::settings()->get('pix')->get('expires_in'); ?>">59:59</div>
			<p class="pagamentos-para-woocommerce-com-appmax--waiting">
				Após finalizar o pagamento, você pode continuar nessa página para
				aguardar a confirmação do pagamento ou fechá-la. Você receberá uma
				notificação assim que o pagamento for confirmado.
			</p>
		</div>
	</div>

	<div class="pagamentos-para-woocommerce-com-appmax--row">
		<div class="pagamentos-para-woocommerce-com-appmax--column pagamentos-para-woocommerce-com-appmax--or">
			<span>OU</span>
		</div>
	</div>

	<div class="pagamentos-para-woocommerce-com-appmax--row pagamentos-para-woocommerce-com-appmax--manual">
		<div class="pagamentos-para-woocommerce-com-appmax--column">
			<p>
				Copie o <strong>Código Pix</strong> abaixo e insira na opção
				<strong>Pix Copia e Cola</strong> no aplicativo <strong>do seu banco</strong>
				para realizar o pagamento do Pix:
			</p>
			<div class="pagamentos-para-woocommerce-com-appmax--item">
				<span class="pagamentos-para-woocommerce-com-appmax--label">
					Pix Copia&Cola
				</span>
				<span class="pagamentos-para-woocommerce-com-appmax--data">
					<span class="pagamentos-para-woocommerce-com-appmax--code"><?=$payment->paymentCode();?></span>
				</span>
				<button
					class="pagamentos-para-woocommerce-com-appmax--copy button copy">
					Copiar Pix Copia&Cola
				</button>
			</div>
		</div>
	</div>

	<script>
		document.addEventListener('DOMContentLoaded', () => {
			const copyDigitable = async (el) => {
				text = '<?=$payment->paymentCode();?>';

				try {
					await navigator.clipboard.writeText(text);
				} catch (err) {
					//use document.execCommand('copy') as fallback
					const textArea = document.createElement("textarea");
					textArea.value = text;
					document.body.appendChild(textArea);
					textArea.focus();
					textArea.select();
					document.execCommand("copy");
					document.body.removeChild(textArea);
				} finally {
					el.textContent = 'Copiado!';
					setTimeout(() => el.textContent = 'Copiar Pix Copia&Cola', 2000);
				}
			}

			document.querySelectorAll(".pagamentos-para-woocommerce-com-appmax--copy").forEach(el => el.addEventListener("click", () => copyDigitable(el)));
		});
	</script>
</div>
<?php elseif ($payment->isPaid()) : ?>
	<h3 style="text-align: center">
		Seu pagamento via Pix foi recebido com sucesso!
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