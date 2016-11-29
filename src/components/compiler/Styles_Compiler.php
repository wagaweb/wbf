<?php
/*
 * This file is part of WBF Framework: https://github.com/wagaweb/wbf
 *
 * @author WAGA Team <dev@waga.it>
 */

namespace WBF\components\compiler;
use \Exception;
use WBF\components\utils\Utilities;
use \WP_Error;

class Styles_Compiler{

	/**
	 * @var Base_Compiler
	 */
	var $base_compiler;

	/**
	 * @var array
	 */
	var $compiling_options;

	/**
	 * Styles_Compiler constructor.
	 *
	 * @param Base_Compiler $base_compiler
	 * @param array $options
	 *
	 * @throws Exception
	 */
	function __construct(Base_Compiler $base_compiler, $options = []){
		if(!is_array($options)){
			throw new \Exception("Invalid arguments passed to Styles_Compiler");
		}

		$this->compiling_options = $options;

		$this->base_compiler = $base_compiler;

		$this->maybe_release_lock();

		return $this;
	}

	/**
	 * Performs action based on $_GET parameters
	 */
	function listen_requests(){
		if (isset($_GET['compile']) && $_GET['compile'] == true) {
			if (current_user_can('manage_options')) {
				$this->compile();
			}
		}

		if (isset($_GET['clear_cache'])) {
			if (current_user_can('manage_options')) {
				do_action("wbf/compiler/cache/pre_clean");
				$this->clear_cache();
				do_action("wbf/compiler/cache/post_clean");
				$this->compile();
			}
		}
	}

	/**
	 * @param bool $setname
	 */
	function compile($setname = false){
		/** This filter is documented in wp-admin/admin.php */
		@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', WP_MAX_MEMORY_LIMIT ) );
		try{
			if(!$this->can_compile()) throw new CompilerBusyException();
			$args = $setname && !empty($setname) ? $this->get_compile_sets()[$setname] : false; //The set args

			$this->lock(); //lock the compiler
			$this->update_last_compile_attempt($setname); //keep note of the current time

			$return_css_flag = true;
			do_action("wbf/compiler/pre_compile");
			if($setname && is_string($setname)){
				/*
				 * SPECIFIC SET COMPILING
				 */
				do_action("wbf/compiler/pre_compile/{$setname}",$args);
				if(isset($args['compile_pre_callback'])){
					call_user_func($args['compile_pre_callback'],$args);
				}
				$css = $this->base_compiler->compile($setname); //COMPILE with specified compiler!
				if(isset($args['output']) && !empty($args['output'])){
					$this->write_to_file($css,$args['output']);
					$return_css_flag = false;
				}
				do_action("wbf/compiler/post_compile/{$setname}",$args,$css);
				if(isset($args['compile_post_callback'])){
					call_user_func($args['compile_post_callback'],$css);
				}
			}else{
				/*
				 * GLOBAL COMPILING
				 */
				foreach($this->get_compile_sets() as $setname => $args){
					if($args['exclude_from_global_compile']) continue;
					do_action("wbf/compiler/pre_compile/{$setname}",$args);
					if(isset($args['compile_pre_callback'])){
						call_user_func($args['compile_pre_callback'],$args);
					}
					$css[$setname] = $this->base_compiler->compile($setname); //COMPILE with specified compiler!
					if(isset($args['output']) && !empty($args['output'])){
						$this->write_to_file($css[$setname],$args['output']);
						$return_css_flag = false;
					}
					do_action("wbf/compiler/post_compile/{$setname}",$args,$css[$setname]);
					if(isset($args['compile_post_callback'])){
						call_user_func($args['compile_post_callback'],$css[$setname]);
					}
				}
			}
			do_action("wbf/compiler/post_compile");

			$this->release_lock(); //release the compiler
			//Display end message:
			static $message_displayed = false;
			if ( current_user_can( 'manage_options' ) && !$message_displayed) {
				if(is_admin()){
					Utilities::admin_show_message(__( 'Theme style files compiled successfully!', 'wbf' ),"updated");
				}else{
					echo '<div class="alert alert-success"><p>'.__('Theme styles files compiled successfully!', 'wbf').'</p></div>';
				}
				$message_displayed = true;
			}
			if($return_css_flag && isset($css)){
				return $css;
			}else{
				return true;
			}
		}catch(Exception $e){
			if(!$e instanceof CompilerBusyException) $this->release_lock(); //release the compiler
			$wpe = new WP_Error( 'compile-failed', $e->getMessage() );
			if ( current_user_can( 'manage_options' ) ) {
				if(is_admin()){
					Utilities::admin_show_message( sprintf(__( 'Theme style files not compiled! Error: %s', 'wbf' ),$e->getMessage()),"error");
				}else{
					echo '<div class="alert alert-warning"><p>'.$wpe->get_error_message().'</p></div>';
				}
			}
		}
	}

	/**
	 * @param $css
	 * @param $path
	 *
	 * @throws Exception
	 */
	function write_to_file($css,$path){
		$pathinfo = pathinfo($path);

		if(!is_dir($pathinfo['dirname'])){
			if(!mkdir($pathinfo['dirname'])){
				throw new Exception("Cannot create ({$pathinfo['dirname']})");
			}
		}

		if(!is_file($path)){
			fclose(fopen($path,"w"));
		}

		if(!is_writable($path)){
			if(!chmod($path,0777)){
				throw new Exception("Output dir ({$path}) is not writeable");
			}
		}

		//$wp_filesystem->put_contents( $args['output'], $css, FS_CHMOD_FILE );
		file_put_contents($path, $css);
	}

	/**
	 * Clear compiler cache
	 */
	function clear_cache(){
		$this->release_lock(); //release the compiler

		foreach($this->base_compiler->compile_sets as $name => $args){
			$cachedir = $args['cache'];
			if(is_dir($cachedir)){
				$files = glob($cachedir."/*");
				foreach($files as $file){ // iterate files
					if(is_file($file))
						unlink($file); // delete file
				}
				if(is_admin()){
					add_action( 'admin_notices', '\WBF\includes\compiler\cache_cleared_admin_notice');
					Utilities::admin_show_message(__( 'Theme cache cleared successfully!', 'wbf' ),"updated");
				}else{
					echo '<div class="alert alert-success"><p>'.__('Theme cache cleared successfully!', 'wbf').'</p></div>';
				}
			}
		}
	}

	/**
	 * Checks lock status to determine whether the compiler can compile or not
	 *
	 * @return bool
	 */
	function can_compile(){
		$busyflag = $this->get_lock_status();
		if($busyflag && $busyflag != 0){
			return false;
		}

		return true;
	}

	/**
	 * Releases the compiler lock if is passed too much time since last compilation attempt
	 * @param int $timelimit (in minutes)
	 */
	function maybe_release_lock($timelimit = 2){
		if(!$this->can_compile()){
			$last_attempt = $this->get_last_compile_attempt();
			if(!$last_attempt){
				$this->release_lock(); //release the compiler just to be sure
			}else{
				$current_time = time();
				$time_diff = ($current_time - $last_attempt)/60;
				if($time_diff > $timelimit){ //2 minutes
					$this->release_lock(); //release the compiler
				}
			}
		}
	}

	function lock(){
		update_option('waboot_compiling_flag',1) or add_option('waboot_compiling_flag',1,'',true);
	}

	function release_lock(){
		update_option('waboot_compiling_flag',0);
	}

	function get_lock_status(){
		return get_option("waboot_compiling_flag",0);
	}

	function update_last_compile_attempt($setname = false){
		$last_attempts = $this->get_last_compile_attempt($setname);
		if(!is_array($last_attempts)){
			$last_attempts = array();
		}
		$time = time();
		if($setname){
			$last_attempts[$setname] = $time;
		}else{
			foreach($this->get_compile_sets() as $name => $args){
				if($args['exclude_from_global_compile']) continue;
				$last_attempts[$name] = $time;
			}
		}
		$last_attempts['_global'] = $time;
		update_option('waboot_compiling_last_attempt',$last_attempts) or add_option('waboot_compiling_last_attempt',$last_attempts,'',true);
	}

	function get_last_compile_attempt($setname = false){
		$last_attempts = get_option('waboot_compiling_last_attempt');
		if(is_array($last_attempts)){
			if(!$setname && isset($last_attempts['_global'])){
				return $last_attempts['_global'];
			}elseif(isset($last_attempts[$setname])){
				return $last_attempts[$setname];
			}
		}
		return false;
	}

	/**
	 * Get the compile sets from current compiler. Return empty array if fails.
	 * @return array
	 */
	function get_compile_sets(){
		if(isset($this->base_compiler)){
			if(isset($this->base_compiler->compile_sets)){
				return $this->base_compiler->compile_sets;
			}
		}

		return array();
	}

	/**
	 * Get the primary compile set
	 * @return bool
	 */
	function get_primary_set(){
		$sets = $this->get_compile_sets();
		foreach($sets as $k => $s){
			if(isset($s['primary']) && $s['primary']){
				return $s;
			}
		}
		return false;
	}
}

class CompilerBusyException extends Exception{
	public function __construct($message = null, $code = 0, Exception $previous = null) {
		if(!isset($message)){
			$message = __("The compiler is busy","wbf");
		}
		parent::__construct($message, $code, $previous);
	}
}