<?php

namespace WBF\components\forms;


use WBF\components\forms\fields\Checkbox;
use WBF\components\forms\fields\Field;
use WBF\components\forms\fields\MultiCheckbox;
use WBF\components\forms\fields\Radio;
use WBF\components\forms\fields\Select;
use WBF\components\forms\fields\Text;

class Form extends AbstractForm implements FormInterface {

	/**
	 * @inheritDoc
	 */
	public function add(string $type, array $params){
		// TODO: Implement add() method.
	}

	/**
	 * @inheritDoc
	 */
	function printFormStart() {
		// TODO: Implement printFormStart() method.
	}

	/**
	 * @inheritDoc
	 */
	function printFormEnd() {
		// TODO: Implement printFormEnd() method.
	}

	/**
	 * @inheritDoc
	 */
	function printForm() {
		// TODO: Implement printForm() method.
	}

	/**
	 * @inheritDoc
	 */
	function printInput( string $id ) {
		// TODO: Implement printInput() method.
	}

	/**
	 * @inheritDoc
	 */
	function printLabel( string $id ) {
		// TODO: Implement printLabel() method.
	}

	/**
	 * @inheritDoc
	 */
	function printField( string $id ) {
		// TODO: Implement printField() method.
	}

	/**
	 * @inheritDoc
	 */
	function isSubmitted() {
		// TODO: Implement isSubmitted() method.
	}

	/**
	 * @inheritDoc
	 */
	function isValid() {
		// TODO: Implement isValid() method.
	}


	/**
	 * @param array $params
	 */
	private function addText(array $params){
		$this->add(Text::class, $params);
	}

	/**
	 * @param array $params
	 */
	private function addCheckbox(array $params){
		$this->add(Checkbox::class, $params);
	}

	/**
	 * @param array $params
	 */
	private function addMulticheckbox(array $params){
		$this->add(MultiCheckbox::class, $params);
	}

	/**
	 * @param array $params
	 */
	private function addRadio(array $params){
		$this->add(Radio::class, $params);
	}

	/**
	 * @param array $params
	 */
	private function addSelect(array $params){
		$this->add(Select::class, $params);
	}
}