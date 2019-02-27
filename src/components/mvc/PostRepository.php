<?php

namespace WBF\components\mvc;

class PostRepository extends Repository
{
	public function __construct()
	{
		$this->setClassName('WP_Post');
		$this->setPostType('post');
	}
}