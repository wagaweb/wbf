<?php

namespace WBF\components\breadcrumb;

interface BreadcrumbInterface{
	/**
	 * @param BreadcrumbItemInterface $item
	 */
	public function addItem(BreadcrumbItemInterface $item);
	/**
	 * @return array
	 */
	public function getItems();
	/**
	 * @param $items
	 */
	public function setItems($items);
}