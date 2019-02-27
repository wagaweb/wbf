<?php

namespace WBF\components\mvc;

interface RepositoryInterface
{
	/**
	 * @param $id
	 *
	 * @return object
	 */
	public function find($id);

	/**
	 * @return array
	 */
	public function findAll();
}