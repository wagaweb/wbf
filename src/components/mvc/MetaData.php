<?php

namespace WBF\components\mvc;

class MetaData
{
	const TYPE_POSTMETA = 'postmeta';
	const TYPE_USERMETA = 'usermeta';
	const TYPE_TERMMETA = 'termmeta';

	private $key;
	private $value;
	private $setterMethodName;
	private $getterMethodName;
	private $type;

	public function __construct($key, $setterMethodName, $getterMethodName, $type, $value = null) {
		$this->key = $key;
		$this->value = $value;
		$this->setterMethodName = $setterMethodName;
		$this->getterMethodName = $getterMethodName;
		$this->type = $type;
	}

	/**
	 * @param mixed $value
	 */
	public function setValue( $value ) {
		$this->value = $value;
	}

	/**
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @return mixed
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * @return string
	 */
	public function getGetterMethodName() {
		return $this->getterMethodName;
	}

	/**
	 * @return mixed
	 */
	public function getSetterMethodName() {
		return $this->setterMethodName;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}
}