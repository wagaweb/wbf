<?php

namespace WBF\components\mvc;

abstract class Model{
	/**
	 * @var \WP_Post
	 */
	private $post;
	/**
	 * @var int
	 */
	private $id;

	/**
	 * Model constructor.
	 *
	 * @param $id
	 * @param bool $load_wp_post
	 */
	public function __construct($id, $load_wp_post = true) {
		$id = (int) $id;
		if($id > 0 && $load_wp_post){
			$this->post = get_post($id);
		}
	}

	/**
	 * @return int
	 */
	public function get_id(){
		return $this->id;
	}

	/**
	 * @param $id
	 */
	public function set_id($id){
		$this->id = (int) $id;
	}

	/**
	 * @return \WP_Post|null
	 */
	public function get_post(){
		return $this->post;
	}

	/**
	 * @param \WP_Post $post
	 */
	public function set_post(\WP_Post $post){
		$this->post = $post;
	}

	/**
	 * @return bool
	 */
	public function is_new(){
		return $this->id > 0;
	}

	/**
	 * Refresh own id by post
	 */
	public function refresh_id(){
		if($this->post instanceof \WP_Post){
			$this->id = $this->post->ID;
		}
	}
}