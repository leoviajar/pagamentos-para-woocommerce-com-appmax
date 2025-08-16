<?php

namespace AppMax\WooCommerce\Gateway\Core\Records;

use DateInterval;
use DateTimeImmutable;
use InvalidArgumentException;
use AppMax\WooCommerce\Gateway\Core\Records\Enums\PaymentMethodEnum;
use AppMax\WooCommerce\Gateway\Core\Records\Enums\PaymentStatusEnum;
use AppMax\WooCommerce\Gateway\Core\Records\Interfaces\RecordableModel;
use AppMax\WooCommerce\Gateway\Api\Models\ApplicationModel;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Settings\KeyingBucket;
use stdClass;
use WC_Order;

/**
 * Payment Record.
 *
 * @package \AppMax\WooCommerce\Gateway
 * @subpackage \AppMax\WooCommerce\Gateway\Core\Records
 * @version 0.1.0
 * @since 0.1.0
 * @category Record
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license piggly
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class PaymentRecord implements RecordableModel
{
	/**
	 * Record ID.
	 *
	 * @since 0.1.0
	 * @var int|null
	 */
	protected ?int $_id = null;

	/**
	 * Woocommerce Order.
	 *
	 * @since 0.1.0
	 * @var WC_Order|null
	 */
	protected ?WC_Order $_order = null;

	/**
	 * Local environment.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	protected string $_env;

	/**
	 * WooCommerce Order ID.
	 *
	 * @since 0.1.0
	 * @var int|null
	 */
	protected ?int $_order_id = null;

	/**
	 * WooCommerce Customer ID.
	 *
	 * @since 0.1.0
	 * @var int|null
	 */
	protected ?int $_customer_id = null;

	/**
	 * AppMax Order ID.
	 *
	 * @since 0.1.0
	 * @var int|null
	 */
	protected ?int $_ext_order_id = null;

	/**
	 * AppMax Customer ID.
	 *
	 * @since 0.1.0
	 * @var int|null
	 */
	protected ?int $_ext_customer_id = null;

	/**
	 * AppMax Site ID.
	 *
	 * @since 0.1.0
	 * @var int|null
	 */
	protected ?int $_ext_site_id = null;

	/**
	 * AppMax Store CNPJ.
	 *
	 * @since 0.1.0
	 * @var string|null
	 */
	protected ?string $_cnpj = null;

	/**
	 * Payment method.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	protected string $_payment_method;

	/**
	 * Local status.
	 *
	 * @since 0.1.0
	 * @var int
	 */
	protected int $_status;

	/**
	 * Payment amount.
	 *
	 * @since 0.1.0
	 * @var float
	 */
	protected float $_amount;

	/**
	 * Payment amount.
	 *
	 * @since 0.1.0
	 * @var float|null
	 */
	protected ?float $_amount_paid = null;

	/**
	 * Order tracking code.
	 *
	 * @since 0.1.0
	 * @var string|null
	 */
	protected ?string $_tracking_code = null;

	/**
	 * Payment reference.
	 *
	 * @since 0.1.0
	 * @var string|null
	 */
	protected ?string $_pay_reference = null;

	/**
	 * Payment media.
	 *
	 * @since 0.1.0
	 * @var string|null
	 */
	protected ?string $_media = null;

	/**
	 * Payment url.
	 *
	 * @since 0.1.0
	 * @var string|null
	 */
	protected ?string $_url = null;

	/**
	 * Payment path.
	 *
	 * @since 0.1.0
	 * @var string|null
	 */
	protected ?string $_path = null;

	/**
	 * Payment code.
	 *
	 * @since 0.1.0
	 * @var string|null
	 */
	protected ?string $_payment_code = null;

	/**
	 * Payment paid at.
	 *
	 * @since 0.1.0
	 * @var DateTimeImmutable|null
	 */
	protected ?DateTimeImmutable $_paid_at = null;

	/**
	 * Payment integrated at.
	 *
	 * @since 0.1.0
	 * @var DateTimeImmutable|null
	 */
	protected ?DateTimeImmutable $_integrated_at = null;

	/**
	 * Payment refunded at.
	 *
	 * @since 0.1.0
	 * @var DateTimeImmutable|null
	 */
	protected ?DateTimeImmutable $_refunded_at = null;

	/**
	 * Payment expires at.
	 *
	 * @since 0.1.0
	 * @var DateTimeImmutable|null
	 */
	protected ?DateTimeImmutable $_expires_at = null;

	/**
	 * Payment created at.
	 *
	 * @since 0.1.0
	 * @var DateTimeImmutable
	 */
	protected DateTimeImmutable $_created_at;

	/**
	 * Payment updated at.
	 *
	 * @since 0.1.0
	 * @var DateTimeImmutable
	 */
	protected DateTimeImmutable $_updated_at;

	/**
	 * Constructor.
	 *
	 * @param string $payment_method
	 * @param float $amount
	 * @param int $status
	 * @param string $env
	 * @see PaymentMethodEnum
	 * @see PaymentStatusEnum
	 * @since 0.1.0
	 */
	public function __construct(string $payment_method, float $amount, int $status = 0, string $env = 'homol')
	{
		if (!PaymentMethodEnum::isValid($payment_method)) {
			throw new InvalidArgumentException('Invalid payment method.');
		}

		if (!PaymentStatusEnum::isValid($status)) {
			throw new InvalidArgumentException('Invalid payment status.');
		}

		$this->_env = $env;
		$this->_payment_method = $payment_method;
		$this->_status = $status;
		$this->_amount = $amount;
		$this->_created_at = Parser::anyToDateTime('now');
		$this->_updated_at = Parser::anyToDateTime('now');
	}

	/**
	 * Get payment environment.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public function env(): string
	{
		return $this->_env;
	}

	/**
	 * Get Woocommerce Order.
	 *
	 * @since 0.1.0
	 * @return WC_Order|null
	 */
	public function order(): ?WC_Order
	{
		return $this->_order;
	}

	/**
	 * Get Woocommerce Order ID.
	 *
	 * @since 0.1.0
	 * @return string|null
	 */
	public function orderId(): ?int
	{
		return $this->_order_id;
	}

	/**
	 * Get Woocommerce Customer ID.
	 *
	 * @since 0.1.0
	 * @return string|null
	 */
	public function customerId(): ?int
	{
		return $this->_customer_id;
	}

	/**
	 * Link order to this Payment.
	 *
	 * @param WC_Order $order
	 * @since 0.1.0
	 * @return self
	 */
	public function linkOrder(WC_Order $order)
	{
		$this->_order = $order;
		$this->_order_id = $order->get_id();
		$this->_customer_id = $order->get_customer_id();
		return $this;
	}

	/**
	 * Unlink order from this Payment.
	 *
	 * @since 0.1.0
	 * @return self
	 */
	public function unlinkOrder()
	{
		$this->_order = null;
		$this->_order_id = null;
		$this->_customer_id = null;
		return $this;
	}

	/**
	 * Check if has order.
	 *
	 * @since 0.1.0
	 * @return bool
	 */
	public function hasOrder(): bool
	{
		return $this->_order !== null;
	}

	/**
	 * Get appmax order id.
	 *
	 * @since 0.1.0
	 * @return int|null
	 */
	public function appMaxOrderId(): ?int
	{
		return $this->_ext_order_id;
	}

	/**
	 * Get appmax customer id.
	 *
	 * @since 0.1.0
	 * @return int|null
	 */
	public function appMaxCustomerId(): ?int
	{
		return $this->_ext_customer_id;
	}

	/**
	 * Get appmax site id.
	 *
	 * @since 0.1.0
	 * @return int|null
	 */
	public function appMaxSiteId(): ?int
	{
		return $this->_ext_site_id;
	}

	/**
	 * Apply appmax ids.
	 *
	 * @param integer $order_id
	 * @param integer $customer_id
	 * @param integer $site_id
	 * @since 0.1.0
	 * @return self
	 */
	public function applyAppMaxId(
		int $order_id,
		int $customer_id,
		int $site_id
	) {
		$this->_ext_order_id = $order_id;
		$this->_ext_customer_id = $customer_id;
		$this->_ext_site_id = $site_id;
		return $this;
	}

	/**
	 * Get CNPJ.
	 *
	 * @since 0.1.0
	 * @return string|null
	 */
	public function cnpj(): ?string
	{
		return $this->_cnpj;
	}

	/**
	 * Set CNPJ.
	 *
	 * @param string $cnpj
	 * @since 0.1.0
	 * @return self
	 */
	public function setCnpj(string $cnpj)
	{
		$this->_cnpj = $cnpj;
		return $this;
	}

	/**
	 * Get payment reference.
	 *
	 * @since 0.1.0
	 * @return string|null
	 */
	public function payReference(): ?string
	{
		return $this->_pay_reference;
	}

	/**
	 * Set payment reference.
	 *
	 * @param string $pay_reference
	 * @since 0.1.0
	 * @return self
	 */
	public function setPayReference(string $pay_reference)
	{
		$this->_pay_reference = $pay_reference;
		return $this;
	}

	/**
	 * Get tracking code.
	 *
	 * @since 0.1.0
	 * @return string|null
	 */
	public function trackingCode(): ?string
	{
		return $this->_tracking_code;
	}

	/**
	 * Set tracking code.
	 *
	 * @param string $tracking_code
	 * @since 0.1.0
	 * @return self
	 */
	public function setTrackingCode(string $tracking_code)
	{
		$this->_tracking_code = $tracking_code;
		return $this;
	}

	/**
	 * Get payment media.
	 *
	 * @since 0.1.0
	 * @return string|null
	 */
	public function media(): ?string
	{
		return $this->_media;
	}

	/**
	 * Set payment media.
	 *
	 * @param string $media
	 * @since 0.1.0
	 * @return self
	 */
	public function setMedia(string $media)
	{
		$this->_media = $media;
		return $this;
	}

	/**
	 * Get payment url.
	 *
	 * @since 0.1.0
	 * @return string|null
	 */
	public function url(): ?string
	{
		return $this->_url;
	}

	/**
	 * Set payment url.
	 *
	 * @param string $url
	 * @since 0.1.0
	 * @return self
	 */
	public function setUrl(string $url)
	{
		$this->_url = $url;
		return $this;
	}

	/**
	 * Get payment path.
	 *
	 * @since 0.1.0
	 * @return string|null
	 */
	public function path(): ?string
	{
		return $this->_path;
	}

	/**
	 * Set payment path.
	 *
	 * @param string $path
	 * @since 0.1.0
	 * @return self
	 */
	public function setPath(string $path)
	{
		$this->_path = $path;
		return $this;
	}

	/**
	 * Get payment payment_code.
	 *
	 * @since 0.1.0
	 * @return string|null
	 */
	public function paymentCode(): ?string
	{
		return $this->_payment_code;
	}

	/**
	 * Set payment payment_code.
	 *
	 * @param string $payment_code
	 * @since 0.1.0
	 * @return self
	 */
	public function setPaymentCode(string $payment_code)
	{
		$this->_payment_code = $payment_code;
		return $this;
	}

	/**
	 * Get status.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public function status(): string
	{
		return $this->_status;
	}

	/**
	 * Set local Payment status.
	 *
	 * @param string $status
	 * @since 0.1.0
	 * @return self
	 */
	public function changeStatus(string $status)
	{
		if (!PaymentStatusEnum::isValid($status)) {
			throw new InvalidArgumentException(
				\sprintf('Status inválido, utilize um dos seguintes valores: %s', \implode(', ', PaymentStatusEnum::statuses()))
			);
		}

		if (PaymentStatusEnum::canUpdateTo($this->_status, $status) === false) {
			throw new InvalidArgumentException(
				\sprintf('Não é possível alterar o status de %s para %s', $this->_status, $status)
			);
		}

		$this->_status = $status;
		return $this;
	}

	/**
	 * Mark as expired.
	 *
	 * @since 0.1.0
	 * @return self
	 */
	public function markAsExpired()
	{
		$this->_status = PaymentStatusEnum::STATUS_EXPIRED;
		return $this;
	}

	/**
	 * Mark as paid.
	 *
	 * @param float $amount
	 * @param DateTimeImmutable $paid_at
	 * @since 0.1.0
	 * @return self
	 */
	public function markAsPaid(float $amount = null, DateTimeImmutable $paid_at = null)
	{
		$this->changeStatus(PaymentStatusEnum::STATUS_APPROVED);
		$this->_paid_at = $paid_at ?? Parser::anyToDateTime('now');
		$this->_amount_paid = $amount;
		return $this;
	}

	/**
	 * Mark as integrated.
	 *
	 * @param float $amount
	 * @param DateTimeImmutable $integrated_at
	 * @param DateTimeImmutable $paid_at
	 * @since 0.1.0
	 * @return self
	 */
	public function markAsIntegrated(float $amount = null, DateTimeImmutable $integrated_at = null, DateTimeImmutable $paid_at = null)
	{
		$this->changeStatus(PaymentStatusEnum::STATUS_INTEGRATED);
		$this->_integrated_at = $integrated_at ?? Parser::anyToDateTime('now');
		$this->_amount_paid = $amount;

		if (empty($this->_paid_at)) {
			$this->_paid_at = $paid_at ?? Parser::anyToDateTime('now');
		}

		return $this;
	}

	/**
	 * Mark as canceled.
	 *
	 * @since 0.1.0
	 * @return self
	 */
	public function markAsCanceled()
	{
		$this->changeStatus(PaymentStatusEnum::STATUS_CANCELLED);
		return $this;
	}

	/**
	 * Mark as failed.
	 *
	 * @since 0.1.0
	 * @return self
	 */
	public function markAsFailed()
	{
		$this->changeStatus(PaymentStatusEnum::STATUS_CANCELLED);
		return $this;
	}

	/**
	 * Mark as refunded.
	 *
	 * @param DateTimeImmutable $refunded_at
	 * @since 0.1.0
	 * @return self
	 */
	public function markAsRefunded(DateTimeImmutable $refunded_at = null)
	{
		$this->changeStatus(PaymentStatusEnum::STATUS_REFUNDED);
		$this->_refunded_at = $refunded_at ?? Parser::anyToDateTime('now');
		return $this;
	}

	/**
	 * Get payment method.
	 *
	 * @since 0.1.0
	 * @return float
	 */
	public function paymentMethod(): string
	{
		return $this->_payment_method;
	}

	/**
	 * Get amount.
	 *
	 * @since 0.1.0
	 * @return float
	 */
	public function amount(): float
	{
		return $this->_amount;
	}

	/**
	 * Is payment method as expected.
	 *
	 * @param string $expected
	 * @since 0.1.0
	 * @return boolean
	 */
	public function isPaymentMethod(string $expected): bool
	{
		return $this->_payment_method === $expected;
	}

	/**
	 * Is local Payment status as expected.
	 *
	 * @param int $expected
	 * @since 0.1.0
	 * @return boolean
	 */
	public function isStatus(int $expected): bool
	{
		return $this->_status === $expected;
	}

	/**
	 * Get if it is expired.
	 *
	 * @since 0.1.0
	 * @return boolean
	 */
	public function isExpired(): bool
	{
		if ($this->isStatus(PaymentStatusEnum::STATUS_EXPIRED)) {
			return true;
		}

		if ($this->isWaiting() === false) {
			return false;
		}

		if ($this->isPaymentMethod(PaymentMethodEnum::PAYMENT_METHOD_CREDIT_CARD)) {
			return false;
		}

		/** @var KeyingBucket $settings */
		$settings = Connector::settings()->get(
			$this->paymentMethod(),
			new KeyingBucket()
		);

		$must_expires = $settings->get('must_expires', false);
		$interval = null;

		if ($this->isPaymentMethod(PaymentMethodEnum::PAYMENT_METHOD_BOLETO)) {
			$days = $settings->get('expires_after', 3) + 5;
			$interval = new DateInterval('P'.$days.'D');
		}

		if ($this->isPaymentMethod(PaymentMethodEnum::PAYMENT_METHOD_PIX)) {
			$seconds = $settings->get('expires_in', 3600);
			$interval = new DateInterval('PT'.$seconds.'S');
		}

		// Must expired and has expired
		if ($must_expires && !empty($this->_expires_at)) {
			if ($this->_expires_at <= (new DateTimeImmutable('now', \wp_timezone()))->sub($interval)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get if it is paid.
	 *
	 * @since 0.1.0
	 * @return boolean
	 */
	public function isPaid(): bool
	{
		return $this->isStatus(PaymentStatusEnum::STATUS_APPROVED) || $this->isStatus(PaymentStatusEnum::STATUS_INTEGRATED);
	}

	/**
	 * Get if it is waiting.
	 *
	 * @since 0.1.0
	 * @return boolean
	 */
	public function isWaiting(): bool
	{
		return $this->isStatus(PaymentStatusEnum::STATUS_CREATED)
			|| $this->isStatus(PaymentStatusEnum::STATUS_PENDING)
			|| $this->isStatus(PaymentStatusEnum::STATUS_IN_ANALYSIS)
			|| $this->isStatus(PaymentStatusEnum::STATUS_PENDING_INTEGRATION)
			|| $this->isStatus(PaymentStatusEnum::STATUS_CHARGEBACK_WAITING);
	}

	/**
	 * Get paid at.
	 *
	 * @since 0.1.0
	 * @return DateTimeImmutable|null
	 */
	public function paidAt(): ?DateTimeImmutable
	{
		return $this->_paid_at;
	}

	/**
	 * Get integrated at.
	 *
	 * @since 0.1.0
	 * @return DateTimeImmutable|null
	 */
	public function integratedAt(): ?DateTimeImmutable
	{
		return $this->_integrated_at;
	}

	/**
	 * Get refunded at.
	 *
	 * @since 0.1.0
	 * @return DateTimeImmutable|null
	 */
	public function refundedAt(): ?DateTimeImmutable
	{
		return $this->_refunded_at;
	}

	/**
	 * Get expires at.
	 *
	 * @since 0.1.0
	 * @return DateTimeImmutable|null
	 */
	public function expiresAt(): ?DateTimeImmutable
	{
		return $this->_expires_at;
	}

	/**
	 * Set expiration date.
	 *
	 * @since 0.1.0
	 * @return self
	 */
	public function willExpiresAt(DateTimeImmutable $expires_at)
	{
		$this->_expires_at = $expires_at;
		return $this;
	}

	/**
	 * Get created at.
	 *
	 * @since 0.1.0
	 * @return DateTimeImmutable
	 */
	public function createdAt(): DateTimeImmutable
	{
		return $this->_created_at;
	}

	/**
	 * Get updated at.
	 *
	 * @since 0.1.0
	 * @return DateTimeImmutable
	 */
	public function updatedAt(): DateTimeImmutable
	{
		return $this->_updated_at;
	}

	/**
	 * Get id.
	 *
	 * @since 0.1.0
	 * @return int|null
	 */
	public function id(): ?int
	{
		return $this->_id;
	}

	/**
	 * Set Record id.
	 *
	 * @since 0.1.0
	 * @return self
	 */
	public function preload(int $id)
	{
		$this->_id = $id;
		return $this;
	}

	/**
	 * Get if it is preloaded from database.
	 *
	 * @since 0.1.0
	 * @return boolean
	 */
	public function isPreloaded(): bool
	{
		return !\is_null($this->_id);
	}

	/**
	 * Create record object from Record object.
	 *
	 * @since 0.1.0
	 * @return stdClass
	 */
	public function toRecord(): stdClass
	{
		$record = new stdClass();

		$record->env = $this->_env;

		if ($this->_order) {
			$record->order_id = $this->_order->get_id();
			$record->customer_id = $this->_order->get_customer_id();
		}

		$record->ext_order_id = $this->_ext_order_id;
		$record->ext_customer_id = $this->_ext_customer_id;
		$record->ext_site_id = $this->_ext_site_id;
		$record->cnpj = $this->_cnpj;
		$record->payment_method = $this->_payment_method;
		$record->status = $this->_status;
		$record->amount = $this->_amount;
		$record->amount_paid = $this->_amount_paid;
		$record->tracking_code = $this->_tracking_code;
		$record->pay_reference = $this->_pay_reference;
		$record->media = $this->_media;
		$record->url = $this->_url;
		$record->path = $this->_path;
		$record->payment_code = $this->_payment_code;

		$record->paid_at = $this->_paid_at ? $this->_paid_at->format('Y-m-d H:i:s') : null;
		$record->integrated_at = $this->_integrated_at ? $this->_integrated_at->format('Y-m-d H:i:s') : null;
		$record->refunded_at = $this->_refunded_at ? $this->_refunded_at->format('Y-m-d H:i:s') : null;
		$record->expires_at = $this->_expires_at ? $this->_expires_at->format('Y-m-d H:i:s') : null;
		$record->created_at = $this->_created_at ? $this->_created_at->format('Y-m-d H:i:s') : null;
		$record->updated_at = (new DateTimeImmutable('now', \wp_timezone()))->format('Y-m-d H:i:s');

		return $record;
	}

	/**
	 * Create Record object from record object.
	 *
	 * @param stdClass $record
	 * @since 0.1.0
	 * @return self
	 */
	public static function fromRecord(stdClass $record)
	{
		$e = new PaymentRecord($record->payment_method, Parser::anyToFloat($record->amount), Parser::anyToInteger($record->status));

		$e->_id = Parser::anyToInteger($record->id);
		$e->_env = $record->env;

		if ($record->order_id) {
			$order = \wc_get_order(Parser::anyToInteger($record->order_id));
			$e->_order = empty($order) ? null : $order;
			$e->_order_id = Parser::anyToInteger($record->order_id);
			$e->_customer_id = Parser::anyToInteger(empty($order) ? $record->customer_id : $order->get_customer_id());
		}

		$e->_payment_method = $record->payment_method;
		$e->_ext_order_id = empty($record->ext_order_id) ? null : Parser::anyToInteger($record->ext_order_id);
		$e->_ext_customer_id = empty($record->ext_customer_id) ? null : Parser::anyToInteger($record->ext_customer_id);
		$e->_ext_site_id = empty($record->ext_site_id) ? null : Parser::anyToInteger($record->ext_site_id);
		$e->_cnpj = $record->cnpj;
		$e->_amount_paid = empty($record->amount_paid) ? null : Parser::anyToFloat($record->amount_paid);
		$e->_tracking_code = $record->tracking_code;
		$e->_pay_reference = $record->pay_reference;
		$e->_media = $record->media;
		$e->_url = $record->url;
		$e->_path = $record->path;
		$e->_payment_code = $record->payment_code;

		$e->_paid_at = Parser::anyToDateTime($record->paid_at);
		$e->_integrated_at = Parser::anyToDateTime($record->integrated_at);
		$e->_refunded_at = Parser::anyToDateTime($record->refunded_at);
		$e->_expires_at = Parser::anyToDateTime($record->expires_at);
		$e->_created_at = Parser::anyToDateTime($record->created_at);
		$e->_updated_at = Parser::anyToDateTime($record->updated_at);

		return $e;
	}

	/**
	 * Create a new Payment model linking with order.
	 *
	 * @param WC_Order $localOrder
	 * @param string $payment_method
	 * @since 0.1.0
	 * @return PaymentRecord
	 */
	public static function create(WC_Order $localOrder, string $payment_method): PaymentRecord
	{
		$payment = new PaymentRecord(
			$payment_method,
			$localOrder->get_total(),
			PaymentStatusEnum::STATUS_PENDING,
			Connector::settings()->get('gateway')->get('environment', ApplicationModel::ENV_HOMOL)
		);

		$payment->linkOrder($localOrder);

		return $payment;
	}
}
