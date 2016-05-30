<?php

namespace wbf\components\mvc;

interface View_Interface{
	public function display($vars = []);
	public function get($vars = []);
}