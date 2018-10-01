<?php

namespace WBF\components\formfields;

use WBF\components\mvc\HTMLView;
use WBF\components\utils\Arrays;
use WBF\components\utils\Utilities;

/**
 * Class FieldsSet
 *
 * This class provides an API to work with form fields.
 * Form fields can be registered, sanitized, validated and rendered.
 *
 * To register a set of fields, add a filter to 'wbf/utilities/form_fields/available' or 'wbf/utilities/form_fields/{$id}/available'
 * where {$id} is an unique identifier of a specific set.
 *
 * The filter must return an array of fields, formatted in a specific way.
 *
 * [
 *      'fieldKey' => [
 *          'label' => 'Field Label' //A simple label
 *          'type' => 'text', //The type of the field, used for rendering,
 *          'options' => [...] //An array of key=>value pair used for the rendering of list fields (select, radios, checkboxes)
 *          'validation' => [...] //An array of validation rules: notEmpty,isEmail,isUrl,password
 *          'sanitization' => [...] //An array of sanitization rules: text,textarea,email,url.
 *                                  //If none is provided, 'text' will be used.
 *          'allowEmpty' => true, //A flag to tell if the field can expected to be empty (used during validation\sanitization)
 *      ]
 *      ...
 * ]
 *
 * This API does NOT provide the rendering layer. The function render_field() tries to create a view based on field type
 * and a template located in a predefined directory.
 *
 * By default, for a field of type 'text', this template will be used:
 * get_stylesheet_directory().'/views/form-fields/text.php'
 *
 * An HTMLView will be initialized based on this template. The view will receive:
 * $label, $name, $value and $args, where $args is the field array.
 *
 * @package WBF\components\formfields
 */
class FieldsSet{
	/**
	 * @var string
	 */
	private $id;
	/**
	 * @var array
	 */
	private $fields;
	/**
	 * @var array
	 */
	private $postedFields;
	/**
	 * @var array
	 */
	private $sanitizedPostedFields;

	const FORM_FIELDS_GROUP_PREFIX = 'wbf_form_fields';

	/**
	 * FieldsSet constructor.
	 *
	 * @param string|null $id an unique identifier for the set
	 * @param bool $loadFields
	 */
	public function __construct($id = null, $loadFields = true) {
		if($id === null){
			$id = 'default';
		}
		$this->id = $id;
		if($loadFields){
			//Try to get the Field instances
			$fields = $this->retrieve_raw_available_fields();
			if(Arrays::is_iterable($fields)){
				$fieldsObjs = [];
				array_walk($fields,function($field) use(&$fieldsObjs){
					try{
						$f = new Field($field);
						$fieldsObjs[$f->get_key()] = $f;
					}catch (\Exception $e){}
				});
				$this->set_fields($fieldsObjs);
			}else{
				$this->fields = [];
			}
			//Try to get the posted fields
			if(isset($_POST[self::FORM_FIELDS_GROUP_PREFIX][$this->id])){
				$this->set_posted_fields($_POST[self::FORM_FIELDS_GROUP_PREFIX][$this->id]);
			}
		}
	}

	/**
	 * @return string
	 */
	public function get_id(){
		return $this->id;
	}

	/**
	 * Get set fields
	 *
	 * @return array
	 */
	public function get_fields(){
		if(!isset($this->fields) || !\is_array($this->fields)){
			$this->fields = [];
		}
		return $this->fields;
	}

	/**
	 * Set fields
	 *
	 * @param array $fields
	 */
	public function set_fields($fields){
		if(!\is_array($fields)){
			$fields = [];
		}
		$this->fields = $fields;
	}

	/**
	 * Set the posted fields
	 *
	 * @param $fields
	 */
	public function set_posted_fields($fields){
		if(\is_array($fields)){
			$this->postedFields;
		}
	}

	/**
	 * Get the posted fields (aka: the key\values pair from $_POST)
	 *
	 * @return array
	 */
	public function get_posted_fields(){
		if(isset($this->postedFields) && \is_array($this->postedFields)){
			return $this->postedFields;
		}
		return [];
	}

	/**
	 * Retrieve the registered fields (not Field instances)
	 *
	 * @return array
	 */
	public function retrieve_raw_available_fields(){
		$fields = apply_filters('wbf/utilities/form_fields/'.$this->get_id().'/available',[]);
		if(!\is_array($fields)){
			$fields = [];
		}
		return $fields;
	}

	/**
	 * Validates the fields in the set. Returns an array of errors.
	 *
	 * @param bool $sanitize
	 *
	 * @return array
	 */
	public function validate_fields($sanitize = true){
		$fields = $this->get_posted_fields();
		if($sanitize){
			$this->sanitize_fields();
			$fields = $this->get_sanitized_posted_fields();
		}
		$errors = [];
		foreach ($fields as $fieldKey => $fieldValue){
			if(!$this->field_exists($fieldKey)) continue;
			$field = $this->get_field($fieldKey);
			if(!$field instanceof Field) continue;
			$rules = $field->get_sanitization_rules();
			$allowEmpty = $field->can_be_empty();
			if(count($rules) === 0) continue;
			$label = $field->get_label();
			foreach ($rules as $rule){
				switch ($rule){
					case 'notEmpty':
						if($fieldValue === ''){
							$errors[$fieldKey] = $label.': The field cannot be empty.';
						}
						break;
					case 'isEmail':
						if(!\is_email($fieldValue)){
							$errors[$fieldKey] = $label.': The field must be an email address.';
						}
						break;
					case 'isUrl':
						if($fieldValue === '' && $allowEmpty){
							break;
						}
						$r = Utilities::validate_url($fieldValue);
						if(!$r){
							$errors[$fieldKey] = $label.': The field must be a valid website address.';
						}
						break;
					case 'password':
						if(strlen($fieldValue) < 8) {
							$errors[$fieldKey] = $label.': Password too short (min 8 chars, number AND letters).';
						}
						if (!preg_match('#[0-9]+#', $fieldValue)) {
							$errors[$fieldKey] = $label.': The password must contain at least a number.';
						}
						if (!preg_match('#[a-zA-Z]+#', $fieldValue)) {
							$errors[$fieldKey] = $label.': The password must contain at least a character.';
						}
						break;
				}
				if(\is_callable($rule) && function_exists($rule)){
					$r = $rule($fieldKey,$fieldValue,$fields);
					if(\is_string($r)){
						$errors[$fieldKey] = $r;
					}
				}
				if(preg_match('|^field:([a-zA-Z]+)|',$rule,$field_to_match)){
					$field_to_match = $field_to_match[1];
					if(!isset($fields[$field_to_match])){
						$errors[$fieldKey] = $label.': The compare field does not exists.';
					}
					if($fieldValue !== $fields[$field_to_match]){
						$errors[$fieldKey] = $label.': The field is not the same.';
					}
				}
			}
		}
		return $errors;
	}

	/**
	 * Sanitize the fields in the set
	 *
	 * @return void
	 */
	public function sanitize_fields(){
		$sanitizedFields = [];
		$fields = $this->get_posted_fields();
		foreach ($fields as $fieldKey => $fieldValue){
			if(!$this->field_exists($fieldKey)) continue;
			$field = $this->get_field($fieldKey);
			if(!$field instanceof Field) continue;
			$rules = $field->get_sanitization_rules();
			if(count($rules) === 0) continue;
			foreach ($rules as $rule){
				switch ($rule){
					case 'text':
						$sanitizedFields[$fieldKey] = \sanitize_text_field($fieldValue);
						break;
					case 'textarea':
						$sanitizedFields[$fieldKey] = \sanitize_textarea_field($fieldValue);
						break;
					case 'email':
						$sanitizedFields[$fieldKey] = \sanitize_email($fieldValue);
						break;
					case 'url':
						$sanitizedFields[$fieldKey] = \sanitize_url($fieldValue);
						break;
				}
			}
		}
		$this->sanitizedPostedFields = $sanitizedFields;
	}

	/**
	 * @return array
	 */
	public function get_sanitized_posted_fields(){
		if(isset($this->sanitizedPostedFields) && \is_array($this->sanitizedPostedFields)){
			return $this->sanitizedPostedFields;
		}
		return [];
	}

	/**
	 * @param $fieldKey
	 *
	 * @return Field|false
	 */
	public function get_field($fieldKey){
		if($this->field_exists($fieldKey)){
			return $this->get_fields()[$fieldKey];
		}
		return false;
	}

	/**
	 * @param $fieldKey
	 *
	 * @return bool
	 */
	public function field_exists($fieldKey){
		return isset($this->get_fields()[$fieldKey]);
	}

	/**
	 * @param string $fieldKey
	 * @param mixed $fieldValue
	 * @param bool|string $customTpl
	 *
	 * @return void
	 */
	public function render_field($fieldKey,$fieldValue,$customTpl = false){
		$field = $this->get_field($fieldKey);
		if(!$field || !$field instanceof Field) echo '';
		$fieldType = $field->get_type();
		try{
			$baseDir = get_stylesheet_directory().'/views/form-fields/';
			$fieldViewsDirectory = apply_filters('wbf/utilities/form_fields/'.$this->id.'/views_directory',$baseDir);
			$v = new HTMLView($fieldViewsDirectory.'/'.$fieldType.'.php',null,false);
			$v->display([
				'label' => $field->get_label(),
				'name' => self::FORM_FIELDS_GROUP_PREFIX.'['.$this->id.']['.$field->get_key().']',
				'value' => $fieldValue,
				'args' => $field
			]);
		}catch (\Exception $e){
			echo 'Unable to display the field template';
		}
	}
}