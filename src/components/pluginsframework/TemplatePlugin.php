<?php

namespace WBF\components\pluginsframework;

use WBF\components\utils\Utilities;

class TemplatePlugin extends BasePlugin implements TemplatePlugin_Interface {
	protected $templates;
	protected $ctp_templates;
	protected $wc_templates; //Embedded support for WooCommerce
	protected $templates_paths;

	public function __construct( $plugin_name, $dir, $version = "1.0.0" ) {
		parent::__construct( $plugin_name, $dir, $version );
		$this->templates       = array();
		$this->templates_paths = array();
		$this->ctp_templates   = array();
		$this->loader->add_filter( 'page_attributes_dropdown_pages_args', $this, "register_templates" );
		$this->loader->add_filter( 'wp_insert_post_data', $this, "register_templates" );
		$this->loader->add_filter( 'template_include', $this, "view_template" );
		$this->loader->add_filter( 'wbf/locate_template/search_paths', $this, 'add_template_base_path', 10, 2 );
		//Embedded support for WooCommerce
		$this->wc_templates = array();
		if(function_exists('is_woocommerce')){
			$this->loader->add_filter( 'woocommerce_locate_template',$this,"override_wc_templates", 11, 3);
		}
		//Embedded support for template wrappers
		$this->loader->add_action( 'init', $this, "maybe_attach_wrapper", 20 );
		//Just to be sure...
		$this->loader->add_action( 'init', $this, "flush_rewrites", 99 );
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
	 * @param string $template_name
	 * @param string $label
	 * @param string $path the complete path to the template
	 *
	 * @return array
	 */
	public function add_template( $template_name, $label, $path ) {
		$current_wp_templates = wp_get_theme()->get_page_templates(); //current wp registered templates

		$this->templates[ $template_name ] = __( $label, $this->plugin_name );
		$this->templates_paths[ $template_name ] = $path;
		$current_wp_templates = array_merge( $current_wp_templates, $this->templates );

		return $this->templates;
	}

	/**
	 * Adds a template to the WP template hierarchy
	 * 
	 * @param string $template_name 	the name of the template (must match WP template hierarchy scheme)
	 * @param string|null $path 		the complete path to the template. If null, $this->get_src_dir()."templates/".$template_name will be taken.
	 *
	 * @return array 					the registered templates
	 */
	public function add_cpt_template( $template_name, $path = null ) {
		$this->ctp_templates[] = $template_name;
		if(!isset($path)){
			$this->templates_paths[ $template_name ] = $this->get_src_dir()."templates/".$template_name;
		}else{
			$this->templates_paths[ $template_name ] = $path;
		}
		return $this->ctp_templates;
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
	 * @return mixed
	 */
	public function override_wc_templates($template, $template_name, $template_path){
		remove_filter("woocommerce_locate_template",[$this,"override_wc_templates"],11);

		//Check if theme has a template for current post\page
		$file = wc_locate_template($template_name);

		//Check if plugin has a template for current post\page
		if ( $file == "" && is_array($this->wc_templates) && in_array( $template_name, $this->wc_templates ) ) {
			$file = $this->templates_paths[ $template_name ];

			return $file;
		}

		return $template;
	}

	/**
	 * Adds plugin templates to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doens't really exist.
	 *
	 * @hooked 'wp_insert_post_data'
	 *
	 * @param array $atts The attributes for the page attributes dropdown
	 *
	 * @return array
	 */
	public function register_templates( $atts ) {
		if(!is_admin()) return $atts; //Otherwise this method will be called on every post creation.

		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list. If it doesn't exist, or it's empty prepare an array
		$templates = wp_cache_get( $cache_key, 'themes' );
		if(empty($templates) && function_exists("get_page_templates")){
			$templates = array_flip(get_page_templates());
		}

		if(is_array($templates)){
			// Since we've updated the cache, we need to delete the old cache
			wp_cache_delete( $cache_key, 'themes' );

			// Now add our template to the list of templates by merging our templates
			// with the existing templates array from the cache.
			$templates = array_merge( $templates, $this->templates ); //Adding plugin templates

			// Add the modified cache to allow WordPress to pick it up for listing
			// available templates
			wp_cache_add( $cache_key, $templates, 'themes', 1800 );
		}

		return $atts;
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

		if($required_tpl == "" || !$required_tpl || !is_string($required_tpl)){
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
	 * @return bool|string
	 */
	private function locate_template_file_in_hierarchy(){
		global $post;
		$file = false;

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

		//Check if theme has a template for current post\page
		foreach ( $possible_templates as $tpl_filename ) {
			/*
			 * Locate the template into theme directories.
			 * Adds specific directories where the template file will be looked for
			 */
			$located = Utilities::locate_template(['names' => $tpl_filename],false,false,$this->get_directories_of_templates_in_theme());
			if(!empty($located)){
				$file = $located;
				break;
			}
		}

		if(!$file){
			//Check if plugin has a template for current post\page
			foreach ( $possible_templates as $tpl_filename ) {
				if ( is_array($this->ctp_templates) && in_array( $tpl_filename, $this->ctp_templates ) ) {
					$located = $this->templates_paths[ $tpl_filename ];
					//todo: do some checks?
					$file = $located;
					break;
				}
			}
		}

		return $file;
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
		$directories = [
			\get_stylesheet_directory()."/".$this->get_plugin_name(),
			\get_stylesheet_directory()."/templates/".$this->get_plugin_name(),
			\get_stylesheet_directory()."/templates/".$this->get_plugin_name()."/parts",
			\get_template_directory()."/".$this->get_plugin_name(),
			\get_template_directory()."/templates/".$this->get_plugin_name(),
			\get_template_directory()."/templates/".$this->get_plugin_name()."/parts"
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
			'cpt_templates' => $this->ctp_templates,
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