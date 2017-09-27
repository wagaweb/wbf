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

		$this->obtain_new_plugin_data('name','What is the name of your plugin? (eg: Foobar Plugin)');
		$this->obtain_new_plugin_data('slug','What is the slug of your plugin? (eg: foobar)');
		$this->obtain_new_plugin_data('namespace','What is the namespace of your plugin? (eg: FooBar)');
		$this->obtain_new_plugin_data('description','What is the description of your plugin? (default: empty)', true, true);
		$this->obtain_new_plugin_data('url','What is the URL of your plugin? (default: empty)',true,true);

		switch($type){
			case 's':
				$tpl_directory = __DIR__.'/skeleton/create-plugin/simple';
				break;
			case 'c':
				$tpl_directory = __DIR__.'/skeleton/create-plugin/complex';
				break;
			case 'm':
				$tpl_directory = __DIR__.'/skeleton/create-plugin/modular';
				break;
			default:
				$tpl_directory = __DIR__.'/skeleton/create-plugin/simple';
				break;
		}

		$new_plugin_directory = $this->get_new_plugin_directory();
		$templatefile = $tpl_directory.'/templatefile';
		if(!file_exists($templatefile)){
			\WP_CLI::error($templatefile.' does not exists');
		}

		try{
			$this->parse_templatefile($templatefile,$new_plugin_directory);
		}catch(\Exception $e){
			\WP_CLI::error($e->getMessage());
		}

		\WP_CLI::success('Created a new plugin called: '.$this->new_plugin_data['name'].' in: '.$new_plugin_directory);
	}

	/**
	 * @param $data_name
	 * @param $msg
	 * @param bool $allow_empty
	 * @param bool $override_with_default
	 * @param string $default
	 */
	private function obtain_new_plugin_data($data_name,$msg,$allow_empty = false,$override_with_default = false, $default = ''){
		$this->new_plugin_data[$data_name] = $this->get_cli_value($msg);
		if(!$allow_empty && empty($this->new_plugin_data[$data_name])){
			\WP_CLI::error('This value cannot be empty');
		}elseif($allow_empty && empty($this->new_plugin_data[$data_name]) && $override_with_default){
			$this->new_plugin_data[$data_name] = $default;
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
	 * @param array $args
	 *
	 * @throws \Exception
	 */
	public function parse_templatefile($templatefile,$output_directory,$args = []){
		$this->new_plugin_data = wp_parse_args($args,$this->new_plugin_data);

		$file = new \SplFileObject($templatefile);
		while(!$file->eof()){
			$line = $file->fgets();
			preg_match("|^([a-zA-Z-.]+):([\/a-zA-Z-.{}]+)$|",$line,$matches);
			if(isset($matches) && isset($matches[2])){
				$src = [
					'path' => dirname($templatefile).'/'.$matches[1],
					'filename' => $matches[1]
				];
				$dst = [
					'input_path' => $matches[2],
					'relative_path' => dirname($matches[2]),
					'filename' => basename($matches[2])
				];
				$dst['filename'] = str_replace("{{slug}}",$this->new_plugin_data['slug'],$dst['filename']);
				$dst['path'] = $output_directory.trailingslashit($dst['relative_path']).$dst['filename'];
				//File creation:
				if(file_exists($dst['path'])){
					throw new \Exception('File '.$dst['path'].' already exists');
				}
				wp_mkdir_p($output_directory);
				wp_mkdir_p(dirname($dst['path']));
				$content = file_get_contents($src['path']);
				$content = $this->parse_skeleton_file_content($content); //Parsing tags
				file_put_contents($dst['path'],$content);
			}
		}
	}

	/**
	 * Replaces tags in a skeleton file content
	 *
	 * @param $file
	 *
	 * @return string
	 */
	private function parse_skeleton_file_content($content){
		$parsed = $content;
		$parsed = preg_replace("|({{ ?slug ?}})|",$this->new_plugin_data['slug'],$parsed);
		$parsed = preg_replace("|({{ ?name ?}})|",$this->new_plugin_data['name'],$parsed);
		$parsed = preg_replace("|{{ ?namespace ?}}|",$this->new_plugin_data['namespace'],$parsed);
		$parsed = preg_replace("|{{ ?url ?}}|",$this->new_plugin_data['url'],$parsed);
		$parsed = preg_replace("|{{ ?description ?}}|",$this->new_plugin_data['description'],$parsed);
		return $parsed;
	}
}