<?php
namespace WBF\modules\options;

use WBF\components\mvc\HTMLView;
use WBF\components\utils\Utilities;

class Admin{

	var $wp_menu_slug = "themeoptions-manager";

	public function init() {
		$all_options = Framework::get_registered_options();

		// Add the required scripts and styles
		add_filter( 'wbf/js/admin/deps', function($deps){
			$deps[] = "wp-color-picker";
			return $deps;
		});

		// Checks if options are available
		if ( $all_options ) {
			// Add the options page and menu item.
			add_action( 'admin_menu', array( $this, 'add_options_page' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );

			// Settings need to be registered after admin_init
			add_action( 'toplevel_page_wbf_options', array( $this, 'process_options_save' ) );
			add_action( 'admin_init', array( $this, 'settings_init' ) );

			// Adds options menu to the admin bar
			add_action( 'wp_before_admin_bar_render', array( $this, 'optionsframework_admin_bar' ) );
		}

		remove_action( 'admin_menu', array( $this, 'add_options_page' ) );
		if(is_array($all_options) && !empty($all_options)){
			add_action( 'wbf_admin_submenu', array( $this, 'add_options_page' ) );
			add_action( 'wbf_admin_submenu', array( $this, 'add_man_page' ), 12 );
			//add_action( 'admin_menu', array( $this, 'add_additional_appearance_link' ) );
		}
		add_action( 'optionsframework_after', array( $this, 'add_copy_in_admin_page' ));
	}

    /*function add_additional_appearance_link(){
        $menu = $this->menu_settings();
        $this->of_app_screen = add_theme_page($menu['page_title'],$menu['menu_title'],$menu['capability'],$menu['menu_slug']);
    }*/

	/**
	 * Process the options save\reset action.
	 *
	 * @hooked 'admin_init'
	 */
	function process_options_save(){
		if(!self::is_options_page()) return;
		/*
		 * Restore Defaults.
		 */
		if(isset($_POST['restore_theme_options'])){
			do_action("wbf/modules/options/pre_restore");
			$options_to_save = $this->get_default_values();
			$options_to_save = apply_filters("wbf/modules/options/after_restore",$options_to_save);
		}elseif(isset($_POST['reset_theme_options'])){
			do_action("wbf/modules/options/pre_reset");
			Framework::reset_theme_options();
			$options_to_save = apply_filters("wbf/modules/options/after_reset",[]);
		}elseif(isset($_POST['update_theme_options'])){
			/*
			 * Save options
			 */
			$root_id = Framework::get_options_root_id();
			if(isset($_POST[$root_id])){
				$options_to_save = $_POST[$root_id];
			}
		}
		if(isset($options_to_save) && is_array($options_to_save) && !empty($options_to_save)){
			$validation_base = apply_filters("wbf/modules/options/pre_save/validation_base",false); 
			$r = Framework::update_theme_options($options_to_save,true,$validation_base);
			if( ($r && isset($_POST['update_theme_options'])) || (!$r && isset($_POST['update_theme_options'])) ){ //$r is FALSE when no options was changed
				Utilities::admin_show_message(__( 'Options saved successfully!', 'wbf' ),"updated");
			}elseif($r && isset($_POST['restore_theme_options'])){
				Utilities::admin_show_message(__( 'Default options restored.', 'wbf' ),"updated");
			}elseif($r && isset($_POST['reset_theme_options'])){
				Utilities::admin_show_message(__( 'Theme options has been cleared and restored to default values.', 'wbf' ),"updated");
			}else{
				Utilities::admin_show_message(__( 'There was an error during options saving.', 'wbf' ),"error");
			}
		}else{
			if(isset($_POST['reset_theme_options'])){
				Utilities::admin_show_message(__( 'Theme options cleared successfully!', 'wbf' ),"success");
			}
		}
	}

	/**
	 * Registers the settings
	 *
	 * @legacy
	 *
	 * @since 1.7.0
	 */
	function settings_init() {
		// Registers the settings fields and callback
		//register_setting( 'optionsframework', Framework::get_options_root_id(),  [ $this, 'validate_options' ] );

		// Displays notice after options save
		//add_action( 'wbf/modules/options/after_validate', array( $this, 'save_options_notice' ) );
	}

	/**
	 * Add a subpage called "Theme Options" to the Waboot Menu
	 */
	function add_options_page() {
		$menu = $this->menu_settings();
		$this->options_screen = WBF()->add_submenu_page( $menu['page_title'], $menu['menu_title'], $menu['capability'], $menu['menu_slug'], array($this, 'display_options_page') );
	}

	/**
	 * Add "Manage Theme Options" subpage to WBF Menu
	 */
	public function add_man_page($parent_slug) {
		WBF()->add_submenu_page(__( "Theme Options Manager", "wbf" ), __( "Import/Export", "wbf" ), "edit_theme_options", $this->wp_menu_slug, array( $this, 'display_manage_options_page') );
	}

	function add_copy_in_admin_page(){
		WBF()->print_copyright();
	}

	static function menu_settings() {
		$menu = array(
			'page_title' => __('Theme Options', 'wbf'),
			'menu_title' => __('Theme Options', 'wbf'),
			'capability' => 'edit_theme_options',
			'old_menu_slug' => 'options-framework',
			'menu_slug' => WBF()->wp_menu_slug
		);
		return apply_filters('optionsframework_menu', $menu);
	}

	/**
	 * Adds options menu item to admin bar
	 */
	function optionsframework_admin_bar() {
		if( current_user_can('edit_theme_options') ){
			global $wp_admin_bar;
			$menu = $this->menu_settings();
			if(current_user_can($menu['capability'])){
				$wp_admin_bar->add_menu( array(
					'id' => 'of_theme_options',
					'title' => $menu['menu_title'],
					'parent' => 'appearance',
					'href' => admin_url( 'admin.php?page=' . $menu['menu_slug'] ),
					'meta' => [
						'title' => _x("Edit theme options","Admin bar","wbf")
					]
				));
			}
		}
	}

	/**
	 * Builds out the theme options manager page.
	 */
	public function display_manage_options_page() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		if ( isset( $_POST['submit-backup'] ) ) {
			switch ( $_POST['option'] ) {
				case 'backup':
					try {
						$file = $this->backup_options_to_file();
						Utilities::admin_show_message( __( "Backup successfully created!", "wbf" ), "updated" );
					} catch ( \Exception $e ) {
						Utilities::admin_show_message( $e->getMessage(), "error" );
					}
					break;
				default:
					Utilities::admin_show_message( __( "Invalid option selected", "wbf" ), "error" );
					break;
			}
		}

		if ( isset( $_POST['submit-restore'] ) ) {
			if ( isset( $_FILES['remote-backup-file'] ) && $_FILES['remote-backup-file']['tmp_name'] != "" ) {
				$file = $_FILES['remote-backup-file'];
				if ( $file['error'] == UPLOAD_ERR_OK && is_uploaded_file( $file['tmp_name'] ) ) {
					try {
						$this->restore_options_from_file( $file );
						Utilities::admin_show_message( __( "Backup successfully restored!", "wbf" ), "updated" );
					} catch ( \Exception $e ) {
						Utilities::admin_show_message( $e->getMessage(), "error" );
					}
				} else {
					Utilities::admin_show_message( __( "Unable to upload the file.", "wbf" ), "error" );
				}
			} elseif ( isset( $_POST['local-backup-file'] ) ) {
				$file = $_POST['local-backup-file'];
				try {
					$this->restore_options_from_file( $file );
					Utilities::admin_show_message( __( "Backup successfully restored!", "wbf" ), "updated" );
				} catch ( \Exception $e ) {
					Utilities::admin_show_message( $e->getMessage(), "error" );
				}
			} else {
				Utilities::admin_show_message( __( "No backup file provided.", "wbf" ), "error" );
			}
		}

		$backup_files = $this->get_backupFiles();

		$v = new HTMLView("src/modules/options/views/admin/manage-options-page.php","wbf");
		$v->for_dashboard()->display([
			'page_title' => __( "Theme Options Manager", "wbf" ),
			'backup_files' => $backup_files,
			'wp_menu_slug' => $this->wp_menu_slug
		]);
	}

	/**
	 * Backup current theme options to a file. Return the file url or throws Exception on fail.
	 *
	 * @param string|null $filename
	 * @param null $theme
	 * @param bool $download
	 *
	 * @return bool|string
	 * @throws \Exception
	 */
	public function backup_options_to_file( $filename = null, $theme = null, $download = false ) {
		if(!isset($theme)){
			$current_settings = Framework::get_saved_options();
			$backup_path = WBF()->get_working_directory() . "/theme-options-backups";
			$backup_url = WBF()->get_working_directory_uri() . "/theme-options-backups";
		}else{
			$current_settings = Framework::get_saved_options($theme);
			$backup_path = WBF()->get_working_directory_of($theme) . "/theme-options-backups";
			$backup_url = WBF()->get_working_directory_uri_of($theme) . "/theme-options-backups";
		}
		if ( ! is_dir( $backup_path ) ) {
			mkdir( $backup_path );
		}

		if(!isset($filename) || !\is_string($filename)){
			$date = date( 'Y-m-d-His' );
			$backup_filename = Framework::get_options_root_id() . "-" . $date . ".options";
		}else{
			$backup_filename = $filename;
		}

		if ( ! file_put_contents( $backup_path . "/" . $backup_filename, base64_encode( json_encode( $current_settings ) ) ) ) {
			throw new \Exception( __( "Unable to create the backup file: " . $backup_path . "/" . $backup_filename ) );
		}

		if ( $download ) { //Not used ATM
			header( 'Content-type: text/plain' );
			header( 'Content-Disposition: attachment; filename="' . $backup_filename . '"' );
			readfile( $backup_path . "/" . $backup_filename );
		}

		return $backup_url . "/" . $backup_filename;
	}

	/**
	 * Read a file and restore the settings stored in it (if valid)
	 *
	 * @param array|string $file this can be a file uploaded through a form (the whole array) or a path to a file
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function restore_options_from_file( $file ) {
		$optionsframework_settings = Framework::get_options_root_id();
		$settings                  = array();

		if ( is_array( $file ) ) {
			//we have an uploaded file
			if ( isset( $file['tmp_name'] ) && is_uploaded_file( $file['tmp_name'] ) ) {
				$settings = $this->get_backup_file_content( $file['tmp_name'] );
			} else {
				throw new \Exception( __( "Invalid backup file provided", "wbf" ) );
			}
		} else {
			//we have a file on disk
			if ( is_file( $file ) ) {
				$settings = $this->get_backup_file_content( $file );
			} else {
				throw new \Exception( __( "Invalid backup file provided", "wbf" ) );
			}
		}
		//Restore the settings
		if ( $settings && ! empty( $settings ) ) {
			if ( ! update_option( $optionsframework_settings, $settings ) ) {
				throw new \Exception( __( "The backup file and the current settings are identical", "wbf" ) );
			}
		} else {
			throw new \Exception( __( "Invalid backup file provided", "wbf" ) );
		}

		return true;
	}

	/**
	 * Read a backup file content. Returns FALSE if the file is not valid.
	 *
	 * @param string $filepath
	 *
	 * @return array|bool
	 */
	private function get_backup_file_content( $filepath ) {
		if ( ! is_file( $filepath ) ) {
			return false;
		}

		$content  = file_get_contents( $filepath );
		$settings = json_decode( base64_decode( $content ), true );

		if ( ! is_array( $settings ) ) {
			return false;
		}

		return $settings;
	}

	/**
	 * Returns an array with all backup files
	 * @return array
	 */
	public function get_backupFiles() {
		$backup_path = WBF()->get_working_directory() . "/theme-options-backups";
		$files       = glob( $backup_path . "/*.options" );
		$output      = array();

		if ( is_array( $files ) ) {
			foreach ( $files as $f ) {
				$info     = pathinfo( $f );
				$output[] = array(
					'path' => $f,
					'url'  => WBF()->get_working_directory_uri() . "/theme-options-backups/" . $info['basename'],
					'name' => $info['basename']
				);
			}
		}

		return $output;
	}

    /**
     * Loads the required stylesheets
     *
     * @legacy
     *
     * @since 1.7.0
     */
    function enqueue_admin_styles() {
        if(!Admin::is_options_page()) return;
        wp_enqueue_style( 'wp-color-picker' );
    }

    /**
     * Builds out the options panel.
     *
     * If we were using the Settings API as it was intended we would use
     * do_settings_sections here.  But as we don't want the settings wrapped in a table,
     * we'll call our own custom optionsframework_fields.  See options-interface.php
     * for specifics on how each individual field is generated.
     *
     * Nonces are provided using the settings_fields()
     *
     * @since 1.7.0
     */
    function display_options_page() {
		$v = new HTMLView("src/modules/options/views/admin/options-page.php","wbf");
		$v->clean()->display([
			'page_title' => _x("Theme Options","Theme Options page title","wbf"),
			'menu' => $this->menu_settings(),
			'tabs' => GUI::get_tabs(),
		]);
    }

	/**
	 * Check if we are in the Theme Options page
	 *
	 * @return bool
	 */
	static public function is_options_page(){
		if(function_exists("get_current_screen")){
			$screen = get_current_screen();
		}else{
			$screen = false;
		}
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : false;
		return isset($screen->id) && $screen->id == "toplevel_page_wbf_options" && $page == "wbf_options";
	}

	/**
	 * Validate Options.
	 *
	 * This runs after the submit/reset button has been clicked and validates the inputs.
	 *
	 * @param array $options_to_validate
	 *
	 * @param bool|array $base
	 *
	 * @return array
	 */
	static function validate_options( $options_to_validate, $base = false ) {
		$clean = array();

		$options = ! $base ? Framework::get_registered_options() : $base;

		/*
		 * Cycle through all possible options (not the saved ones)
		 */
		foreach($options as $option){
			
			if(!isset($option['id']) || !isset($option['type']) || $option['type'] == "heading"){
				continue;
			}

			$id = Framework::sanitize_option_id($option['id']);

			if(array_key_exists($id,$options_to_validate)){
				if( has_filter( 'of_sanitize_' . $option['type'] ) ) {
					$sanitized_value = apply_filters( 'of_sanitize_' . $option['type'], $options_to_validate[$id], $option );
					$clean[$id] = $sanitized_value;
				}
				//NOTE: if no sanitize filter is provided at this point, the option value is lost.
			}else{
				//Checkboxes is not set when unchecked...
				switch($option['type']){
					case "checkbox":
						if(!isset($options_to_validate[$id])){
							// Set checkbox to false if it wasn't sent in the $_POST
							$clean[$id] = false;
						}
						break;
					case "multicheck":
						if(!isset($options_to_validate[$id])){
							// Set each item in the multicheck to false if it wasn't sent in the $_POST
							foreach($option['options'] as $key => $value ) {
								$clean[$id][$key] = false;
							}
						}
						break;
				}
			}
		}

		// Hook to run after validation
		do_action( 'wbf/modules/options/after_validate', $clean );

		return $clean;
	}

	/**
	 * Get the default values for all the theme options
	 *
	 * Get an array of all default values as set in
	 * options.php. The 'id','std' and 'type' keys need
	 * to be defined in the configuration array. In the
	 * event that these keys are not present the option
	 * will not be included in this function's output.
	 *
	 * @return array Re-keyed options configuration array.
	 *
	 */
	function get_default_values() {
		return Framework::get_default_values();
	}

	/**
	 * Display message when options have been saved
	 *
	 * @hooked 'optionsframework_after_validate'
	 *
	 * @legacy
	 */
	function save_options_notice(){
		add_settings_error('options-framework', 'save_options', __('Options saved.', 'textdomain'), 'updated fade');
	}
}