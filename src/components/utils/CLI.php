<?php

namespace WBF\components\utils;

class CLI{
	const EXEC_FUNCTION_SYSTEM = 'system';
	const EXEC_FUNCTION_EXEC = 'exec';
	const EXEC_FUNCTION_PASSTHRU = 'passthru';

	/**
	 * Executes a WP-Cli command
	 *
	 * @param string $command
	 * @param string $executable (default: wp)
	 * @param string $exec_function (default: exec)
	 * @param null &$output the variable in which put the command output (for exec and passthru)
	 * @param null &$return_var the variable in which put the command return status (for exec and passthru)
	 *
	 * @return mixed the $exec_function output
	 * @throws \Exception
	 */
	public static function exec_wp_cli_command($command,$executable='wp',$exec_function = self::EXEC_FUNCTION_EXEC,&$output = null,&$return_var = null){
		$cmd = 'cd '.ABSPATH;
		$cmd .= ' &&';
		$cmd .= ' '.$executable;
		$cmd .= ' '.$command;
		return self::perform_command($cmd,$exec_function,$output,$return_var);
	}

	/**
	 * Executes a script or a command within the theme directory.
	 *
	 * @param string $command
	 * @param string $relative_dir (default empty string)
	 * @param bool $null_output append ' > /dev/null 2>/dev/null &' to the command.
	 * @param string $exec_function (default: exec)
	 * @param null &$output the variable in which put the command output (for exec and passthru)
	 * @param null &$return_var the variable in which put the command return status (for exec and passthru)
	 *
	 * @return mixed the $exec_function output
	 * @throws \Exception
	 */
	public static function exec_cli_script($command,$relative_dir='',$null_output=false,$exec_function=self::EXEC_FUNCTION_EXEC,&$output= null,&$return_var= null){
		$cmd = 'cd '.get_stylesheet_directory().$relative_dir;
		$cmd .= ' &&';
		$cmd .= ' '.$command;
		if($null_output){
			$cmd .= ' > /dev/null 2>/dev/null &';
		}
		return self::perform_command($cmd,$exec_function,$output,$return_var);
	}

	/**
	 * @see https://stackoverflow.com/questions/732832/php-exec-vs-system-vs-passthru
	 *
	 * @param string $cmd
	 * @param string $exec_function
	 * @param null $output
	 * @param null $return_var
	 *
	 * @return bool|string
	 * @throws \Exception
	 */
	private static function perform_command($cmd,$exec_function,&$output=null,&$return_var=null){
		if(\function_exists($exec_function)){
			switch($exec_function){
				case self::EXEC_FUNCTION_SYSTEM:
					return system($cmd,$exec_arg1);
					break;
				case self::EXEC_FUNCTION_EXEC:
					return exec($cmd,$output,$return_var);
					break;
				case self::EXEC_FUNCTION_PASSTHRU:
					passthru($cmd,$return_var);
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