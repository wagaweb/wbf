<?php

namespace WBF\modules\commands\cli;

use WBF\components\utils\Paths;
use WBF\modules\commands\BaseCommand;
use WBF\modules\options\Admin;

class ThemeOptions extends BaseCommand {

	public function configure() {
		$this->set_name('wbf:theme-options');
		$this->set_shortdesc("Manage theme options.");
		$this->add_arg('operation',BaseCommand::ARG_TYPE_POSITIONAL);
	}

	/**
	 * @param $args
	 * @param $assoc_args
	 */
	public function __invoke($args,$assoc_args) {
		$valid_operations = ['backup','restore'];

		if(!in_array($args[0],$valid_operations)){
			\WP_CLI::error('Invalid operation. Valid operations are: '.implode(', ',$valid_operations));
		}

		switch($args[0]){
			case 'backup':
				$this->backup_theme_options();
				break;
			case 'restore':
				$this->restore_theme_options();
				break;
		}
	}

	/**
	 * Backup theme options subcommand
	 */
	private function backup_theme_options(){
		$adm = new Admin();
		try{
			$file = $adm->backup_options_to_file();
			$file = Paths::url_to_path($file);
			\WP_CLI::success('Theme Options backup file successfully created ad: '.$file);
		}catch(\Exception $e){
			\WP_Cli::error($e->getMessage());
		}
	}

	/**
	 * Restore theme options subcommand
	 */
	private function restore_theme_options(){
		$adm = new Admin();
		$files = $adm->get_backupFiles();
		if(empty($files)){
			\WP_CLI::error('No backup file found');
		}
		\WP_CLI::log('Available backup files:');
		for($i = 0; $i<count($files); $i++){
			$j = $i+1;
			\WP_CLI::log($j.'] '.$files[$i]['name']);
		}
		$selected_files_index = $this->get_cli_value('Which one do you wish to restore?');
		$selected_files_index = (int) $selected_files_index;
		if($selected_files_index > count($files)){
			\WP_CLI::error('Wrong index provided');
		}
		$selected_files_index--;
		try{
			\WP_CLI::log('Restoring: '.$files[$selected_files_index]['path']);
			$adm->restore_options_from_file($files[$selected_files_index]['path']);
			\WP_CLI::success('Successfully restored: '.$files[$selected_files_index]['name']);
		}catch (\Exception $e){
			\WP_CLI::error($e->getMessage());
		}
	}
}