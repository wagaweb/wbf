<?php

namespace WBF\modules\commands;

abstract class BaseCommand{
	//See class-wp-cli.php add_command() method for the implemented setter \ getter methods

	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var array
	 */
	private $args = [];

	/**
	 * BaseCommand constructor.
	 */
	public function __construct() {
		$this->configure();
	}

	/**
	 * Configure the command
	 */
	public function configure(){}

	/**
	 * Register the command to WordPress
	 */
	public function register(){
		if(defined('WP_CLI') && WP_CLI){
			if(isset($this->name)){
				\WP_CLI::add_command($this->name,$this,$this->args);
			}
		}
	}

	/**
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function set_name( $name ) {
		$this->name = $name;
	}

	/**
	 * @return string|null
	 */
	public function get_shortdesc(){
		return isset($this->args['shortdesc']) ? $this->args['shortdesc'] : null;
	}

	/**
	 * @param $content
	 */
	public function set_shortdesc($content){
		$this->args['shortdesc'] = $content;
	}

	/**
	 * @return string|null
	 */
	public function get_synopsis(){
		return isset($this->args['synopsis']) ? $this->args['synopsis'] : null;
	}

	/**
	 * Set the command synopsis. WP_CLI uses this for validation if array is provided.
	 * See: SynopsisParser.php and class-wp-cli.php in wp-cli.phar for info about the array format.
	 *
	 * @example:
	 *
	 * [
	 *      [
	 *          'type' => 'positional' //can be: positional, assoc, generic, flag
	 *          'name' => 'foobar',
	 *          'description' => 'Foobar description'
	 *      ],
	 *      [
	 *          ...
	 *      ]
	 *      ...
	 * ]
	 *
	 * @param array|string $content
	 */
	public function set_synopsis($content){
		$this->args['synopsis'] = $content;
	}

	/**
	 * @return string|null
	 */
	public function get_when(){
		return isset($this->args['when']) ? $this->args['when'] : null;
	}

	/**
	 * @param $content
	 */
	public function set_when($content){
		$this->args['when'] = $content;
	}

	/**
	 * @return string|null
	 */
	public function get_before_invoke(){
		return isset($this->args['before_invoke']) ? $this->args['before_invoke'] : null;
	}

	/**
	 * @param $content
	 */
	public function set_before_invoke($content){
		$this->args['before_invoke'] = $content;
	}

	/**
	 * @return string|null
	 */
	public function get_after_invoke(){
		return isset($this->args['after_invoke']) ? $this->args['after_invoke'] : null;
	}

	/**
	 * @param $content
	 */
	public function set_after_invoke($content){
		$this->args['after_invoke'] = $content;
	}

	/**
	 * @return array
	 */
	public function get_args() {
		return $this->args;
	}

	/**
	 * @param array $args
	 */
	public function set_args( $args ) {
		$this->args = $args;
	}

	/**
	 * Invoke the command
	 */
	public function __invoke( $args, $assoc_args ) {
		if(class_exists('WP_CLI')){
			\WP_CLI::success('Command ready');
		}
	}
}