<?php

namespace WBF;

class PluginCore {

	/**
	 * @var array
	 */
	var $options;

	/**
	 * @var \WBF\includes\Plugin_Update_Checker
	 */
	var $update_instance;

	/**
	 * @var array
	 */
	var $modules;

	/**
	 * @var string
	 */
	var $url;

	/**
	 * @var string
	 */
	var $path;

	/**
	 * @var \Mobile_Detect
	 */
	var $mobile_detect;

	/**
	 * @var \WBF\admin\Notice_Manager
	 */
	var $notice_manager;

	/**
	 * @var \WBF\includes\Resources
	 */
	var $resources;

	/**
	 * @var
	 */
	var $wp_menu_slug;

	/**
	 * @var string
	 */
	const version = "0.13.13";

	/**
	 * Return a new instance of WBF
	 *
	 * @param array $args options that will be used in startup
	 *
	 * @return WBF
	 */
	public static function getInstance($args = []){
		static $instance = null;
		if (null === $instance) {
			$instance = new static($args);
		}

		return $instance;
	}

	/**
	 * WBF constructor.
	 *
	 * @param array $args options that will be used in startup
	 */
	protected function __construct($args = []){
		if(!defined("WBF_PREVENT_STARTUP")){
			if($this->is_plugin()){
				$this->startup($args);
			}
		}
	}
	
	/**
	 * WBF Startup. Adds filters and actions.
	 *
	 * @param array $options
	 */
	function startup($options = []){
		//Check options
		$options = wp_parse_args($options,[
			'do_global_theme_customizations' => true,
			'check_for_updates' => true,
			'handle_errors' => true
		]);
		$this->options = $options;

		if($this->options['handle_errors']){
			set_error_handler([$this,"handle_errors"],E_USER_WARNING); //http://php.net/manual/en/language.types.callable.php
		}

		$this->maybe_run_activation();
		$this->maybe_add_option();

		$this->resources = \WBF\includes\Resources::getInstance();
		$this->url = $this->resources->get_url();
		$this->path = $this->resources->get_path();

		$this->resources->maybe_add_work_directory();

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
		add_action( 'admin_enqueue_scripts', [$this,"register_libs"] );

		/*
		 * |- Main Actions: END
		 */

		//Setup admin menu:
		add_action( 'admin_menu', [$this,"admin_menu"], 11 );
		add_action( 'admin_bar_menu', [$this,"add_env_notice"], 1000 );
		add_action( 'admin_bar_menu', [$this,"add_admin_compile_button"], 990 );

		//Init License Manager: //todo: move to plugin framweork?
		if(class_exists('\WBF\admin\License_Manager')){
			\WBF\admin\License_Manager::init();
		}

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
	 * @param $errno
	 * @param $errstr
	 * @param $errfile
	 * @param $errline
	 * @param $errcontext
	 */
	public function handle_errors($errno,$errstr,$errfile,$errline,$errcontext){
		global $wbf_notice_manager;
		if($wbf_notice_manager && is_admin() && current_user_can("manage_options")){
			$str = sprintf('[Admin Only] There was an USER_WARNING error generated at %s:%s: <strong>%s</strong>',basename($errfile),$errline,$errstr);
			$wbf_notice_manager->add_notice($errline,$str,"error","_flash_");
		}
	}

	/**
	 * Get Mobile detect class
	 *
	 * @return \Mobile_Detect
	 */
	public function get_mobile_detect() {
		if ( ! $this->mobile_detect instanceof \Mobile_Detect ) {
			$this->mobile_detect = new \Mobile_Detect();
			$this->mobile_detect->setDetectionType( 'extended' );
		}
		return $this->mobile_detect;
	}

	/**
	 * Initialize the style compiler as global variable
	 *
	 * @param $args
	 * @param null $base_compiler
	 */
	static function set_styles_compiler($args,$base_compiler = null){
		global $wbf_styles_compiler;
		if(!isset($wbf_styles_compiler) || !$wbf_styles_compiler){
			$GLOBALS['wbf_styles_compiler'] = new \WBF\includes\compiler\Styles_Compiler($args,$base_compiler);
		}
	}

	/**
	 * Checks if current admin page is part of WBF
	 * @return bool
	 */
	static function is_wbf_admin_page(){
		global $plugin_page,$wbf_options_framework;

		//todo: this is ugly, we could expose a function like register_wbf_admin_page()
		$valid_pages = [
			self::getInstance()->wp_menu_slug,
			\WBF\modules\components\ComponentsManager::$wp_menu_slug,
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
	static function get_copyright(){
		$v = new \WBF\includes\mvc\HTMLView("views/admin/copyright.php","wbf");

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
	static function print_copyright(){
		echo self::get_copyright();
	}

	/**
	 * Checks if $module_name is loaded
	 *
	 * @param $module_name
	 *
	 * @return bool
	 */
	static function module_is_loaded($module_name){
		$modules = self::get_modules();
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
	static function get_modules($include = false){
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

		$modules_dir = self::get_path()."modules";
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
	static function get_extensions($include = false){
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

		$exts_dir = self::get_path()."extensions";
		$dirs = array_filter(glob($exts_dir."/*"), 'is_dir');
		$dirs = apply_filters("wbf/extensions/available", $dirs); //Allow developers to add\delete extensions
		foreach($dirs as $d){
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
	 * @hooked 'after_setup_theme'
	 *
	 * @return mixed
	 */
	function load_modules(){
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
	function load_extensions(){
		return $this->get_extensions(true);
	}

	/**
	 * Init modules activations procedures
	 */
	function load_modules_activation_hooks(){
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
	function load_modules_deactivation_hooks(){
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
	static function is_plugin(){
		$path = self::get_path();
		if(preg_match("/plugins/",$path)){
			$is_plugin = true;
		}else{
			$is_plugin = false;
		}
		return apply_filters("wbf/is_plugin",$is_plugin);
	}

	/*
	 *
	 *
	 * PATHS AND RESOURCES (deprecated)
	 *
	 *
	 */

	/**
	 * Returns WBF url or FALSE
	 *
	 * @deprecated
	 *
	 * @return bool|string
	 */
	static function get_url(){
		return \WBF\includes\Resources::getInstance()->get_url();
	}

	/**
	 * Returns WBF path or FALSE
	 *
	 * @deprecated
	 *
	 * @return bool|string
	 */
	static function get_path(){
		return \WBF\includes\Resources::getInstance()->get_path();
	}

	/**
	 * Gets WBF admin assets uri
	 *
	 * @deprecated
	 *
	 * @return bool|string
	 */
	static function get_admin_assets_uri(){
		return \WBF\includes\Resources::getInstance()->get_admin_assets_uri();
	}

	/**
	 * Gets WBF assets uri
	 * @param bool $admin_assets_flag
	 *
	 * @deprecated
	 *
	 * @return bool|string
	 */
	static function get_assets_uri($admin_assets_flag = false){
		return \WBF\includes\Resources::getInstance()->get_assets_uri($admin_assets_flag);
	}

	/**
	 * Returns WBF Theme dir
	 *
	 * @deprecated
	 *
	 * @return bool|string
	 */
	static function get_theme_dir(){
		return \WBF\includes\Resources::getInstance()->get_theme_dir();
	}

	/**
	 * Prefix $to with the WBF URL
	 * @param $to
	 *
	 * @deprecated
	 *
	 * @return bool|string
	 */
	static function prefix_url($to){
		return \WBF\includes\Resources::getInstance()->prefix_url($to);
	}

	/**
	 * Prefix $to with the WBF PATH
	 * @param $to
	 *
	 * @deprecated
	 *
	 * @return bool|string
	 */
	static function prefix_path($to){
		return \WBF\includes\Resources::getInstance()->prefix_path($to);
	}

	/*
	 *
	 *
	 * BACKUP FUNCTIONS
	 *
	 *
	 */

	static function get_behavior( $name, $post_id = 0, $return = "value" ) {
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

	function do_global_theme_customizations(){
		// Global Customization
		wbf_locate_file( '/public/theme-customs.php', true );

		// Email encoder
		wbf_locate_file( '/public/email-encoder.php', true );
	}

	/**
	 * Wordpress "plugins_loaded" callback
	 */
	function plugins_loaded(){
		// Load extensions
		$this->load_extensions();
	}

	/**
	 * Wordpress "after_setup_theme" callback
	 */
	function after_setup_theme() {
		global $wbf_notice_manager;

		$this->options = apply_filters("wbf/options",$this->options);

		$this->wp_menu_slug = "wbf_options";

		$this->modules = $this->load_modules();

		do_action("wbf_after_setup_theme");

		// Make framework available for translation.
		load_textdomain( 'wbf', self::get_path() . 'languages/wbf-'.get_locale().".mo");

		if(!isset($wbf_notice_manager)){
			$GLOBALS['wbf_notice_manager'] = new \WBF\admin\Notice_Manager(); // Loads notice manager. The notice manager can be already loaded by plugins constructor prior this point.
			$this->notice_manager = &$GLOBALS['wbf_notice_manager'];
		}

		// Load the CSS
		wbf_locate_file( '/public/public-styles.php', true );
		wbf_locate_file( '/admin/adm-styles.php', true );

		// Load scripts
		//locate_template( '/wbf/public/scripts.php', true );
		wbf_locate_file( '/admin/adm-scripts.php', true );

		if($this->options['do_global_theme_customizations']){
			$this->do_global_theme_customizations();
		}

		// ACF INTEGRATION
		if(!self::is_plugin()){
			$this->load_extensions();
		}

		// Google Fonts
		wbf_locate_file('/includes/google-fonts-retriever.php', true);
		if(class_exists("WBF\\GoogleFontsRetriever")) $GLOBALS['wbf_gfont_fetcher'] = \WBF\GoogleFontsRetriever::getInstance();
	}

	/**
	 * Wordpress "init" callback
	 */
	function init() {
		do_action("wbf_init");

		if($this->options['check_for_updates']){
			//Set update server
			if(self::is_plugin()){
				$this->update_instance = new \WBF\includes\Plugin_Update_Checker(
					"http://update.waboot.org/?action=get_metadata&slug=wbf&type=plugin", //$metadataUrl
					self::get_path()."wbf.php", //$pluginFile
					"wbf", //$slug
					null, //$plugin_license
					false, //$checkLicense
					12, //$checkPeriod
					'wbf_updates', //$optionName
					is_multisite() //$muPluginFile
				);
			}
		}

		// Breadcrumbs
		if(!class_exists("Breadcrumb_Trail") && !function_exists("breadcrumb_trail")){
			wbf_locate_file( '/vendor/breadcrumb-trail.php', true);
			wbf_locate_file( '/public/breadcrumb-trail.php', true );
		}

		if(function_exists('\WBF\modules\options\of_check_options_deps')) \WBF\modules\options\of_check_options_deps(); //Check if theme options dependencies are met
		$GLOBALS['wbf_notice_manager']->enqueue_notices(); //Display notices
	}

	/**
	 * Register libraries used by WBF ecosystem
	 */
	function register_libs(){
		/*
		 * STYLES
		 */
		wp_register_style("jquery-ui-style","//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css",array(),false,"all");
		wp_register_style("owlcarousel-css",WBF_URL."/vendor/owlcarousel/assets/owl.carousel.css");
		/*
		 * SCRIPTS
		 */
		wp_register_script('gmapapi', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places', array('jquery'), false, false );
		if(defined("WBF_ENV") && WBF_ENV == "dev"){
			wp_register_script("wbfgmapmc",WBF_URL."/assets/src/js/includes/wbfgmap/markerclusterer.js",array("jquery","gmapapi"),false,true);
			wp_register_script("wbfgmap",WBF_URL."/assets/src/js/includes/wbfgmap/acfmap.js",array("jquery","gmapapi","wbfgmapmc"),false,true);
		}else{
			wp_register_script("wbfgmap",WBF_URL."/includes/scripts/wbfgmap.min.js",array("jquery","gmapapi"),false,true);
		}
		wp_register_script("imagesLoaded-js",WBF_URL."/vendor/imagesloaded/imagesloaded.pkgd.min.js",[],false,true);
		wp_register_script("owlcarousel-js",WBF_URL."/vendor/owlcarousel/owl.carousel.min.js",array("jquery"),false,true);
	}

	/**
	 * Register menu item
	 *
	 * @hooked 'admin_menu'
	 */
	function admin_menu(){
		global $menu,$options_framework_admin,$WBFThemeUpdateChecker;

		//Check if must display the bubble warning
		if(isset($WBFThemeUpdateChecker))
			$updates_state = get_option($WBFThemeUpdateChecker->optionName,null);

		if(isset($updates_state) && !is_null($updates_state->update))
			$warning_count = 1;
		else
			$warning_count = 0;

		$page_title = "WBF";
		$menu_title = apply_filters("wbf/admin_menu/label",'WBF');
		$menu_label = sprintf( __( '%s %s' ), $menu_title, "<span class='update-plugins count-$warning_count' title='".__("Update available","wbf")."'><span class='update-count'>" . number_format_i18n($warning_count) . "</span></span>" );
		$menu_slug = $this->wp_menu_slug;

		$menu['58'] = $menu['59']; //move the separator before "Appearance" one position up
		$waboot_menu = add_menu_page( $page_title, $menu_label, "edit_theme_options", $menu_slug, "WBF::options_page", "dashicons-text", 59 );
		//$waboot_options = add_submenu_page( "waboot_options", __( "Theme options", "waboot" ), __( "Theme Options", "waboot" ), "edit_theme_options", "waboot_options", array($options_framework_admin,"options_page") );
		do_action("wbf_admin_submenu","wbf_options");
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
	function unset_unwanted_updates($value){
		$acf_update_path = preg_replace("/^\//","",self::get_path().'vendor/acf/acf.php');

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
	function do_not_load_pagebuilder($module_dirs){
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
	 * @param $wp_admin_bar
	 * @since 0.2.0
	 */
	function add_env_notice($wp_admin_bar){
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
	 * Add a "Compile Less" button to the toolbar
	 *
	 * @hooked 'admin_bar_menu' - 990
	 *
	 * @param $wp_admin_bar
	 * @since 0.1.1
	 */
	function add_admin_compile_button($wp_admin_bar){
		global $post;

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

	function maybe_run_activation($force = false){
		if($force){
			$this->activation();
		}else{
			$opt = get_option( "wbf_installed" );
			if ( ! $opt ) {
				$this->activation();
			}
		}
	}

	function maybe_add_option() {
		$opt = get_option( "wbf_installed" );
		if( ! $opt || !self::has_valid_wbf_path()) {
			$this->add_wbf_options();
		}else{
			if(WBF_DIRECTORY != get_option("wbf_path")){
				//This case may fire when switch from a wbf-as-plugin to a wbf-in-theme environment @since 0.13.8
				$this->add_wbf_options();
			}
		}
	}

	function add_wbf_options(){
		update_option( "wbf_installed", true ); //Set a flag to make other component able to check if framework is installed
		update_option( "wbf_path", WBF_DIRECTORY );
		update_option( "wbf_url", WBF_URL );
		update_option( "wbf_components_saved_once", false );
	}

	function has_valid_wbf_path(){
		$path = get_option("wbf_path");
		if(!$path || empty($path) || !is_string($path)){
			return false;
		}
		if(file_exists($path."/wbf.php")){
			return true;
		}
		return false;
	}

	function activation() {
		$this->load_modules_activation_hooks();

		$this->add_wbf_options();
		do_action("wbf_activated");
		//self::enable_default_components();
	}

	function deactivation($template = null) {
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
	 * Waboot options page for further uses
	 */
	static function options_page() {
		/*$options_framework_admin = new Waboot_Options_Framework_Admin;
		$options_framework_admin->options_page();*/
		return true;
		?>
		<div class="wrap">
			<h2><?php _e( "Waboot Options", "wbf" ); ?></h2>

			<p>
				--- Placeholder ---
			</p>
		</div>
		<?php
	}
}