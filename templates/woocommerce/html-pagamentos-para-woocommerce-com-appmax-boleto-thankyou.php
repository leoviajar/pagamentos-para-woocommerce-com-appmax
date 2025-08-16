<?php

use AppMax\WooCommerce\Gateway\Core\Records\PaymentRecord;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Settings\KeyingBucket;

if (! defined('ABSPATH')) {
	exit;
}

/** @var KeyingBucket $settings */
$settings = Connector::settings()->get('boleto');
/** @var PaymentRecord $payment */
?>
<style>
	.pgly-item .boleto-code { word-break: break-all; display: table; font-size: 12px; }
</style>
<div id="pagamentos-para-woocommerce-com-appmax-boleto">
	<h3><?=wpautop($settings->get('description'))?></h3>
	<div class="pagamentos-para-woocommerce-com-appmax--row pagamentos-para-woocommerce-com-appmax--review">
		<div class="pagamentos-para-woocommerce-com-appmax--column">
			<div class="pagamentos-para-woocommerce-com-appmax--item">
				<div class="pagamentos-para-woocommerce-com-appmax--centered pagamentos-para-woocommerce-com-appmax--space">
					<?=wpautop($settings->get('instructions'))?>
				</div>
				<span class="pagamentos-para-woocommerce-com-appmax--label">
					Valor do Pedido
				</span>
				<span class="pagamentos-para-woocommerce-com-appmax--data">
					R$ <?=\wc_format_decimal($order->get_total(), 2);?>
				</span>
				<?php if (!empty($payment->expiresAt())) : ?>
				<span class="pagamentos-para-woocommerce-com-appmax--label">
					Data de Vencimento
				</span>
				<span class="pagamentos-para-woocommerce-com-appmax--data">
					<?=$payment->expiresAt()->format('d/m/Y')?>
				</span>
				<?php endif; ?>
			</div>
		</div>
	</div>


	<div class="pagamentos-para-woocommerce-com-appmax--row pagamentos-para-woocommerce-com-appmax--manual">
		<div class="pagamentos-para-woocommerce-com-appmax--column">
			<p>
				Copie a <strong>Linha Digitável</strong> abaixo e insira na opção
				<strong>Pagamento de Boletos</strong> no aplicativo <strong>do seu banco</strong>
				para realizar o pagamento:
			</p>
			<div class="pagamentos-para-woocommerce-com-appmax--item">
				<span class="pagamentos-para-woocommerce-com-appmax--label">
					Linha Digitável
				</span>
				<span class="pagamentos-para-woocommerce-com-appmax--data">
					<span><?=$payment->paymentCode();?></span>
				</span>
				<button
					class="pagamentos-para-woocommerce-com-appmax--copy button copy">
					Copiar Linha Digitável
				</button>
			</div>
		</div>
	</div>

	<div class="pagamentos-para-woocommerce-com-appmax--row">
		<div class="pagamentos-para-woocommerce-com-appmax--column pagamentos-para-woocommerce-com-appmax--or">
			<span>OU</span>
		</div>
	</div>

	<div class="pagamentos-para-woocommerce-com-appmax--row pagamentos-para-woocommerce-com-appmax--manual">
		<div class="pagamentos-para-woocommerce-com-appmax--column" style="text-align: center">
			<div class="pagamentos-para-woocommerce-com-appmax--centered pagamentos-para-woocommerce-com-appmax--space">
				<p>
					Clique abaixo para realizar a impressão do boleto bancário:
				</p>
			</div>
			<a target="_blank" href="<?=$payment->media()?>"
				class="button copy" style="margin: 32px 0 0;">
				Imprimir Boleto
			</a>
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
					setTimeout(() => el.textContent = 'Copiar Linha Digitável', 2000);
				}
			}

			document.querySelectorAll(".pagamentos-para-woocommerce-com-appmax--copy").forEach(el => el.addEventListener("click", () => copyDigitable(el)));
		});
	</script>
</div>