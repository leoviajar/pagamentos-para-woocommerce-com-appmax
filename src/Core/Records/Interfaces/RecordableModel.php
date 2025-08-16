<?php

namespace AppMax\WooCommerce\Gateway\Core\Records\Interfaces;

use stdClass;

/**
 * Interface for a recordable model.
 *
 * @package \AppMax\WooCommerce\Gateway
 * @subpackage \AppMax\WooCommerce\Gateway\Core\Records\Interfaces
 * @version 0.1.0
 * @since 0.1.0
 * @category Interface
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @license piggly
 * @copyright 2023 Piggly Lab <dev@piggly.com.br>
 */
interface RecordableModel
{
	/**
	 * Get model id.
	 *
	 * @since 0.1.0
	 * @return int|null
	 */
	public function id(): ?int;

	/**
	 * Set model id.
	 *
	 * @since 0.1.0
	 * @return self
	 */
	public function preload(int $id);

	/**
	 * Get if it is preloaded from database.
	 *
	 * @since 0.1.0
	 * @return boolean
	 */
	public function isPreloaded(): bool;

	/**
	 * Create record object from model object.
	 *
	 * @since 0.1.0
	 * @return stdClass
	 */
	public function toRecord(): stdClass;

	/**
	 * Create model object from record object.
	 *
	 * @param stdClass $record
	 * @since 0.1.0
	 * @return self
	 */
	public static function fromRecord(stdClass $record);
}
