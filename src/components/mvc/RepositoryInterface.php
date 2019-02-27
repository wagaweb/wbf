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
}