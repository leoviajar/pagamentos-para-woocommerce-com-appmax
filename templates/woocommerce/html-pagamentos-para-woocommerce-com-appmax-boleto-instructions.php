<?php

use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Settings\KeyingBucket;

if (! defined('ABSPATH')) {
	exit;
}

/** @var KeyingBucket $settings */
$settings = Connector::settings()->get('boleto');

?>
<div id="pagamentos-para-woocommerce-com-appmax-boleto">
	<?=wpautop($settings->get('description'))?>
	<div style="margin-top: 32px">
		<div class="pagamentos-para-woocommerce-com-appmax--wrapper">
			<div class="pagamentos-para-woocommerce-com-appmax--step" data-step="1">
				<p>
					Finalize a sua compra, imprima o boleto ou abra
					o app do banco	na opção Boleto.
				</p>
			</div>
			<div class="pagamentos-para-woocommerce-com-appmax--step" data-step="2">
				<p>
					Aponte a câmera do celular para o código de barras
					ou copie e cole a linha digitável.
				</p>
			</div>
			<div class="pagamentos-para-woocommerce-com-appmax--step" data-step="3">
				<p>
					Confira os dados e confirme o seu pagamento
					pelo app do Banco.
				</p>
			</div>
			<div class="pagamentos-para-woocommerce-com-appmax--step" data-step="4">
				<p>
					Assim que o pagamento for identificado, enviaremos uma mensagem de confirmação.
				</p>
			</div>
		</div>
	</div>
</div>