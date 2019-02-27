<?php

namespace WBF\components\mvc;

use WBF\components\utils\Utilities;

abstract class Repository implements RepositoryInterface
{
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
	 * @param string $returnType
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
	 * @param $modelClassName
	 *
	 * @return RepositoryInterface
	 * @throws ModelException
	 */
	public static function get($modelClassName){
		if($modelClassName === \WP_Post::class){
			return new PostRepository();
		}
		$repositoryClassName = $modelClassName.'Repository';
		if(class_exists($repositoryClassName)){
			$repository = new $repositoryClassName();
		}else{
			$repository = new PostRepository();
			$repository->setClassName($modelClassName);
			$postTypeName = Utilities::strip_namespace($modelClassName);
			if(\is_string($postTypeName)){
				$postTypeName = strtolower($postTypeName);
				$repository->setPostType($postTypeName);
			}
		}
		if(!$repository instanceof RepositoryInterface){
			throw new ModelException('Invalid model class name provided');
		}
		return $repository;
	}
}