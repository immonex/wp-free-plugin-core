<?php
/**
 * Plugin Name: immonex Test Plugin
 * Description: Minimal plugin skeleton for testing purposes
 */

namespace myimmonex\TestPlugin;

// NO Composer autoload required here...
require ( __DIR__ . '/includes/Test_Plugin.php' );

$immonex_test_plugin = new Test_Plugin( basename( __FILE__, '.php' ) );
$immonex_test_plugin->init();
