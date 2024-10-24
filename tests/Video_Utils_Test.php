<?php
/**
 * Unit tests for Video_Utils class.
 */

use myimmonex\TestPlugin\Test_Plugin;

class Video_Utils_Test extends WP_UnitTestCase {

	private $ns;

	public function setUp() {
		$test_plugin = new Test_Plugin( 'immonex-test-plugin' );
		$base_class  = get_parent_class( $test_plugin );
		$reflection  = new \ReflectionClass( $base_class );
		$this->ns    = $reflection->getNamespaceName();
	} // setUp

	public function test_embed_url() {
		$source   = 'https://youtube.com/watch?v=vwtIMTCHWuI';
		$expected = 'https://www.youtube.com/embed/vwtIMTCHWuI?feature=oembed';
		$this->assertEquals( $expected, ( "{$this->ns}\Video_Utils" )::get_embed_url( $source ) );

		$expected = 'https://www.youtube-nocookie.com/embed/vwtIMTCHWuI?feature=oembed';
		$this->assertEquals( $expected, ( "{$this->ns}\Video_Utils" )::get_embed_url( $source, [ 'youtube-nocookie' => true ] ) );

		$source   = 'https://youtube-nocookie.com/watch?v=vwtIMTCHWuI';
		$this->assertEquals( $expected, ( "{$this->ns}\Video_Utils" )::get_embed_url( $source ) );

		$source   = 'https://vimeo.com/566031681';
		$expected = 'https://player.vimeo.com/video/566031681';
		$this->assertEquals( $expected, ( "{$this->ns}\Video_Utils" )::get_embed_url( $source ) );

		$source   = 'https://vimeo.com/508864124/fcbbcc92fa';
		$expected = 'https://player.vimeo.com/video/508864124?h=fcbbcc92fa';
		$this->assertEquals( $expected, ( "{$this->ns}\Video_Utils" )::get_embed_url( $source ) );

		$source   = 'https://vimeo.com/508864124?h=fcbbcc92fa';
		$this->assertEquals( $expected, ( "{$this->ns}\Video_Utils" )::get_embed_url( $source ) );

		$source   = 'https://vimeo.com/508864124/fcbbcc92fa';
		$expected = 'https://player.vimeo.com/video/508864124?h=fcbbcc92fa#t=60';
		$this->assertEquals( $expected, ( "{$this->ns}\Video_Utils" )::get_embed_url( $source, [ 'time' => 60 ] ) );
	} // test_embed_url

} // class Video_Utils_Test
