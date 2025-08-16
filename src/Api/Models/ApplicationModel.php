<?php

namespace AppMax\WooCommerce\Gateway\Api\Models;

use AppMax\WooCommerce\Gateway\Api\Environments\HomologationEnvironment;
use AppMax\WooCommerce\Gateway\Api\Environments\ProductionEnvironment;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Interfaces\EnvInterface;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Models\ApplicationModel as BaseApplicationModel;

/**
 * Application model structure.
 *
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Models
 * @version 0.1.0
 * @since 0.1.0
 * @category Model
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license PGLY
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
class ApplicationModel extends BaseApplicationModel
{
	/**
	 * Must return the Environment object according to
	 * the current Environment.
	 *
	 * @since 0.1.0
	 * @return EnvInterface
	 */
	public function createEnvironment(): EnvInterface
	{
		return $this->_fields['environment'] === static::ENV_PRODUCTION
			? new ProductionEnvironment()
			: new HomologationEnvironment();
	}

	/**
	 * Export object data to an array.
	 *
	 * @since 0.1.0
	 * @return array
	 */
	public function export(): array
	{
		$arr = parent::export();
		return $arr;
	}

	/**
	 * Create a new application model with data.
	 *
	 * @param array $data
	 * @since 0.1.0
	 * @return self
	 */
	public static function import(array $data)
	{
		$m = parent::import($data);
		return $m;
	}
}
