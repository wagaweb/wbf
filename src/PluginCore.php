<?php

namespace WBF;

use WBF\components\assets\AssetsManager;
use WBF\components\compiler\Base_Compiler;
use WBF\components\compiler\less\Less_Compiler;
use WBF\components\compiler\Styles_Compiler;
use WBF\components\customupdater\Plugin_Update_Checker;
use WBF\components\license\License_Manager;
use WBF\components\mvc\HTMLView;
use WBF\components\notices\Notice_Manager;
use WBF\components\pluginsframework\BasePlugin;
use WBF\components\pluginsframework\TemplatePlugin;
use WBF\components\utils\Utilities;
use WBF\includes\GoogleFontsRetriever;
use WBF\includes\ServiceManager;
use WBF\legacy\Resources;
use WBF\modules\components\GUI;
use WBF\modules\options\Framework;

class PluginCore {

	/**
	 * @var string
	 */
	var $wp_menu_slug = "wbf_options";

	/**
	 * @var array
	 */
	private $options;

	/**
	 * @var array
	 */
	private $modules;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var string
	 */
	private $path;

	/**
	 * The WBF working directory path
	 * @var string
	 */
	private $working_directory;

	/**
	 * @var ServiceManager
	 */
	var $services;

	/**
	 * @var string
	 */
	const version = "1.1.0";

	/**
	 * WBF constructor.
	 *
	 * @param null|string $path
	 * @param null|string $url
	 * @param array $options
	 * @param null|ServiceManager $service_manager
	 *
	 * @throws \Exception
	 */
	public function __construct($path = null, $url = null, $options = [], $service_manager = null){
		$update_wbf_path_flag = false;
		$update_wbf_url_flag = false;

		if(!defined('WBF_DIRECTORY')){
			if(!$path){
				$path = get_option("wbf_path");
			}else{
				$update_wbf_path_flag = true;
			}

			if($path && is_string($path) && !empty($path)){
				$path = rtrim($path,"/")."/";
			}else{
				throw new \Exception('Invalid path provided');
			}

			if( $update_wbf_path_flag && ( get_option('wbf_path','') !== $path) ){
				update_option('wbf_path',$path);
			}

			define('WBF_DIRECTORY',$path);
		}else{
			$path = rtrim(WBF_DIRECTORY,"/")."/";
		}

		if(!defined('WBF_URL')){
			if(!$url){
				$url = get_option("wbf_url");
			}else{
				$update_wbf_url_flag = true;
			}

			if($url && is_string($url) && !empty($url)){
				$url = rtrim($url,"/")."/";
			}else{
				throw new \Exception('Invalid url provided');
			}

			if( $update_wbf_url_flag && ( get_option('wbf_url','') !== $url) ){
				update_option('wbf_path',$url);
			}

			define('WBF_URL',$url);
		}else{
			$url = rtrim(WBF_URL,"/")."/";;
		}

		$this->path = $path;

		$this->url = $url;

		if(!defined("WBF_WORK_DIRECTORY_NAME")){
			define("WBF_WORK_DIRECTORY_NAME","wbf-wd");
		}

		if(!defined("WBF_WORK_DIRECTORY")){
			define("WBF_WORK_DIRECTORY", WP_CONTENT_DIR."/".WBF_WORK_DIRECTORY_NAME);
		}

		$options = wp_parse_args($options,[
			'do_global_theme_customizations' => true,
			'check_for_updates' => true,
			'handle_errors' => true
		]);

		$this->options = $options;

		if(!isset($service_manager)){
			$service_manager = new ServiceManager();
		}
		$this->services = $service_manager;
	}

	/**
	 * WBF Startup. Adds filters and actions.
	 */
	public function startup(){
		if($this->options['handle_errors']){
			set_error_handler([$this,"handle_errors"],E_USER_WARNING); //http://php.net/manual/en/language.types.callable.php
		}

		$this->maybe_run_activation();
		$this->maybe_add_option();
		update_option("wbf_version",self::version);

		$this->maybe_add_work_directory();

		if($this->is_plugin()){
			add_action('activate_' . plugin_basename(__FILE__), [$this,"maybe_run_activation"]);
			add_action('deactivate_' . plugin_basename(__FILE__), [$this,"deactivation"]);
		}else{
			add_action( "after_switch_theme", [$this,"activation"] );
			add_action( "switch_theme", [$this,"deactivation"], 4 );
		}

		/*
		 * Main Actions: BEGIN
		 */

		if($this->is_plugin()) {
			add_action( "plugins_loaded", [$this,"plugins_loaded"], 11 );
		}
		add_action( "after_setup_theme", [$this,"after_setup_theme"], 11 );
		add_action( "init", [$this,"init"], 11 );

		add_action( 'wp_enqueue_scripts', [$this,"register_libs"] );
		add_action( "admin_enqueue_scripts", [$this, "enqueue_admin_assets"]);
		add_action( 'admin_enqueue_scripts', [$this,"register_libs"] );

		/*
		 * |- Main Actions: END
		 */

		//Setup admin menu:
		add_action( 'admin_menu', [$this,"admin_menu"], 11 );
		add_action( 'admin_bar_menu', [$this,"add_env_notice"], 1000 );
		add_action( 'admin_bar_menu', [$this,"add_admin_compile_button"], 990 );

		//Additional settings:
		add_filter( 'site_transient_update_plugins', [$this,"unset_unwanted_updates"], 999 );

		add_filter( 'wbf/modules/available', [$this,"do_not_load_pagebuilder"], 999 ); //todo: its not stable yet
		add_filter( 'wbf/modules/behaviors/priority', function(){
			return 9;
		});
		add_filter( 'wbf/modules/options/priority', function(){
			return 11;
		});
	}

	/*
	 *
	 *
	 * UTILITY
	 *
	 *
	 */

	/**
	 * WBF Error handler. Registerd during startup.
	 *
	 * @param int $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param int $errline
	 * @param array $errcontext
	 */
	public function handle_errors($errno,$errstr,$errfile = "",$errline = 0,$errcontext = []){
		$wbf_notice_manager = Utilities::get_wbf_notice_manager();
		if($wbf_notice_manager && is_admin() && current_user_can("manage_options")){
			$str = sprintf('[Admin Only] There was an USER_WARNING error generated at %s:%s: <strong>%s</strong>',basename($errfile),$errline,$errstr);
			$wbf_notice_manager->add_notice($errline,$str,"error","_flash_");
		}
	}

	/**
	 * Checks if current admin page is part of WBF
	 * @return bool
	 */
	public function is_wbf_admin_page(){
		global $plugin_page,$wbf_options_framework;

		//todo: this is ugly, we could expose a function like register_wbf_admin_page()
		$valid_pages = [
			WBF()->wp_menu_slug,
			GUI::$wp_menu_slug,
			'wbf_licenses'
		];

		if(isset($wbf_options_framework)){
			$valid_pages[] = $wbf_options_framework->admin->wp_menu_slug;
		}

		if(in_array($plugin_page,$valid_pages)){
			return true;
		}

		return false;
	}

	/**
	 * Get the copyright string
	 *
	 * @return string
	 */
	public function get_copyright(){
		$v = new components\mvc\HTMLView("src/views/admin/copyright.php","wbf");

		$label = "WBF";
		$version = self::version;

		$theme = wp_get_theme();
		if($theme && isset($theme->stylesheet)){
			if($theme->stylesheet == "waboot"){
				$label = "Waboot";
				$version = $theme->version;
			}
			elseif($theme->stylesheet != "waboot" && $theme->template == "waboot"){
				$theme = wp_get_theme("waboot");
				if($theme && isset($theme->version)){
					$label = "Waboot";
					$version = $theme->version;
				}
			}
		}

		$output = $v->clean()->get([
			'label' => $label,
			'version' => $version,
		]);

		return $output;
	}

	/**
	 * Print copyright string
	 *
	 * @return void
	 */
	public function print_copyright(){
		echo $this->get_copyright();
	}

	/**
	 * Checks if $module_name is loaded
	 *
	 * @param $module_name
	 *
	 * @return bool
	 */
	public function module_is_loaded($module_name){
		$modules = $this->get_modules();
		foreach($modules as $name => $params){
			if($name == $module_name) return true;
		}

		return false;
	}

	/**
	 * Retrieve WBF Modules
	 *
	 * @param bool|false $include
	 *
	 * @return mixed
	 */
	public function get_modules($include = false){
		static $modules = array();
		if(!empty($modules)){
			if(!$include){
				return $modules;
			}else{
				foreach($modules as $m){
					require_once $m['bootstrap'];
				}
			}
		}

		$modules_dir = $this->get_path()."src/modules";
		$dirs = array_filter(glob($modules_dir."/*"), 'is_dir');
		$dirs = apply_filters("wbf/modules/available", $dirs); //Allow developers to add\delete modules
		foreach($dirs as $d){
			$current_module_dir = $d;
			if(is_file($current_module_dir."/bootstrap.php")){
				$modules[basename($d)] = array(
					'path' => $current_module_dir,
					'bootstrap' => $current_module_dir."/bootstrap.php",
					'activation' => is_file($current_module_dir."/activation.php") ? $current_module_dir."/activation.php" : false,
					'deactivation' => is_file($current_module_dir."/deactivation.php") ? $current_module_dir."/deactivation.php" : false,
					'priority' => apply_filters("wbf/modules/".basename($d)."/priority",10)
				);
			}
		}

		uasort($modules,function($a,$b){
			if($a['priority'] == $b['priority']){
				return 0;
			}
			return ($a['priority'] < $b['priority']) ? -1 : 1;
		});

		if($include){
			foreach($modules as $m){
				require_once $m['bootstrap'];
			}
		}

		return $modules;
	}

	/**
	 * Retrieve WBF Extensions
	 *
	 * @param bool|false $include
	 *
	 * @return mixed
	 */
	public function get_extensions($include = false){
		static $exts = array();
		if(!empty($exts)){
			if(!$include){
				return $exts;
			}else{
				foreach($exts as $e){
					require_once $e['bootstrap'];
				}
			}
		}

		$exts_source_dirs = [
			$this->get_working_directory(true)."/_extensions",
			$this->get_path()."src/extensions"
		];
		$exts_dirs = [];
		foreach ($exts_source_dirs as $dir){
			if(!is_dir($dir)) continue;
			$dirs = array_filter(glob($dir."/*"), 'is_dir');
			$exts_dirs = array_merge($exts_dirs,$dirs);
		}
		$exts_dirs = apply_filters("wbf/extensions/available", $exts_dirs); //Allow developers to add\delete extensions
		foreach($exts_dirs as $d){
			$current_ext_dir = $d;
			if(is_file($current_ext_dir."/bootstrap.php")){
				$exts[basename($d)] = array(
					'path' => $current_ext_dir,
					'bootstrap' => $current_ext_dir."/bootstrap.php",
				);
				if($include) require_once $exts[basename($d)]['bootstrap'];
			}
		}
		return $exts;
	}

	/**
	 * Retrieve and includes WBF Modules
	 *
	 * @called at 'after_setup_theme', 11
	 *
	 * @return mixed
	 */
	public function load_modules(){
		return $this->get_modules(true);
	}

	/**
	 * Retrieve and includes WBF Extensions
	 *
	 * @hooked 'plugins_loaded'
	 *
	 * @since 0.13.10
	 *
	 * @return mixed
	 */
	public function load_extensions(){
		return $this->get_extensions(true);
	}

	/**
	 * Init modules activations procedures
	 */
	public function load_modules_activation_hooks(){
		$modules = $this->get_modules();
		foreach($modules as $m){
			if($m['activation']){
				require_once $m['activation'];
			}
		}
	}

	/**
	 * Init modules deactivation procedures
	 */
	public function load_modules_deactivation_hooks(){
		$modules = $this->get_modules();
		foreach($modules as $m){
			if($m['deactivation']){
				require_once $m['deactivation'];
			}
		}
	}

	/**
	 * Checks if WBF is in the plugins directory
	 *
	 * @return bool
	 */
	public function is_plugin(){
		$path = WBF()->get_path();
		$is_plugin = strpos( $path, "plugins" ) !== false;
		return apply_filters("wbf/is_plugin",$is_plugin);
	}

	/**
	 * Gets the currently loaded plugins
	 *
	 * @return array
	 */
	public function get_registered_plugins(){
		return BasePlugin::get_loaded_plugins();
	}

	/*
	 *
	 *
	 * PATHS AND RESOURCES
	 *
	 *
	 */

	/**
	 * Returns WBF url or FALSE
	 *
	 * @return bool|string
	 */
	public function get_url(){
		return $this->url;
	}

	/**
	 * Returns WBF path or FALSE
	 *
	 * @return bool|string
	 */
	public function get_path(){
		return $this->path;
	}

	/**
	 * Gets WBF admin assets uri
	 *
	 * @return bool|string
	 */
	public function get_admin_assets_uri(){
		return $this->get_assets_uri(true);
	}

	/**
	 * Gets WBF assets uri
	 * @param bool $admin_assets_flag
	 *
	 * @return bool|string
	 */
	public function get_assets_uri($admin_assets_flag = false){
		if($admin_assets_flag){
			return $this->prefix_url("admin");
		}else{
			return $this->prefix_url("public");
		}
	}

	/**
	 * Returns WBF working directory
	 *
	 * @param bool $base (return dirname() of working directory)
	 *
	 * @return bool|string
	 */
	public function get_wd($base = false){
		return $this->get_working_directory($base);
	}

	/**
	 * Returns WBF base working directory (without the theme)
	 *
	 * @return bool|string
	 */
	public function get_base_working_directory(){
		if($this->working_directory){
			return rtrim(dirname($this->working_directory),"/");
		}
		return false;
	}

	/**
	 * Returns WBF working directory
	 *
	 * @param bool $base (return dirname() of working directory)
	 *
	 * @return bool|string
	 */
	public function get_working_directory($base = false){
		if($this->working_directory){
			if($base){
				return dirname(rtrim($this->working_directory,"/"));
			}
			return rtrim($this->working_directory,"/");
		}
		return false;
	}

	/**
	 * Returns WBF working directory URI
	 *
	 * @param bool $base
	 *
	 * @return mixed
	 */
	public function get_working_directory_uri($base = false){
		return path_to_url($this->get_working_directory($base));
	}

	/**
	 * Tries to create the WBF working directory
	 */
	private function maybe_add_work_directory(){
		$theme = wp_get_theme();
		if(defined("WBF_WORK_DIRECTORY_NAME")){
			$path = WBF_WORK_DIRECTORY."/".$theme->get_stylesheet();
			if(!is_dir(WBF_WORK_DIRECTORY)){ //We do not have the working directory
				Utilities::mkpath($path);
			}elseif(!is_dir($path)){ //We have the working directory, but not the theme directory in it
				@mkdir($path);
			}
			if(is_dir($path)){
				$this->working_directory = $path;
			}
		}
	}

	/**
	 * Prefix $to with the WBF URL
	 * @param $to
	 *
	 * @return bool|string
	 */
	public function prefix_url($to){
		$url = trim($this->get_url());
		$to = trim($to);
		if($url){
			return rtrim($url,"/")."/".ltrim($to,"/");
		}else{
			return false;
		}
	}

	/**
	 * Prefix $to with the WBF PATH
	 * @param $to
	 *
	 * @return bool|string
	 */
	public function prefix_path($to){
		$path = trim($this->get_path());
		$to = trim($to);
		if($path){
			return rtrim($path,"/")."/".ltrim($to,"/");
		}else{
			return false;
		}
	}

	/*
	 *
	 *
	 * BACKUP FUNCTIONS
	 *
	 *
	 */

	public function get_behavior( $name, $post_id = 0, $return = "value" ) {
		if ( $post_id == 0 ) {
			global $post;
			$post_id = $post->ID;
		}

		$b = get_post_meta( "_behavior_" . $post_id, $name, true );

		if(!isset($b) || (is_bool($b) && $b == false)){
			$config = get_option( 'optionsframework' );
			$b = \WBF\modules\options\of_get_option( $config['id'] . "_behavior_" . $name );
		}

		$b = apply_filters("wbf/modules/behaviors/get",$b);
		$b = apply_filters("wbf/modules/behaviors/get/".$name,$b);

		return $b;
	}

	/*
	 *
	 *
	 * HOOKS
	 *
	 *
	 */

	/**
	 * Apply some hooks to the current theme
	 *
	 * @called at 'after_setup_theme', 11
	 */
	public function do_global_theme_customizations(){
		// Global Customization
		wbf_locate_file( '/src/includes/theme-customs.php', true );

		// Email encoder
		wbf_locate_file( '/src/includes/email-encoder.php', true );
	}

	/**
	 * Wordpress "plugins_loaded" callback
	 */
	public function plugins_loaded(){
		// Load extensions
		$this->load_extensions();
	}

	/**
	 * Wordpress "after_setup_theme" callback
	 */
	public function after_setup_theme() {
		// Loads notice manager. The notice manager can be already loaded by plugins constructor prior this point.
		$wbf_notice_manager = Utilities::get_wbf_notice_manager();
		$this->services->set_notice_manager($wbf_notice_manager);

		$this->options = apply_filters("wbf/options",$this->options);

		$this->wp_menu_slug = "wbf_options";

		$this->modules = $this->load_modules();

		do_action("wbf_after_module_loaded");

		do_action("wbf_after_setup_theme");

		// Make framework available for translation.
		load_textdomain( 'wbf', $this->get_path() . 'languages/wbf-'.get_locale().".mo");

		if($this->options['do_global_theme_customizations']){
			$this->do_global_theme_customizations();
		}

		// ACF INTEGRATION
		if(!$this->is_plugin()){
			$this->load_extensions();
		}

		// Google Fonts
		if(class_exists("WBF\\includes\\GoogleFontsRetriever")) $GLOBALS['wbf_gfont_fetcher'] = GoogleFontsRetriever::getInstance();

		do_action("wbf_after_setup_theme_end");
	}

	/**
	 * Wordpress "init" callback
	 */
	public function init() {
		do_action("wbf_init");

		if($this->options['check_for_updates']){
			//Set update server
			if($this->is_plugin()){
				$this->services->set_updater(new Plugin_Update_Checker(
					"http://update.waboot.org/resource/info/plugin/wbf", //$metadataUrl
					$this->get_path()."wbf.php", //$pluginFile
					"wbf", //$slug
					null, //$plugin_license
					false, //$checkLicense
					12, //$checkPeriod
					'wbf_updates', //$optionName
					is_multisite() //$muPluginFile
				));
			}
		}

		// Breadcrumbs
		wbf_locate_file( '/src/components/breadcrumb/functions.php', true);
		/*if(!class_exists("Breadcrumb_Trail") && !function_exists("breadcrumb_trail")){
			wbf_locate_file( '/src/components/breadcrumb/vendor/breadcrumb-trail.php', true);
			wbf_locate_file( '/src/components/breadcrumb/WBF_Breadcrumb_Trail.php', true);
		}*/


		if(function_exists('\WBF\modules\options\of_check_options_deps')) \WBF\modules\options\of_check_options_deps(); //Check if theme options dependencies are met
		$GLOBALS['wbf_notice_manager']->enqueue_notices(); //Display notices

		do_action("wbf_init_end");
	}

	/**
	 * Enqueues admin relative assets
	 */
	public function enqueue_admin_assets(){
		$assets = [
			"wbf-admin" => [
				'uri' => defined("SCRIPT_DEBUG") && SCRIPT_DEBUG ? WBF()->prefix_url("assets/dist/js/wbf-admin.js") : Resources::getInstance()->prefix_url("assets/dist/js/wbf-admin.min.js"),
				'path' => defined("SCRIPT_DEBUG") && SCRIPT_DEBUG ? WBF()->prefix_path("assets/dist/js/wbf-admin.js") : Resources::getInstance()->prefix_path("assets/dist/js/wbf-admin.min.js"),
				'deps' => apply_filters("wbf/js/admin/deps",["jquery","backbone","underscore"]),
				'i10n' => [
					'name' => 'wbfData',
					'params' => apply_filters("wbf/js/admin/localization",[
						'ajaxurl' => admin_url('admin-ajax.php'),
						'wpurl' => get_bloginfo('wpurl'),
						'wp_screen' => function_exists("get_current_screen") ? get_current_screen() : null,
						'isAdmin' => is_admin(),
						'is_wbf_admin_page' => $this->is_wbf_admin_page()
					])
				],
				'type' => 'js',
			],
			'wbf-admin-style' => [
				'uri' => WBF()->prefix_url('assets/dist/css/admin.min.css'),
				'path' => WBF()->prefix_path('assets/dist/css/admin.min.css'),
				'type' => 'css'
			]
		];
		$am = new AssetsManager($assets);
		$am->enqueue();
	}

	/**
	 * Register libraries used by WBF ecosystem
	 */
	public function register_libs(){
		$res = WBF();
		$libs = [
			"jquery-ui-style" => [
				'uri' => "//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css",
				'type' => 'css',
				'enqueue' => false
			],
			"owlcarousel-css" => [
				'uri' => $res->prefix_url("/vendor/owl.carousel/dist/assets/owl.carousel.css"),
				'path' => $res->prefix_path("/vendor/owl.carousel/dist/assets/owl.carousel.css"),
				'type' => 'css',
				'enqueue' => false
			],
			"gmapapi" => [
				"uri" => 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places',
				'type' => "js",
				'deps' => ['jquery'],
				'enqueue' => false
			],
			"imagesLoaded-js" => [
				"uri" => $res->prefix_url("/vendor/imagesloaded/imagesloaded.pkgd.min.js"),
				"path" => $res->prefix_path("/vendor/imagesloaded/imagesloaded.pkgd.min.js"),
				"type" => "js",
				'enqueue' => false,
				'in_footer' => true,
			],
			"owlcarousel-js" => [
				"uri" => $res->prefix_url("/vendor/owl.carousel/dist/owl.carousel.min.js"),
				"path" => $res->prefix_path("/vendor/owl.carousel/dist/owl.carousel.min.js"),
				"type" => "js",
				'enqueue' => false,
				'in_footer' => true,
			],
			/* For now, this is included via Advanced_Color.php
			"spectrum-js" => [
				"uri" => $res->prefix_url("/assets/dist/js/includes/spectrum.min.js"),
				"path" => $res->prefix_path("/assets/dist/js/includes/spectrum.min.js"),
				"type" => "js",
				'enqueue' => false,
				'in_footer' => true,
			]*/
		];
		if( (defined("WBF_ENV") && WBF_ENV === "dev") || SCRIPT_DEBUG){
			$libs["wbfgmapmc"] = [
				"uri" => $res->prefix_url("/assets/src/js/includes/wbfgmap/markerclusterer.js"),
				"path" => $res->prefix_path("/assets/src/js/includes/wbfgmap/markerclusterer.js"),
				"deps" => ["jquery","gmapapi"],
				"type" => "js",
				'enqueue' => false,
				'in_footer' => true,
			];
			$libs["wbfgmap"] = [
				"uri" => $res->prefix_url("/assets/src/js/includes/wbfgmap/acfmap.js"),
				"path" => $res->prefix_path("/assets/src/js/includes/wbfgmap/acfmap.js"),
				"deps" => ["jquery","gmapapi","wbfgmapmc"],
				"type" => "js",
				'enqueue' => false,
				'in_footer' => true,
			];
		}else{
			$libs["wbfgmap"] = [
				"uri" => $res->prefix_url("/assets/dist/js/includes/wbfgmap.min.js"),
				"path" => $res->prefix_path("/assets/dist/js/includes/wbfgmap.min.js"),
				"deps" => ["jquery","gmapapi"],
				"type" => "js",
				'enqueue' => false,
				'in_footer' => true,
			];
		}
		
		$a = new AssetsManager($libs);
		$a->enqueue();
	}

	/**
	 * Register menu item
	 *
	 * @hooked 'admin_menu'
	 */
	public function admin_menu(){
		global $menu;

		//Check if must display the bubble warning
		$updater = $this->services->get_updater();
		if(isset($updater) && property_exists($updater,'optionName')){
			$updates_state = get_option($updater->optionName,null);
		}
		$warning_count = isset($updates_state) && !is_null($updates_state->update) ? 1 : 0;

		$page_title = "WBF";
		$menu_title = apply_filters("wbf/admin_menu/label",'WBF');
		$menu_label = sprintf( __( '%s %s' ), $menu_title, "<span class='update-plugins count-$warning_count' title='".__("Update available","wbf")."'><span class='update-count'>" . number_format_i18n($warning_count) . "</span></span>" );
		$menu_slug = $this->wp_menu_slug;

		$menu['58'] = $menu['59']; //move the separator before "Appearance" one position up
		$icon = apply_filters("wbf/admin_menu/icon","dashicons-text");

		$wbf_menu = add_menu_page( $page_title, $menu_label, "edit_theme_options", $menu_slug, [$this,"options_page"], $icon, 59 );
		do_action("wbf_admin_submenu",$menu_slug);
		$wbf_info_submenu = add_submenu_page($menu_slug,__("WBF Status","wbf"),__("WBF Status","wbf"),"manage_options","wbf_status",[$this,"settings_page"]);
	}

	/**
	 * Unset updates from integrated plugins, ect...
	 *
	 * @hooked 'site_transient_update_plugins'
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function unset_unwanted_updates($value){
		$acf_update_path = preg_replace("/^\//","",$this->get_path().'vendor/acf/acf.php');

		if(isset($value->response[$acf_update_path])){
			unset($value->response[$acf_update_path]);
		}

		return $value;
	}

	/**
	 * Exclude pagebuilder from loading
	 *
	 * @hooked 'wbf/modules/available'
	 *
	 * @param $module_dirs
	 *
	 * @return mixed
	 */
	public function do_not_load_pagebuilder($module_dirs){
		foreach($module_dirs as $k => $dir){
			$module_name = basename($dir);
			if($module_name == "pagebuilder"){
				unset($module_dirs[$k]);
			}
		}

		return $module_dirs;
	}

	/**
	 * Add env notice to the admin bar
	 *
	 * @hooked 'admin_bar_menu' - 1000
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar
	 * @since 0.2.0
	 */
	public function add_env_notice($wp_admin_bar){
		global $post;

		if ( current_user_can( 'manage_options' ) ) {
			$args = array(
				'id'    => 'wbf_env_notice',
				'title' => _x("ENV","WBF Admin Bar","wbf").': '.WBF_ENV,
				'href'  => "#",
				'meta'  => array( 'class' => 'wbf-toolbar-env-notice' )
			);
			$wp_admin_bar->add_node( $args );
		}
	}

	/**
	 * Add a "Compile CSS" button to the toolbar
	 *
	 * @hooked 'admin_bar_menu' - 990
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar
	 * @since 0.1.1
	 */
	public function add_admin_compile_button($wp_admin_bar){
		global $post,$wbf_styles_compiler;

		if(!isset($wbf_styles_compiler) || !$wbf_styles_compiler instanceof Styles_Compiler) return;

		if ( current_user_can( 'manage_options' ) ) {
			$args = array(
				'id'    => 'wbf_compile_styles',
				'title' => 'Compile CSS',
				'href'  => add_query_arg('compile','true'),
				'meta'  => array( 'class' => 'wbf-toolbar-compile-theme-styles-button' )
			);
			$wp_admin_bar->add_node( $args );
		}
	}

	/*
	 *
	 *
	 * ACTIVATION \ DEACTIVATION
	 *
	 *
	 */

	/**
	 * Calls $this->activation() if the option 'wbf_installed' is FALSE
	 *
	 * @param bool $force
	 */
	public function maybe_run_activation($force = false){
		if($force){
			$this->activation();
		}else{
			$opt = get_option( "wbf_installed" );
			if ( ! $opt ) {
				$this->activation();
			}
		}
	}

	/**
	 * Calls $this->add_wbf_options() if the option 'wbf_installed' is FALSE
	 */
	public function maybe_add_option() {
		$opt = get_option( "wbf_installed", false );
		if( ! $opt || !$this->has_valid_wbf_path()) {
			$this->add_wbf_options();
		}
	}

	/**
	 * Adds common WBF options
	 */
	public function add_wbf_options(){
		update_option( "wbf_installed", true ); //Set a flag to make other component able to check if framework is installed

		if(!get_option('wbf_path',false)){
			update_option( "wbf_path", $this->get_path() );
		}

		if(!get_option('wbf_url',false)){
			update_option( "wbf_url", $this->get_url() );
		}

		update_option( "wbf_components_saved_once", false );
	}

	/**
	 * Checks if 'wbf_path' option is a valid path to wbf.php
	 *
	 * @return bool
	 */
	public function has_valid_wbf_path(){
		$path = get_option("wbf_path");
		if(!$path || empty($path) || !is_string($path)){
			return false;
		}
		if(file_exists($path."/wbf.php")){
			return true;
		}
		return false;
	}

	/**
	 * Loads modules activations and setup common options
	 */
	public function activation() {
		$this->load_modules_activation_hooks();

		$this->add_wbf_options();
		do_action("wbf_activated");
		//$this->enable_default_components();
	}

	/**
	 * Remove WBF from the database and calls modules de-activations
	 *
	 * @param null $template
	 */
	public function deactivation($template = null) {
		$this->load_modules_deactivation_hooks();
		delete_option( "wbf_installed" );
		delete_option( "wbf_path" );
		delete_option( "wbf_url" );
		if($template){
			$theme_switched = get_option( 'theme_switched', "" );
			do_action("wbf_deactivated", $theme_switched);
		}else{
			do_action("wbf_deactivated", "plugin");
		}
		/*if(!empty($theme_switched)){
			$wbf_components_saved_once = (array) get_option("wbf_components_saved_once", array());
			if(($key = array_search($theme_switched, $wbf_components_saved_once)) !== false) {
				unset($wbf_components_saved_once[$key]);
			}
			if(empty($wbf_components_saved_once)){
				delete_option( "wbf_components_saved_once" );
			}else{
				update_option( "wbf_components_saved_once", $wbf_components_saved_once );
			}
		}*/
	}

	/**
	 * Placeholder callback
	 */
	public function options_page(){
		if(has_action("wbf/theme_options/register") || has_filter("wbf/modules/options/available") || has_action("wbf/modules/behaviors/available")){
			return; //if we have theme options, do not display the default page.
		}
		$v = new HTMLView("src/views/admin/default-page.php","wbf");
		$v->for_dashboard()->display([
			'page_title' => __("Welcome to WBF!")
		]);
	}

	/**
	 * WBF options page (for now it just display info about WBF current status)
	 */
	public function settings_page() {
		$v = new HTMLView("src/views/admin/settings.php","wbf");

		$data = [
			'engine_info' => [
				'title' => _x("Engine information","Setting page","wbf"),
				'data' => [
					'version' => [
						'name' => _x("Version","Setting Page","wbf"),
						'value' => get_plugin_data(dirname(dirname(__FILE__))."/wbf.php")['Version'],
					],
					'path' => [
						'name' => _x("Pathname","Setting Page","wbf"),
						'value' => $this->get_path()
					],
					'url' => [
						'name' => _x("URL","Setting Page","wbf"),
						'value' => $this->get_url()
					],
					'wd' => [
						'name' => _x("Working directory","Setting Page","wbf"),
						'value' => $this->get_working_directory()
					],
					'startup_options' => [
						'name' => _x("Startup options","Setting page"."wbf"),
						'value' => $this->options
					]
				]
			],
			'modules' => [
				'title' => _x("Loaded Modules","Setting page","wbf"),
				'data' => $this->modules
			],
			'extensions' => [
				'title' => _x("Loaded Extensions","Setting page","wbf"),
				'data' => $this->get_extensions()
			],
			'plugins' => [
				'title' => _x("Loaded Plugins","Settings page","wbf"),
				'data' => call_user_func(function(){
					$loaded_plugins = BasePlugin::get_loaded_plugins();
					$plugins = [];
					if(!empty($loaded_plugins)){
						foreach($loaded_plugins as $slug => $plugin){
							$plugins[$slug] = [
								'name' => $plugin->get_plugin_name(),
								'value' => [
									'version' => $plugin->get_version(),
									'templates' => $plugin instanceof TemplatePlugin ? $plugin->get_registered_templates() : [],
								]
							];
						}
					}
					return $plugins;
				})
			]
		];

		$v->display([
			'page_title' => __("WBF Status"),
			'sections' => $data,
			'force_plugin_update_link' => admin_url()."/update-core.php?force_wbf_plugin_update_check=1"
		]);
	}

	/*
	 * SERVICES
	 */

	/**
	 * @return ServiceManager
	 */
	public function get_service_manager(){
		return $this->services;
	}

	/**
	 * @return ServiceManager
	 */
	public function services(){
		return $this->get_service_manager();
	}

	/**
	 * Get the requested service
	 *
	 * @param $service_name
	 *
	 * @return \Mobile_Detect|Styles_Compiler|Plugin_Update_Checker|Notice_Manager
	 * @throws \Exception
	 */
	public function get_service($service_name){
		switch($service_name){
			case 'notices_manager':
				return $this->services->get_notice_manager();
				break;
			case 'styles_compiler':
				return $this->services->get_styles_compiler();
				break;
			case 'updater':
				return $this->services->get_updater();
				break;
			case 'mobile_detect':
				return $this->services->get_mobile_detect();
				break;
			default:
				throw new \Exception('Service '.$service_name.' not available');
				break;
		}
	}

	/**
	 * Get Mobile detect class
	 *
	 * @return \Mobile_Detect
	 */
	public function get_mobile_detect() {
		if ( ! $this->services->get_mobile_detect() instanceof \Mobile_Detect ) {
			$this->services->set_mobile_detect(new \Mobile_Detect());
			$this->services->get_mobile_detect()->setDetectionType( 'extended' );
		}
		return $this->services->get_mobile_detect();
	}

	/**
	 * Initialize the style compiler as global variable
	 *
	 * @param $args
	 * @param null|Base_Compiler $base_compiler
	 */
	public function set_styles_compiler($args,$base_compiler = null){
		global $wbf_styles_compiler;

		if(!isset($wbf_styles_compiler) || !$wbf_styles_compiler){
			if(!isset($base_compiler)){
				$base_compiler = new Less_Compiler($args);
			}
			$wbf_styles_compiler = new components\compiler\Styles_Compiler($base_compiler);
		}
		$this->services->set_styles_compiler($wbf_styles_compiler);
		$this->services->get_styles_compiler()->listen_requests();
	}

	/**
	 * Provides backward compatibility for some proprieties
	 *
	 * @param $name
	 *
	 * @return null
	 */
	public function __get($name) {
		$available_properties = [
			'resources','notice_manager','Styles_Compiler'
		];

		if (in_array($name, $available_properties)) {
			switch ($name){
				case 'resources':
					require_once __DIR__.'/legacy/Resources.php';
					return new Resources();
					break;
				case 'notice_manager':
					$this->services->get_notice_manager();
					break;
				case 'Styles_Compiler':
					$this->services->get_styles_compiler();
					break;
			}
		}

		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via __get(): ' . $name .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_NOTICE);
		return null;
	}
}