<?php
/**
 * Unit tests for Geo_Utils class.
 */

use myimmonex\TestPlugin\Test_Plugin;

class Geo_Utils_Test extends WP_UnitTestCase {

	private $class;
	private $util;

	public function setUp(): void {
		$test_plugin = new Test_Plugin( 'immonex-test-plugin' );
		$base_class  = get_parent_class( $test_plugin );
		$reflection  = new \ReflectionClass( $base_class );
		$ns          = $reflection->getNamespaceName();
		$this->class = "{$ns}\Geo_Utils";
		$this->util = new $this->class();
	} // setUp

	function test_validate_coords() {
		$this->assertEquals( -23.6784, $this->util->validate_coords( 'foo-23.6784 bar', 'lat' ) );
		$this->assertEquals( 58.123, $this->util->validate_coords( '58.123 bar', 'lat' ) );
		$this->assertEquals( 23.987, $this->util->validate_coords( 23.987, 'lat' ) );
		$this->assertEquals( 53.210242, $this->util->validate_coords( '47/2:53.210242', 'lat' ) );
		$this->assertEquals( -23, $this->util->validate_coords( '-23.0', 'lat' ) );
		$this->assertFalse( $this->util->validate_coords( '-23', 'lat' ) );
		$this->assertFalse( $this->util->validate_coords( -98, 'lat' ) );
		$this->assertFalse( $this->util->validate_coords( '91.123 bar', 'lat' ) );

		$this->assertEquals( -123.6784, $this->util->validate_coords( 'foo -123.6784 bar', 'lng' ) );
		$this->assertEquals( 58.123, $this->util->validate_coords( '58.123 bar', 'lng' ) );
		$this->assertEquals( 23.987, $this->util->validate_coords( 23.987, 'lng' ) );
		$this->assertEquals( 7.406623, $this->util->validate_coords( '7.406623 - 248/8:53.263831,7.514415 - 286/1:53.264098, 7.514749', 'lng' ) );
		$this->assertEquals( -123, $this->util->validate_coords( -123, 'lng' ) );
		$this->assertFalse( $this->util->validate_coords( -181, 'lng' ) );
		$this->assertFalse( $this->util->validate_coords( '191.123 bar', 'lng' ) );

		$this->assertEquals( '58.123,-123.6784', $this->util->validate_coords( 'foo 58.123, ! -123.6784 bar' ) );
		$this->assertEquals( '58.123,-123.6784', $this->util->validate_coords( '47 foo 58.123, ! -123.6784 bar' ) );
		$this->assertFalse( $this->util->validate_coords( 'foo 98.123, ! -123.6784 bar', 'coords' ) );
		$this->assertFalse( $this->util->validate_coords( 'foo 58.123, ! -181.6784 bar', 'coords' ) );
	} // test_validate_coords

	function test_convert_iso_country_code() {
		$this->assertEquals( 'DE', $this->util->convert_iso_country_code( 'DEU' ) );
		$this->assertEquals( 'DEU', $this->util->convert_iso_country_code( 'DE' ) );
		$this->assertEquals( 'ES', $this->util->convert_iso_country_code( 'esp' ) );
		$this->assertEquals( 'AD', $this->util->convert_iso_country_code( 'And' ) );
	} // test_convert_iso_country_code

} // class Geo_Utils_Test
