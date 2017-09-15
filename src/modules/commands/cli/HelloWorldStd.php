<?php

namespace WBF\modules\commands\cli;

class HelloWorldStd extends \WP_CLI_Command{
	public function __invoke() {
		\WP_CLI::success('Commands ready');
	}
}