<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Get\Order;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;

/**
 * Tracking payload.
 * Generally used in order payload.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Payloads\Get\Order
 * @category Payloads
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class GetVisitPayload extends AbstractPayload
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $_fields = [
		'utm_source' => null,
		'utm_campaign' => null,
		'utm_medium' => null,
		'utm_content' => null,
		'utm_term' => null,
		'affiliate_id' => null
	];

	/**
	 * Get utm_source field.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function getSource(): ?string
	{
		return $this->_fields['utm_source'];
	}

	/**
	 * Get utm_campaign field.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function getCampaign(): ?string
	{
		return $this->_fields['utm_campaign'];
	}

	/**
	 * Get utm_medium field.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function getMedium(): ?string
	{
		return $this->_fields['utm_medium'];
	}

	/**
	 * Get utm_content field.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function getContent(): ?string
	{
		return $this->_fields['utm_content'];
	}

	/**
	 * Get utm_term field.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function getTerm(): ?string
	{
		return $this->_fields['utm_term'];
	}

	/**
	 * Get affiliate_id field.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function getAffiliateId(): ?string
	{
		return $this->_fields['affiliate_id'];
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
		return $this->_fields;
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
		return $this->toArray();
	}

	/**
	 * Import and return the object.
	 *
	 * @param array $body
	 * @since 1.0.0
	 * @return GetVisitPayload*/
	public static function import(array $data = []): GetVisitPayload
	{
		$payload = new GetVisitPayload();

		if (isset($data['utm_source'])) {
			$payload->_fields['utm_source'] = Parser::anyToString($data['utm_source']);
		}

		if (isset($data['utm_campaign'])) {
			$payload->_fields['utm_campaign'] = Parser::anyToString($data['utm_campaign']);
		}

		if (isset($data['utm_medium'])) {
			$payload->_fields['utm_medium'] = Parser::anyToString($data['utm_medium']);
		}

		if (isset($data['utm_content'])) {
			$payload->_fields['utm_content'] = Parser::anyToString($data['utm_content']);
		}

		if (isset($data['utm_term'])) {
			$payload->_fields['utm_term'] = Parser::anyToString($data['utm_term']);
		}

		if (isset($data['utm_term'])) {
			$payload->_fields['affiliate_id'] = Parser::anyToString($data['affiliate_id']);
		}

		return $payload;
	}
}
