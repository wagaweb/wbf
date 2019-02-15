<?php

namespace WBF\components\breadcrumb;

abstract class Breadcrumb implements BreadcrumbInterface {
	/**
	 * @var ItemListIterator
	 */
	protected $items;

	/**
	 * @param BreadcrumbItemInterface $item
	 */
	public function addItem(BreadcrumbItemInterface $item){
		$this->items[] = $item;
	}

	/**
	 * @return ItemListIterator
	 */
	public function getItems(){
		return $this->items;
	}

	/**
	 * @param ItemListIterator $items
	 *
	 * @throws BreadcrumbException
	 */
	public function setItems($items){
		if(!$items instanceof \Iterator){
			throw new BreadcrumbException('Invalid items list provided');
		}
		$this->items = $items;
	}
}