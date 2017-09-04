<?php

namespace WBF\modules\commands;

add_action('init', function(){
	/*if( !defined('WP_CLI') || !WP_CLI ){
		return;
	}*/

	$registered_commands = [];
	$commands_directories = [
		__DIR__.'/cli',
		get_stylesheet_directory().'/inc/cli',
		get_template_directory().'/inc/cli'
	];

	//Getting class commands
	$commands_directories = array_unique($commands_directories);
	$commands_directories = apply_filters('wbf/commands/directories',$commands_directories);
	foreach ($commands_directories as $directory){
		$files = glob($directory."/*.php");
		foreach ($files as $file){
			require_once $file;
			$class_name = rtrim(basename($file),'.php');
			if(class_exists($class_name)){
				$command_instance = new $class_name();
				if($command_instance instanceof BaseCommand){
					$registered_commands[] = [
						'type' => 'class',
						'class_name' => $class_name,
						'runner' => $command_instance
					];
				}elseif(class_exists('\WP_CLI_Command') && $command_instance instanceof \WP_CLI_Command){
					$registered_commands[] = [
						'type' => 'class',
						'class_name' => $class_name,
						'runner' => $class_name,
						'name' => $class_name
					];
				}
			}
		}
	}

	//Getting all other command types
	$registered_commands = apply_filters('wbf/commands/registered',$registered_commands);
	foreach ($registered_commands as $command_entry){
		$command_entry = wp_parse_args($command_entry,[
			'type' => null,
			'class_name' => null,
			'name' => null,
			'runner' => null,
			'args' => []
		]);
		switch($command_entry['type']){
			case 'class':
				$command_instance = $command_entry['runner'];
				if($command_instance instanceof BaseCommand && method_exists($command_instance,'configure') && method_exists($command_instance,'register')){
					$command_instance->configure();
					$command_instance->register();
				}elseif(class_exists('\WP_CLI_Command') && $command_instance instanceof \WP_CLI_Command){
					if(class_exists('\WP_CLI'))
						\WP_CLI::add_command($command_entry['name'],$command_entry['runner'],$command_entry['args']);
				}
				break;
			case 'callable':
				if(isset($command_entry['name'],$command_entry['runner'])){
					if(class_exists('\WP_CLI'))
						\WP_CLI::add_command($command_entry['name'],$command_entry['runner'],$command_entry['args']);
				}
				break;
		}
	}

},14);