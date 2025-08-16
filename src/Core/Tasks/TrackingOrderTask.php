<?php

namespace AppMax\WooCommerce\Gateway\Core\Tasks;

use Exception;
use AppMax\WooCommerce\Gateway\Core\Records\PaymentRecord;
use AppMax\WooCommerce\Gateway\Core\Tasks\Interfaces\RunnableTask;
use AppMax\WooCommerce\Gateway\Api\Payloads\Create\Order\CreateDeliveryTrackingCodePayload;
use AppMax\WooCommerce\Gateway\ApiTools;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use WC_Order;

/**
 * Tracking order task.
 *
 * @package \AppMax\WooCommerce\Gateway
 * @subpackage \AppMax\WooCommerce\Gateway\Core\Tasks
 * @version 0.1.0
 * @since 0.1.0
 * @category Task
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license piggly
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class TrackingOrderTask implements RunnableTask
{
	/**
	 * Payment data.
	 *
	 * @var PaymentRecord
	 * @since 0.1.0
	 */
	protected $_payment;

	/**
	 * Tracking code.
	 *
	 * @var string
	 * @since 0.1.0
	 */
	protected $_tracking_code;

	/**
	 * Constructor.
	 *
	 * @param PaymentRecord $payment
	 * @param string $tracking_code
	 * @since 0.1.0
	 * @return void
	 */
	public function __construct(PaymentRecord $payment, string $tracking_code)
	{
		$this->_payment = $payment;
		$this->_tracking_code = $tracking_code;
	}

	/**
	 * Run task.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function run()
	{
		if (ApiTools::hasCredentials() === false) {
			return;
		}

		try {
			$response = ApiTools::getApi()->order()->deliveryCode(new CreateDeliveryTrackingCodePayload(
				$this->_payment->appMaxOrderId(),
				$this->_tracking_code
			));

			if ($response && $this->_payment->hasOrder()) {
				\update_post_meta(
					$this->_payment->order()->get_id(),
					'_appmax_tracking_code',
					$this->_tracking_code
				);
			}
		} catch (Exception $e) {
			Connector::debugger()->force()->error($e->getMessage());
		}
	}
}
