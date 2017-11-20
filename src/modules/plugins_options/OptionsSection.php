<?php

namespace WBF\modules\plugins_options;

use WBF\components\pluginsframework\BasePlugin;

class OptionsSection extends OptionsTab {

	/**
	 * @var OptionsTab
	 */
	private $parent_tab;

	/**
	 * OptionsTab constructor.
	 *
	 * @param OptionsTab $parent_tab
	 * @param $title
	 * @param array $params
	 */
	public function __construct(&$parent_tab,$title,$params = []) {
		$this->parent_tab = $parent_tab;
		$this->parent_plugin = $parent_tab->get_parent_plugin();
		$this->title = $title;
		$raw_slug = sanitize_title($title);
		$parent_raw_slug = sanitize_title($parent_tab->get_title());
		$params = wp_parse_args($params,[
			'slug' => $raw_slug,
			'label' => $title,
			'href' => add_query_arg([
				'page' => 'wbf-plugins-options',
				'tab' => $parent_raw_slug,
				'section' => $raw_slug
			],admin_url('admin.php')),
			'order' => 0
		]);
		$this->slug = $params['slug'];
		$this->label = $params['label'];
		$this->href = $params['href'];
		$this->order = $params['order'];
	}
}