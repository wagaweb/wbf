<?php

namespace WBF\components\pluginsframework;

use WBF\components\license\License_Manager;
use WBF\components\notices\Notice_Manager;
use WBF\components\license\License;
use WBF\components\customupdater\Plugin_Update_Checker;
use WBF\components\utils\Utilities;

class BasePlugin {
	/**
	 * A reference to an instance of this class for singleton usage.
	 *
	 * @since 1.0.0
	 *
	 * @var   BasePlugin
	 */
	private static $instance;
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Loader $loader maintains and registers all hooks for the plugin.
	 */
	protected $loader;
	/**
	 * The i18n instance
	 *
	 * @access protected
	 * @var I18n
	 */
	protected $i18n;
	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;
	/**
	 * The plugin dir
	 *
	 * @since    1.0.0
	 * @access   protected
	 */
	protected $plugin_dir;
	/**
	 * The full path to main plugin file
	 *
	 * @since 0.10.0
	 * @access   protected
	 * @var string
	 */
	protected $plugin_path;
	/**
	 * The path relative to WP_PLUGIN_DIR
	 *
	 * @var string
	 */
	protected $plugin_relative_dir;
	/**
	 * The path to /src/ if exists
	 * 
	 * @var string
	 */
	protected $src_path;
	/**
	 * The namespace to the public class (if provided)
	 * @var string
	 */
	protected $public_class_name;
	/**
	 * The namespace to the admin class (if provided)
	 * @var string
	 */
	protected $admin_class_name;
	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;
	/**
	 * The instance of Plugin_Update_Checker
	 *
	 * @since    0.10.0
	 * @access   protected
	 * @var      object
	 */
	protected $update_instance;
	/**
	 * The instance of License
	 * @access public
	 * @var \WBF\includes\License
	 */
	public $license = false;
	/**
	 * The instance of Notice_Manager.
	 * @var Notice_Manager
	 */
	public $notice_manager;
	/**
	 * @var boolean
	 * @since 0.14.8
	 */
	var $use_object_cache = false;
	/**
	 * @var bool
	 */
	protected $debug_mode = false;
	/**
	 * @var bool
	 */
	protected $script_debug_mode = false;

	/**
	 * BasePlugin constructor.
	 *
	 * @param string $plugin_name
	 * @param string $dir
	 * @param string $version
	 */
	public function __construct( $plugin_name, $dir, $version = "1.0.0" ) {
		$this->plugin_name = $plugin_name;
		$this->plugin_dir  = $dir;
		$this->plugin_path = $this->plugin_dir.$this->plugin_name.".php";
		//Set relative path
		$pinfo = pathinfo($dir);
		$this->plugin_relative_dir = "/".$pinfo['basename'];
		//Set src path
		if(is_dir($this->plugin_dir."/src")){
			$this->src_path = $this->plugin_dir."src/";
		}

		//Set paths for Admin and Public
		$class_name_parts = explode("\\",get_class($this));
		if(!isset($this->public_class_name) && (is_file($this->get_src_dir()."public/class-public.php") || is_file($this->get_src_dir()."public/Public.php"))){
			$class_name = $class_name_parts[0].'\pub\Pub';
			$this->public_class_name = $class_name;
		}elseif(!isset($this->public_class_name) && is_file($this->get_src_dir()."frontend/Frontend.php")){
			$class_name = $class_name_parts[0].'\frontend\Frontend';
			$this->public_class_name = $class_name;
		}elseif(!isset($this->public_class_name) && is_file($this->get_src_dir()."Frontend.php")){ //@since 0.14.8
			$class_name = $class_name_parts[0].'\Frontend';
			$this->public_class_name = $class_name;
		}

		if(!isset($this->admin_class_name) && (is_file($this->get_src_dir()."admin/class-admin.php") || is_file($this->get_src_dir()."admin/Admin.php"))){
			$class_name = $class_name_parts[0].'\admin\Admin';
			$this->admin_class_name = $class_name;
		}elseif(!isset($this->admin_class_name) && is_file($this->get_src_dir()."Admin.php")){ //@since 0.14.8
			$class_name = $class_name_parts[0].'\Admin';
			$this->admin_class_name = $class_name;
		}

		//Get the version
		if(function_exists("get_plugin_data")){
			$pluginHeader = get_plugin_data($this->plugin_path, false, false);
			if ( isset($pluginHeader['Version']) ) {
				$this->version = $pluginHeader['Version'];
			} else {
				$this->version = $version;
			}
		}else{
			$this->version = $version;
		}

		// Adds custom links in page plugins
		$plugin_file = $this->plugin_name.'/'.$this->plugin_name.'.php';
		$this->loader->add_action('plugin_action_links_'.$plugin_file, $this, 'add_plugin_action_links');


		//Check if debug mode must be activated
		if( (defined("WP_DEBUG") && WP_DEBUG) || (defined("WBF_ENV") && WBF_ENV == "dev") ){
			$this->debug_mode = true;
		}

		if( defined("SCRIPT_DEBUG") && SCRIPT_DEBUG ){
			$this->script_debug_mode = true;
		}

		$GLOBALS['wbf_loaded_plugins'][$this->get_plugin_name()] = &$this;

		$this->load_dependencies();
		$this->set_locale();
	}

	/**
	 * Adds a settings link in the plugin page after deactivation link only (i.e. not visible unless you activate the plugin)
	 *
	 * @param array $links array of action links (e.g. activation/deactivation)
	 * @hooked plugin_action_links_plugin_name/plugin_name.php
	 * @return array
	 */
	public function add_plugin_action_links($links){
		/**
		 * Do we want some defaults ??
		 */
//		$default_settings_link = [
//			[
//				'name' => 'name',
//				'link' => 'link'
//			]
//		];
//		$pages = apply_filters('wbf/plugin_framework/base_plugin', $default_settings_links);
		$pages = [];
		$pages = apply_filters('wbf/plugin_framework/base_plugin/wbf_action_links', $pages);

		if (isset($pages) && !empty($pages)) {
			foreach ( $pages as $page ) {
				$links[] = '<a href="' . esc_url( get_admin_url( null, $page['link'] ) ) . '">' . $page['name'] . '</a>';
			}
		}
		return $links;
	}

	/**
	 * Builds the add_action for add_plugin_action_link()
	 *
	 */
	public function set_plugin_action_link() {
		$plugin_file = $this->plugin_name.'/'.$this->plugin_name;
		$this->loader->add_action('plugin_action_links_'.$plugin_file.'.php', $this, 'add_plugin_action_links');
	}


	/**
	 * Set the update server for the plugin. You can specify also a License class.
	 * The License class must extends WBF\includes\License and implements WBF\includes\License_Interface.
	 *
	 * @param string|null $endpoint
	 * @param License|null $license
	 *
	 * @return bool|Plugin_Update_Checker
	 */
	public function set_update_server($endpoint = null,License $license = null){
		if(isset($endpoint) && is_string($endpoint) && !empty($endpoint)){
			if($license){
				$this->register_license($license);
			}elseif(is_file($this->plugin_dir."/includes/class-ls.php") && !isset($license)){
				//Automatically register a new license
				require_once $this->plugin_dir."/includes/class-ls.php";
				$classname = preg_replace("/Plugin/","LS",get_class($this));
				$license = $classname::getInstance($this->plugin_name);
				if($license){
					$license->type = "plugin";
					$this->register_license($license);
				}
			}
			$this->update_instance = new Plugin_Update_Checker(
				$endpoint,
				$this->plugin_dir.$this->plugin_name.".php",
				$this->plugin_name,
				$this->license
			);
			return $this->update_instance;
		}else{
			return false;
		}
	}

	/**
	 * Just a wrapper around License_Manager::register
	 * @param License $license
	 */
	public function register_license(License $license){
		$this->license = License_Manager::register_plugin_license($license);
	}

	/**
	 * Load the required dependencies for the plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Waboot_Galleries_Loader. Orchestrates the hooks of the plugin.
	 * - Waboot_Galleries_i18n. Defines internationalization functionality.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	protected function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		//require_once plugin_dir_path( dirname( __FILE__ ) ) . 'class-waboot-plugin-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		//require_once plugin_dir_path( dirname( __FILE__ ) ) . 'class-waboot-plugin-i18n.php';

		//Load Notice Manager if needed
		$wbf_notice_manager = Utilities::get_wbf_notice_manager();
		$this->notice_manager = &$wbf_notice_manager;

		$this->loader = new Loader($this);
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Waboot_Plugin_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$this->i18n = new I18n();
		$this->i18n->set_domain( $this->get_plugin_name() );
		$this->i18n->set_language_dir( $this->plugin_relative_dir."/languages/" );
		$this->loader->add_action( 'plugins_loaded', $this->i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Check if cache is enabled
	 *
	 * @since 0.14.8
	 *
	 * @return bool
	 */
	public function cache_enabled(){
		return $this->use_object_cache;
	}

	/**
	 * Get a list of plugin transient
	 *
	 * @since 0.14.8
	 *
	 * @return array|null|object
	 */
	function list_transients(){
		global $wpdb;
		$sql = "SELECT `option_name` AS `name`, `option_value` AS `value`
            FROM  $wpdb->options
            WHERE `option_name` LIKE '%transient_".$this->get_plugin_name()."%'
            ORDER BY `option_name`";
		$results = $wpdb->get_results( $sql );

		if(is_array($results)){
			return $results;
		}

		return [];
	}

	/**
	 * Clear plugin object cache
	 *
	 * @since 0.14.8
	 *
	 * @param null $node_type
	 * @param $node_id
	 */
	public function clear_transients($node_type = null, $node_id = null){
		global $wpdb;
		$transients = $this->list_transients();
		foreach($transients as $t){
			if(!isset($node_type)){
				$wpdb->delete($wpdb->options,[
					'option_name' => $t->name
				]);
			}else{
				if(isset($node_id)){
					$transient_pattern = "\[".$node_type.":".$node_id."\]";
				}else{
					$transient_pattern = "\[".$node_type."[:a-zA-Z0-9]*\]";
				}
				if(preg_match("/".$transient_pattern."/",$t->name)){
					$wpdb->delete($wpdb->options,[
						'option_name' => $t->name
					]);
				}
			}
		}
	}

	/**
	 * Get a transient if cache is enabled
	 *
	 * @since 0.14.8
	 *
	 * @param $transient_name
	 *
	 * @return bool
	 */
	public function maybe_get_transient($transient_name){
		if($this->cache_enabled()){
			$transient_name = $this->get_plugin_name()."[".$transient_name."]";
			return get_transient($transient_name);
		}
		return false;
	}

	/**
	 * Add a transient if cache is enabled.
	 *
	 * @usage
	 * This caching system supports a special type of transient names. Any provided transient name will be prefixed with the name of the plugin and wrapped in square brackets.
	 *
	 * So: foobar => pluginName[foobar]
	 *
	 * foobar will become a "node". You then can remove the whole node or parts of it.
	 *
	 * To create node parts, you have to specify their name after a colon: "foobar:baz".
	 *
	 * This is useful to create semantic nodes; by doing "maybe_set_transient("object:12",<value>)", you have just set the cache for the "object" with id 12. You then can remove only this cache or the whole "object".
	 *
	 * @since 0.14.8
	 *
	 * @param $transient_name
	 * @param $value
	 */
	public function maybe_set_transient($transient_name,$value){
		if($this->cache_enabled()){
			$transient_name = $this->get_plugin_name()."[".$transient_name."]";
			set_transient($transient_name,$value);
		}
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Returns an instance of this class. An implementation of the singleton design pattern.
	 *
	 * @param $plugin_name
	 * @param $dir
	 * @param $version
	 *
	 * @return BasePlugin A reference to an instance of this class.
	 * @since    1.0.0
	 */
	public static function get_instance( $plugin_name, $dir, $version ) {
		if ( null == self::$instance ) {
			self::$instance = new self( $plugin_name, $dir, $version );
		}

		return self::$instance;
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	public function get_uri(){
		return get_bloginfo("wpurl")."/wp-content/plugins/".$this->plugin_name."/";
	}


	public function get_dir(){
		return $this->plugin_dir;
	}

	public function get_src_dir(){
		if(isset($this->src_path)){
			return $this->src_path;
		}else{
			return $this->get_dir();
		}
	}

	public function get_path(){
		return $this->plugin_path;
	}

	public function get_relative_dir(){
		return $this->plugin_relative_dir;
	}

	public function get_public_class_name(){
		return $this->public_class_name;
	}

	public function get_admin_class_name(){
		return $this->admin_class_name;
	}

	public function is_debug(){
		return $this->debug_mode;
	}

	public function is_script_debug(){
		return $this->script_debug_mode;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Get the instance of i18n related to the plugin
	 * 
	 * @return I18n
	 */
	public function get_locale(){
		return $this->i18n;
	}

	/**
	 * Get the textdomain ralated to the plugin
	 *
	 * @return I18n|false
	 */
	public function get_textdomain(){
		$locale = $this->get_locale();
		if($locale instanceof I18n){
			return $locale->get_domain();
		}
		return false;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Get admin\core\frontend instances for $plugin
	 *
	 * @param string $plugin
	 *
	 * @return array with 'core', 'admin' and 'public' keys. Each keys is associated with respective classes.
	 * @throws \Exception
	 */
	static function get_instances_of($plugin){
		global $wbf_loaded_plugins;
		if(isset($wbf_loaded_plugins[$plugin])){
			$plugin = $wbf_loaded_plugins[$plugin];
			$loader = $plugin->get_loader();
			if($plugin && (isset($loader->public_plugin) || isset($loader->admin_plugin))){
				return [
					'core' => $plugin,
					'public' => isset($loader->public_plugin) ? $loader->public_plugin : false,
					'admin' => isset($loader->admin_plugin) ? $loader->admin_plugin : false
				];
			}else{
				throw new \Exception("Trying to get $plugin instances: module $plugin has no instances");
			}
		}else{
			return [];
		}
	}

	/**
	 * Get loaded plugin instances
	 *
	 * @return array
	 */
	static function get_loaded_plugins(){
		global $wbf_loaded_plugins;
		if(isset($wbf_loaded_plugins) && is_array($wbf_loaded_plugins)){
			return $wbf_loaded_plugins;
		}
		return [];
	}
}