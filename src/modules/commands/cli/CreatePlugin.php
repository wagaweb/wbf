<?php

namespace WBF\modules\commands\cli;

use WBF\modules\commands\BaseCommand;

class CreatePlugin extends BaseCommand {
	public function configure() {
		$this->set_name('wbf:create-plugin');
	}

	public function __invoke() {
		parent::__invoke();
	}
}