<?php
namespace FisPressShop\includes;

use WBF\includes\pluginsframework\Plugin;

class View {
	var $template;

	var $args;

	/**
	 * Initialize a new view
	 *
	 * @param string $relative_file_path a path relative to $module "views" dir or to the root "views" dir if $module is not provided
	 * @param string|null $plugin a plugin directory name or an instance of \WBF\includes\pluginsframework\Plugin
	 *
	 * @throws \Exception
	 */
	function __construct($relative_file_path,$plugin){
		if(!is_string($relative_file_path) || empty($relative_file_path)){
			throw new \Exception("Cannot create View, invalid file path");
		}
		if($plugin instanceof Plugin){
			$abs_path = rtrim($plugin->get_dir(),"/")."/".$relative_file_path;
		}elseif(is_string($plugin)){
			$abs_path = rtrim(WP_CONTENT_DIR,"/")."/".$plugin."/".$relative_file_path;
		}else{
			throw new \Exception("Invalid plugin parameter for View rendering");
		}
		if(!file_exists($abs_path)){
			throw new \Exception("File {$abs_path} does not exists");
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
	function clean(){
		$this->args['page_title'] = "";
		$this->args['wrapper_class'] = "";
		$this->args['wrapper_el'] = "";
		$this->args['title_wrapper'] = "%s";
		return $this;
	}

	/**
	 * Print out the view. The provided vars will be extracted with extract() but they will be also available through $GLOBALS['template_vars'].
	 * @param array $vars associative array of variable that will be usable in the template file.
	 */
	function display($vars = []){
		$vars = wp_parse_args($vars,$this->args);

		$GLOBALS['template_vars'] = $vars;
		extract($vars);

		if($vars['wrapper_el'] != ""){
			echo "<{$vars['wrapper_el']} class='".$vars['wrapper_class']."'>";
			printf($vars['title_wrapper'],$vars['page_title']);
			include $this->template['dirname']."/".$this->template['basename'];
			echo "</{$vars['wrapper_el']}><!-- .wrap -->";
		}else{
			include $this->template['dirname']."/".$this->template['basename'];
		}
	}
}