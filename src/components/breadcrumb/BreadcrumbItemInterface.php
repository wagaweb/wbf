<?php

namespace WBF\components\breadcrumb;

interface BreadcrumbItemInterface{
	/**
	 * @return string
	 */
	public function getLabel();
	/**
	 * @param string $label
	 */
	public function setLabel($label);
	/**
	 * @return string
	 */
	public function getLink();
	/**
	 * @param string $link
	 */
	public function setLink($link);
}