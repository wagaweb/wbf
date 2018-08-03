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
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function exec_wp_cli_command($command,$executable='wp',$exec_function = self::EXEC_FUNCTION_EXEC){
		$cmd = 'cd '.ABSPATH;
		$cmd .= ' &&';
		$cmd .= ' '.$executable;
		$cmd .= ' '.$command;
		return self::perform_command($cmd,$exec_function);
	}

	/**
	 * Executes a command from the theme directory
	 *
	 * @param string $command
	 * @param string $relative_path (additional path to append to the theme directory)
	 * @param string $exec_function (default: exec)
	 * @param bool $no_output whether append ' > /dev/null 2>/dev/null &' to the command or not
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function exec_cli_command_from_theme_directory($command,$relative_path='',$exec_function=self::EXEC_FUNCTION_EXEC,$no_output=false){
		return self::exec_cli_command($command,get_stylesheet_directory().$relative_path,$exec_function,$no_output);
	}

	/**
	 * Executes a command from the specified $wd
	 *
	 * @param string $command
	 * @param string $wd the working directory (default to ABSPATH)
	 * @param string $exec_function (default: exec)
	 * @param bool $no_output whether append ' > /dev/null 2>/dev/null &' to the command or not
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function exec_cli_command($command,$wd=null,$exec_function=self::EXEC_FUNCTION_EXEC,$no_output=false){
		if(!$wd){
			$wd = ABSPATH;
		}
		$cmd = 'cd '.$wd;
		$cmd .= ' &&';
		$cmd .= ' '.$command;
		if($no_output){
			$cmd .= ' > /dev/null 2>/dev/null &';
		}
		return self::perform_command($cmd,$exec_function);
	}

	/**
	 * @see https://stackoverflow.com/questions/732832/php-exec-vs-system-vs-passthru
	 *
	 * @param string $cmd
	 * @param string $exec_function
	 *
	 * @return array
	 * @throws \Exception
	 */
	private static function perform_command($cmd,$exec_function){
		switch($exec_function){
			case self::EXEC_FUNCTION_SYSTEM:
				$status = '';
				$r = system($cmd,$status);
				return [
					'output' => $r,
					'status' => $status
				];
				break;
			case self::EXEC_FUNCTION_EXEC:
				$output = '';
				$status = '';
				$r = exec($cmd,$output,$return_var);
				return [
					'output' => $output,
					'status' => $status
				];
				break;
			case self::EXEC_FUNCTION_PASSTHRU:
				$status = '';
				passthru($cmd,$status);
				return [
					'status' => $status
				];
				break;
			default:
				throw new \Exception('Invalid function provided');
		}
	}
}