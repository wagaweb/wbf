<?php
/**
 * WBF Simple View Class.
 * @since 0.13.12
 *
 * Usage:
 *
 * - Create the template file: tpl.php
 * Eg:
 * <h1><?php echo $var1_name; ?><h2>
 * <p><?php echo $var2_name; ?></p>
 *
 * - Create a new instance: $v = new View("path/to/tpl.php")
 * - Display the view:
 *
 * $v->display([
 *  'var1_name' => 'var1_value'
 *  'var2_name' => 'var2_value'
 * ]);
 *
 * There are some predefined values:
 *
 * page_title = "Page Title"
 * wrapper_class = "wrap"
 * wrapper_el = "div"
 * title_wrapper "<h1>%s</h1>"
 *
 * These values will display a page like this:
 *
 * <div class="wrap">
 *  <h1>Page Title</h1>
 *  {your-template-file}
 * </div>
 *
 * You can clean these values before displaying by:
 *
 * $v->clean()->display([
 *  'var1_name' => 'var1_value'
 *  'var2_name' => 'var2_value'
 * ]);
 *
 * Enjoy!
 */

namespace WBF\includes\mvc;

use WBF\includes\pluginsframework\Plugin;
use WBF\includes\Utilities;

class View {
	var $template;

	var $args;

	/**
	 * Initialize a new view. If the $plugin argument is not provided, the template file will be searched into stylesheet and template directories.
	 *
	 * @param string $relative_file_path a path relative to $module "views" dir or to the root "views" dir if $module is not provided
	 * @param string|\WBF\includes\pluginsframework\Plugin  $plugin a plugin directory name or an instance of \WBF\includes\pluginsframework\Plugin
	 *
	 * @throws \Exception
	 */
	function __construct($relative_file_path,$plugin = null){
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