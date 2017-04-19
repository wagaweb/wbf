<?php

namespace WBF\components\widgets;

class WBF_Widget extends \WP_Widget {

	/**
	 * Return the class name of field type $type
	 *
	 * @param $field_type
	 *
	 * @return string
	 * @throws \Exception
	 */
	private function get_class_name($field_type){
		$class_name_prefix = __NAMESPACE__."\\fields\\";
		$field_type_to_class = [
			'checkbox' => 'Checkbox',
			'multicheckbox' => 'MultiCheckbox',
			'radio' => 'Radio',
			'select' => 'Select',
			'text' => 'Text',
		];
		if(array_key_exists($field_type,$field_type_to_class)){
			$class_name = $class_name_prefix.$field_type_to_class[$field_type];
			return $class_name;
		}else{
			throw new \Exception("Invalid field type");
		}
	}

	/**
	 * Print Widgets options in the backend widget form
	 *
	 * @param   array   $instance           Previously saved values from database
	 * @param   array   $form_options
	 */
	public function print_options($instance, $form_options){
		foreach ($form_options as $slug => $form_option) {
			try{
				$class_name = $this->get_class_name($form_option['type']);
				if(class_exists($class_name)){
					$field = [
						'slug' => $slug,
						'id' => $this->get_field_id($slug),
						'name' => $this->get_field_name($slug),
						'options' => $form_option
					];
					$f = new $class_name($instance, $field);
					$f->get_html();
				}
			}catch (\Exception $e){}
		}
	}
}