<?php

namespace WBF\modules\commands;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

trait MonoLoggerTrait{
	/**
	 * @var Logger
	 */
	private $fileLogger;

	/**
	 * @param string $name the name to pass to \Monolog\Logger constructor
	 * @param string $filename the full path to log file
	 * @param int $level default to \Monolog\Logger::DEBUG
	 *
	 * @throws \Exception
	 */
	public function initFileLogger($name,$filename,$level = Logger::DEBUG)
	{
		$logger = new Logger($name);
		$basedir = dirname($filename);
		if(!\is_dir($basedir) && !wp_mkdir_p($basedir)){
			throw new \Exception('Unable to create directory: '.$basedir);
		}
		try{
			$handler = new StreamHandler($filename,$level);
			$logger->pushHandler($handler);
			$this->fileLogger = $logger;
		}catch (\Exception $e){
			throw new \Exception($e->getMessage());
		}
	}

	/**
	 * @return Logger
	 */
	public function getFileLogger()
	{
		return $this->fileLogger;
	}

	/**
	 * @return bool
	 */
	public function fileLoggerInitialized()
	{
		return $this->getFileLogger() !== null;
	}
}