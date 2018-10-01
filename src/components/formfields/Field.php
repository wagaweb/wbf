<?php

namespace WBF\components\formfields;

class Field{
	/**
	 * @var string
	 */
	private $key;
	/**
	 * @var string
	 */
	private $label;
	/**
	 * @var array
	 */
	private $validationRules;
	/**
	 * @var array
	 */
	private $sanitizationRules;
	/**
	 * @var string
	 */
	private $type;
	/**
	 * @var bool
	 */
	private $canBeEmpty;

	public function __construct($args) {
		$defaults = [
			'type' => 'text',
			'validation' => [],
			'sanitization' => ['text'],
			'allowEmpty' => true
		];
		$args = wp_parse_args($args,$defaults);
		if(!isset($args['key'])){
			throw new \Exception('No field key provided');
		}
		$this->key = $args['key'];
		$this->label = isset($args['label']) ? $args['label'] : preg_replace('/[-_]/',' ',ucfirst($args['key']));
		$this->validationRules = isset($args['validation']) && \is_array($args['validation']) ? $args['validation'] : [];
		$this->sanitizationRules = isset($args['sanitization']) && \is_array($args['sanitization']) ? $args['sanitization'] : ['text'];
		$this->type = $args['type'];
		$this->canBeEmpty = isset($args['allowEmpty']) ? (bool) $args['allowEmpty'] : true;
	}

	public function get_key(){
		return $this->key;
	}

	public function get_label(){
		return $this->label;
	}

	public function get_validation_rules(){
		return $this->validationRules;
	}

	public function get_sanitization_rules(){
		return $this->sanitizationRules;
	}

	public function get_type(){
		return $this->type;
	}

	public function can_be_empty(){
		return $this->canBeEmpty;
	}
}