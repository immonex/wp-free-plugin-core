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
		$this->assertEquals( -23, $this->util->validate_coords( -23, 'lat' ) );
		$this->assertFalse( $this->util->validate_coords( -98, 'lat' ) );
		$this->assertFalse( $this->util->validate_coords( '91.123 bar', 'lat' ) );

		$this->assertEquals( -123.6784, $this->util->validate_coords( 'foo -123.6784 bar', 'lng' ) );
		$this->assertEquals( 58.123, $this->util->validate_coords( '58.123 bar', 'lng' ) );
		$this->assertEquals( 23.987, $this->util->validate_coords( 23.987, 'lng' ) );
		$this->assertEquals( -123, $this->util->validate_coords( -123, 'lng' ) );
		$this->assertFalse( $this->util->validate_coords( -181, 'lng' ) );
		$this->assertFalse( $this->util->validate_coords( '191.123 bar', 'lng' ) );

		$this->assertEquals( '58.123,-123.6784', $this->util->validate_coords( 'foo 58.123, ! -123.6784 bar' ) );
		$this->assertFalse( $this->util->validate_coords( 'foo 98.123, ! -123.6784 bar', 'coords' ) );
		$this->assertFalse( $this->util->validate_coords( 'foo 58.123, ! -181.6784 bar', 'coords' ) );
	} // test_validate_coords

} // class Geo_Utils_Test
