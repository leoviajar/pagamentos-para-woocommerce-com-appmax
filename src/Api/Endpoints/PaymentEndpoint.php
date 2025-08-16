<?php

namespace AppMax\WooCommerce\Gateway\Api\Endpoints;

use Exception;
use AppMax\WooCommerce\Gateway\Api\Models\PaymentModel;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\CreatePaymentPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\GetPaymentPayload;
use AppMax\WooCommerce\Gateway\Api\Values\InstallmentValue;
use AppMax\WooCommerce\Gateway\ApiTools;

/**
 * Payment endpoint.
 *
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Endpoints
 * @version 1.0.0
 * @since 1.0.0
 * @category Endpoint
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license PGLY
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class PaymentEndpoint extends AbstractEndpoint
{
	/**
	 * Create a new Payment.
	 *
	 * @param CreatePaymentPayload $payload
	 * @since 1.0.0
	 * @return GetPaymentPayload|null
	 */
	public function create(CreatePaymentPayload $payload): ?GetPaymentPayload
	{
		try {
			$data = $payload->toArray();
			$data['access-token'] = ApiTools::getCredential();

			$url = '/payment';

			switch ($payload->getMethod()->getPaymentType()) {
				case 'Boleto':
					$url .= '/boleto';
					break;
				case 'CreditCard':
					$url .= '/credit-card';
					break;
				case 'pix':
					$url .= '/pix';
					break;
			}


			if ($this->isDebugging()) {
				$this->_log('payment', 'create.request', 'POST', \json_encode($payload->toCensoredArray()));
			}

			$response = $this->_request->post($url, $data)->call();

			if ($this->isDebugging()) {
				$this->_log('payment', 'create.response', $response->getStatus(), \json_encode($response->getBody()));
			}

			$this->_throwOnError($response);
			return GetPaymentPayload::import($response->getBody()['data']);
		} catch (Exception $e) {
			$this->_inspectException($e, 'payment', 'create.response');
			return null;
		}
	}

	/**
	 * Installments for payment.
	 *
	 * @param float $total
	 * @param int $max_installments
	 * @since 1.1.0-beta
	 * @return InstallmentValue[]
	 */
	public function installments(float $total, int $max_installments = 12): array
	{
		try {
			$data = [
				'access-token' => ApiTools::getCredential(),
				'total' => $total,
				'installments' => $max_installments,
				'format' => 1
			];

			if ($this->isDebugging()) {
				$this->_log('payment', 'installments.request', 'GET', \json_encode([
					'total' => $total,
					'installments' => $max_installments,
					'format' => 1
				]));
			}

			$response = $this->_request->post('/payment/installments', $data)->headers(['Access-Token' => ApiTools::getCredential()])->call();

			if ($this->isDebugging()) {
				$this->_log('payment', 'installments.response', $response->getStatus(), \json_encode($response->getBody()));
			}

			if ($response->getStatus() >= 400) {
				return null;
			}

			$this->_throwOnError($response);
			$installments = [];

			foreach ($response->getBody()['data'] as $installment => $value) {
				\array_push($installments, new InstallmentValue($value, \intval($installment)));
			}

			return $installments;
		} catch (Exception $e) {
			$this->_inspectException($e, 'payment', 'installments.response');
			return null;
		}
	}
}
