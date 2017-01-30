<?php

namespace WBF\components\widgets\fields;

abstract class Field {
	var $id;
	var $name;
	var $options = [];
	var $slug;
	var $instance;

	public function __construct($instance, $field) {
		$this->id = $field['id'];
		$this->name = $field['name'];
		$this->options = $field['options'];
		$this->slug = $field['slug'];
		$this->instance = $instance;
	}

	public function get_html(){
		// implement this function to print the html of the form
	}
}