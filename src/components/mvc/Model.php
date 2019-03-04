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
	 * @param bool $loadWpPost
	 *
	 * @throws ModelException
	 */
	public function __construct($id, $loadWpPost = true) {
		if(!\is_int($id)){
			throw new ModelException('Invalid ID provided');
		}
		$this->setId($id);
		if($loadWpPost){
			$this->loadPost();
		}
	}

	/**
	 * @return int
	 */
	public function getId(){
		return $this->id;
	}

	/**
	 * @param $id
	 */
	public function setId($id){
		$this->id = (int) $id;
	}

	/**
	 * @return \WP_Post|null
	 */
	public function getPost(){
		return $this->post;
	}

	/**
	 * Loads the \WP_Post
	 */
	public function loadPost(){
		if($this->getId() > 0){
			$this->post = get_post($this->getId());
		}
	}

	/**
	 * @param \WP_Post $post
	 */
	public function setPost(\WP_Post $post){
		$this->post = $post;
	}

	/**
	 * @return bool
	 */
	public function isNew(){
		return $this->id > 0;
	}

	/**
	 * Refresh own id by post
	 */
	public function refreshId(){
		if($this->post instanceof \WP_Post){
			$this->id = $this->post->ID;
		}
	}

	/**
	 * Calls WordPress core actions for inserting a post
	 * @param $id
	 */
	public function callPostCreationCoreActions($id){
		$post = get_post( $id );
		do_action( 'save_post_product', $id, $post, false );
		do_action( 'save_post', $id, $post, false );
		do_action( 'wp_insert_post', $id, $post, false );
	}

	/**
	 * Calls WordPress core actions for updating a post
	 */
	public function callPostUpdatingCoreActions(){
		$id = $this->getId();
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