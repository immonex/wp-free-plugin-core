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

	public function test_encode_special_chars() {
		$source   = 'foo [bar] \'baz\' "SNAFU"! test';
		$expected = 'foo -!SQBL!-bar-!SQBR!- -!SQT!-baz-!SQT!- -!DQT!-SNAFU-!DQT!-! test';
		$this->assertEquals( $expected, ( "{$this->ns}\String_Utils" )::encode_special_chars( $source ) );

		$source   = [
			'foo [bar] \'baz\'',
			' "SNAFU"! test',
		];
		$expected = [
			'foo -!SQBL!-bar-!SQBR!- -!SQT!-baz-!SQT!-',
			' -!DQT!-SNAFU-!DQT!-! test',
		];
		$this->assertEquals( $expected, ( "{$this->ns}\String_Utils" )::encode_special_chars( $source ) );
	} // test_encode_special_chars

	public function test_decode_special_chars() {
		$source   = 'foo -!GRAV!-bar-!GRAV!- -!ANBL!-baz-!ANBR!- -!ROBL!-SNAFU-!ROBR!-! -!BSL!-test';
		$expected = 'foo `bar` <baz> (SNAFU)! \test';
		$this->assertEquals( $expected, ( "{$this->ns}\String_Utils" )::decode_special_chars( $source ) );

		$source   = [
			'A' => 'foo -!GRAV!-bar-!GRAV!- -!ANBL!-baz-!ANBR!-',
			'B' => ' -!ROBL!-SNAFU-!ROBR!-! -!BSL!-test',
		];
		$expected = [
			'A' => 'foo `bar` <baz>',
			'B' => ' (SNAFU)! \test',
		];
		$this->assertEquals( $expected, ( "{$this->ns}\String_Utils" )::decode_special_chars( $source ) );
	} // test_encode_special_chars

	public function test_shorten_paths() {
		$source   = '/long/path/to/wp-content/uploads/filename.txt';
		$expected = '…wp-content/uploads/filename.txt';
		$this->assertEquals( $expected, ( "{$this->ns}\String_Utils" )::shorten_paths( $source ) );

		$source   = '/long/path/to/wp-content/uploads/filename.txt';
		$expected = '…/uploads/filename.txt';
		$this->assertEquals( $expected, ( "{$this->ns}\String_Utils" )::shorten_paths( $source, '/uploads/' ) );

		$source   = '/long/path/to/wp-content/uploads/filename.txt';
		$expected = '.../uploads/filename.txt';
		$this->assertEquals( $expected, ( "{$this->ns}\String_Utils" )::shorten_paths( $source, '/uploads/', '...' ) );
	} // test_shorten_paths

	public function test_get_bytes() {
		$this->assertEquals( 0, ( "{$this->ns}\String_Utils" )::get_bytes( '0' ) );
		$this->assertEquals( 0, ( "{$this->ns}\String_Utils" )::get_bytes( '0B' ) );
		$this->assertEquals( 0, ( "{$this->ns}\String_Utils" )::get_bytes( '0b' ) );
		$this->assertEquals( 0, ( "{$this->ns}\String_Utils" )::get_bytes( '0K' ) );
		$this->assertEquals( 0, ( "{$this->ns}\String_Utils" )::get_bytes( '0k' ) );

		$this->assertEquals( 1, ( "{$this->ns}\String_Utils" )::get_bytes( '1' ) );
		$this->assertEquals( 1, ( "{$this->ns}\String_Utils" )::get_bytes( '1B' ) );
		$this->assertEquals( 1, ( "{$this->ns}\String_Utils" )::get_bytes( '1b' ) );
		$this->assertEquals( 1024, ( "{$this->ns}\String_Utils" )::get_bytes( '1K' ) );
		$this->assertEquals( 1024, ( "{$this->ns}\String_Utils" )::get_bytes( '1k' ) );
		$this->assertEquals( 1048576, ( "{$this->ns}\String_Utils" )::get_bytes( '1M' ) );
		$this->assertEquals( 1048576, ( "{$this->ns}\String_Utils" )::get_bytes( '1m' ) );
		$this->assertEquals( '1.048.576', ( "{$this->ns}\String_Utils" )::get_bytes( '1m', true ) );
		$this->assertEquals( 1073741824, ( "{$this->ns}\String_Utils" )::get_bytes( '1G' ) );
		$this->assertEquals( '1.073.741.824', ( "{$this->ns}\String_Utils" )::get_bytes( '1G', true ) );
	} // test_get_bytes

} // class String_Utils_Test
