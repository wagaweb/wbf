<?php
/*
 * This file is part of WBF Framework: https://github.com/wagaweb/wbf
 *
 * @author WAGA Team <dev@waga.it>
 */

namespace WBF\components\customupdater;

use WBF\components\license\License;
use WBF\components\notices\Notice_Manager;
use WBF\components\pluginsframework\BasePlugin;
use WBF\components\pluginsframework\Plugin;
use WBF\components\utils\Utilities;
use WBF\PluginCore;

class Plugin_Update_Checker{
	/**
	 * @var BasePlugin
	 */
	public $pluginInstance;
	/**
	 * @var bool
	 */
	public $checkLicense;
	/**
	 * @var License
	 */
	public $plugin_license;
	/**
	 * @var Notice_Manager
	 */
	public $notice_manager;
	/**
	 * @var string
	 */
	public $plugin_slug;
	/**
	 * The URL of the plugin's metadata file.
	 * @var string
	 */
	public $metadataUrl = '';
	/**
	 * @var string
	 */
	public $infoEndpoint;
	/**
	 * Full path of the main plugin file.
	 * @var string
	 */
	public $pluginAbsolutePath = '';
	/**
	 * Plugin filename relative to the plugins directory. Many WP APIs use this to identify plugins.
	 * @var string
	 */
	public $pluginFile = '';
	/**
	 * @var string
	 */
	public $slug = '';
	/**
	 * How often to check for updates (in seconds).
	 * @var int
	 */
	public $checkPeriod = 43200;
	/**
	 * Where to store the update info.
	 * @var string
	 */
	public $optionName = '';
	/**
	 * @var bool
	 */
	public $debugMode = false;
	/**
	 * @var bool
	 */
	public $upgradable = true;
	/**
	 * @var string
	 */
	public $cronHook;
	/**
	 * @var array
	 */
	public $updateData;

	/**
	 * Class constructor.
	 *
	 * @param string $metadataUrl The URL of the plugin's metadata file.
	 * @param BasePlugin|string $plugin Fully qualified path to the main plugin file or an instance of BasePlugin
	 * @param string $slug The plugin's 'slug'. If not specified, the filename part of $pluginFile sans '.php' will be used as the slug.
	 * @param string|null $infoEndpoint
	 * @param integer $checkPeriod How often to check for updates (in seconds). Defaults to checking every 12 hours (43200). Set to 0 to disable automatic update checks.
	 *
	 * @throws \Exception
	 */
	public function __construct($metadataUrl, $plugin, $slug = null, $infoEndpoint = null, $checkPeriod = 43200){
		$this->metadataUrl = $metadataUrl;
		if($plugin instanceof BasePlugin){
			$this->pluginAbsolutePath = $plugin->get_path();
			$this->pluginInstance =& $plugin;
		}elseif(\is_string($plugin) && file_exists($plugin)){
			$this->pluginAbsolutePath = $plugin;
		}else{
			throw new \Exception('Plugin main file not found');
		}

		$this->pluginFile = plugin_basename($this->pluginAbsolutePath);

		if($slug !== null && \is_string($slug)){
			$this->slug = $slug;
		}elseif($plugin instanceof BasePlugin){
			$this->slug = $plugin->get_name();
		}else{
			$this->slug = basename($this->pluginFile, '.php');
		}

		$this->optionName = 'external_updates-' . $this->slug;

		$this->checkPeriod = (int) $checkPeriod > 0 ? (int) $checkPeriod : 43200;

		$this->debugMode = defined('WP_DEBUG') && WP_DEBUG;

		$this->cronHook = 'check_plugin_updates-' . $this->slug;

		if($infoEndpoint !== null){
			$this->infoEndpoint = $infoEndpoint;
		}
	}

	public function initialize(){
		if($this->mustCheckLicense()){
			if($this->plugin_license->is_valid()){
				$this->setUpgradable($this->slug);
			}else{
				$this->setNotUpgradable($this->slug);
			}
		}
		$this->installHooks();
	}

	/**
	 * Install the hooks required to run periodic update checks and inject update info
	 * into WP data structures.
	 *
	 * @return void
	 */
	public function installHooks() {
		if($this->checkPeriod > 0){
			add_action( 'admin_init', array($this, 'maybeSetUpdate') );
			$this->setupUpdateCheckCron();
			//Like WordPress itself, we check more often on certain pages.
			/** @see wp_update_plugins */
			add_action('load-update-core.php', array($this, 'maybeSetUpdate'));
			add_action('load-plugins.php', array($this, 'maybeSetUpdate'));
			add_action('load-update.php', array($this, 'maybeSetUpdate'));
			//This hook fires after a bulk update is complete.
			add_action('upgrader_process_complete', array($this, 'maybeSetUpdate'), 11, 0);
		}else{
			wp_clear_scheduled_hook($this->cronHook);
		}
		add_filter('site_transient_update_plugins', [$this,'maybeInjectUpdate']);
		add_filter('plugins_api', [$this,'handlePluginInfoRequests'], 20, 3);
	}

	/**
	 * Set the update if necessary
	 *
	 * @param bool|null $force
	 *
	 * @throws \Exception
	 */
	public function maybeSetUpdate($force = null){
		if(!is_bool($force)){
			global $pagenow;
			$force = false;
			if($pagenow === 'update-core.php' && isset($_GET['force-check'])){
				$force = true;
			}
		}
		$lastCheck = get_option($this->optionName,null);
		if($force || (!$lastCheck || !is_array($lastCheck) || !isset($lastCheck['time']) || (time() - (int) $lastCheck['time']) > $this->checkPeriod) ){
			//Check update
			$update = $this->requestUpdate();
			if(\is_array($update)){
				$this->setUpdateData($update);
				$this->updateCacheOption($update);
				$this->enqueueUpdatesAvailableNotice();
			}else{
				$this->updateCacheOption(false);
			}
		}
	}

	/**
	 * @param mixed $update
	 */
	public function updateCacheOption($update){
		update_option($this->optionName,[
			'time' => time(),
			'update' => \is_array($update) ? $update : false
		]);
	}

	/**
	 * @return array|bool
	 */
	public function getCacheOption(){
		return get_option($this->optionName,false);
	}

	/**
	 * @param array $update
	 */
	public function setUpdateData($update){
		$this->updateData = $update;
	}

	/**
	 * @return array
	 */
	public function getUpdateData(){
		return $this->updateData;
	}

	/**
	 * Inject custom update info
	 *
	 * @hooked 'site_transient_update_plugins'
	 *
	 * @param \stdClass $updates
	 * @return \stdClass
	 */
	public function maybeInjectUpdate($updates){
		if(isset($updates->response[$this->slug])) return $updates;
		$update = $this->getUpdateData();
		if(!\is_array($update)){
			$update = $this->getCacheOption();
			if(\is_array($update) && array_key_exists('update',$update) && $update['update'] !== false){
				$update = $update['update'];
			}else{
				return $updates;
			}
		}
		$response = new \stdClass();
		$response->slug = $update['slug'];
		$response->plugin = $this->pluginFile;
		$response->new_version = $update['version'];
		$response->url = isset($update['url']) ? $update['url'] : '';
		$response->package = $this->upgradable ? $update['download_url'] : null;
		$response->icons = isset($update['icons']) && \is_array($update['icons']) ? $update['icons'] : [];
		$response->banners = isset($update['banners']) && \is_array($update['banners']) ? $update['banners'] : [];
		$response->banners_rtl = isset($update['banners_rtl']) && \is_array($update['banners_rtl']) ? $update['banners_rtl'] : [];
		$response->compatibility = new \stdClass(); //?
		$updates->response[$this->pluginFile] = $response;
		return $updates;
	}

	/**
	 * Request the update package
	 * @return array|bool
	 */
	public function requestUpdate(){
		$currentVersion = $this->getCurrentVersion();
		$latestPackage = $this->getUpdatePackage();
		if(!is_wp_error($latestPackage) && isset($latestPackage['version'])){
			$r = version_compare($currentVersion,$latestPackage['version']);
			if($r === -1){
				$update = $latestPackage;
				return $update;
			}
		}
		$update = false;
		return $update;
	}

	/**
	 * Get update package from the endpoint.
	 * The update package must be a json with the following fields:
	 *
	 * - slug : string,
	 * - version : string
	 * - [url] : string
	 * - download_url : string
	 * - [icons] : array
	 * - [banners] : array
	 * - [banners_rtl] : array
	 *
	 * @return array|\WP_Error
	 */
	private function getUpdatePackage(){
		$result = wp_remote_get(
			$this->metadataUrl,
			[
				'timeout' => 5, //seconds
				'headers' => [
					'Accept' => 'application/json'
				],
			]
		);
		if(is_wp_error($result)){
			return $result;
		}
		if(isset($result['response']['code']) && $result['response']['code'] === 200 && isset($result['body']) && $result['body'] !== ''){
			$response = json_decode($result['body'],true);
			if(\is_array($response)){
				return $response;
			}
		}
		return new \WP_Error('wbf_invalid_update_response','Invalid Update Response');
	}

	/**
	 * Get the current plugin version
	 *
	 * @return string|bool
	 */
	private function getCurrentVersion(){
		$data = get_plugin_data($this->pluginAbsolutePath);
		if(isset($data['Version'])){
			return $data['Version'];
		}
		return false;
	}

	/**
	 * Set the cron schedule to check for updates
	 */
	private function setupUpdateCheckCron(){
		$scheduleName = call_user_func(function(){
			$defaultSchedules = wp_get_schedules();
			foreach ($defaultSchedules as $scheduleName => $scheduleValues){
				if($scheduleValues['interval'] === $this->checkPeriod){
					return $scheduleName;
				}
			}
			return false;
		});
		if(!$scheduleName){
			//Use a custom cron schedule.
			$scheduleName = 'every' . $this->checkPeriod . 'hours';
			add_filter('cron_schedules', function($schedules) use($scheduleName){
				$scheduleName = 'every' . $this->checkPeriod . 'hours';
				$schedules[$scheduleName] = array(
					'interval' => $this->checkPeriod,
					'display' => sprintf('Every %d hours', $this->checkPeriod),
				);
				return $schedules;
			});
		}
		if(!wp_next_scheduled($this->cronHook) && !defined('WP_INSTALLING')) {
			wp_schedule_event(time(), $scheduleName, $this->cronHook);
		}
		add_action($this->cronHook, array($this, 'maybeSetUpdate'));
	}

	/**
	 * Assign a license to validate before update the plugin
	 * @param License $license
	 * @param bool $checkLicense
	 */
	public function setLicense(License $license, $checkLicense = true){
		$this->plugin_license = $license;
		$this->checkLicense = $checkLicense;
	}

	/**
	 * @return bool
	 */
	public function mustCheckLicense(){
		return $this->plugin_license instanceof License && $this->checkLicense;
	}

	/**
	 * Fetching plugins information during plugins_api calls. WP calls this hook when user is viewing plugin details.
	 *
	 * @param bool|array $result
	 * @param string|null $action
	 * @param array|null $args
	 *
	 * @hooked 'plugin_api'
	 *
	 * @return \stdClass|bool
	 */
	public function handlePluginInfoRequests($result, $action = null, $args = null){
		$relevant = $action === 'plugin_information' && isset($args->slug) && ($args->slug === $this->slug);
		if(!$relevant){
			return $result;
		}
		//Here $result is a \stdClass with (eg):
		/**
		 * stdClass::__set_state(array(
			'name' => '...',
			'slug' => '...',
			'version' => '3.2.6',
			'author' => '<a href="https://...">ShortPixel</a>',
			'author_profile' => 'https://...',
			'requires' => '...',
			'tested' => '...',
			'requires_php' => false,
			'compatibility' => array (),
			'rating' => 92.0,
			'ratings' =>
				array ( 5 => 143, 4 => 9, 3 => 5, 2 => 2, 1 => 13),
			'num_ratings' => 172,
			'support_threads' => 12,
			'support_threads_resolved' => 3,
			'active_installs' => 300000,
			'last_updated' => '2018-07-04 10:13am GMT',
			'added' => '2009-06-01',
			'homepage' => 'http://www....,
			'sections' => array (
				'description' => '[HTML]',
		        'installation' => '[HTML]',
		        'faq' => '[HTML]',
				'changelog' => '[HTML],
				'screenshots' => '<ol><li><a href="https://ps.w.org/enable-media-replace/assets/screenshot-1.png?rev=1702418"><img src="https://ps.w.org/enable-media-replace/assets/screenshot-1.png?rev=1702418" alt="The new link in the media library."></a><p>The new link in the media library.</p></li><li><a href="https://ps.w.org/enable-media-replace/assets/screenshot-2.png?rev=1702418"><img src="https://ps.w.org/enable-media-replace/assets/screenshot-2.png?rev=1702418" alt="The replace media-button as seen in the &quot;Edit media&quot; view."></a><p>The replace media-button as seen in the "Edit media" view.</p></li><li><a href="https://ps.w.org/enable-media-replace/assets/screenshot-3.png?rev=1702418"><img src="https://ps.w.org/enable-media-replace/assets/screenshot-3.png?rev=1702418" alt="The upload options."></a><p>The upload options.</p></li><li><a href="https://ps.w.org/enable-media-replace/assets/screenshot-4.png?rev=1702418"><img src="https://ps.w.org/enable-media-replace/assets/screenshot-4.png?rev=1702418" alt="Get the file ID in the edit file URL"></a><p>Get the file ID in the edit file URL</p></li></ol>',
				'reviews' => '[HTML]'
			'download_link' => 'https://...',
			'screenshots' =>
				array (
					1 =>
						array (
							'src' => 'https://...',
							'caption' => 'The new link in the media library.',
						),
		            ...
			'tags' =>
				array (
				'attachment' => 'attachment',
				'files' => 'files',
				'media' => 'media',
				'replace' => 'replace',
				'replace-image' => 'replace image',
				),
			'versions' =>
				array (
					'1.0' => 'https://downloads.wordpress.org/plugin/....1.0.zip',
					'1.1' => 'https://downloads.wordpress.org/plugin/....1.1.zip',
					'1.2' => 'https://downloads.wordpress.org/plugin/....1.2.zip',
				),
			'donate_link' => 'https://www....',
			'banners' =>
				array (
					'low' => 'https://...',
					'high' => 'https://...',
				),
			'contributors' => array (),
		))
		 */
		if($this->infoEndpoint === null){
			$update = $this->requestUpdate();
			$result = new \stdClass();
			$result->name = $update['name'];
			$result->slug = $update['slug'];
			$result->version = $update['version'];
			$result->sections = [
				'description' => '',
				'installation' => '',
				'faq' => '',
				'changelog' => $this->getPluginChangelog(),
				'screenshots' => '',
				'reviews' => ''
			];
		}elseif(is_string($this->infoEndpoint)){
			$infoRequest = wp_remote_get(
				$this->infoEndpoint,
				[
					'timeout' => 5, //seconds
					'headers' => [
						'Accept' => 'application/json'
					],
				]
			);
			if(isset($infoRequest['response']['code']) &&
			   $infoRequest['response']['code'] === 200 &&
			   isset($infoRequest['body']) &&
			   $infoRequest['body'] !== ''
			){
				$response = json_decode($result['body'],false);
				if($response instanceof \stdClass){
					return $response;
				}
			}
		}
		return $result;
	}

	/**
	 * Get changelog file content
	 * @return string
	 */
	public function getPluginChangelog(){
		if($this->pluginInstance && method_exists($this->pluginInstance,'get_changelog')){
			return $this->pluginInstance->get_changelog();
		}
		if(\is_file(dirname($this->pluginAbsolutePath).'/changelog')){
			$changelog = file_get_contents(dirname($this->pluginAbsolutePath).'/changelog');
			if(\is_string($changelog)){
				return $changelog;
			}
		}
		return '';
	}

	/**
	 * Add the update notice
	 * @throws \Exception
	 */
	public function enqueueUpdatesAvailableNotice(){
		$unable_to_update = get_option("wbf_unable_to_update_plugins",array());
		if(!empty($unable_to_update) && WBF()->is_wbf_admin_page()){
			$message = sprintf(__( 'One or more plugins has an updated version available! <a href="%s" title="Enter a valid license">Enter a valid license</a> to get latest updates.', 'wbf' ),"admin.php?page=wbf_licenses");
			WBF()->get_service_manager()->get_notice_manager()->add_notice($this->plugin_slug."-update",$message,"nag","_flash_");
		}
	}

	/**
	 * Removes $plugin_name from the list of not upgradable plugins
	 * @param $plugin_name
	 */
	protected function setNotUpgradable($plugin_name){
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
		$this->upgradable = false;
	}

	/**
	 * Adds $plugin_name from the list of not upgradable plugins
	 * @param $plugin_name
	 */
	protected function setUpgradable($plugin_name){
		$opt = get_option("wbf_unable_to_update_plugins",array());
		if(!in_array($plugin_name,$opt)){
			$opt[] = $plugin_name;
		}
		update_option("wbf_unable_to_update_plugins",$opt);
		$this->upgradable = true;
	}

	/**
	 * Clear the list of not upgradable plugins
	 */
	protected static function clearUpgradablePluginsStatusCache(){
		update_option("wbf_unable_to_update_plugins",array());
	}
}