<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Get;

use DateTimeImmutable;
use DateTimeZone;
use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\Order\GetVisitPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\Order\GetAffiliateCommissionPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\Order\GetBundlePayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\Order\GetCompanyPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\Order\GetCoProductionCommissionPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\Order\GetFreightPayload;
use AppMax\WooCommerce\Gateway\Api\Payloads\Get\Order\GetPartnerPayload;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;

/**
 * Order payload.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Payloads\Get
 * @category Payloads
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class GetOrderPayload extends AbstractPayload
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $_fields = [
		'id' => null,
		'customer_id' => null,
		'total_products' => null,
		'payment_type' => null,
		'status' => null,
		'paid_at' => null,
		'refunded_at' => null,
		'integrated_at' => null,
		'created_at' => null,
		'discount' => null,
		'interest' => null,
		'upsell_order_id' => null,
		'origin' => null,
		'total' => null,
		'traffic_description' => null,
		'full_payment_amount' => null,
	];

	/**
	 * Freight value.
	 *
	 * @var GetFreightPayload|null
	 * @since 1.0.0
	 */
	protected $_freight = null;

	/**
	 * Customer payload.
	 *
	 * @var GetCustomerPayload|null
	 * @since 1.0.0
	 */
	protected $_customer = null;

	/**
	 * Company payload.
	 *
	 * @var GetCompanyPayload|null
	 * @since 1.0.0
	 */
	protected $_company = null;

	/**
	 * Partner payload.
	 *
	 * @var GetPartnerPayload|null
	 * @since 1.0.0
	 */
	protected $_partner = null;

	/**
	 * Affiliate Comission payload.
	 *
	 * @var GetAffiliateCommissionPayload[]
	 * @since 1.0.0
	 */
	protected $_affiliates = [];

	/**
	 * Co Production Comission payload.
	 *
	 * @var GetCoProductionCommissionPayload[]
	 * @since 1.0.0
	 */
	protected $_co_productions = [];

	/**
	 * Bundles payload.
	 *
	 * @var GetBundlePayload[]
	 * @since 1.0.0
	 */
	protected $_bundles = [];

	/**
	 * Visit payload.
	 *
	 * @var GetVisitPayload|null
	 * @since 1.0.0
	 */
	protected $_visit = null;

	/**
	 * Construct object.
	 *
	 * @param int $id Order ID.
	 * @param int $customer_id Customer ID.
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct(
		$id,
		$customer_id
	) {
		$this->_fields['id'] = Parser::anyToInteger($id);
		$this->_fields['customer_id'] = Parser::anyToInteger($customer_id);
	}

	/**
	 * Get id field.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function getId(): int
	{
		return $this->_fields['id'];
	}

	/**
	 * Get customer id field.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function getCustomerId(): int
	{
		return $this->_fields['customer_id'];
	}

	/**
	 * Get total products field.
	 *
	 * @since 1.0.0
	 * @return float|null
	 */
	public function getTotalProducts(): ?float
	{
		return $this->_fields['total_products'];
	}

	/**
	 * Get total field.
	 *
	 * @since 1.0.0
	 * @return float|null
	 */
	public function getTotal(): ?float
	{
		return $this->_fields['total'];
	}

	/**
	 * Get payment type field.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function getPaymentType(): ?string
	{
		return $this->_fields['payment_type'];
	}

	/**
	 * Get status field.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function getStatus(): ?string
	{
		return $this->_fields['status'];
	}

	/**
	 * Get paid at field.
	 *
	 * @since 1.0.0
	 * @return DateTimeImmutable|null
	 */
	public function getPaidAt(): ?DateTimeImmutable
	{
		return $this->_fields['paid_at'];
	}

	/**
	 * Get refunded at field.
	 *
	 * @since 1.0.0
	 * @return DateTimeImmutable|null
	 */
	public function getRefundedAt(): ?DateTimeImmutable
	{
		return $this->_fields['refunded_at'];
	}

	/**
	 * Get integrated at field.
	 *
	 * @since 1.0.0
	 * @return DateTimeImmutable|null
	 */
	public function getIntegratedAt(): ?DateTimeImmutable
	{
		return $this->_fields['integrated_at'];
	}

	/**
	 * Get created at field.
	 *
	 * @since 1.0.0
	 * @return DateTimeImmutable|null
	 */
	public function getCreatedAt(): ?DateTimeImmutable
	{
		return $this->_fields['created_at'];
	}

	/**
	 * Get discount field.
	 *
	 * @since 1.0.0
	 * @return float|null
	 */
	public function getDiscount(): ?float
	{
		return $this->_fields['discount'];
	}

	/**
	 * Get interest field.
	 *
	 * @since 1.0.0
	 * @return float|null
	 */
	public function getInterest(): ?float
	{
		return $this->_fields['interest'];
	}

	/**
	 * Get upsell order id field.
	 *
	 * @since 1.0.0
	 * @return int|null
	 */
	public function getUpsellOrderId(): ?int
	{
		return $this->_fields['upsell_order_id'];
	}

	/**
	 * Get origin field.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function getOrigin(): ?string
	{
		return $this->_fields['origin'];
	}

	/**
	 * Get traffic description field.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function getTrafficDescription(): ?string
	{
		return $this->_fields['traffic_description'];
	}

	/**
	 * Get full payment amount field.
	 *
	 * @since 1.0.0
	 * @return float|null
	 */
	public function getFullPaymentAmount(): ?float
	{
		return $this->_fields['full_payment_amount'];
	}

	/**
	 * Get freight payload.
	 *
	 * @since 1.0.0
	 * @return GetFreightPayload|null
	 */
	public function getFreight(): ?GetFreightPayload
	{
		return $this->_freight;
	}

	/**
	 * Get customer payload.
	 *
	 * @since 1.0.0
	 * @return GetCustomerPayload|null
	 */
	public function getCustomer(): ?GetCustomerPayload
	{
		return $this->_customer;
	}

	/**
	 * Get company payload.
	 *
	 * @since 1.0.0
	 * @return GetCompanyPayload|null
	 */
	public function getCompany(): ?GetCompanyPayload
	{
		return $this->_company;
	}

	/**
	 * Get partner payload.
	 *
	 * @since 1.0.0
	 * @return GetPartnerPayload|null
	 */
	public function getPartner(): ?GetPartnerPayload
	{
		return $this->_partner;
	}

	/**
	 * Get affiliates payload.
	 *
	 * @since 1.0.0
	 * @return GetAffiliateCommissionPayload[]
	 */
	public function getAffiliates(): array
	{
		return $this->_affiliates;
	}

	/**
	 * Get co productions payload.
	 *
	 * @since 1.0.0
	 * @return GetCoProductionCommissionPayload[]
	 */
	public function getCoProductions(): array
	{
		return $this->_co_productions;
	}

	/**
	 * Get bundles payload.
	 *
	 * @since 1.0.0
	 * @return GetBundlePayload[]
	 */
	public function getBundles(): array
	{
		return $this->_bundles;
	}

	/**
	 * Get visit payload.
	 *
	 * @since 1.0.0
	 * @return GetVisitPayload|null
	 */
	public function getVisit(): ?GetVisitPayload
	{
		return $this->_visit;
	}

	/**
	 * Get all fields converting payloads
	 * to an array and removing null values.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function toArray(): array
	{
		$arr = [
			'id' => $this->getId(),
			'customer_id' => $this->getCustomerId(),
		];

		if (!empty($this->_fields['total_products'])) {
			$arr['total_products'] = $this->_fields['total_products'];
		}

		if (!empty($this->_fields['payment_type'])) {
			$arr['payment_type'] = $this->_fields['payment_type'];
		}

		if (!empty($this->_fields['status'])) {
			$arr['status'] = $this->_fields['status'];
		}

		if (!empty($this->_fields['paid_at'])) {
			$arr['paid_at'] = $this->_fields['paid_at']->format('Y-m-d H:i:s');
		}

		if (!empty($this->_fields['refunded_at'])) {
			$arr['refunded_at'] = $this->_fields['refunded_at']->format('Y-m-d H:i:s');
		}

		if (!empty($this->_fields['integrated_at'])) {
			$arr['integrated_at'] = $this->_fields['integrated_at']->format('Y-m-d H:i:s');
		}

		if (!empty($this->_fields['created_at'])) {
			$arr['created_at'] = $this->_fields['created_at']->format('Y-m-d H:i:s');
		}

		if (!empty($this->_fields['discount'])) {
			$arr['discount'] = $this->_fields['discount'];
		}

		if (!empty($this->_fields['interest'])) {
			$arr['interest'] = $this->_fields['interest'];
		}

		if (!empty($this->_fields['upsell_order_id'])) {
			$arr['upsell_order_id'] = $this->_fields['upsell_order_id'];
		}

		if (!empty($this->_fields['origin'])) {
			$arr['origin'] = $this->_fields['origin'];
		}

		if (!empty($this->_fields['total'])) {
			$arr['total'] = $this->_fields['total'];
		}

		if (!empty($this->_fields['traffic_description'])) {
			$arr['traffic_description'] = $this->_fields['traffic_description'];
		}

		if (!empty($this->_fields['full_payment_amount'])) {
			$arr['full_payment_amount'] = $this->_fields['full_payment_amount'];
		}


		if (!empty($this->_customer)) {
			$arr['customer'] = $this->_customer->toArray();
		}

		if (!empty($this->_freight)) {
			$arr['freight'] = $this->_freight->toArray();
		}

		if (!empty($this->_company)) {
			$arr['company'] = $this->_company->toArray();
		}

		if (!empty($this->_partner)) {
			$arr['partner'] = $this->_partner->toArray();
		}

		if (!empty($this->_visit)) {
			$arr['visit'] = $this->_visit->toArray();
		}

		if (!empty($this->_affiliates)) {
			$arr['affiliate_comissions'] = array_map(function ($affiliate) {
				return $affiliate->toArray();
			}, $this->_affiliates);
		}

		if (!empty($this->_co_productions)) {
			$arr['co_production_comissions'] = array_map(function ($co_production) {
				return $co_production->toArray();
			}, $this->_co_productions);
		}

		if (!empty($this->_bundles)) {
			$arr['bundles'] = array_map(function ($bundles) {
				return $bundles->toArray();
			}, $this->_bundles);
		}

		return $arr;
	}

	/**
	 * Get all fields converting payloads
	 * to an array and removing null values.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function toCensoredArray(): array
	{
		return [
			'id' => $this->getId(),
			'customer_id' => $this->getCustomerId(),
		];
	}

	/**
	 * Import and return the object.
	 *
	 * @param array $body
	 * @since 1.0.0
	 * @return GetOrderPayload
	 */
	public static function import(array $data = []): GetOrderPayload
	{
		$payload = new GetOrderPayload(
			$data['id'],
			$data['customer_id']
		);

		$payload->_fields['total_products'] = !isset($data['total_products']) ? null : Parser::anyToFloat($data['total_products']);
		$payload->_fields['payment_type'] = !isset($data['payment_type']) ? null : Parser::anyToString($data['payment_type']);
		$payload->_fields['status'] = !isset($data['status']) ? null : Parser::anyToString($data['status']);
		$payload->_fields['paid_at'] = !isset($data['paid_at']) ? null : new DateTimeImmutable($data['paid_at'], new DateTimeZone('America/Sao_Paulo'));
		$payload->_fields['refunded_at'] = !isset($data['refunded_at']) ? null : new DateTimeImmutable($data['refunded_at'], new DateTimeZone('America/Sao_Paulo'));
		$payload->_fields['integrated_at'] = !isset($data['integrated_at']) ? null : DateTimeImmutable::createFromFormat('d/m/Y H\hi', $data['integrated_at'], new DateTimeZone('America/Sao_Paulo'));
		$payload->_fields['created_at'] = !isset($data['created_at']) ? null : new DateTimeImmutable($data['created_at'], new DateTimeZone('America/Sao_Paulo'));
		$payload->_fields['discount'] = !isset($data['discount']) ? null : Parser::anyToFloat($data['discount']);
		$payload->_fields['interest'] = !isset($data['interest']) ? null : Parser::anyToFloat($data['interest']);
		$payload->_fields['upsell_order_id'] = !isset($data['upsell_order_id']) ? null : Parser::anyToInteger($data['upsell_order_id']);
		$payload->_fields['origin'] = !isset($data['origin']) ? null : Parser::anyToString($data['origin']);
		$payload->_fields['total'] = !isset($data['total']) ? null : Parser::anyToFloat($data['total']);
		$payload->_fields['traffic_description'] = !isset($data['traffic_description']) ? null : Parser::anyToString($data['traffic_description']);
		$payload->_fields['full_payment_amount'] = !isset($data['full_payment_amount']) ? null : Parser::anyToFloat($data['full_payment_amount']);

		if (isset($data['freight_type'], $data['freight_value'])) {
			$payload->_freight = new GetFreightPayload(
				$data['freight_type'],
				$data['freight_value']
			);
		}

		if (isset($data['partner_total'], $data['partner_affiliate_total'])) {
			$payload->_partner = new GetPartnerPayload(
				$data['partner_total'],
				$data['partner_affiliate_total']
			);
		}

		if (isset($data['customer'])) {
			$payload->_customer = GetCustomerPayload::import($data['customer']);
		}

		if (isset($data['affiliate_commission']) && \is_array($data['affiliate_commission'])) {
			$payload->_affiliates = \array_map(function ($affiliate) {
				return GetAffiliateCommissionPayload::import($affiliate);
			}, $data['affiliate_commission']);
		}

		if (isset($data['co_production_commission']) && \is_array($data['co_production_commission'])) {
			$payload->_co_productions = \array_map(function ($affiliate) {
				return GetCoProductionCommissionPayload::import($affiliate);
			}, $data['co_production_commission']);
		}

		if (isset($data['visit'])) {
			$payload->_visit = GetVisitPayloaD::import($data['visit']);
		}

		if (isset($data['bundles']) && \is_array($data['bundles'])) {
			$payload->_bundles = \array_map(function ($bundle) {
				return GetBundlePayload::import($bundle);
			}, $data['bundles']);
		}

		if (isset($data['company_name'], $data['company_cnpj'], $data['company_email'])) {
			$payload->_company = new GetCompanyPayload(
				$data['company_name'],
				$data['company_cnpj'],
				$data['company_email']
			);
		}

		return $payload;
	}
}
