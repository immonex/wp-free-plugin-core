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

		switch_to_locale( 'de_DE' );
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

		$expected = strtotime( '2022-10-08 13:12' );
		$filename = '20221008_1312_test.csv';
		$this->assertEquals( $expected, ( "{$this->ns}\String_Utils" )::get_leading_timestamp( $filename ) );
	} // test_leading_timestamp

	public function test_utc_to_local_time() {
		$utc_time   = '2023-10-18 12:00:00';
		$utc_ts     = 1697630400;
		$local_time = get_date_from_gmt( $utc_time );
		$local_ts   = strtotime( $local_time );

		$this->assertEquals( $local_ts, ( "{$this->ns}\String_Utils" )::utc_to_local_time( $utc_time ) );
		$this->assertEquals( $local_ts, ( "{$this->ns}\String_Utils" )::utc_to_local_time( $utc_ts ) );
		$this->assertEquals( $local_time, ( "{$this->ns}\String_Utils" )::utc_to_local_time( $utc_time, 'Y-m-d H:i:s' ) );
		$this->assertEquals( $local_time, ( "{$this->ns}\String_Utils" )::utc_to_local_time( $utc_ts, 'Y-m-d H:i:s' ) );
	} // test_utc_to_local_time

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

	public function test_get_plain_filename() {
		$this->assertEquals( 'filename.png', ( "{$this->ns}\String_Utils" )::get_plain_filename( 'filename-1.png' ) );
		$this->assertEquals( 'filename.png', ( "{$this->ns}\String_Utils" )::get_plain_filename( 'filename-12.png' ) );
		$this->assertEquals( 'filename.png', ( "{$this->ns}\String_Utils" )::get_plain_filename( 'filename-12-scaled.png' ) );
		$this->assertEquals( 'filename-12.png', ( "{$this->ns}\String_Utils" )::get_plain_filename( 'filename-12.png', 'counter+size' ) );
		$this->assertEquals( 'filename-12-scaled.png', ( "{$this->ns}\String_Utils" )::get_plain_filename( 'filename-12-scaled.png', 'counter+size' ) );
		$this->assertEquals( 'filename.png', ( "{$this->ns}\String_Utils" )::get_plain_filename( 'filename-640x480.png', 'counter+size' ) );
		$this->assertEquals( 'filename.png', ( "{$this->ns}\String_Utils" )::get_plain_filename( 'filename-640x480-scaled.png', 'counter+size' ) );
		$this->assertEquals( 'filename.png', ( "{$this->ns}\String_Utils" )::get_plain_filename( 'filename-12-640x480.png', 'counter+size' ) );
		$this->assertEquals( 'filename.png', ( "{$this->ns}\String_Utils" )::get_plain_filename( 'filename-12-640x480-scaled.png', 'counter+size' ) );
	} // test_get_plain_filename

	public function test_get_plain_unzip_folder_name() {
		$this->assertEquals( 'filename-testarchive', ( "{$this->ns}\String_Utils" )::get_plain_unzip_folder_name( 'filename Test!archive.zip' ) );
	} // get_plain_unzip_folder_name

	public function test_get_path_with_unified_directory_separators() {
		$this->assertEquals( '/path/to/dir', ( "{$this->ns}\String_Utils" )::unify_dirsep( '/path\to\dir', 0, '/' ) );
		$this->assertEquals( '\\path\\to\\dir', ( "{$this->ns}\String_Utils" )::unify_dirsep( '\\path/to/dir', 0, '\\' ) );
		$this->assertEquals( '/path/to/dir', ( "{$this->ns}\String_Utils" )::unify_dirsep( '/path\to\dir\\', -1, '/' ) );
		$this->assertEquals( '/path/to/dir/', ( "{$this->ns}\String_Utils" )::unify_dirsep( '/path\to\dir\\', 1, '/' ) );
		$this->assertEquals( '/path/to/dir/', ( "{$this->ns}\String_Utils" )::unify_dirsep( '/path\to\dir', 1, '/' ) );
		$this->assertEquals( '\\path\\to\\dir\\', ( "{$this->ns}\String_Utils" )::unify_dirsep( '/path/to/dir', 1, '\\' ) );
		$this->assertEquals( '\\path\\to\\dir\\', ( "{$this->ns}\String_Utils" )::unify_dirsep( '/path/to/dir/', 1, '\\' ) );
	} // test_get_path_with_unified_directory_separators

	public function test_smooth_round() {
		$value = 1895000;

		$this->assertEquals( 1900000, ( "{$this->ns}\String_Utils" )::smooth_round( $value ) );
		$this->assertEquals( 1890000, ( "{$this->ns}\String_Utils" )::smooth_round( $value, true ) );
	} // test_smooth_round

	public function test_format_number() {
		$this->assertEquals( '123,00', ( "{$this->ns}\String_Utils" )::format_number( 123 ) );
		$this->assertEquals( '123', ( "{$this->ns}\String_Utils" )::format_number( 123, 0 ) );
		$this->assertEquals( '123,10', ( "{$this->ns}\String_Utils" )::format_number( 123.1, 2 ) );
		$this->assertEquals( '123,1', ( "{$this->ns}\String_Utils" )::format_number( 123.1, 9 ) );
		$this->assertEquals( '123,1', ( "{$this->ns}\String_Utils" )::format_number( '123.1000', 9 ) );
		$this->assertEquals( '120.500,3', ( "{$this->ns}\String_Utils" )::format_number( 120500.3, 9 ) );
		$this->assertEquals( '120.500,4', ( "{$this->ns}\String_Utils" )::format_number( 'foo 120.500,40 bar', 9 ) );
		$this->assertEquals( '120.500', ( "{$this->ns}\String_Utils" )::format_number( '120.500,000', 9 ) );
		$this->assertEquals( '123,00 m²', ( "{$this->ns}\String_Utils" )::format_number( 123, 2, 'm²' ) );
		$this->assertEquals( '€ 123,00', ( "{$this->ns}\String_Utils" )::format_number( 123, 2, '€', [ 'unit_pos' => 'before' ] ) );
		$this->assertEquals( '123,00 €', ( "{$this->ns}\String_Utils" )::format_number( 123, 2, '€', [ 'unit_pos' => 'after', 'unit_sep' => ' ' ] ) );
		$this->assertEquals( 'not specified', ( "{$this->ns}\String_Utils" )::format_number( 0, 2, '€', [ 'if_zero' => 'not specified' ] ) );
		$this->assertEquals( '', ( "{$this->ns}\String_Utils" )::format_number( 0, 2, '€' ) );
	} // test_filter_detail_items_by_name

} // class String_Utils_Test
