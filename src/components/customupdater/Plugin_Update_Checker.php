<?php

namespace WBF\components\customupdater;

use WBF\components\license\License;
use WBF\components\notices\Notice_Manager;
use WBF\components\utils\Utilities;

if(is_file(dirname(__FILE__)."/vendor/autoload.php")){
	require_once "vendor/autoload.php";
}

class Plugin_Update_Checker extends \PluginUpdateChecker{

	/**
	 * @var bool
	 */
	var $checkLicense;

	/**
	 * @var License
	 */
	var $plugin_license;

	/**
	 * @var Notice_Manager
	 */
	var $notice_manager;

	/**
	 * @var string
	 */
	var $plugin_slug;

	/**
	 * Class constructor.
	 *
	 * @param string $metadataUrl The URL of the plugin's metadata file.
	 * @param string $pluginFile Fully qualified path to the main plugin file.
	 * @param string $slug The plugin's 'slug'. If not specified, the filename part of $pluginFile sans '.php' will be used as the slug.
	 * @param int $plugin_license
	 * @param bool $checkLicense
	 * @param integer $checkPeriod How often to check for updates (in hours). Defaults to checking every 12 hours. Set to 0 to disable automatic update checks.
	 * @param string $optionName Where to store book-keeping info about update checks. Defaults to 'external_updates-$slug'.
	 * @param string $muPluginFile Optional. The plugin filename relative to the mu-plugins directory.
	 */
	public function __construct($metadataUrl, $pluginFile, $slug = '', $plugin_license = null, $checkLicense = false, $checkPeriod = 12, $optionName = '', $muPluginFile = ''){
		$this->checkLicense = true;
		$this->plugin_license = $plugin_license;
		$this->plugin_slug = $slug;
		//Load Notice Manager if needed
		$wbf_notice_manager = Utilities::get_wbf_notice_manager();
		$this->notice_manager = &$wbf_notice_manager;
		parent::__construct($metadataUrl,$pluginFile,$slug,$checkPeriod,$optionName,$muPluginFile);
	}

	/**
	 * Install the hooks required to run periodic update checks and inject update info
	 * into WP data structures.
	 *
	 * @return void
	 */
	protected function installHooks() {
		if($this->checkLicense){
			if($this->plugin_license && $this->plugin_license->is_valid() || !$this->plugin_license){
				parent::installHooks();
				$this->remove_from_not_upgradable_plugin($this->slug);
			}else{
				$update = $this->maybeCheckForUpdates();
				if(!is_null($update) && $update != false){
					$this->add_not_upgradable_plugin($this->slug);
					$this->update_available_notice();
					//Inject the fake update...
					add_filter('site_transient_update_plugins', array($this,'injectFakeUpdate')); //WP 3.0+
					add_filter('transient_update_plugins', array($this,'injectFakeUpdate')); //WP 2.8+
				}
			}
		}else{
			parent::installHooks();
		}
	}

	/**
	 * Retrieve plugin info from the configured API endpoint.
	 *
	 * @uses wp_remote_get()
	 *
	 * @param array $queryArgs Additional query arguments to append to the request. Optional.
	 * @return \PluginInfo
	 */
	public function requestInfo($queryArgs = array()){
		//Query args to append to the URL. Plugins can add their own by using a filter callback (see addQueryArgFilter()).
		$installedVersion = $this->getInstalledVersion();
		$queryArgs['installed_version'] = ($installedVersion !== null) ? $installedVersion : '';
		$queryArgs = apply_filters('puc_request_info_query_args-'.$this->slug, $queryArgs);

		//Various options for the wp_remote_get() call. Plugins can filter these, too.
		//For those options, you can see @ class-http.php->request()
		$options = array(
			'timeout' => 5, //seconds
			'headers' => array(
				'Accept' => 'application/json'
			),
		);
		$options = apply_filters('puc_request_info_options-'.$this->slug, $options); //todo: will be deprecated
		$options = apply_filters('wbf/plugins/'.$this->slug.'/updates/request_options/', $options);

		//The plugin info should be at 'http://your-api.com/url/here/$slug/info.json'
		$url = $this->metadataUrl;
		if ( !empty($queryArgs) ){
			$url = add_query_arg($queryArgs, $url);
		}

		$result = wp_remote_get(
			$url,
			$options
		);

		//Try to parse the response
		$pluginInfo = null;
		if ( !is_wp_error($result) && isset($result['response']['code']) && ($result['response']['code'] == 200) && !empty($result['body']) ){
			$pluginInfo = \PluginInfo_1_6::fromJson($result['body'], $this->debugMode);
			$pluginInfo->filename = $this->pluginFile;
		} else if ( $this->debugMode ) {
			//$message = sprintf("The URL %s does not point to a valid plugin metadata file. ", $url);
			$message = sprintf(__("The update url provided for plugin %s does not point to a valid plugin metadata file. ","wbf"), $this->slug);
			if ( is_wp_error($result) ) {
				$message .= "WP HTTP error: " . $result->get_error_message();
			} else if ( isset($result['response']['code']) ) {
				$message .= "HTTP response code is " . $result['response']['code'] . " (expected: 200)";
			} else {
				$message .= "wp_remote_get() returned an unexpected result.";
			}
			//trigger_error($message, E_USER_WARNING);
			$this->notice_manager->add_notice($this->slug."_update",$message,"error","_flash_");
		}

		$pluginInfo = apply_filters('puc_request_info_result-'.$this->slug, $pluginInfo, $result);
		return $pluginInfo;
	}


	/**
	 * Check for updates if the configured check interval has already elapsed.
	 * Will use a shorter check interval on certain admin pages like "Dashboard -> Updates" or when doing cron.
	 *
	 * You can override the default behaviour by using the "puc_check_now-$slug" filter.
	 *
	 * The filter callback will be passed three parameters:
	 *     - Current decision. TRUE = check updates now, FALSE = don't check now.
	 *     - Last check time as a Unix timestamp.
	 *     - Configured check period in hours.
	 * Return TRUE to check for updates immediately, or FALSE to cancel.
	 *
	 * This method is declared public because it's a hook callback. Calling it directly is not recommended.
	 *
	 * @hooked 'check_plugin_updates-<plugin_slug>'
	 */
	public function maybeCheckForUpdates(){
		if ( empty($this->checkPeriod) ){
			return false;
		}

		if(!apply_filters("wbf/plugins/$this->slug/updates/check_for_updates",true)){
			return false;
		}

		$currentFilter = current_filter();
		if ( in_array($currentFilter, array('load-update-core.php', 'upgrader_process_complete')) ) {
			//Check more often when the user visits "Dashboard -> Updates" or does a bulk update.
			$timeout = 60;
		} else if ( in_array($currentFilter, array('load-plugins.php', 'load-update.php')) ) {
			//Also check more often on the "Plugins" page and /wp-admin/update.php.
			$timeout = 3600;
		} else if ( isset($this->throttleRedundantChecks) && $this->throttleRedundantChecks && ($this->getUpdate() !== null) ) {
			//Check less frequently if it's already known that an update is available.
			$timeout = $this->throttledCheckPeriod * 3600;
		} else if ( defined('DOING_CRON') && constant('DOING_CRON') ) {
			//WordPress cron schedules are not exact, so lets do an update check even
			//if slightly less than $checkPeriod hours have elapsed since the last check.
			$cronFuzziness = 20 * 60;
			$timeout = $this->checkPeriod * 3600 - $cronFuzziness;
		} else {
			$timeout = $this->checkPeriod * 3600;
		}

		$timeout = apply_filters("wbf/plugin_framework/updates/check_timeout",$timeout);
		$timeout = apply_filters("wbf/plugins/$this->slug/updates/check_timeout",$timeout);

		$state = $this->getUpdateState();
		$shouldCheck = empty($state) || !isset($state->lastCheck) || ( (time() - $state->lastCheck) >= $timeout );
		$shouldCheck = apply_filters('puc_check_now-' . $this->slug, $shouldCheck, (!empty($state) && isset($state->lastCheck)) ? $state->lastCheck : 0, $this->checkPeriod); //Let plugin authors substitute their own algorithm.

		if($shouldCheck){
			$result = $this->checkForUpdates();
			return $result;
		}

		return false;
	}

	/**
	 * Insert a fake update
	 *
	 * @param \StdClass $updates Update list.
	 * @return \StdClass Modified update list.
	 */
	public function injectFakeUpdate($updates){
		//Is there an update to insert?
		$update = $this->getUpdate();

		//No update notifications for mu-plugins unless explicitly enabled. The MU plugin file
		//is usually different from the main plugin file so the update wouldn't show up properly anyway.
		if ( !empty($update) && empty($this->muPluginFile) && $this->isMuPlugin() ) {
			$update = null;
		}

		if ( !empty($update) ) {
			//Let plugins filter the update info before it's passed on to WordPress.
			$update = apply_filters('puc_pre_inject_update-' . $this->slug, $update);
			if ( !is_object($updates) ) {
				$updates = new \StdClass();
				$updates->response = array();
			}

			$wpUpdate = $update->toWpFormat();
			$pluginFile = $this->pluginFile;

			if ( $this->isMuPlugin() ) {
				//WP does not support automatic update installation for mu-plugins, but we can still display a notice.
				$wpUpdate->package = null;
				$pluginFile = $this->muPluginFile;
			}

			//Set the pkg to null
			$wpUpdate->package = null;

			$updates->response[$pluginFile] = $wpUpdate;

		} else if ( isset($updates, $updates->response) ) {
			unset($updates->response[$this->pluginFile]);
			if ( !empty($this->muPluginFile) ) {
				unset($updates->response[$this->muPluginFile]);
			}
		}

		return $updates;
	}

	/**
	 * Add the update notice
	 */
	public function update_available_notice(){
		$unable_to_update = get_option("wbf_unable_to_update_plugins",array());
		if(!empty($unable_to_update) && \WBF::is_wbf_admin_page()){
			$message = sprintf(__( 'One or more plugins has an updated version available! <a href="%s" title="Enter a valid license">Enter a valid license</a> to get latest updates.', 'wbf' ),"admin.php?page=wbf_licenses");
			$this->notice_manager->add_notice($this->plugin_slug."-update",$message,"nag","_flash_");
		}
	}

	/**
	 * Removes $plugin_name from the list of not upgradable plugins
	 * @param $plugin_name
	 */
	protected function remove_from_not_upgradable_plugin($plugin_name){
		$opt = get_option("wbf_unable_to_update_plugins",[]);
		if(!is_array($opt)){
			$opt = []; //Correct the data if broken
		}
		foreach($opt as $k => $plg){
			if($plg == $plugin_name){
				unset($opt[$k]);
			}
		}
		update_option("wbf_unable_to_update_plugins",$opt);
	}

	/**
	 * Adds $plugin_name from the list of not upgradable plugins
	 * @param $plugin_name
	 */
	protected function add_not_upgradable_plugin($plugin_name){
		$opt = get_option("wbf_unable_to_update_plugins",array());
		if(!in_array($plugin_name,$opt)){
			$opt[] = $plugin_name;
		}
		update_option("wbf_unable_to_update_plugins",$opt);
	}

	/**
	 * Clear the list of not upgradable plugins
	 */
	protected function clear_not_upgradable_plugins(){
		update_option("wbf_unable_to_update_plugins",array());
	}
}