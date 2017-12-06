<?php

namespace WBF\legacy;

class Resources{
	public function get_working_directory($base = false){
		return WBF()->get_working_directory($base);
	}
	public function get_working_directory_uri($base = false){
		return WBF()->get_working_directory_uri($base);
	}
}