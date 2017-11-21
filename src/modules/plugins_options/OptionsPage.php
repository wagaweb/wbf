<?php

namespace WBF\modules\plugins_options;

use WBF\components\pluginsframework\BasePlugin;

class OptionsPage {
	/**
	 * @var BasePlugin
	 */
	protected $parent_plugin;
	/**
	 * @var string
	 */
	protected $href;
	/**
	 * @var string
	 */
	protected $slug;
	/**
	 * @var string
	 */
	protected $title;
	/**
	 * @var string
	 */
	protected $label;
	/**
	 * @var int
	 */
	protected $order;

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
			],admin_url('admin.php')),
			'order' => 0
		]);
		$this->slug = $params['slug'];
		$this->label = $params['label'];
		$this->href = $params['href'];
		$this->order = $params['order'];
	}

	/**
	 * @return BasePlugin
	 */
	public function get_parent_plugin(){
		return $this->parent_plugin;
	}

	/**
	 * @return string
	 */
	public function get_title(){
		return $this->title;
	}

	/**
	 * @return string
	 */
	public function get_slug(){
		return $this->slug;
	}

	/**
	 * @param $slug
	 */
	public function set_slug($slug){
		$this->slug = $slug;
	}

	/**
	 * @return string
	 */
	public function get_label(){
		return $this->label;
	}

	/**
	 * @param $label
	 */
	public function set_label($label){
		$this->label = $label;
	}

	/**
	 * @return string
	 */
	public function get_href(){
		return $this->href;
	}

	/**
	 * @param $href
	 */
	public function set_href($href){
		$this->href = $href;
	}

	/**
	 * Renders the tab
	 */
	public function render(){}
}