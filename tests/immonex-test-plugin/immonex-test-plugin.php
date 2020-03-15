<?php
/**
 * Plugin Name: immonex Test Plugin
 * Description: Minimal plugin skeleton for testing purposes
 *
 * @package immonex-test-plugin
 */

namespace myimmonex\TestPlugin;

// NO extra autoloader required here...
require __DIR__ . '/includes/class-test-plugin.php';

$immonex_test_plugin = new Test_Plugin( basename( __FILE__, '.php' ) );
$immonex_test_plugin->init();
