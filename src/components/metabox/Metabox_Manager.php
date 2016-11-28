<?php
namespace WBF\components\mvc;

interface Metabox_Manager {
	public function add_field($field_data,$group_name = false);
	public function add_field_group($group_data,$fields);
	public function get_field($field_name);
	public function print_field($field_name);
}