<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Create\Customer;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;

/**
 * Tracking payload.
 * Generally used in customer payload.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Payloads\Create\Customer
 * @category Payloads
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class CreateTrackingPayload extends AbstractPayload
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
		'utm_term' => null
	];

	/**
	 * set utm_source field.
	 *
	 * @param mixed $source
	 * @since 1.0.0
	 * @return self
	 */
	public function setSource($source)
	{
		$this->_fields['utm_source'] = Parser::anyToString($source);
		return $this;
	}

	/**
	 * set utm_campaign field.
	 *
	 * @param mixed $source
	 * @since 1.0.0
	 * @return self
	 */
	public function setCampaign($campaign)
	{
		$this->_fields['utm_campaign'] = Parser::anyToString($campaign);
		return $this;
	}

	/**
	 * set utm_medium field.
	 *
	 * @param mixed $source
	 * @since 1.0.0
	 * @return self
	 */
	public function setMedium($medium)
	{
		$this->_fields['utm_medium'] = Parser::anyToString($medium);
		return $this;
	}

	/**
	 * set utm_content field.
	 *
	 * @param mixed $source
	 * @since 1.0.0
	 * @return self
	 */
	public function setContent($content)
	{
		$this->_fields['utm_content'] = Parser::anyToString($content);
		return $this;
	}

	/**
	 * set utm_term field.
	 *
	 * @param mixed $source
	 * @since 1.0.0
	 * @return self
	 */
	public function setTerm($term)
	{
		$this->_fields['utm_term'] = Parser::anyToString($term);
		return $this;
	}

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
	 * @return CreateTrackingPayload*/
	public static function import(array $data = []): CreateTrackingPayload
	{
		$payload = new CreateTrackingPayload();

		if (isset($data['utm_source'])) {
			$payload->setSource($data['utm_source']);
		}

		if (isset($data['utm_campaign'])) {
			$payload->setCampaign($data['utm_campaign']);
		}

		if (isset($data['utm_medium'])) {
			$payload->setMedium($data['utm_medium']);
		}

		if (isset($data['utm_content'])) {
			$payload->setContent($data['utm_content']);
		}

		if (isset($data['utm_term'])) {
			$payload->setTerm($data['utm_term']);
		}

		return $payload;
	}
}
