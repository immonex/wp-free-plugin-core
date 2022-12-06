<?php
/**
 * Unit tests for Local_FS_Utils class.
 */

use myimmonex\TestPlugin\Test_Plugin;

class Local_FS_Utils_Test extends WP_UnitTestCase {

	private $class;
	private $util;

	public function setUp() {
		$test_plugin = new Test_Plugin( 'immonex-test-plugin' );
		$base_class  = get_parent_class( $test_plugin );
		$reflection  = new \ReflectionClass( $base_class );
		$ns          = $reflection->getNamespaceName();
		$this->class = "{$ns}\Local_FS_Utils";
		$this->util = new $this->class();
	} // setUp

	public function test_get_filtered_file_list_by_name_asc() {
		$params = [
			'file_extensions' => [ 'csV' ],
			'return_paths'    => true,
		];

		$expected = [
			__DIR__ . '/data/csv_test_1.csv',
			__DIR__ . '/data/csv_test_2.csv',
		];
		$this->assertEquals( $expected, $this->util->scan_dir( __DIR__ . '/data', $params ) );

		$params['file_extensions'] = [ 'csv' ];
		$params['max_depth']       = 1;

		$expected = [
			__DIR__ . '/data/csv_test_1.csv',
			__DIR__ . '/data/csv_test_2.csv',
			__DIR__ . '/data/subfolder_1/csv_test_3.csv',
			__DIR__ . '/data/subfolder_3/csv_test_4.csv',
		];
		$this->assertEquals( $expected, $this->util->scan_dir( __DIR__ . '/data', $params ) );

		$params['file_extensions'] = [ 'CSV' ];
		$params['max_depth']       = 3;

		$expected = [
			__DIR__ . '/data/csv_test_1.csv',
			__DIR__ . '/data/csv_test_2.csv',
			__DIR__ . '/data/subfolder_1/csv_test_3.csv',
			__DIR__ . '/data/subfolder_2/subfolder_2_1/csv_test_5.csv',
			__DIR__ . '/data/subfolder_3/csv_test_4.csv',
		];
		$this->assertEquals( $expected, $this->util->scan_dir( __DIR__ . '/data', $params ) );

		$params['exclude']   = [ 'subfolder_1' ];
		$params['scope']     = 'files_and_folders';
		$params['max_depth'] = 1;

		$expected = [
			__DIR__ . '/data/csv_test_1.csv',
			__DIR__ . '/data/csv_test_2.csv',
			__DIR__ . '/data/subfolder_2',
			__DIR__ . '/data/subfolder_2/subfolder_2_1',
			__DIR__ . '/data/subfolder_3',
			__DIR__ . '/data/subfolder_3/csv_test_4.csv',
		];
		$this->assertEquals( $expected, $this->util->scan_dir( __DIR__ . '/data', $params ) );
	} // test_get_filtered_file_list_by_name_asc

	public function test_get_filtered_file_list_by_name_desc() {
		$params = [
			'file_extensions' => [ 'csV' ],
			'return_paths'    => true,
			'order_by'        => 'filename desc',
		];

		$expected = [
			__DIR__ . '/data/csv_test_2.csv',
			__DIR__ . '/data/csv_test_1.csv',
		];
		$this->assertEquals( $expected, $this->util->scan_dir( __DIR__ . '/data', $params ) );

		$params['file_extensions'] = [ 'csv' ];
		$params['max_depth']       = 1;

		$expected = [
			__DIR__ . '/data/subfolder_3/csv_test_4.csv',
			__DIR__ . '/data/subfolder_1/csv_test_3.csv',
			__DIR__ . '/data/csv_test_2.csv',
			__DIR__ . '/data/csv_test_1.csv',
		];
		$this->assertEquals( $expected, $this->util->scan_dir( __DIR__ . '/data', $params ) );

		$params['file_extensions'] = [ 'CSV' ];
		$params['max_depth']       = 3;

		$expected = [
			__DIR__ . '/data/subfolder_3/csv_test_4.csv',
			__DIR__ . '/data/subfolder_2/subfolder_2_1/csv_test_5.csv',
			__DIR__ . '/data/subfolder_1/csv_test_3.csv',
			__DIR__ . '/data/csv_test_2.csv',
			__DIR__ . '/data/csv_test_1.csv',
		];
		$this->assertEquals( $expected, $this->util->scan_dir( __DIR__ . '/data', $params ) );
	} // test_get_filtered_file_list_by_name_desc

	public function test_get_wildcard_filtered_list() {
		$params = [
			'file_extensions' => [ 'csv' ],
			'return_paths'    => true,
			'max_depth'       => 2,
			'exclude'         => [
				'sub*',
			],
		];

		$expected = [
			__DIR__ . '/data/csv_test_1.csv',
			__DIR__ . '/data/csv_test_2.csv',
		];
		$this->assertEquals( $expected, $this->util->scan_dir( __DIR__ . '/data', $params ) );

	} // test_get_wildcard_filtered_list

	public function test_get_regex_filtered_list() {
		$params = [
			'file_extensions'             => [ 'csv' ],
			'return_paths'                => true,
			'max_depth'                   => 2,
			'exclude'                     => [
				'/^subfolder_[0-9]_[0-9]/',
			],
			'apply_exclude_in_subfolders' => true,
		];

		$expected = [
			__DIR__ . '/data/csv_test_1.csv',
			__DIR__ . '/data/csv_test_2.csv',
			__DIR__ . '/data/subfolder_1/csv_test_3.csv',
			__DIR__ . '/data/subfolder_3/csv_test_4.csv',
		];
		$this->assertEquals( $expected, $this->util->scan_dir( __DIR__ . '/data', $params ) );
	} // test_get_regex_filtered_list

	public function test_get_folder_list() {
		$params = [
			'scope'        => 'folders',
			'return_paths' => true,
		];

		$expected = [
			__DIR__ . '/data/subfolder_1',
			__DIR__ . '/data/subfolder_2',
			__DIR__ . '/data/subfolder_3',
		];
		$this->assertEquals( $expected, $this->util->scan_dir( __DIR__ . '/data', $params ) );

		$params['exclude']   = 'subfolder_1';
		$params['max_depth'] = 1;

		$expected = [
			__DIR__ . '/data/subfolder_2',
			__DIR__ . '/data/subfolder_2/subfolder_2_1',
			__DIR__ . '/data/subfolder_3',
		];
		$this->assertEquals( $expected, $this->util->scan_dir( __DIR__ . '/data', $params ) );
	} // test_get_folder_list

	public function test_get_filtered_file_list_by_mtime_asc() {
		$params = [
			'file_extensions' => [ 'csv' ],
			'return_paths'    => true,
			'max_depth'       => 3,
			'order_by'        => 'mtime asc',
		];

		$expected = [
			__DIR__ . '/data/csv_test_1.csv', // 2022-11-21 15:06
			__DIR__ . '/data/subfolder_2/subfolder_2_1/csv_test_5.csv', // 2022-11-21 15:12
			__DIR__ . '/data/subfolder_1/csv_test_3.csv', // 2022-11-25 13:01
			__DIR__ . '/data/subfolder_3/csv_test_4.csv', // 2022-11-25 13:01
			__DIR__ . '/data/csv_test_2.csv', // 2022-11-25 13:03
		];
		$this->assertEquals( $expected, $this->util->scan_dir( __DIR__ . '/data', $params ) );
	} // test_get_filtered_file_list_by_mtime_asc

	public function test_get_file_list_of_multiple_folders() {
		$directories = [
			__DIR__ . '/data/subfolder_2',
			__DIR__ . '/data/subfolder_3',
		];
		$params      = [
			'file_extensions' => [ 'csv' ],
			'return_paths'    => true,
			'max_depth'       => 1,
			'order_by'        => 'mtime desc',
		];

		$expected = [
			__DIR__ . '/data/subfolder_3/csv_test_4.csv', // 2022-11-25 13:01
			__DIR__ . '/data/subfolder_2/subfolder_2_1/csv_test_5.csv', // 2022-11-21 15:12
		];
		$this->assertEquals( $expected, $this->util->scan_dir( $directories, $params ) );
	} // test_get_file_list_of_multiple_folders

	function test_get_file_mtime() {
		$temp_file_1 = __DIR__ . '/data/temp.dat';
		$temp_handle = fopen( $temp_file_1, 'w+' );
		fclose( $temp_handle );

		$temp_file_2 = __DIR__ . '/data/20221031_1403_temp.dat';
		$temp_handle = fopen( $temp_file_2, 'w+' );
		fclose( $temp_handle );

		$expected_filesystem_ts = filemtime( $temp_file_1 );
		$expected_filename_ts   = strtotime( '2022-10-31 14:03:00' );

		$this->assertEquals( $expected_filesystem_ts, $this->util->get_mtime( $temp_file_1 ) );
		$this->assertFalse( $this->util->get_mtime( $temp_file_1, 'only' ) );

		$object = new \SplFileInfo( $temp_file_2 );
		$this->assertEquals( $expected_filename_ts, $this->util->get_mtime( $object ) );
		$this->assertEquals( $expected_filesystem_ts, $this->util->get_mtime( $object, '' ) );

		unlink( $temp_file_1 );
		unlink( $temp_file_2 );
	} // test_get_file_mtime

} // class Local_FS_Utils_Test
