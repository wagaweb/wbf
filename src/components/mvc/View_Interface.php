<?php

namespace WBF\components\mvc;

interface View_Interface{
	public function display($vars = []);
	public function get($vars = []);
}