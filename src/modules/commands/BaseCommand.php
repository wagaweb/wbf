<?php

namespace WBF\modules\commands;

use function GuzzleHttp\Psr7\str;

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

	const ARG_TYPE_POSITIONAL = 'positional';
	const ARG_TYPE_ASSOC = 'assoc';
	const ARG_TYPE_GENERIC = 'generic';
	const ARG_TYPE_FLAG = 'flag';

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
	 *
	 * @return $this
	 */
	public function set_name( $name ) {
		$this->name = $name;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function get_shortdesc(){
		return isset($this->args['shortdesc']) ? $this->args['shortdesc'] : null;
	}

	/**
	 * @param $content
	 *
	 * @return $this
	 */
	public function set_shortdesc($content){
		$this->args['shortdesc'] = $content;
		return $this;
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
	 *
	 * @return $this
	 */
	public function set_synopsis($content){
		$this->args['synopsis'] = $content;
		return $this;
	}

	/**
	 * Adds an argument
	 *
	 * @param string $name
	 * @param string $type (can be: positional, assoc, generic, flag)
	 * @param string $description
	 *
	 * @return $this
	 */
	public function add_arg($name,$type,$description = ''){
		$current_synopsis = $this->get_synopsis();
		if(!isset($current_synopsis) || !is_array($current_synopsis) ){
			$current_synopsis = [];
		}
		$current_synopsis[] = [
			'name' => $name,
			'type' => $type,
			'description' => $description
		];
		$this->set_synopsis($current_synopsis);
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function get_when(){
		return isset($this->args['when']) ? $this->args['when'] : null;
	}

	/**
	 * @param $content
	 *
	 * @return $this
	 */
	public function set_when($content){
		$this->args['when'] = $content;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function get_before_invoke(){
		return isset($this->args['before_invoke']) ? $this->args['before_invoke'] : null;
	}

	/**
	 * @param $content
	 *
	 * @return $this
	 */
	public function set_before_invoke($content){
		$this->args['before_invoke'] = $content;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function get_after_invoke(){
		return isset($this->args['after_invoke']) ? $this->args['after_invoke'] : null;
	}

	/**
	 * @param $content
	 *
	 * @return $this
	 */
	public function set_after_invoke($content){
		$this->args['after_invoke'] = $content;
		return $this;
	}

	/**
	 * @return array
	 */
	public function get_args() {
		return $this->args;
	}

	/**
	 * @param array $args
	 *
	 * @return $this
	 */
	public function set_args( $args ) {
		$this->args = $args;
		return $this;
	}

	/**
	 * Invoke the command
	 */
	public function __invoke( $args, $assoc_args ) {
		if(class_exists('WP_CLI')){
			\WP_CLI::success('Command ready');
		}
	}

	/**
	 * Get a value from STDIN
	 *
	 * @param $question
	 *
	 * @param bool $strtolower
	 *
	 * @return string
	 */
	public function get_cli_value($question, $strtolower = false){
		fwrite( STDOUT, $question  );
		$answer = trim( fgets( STDIN ) );
		if($strtolower){
			$answer = strtolower($answer);
		}
		return $answer;
	}
}