<?php

namespace WBF\modules\commands\cli;

use WBF\modules\commands\BaseCommand;

class CreatePlugin extends BaseCommand {
	public function configure() {
		$this->set_name('wbf:create-plugin');
		$this->set_shortdesc("Create e new WBF-compatible plugin.");
		$this->set_synopsis("
		* ## OPTIONS'
		* 
		* <type>,
		* : The type of the plugin to create (eg: simple)
		* ");
	}

	public function __invoke($args) {
		$type = $args[0];
		\WP_CLI::success($args[0]);
	}
}