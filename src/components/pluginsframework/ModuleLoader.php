<?php

namespace WBF\components\pluginsframework;

class ModuleLoader extends Loader{
	/**
	 * @var array
	 */
	protected $modules;
	/**
	 * @var BasePlugin
	 */
	protected $plugin;
	/**
	 * @var string
	 */
	protected $module_base_namespace;

	public function __construct( &$caller = null, $module_base_namespace ) {
		parent::__construct( $caller );
		$this->plugin = $caller;
		$this->module_base_namespace = $module_base_namespace;
	}

	/**
	 * Register a new module
	 *
	 * @param $slug
	 *
	 * @throws \Exception
	 */
	public function register_module($slug){
		$module_dir = $this->plugin->get_dir()."src/modules/".$slug;
		if(is_dir($module_dir) && file_exists($module_dir."/Module.php")){
			$this->modules[$slug] = [
				'slug' => $slug,
				'className' => "\\".$this->module_base_namespace."\\modules\\{$slug}\\Module",
				'dir' => $module_dir,
				'bootstrap' => $module_dir."/Module.php"
			];
		}else{
			throw new \Exception("Module {$module_dir} not found");
		}
	}

	/**
	 * Loads all register modules
	 */
	public function load_modules(){
		if(!isset($this->modules) || empty($this->modules)) return;

		foreach($this->modules as $k => $m){
			$module = new $m['className']($this->plugin,$m['slug']);
			$this->modules[$k] = $module;
			$module->run();
		}
	}

	/**
	 * Calls run() on every registered module and fires the hooks registration.
	 */
	public function run() {
		$this->load_modules();
		parent::run();
	}
}