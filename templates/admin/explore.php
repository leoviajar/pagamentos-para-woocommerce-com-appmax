<?php

use AppMax\WooCommerce\Gateway\Core\Configuration;
use AppMax\WooCommerce\Gateway\Core\Database\Schemas\PaymentsTableSchema;
use AppMax\WooCommerce\Gateway\Core\Records\Enums\PaymentMethodEnum;
use AppMax\WooCommerce\Gateway\Core\Records\Enums\PaymentStatusEnum;
use AppMax\WooCommerce\Gateway\Core\Records\PaymentRecord;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Collection\RecordCollection;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Settings\KeyingBucket;

$defaults = [
	'status' => '',
	'number' => ''
];

$baseUrl = \admin_url('admin.php?page='.Connector::domain().'-explore');

foreach ($defaults as $name => $value) {
	$value = htmlentities($_GET[$name] ?? null);

	if (!empty($value)) {
		$defaults[$name] = $value;
	}
}

/** @var KeyingBucket $settings */
$settings = Connector::settings()->get('gateway', new KeyingBucket());

$table = PaymentsTableSchema::tableName();
$collection = new RecordCollection("SELECT * FROM {$table}");

if (!empty($defaults['status'])) {
	$defaults['status'] = intval($defaults['status']);
	$collection->where("`status` = {$defaults['status']}");
}

if (!empty($defaults['number'])) {
	$defaults['number'] = intval($defaults['number']);
	$collection->where("`order_id` = {$defaults['number']} OR `ext_order_id` = {$defaults['number']} OR `id` = {$defaults['number']}");
}

$collection->order_by('`updated_at`', 'DESC');
$collection->paginate(10, intval($_GET['paged'] ?? 1));

/** @var array<PaymentRecord> */
$entities = array_map(function (stdClass $record) {
	return PaymentRecord::fromRecord($record);
}, $collection->get());
?>

<img
	src="<?php echo esc_url(Connector::plugin()->getUrl().'assets/images/appmax-logo.png');?>"
	alt="Logo"
	style="width: 100%; max-width: 160px; margin: 0; display: block;"
>

<h1 class="pgly-wps--title">Pagamentos</h1>

<div id="woo-appmax-gateway-notification"></div>

<button id="woo-appmax-gateway-payment-check" class="pgly-wps--button pgly-async--behaviour pgly-wps-is-accent"
	data-action="<?php echo \admin_url('admin-ajax.php') . '?action=' . Connector::plugin()->getName() . '_all_process';?>"
	data-x-security="<?php echo wp_create_nonce(Configuration::nonceAction())?>">
	Verificar Pagamentos Pendentes
	<svg class="pgly-wps--spinner pgly-wps-is-white" viewBox="0 0 50 50">
		<circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
	</svg>
</button>

<script>
	document.addEventListener('DOMContentLoaded', () => {
		(new appMaxGateway.App()).explore();
	});
</script>

<div class="pgly-wps--notification pgly-wps-is-primary">
	Para continuar, defina o filtro de busca abaixo e localize pagamentos mais facilmente.
</div>

<form action="<?php echo esc_url($baseUrl)?>" method="GET">
	<input type="hidden" name="page"
		value="<?php echo esc_attr(Connector::domain().'-explore')?>" />
	<div class="pgly-wps--row">
		<div class="pgly-wps--column pgly-wps-col--6">
			<div class="pgly-wps--field pgly-form--input pgly-form--select">
				<label class="pgly-wps--label">Status</label>
				<div>
					<select name="status">
						<?php
						$values = [
							'Todos' => '',
							'Cancelado' => PaymentStatusEnum::STATUS_CANCELLED,
							'Criado' => PaymentStatusEnum::STATUS_CREATED,
							'Aprovado' => PaymentStatusEnum::STATUS_APPROVED,
							'Expirado' => PaymentStatusEnum::STATUS_EXPIRED,
							'Integrado' => PaymentStatusEnum::STATUS_INTEGRATED,
							'Pendente de Integração' => PaymentStatusEnum::STATUS_PENDING_INTEGRATION,
							'Reembolsado' => PaymentStatusEnum::STATUS_REFUNDED,
							'Chargeback Perdido' => PaymentStatusEnum::STATUS_CHARGEBACK_LOST,
							'Chargeback Ganho' => PaymentStatusEnum::STATUS_CHARGEBACK_WIN,
							'Chargeback em Tratativa' => PaymentStatusEnum::STATUS_CHARGEBACK_WAITING,
							'Análise Antifraude' => PaymentStatusEnum::STATUS_IN_ANALYSIS,
							'Pendente' => PaymentStatusEnum::STATUS_PENDING,
						];

foreach ($values as $name => $value) : ?>
						<option value="<?php echo esc_attr($value);?>" <?php echo $value === $defaults['status'] ? 'selected="selected"' : ''?>><?php echo esc_html($name);?>
						</option>
						<?php endforeach; ?>
					</select>
				</div>
				<span class="pgly-wps--message"></span>
				<p class="pgly-wps--description">
					Selecione o status para filtragem.
				</p>
			</div>
		</div>
		<div class="pgly-wps--column pgly-wps-col--6">
			<div class="pgly-wps--field pgly-form--input pgly-form--select">
				<label class="pgly-wps--label">ID Local / Número do Pedido Local / Número do Pedido na AppMax</label>
				<input name="number" placeholder="Insira qualquer valor" type="text"
					value="<?php echo esc_attr($defaults['number']);?>">
				<span class="pgly-wps--message"></span>
				<p class="pgly-wps--description">
					Selecione o tipo para ser filtrado.
				</p>
			</div>
		</div>
	</div>
	<div class="pgly-wps--row">
		<div class="pgly-wps--column">
			<button type="submit" class="pgly-wps--button pgly-wps-is-primary pgly-form--submit">
				Aplicar Filtro
			</button>
			<a style="float: right" href="<?php echo esc_url($baseUrl)?>"
				class="pgly-wps--button pgly-wps-is-secondary">
				Limpar Filtros
			</a>
		</div>
	</div>
</form>

<?php

foreach ($entities as $payment) :
	?>

<div class="pgly-wps--row">
	<div class="pgly-wps--column pgly-wps-col--12 pgly-wps-is-compact">
		<div class="pgly-wps--card pgly-wps-is-white pgly-wps-is-compact">
			<div class="inside left">
				<strong class="pgly-wps--subtitle pgly-wps-is-6" style="margin: 0 0 6px;">
					Pagamento
					#<?php echo esc_html($payment->payReference());?>
					em
					<?php echo PaymentMethodEnum::label($payment->paymentMethod());?>
				</strong>
				<?php if ($payment->hasOrder()) : ?>
				<a href="<?php echo(esc_url($payment->order()->get_edit_order_url())); ?>"
					target="_blank"
					title="Ver Pedido">
					(Ver Pedido)
				</a>
				<?php endif; ?>
				<div>
					<?php if ($payment->isPaid()) : ?>
					<div class="pgly-wps--item">
						<strong>Valor Pago</strong>
						<span><?php echo sprintf('%s %s', 'R$', \number_format($payment->amount(), 2, ',', '.'));?></span>
					</div>
					<?php endif; ?>
					<div class="pgly-wps--item">
						<strong>Status</strong>
						<span><?php echo esc_html(PaymentStatusEnum::label($payment->status()));?></span>
					</div>
					<div class="pgly-wps--item">
						<strong>AppMax / Cliente</strong>
						<span><?php echo esc_html($payment->appMaxCustomerId());?></span>
					</div>
					<div class="pgly-wps--item">
						<strong>AppMax / Pedido</strong>
						<span><?php echo esc_html($payment->appMaxOrderId());?></span>
					</div>
					<div class="pgly-wps--item">
						<strong>CNPJ da Loja</strong>
						<span><?php echo esc_html($payment->cnpj());?></span>
					</div>
					<div class="pgly-wps--item">
						<strong>Valor</strong>
						<span><?php echo sprintf('%s %s', 'R$', \number_format($payment->amount(), 2, ',', '.'));?></span>
					</div>
					<div class="pgly-wps--item">
						<strong>Última Verificação</strong>
						<span><?php echo $payment->updatedAt() instanceof DateTimeImmutable ? $payment->updatedAt()->format('d/m/Y') : '(Não verificado)';?></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
endforeach;

echo $collection->htmlPagination($baseUrl);
?>