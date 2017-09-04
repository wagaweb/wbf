<?php

namespace WBF\modules\commands;

add_action('init', function(){
	if( !defined('WP_CLI') || !WP_CLI ){
		return;
	}

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
			if(preg_match('|'.__DIR__.'|',$file)){
				$class_name = '\WBF\modules\commands\cli\\'.$class_name;
			}
			if(class_exists($class_name)){
				$command_instance = new $class_name();
				if($command_instance instanceof BaseCommand){
					$registered_commands[] = [
						'type' => 'custom-class',
						'class_name' => $class_name,
						'runner' => $command_instance
					];
				}elseif(class_exists('\WP_CLI_Command') && $command_instance instanceof \WP_CLI_Command){
					if(preg_match('|'.__DIR__.'|',$file)){
						$name = strtolower(substr($class_name, strrpos($class_name, '\\') + 1)); //Strip namespace
					}else{
						$name = $class_name;
					}
					$registered_commands[] = [
						'type' => 'vanilla-class',
						'class_name' => $class_name,
						'runner' => $class_name,
						'name' => $name
					];
				}
			}
		}
	}

	//Getting all other command types
	$registered_commands = apply_filters('wbf/commands/registered',$registered_commands);

	$registered_commands[] = [
		'type' => 'callable',
		'name' => 'wbf:test-callable',
		'runner' => function(){
			\WP_CLI::success('Command ready');
		}
	];

	foreach ($registered_commands as $command_entry){
		$command_entry = wp_parse_args($command_entry,[
			'type' => null,
			'class_name' => null,
			'name' => null,
			'runner' => null,
			'args' => []
		]);
		switch($command_entry['type']){
			case 'custom-class':
				$command_instance = $command_entry['runner'];
				if($command_instance instanceof BaseCommand && method_exists($command_instance,'configure') && method_exists($command_instance,'register')){
					$command_instance->configure();
					$command_instance->register();
				}
				break;
			case 'vanilla-class':
				if(class_exists('\WP_CLI_Command')){
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