<?php
namespace WBF\extensions\acf;

use WBF\components\mvc\Metabox_Manager;

class ACF_Metabox_Manager implements Metabox_Manager {

	public function add_field( $field_data, $group_name = false ) {
		// TODO: Implement add_field() method.
	}

	public function add_field_group( $group_data, $fields ) {
		$group_data = wp_parse_args($group_data,[
			'key' => isset($group_data['name']) ? "field_".$group_data['name'] : "",
			'label' => "",
			'name' => "",
			'type' => '',
			'required' => false
		]);

		$acf_field_group = $group_data;
		$acf_field_group['fields'] = $fields;

		\acf_add_local_field_group($acf_field_group);
	}

	public function get_field( $field_name ) {
		// TODO: Implement get_field() method.
	}

	public function print_field( $field_name ) {
		// TODO: Implement print_field() method.
	}
}