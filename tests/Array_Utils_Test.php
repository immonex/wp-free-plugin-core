<?php
/**
 * Unit tests for Array_Utils class.
 */

use myimmonex\TestPlugin\Test_Plugin;

class Array_Utils_Test extends WP_UnitTestCase {

	private $ns;

	public function setUp(): void {
		$test_plugin = new Test_Plugin( 'immonex-test-plugin' );
		$base_class  = get_parent_class( $test_plugin );
		$reflection  = new \ReflectionClass( $base_class );
		$this->ns    = $reflection->getNamespaceName();
	} // setUp

	public function test_array_has() {
		$arr = [
			'foo'    => 'bar',
			'bar'    => '',
			'baz'    => '123',
			'foobar' => 'snafu',
		];

		$this->assertTrue( ( "{$this->ns}\Array_Utils" )::array_has( 'foo', $arr ) );
		$this->assertTrue( ( "{$this->ns}\Array_Utils" )::array_has( 'foo*', $arr ) );
		$this->assertTrue( ( "{$this->ns}\Array_Utils" )::array_has( '*bar', $arr ) );
		$this->assertFalse( ( "{$this->ns}\Array_Utils" )::array_has( 'foozar', $arr ) );
		$this->assertFalse( ( "{$this->ns}\Array_Utils" )::array_has( 'bar', $arr ) );
	} // test_array_has

} // class Video_Utils_Test
