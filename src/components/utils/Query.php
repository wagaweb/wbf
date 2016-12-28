<?php
namespace WBF\components\utils;


class Query {
	const PAGE_TYPE_DEFAULT_HOME = "default_home";
	const PAGE_TYPE_STATIC_HOME = "static_home";
	const PAGE_TYPE_BLOG_PAGE = "blog_page";
	const PAGE_TYPE_COMMON = "common";

	/**
	 * Get the current page type. Can be "default_home" | "static_home" | "blog_page" | "common"
	 *
	 * @return string
	 */
	static function get_current_page_type(){
		if ( is_front_page() && is_home() ) {
			// Default homepage
			return self::PAGE_TYPE_DEFAULT_HOME;
		} elseif ( is_front_page() ) {
			// static homepage
			return self::PAGE_TYPE_STATIC_HOME;
		} elseif ( is_home() ) {
			// blog page
			return self::PAGE_TYPE_BLOG_PAGE;
		} else {
			//everything else
			return self::PAGE_TYPE_COMMON;
		}
	}

	/**
	 * Return TRUE when the default home page in displayed
	 *
	 * @return bool
	 */
	static function is_default_home(){
		return self::get_current_page_type() == self::PAGE_TYPE_DEFAULT_HOME;
	}

	/**
	 * Return TRUE when the static home page in displayed
	 *
	 * @return bool
	 */
	static function is_static_home(){
		return self::get_current_page_type() == self::PAGE_TYPE_STATIC_HOME;
	}

	/**
	 * Return TRUE when the user defined blog page in displayed
	 *
	 * @return bool
	 */
	static function is_blog_page(){
		return self::get_current_page_type() == self::PAGE_TYPE_BLOG_PAGE;
	}

	/**
	 * Return TRUE when a common page page in displayed
	 *
	 * @return bool
	 */
	static function is_common_page(){
		return self::get_current_page_type() == self::PAGE_TYPE_COMMON;
	}
}