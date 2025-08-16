<?php

namespace AppMax\WooCommerce\Gateway\Core\Repositories;

use Exception;
use AppMax\WooCommerce\Gateway\Core\Database\Schemas\AbstractTableSchema;
use AppMax\WooCommerce\Gateway\Core\Models\Interfaces\RecordableModel;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\Wordpress\Connector;
use stdClass;

/**
 * Abstract repository.
 *
 * @package \AppMax\WooCommerce\Gateway
 * @subpackage \AppMax\WooCommerce\Gateway\Core\Repo
 * @version 0.1.0
 * @since 0.1.0
 * @category Repo
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license piggly
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
abstract class AbstractRepo
{
	/**
	 * Get by query.
	 *
	 * @param string $sql
	 * @since 0.1.0
	 * @return array<stdClass>
	 */
	protected static function byQuery(string $sql): array
	{
		global $wpdb;
		$table = static::schema()::tableName();

		return $wpdb->get_results(\str_replace('{table}', $table, $sql));
	}

	/**
	 * Save model.
	 *
	 * @param RecordableModel $model
	 * @since 0.1.0
	 * @return bool
	 */
	public static function save($model): bool
	{
		$data = \json_decode(\json_encode($model->toRecord()), true);

		try {
			if ($model->isPreloaded()) {
				static::update($data, ['id' => $model->id()]);
			} else {
				$data = static::insert($data, 'id');
				$model->preload($data['id']);
			}

			return true;
		} catch(Exception $e) {
			Connector::debugger()->force()->error($e->getMessage());
			throw new Exception('Não foi possível iniciar a operação. Tente novamente ou contate o administrador da loja.');
		}
	}

	/**
	 * Insert record.
	 *
	 * @param array $data
	 * @param string $primary_key
	 * @since 0.1.0
	 * @return array
	 * @throws Exception if fail
	 */
	protected static function insert(array $data, string $primary_key): array
	{
		global $wpdb;
		$table = static::schema()::tableName();

		if ($wpdb->insert($table, $data) === \false) {
			$error = empty($wpdb->last_error) ? $wpdb->last_query : $wpdb->last_error;
			throw new Exception("Erro ao executar a query: {$error}.");
		}

		$data[$primary_key] = $wpdb->insert_id;
		return $data;
	}

	/**
	 * Update record.
	 *
	 * @param array $data
	 * @param array $where
	 * @since 0.1.0
	 * @return array
	 * @throws Exception if fail
	 */
	protected static function update(array $data, array $where = []): array
	{
		global $wpdb;
		$table = static::schema()::tableName();

		if ($wpdb->update($table, $data, $where) === false) {
			$error = empty($wpdb->last_error) ? $wpdb->last_query : $wpdb->last_error;
			throw new Exception("Erro ao executar a query: {$error}.");
		}

		return $data;
	}
	/**
	 * Delete record.
	 *
	 * @param array $where
	 * @since 0.1.0
	 * @return bool
	 */
	protected static function delete(array $where): bool
	{
		global $wpdb;
		$table = static::schema()::tableName();

		if ($wpdb->delete($table, $where) === false) {
			$error = empty($wpdb->last_error) ? $wpdb->last_query : $wpdb->last_error;
			throw new Exception("Erro ao executar a query: {$error}.");
		}

		return true;
	}

	/**
	 * Get table schema.
	 *
	 * @since 0.1.0
	 * @return AbstractTableSchema
	 */
	abstract public static function schema(): AbstractTableSchema;
}
