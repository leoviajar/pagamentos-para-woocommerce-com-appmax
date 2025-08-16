<?php

use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;

if (! defined('ABSPATH')) {
	exit;
}

$plugin = Connector::plugin();

$path  = \WP_CONTENT_DIR.'/'.$plugin->getDomain();
$files = [];
$files = glob($path.'/*.log');

usort($files, function ($x, $y) {
	return filemtime($x) < filemtime($y);
});

$files = array_map(function ($item) {
	return [
		'path' => $item,
		'basename' => basename($item),
		'label' => sprintf('%s => (%s)', basename($item), (new DateTime('@'.filemtime($item)))->setTimezone(wp_timezone())->format('d/m/Y H:i:s'))
	];
}, $files);

$curr_file = $files[0] ?? null;

if (!empty(($get_file = \filter_input(INPUT_POST, 'log_path', FILTER_SANITIZE_STRING)))) {
	foreach ($files as $file) {
		if ($file['basename'] === $get_file) {
			$curr_file = $file;
		}
	}
}

?>
<img
	src="<?php echo esc_url(Connector::plugin()->getUrl().'assets/images/appmax-logo.png');?>"
	alt="Logo"
	style="width: 100%; max-width: 160px; margin: 0; display: block;"
>

<h1 class="pgly-wps--title">
	Status do Plugin
</h1>


<div class="pgly-wps--row">
	<p class="pgly-wps--notification pgly-wps-is-info" style="width: 100%">
		<strong>Versão do Plugin</strong>:
		<code><?php echo(esc_html(get_option(\PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_ACTION.'_version', '0')));?></code>.
		<br>
		<strong>Versão do Banco de Dados</strong>:
		<code><?php echo(esc_html(get_option(\PAGAMENTOS_PARA_WOOCOMMERCE_COM_APPMAX_ACTION.'_migrations', '0')));?></code>.
	</p>
</div>

<h1 class="pgly-wps--title pgly-wps-is-6">
	Logs de Depuração
</h1>

<form
	action="<?=admin_url('admin.php?page='.$plugin->getDomain().'-logs')?>"
	method="POST">
	<div class="pgly-wps--row">
		<div class="pgly-wps--column">
			<div class="pgly-wps--field">
				<label class="pgly-wps--label" for="log_path">Logs disponíveis</label>
				<select name="log_path" id="log_path">
					<?php
					foreach ($files as $file) {
						printf('<option value="%s" %s>%s</option>', $file['basename'], $curr_file['basename'] === $file['basename'] ? 'selected="selected"' : '', $file['label']);
					}
?>
				</select>
			</div>
		</div>
	</div>
	<div class="pgly-wps--row">
		<div class="pgly-wps--column">
			<button class="pgly-wps--button pgly-wps-is-primary" type="submit">
				Abrir Log
			</button>
		</div>
	</div>
</form>

<?php if ($curr_file) : ?>
<div class="pgly-wps--row">
	<div class="pgly-wps--column">
		<h3 class="pgly-wps--title">
			<?=$curr_file['label']?>)</h3>

		<div class="pgly-wps--logger">
			<pre><?php echo esc_html(file_get_contents($curr_file['path'])); ?></pre>
		</div>
	</div>
</div>
<?php endif;
?>