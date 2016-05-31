<?php

namespace WBF\components\mvc;

use WBF\components\pluginsframework\Plugin;
use WBF\components\utils\Utilities;

abstract class View{
	/**
	 * @var string
	 */
	var $template;

	/**
	 * @var array
	 */
	var $args;

	/**
	 * Initialize a new view. If the $plugin argument is not provided, the template file will be searched into stylesheet and template directories.
	 *
	 * @param string $relative_file_path a path to the view file starting from the theme or plugin directory
	 * @param string|\WBF\components\pluginsframework\Plugin  $plugin a plugin directory name or an instance of \WBF\includes\pluginsframework\Plugin
	 *
	 * @throws \Exception
	 */
	public function __construct($relative_file_path,$plugin = null){
		if(!is_string($relative_file_path) || empty($relative_file_path)){
			throw new \Exception("Cannot create View, invalid file path");
		}
		if(isset($plugin) && !$plugin instanceof Plugin && !is_string($plugin)){
			throw new \Exception("Invalid plugin parameter for View rendering");
		}

		$search_paths = self::get_search_paths($relative_file_path,$plugin);

		//Searching for template
		foreach($search_paths as $path){
			if(file_exists($path)){
				$abs_path = $path;
			}
		}

		if(!isset($abs_path) || !file_exists($abs_path)){
			throw new \Exception("File {$relative_file_path} does not exists in any of these locations: ".implode(",\n",$search_paths));
		}

		$this->template = pathinfo($abs_path);
		$this->args = [
			'page_title' => "Page Title",
			'wrapper_class' => "wrap",
			'wrapper_el' => "div",
			'title_wrapper' => "<h1>%s</h1>"
		];
	}

	/**
	 * Clean the predefined args, providing a clean template.
	 * @return $this
	 */
	public function clean(){
		$this->args['page_title'] = "";
		$this->args['wrapper_class'] = "";
		$this->args['wrapper_el'] = "";
		$this->args['title_wrapper'] = "%s";
		return $this;
	}

	/**
	 * Get the search paths given the $relative_file_path.
	 *
	 * The View will look for a valid file in these locations:
	 *
	 * IF PLUGIN (when $relative_file_path == "src/view/foo.php"):
	 * - <parent_theme>/<relative_file_path WITHOUT /src/ if present>/<plugin_dir_name>-<file_name> (eg: wp-content/themes/twentyfifteen/views/wb-sample-foo.php)
	 * - <parent_theme/child_theme>/<plugin_dir_name>/<relative_file_path WITHOUT /src/ if present>/<file_name> (eg: wp-content/themes/twentyfifteen/wb-sample/views/foo.php)
	 * - <plugin_path>
	 *
	 * IF THEME:
	 * <parent_theme/child_theme>/<relative_file_path>
	 *
	 * @param $relative_file_path
	 * @param null $plugin
	 *
	 * @return array
	 * @throws \Exception
	 */
	static function get_search_paths($relative_file_path,$plugin = null){
		if(isset($plugin)){
			if($plugin instanceof Plugin){
				$plugin_abspath = Utilities::maybe_strip_trailing_slash($plugin->get_src_dir())."/".$relative_file_path;
				$plugin_dirname = $plugin->get_relative_dir();
			}elseif(is_string($plugin)){
				$plugin_abspath = Utilities::maybe_strip_trailing_slash(WP_CONTENT_DIR)."/plugins/".$plugin."/".$relative_file_path;
				$plugin_dirname = $plugin;
			}else{
				throw new \Exception("Plugin parameter is neither a Plugin or a string");
			}
			$search_paths = [];
			$relative_file_path = preg_replace("/^\/?src\//","",$relative_file_path); //Strip src/
			//Theme and parent
			foreach([Utilities::maybe_strip_trailing_slash(get_stylesheet_directory()),Utilities::maybe_strip_trailing_slash(get_template_directory())] as $template_dir){
				$search_paths[] = $template_dir."/".dirname($relative_file_path)."/".$plugin_dirname."-".basename($relative_file_path);
				$search_paths[] = $template_dir."/".$plugin_dirname."/".basename($relative_file_path);
			}
			//Plugin
			$search_paths[] = $plugin_abspath;
		}else{
			$search_paths = [];
			foreach([Utilities::maybe_strip_trailing_slash(get_stylesheet_directory()),Utilities::maybe_strip_trailing_slash(get_template_directory())] as $template_dir){
				$search_paths[] = $template_dir."/".$relative_file_path;
			}
		}

		$search_paths = array_unique($search_paths); //Clean up

		return $search_paths;
	}
}