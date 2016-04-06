<?php

namespace WBF\modules\options;

class Organizer {
	/**
	 * @var Organizer The reference to *Singleton* instance of this class
	 */
	private static $instance;

	/**
	 * @var array
	 */
	private $options = [];

	/**
	 * @var array
	 */
	private $sections = [];

	/**
	 * @var array
	 */
	private $groups = [];

	/**
	 * @var string
	 */
	private $current_section = "default";

	/**
	 * @var string
	 */
	private $current_group = "default";

	/**
	 * Returns the *OptionsManager* instance of this class.
	 *
	 * @return Organizer The *Singleton* instance.
	 */
	public static function getInstance() {
		if (null === static::$instance) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	public function set_section($section){
		$this->current_section = $section;
	}

	public function reset_section(){
		$this->current_section = "default";
	}

	public function set_group($group){
		$this->current_group = $group;
	}

	public function reset_group(){
		$this->current_group = "default";
	}

	/**
	 * Add a new section
	 *
	 * @param string $id
	 * @param string $label
	 * @param string|null $group
	 * @param array $params
	 *
	 * @return $this
	 */
	public function add_section($id,$label,$group = null,$params = []){
		if(!isset($group)){
			$group = $this->current_group;
		}

		$option = [
			'name' => $label,
			'type' => 'heading',
			'section_id' => $id,
			'group_id' => $group
		];

		if(is_array($params) && !empty($params)){
			$option = array_merge($option,$params);
		}

		$this->sections[$id][] = $option;

		if(!isset($this->groups[$group])){
			$this->groups[$group] = [];
		}

		if(!isset($this->groups[$group][$id])){
			$this->groups[$group][$id][] = $option;
		}

		$this->options[] = $option;

		return $this;
	}

	/**
	 * Add a new options
	 *
	 * @param array $option
	 *
	 * @param string|null $section
	 *
	 * @param string|null $group
	 *
	 * @param array $params
	 *
	 * @return $this
	 */
	public function add($option,$section = null,$group = null,$params = []){
		if(!isset($section)){
			$section = $this->current_section;
		}
		if(!isset($group)){
			$group = $this->current_group;
		}
		$option['section_id'] = $section;
		$option['group_id'] = $group;

		if(is_array($params) && !empty($params)){
			$option = array_merge($option,$params);
		}

		if(isset($this->groups[$group][$section])){
			$this->groups[$group][$section][] = $option;
		}else{
			$this->groups[$group][$section][] = $option;
		}

		if(isset($this->sections[$section])){
			$this->sections[$section][] = $option;
		}else{
			$this->sections[$section][] = $option;
		}

		$this->options[] = $option;
		return $this;
	}

	/**
	 * Generate the structure
	 */
	public function generate(){
		$sections = apply_filters("wbf/modules/options/organizer/sections",$this->sections, $this);
		//$groups = apply_filters("wbf/modules/options/organizer/groups",$this->groups, $this);
		$res = [];
		foreach($sections as $sections => $options){
			if(is_array($options) && !empty($options)){
				foreach($options as $opt){
					$res[] = $opt;
				}
			}
		}
		$options = apply_filters("wbf/modules/options/organizer/output",$res, $this);
		return $options;
	}

	/**
	 * Get the options of the specified group
	 *
	 * @param $group
	 * @param bool|true $flatten
	 * @return array
	 */
	public function get_group($group, $flatten = true){
		$res = [];
		if(isset($this->groups[$group])){
			if(!$flatten){
				$res = $this->groups[$group];
			}else{
				foreach($this->groups[$group] as $section => $options){
					foreach($options as $opt){
						$res[] = $opt;
					}
				}
			}
		}
		return $res;
	}

	/**
	 * Get the options of the specified section
	 *
	 * @param $section
	 * @return array
	 */
	public function get_section($section){
		$res = [];
		if(isset($this->sections[$section])){
			$res = $this->sections[$section];
		}
		return $res;
	}

	/**
	 * Protected constructor to prevent creating a new instance of the
	 * *Singleton* via the `new` operator from outside of this class.
	 */
	protected function __construct() {}

	/**
	 * Private clone method to prevent cloning of the instance of the
	 * *Singleton* instance.
	 *
	 * @return void
	 */
	private function __clone() {}

	/**
	 * Private unserialize method to prevent unserializing of the *Singleton*
	 * instance.
	 *
	 * @return void
	 */
	private function __wakeup() {}
}