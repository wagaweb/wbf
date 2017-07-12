<?php

namespace WBF\components\forms;

/**
 * Interface FormInterface
 * @package WBF\components\forms
 */
interface Form_Interface {

	/**
	 * @param string $type
	 * @param array $params
	 */
	function add(string $type, array $params);

	/**
	 * @return void
	 */
	function printFormStart();

	/**
	 * @return void
	 */
	function printFormEnd();

	/**
	 * @return void
	 */
	function printForm();

	/**
	 * @param string $id
	 *
	 * @return void
	 */
	function printInput(string $id);

	/**
	 * @param string $id
	 *
	 * @return void
	 */
	function printLabel(string $id);

	/**
	 * @param string $id
	 *
	 * @return void
	 */
	function printField(string $id);

	/**
	 * @return boolean
	 */
	function isSubmitted();

	/**
	 * @return boolean
	 */
	function isValid();
}