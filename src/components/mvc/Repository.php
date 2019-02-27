<?php

namespace WBF\components\mvc;

abstract class Repository implements RepositoryInterface
{
	const FIND_ALL_IDS = 0;
	const FIND_ALL_OBJECT = 1;

	/**
	 * @var string
	 */
	private $className;
	/**
	 * @var string
	 */
	private $postType;

	/**
	 * @param $id
	 *
	 * @return object
	 */
	public function find( $id )
	{
		$p = \get_post($id);
		if($p instanceof \WP_Post){
			if($this->isVanillaPost()){
				return $p;
			}
			$pp = $this->createModelInstance($p->ID);
			return $pp;
		}
		return null;
	}

	/**
	 * @param int $returnType
	 *
	 * @return array
	 */
	public function findAll($returnType = self::FIND_ALL_IDS)
	{
		$args = [
			'post_type' => $this->getPostType(),
			'posts_per_page' => -1,
			'fields' => 'ids'
		];
		if($returnType === self::FIND_ALL_OBJECT && $this->isVanillaPost()){
			unset($args['fields']);
		}

		$posts = get_posts($args);
		if(!\is_array($posts)){
			$posts = [];
		}

		if(!$this->isVanillaPost() && $returnType === self::FIND_ALL_OBJECT){
			foreach ($posts as $k => $postId){
				$posts[$k] = $this->createModelInstance($postId);
			}
		}

		return $posts;
	}

	/**
	 * @return mixed
	 */
	public function getPostType()
	{
		return $this->postType;
	}

	/**
	 * @param mixed $postType
	 */
	public function setPostType( $postType )
	{
		$this->postType = $postType;
	}

	/**
	 * @return string
	 */
	public function getClassName()
	{
		return $this->className;
	}

	/**
	 * @param string $className
	 */
	public function setClassName( $className )
	{
		$this->className = $className;
	}

	/**
	 * @return bool
	 */
	private function isVanillaPost()
	{
		return $this->getClassName() === 'WP_Post';
	}

	/**
	 * @param $constructorParam
	 *
	 * @return object
	 */
	private function createModelInstance($constructorParam)
	{
		$className = $this->getClassName();
		return new $className($constructorParam);
	}

	/**
	 * @param $className
	 *
	 * @return RepositoryInterface
	 * @throws ModelException
	 */
	public static function get($className){
		static $repository;
		if($repository instanceof RepositoryInterface){
			return $repository;
		}
		$className = $className.'Repository';
		$repository = new $className();
		if(!$repository instanceof RepositoryInterface){
			throw new ModelException('Invalid repository class provided');
		}
		return $repository;
	}
}