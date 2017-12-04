<?php

namespace WBF\components\utils;

class CLI{
	/**
	 * Executes a WP-Cli command
	 *
	 * @param string $command
	 * @param string $wpcli_executable (default: wp)
	 * @param string $exec_function (default: exec)
	 * @param null $exec_arg1
	 * @param null $exec_arg2
	 *
	 * @return mixed the $exec_function output
	 * @throws \Exception
	 */
	public static function exec_wp_cli_command($command,$wpcli_executable = "wp",$exec_function = "system",&$exec_arg1 = null,&$exec_arg2 = null){
		$cmd = 'cd '.ABSPATH;
		$cmd .= ' &&';
		$cmd .= ' '.$wpcli_executable;
		$cmd .= ' '.$command;

		if(\function_exists($exec_function)){
			switch($exec_function){
				case 'system':
					return system($cmd,$exec_arg1);
					break;
				case 'exec':
					return exec($cmd,$exec_arg1,$exec_arg2);
					break;
				case 'passthru':
					passthru($cmd,$exec_arg1);
					return true;
					break;
				default:
					return $exec_function($cmd);
			}
		}else{
			throw new \Exception('Invalid $exec_function provided');
		}
	}

	/**
	 * Executes a script or a command within the theme directory.
	 *
	 * @param string $command
	 * @param string $relative_dir (default empty string)
	 * @param bool $null_output append ' > /dev/null 2>/dev/null &' to the command.
	 * @param string $exec_function (default: exec)
	 * @param null $exec_arg1
	 * @param null $exec_arg2
	 *
	 * @return mixed the $exec_function output
	 * @throws \Exception
	 */
	public static function exec_cli_script($command,$relative_dir = "",$null_output = false,$exec_function = "system",&$exec_arg1 = null,&$exec_arg2 = null){
		$cmd = 'cd '.get_stylesheet_directory().$relative_dir;
		$cmd .= ' &&';
		$cmd .= ' '.$command;
		if($null_output){
			$cmd .= ' > /dev/null 2>/dev/null &';
		}

		if(\function_exists($exec_function)){
			switch($exec_function){
				case 'system':
					return system($cmd,$exec_arg1);
					break;
				case 'exec':
					return exec($cmd,$exec_arg1,$exec_arg2);
					break;
				case 'passthru':
					passthru($cmd,$exec_arg1);
					return true;
					break;
				default:
					return $exec_function($cmd);
			}
		}else{
			throw new \Exception('Invalid $exec_function provided');
		}
	}
}