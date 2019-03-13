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
		$criteria = [
			'post_type' => $this->getPostType(),
			'posts_per_page' => -1,
			'fields' => 'ids'
		];
		if($returnType === self::FIND_ALL_OBJECT && $this->isVanillaPost()){
			unset($criteria['fields']);
		}

		$posts = get_posts($criteria);
		if(!\is_array($posts)){
			$posts = [];
		}

		if(!$this->isVanillaPost() && $returnType === self::FIND_ALL_OBJECT){
			foreach ($posts as $k => $postId){
				$pp = $this->createModelInstance($postId);
				$posts[$k] = $pp;
			}
		}

		return $posts;
	}

	/**
	 * @param array $criteria (@see https://codex.wordpress.org/Class_Reference/WP_Query#Parameters)
	 * @param array|null $orderBy
	 * @param int|null $limit
	 * @param int|null $offset
	 * @param string $returnType
	 *
	 * @return array
	 */
	public function findByParams( array $criteria, array $orderBy = null, $limit = null, $offset = null, $returnType = self::FIND_ALL_IDS )
	{
		$criteria['post_type'] = $this->getPostType();
		$criteria['fields'] = 'ids';

		if($returnType === self::FIND_ALL_OBJECT && $this->isVanillaPost()){
			unset($criteria['fields']);
		}

		if($orderBy === null || !\is_array($orderBy) || count($orderBy) <= 0){
			$orderBy = ['date' => 'DESC'];
		}

		if($limit === null){
			$limit = 5;
		}

		if($offset === null){
			$offset = 0;
		}

		$criteria['orderby'] = array_keys($orderBy)[0];
		$criteria['order'] = array_values($orderBy)[0];
		$criteria['offset'] = $offset;
		$criteria['posts_per_page'] = $limit;

		$posts = get_posts($criteria);

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
	 * Save Model to the database
	 *
	 * @param Model $model
	 */
	public function persist(Model &$model){
		$metaList = $model->getMetaList();
		//todo: persist with wp_insert_post... but... so the model will be available only for Post and PostType? Not for users?
	}

	/**
	 * @param array $criteria (@see https://codex.wordpress.org/Class_Reference/WP_Query#Parameters)
	 * @param int|null $page
	 * @param string $returnType
	 *
	 * @return \WP_Query
	 */
	public function getQuery( array $criteria = [], $page = null, $returnType = self::FIND_ALL_IDS )
	{
		$criteria['post_type'] = $this->getPostType();
		if($returnType === self::FIND_ALL_IDS){
			$criteria['fields'] = 'ids';
		}

		if($page !== null){
			$criteria['paged'] = (int) $page;
		}

		$q = new \WP_Query($criteria);

		return $q;
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
	 * Loads model metadata from the DB
	 *
	 * @param Model $model
	 */
	public function loadModelMetaData(Model &$model)
	{
		$metas = $model->getMetaList(true);
		if(!\is_array($metas) || count($metas) == 0){
			return;
		}
		foreach ($metas as $meta){
			if(!$meta instanceof MetaData) continue;
			switch($meta->getType()){
				case MetaData::TYPE_POSTMETA:
					$model->{$meta->getSetterMethodName()}(get_post_meta($model->getId(),$meta->getKey(),true));
					break;
				case MetaData::TYPE_TERMMETA:
					$model->{$meta->getSetterMethodName()}(get_term_meta($model->getId(),$meta->getKey(),true));
					break;
				case MetaData::TYPE_USERMETA:
					$model->{$meta->getSetterMethodName()}(get_user_meta($model->getId(),$meta->getKey(),true));
					break;
			}
		}
	}

	/**
	 * @param $modelClassName
	 *
	 * @return RepositoryInterface
	 * @throws ModelException
	 */
	public static function get($modelClassName)
	{
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