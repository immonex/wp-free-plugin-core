<?php
/**
 * Unit tests for Test_Plugin class.
 */

use myimmonex\TestPlugin\Test_Plugin;

class My_Plugin_Test extends WP_UnitTestCase {

	private $test_plugin;

	public function setUp() {
		$this->test_plugin = new Test_Plugin( 'immonex-test-plugin' );

		$this->test_plugin->register_plugin_settings();
	} // setUp

	public function test_bootstrap_data() {
		$expected = [
			'plugin_name'   => 'My immonex Test Plugin',
			'plugin_slug'   => 'immonex-test-plugin',
			'plugin_prefix' => 'testplugin_',
			'public_prefix' => 'testplugin-'
		];

		$bootstrap_data = $this->test_plugin->bootstrap_data;

		foreach ( $expected as $key => $expected_value ) {
			$this->assertEquals( $expected_value, $bootstrap_data[$key] );
		}
	} // test_bootstrap_data

	public function test_sanitize_plugin_options_float() {
		$input = [ 'foo_float' => '123,456' ];
		$expected = array_merge(
			$this->test_plugin->plugin_options,
			[ 'foo_float' => 123.456 ]
		);

		$this->assertEquals( $expected, $this->test_plugin->sanitize_plugin_options( $input ) );
	} // test_sanitize_plugin_options_float

	public function test_sanitize_text() {
		$compare = [
			[
				'in'  => [ 'foo_text' => 'test' ],
				'exp' => [ 'foo_text' => 'test' ],
			],
			[
				'in'  => [ 'foo_text' => 'foobar' ],
				'exp' => [ 'foo_text' => '' ],
			],
		];

		foreach ( $compare as $test ) {
			$input    = $test['in'];
			$expected = array_merge(
				$this->test_plugin->plugin_options,
				$test['exp']
			);
			$this->assertEquals( $expected, $this->test_plugin->sanitize_plugin_options( $input ) );
		}
	} // test_sanitize_page_id_or_url

	public function test_sanitize_plugin_options_lat_lon() {
		$compare = [
			[
				'in'  => [ 'foo_lat' => '95,278' ],
				'exp' => [ 'foo_lat' => 90 ],
			],
			[
				'in'  => [ 'foo_lat' => '55,278' ],
				'exp' => [ 'foo_lat' => 55.278 ],
			],
			[
				'in'  => [ 'foo_lon' => '-190,123' ],
				'exp' => [ 'foo_lon' => -180 ],
			],
			[
				'in'  => [ 'foo_lon' => '-179,123' ],
				'exp' => [ 'foo_lon' => -179.123 ],
			],
		];

		foreach ( $compare as $test ) {
			$input    = $test['in'];
			$expected = array_merge(
				$this->test_plugin->plugin_options,
				$test['exp']
			);
			$this->assertEquals( $expected, $this->test_plugin->sanitize_plugin_options( $input ) );
		}
	} // test_sanitize_plugin_options_float

	public function test_sanitize_page_id_or_url() {
		$compare = [
			[
				'in'  => [ 'foo_page_id_or_url' => '999' ],
				'exp' => [ 'foo_page_id_or_url' => 999 ],
			],
			[
				'in'  => [ 'foo_page_id_or_url' => 'https://immonex.de/?t=1&v=x' ],
				'exp' => [ 'foo_page_id_or_url' => 'https://immonex.de/?t=1&v=x' ],
			],
		];

		foreach ( $compare as $test ) {
			$input    = $test['in'];
			$expected = array_merge(
				$this->test_plugin->plugin_options,
				$test['exp']
			);
			$this->assertEquals( $expected, $this->test_plugin->sanitize_plugin_options( $input ) );
		}
	} // test_sanitize_page_id_or_url

} // class My_Plugin_Test
