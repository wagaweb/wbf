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
	 *
	 * @throws ModelException
	 */
	public function __construct($id, $load_wp_post = true) {
		if(!\is_int($id)){
			throw new ModelException('Invalid ID provided');
		}
		$this->set_id($id);
		if($load_wp_post){
			$this->load_post();
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
	 * Loads the \WP_Post
	 */
	public function load_post(){
		if($this->get_id() > 0){
			$this->post = get_post($this->get_id());
		}
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

	/**
	 * Calls WordPress core actions for inserting a post
	 * @param $id
	 */
	public function call_post_creation_core_actions($id){
		$post = get_post( $id );
		do_action( 'save_post_product', $id, $post, false );
		do_action( 'save_post', $id, $post, false );
		do_action( 'wp_insert_post', $id, $post, false );
	}

	/**
	 * Calls WordPress core actions for updating a post
	 */
	public function call_post_updating_core_actions(){
		$id = $this->get_id();
		$post = get_post( $id );
		do_action( 'edit_post', $id, $post );
		$postAfter = get_post( $id );
		do_action( 'post_updated', $id, $postAfter, $post);
		$post = $postAfter;
		do_action( 'save_post_product', $id, $post, true );
		do_action( 'save_post', $id, $post, true );
		do_action( 'wp_insert_post', $id, $post, true );
	}
}