<?php

namespace AppMax\WooCommerce\Gateway\Api\Payloads\Create;

use AppMax\WooCommerce\Gateway\Api\Payloads\AbstractPayload;
use AppMax\WooCommerce\Gateway\Api\Utils\Parser;
use AppMax\WooCommerce\Gateway\Api\Validations\NotEmptyValidation;

/**
 * Product payload.
 *
 * @since 1.0.0
 * @package AppMax\WooCommerce\Gateway\Api
 * @subpackage AppMax\WooCommerce\Gateway\Api\Payloads\Create
 * @category Payloads
 * @author Caique Araujo <caique@piggly.com.br>
 * @author Piggly Lab <dev@piggly.com.br>
 * @copyright 2023 Piggly Lab
 */
class CreateProductPayload extends AbstractPayload
{
	/**
	 * All payload fields.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $_fields = [
		'sku' => null,
		'name' => null,
		'price' => null,
		'description' => null, // string
		'weight' => null, // float
		'height' => null, // float
		'length' => null, // float
		'width' => null, // float
		'external_id' => null, // integer
		'is_app' => false, // boolean
		'is_info' => false, // boolean
		'image' => null, // string
	];

	/**
	 * Construct object.
	 *
	 * @param string $sku
	 * @param string $name
	 * @param float $price
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct($sku, $name, $price)
	{
		$this->_fields['sku'] = Parser::anyToString(NotEmptyValidation::validate($sku, 'SKU'));
		$this->_fields['name'] = Parser::anyToString(NotEmptyValidation::validate($name, 'Nome'));
		$this->_fields['price'] = Parser::anyToFloat(NotEmptyValidation::validate($price, 'Preço'));
	}

	/**
	 * Get SKU field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getSku(): string
	{
		return $this->_fields['sku'];
	}

	/**
	 * Get name field.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getName(): string
	{
		return $this->_fields['name'];
	}

	/**
	 * Get price field.
	 *
	 * @since 1.0.0
	 * @return float
	 */
	public function getPrice(): float
	{
		return $this->_fields['price'];
	}

	/**
	 * Get description field.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function getDescription(): ?string
	{
		return $this->_fields['description'];
	}

	/**
	 * Get weight field.
	 *
	 * @since 1.0.0
	 * @return float|null
	 */
	public function getWeight(): ?float
	{
		return $this->_fields['weight'];
	}

	/**
	 * Get height field.
	 *
	 * @since 1.0.0
	 * @return float|null
	 */
	public function getHeight(): ?float
	{
		return $this->_fields['height'];
	}

	/**
	 * Get length field.
	 *
	 * @since 1.0.0
	 * @return float|null
	 */
	public function getLength(): ?float
	{
		return $this->_fields['length'];
	}

	/**
	 * Get width field.
	 *
	 * @since 1.0.0
	 * @return float|null
	 */
	public function getWidth(): ?float
	{
		return $this->_fields['width'];
	}

	/**
	 * Get external id field.
	 *
	 * @since 1.0.0
	 * @return int|null
	 */
	public function getExternalId(): ?int
	{
		return $this->_fields['external_id'];
	}

	/**
	 * Get is app field.
	 *
	 * @since 1.0.0
	 * @return bool|null
	 */
	public function isApp(): ?bool
	{
		return $this->_fields['is_app'];
	}

	/**
	 * Get is info field.
	 *
	 * @since 1.0.0
	 * @return bool|null
	 */
	public function isInfo(): ?bool
	{
		return $this->_fields['is_info'];
	}

	/**
	 * Get image field.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function getImage(): ?string
	{
		return $this->_fields['image'];
	}

	/**
	 * Set description.
	 *
	 * @param string $description
	 * @return self
	 * @since 1.0.0
	 */
	public function setDescription($description)
	{
		$this->_fields['description'] = Parser::anyToString($description);
		return $this;
	}

	/**
	 * Set weight.
	 *
	 * @param float $weight
	 * @return self
	 * @since 1.0.0
	 */
	public function setWeight($weight)
	{
		$this->_fields['weight'] = Parser::anyToFloat($weight);
		return $this;
	}

	/**
	 * Set height.
	 *
	 * @param float $height
	 * @return self
	 * @since 1.0.0
	 */
	public function setHeight($height)
	{
		$this->_fields['height'] = Parser::anyToFloat($height);
		return $this;
	}

	/**
	 * Set length.
	 *
	 * @param float $length
	 * @return self
	 * @since 1.0.0
	 */
	public function setLength($length)
	{
		$this->_fields['length'] = Parser::anyToFloat($length);
		return $this;
	}

	/**
	 * Set width.
	 *
	 * @param float $width
	 * @return self
	 * @since 1.0.0
	 */
	public function setWidth($width)
	{
		$this->_fields['width'] = Parser::anyToFloat($width);
		return $this;
	}

	/**
	 * Set external id.
	 *
	 * @param int $external_id
	 * @return self
	 * @since 1.0.0
	 */
	public function setExternalId($external_id)
	{
		$this->_fields['external_id'] = Parser::anyToInteger($external_id);
		return $this;
	}

	/**
	 * Set app as true.
	 *
	 * @param bool $is_app
	 * @return self
	 * @since 1.0.0
	 */
	public function asApp()
	{
		$this->_fields['is_app'] = true;
		return $this;
	}

	/**
	 * Set info as true.
	 *
	 * @return self
	 * @since 1.0.0
	 */
	public function asInfo()
	{
		$this->_fields['is_info'] = true;
		return $this;
	}

	/**
	 * Set image.
	 *
	 * @param string $image
	 * @return self
	 * @since 1.0.0
	 */
	public function setImage($image)
	{
		$this->_fields['image'] = Parser::anyToString($image);
		return $this;
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
			'sku' => $this->getSku(),
			'name' => $this->getName(),
			'price' => $this->getPrice(),
		];

		if (!empty($this->getDescription())) {
			$arr['description'] = $this->getDescription();
		}

		if (!empty($this->getWeight())) {
			$arr['weight'] = $this->getWeight();
		}

		if (!empty($this->getHeight())) {
			$arr['height'] = $this->getHeight();
		}

		if (!empty($this->getLength())) {
			$arr['length'] = $this->getLength();
		}

		if (!empty($this->getWidth())) {
			$arr['width'] = $this->getWidth();
		}

		if (!empty($this->getExternalId())) {
			$arr['external_id'] = $this->getExternalId();
		}

		if (!empty($this->isApp())) {
			$arr['is_app'] = $this->isApp();
		}

		if (!empty($this->isInfo())) {
			$arr['is_info'] = $this->isInfo();
		}

		if (!empty($this->getImage())) {
			$arr['image'] = $this->getImage();
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
		return $this->toArray();
	}

	/**
	 * Import and return the object.
	 *
	 * @param array $body
	 * @since 1.0.0
	 * @return CreateProductPayload
	 */
	public static function import(array $data = []): CreateProductPayload
	{
		$payload = new CreateProductPayload($data['sku'], $data['name'], $data['price']);

		if (isset($data['description'])) {
			$payload->setDescription($data['description']);
		}

		if (isset($data['weight'])) {
			$payload->setWeight($data['weight']);
		}

		if (isset($data['height'])) {
			$payload->setHeight($data['height']);
		}

		if (isset($data['length'])) {
			$payload->setLength($data['length']);
		}

		if (isset($data['width'])) {
			$payload->setWidth($data['width']);
		}

		if (isset($data['external_id'])) {
			$payload->setExternalId($data['external_id']);
		}

		if (isset($data['is_app']) && $data['is_app'] === true) {
			$payload->asApp();
		}

		if (isset($data['is_info']) && $data['is_info'] === true) {
			$payload->asInfo();
		}

		if (isset($data['image'])) {
			$payload->setImage($data['image']);
		}

		return $payload;
	}
}
