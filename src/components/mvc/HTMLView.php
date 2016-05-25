<?php
/**
 * WBF Simple View Class.
 * @since 0.13.12
 *
 * Usage:
 *
 * THEMES:
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
 * PLUGINS:
 *
 * You can provide a \WBF\includes\pluginsframework\Plugin instance as second argument in View constructor to address a template file relative to the plugin path
 *
 * Enjoy!
 */

namespace WBF\includes\mvc;

class HTMLView extends View implements View_Interface{
	/**
	 * Print out the view. The provided vars will be extracted with extract() but they will be also available through $GLOBALS['template_vars'].
	 * @param array $vars associative array of variable that will be usable in the template file.
	 */
	public function display($vars = []){
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

	/**
	 * Get the view output. The provided vars will be extracted with extract() but they will be also available through $GLOBALS['template_vars'].
	 * @param array $vars
	 */
	public function get($vars = []){
		ob_start();
		$this->display($vars);
		$output = trim(preg_replace( "|[\r\n\t]|", "", ob_get_clean()));
		return $output;
	}
}