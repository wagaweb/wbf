<?php

namespace WBF\modules\commands\cli;

use WBF\modules\commands\BaseCommand;

class HelloWorld extends BaseCommand{
	public function configure() {
		$this->set_name("wbf:hello-world");
		$this->set_shortdesc('This command prints hello world');
	}
}