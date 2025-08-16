<?php

use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;

if (!defined('ABSPATH')) {
	exit;
}
?>
<a href="<?=admin_url('admin.php?page='.Connector::domain())?>"
	class="button-primary">
	Ir para Configurações Avançadas
</a>

<script>
	(function() {
		window.location.href =
			"<?=admin_url('admin.php?page='.Connector::domain())?>";
	})();
</script>

<style>
	p.submit {
		display: none !important;
	}
</style>