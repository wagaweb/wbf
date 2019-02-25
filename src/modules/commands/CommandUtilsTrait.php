<?php

namespace WBF\modules\commands;

trait CommandUtilsTrait
{
	/**
	 * @param $m
	 */
	public function log($m)
	{
		if($this->isWPCli()){
			\WP_CLI::log($m);
		}
	}

	/**
	 * @param $m
	 */
	public function debug($m)
	{
		if($this->isWPCli()){
			\WP_CLI::debug($m);
		}
	}

	/**
	 * @param $m
	 */
	public function warning($m)
	{
		if($this->isWPCli()){
			\WP_CLI::warning($m);
		}
	}

	/**
	 * @param $m
	 */
	public function error($m)
	{
		if($this->isWPCli()){
			\WP_CLI::error($m);
		}
	}

	/**
	 * @param $m
	 */
	public function success($m)
	{
		if($this->isWPCli()){
			\WP_CLI::success($m);
		}
	}

	/**
	 * @return bool
	 */
	public function isWPCli()
	{
		return defined('WP_CLI') && WP_CLI;
	}
}