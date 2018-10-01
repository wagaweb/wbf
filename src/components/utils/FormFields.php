<?php
namespace WBF\components\utils;

/**
 * Class FormFields
 *
 * This class provides an API to work with form fields.
 * Form fields can be registered, sanitized and validated.
 *
 * To register a set of fields, add a filter to 'wbf/utilities/form_fields/available' or 'wbf/utilities/form_fields/{$id}/available'
 * where {$id} is an unique identifier of a specific set.
 *
 * The filter must return an array of fields, formatted in a specific way.
 *
 * [
 *      'fieldKey' => [
 *          'label' => 'Field Label'
 *          'validation' => [...]
 *          'sanitization' => [...]
 *          'allowEmpty' => true,
 *      ]
 *      ...
 * ]
 *
 * @package WBF\components\utils
 */
class FormFields{
	/**
	 * Get all registered custom form fields
	 *
	 * @param null|string $id
	 *
	 * @return array
	 */
	public static function get_available_fields($id = null){
		if($id === null){
			$profile_fields = apply_filters('wbf/utilities/form_fields/'.$id.'/available',[]);
		}else{
			$profile_fields = apply_filters('wbf/utilities/form_fields/available',[]);
		}
		if(!\is_array($profile_fields)){
			$profile_fields = [];
		}
		return $profile_fields;
	}

	/**
	 * Validate and (optionally) sanitize a set of $fields
	 *
	 * @param array $fields
	 * @param string|null $set
	 * @param bool $sanitize
	 *
	 * @return array
	 */
	public static function validate_fields($fields,$set = null,$sanitize = true){
		if($sanitize){
			$fields = self::sanitize_fields($fields,$set);
		}
		$errors = [];
		foreach ($fields as $fieldKey => $fieldValue){
			$rules = self::get_field_validation_rules($fieldKey,$set);
			$allowEmpty = self::field_can_be_empty($fieldKey,$set);
			if(count($rules) === 0) continue;
			$label = self::get_field_label($fieldKey,$set);
			foreach ($rules as $rule){
				switch ($rule){
					case 'notEmpty':
						$fieldValue = sanitize_text_field($fieldValue);
						$fields[$fieldKey] = $fieldValue;
						if($fieldValue === ''){
							$errors[$fieldKey] = $label.': The field cannot be empty.';
						}
						break;
					case 'isEmail':
						$fieldValue = sanitize_email($fieldValue);
						$fields[$fieldKey] = $fieldValue;
						if(!is_email($fieldValue)){
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
		return $errors;
	}

	/**
	 * Sanitize a set of $fields
	 *
	 * @param array $fields
	 * @param string|null $set
	 *
	 * @return array
	 */
	private static function sanitize_fields($fields,$set = null){
		$sanitizedFields = [];
		foreach ($fields as $fieldKey => $fieldValue){
			$rules = self::get_field_sanitization_rules($fieldKey,$set);
			if(count($rules) === 0) continue;
			foreach ($rules as $rule){
				switch ($rule){
					case 'text':
						$sanitizedFields[$fieldKey] = sanitize_text_field($fieldValue);
						break;
					case 'textarea':
						$sanitizedFields[$fieldKey] = sanitize_textarea_field($fieldValue);
						break;
					case 'email':
						$sanitizedFields[$fieldKey] = sanitize_email($fieldValue);
						break;
					case 'url':
						$sanitizedFields[$fieldKey] = sanitize_url($fieldValue);
						break;
				}
			}
		}
		return $sanitizedFields;
	}

	/**
	 * Get the validation rules of a field
	 *
	 * @param string $fieldName
	 * @param string|null $set
	 *
	 * @return array
	 */
	private static function get_field_validation_rules($fieldName,$set = null){
		$field = self::get_single_field($fieldName,$set);
		if(!$field){
			return [];
		}
		return isset($field['validation']) && \is_array($field['validation']) ? $field['validation'] : [];
	}

	/**
	 * Get sanitization rules of a field
	 *
	 * @param string $fieldName
	 * @param string|null $set
	 *
	 * @return array
	 */
	private static function get_field_sanitization_rules($fieldName,$set = null){
		$field = self::get_single_field($fieldName,$set);
		if(!$field){
			return [];
		}
		return isset($field['sanitization']) && \is_array($field['sanitization']) ? $field['sanitization'] : ['text'];
	}

	/**
	 * Get a single field
	 *
	 * @param $fieldName
	 * @param string|null $set
	 *
	 * @return bool|array
	 */
	private static function get_single_field($fieldName,$set = null){
		$fields = self::get_available_fields($set);
		if(!isset($fields[$fieldName])) return false;
		return $fields[$fieldName];
	}

	/**
	 * Get the label of a field
	 *
	 * @param $fieldName
	 * @param string|null $set
	 *
	 * @return string
	 */
	private static function get_field_label($fieldName,$set = null){
		$field = self::get_single_field($fieldName,$set);
		if(!$field){
			return '';
		}
		return isset($field['label']) ? $field['label'] : preg_replace('/[-_]/',' ',ucfirst($fieldName));
	}

	/**
	 * Checks if a field can be empty
	 *
	 * @param $fieldName
	 * @param string|null $set
	 *
	 * @return bool
	 */
	private static function field_can_be_empty($fieldName,$set = null){
		$field = self::get_single_field($fieldName,$set);
		return isset($field['allowEmpty']) ? (bool) $field['allowEmpty'] : true;
	}
}