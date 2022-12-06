<?php
/**
 * Unit tests for String_Utils class.
 */

use myimmonex\TestPlugin\Test_Plugin;

class String_Utils_Test extends WP_UnitTestCase {

	private $ns;

	public function setUp() {
		$test_plugin = new Test_Plugin( 'immonex-test-plugin' );
		$base_class  = get_parent_class( $test_plugin );
		$reflection  = new \ReflectionClass( $base_class );
		$this->ns    = $reflection->getNamespaceName();
	} // setUp

	public function test_leading_timestamp() {
		$expected = strtotime( '2022-10-08 13:30:12' );

		$filename = '20221008_133012_test.csv';
		$this->assertEquals( $expected, ( "{$this->ns}\String_Utils" )::get_leading_timestamp( $filename ) );

		$filename = '/path/to/2022-10-08_13-30-12_test.csv';
		$this->assertEquals( $expected, ( "{$this->ns}\String_Utils" )::get_leading_timestamp( $filename ) );

		$expected = strtotime( '2022-10-18 00:00:00' );
		$filename = '/path/to/2022-10-18_24-60-123_test.csv';
		$this->assertEquals( $expected, ( "{$this->ns}\String_Utils" )::get_leading_timestamp( $filename ) );

		$expected = strtotime( '2022-10-18 14:40:00' );
		$filename = '/path/to/2022-10-18_14-40 test.csv';
		$this->assertEquals( $expected, ( "{$this->ns}\String_Utils" )::get_leading_timestamp( $filename ) );

		$filename = '1499-10-08_01-01x00';
		$this->assertFalse( ( "{$this->ns}\String_Utils" )::get_leading_timestamp( $filename ) );

		$filename = '2299-10-08 01:23:24';
		$this->assertFalse( ( "{$this->ns}\String_Utils" )::get_leading_timestamp( $filename ) );
	} // test_leading_timestamp

} // class String_Utils_Test
