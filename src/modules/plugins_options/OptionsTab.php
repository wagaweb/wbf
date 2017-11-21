<?php

namespace WBF\modules\plugins_options;

use WBF\components\pluginsframework\BasePlugin;

class OptionsTab extends OptionsPage {
	/**
	 * @var array
	 */
	private $sections;

	/**
	 * OptionsTab constructor.
	 *
	 * @param BasePlugin $parent_plugin
	 * @param $title
	 * @param array $params
	 */
	public function __construct(&$parent_plugin,$title,$params = []) {
		$this->parent_plugin = &$parent_plugin;
		$this->title = $title;
		$raw_slug = sanitize_title($title);
		$params = wp_parse_args($params,[
			'slug' => $raw_slug,
			'label' => $title,
			'href' => add_query_arg([
				'page' => 'wbf-plugins-options',
				'tab' => $raw_slug
			],admin_url('admin.php')),
			'order' => 0
		]);
		$this->slug = $params['slug'];
		$this->label = $params['label'];
		$this->href = $params['href'];
		$this->order = $params['order'];
	}

	/**
	 * @param OptionsTab $section
	 */
	public function add_section(OptionsTab $section){
		$this->sections[] = $section;
	}

	/**
	 * @return array
	 */
	public function get_sections(){
		return $this->sections;
	}

	/**
	 * @return bool
	 */
	public function has_sections(){
		return count($this->get_sections()) > 0;
	}
}