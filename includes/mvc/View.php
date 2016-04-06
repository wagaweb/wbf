<?php

namespace wbf\includes\mvc;

use WBF\includes\pluginsframework\Plugin;
use WBF\includes\Utilities;

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
	 * @param string|\WBF\includes\pluginsframework\Plugin  $plugin a plugin directory name or an instance of \WBF\includes\pluginsframework\Plugin
	 *
	 * @throws \Exception
	 */
	public function __construct($relative_file_path,$plugin = null){
		if(!is_string($relative_file_path) || empty($relative_file_path)){
			throw new \Exception("Cannot create View, invalid file path");
		}
		if(isset($plugin)){
			if($plugin instanceof Plugin){
				$abs_path = Utilities::maybe_strip_trailing_slash($plugin->get_dir())."/".$relative_file_path;
			}elseif(is_string($plugin)){
				$abs_path = Utilities::maybe_strip_trailing_slash(WP_CONTENT_DIR)."/plugins/".$plugin."/".$relative_file_path;
			}else{
				throw new \Exception("Invalid plugin parameter for View rendering");
			}
		}else{
			$template_dir = Utilities::maybe_strip_trailing_slash(get_stylesheet_directory());
			if(file_exists($template_dir."/".$relative_file_path)){
				$abs_path = $template_dir."/".$relative_file_path;
			}else{
				$template_dir = Utilities::maybe_strip_trailing_slash(get_template_directory());
				if(file_exists($template_dir."/".$relative_file_path)){
					$abs_path = $template_dir."/".$relative_file_path;
				}else{
					throw new \Exception("Invalid relative file path provided");
				}
			}
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
	public function clean(){
		$this->args['page_title'] = "";
		$this->args['wrapper_class'] = "";
		$this->args['wrapper_el'] = "";
		$this->args['title_wrapper'] = "%s";
		return $this;
	}
}