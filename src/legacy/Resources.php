<?php

namespace WBF\legacy;

class Resources{
	public function get_working_directory(){
		return WBF()->get_working_directory();
	}
	public function get_working_directory_uri($base = false){
		return WBF()->get_working_directory_uri($base);
	}
}