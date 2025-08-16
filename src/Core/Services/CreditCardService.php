<?php

namespace AppMax\WooCommerce\Gateway\Core\Services;

use AppMax\WooCommerce\Gateway\Api\Utils\Parser;
use AppMax\WooCommerce\Gateway\Api\Values\InstallmentValue;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use WC_Order;

/**
 * Services for credit card.
 *
 * @package \AppMax\WooCommerce\Gateway
 * @subpackage \AppMax\WooCommerce\Gateway\Core\Services
 * @version 0.2.0
 * @since 0.2.0
 * @category Service
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license piggly
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class CreditCardService
{
	/**
	 * Calculate installments to cart, displaying it as HTML.
	 *
	 * @param int $installment
	 * @param int $max_installment
	 * @since 0.2.0
	 * @since 1.1.0-beta Calculate selected installment value to order.
	 * @return InstallmentValue|null
	 */
	public static function installmentsToOrder(WC_Order $order, int $installment, int $max_installment): ?InstallmentValue
	{
		$total = $order->get_total();

		if (empty($total) || $installment <= 1) {
			return null;
		}

		$installments = (new AppMaxService())->getInstallments(
			$total,
			$max_installment
		);

		$installment = \array_filter($installments, function ($item) use ($installment) {
			return $item->getInstallment() === $installment;
		});

		if (empty($installment)) {
			return null;
		}

		return \array_shift($installment);
	}

	/**
	 * Calculate installments to cart, displaying it as HTML.
	 *
	 * @since 0.2.0
	 * @since 1.1.0-beta Calculate selected installment value to cart.
	 * @return string
	 */
	public static function installmentsOnCart(): string
	{
		$total = Parser::anyToFloat(WC()->cart->get_totals()['total']);

		if (empty($total)) {
			return '';
		}

		/** @var KeyingBucket $settings */
		$settings = Connector::settings()->get('credit_card');


		return \implode(' ', static::displayInstallments(
			(new AppMaxService())->getInstallments(
				$total,
				$settings->get('max_installments', 12)
			),
			$settings->get('show_total', false)
		));
	}

	/**
	 * Gera as opções de HTML (<option>) para um seletor de parcelas.
	 *
	 * Esta função formata um array de objetos de parcela em tags <option>,
	 * adicionando a informação "sem juros" para a 2ª e 3ª parcelas e
	 * selecionando a última parcela como padrão.
	 *
	 * @param object[] $installments Array de objetos de parcela. Cada objeto deve ter métodos como getInstallment(), getTotal() e getInstallmentAmount().
	 * @param bool     $show_total   Se verdadeiro, exibe o valor total da compra ao lado da parcela. Ex: (R$ 1.250,00).
	 * @return string[] Array de strings, cada uma contendo uma tag <option> formatada.
	 */
	public static function displayInstallments(array $installments, bool $show_total = true): array
	{
		// Validação de entrada: se o array estiver vazio, retorna um array vazio.
		if (empty($installments)) {
			return [];
		}

		$_installments = [];
		$last_key = array_key_last($installments); // Chave do último elemento para a lógica de 'selected'

		foreach ($installments as $index => $installment) {
			// Validação do objeto: garante que os métodos necessários existam para evitar erros fatais.
			if (
				!is_object($installment) ||
				!method_exists($installment, 'getInstallment') ||
				!method_exists($installment, 'getTotal') ||
				!method_exists($installment, 'getInstallmentAmount')
			) {
				// Pula esta iteração se o objeto não for válido, pode-se adicionar um log de erro aqui.
				continue;
			}

			$num_installments = $installment->getInstallment();
			$total_amount = (float) $installment->getTotal();
			$installment_amount = (float) $installment->getInstallmentAmount();

			// Lógica para pagamento à vista (1x)
			if ($num_installments === 1) {
				$_installments[] = \sprintf(
					'<option value="1">%s à vista</option>',
					static::formatPrice($total_amount)
				);
				continue;
			}

			// --- AJUSTE REALIZADO AQUI ---
			// Define o texto "sem juros" apenas se o número de parcelas for 2 ou 3.
			$interest_text = '';
			if ($num_installments === 2 || $num_installments === 3) {
				$interest_text = 'sem juros';
			}
			// --- FIM DO AJUSTE ---

			// Lógica para exibir o total
			$total_text = $show_total ? \sprintf('(%s)', static::formatPrice($total_amount)) : '';

			// Lógica para selecionar a última opção
			$selected_attr = ($index === $last_key) ? 'selected="selected"' : '';

			// Montagem da string final usando um array de partes para maior clareza
			$option_parts = [
				\sprintf('%dx de %s', $num_installments, static::formatPrice($installment_amount)),
				$interest_text,
				$total_text,
			];

			// Filtra partes vazias e as une com um espaço
			$option_text = \implode(' ', \array_filter($option_parts));

			$_installments[] = \sprintf(
				'<option value="%d" %s>%s</option>',
				$num_installments,
				$selected_attr,
				$option_text
			);
		}

		return $_installments;
	}

	/**
	 * Format price to string.
	 *
	 * @param float $amount
	 * @since 0.1.0
	 * @return string
	 */
	public static function formatPrice(float $amount): string
	{
		return sprintf(
			"%s %s",
			\get_woocommerce_currency_symbol(),
			\number_format(
				$amount,
				\wc_get_price_decimals(),
				\wc_get_price_decimal_separator(),
				\wc_get_price_thousand_separator(),
			)
		);
	}
}
