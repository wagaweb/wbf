<?php

namespace WBF\components\forms;

abstract class BaseForm {
	/**
	 * @var string
	 */
	private $method;

	/**
	 * @var string
	 */
	private $action;

	/**
	 * @var string
	 */
	private $name_prefix;

	/**
	 * @var array|boolean
	 */
	private $invalid_fields;

	/**
	 * Form constructor.
	 *
	 * @param array $fields
	 * @param array $params
	 */
	public function __construct(array $fields, array $params) {

	}

	/**
	 * @return string
	 */
	public function getMethod(): string {
		return $this->method;
	}

	/**
	 * @param string $method
	 */
	public function setMethod( string $method ) {
		$this->method = $method;
	}

	/**
	 * @return string
	 */
	public function getAction(): string {
		return $this->action;
	}

	/**
	 * @param string $action
	 */
	public function setAction( string $action ) {
		$this->action = $action;
	}

	/**
	 * @return string
	 */
	public function getNamePrefix(): string {
		return $this->name_prefix;
	}

	/**
	 * @param string $name_prefix
	 */
	public function setNamePrefix( string $name_prefix ) {
		$this->name_prefix = $name_prefix;
	}

	/**
	 * @return array|bool
	 */
	public function getInvalidFields() {
		return $this->invalid_fields;
	}

	/**
	 * @param array|bool $invalid_fields
	 */
	public function setInvalidFields( $invalid_fields ) {
		$this->invalid_fields = $invalid_fields;
	}

}