<?php

namespace AppMax\WooCommerce\Gateway\Core\Repositories;

use Exception;
use AppMax\WooCommerce\Gateway\Core\Database\Schemas\PaymentsTableSchema;
use AppMax\WooCommerce\Gateway\Core\Records\PaymentRecord;
use WC_Order;

/**
 * Payments database manipulation.
 *
 * @package \AppMax\WooCommerce\Gateway
 * @subpackage \AppMax\WooCommerce\Gateway\Core\Repositories
 * @version 0.1.0
 * @since 0.1.0
 * @category Repository
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license piggly
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class PaymentsRepo extends AbstractRepo
{
	/**
	 * Get by id.
	 *
	 * @param int $id
	 * @since 0.1.0
	 * @return PaymentRecord|null
	 */
	public static function byId(int $id)
	{
		global $wpdb;
		$results = static::byQuery($wpdb->prepare("SELECT * FROM {table} WHERE `id` = %d LIMIT 1", $id));
		return empty($results[0]) ? null : PaymentRecord::fromRecord($results[0]);
	}

	/**
	 * Get by id.
	 *
	 * @param WC_Order|integer $id
	 * @since 0.1.0
	 * @return PaymentRecord|null
	 */
	public static function byOrderId($id)
	{
		global $wpdb;
		$id = $id instanceof WC_Order ? $id->get_id() : $id;
		$results = static::byQuery($wpdb->prepare("SELECT * FROM {table} WHERE `order_id` = %d LIMIT 1", $id));
		return empty($results[0]) ? null : PaymentRecord::fromRecord($results[0]);
	}

	/**
	 * Get by external order id.
	 *
	 * @param integer $id
	 * @since 0.1.0
	 * @return PaymentRecord|null
	 */
	public static function byExternalOrderId(int $id)
	{
		global $wpdb;
		$results = static::byQuery($wpdb->prepare("SELECT * FROM {table} WHERE `ext_order_id` = %d LIMIT 1", $id));
		return empty($results[0]) ? null : PaymentRecord::fromRecord($results[0]);
	}

	/**
	 * Get by payment reference.
	 *
	 * @param WC_Order|integer $id
	 * @since 0.1.0
	 * @return PaymentRecord|null
	 */
	public static function byPaymentReference($id)
	{
		global $wpdb;
		$results = static::byQuery($wpdb->prepare("SELECT * FROM {table} WHERE `pay_reference` = %s  LIMIT 1", $id));
		return empty($results[0]) ? null : PaymentRecord::fromRecord($results[0]);
	}

	/**
	 * Unlink order in boletos.
	 *
	 * @param integer $id
	 * @since 0.1.0
	 * @return void
	 */
	public static function unlinkOrder(int $id)
	{
		static::update(['order_id' => null], ['order_id' => $id]);
	}

	/**
	 * Save model.
	 *
	 * @param PaymentRecord $model
	 * @since 0.1.0
	 * @return bool
	 */
	public static function save($model): bool
	{
		if (!($model instanceof PaymentRecord)) {
			throw new Exception('Expecting PaymentRecord.');
		}

		$saved = parent::save($model);

		if ($saved && $model->hasOrder()) {
			$order = $model->order();
			$order->update_meta_data('_pagamentos_para_woocommerce_com_appmax_paymentid', $model->id());
			$order->save();
		}

		return $saved;
	}

	/**
	 * Get table schema.
	 *
	 * @since 0.1.0
	 * @return AbstractTableSchema
	 */
	public static function schema(): PaymentsTableSchema
	{
		return new PaymentsTableSchema();
	}
}
