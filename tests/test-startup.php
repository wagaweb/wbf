<?php
/**
 * Class SampleTest
 *
 * @package Wbf
 */

/**
 * Sample test case.
 */
class StartupTest extends WP_UnitTestCase {

	public function setUp(){
		parent::setUp();
	}

	/**
	 * Testing WBF function
	 */
	function test_global_object() {
		$this->assertTrue( function_exists('WBF') );

		$wbf = WBF();
		$this->assertTrue( $wbf instanceof \WBF\PluginCore );
	}
}
