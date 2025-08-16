<?php

namespace AppMax\WooCommerce\Gateway\Api\Values;

/**
 * Installment payload.
 * Generally used in payment payload.
 *
 * @since 1.1.0-beta
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Values
 * @category Payloads
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class InstallmentValue
{
	/**
	 * Total calcultated.
	 *
	 * @var float
	 * @since 1.1.0-beta
	 */
	protected $_total;

	/**
	 * Installment related.
	 *
	 * @var int
	 * @since 1.1.0-beta
	 */
	protected $_installment;

	/**
	 * Installment amount.
	 *
	 * @var float
	 * @since 1.1.0-beta
	 */
	protected $_installment_amount;

	/**
	 * Create an installment.
	 *
	 * @param float $total
	 * @param integer $installment
	 */
	public function __construct(float $total, int $installment)
	{
		$this->_total = $total;
		$this->_installment = $installment;
		$this->_installment_amount = $total / $installment;
	}

	/**
	 * Get total.
	 *
	 * @since 1.1.0-beta
	 * @return float
	 */
	public function getTotal(): float
	{
		return $this->_total;
	}

	/**
	 * Get installment.
	 *
	 * @since 1.1.0-beta
	 * @return integer
	 */
	public function getInstallment(): int
	{
		return $this->_installment;
	}

	/**
	 * Get installment amount.
	 *
	 * @since 1.1.0-beta
	 * @return float
	 */
	public function getInstallmentAmount(): float
	{
		return $this->_installment_amount;
	}

	/**
	 * Get interest tax.
	 *
	 * @param float $total
	 * @since 1.1.0-beta
	 * @return float
	 */
	public function getInterest(float $total): float
	{
		if ($this->_total >= $total) {
			return $this->_total - $total;
		}

		return $total - $this->_total;
	}
}
