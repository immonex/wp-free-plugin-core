<?php
/**
 * Unit tests for Plugin_Cache class.
 */

use myimmonex\TestPlugin\Test_Plugin;

class Plugin_Cache_Test extends WP_UnitTestCase {

	private $class;
	private $instance;

	public function setUp(): void {
		$test_plugin    = new Test_Plugin( 'immonex-test-plugin' );
		$base_class     = get_parent_class( $test_plugin );
		$reflection     = new \ReflectionClass( $base_class );
		$ns             = $reflection->getNamespaceName();
		$this->class    = "{$ns}\Plugin_Cache";
		$this->instance = new $this->class( $test_plugin->plugin_slug );
	} // setUp

	function test_read_write_cache() {
		$this->assertEquals( 'bar', $this->instance->get( 'bar', 'foo' ) );

		$this->instance->set( 'foo', 'baz' );
		$this->assertEquals( 'baz', $this->instance->get( '', 'foo' ) );

		$this->instance->flush( 'foo' );
		$this->assertFalse( $this->instance->get( null, 'foo' ) );

		$this->instance->set( 'foo', 'baz' );
		$this->instance->flush();
		$this->assertFalse( $this->instance->get( null, 'foo' ) );

		$this->assertEquals( '', $this->instance->get( '', 'foo' ) );
		$this->assertFalse( $this->instance->get( null, 'foo' ) );
	} // test_read_write_cache

} // class Plugin_Cache_Test
