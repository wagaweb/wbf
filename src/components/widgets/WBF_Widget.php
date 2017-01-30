<?php

namespace WBF\components\widgets;


class WBF_Widget extends \WP_Widget {


	/**
	 * Print Widgets options in the backend widget form
	 *
	 * @param   array   $instance           Previously saved values from database
	 * @param   array   $form_options
	 */
	public function print_options($instance, $form_options){

		foreach ($form_options as $slug => $form_option) {

			$class_name = __NAMESPACE__."\\fields\\".strtoupper($form_option['type']);

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
		}
	}
}