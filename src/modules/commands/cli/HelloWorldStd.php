<?php

namespace WBF\modules\commands\cli;

class HelloWorldStd{
	public function __invoke() {
		\WP_CLI::success('Commands ready');
	}
}