<?php
/*
 * This file is part of WBF Framework: https://www.waboot.io
 *
 * @author WAGA Team
 */

namespace WBF\components\mvc;

interface View_Interface{
	public function display($vars = []);
	public function get($vars = []);
}