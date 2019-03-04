<?php

namespace WBF\components\mvc;

interface RepositoryInterface
{
	const FIND_ALL_IDS = 'ids';
	const FIND_ALL_OBJECT = 'objects';

	/**
	 * @param $id
	 *
	 * @return object
	 */
	public function find($id);

	/**
	 * @param string $returnType
	 *
	 * @return array
	 */
	public function findAll($returnType);

	/**
	 * @param array $criteria
	 * @param array|null $orderBy
	 * @param int|null $limit
	 * @param int|null $offset
	 * @param string $returnType
	 *
	 * @return array
	 */
	public function findByParams(array $criteria, array $orderBy, $limit, $offset, $returnType);
}