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

		$this->obtain_new_plugin_data('name','What is the slug of your plugin? (eg: Foobar Plugin)');
		$this->obtain_new_plugin_data('slug','What is the slug of your plugin? (eg: foobar)');
		$this->obtain_new_plugin_data('namespace','What is the namespace of your plugin? (eg: FooBar)');

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
			default:
				\WP_CLI::error('Invalid plugin type');
				break;
		}
	}

	private function create_simple_plugin(){
		$tpl_directory = __DIR__.'/skeleton/create-plugin/simple';
		$new_plugin_directory = $this->get_new_plugin_directory();
		$templatefile = $tpl_directory.'/templatefile';

		$this->parse_templatefile($templatefile,$new_plugin_directory);

		\WP_CLI::success('Creating a simple plugin called: '.$this->new_plugin_data['name'].' in: '.$new_plugin_directory);
	}

	private function create_complex_plugin(){
		$tpl_directory = __DIR__.'/skeleton/create-plugin/complex';
		$new_plugin_directory = $this->get_new_plugin_directory();
		$templatefile = $tpl_directory.'/templatefile';

		$this->parse_templatefile($templatefile,$new_plugin_directory);

		\WP_CLI::success('Created a complex plugin called: '.$this->new_plugin_data['name'].' in: '.$new_plugin_directory);
	}

	private function create_modular_plugin(){
		$tpl_directory = __DIR__.'/skeleton/create-plugin/modular';
		$new_plugin_directory = $this->get_new_plugin_directory();
		$templatefile = $tpl_directory.'/templatefile';

		$this->parse_templatefile($templatefile,$new_plugin_directory);
		
		\WP_CLI::success('Created a modular plugin called: '.$this->new_plugin_data['name'].' in: '.$new_plugin_directory);
	}

	/**
	 * @param $data_name
	 * @param $msg
	 */
	private function obtain_new_plugin_data($data_name,$msg){
		$this->new_plugin_data[$data_name] = $this->get_cli_value($msg);
		if(empty($this->new_plugin_data[$data_name])){
			\WP_CLI::error('This value cannot be empty');
		}
	}
	
	/**
	 * @return string
	 */
	private function get_new_plugin_directory(){
		return WP_CONTENT_DIR.'/plugins/'.$this->new_plugin_data['slug'];
	}

	/**
	 * Parse a templatefile and create the skeleton to the output directory
	 *
	 * @param $templatefile
	 * @param $output_directory
	 */
	private function parse_templatefile($templatefile,$output_directory,$args = []){
		$file = new \SplFileObject($templatefile);
		while(!$file->eof()){
			$line = $file->fgets();
			preg_match("|^([a-zA-Z-.]+):([\/a-zA-Z-.{}]+)$|","wbf-sample.php.tpl:/{{slug}}.php",$matches);
			if(isset($matches) && isset($matches[2])){
				$source_filename = $matches[1];
				$destination_path = $matches[2];
			}
		}
	}
}