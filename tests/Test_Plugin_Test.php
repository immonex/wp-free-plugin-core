<?php
/**
 * Unit tests for Test_Plugin class.
 */

use myimmonex\TestPlugin\Test_Plugin;

class My_Plugin_Test extends WP_UnitTestCase {
	private $test_plugin;

	public function setUp() {
		$this->test_plugin = new Test_Plugin( 'immonex-test-plugin' );
	} // setUp

	public function test_bootstrap_data() {
		$expected = array(
			'plugin_name' => 'My immonex Test Plugin',
			'plugin_slug' => 'immonex-test-plugin',
			'plugin_prefix' => 'testplugin_',
			'public_prefix' => 'testplugin-'
		);

		$bootstrap_data = $this->test_plugin->bootstrap_data;

		foreach ( $expected as $key => $expected_value ) {
			$this->assertEquals( $expected_value, $bootstrap_data[$key] );
		}
	} // test_bootstrap_data
} // class My_Plugin_Test
