<?php

namespace WBF\components\pluginsframework;

class BaseModule{
	/**
	 * @var BasePlugin
	 */
	protected $plugin;
	/**
	 * @var string
	 */
	protected $name;
	/**
	 * @var string
	 */
	protected $uri;
	/**
	 * @var string
	 */
	protected $dir;
	/**
	 * @var mixed
	 */
	protected $admin;
	/**
	 * @var mixed
	 */
	protected $frontend;

	public function __construct(BasePlugin &$plugin, $name) {
		$this->plugin = $plugin;
		$this->dir = $this->plugin->get_dir()."src/modules".$this->name;
		$this->uri = $this->plugin->get_uri()."src/modules".$this->name;

		//Set paths for Admin and Frontend
		if(is_file($this->dir."/Admin.php")){
			$adminClassName = __NAMESPACE__."\\Admin";
			$this->admin = new $adminClassName($this);
		}
		if(is_file($this->dir."/Frontend.php")){
			$publicClassName = __NAMESPACE__."\\Frontend";
			$this->frontend = new $publicClassName($this);
		}

		//Setup commands commands:
		if ( defined('WP_CLI') && WP_CLI ) {
			if(is_dir($this->dir."/commands")){
				$commands = glob($this->dir."/commands/*.php");
				if(is_array($commands) && !empty($commands)){
					foreach($commands as $c){
						require_once $c;
						$command_name = sanitize_title(basename(rtrim($c,".php")));
						$class_name = __NAMESPACE__."\\commands\\".basename(rtrim($c,".php"));
						\WP_CLI::add_command($command_name,$class_name);
					}
				}
			}
		}
	}

	/**
	 * @return mixed
	 */
	public function get_admin() {
		return $this->admin;
	}

	/**
	 * @return mixed
	 */
	public function get_frontend() {
		return $this->frontend;
	}

	/**
	 * @return BasePlugin
	 */
	public function get_plugin(){
		return $this->plugin;
	}

	/**
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function get_uri() {
		return $this->uri;
	}

	/**
	 * @return string
	 */
	public function get_dir() {
		return $this->dir;
	}

	/**
	 * @return \WBF\components\pluginsframework\Loader
	 */
	public function get_loader(){
		return $this->plugin->get_loader();
	}
}