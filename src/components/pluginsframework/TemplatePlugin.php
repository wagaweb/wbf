<?php
/*
 * This file is part of WBF Framework: https://github.com/wagaweb/wbf
 *
 * @author WAGA Team <dev@waga.it>
 */

namespace WBF\components\pluginsframework;

use WBF\components\utils\Utilities;

class TemplatePlugin extends BasePlugin {
	/*
	 * @var array registered common templates
	 */
	protected $templates;
	/*
	 * @var array registered hierarchy templates
	 */
	protected $hierarchy_templates;
	/*
	 * @var array registered WC templates
	 */
	protected $wc_templates; //Embedded support for WooCommerce
	/**
	 * @var array paths of registered templates
	 */
	protected $templates_paths;
	/**
	 * @var array store current templates searched by WordPress (update during template_redirect)
	 */
	protected $current_searched_templates;

	public function __construct( $plugin_name, $dir, $version = "1.0.0" ) {
		parent::__construct( $plugin_name, $dir, $version );
		$this->templates       = array();
		$this->templates_paths = array();
		$this->hierarchy_templates   = array();
		$this->loader->add_filter( 'template_redirect', $this, "store_searched_hierarchy_templates" ); //Make use of $type_template_hierarchy filter introduced by WordPress 4.7
		$this->loader->add_filter( 'template_include', $this, "view_template" );
		$this->loader->add_filter( 'wbf/locate_template/search_paths', $this, 'add_template_base_path', 10, 2 );
		//Embedded support for WooCommerce
		$this->wc_templates = array();
		//if(function_exists('is_woocommerce')){
			//$this->loader->add_filter( 'woocommerce_locate_template',$this,"override_wc_locate_templates", 11, 3);
			$this->loader->add_filter( 'wc_get_template',$this,"override_wc_get_templates", 11, 5);
		//}
		//Embedded support for template wrappers
		$this->loader->add_action( 'init', $this, "maybe_attach_wrapper", 20 );
		//Just to be sure...
		$this->loader->add_action( 'init', $this, "flush_rewrites", 99 );
		//Automatically adds any template under /src/templates
		$this->loader->add_action( 'init', $this, "loads_hierarchy_templates", 999 );
	}

	/**
	 * Automatically adds any not already added template to hierarchy templates
	 *
	 * @hooked 'init', 999
	 */
	public function loads_hierarchy_templates(){
		$templates = glob($this->get_src_dir()."/templates/*.php");
		foreach ($templates as $tpl){
			$template_name = basename($tpl);
			if(!array_key_exists($template_name,$this->templates) && !in_array($template_name,$this->hierarchy_templates)){
				$this->add_hierarchy_template($template_name,$tpl);
			}
		}
	}

	/**
	 * Sometime the templates in plugins does not being used. Flush rewrites do the job.
	 * @hooked 'init', 99
	 */
	public function flush_rewrites(){
		\flush_rewrite_rules();
	}

	/**
	 * Adds a new template to WP page template selector.
	 *
	 * @param string $label the label to the template
	 * @param string $path the complete path to the template
	 * @param string $post_type the post type to link the template to (default to: "page")
 	 *
	 * @return array
	 */
	public function add_template( $label, $path, $post_type = "page" ) {
		$template_name = basename($path);

		$this->templates[ $template_name ] = __( $label, $this->plugin_name );
		$this->templates_paths[ $template_name ] = $path;

		add_filter( "theme_{$post_type}_templates", function($post_templates, $theme, $post, $post_type) use($template_name,$label){
			if(!array_key_exists($template_name,$post_templates)){
				$post_templates[$template_name] = $label;
			}
			return $post_templates;
		}, 10, 4 );

		return $this->templates;
	}

	/**
	 * Adds a template to the WP template hierarchy. This is not required for most standard template. WBF will try to guess
	 * the template file name by get_queried_object (for archives) and post-type (for everything else) - see: locate_template_file_in_hierarchy() and then
	 * search for that file name in current child/parent theme and in current plugin standard directories.
	 *
	 * @param string $template_name 	the name of the template (must match WP template hierarchy scheme)
	 * @param string|null $path 		the complete path to the template. If null, $this->get_src_dir()."templates/".$template_name will be taken.
	 *
	 * @return array 					the registered templates
	 */
	public function add_hierarchy_template($template_name, $path = null){
		$this->hierarchy_templates[] = $template_name;
		if(!isset($path)){
			$this->templates_paths[ $template_name ] = $this->get_src_dir()."templates/".$template_name;
		}else{
			$this->templates_paths[ $template_name ] = $path;
		}
		return $this->hierarchy_templates;
	}

	public function add_cpt_template( $template_name, $path = null ) {
		return $this->add_hierarchy_template($template_name,$path);
	}

	/**
	 * Adds a template to the Woocommerce template hierarchy
	 *
	 * @param string $template_name 	the name of the template (must match WP template hierarchy scheme)
	 * @param string|null $path 		the complete path to the template. If null, $this->get_src_dir()."templates/".$template_name will be taken.
	 *
	 * @return array 					the registered templates
	 */
	public function add_wc_template( $template_name, $path = null ){
		$this->wc_templates[] = $template_name;
		if(!isset($path)){
			$this->templates_paths[ $template_name ] = $this->get_src_dir()."templates/woocommerce/".$template_name;
		}

		//WE NEED A SPECIAL CASE FOR "archive-product.php" read on...
		if($template_name == "archive-product.php"){
			add_filter("template_include",function($template){
				//WooCommerce hard code the archive-product.php template in template_include. See class-wc-template-loader.php
				//We need to bypass this.
				if(!function_exists('wc_get_page_id')) return $template; //return early if no WC is detected
				if(is_post_type_archive('product') || is_page( wc_get_page_id('shop'))){ //For some reason this targets the SHOP PAGE
					//This is a copy-paste from WC source:
					$file 	= 'archive-product.php';
					$find[] = $file;
					$find[] = WC()->template_path() . $file;
					$theme_template = locate_template( array_unique( $find ) );
					if(!$theme_template && !WC_TEMPLATE_DEBUG_MODE && file_exists($this->templates_paths['archive-product.php'])){
						$template = $this->templates_paths['archive-product.php']; //If no theme template was found, inject our template (if exists)
					}
				}
				return $template;
			},11);
		}

		return $this->wc_templates;
	}

	/**
	 * Makes sure WooCommerce will search templates in plugin
	 *
	 * @hooked 'woocommerce_locate_template'
	 *
	 * @param $template
	 * @param $template_name
	 * @param $template_path
	 *
	 * @deprecated in favor of override_wc_get_templates
	 *
	 * @return mixed
	 */
	public function override_wc_locate_templates($template, $template_name, $template_path){
		remove_filter("woocommerce_locate_template",[$this,"override_wc_locate_templates"],11);

		//Check if theme has a template for current post\page
		$file = wc_locate_template($template_name);

		//Check if plugin has a template for current post\page
		if ( $file == "" && is_array($this->wc_templates) && in_array( $template_name, $this->wc_templates ) ) {
			$file = $this->templates_paths[ $template_name ];

			return $file;
		}

		add_filter("woocommerce_locate_template",[$this,"override_wc_locate_templates"],11,3);

		return $template;
	}

	/**
	 * Inject templates into WC template hierarchy
	 *
	 * @hooked 'wc_get_template'
	 *
	 * @param $located
	 * @param $template_name
	 * @param $args
	 * @param $template_path
	 * @param $default_path
	 *
	 * @return mixed
	 */
	public function override_wc_get_templates($located, $template_name, $args, $template_path, $default_path){
		if(in_array($template_name,$this->wc_templates)){
			//Check if theme has a template for current post\page
			$theme_dir = get_stylesheet_directory();
			if(preg_match("|$theme_dir|",$located)){
				return $located; //Return if the theme override the template
			}
			//Check if plugin has a template for current post\page
			if(isset($this->templates_paths[ $template_name ]) && is_file($this->templates_paths[ $template_name ])){
				$located = $this->templates_paths[ $template_name ];
			}
		}

		return $located;
	}

	/**
	 * Store current searched template, used later in locate_template_file_in_hierarchy()
	 *
	 * @hooked 'template_redirect'
	 */
	public function store_searched_hierarchy_templates(){
		$templates_types = [
			'archive',
			'category',
			'tag',
			'taxonomy',
			'emded',
			'page',
			'single',
			'singular',
			'attachment'
		];
		foreach ($templates_types as $type){
			add_filter("{$type}_template_hierarchy", function($templates) use($type){
				if(!is_array($this->current_searched_templates)) $this->current_searched_templates = [];
				$this->current_searched_templates = array_merge($this->current_searched_templates,$templates);
				return $templates;
			});
		}
	}

	/**
	 * Checks if the template is assigned to the page
	 *
	 * @hooked 'template_include'
	 * 
	 * @param $template
	 *
	 * @return string
	 */
	public function view_template( $template ) {
		global $post;

		// If no posts found, return to
		// avoid "Trying to get property of non-object" error
		if ( ! isset( $post ) ) {
			return $template;
		}

		$required_tpl = get_post_meta( $post->ID, '_wp_page_template', true ); //Get the template set via wp editor

		if($required_tpl == "" || $required_tpl == "default" || !$required_tpl || !is_string($required_tpl)){
			$file = $this->locate_template_file_in_hierarchy();
		}else{
			$file = $this->locate_template_file($required_tpl);
		}

		if(!$file){
			return $template;
		}

		return $file;
	}

	/**
	 * Locate a file registered with $this->add_template()
	 *
	 * @param $required_tpl
	 *
	 * @return bool|string
	 */
	private function locate_template_file($required_tpl){
		$file = false;
		if(isset($this->templates[$required_tpl])){ //We need at least the base template...
			//But search in theme first...
			foreach ($this->get_directories_of_templates_in_theme() as $directory){
				if(file_exists($directory."/".$required_tpl)){
					$file = $directory."/".$required_tpl;
					break;
				}
			}
			//Then get the base template...
			if(!$file){
				$located = $this->templates_paths[$required_tpl];
				if(file_exists($located)){
					$file = $located;
				}
			}
		}
		return $file;
	}

	/**
	 * Locate a WordPress template hierarchy file (that can also be registered with $this->register_cpt_template())
	 *
	 * @uses $this->assemble_possible_hierarchy_templates
	 *
	 * @return bool|string
	 */
	private function locate_template_file_in_hierarchy(){
		$file = false;
		if(isset($this->current_searched_templates) && !empty($this->current_searched_templates)){
			/*
			 * This part make use of {$type}_template_hierarchy filter introduced with WordPress 4.7
			 */
			$possible_templates = $this->current_searched_templates;
		}else{
			/*
			 * This is a fallback
			 */
			$possible_templates = $this->assemble_possible_hierarchy_templates();
		}

		//Check if theme or current plugin has a template for current post\page
		foreach ( $possible_templates as $tpl_filename ) {
			if(!in_array($tpl_filename,$this->hierarchy_templates)) continue; //skip not registered templates
			/*
			 * Locate the template into theme or current plugin directories.
			 * Adds specific directories where the template file will be looked for
			 */
			$paths = $this->add_template_base_path($this->get_directories_of_templates_in_theme());
			//In Utility::locate_template is hooked $this->add_template_base_path at "wbf/locate_template/search_paths" which adds plugins paths to search locations
			$located = Utilities::locate_template(['names' => $tpl_filename],false,false,$paths);
			if(!empty($located)){
				$file = $located;
				break;
			}
		}

		return $file;
	}

	/**
	 * Mimic the WordPress template-loader.php logic
	 *
	 * @return array
	 */
	private function assemble_possible_hierarchy_templates(){
		global $post;
		if(is_archive()){
			$q_obj = get_queried_object();
			if(is_category()){
				$possible_templates = array(
					"category-{$q_obj->slug}",
					"category-{$q_obj->term_id}.php"
				);
			}elseif(is_tag()){
				$possible_templates = array(
					"tag-{$q_obj->slug}",
					"tag-{$q_obj->term_id}.php"
				);
			}elseif(is_tax()){
				$possible_templates = array(
					"taxonomy-{$q_obj->taxonomy}-{$q_obj->slug}.php",
					"taxonomy-{$q_obj->taxonomy}.php"
				);
			}else{
				$post_type = get_post_type( $post->ID );
				$possible_templates = array(
					"archive-{$post_type}.php"
				);
			}
		}elseif(is_search()){
			$possible_templates = [
				'search.php'
			];
		}else{
			$post_type = get_post_type( $post->ID );
			$possible_templates = array(
				"attachment.php",
				"single-" . $post_type . ".php",
				"single-post.php",
				$post_type . ".php",
				"single-" . $post->ID . ".php"
			);
		}
		return $possible_templates;
	}

	/**
	 * Adds template sources directories for Utilities::locate_template() function.
	 * 
	 * @hooked 'wbf/locate_template/search_paths'
	 *
	 * Utilities::locate_template() is used here by locate_template_file_in_hierarchy() (but with different template sources location) and by Utilities::get_template_part().
	 * Utilities::get_template_part() is frequently used in plugins as wbf_get_template_part() to obtain template partials.
	 * 
	 * @param $paths
	 *
	 * @return array
	 */
	public function add_template_base_path($paths){
		$new_paths = array(
			$this->get_src_dir(),
			$this->get_dir(),
			$this->get_src_dir()."templates",
			$this->get_src_dir()."templates/parts",
			$this->get_src_dir()."public",
			$this->get_dir()."public",
			$this->get_dir()."templates",
		);

		$new_paths = array_merge($this->get_directories_of_templates_in_theme(),$new_paths);
		$new_paths = apply_filters("wbf/plugin_framework/template_plugin/template_parts_src",$new_paths,$this);
		$new_paths = apply_filters("wbf/plugins/{$this->plugin_name}/template_parts_src",$new_paths,$this);

		$new_paths = array_unique($new_paths);

		foreach($new_paths as $np){
			if(!in_array($np,$paths)){
				$paths[] = $np;
			}
		}

		return $paths;
	}

	/**
	 * Gets the paths where template file can be looked for in themes
	 */
	private function get_directories_of_templates_in_theme(){
		$stylesheet_dir = \get_stylesheet_directory();
		$template_dir = \get_template_directory();

		$directories = [
			$stylesheet_dir."/".$this->get_plugin_name(),
			$stylesheet_dir."/templates/".$this->get_plugin_name(),
			$stylesheet_dir."/templates/".$this->get_plugin_name()."/parts",
			$template_dir."/".$this->get_plugin_name(),
			$template_dir."/templates/".$this->get_plugin_name(),
			$template_dir."/templates/".$this->get_plugin_name()."/parts"
		];

		$directories = array_unique($directories);

		return $directories;
	}

	/**
	 * Getter of registered templates
	 *
	 * @return array
	 */
	public function get_registered_templates(){
		$tpl = [
			'cpt_templates' => $this->hierarchy_templates,
			'std_templates' => $this->templates,
			'wc_templates' => isset($this->wc_templates) ? $this->wc_templates : []
		];
		return $tpl;
	}

	/**
	 * Checks if can attach the wrapper
	 *
	 * @hooked 'init', 20
	 */
	public function maybe_attach_wrapper(){
		$slug = $this->get_plugin_name();
		if(!has_action( $slug.'/before_main_content')){
			add_action( $slug.'/before_main_content', [$this, 'render_wrapper_start'] );
		}
		if(!has_action( $slug.'/after_main_content')){
			add_action( $slug.'/after_main_content', [$this, 'render_wrapper_end'] );
		}
	}

	/**
	 * Render the wrapper start for templates
	 */
	public function render_wrapper_start(){
		switch( wp_get_theme()->get_template() ) {
			case 'twentyeleven' :
				echo '<div id="primary"><div id="content" role="main" class="twentyeleven">';
				break;
			case 'twentytwelve' :
				echo '<div id="primary" class="site-content"><div id="content" role="main" class="twentytwelve">';
				break;
			case 'twentythirteen' :
				echo '<div id="primary" class="site-content"><div id="content" role="main" class="entry-content twentythirteen">';
				break;
			case 'twentyfourteen' :
				echo '<div id="primary" class="content-area"><div id="content" role="main" class="site-content twentyfourteen"><div class="tfwc">';
				break;
			case 'twentyfifteen' :
				echo '<div id="primary" role="main" class="content-area twentyfifteen"><div id="main" class="site-main t15wc">';
				break;
			case 'twentysixteen' :
				echo '<div id="primary" class="content-area twentysixteen"><main id="main" class="site-main" role="main">';
				break;
			default :
				echo '<div id="container"><div id="content" role="main">';
				break;
		}
	}

	/**
	 * Render the wrapper end for templates
	 */
	public function render_wrapper_end(){
		switch( wp_get_theme()->get_template() ) {
			case 'twentyeleven' :
				echo '</div></div>';
				break;
			case 'twentytwelve' :
				echo '</div></div>';
				break;
			case 'twentythirteen' :
				echo '</div></div>';
				break;
			case 'twentyfourteen' :
				echo '</div></div></div>';
				get_sidebar( 'content' );
				break;
			case 'twentyfifteen' :
				echo '</div></div>';
				break;
			case 'twentysixteen' :
				echo '</div></main>';
				break;
			default :
				echo '</div></div>';
				break;
		}
	}
}