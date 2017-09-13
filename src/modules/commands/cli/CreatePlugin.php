<?php

namespace WBF\modules\commands\cli;

use WBF\modules\commands\BaseCommand;

class CreatePlugin extends BaseCommand {
	private $new_plugin_data;

	public function configure() {
		$this->set_name('wbf:create-plugin');
		$this->set_shortdesc("Create a new WBF-compatible plugin.");
	}

	public function __invoke($args,$assoc_args) {
		$type = $this->get_cli_value('Which type of plugin do you want to create? [s(imple)|c(omplex)|m(odular)]');

		if(!in_array($type,['s','c','m'])){
			\WP_CLI::error('Unrecognized plugin type.');
		}

		$this->new_plugin_data['type'] = $type;
		$this->new_plugin_data['name'] = $this->get_cli_value('What is the slug of your plugin? (eg: foobar)');
		$this->new_plugin_data['namespace'] = $this->get_cli_value('What is the namespace of your plugin? (eg: FooBar)');

		switch($type){
			case 's':
				$this->create_simple_plugin();
				break;
			case 'c':
				$this->create_complex_plugin();
				break;
			case 'm':
				$this->create_modular_plugin();
				break;
		}
	}

	private function create_simple_plugin(){
		\WP_CLI::success('Creating a simple plugin called: '.$this->new_plugin_data['name']);
	}

	private function create_complex_plugin(){
		\WP_CLI::success('Creating a complex plugin called: '.$this->new_plugin_data['name']);
	}

	private function create_modular_plugin(){
		\WP_CLI::success('Creating a modular plugin called: '.$this->new_plugin_data['name']);
	}
}