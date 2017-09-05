<?php

namespace WBF\modules\commands\cli;

use WBF\modules\commands\BaseCommand;

class CreatePlugin extends BaseCommand {
	public function configure() {
		$this->set_name('wbf:create-plugin');
		$this->set_shortdesc("Create e new WBF-compatible plugin.");
		$this->set_synopsis([
			[
				'type' => 'positional',
				'name' => 'type',
				'description' => 'The plugin type to create'
			]
		]);
	}

	public function __invoke($args,$assoc_args) {
		$type = $args[0];
		\WP_CLI::success($args[0]);
	}
}