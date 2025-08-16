<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads;

use JsonSerializable;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Interfaces\Importable;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Interfaces\Arrayable;
use AppMax\WooCommerce\Gateway\Vendor\Piggly\ApiClient\Interfaces\SensitiveDataArrayable;

/**
 * Abstraction for payloads.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Payloads\Create
 * @category Payloads
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
abstract class AbstractPayload implements Arrayable, SensitiveDataArrayable, JsonSerializable, Importable
{
	/**
	 * Serializes the object to a value that can be serialized natively by json_encode().
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function jsonSerialize()
	{
		return $this->toArray();
	}
}
