<?php
namespace WBF\modules\components;


use WBF\components\utils\Utilities;
use WBF\modules\options\Framework;
use WBF\modules\options\Organizer;

class Component {
	/**
	 * @var string
	 */
    var $name;
	/**
	 * @var bool
	 */
    var $active;
	/**
	 * @var string
	 */
    var $file;
	/**
	 * @var array
	 */
    var $files;
	/**
	 * @var bool
	 */
    var $is_child_component;
	/**
	 * @var string
	 */
    var $directory_uri;
	/**
	 * @var string
	 */
    var $directory;
	/**
	 * @var string
	 */
	var $directory_name;
	/**
	 * @var string
	 */
	var $relative_path;
	/**
	 * @var string
	 */
	var $category;
	/**
	 * @var array
	 */
	var $tags = [];
	/**
	 * @var array if the filter is on "*" the component will be always loaded
	 */
    var $filters = [
      'post_type' => '*',
      'node_id' => '*'
    ];
	/**
	 * @var bool
	 */
	var $override = false;
	/**
	 * @var string
	 */
	var $version;

	/**
	 * Component constructor.
	 *
	 * @param array $component_data (an array with at least "nicename","enabled","file", and "child_component")
	 */
    public function __construct($component_data){
        $this->name = $component_data['nicename'];
        $this->active = $component_data['enabled'];
        $this->file = $component_data['file'];
        $this->is_child_component = $component_data['child_component'];
	    $pathinfo = pathinfo($component_data['file']);
	    $this->directory_uri = Utilities::path_to_url($pathinfo['dirname']);
	    $this->directory = $pathinfo['dirname'];
	    $this->directory_name = basename($pathinfo['dirname']);
        if($this->is_child_component){
	        $this->relative_path = get_child_dirname()."/".basename($pathinfo['dirname']);
        }else{
	        $this->relative_path = get_root_dirname()."/".basename($pathinfo['dirname']);
        }
	    if(isset($component_data['override'])){
		    $this->override = $component_data['override'];
	    }
	    if(isset($component_data['metadata'])){
	    	if(isset($component_data['metadata']['category']) && !empty($component_data['metadata']['category'])){
	    		$this->category = $component_data['metadata']['category'];
		    }
		    if(isset($component_data['metadata']['tags']) && is_array($component_data['metadata']['tags']) && !empty($component_data['metadata']['tags'])){
			    $this->tags = $component_data['metadata']['tags'];
		    }
		    if(isset($component_data['metadata']['version'])){
	    		$this->set_version($component_data['metadata']['version']);
		    }
	    }
    }

    /**
     * Register the component $filters
     *
     * DO NOT EVER, AND I MEAN EVER, PUT THIS INTO OBJECT CONSTRUCTOR, IT WILL BLOW THIGS UP!
     */
    public function detectFilters(){
		static $filters_updated_flag;
	    //if(isset($filters_updated_flag) && $filters_updated_flag) return; //the method was already called at least once

        //Detect the filters
        if(\WBF\modules\options\of_get_option($this->name."_selective_disable","0") == 1){
            $this->filters = array();
        }elseif(\WBF\modules\options\of_get_option($this->name."_enabled_for_all_pages","1") == 1){
            $this->filters = array(
              'post_type' => '*',
              'node_id' => '*'
            );
        }else{
            $this->filters = array(
              'post_type' => array(),
              'node_id' => array()
            );
            $allowed_post_types = \WBF\modules\options\of_get_option($this->name."_load_locations",[]);
            if(!is_array($allowed_post_types)){
            	$allowed_post_types = [
            		'front' => 0,
		            'home' => 0
	            ]; //Ensure the data type
            }
	        if($allowed_post_types['front'] == 1){
                array_push($this->filters['node_id'],get_option("page_on_front"));
                unset($allowed_post_types['front']);
            }
            if($allowed_post_types['home'] == 1){
                array_push($this->filters['node_id'],get_option("page_for_posts"));
                unset($allowed_post_types['home']);
            }
            foreach($allowed_post_types as $k => $val){
                if($val == 1){
                    array_push($this->filters['post_type'],$k);
                }
            }
            $specific_ids = \WBF\modules\options\of_get_option($this->name."_load_locations_ids",array());
            if(!empty($specific_ids)){
                $specific_ids = explode(',',trim($specific_ids));
                foreach($specific_ids as $id){
                    $id = trim($id);
                    if(!in_array($id,$this->filters['node_id']))
                        array_push($this->filters['node_id'],$id);
                }
            }
        }

	    $filters_updated_flag = true;
    }

    /**
     * Method called on "init" action for each active components
     */
    public function setup(){}

    /**
     * Method called from &_optionsframework_options() by registeredActiveComponentOptions()
     */
    public function register_options(){
	    $orgzr = Organizer::getInstance();

	    $orgzr->set_group("components");

	    $section_name = $this->name."_component";
	    $additional_params = [
		    'component' => true,
		    'component_name' => $this->name
	    ];

	    $orgzr->add_section($section_name,$this->name." Component",null,$additional_params);

	    $orgzr->set_section($section_name);

        do_action("wbf/modules/components/component/{$section_name}/register_options",$this,$section_name);
        $custom_options = apply_filters("wbf/modules/components/component/{$section_name}/register_custom_options",[],$this,$section_name);
        if(is_array($custom_options) && !empty($custom_options)){
            foreach($custom_options as $opt){
                $orgzr->add($opt,null,null,$additional_params);
            }
        }

	    $filter_locs = array_merge(array("front"=>"Frontpage","home"=>"Blog"),wbf_get_filtered_post_types());

        $component_default_options = [
        	'enable_on_all_pages' => [
		        'name' => __( 'Enable on all pages', 'wbf' ),
		        'desc' => __( 'Check this box to load the component in every page (load locations will be ignored).', 'wbf' ),
		        'id'   => $this->name.'_enabled_for_all_pages',
		        'std'  => '1',
		        'class' => 'enabled_for_all_pages',
		        'type' => 'checkbox'
	        ],
        	'load_locations' => [
		        'id' => $this->name.'_load_locations',
		        'name' => __('Load locations','wbf'),
		        'desc' => __('You can load the component only into one ore more page types by selecting them from the list below', 'wbf'),
		        'class' => 'load_locations',
		        'type' => 'multicheck',
		        'options' => $filter_locs,
	        ],
        	'load_locations_ids' => [
		        'id' => $this->name.'_load_locations_ids',
		        'name' => __('Load locations by ID','wbf'),
		        'desc' => __('You can load the component for specific pages by enter here the respective ids (comma separated)', 'wbf'),
		        'class' => 'load_locations_id',
		        'type' => 'text',
	        ],
        ];

        $component_default_options = apply_filters("wbf/modules/components/component/default_options",$component_default_options,$this);

        foreach ($component_default_options as $k => $v){
	        $component_default_options[$k]['component'] = true;
	        $orgzr->add($component_default_options[$k],null,null,$additional_params);
        }

	    $orgzr->reset_group();
	    $orgzr->reset_section();
    }

    /**
     * Method called on "wp" action for each active components that is enabled for current displayed page
     */
    public function run(){}

    /**
     * Method called on "wp_enqueue_scripts" action for each active components that is enabled for current displayed page
     */
    public function scripts(){}

    /**
     * Method called on "wp_enqueue_scripts" action for each active components that is enabled for current displayed page
     */
    public function styles(){}

    /**
     * Method called on "widgets_init" action for each active components that is enabled for current displayed page
     */
    public function widgets(){}

	/**
	 * Filter called during "wbf/modules/components/component/{$component_name}/register_options" by addRegisteredComponentOptions().
	 * By default $options is passed empty to this method. This is a backward compatibility method, mostly; register_options() is better.
	 *
	 * @param $options
	 *
	 * @return mixed
	 */
    public function theme_options($options){
		return $options;
	}

	public function get_theme_options_values(){
		return Framework::get_options_values_by_suffix($this->name);
	}

    public function onActivate(){
    	//Update the component state option
	    $states = ComponentsManager::get_components_state();
	    $states[$this->name] = 1;
	    ComponentsManager::update_components_state($states);

	    $this->restore_theme_options();
        Utilities::add_admin_notice("$this->name"."_activated",_x( sprintf("Activated: %s",$this->name),"component", "wbf" ),"success",['manual_display' => true]);

	    do_action("wbf/modules/components/on_activate",$this);
    }

    public function onDeactivate(){
	    //Update the component state option
	    $states = ComponentsManager::get_components_state();
	    $states[$this->name] = 0;
	    ComponentsManager::update_components_state($states);

		$this->backup_theme_options();
	    Utilities::add_admin_notice("$this->name"."_deactivated",_x( sprintf("Deactivated: %s",$this->name),"component", "wbf" ),"success",['manual_display' => true]);

	    do_action("wbf/modules/components/on_deactivate",$this);
    }

    /**
     * Retrieve a file from component directory
     *
     * @param string $filepath
     * @return string
     */
    public function file($filepath){
        if(is_child_theme()){
            $child_file = get_child_components_directory_uri().$this->name."/".$filepath;
            $child_file_path = url_to_path($child_file);
            if(is_file($child_file_path)){
                return $child_file;
            }
        }
        return $this->directory_uri."/".$filepath;
    }

	/**
	 * Backups the current component options. Called during deactivation.
	 *
	 * @return bool
	 */
	private function backup_theme_options(){
		$options = $this->get_theme_options_values();
		if(is_array($options) && !empty($options)){
			$component_options_backup = get_option("wbf_component_options_backup",[]);
			$component_options_backup[$this->name] = $options;
			update_option("wbf_component_options_backup",$component_options_backup);
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Restore current component options. Called during activation.
	 *
	 * @use WBF\modules\options\Framework::update_theme_options
	 *
	 * @return array|bool
	 */
	private function restore_theme_options(){
		$component_options_backup = get_option("wbf_component_options_backup",[]);
		if(isset($component_options_backup[$this->name])){
			$current_options = Framework::get_options_values();
			if(!is_array($current_options)){
				$current_options = [];
			}
			return Framework::update_theme_options(array_merge($current_options,$component_options_backup[$this->name]));
		}else{
			return false;
		}
	}

	/**
	 * Is this component active?
	 *
	 * @return bool|mixed
	 */
	public function is_active(){
		return $this->active;
	}

	/**
	 * Return the component slug
	 *
	 * @return string
	 */
	public function get_slug(){
		return $this->name;
	}

	/**
	 * @param $version
	 */
	public function set_version($version){
		$this->version = $version;
	}

	/**
	 * @return string
	 */
	public function get_version(){
		if(!isset($this->version)){
			$data = ComponentFactory::get_component_data($this->file);
			if(isset($data['Version'])){
				$this->set_version($data['Version']);
			}else{
				$this->set_version('1.0.0');
			}
		}
		return $this->version;
	}
}