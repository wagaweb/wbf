<?php

namespace WBF\components\mvc;

class MetaData
{
	private $key;
	private $value;

	public function __construct($key, $value = null) {
		$this->key = $key;
		$this->value = $value;
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
}