<?php
/**
 * Plugin Name: My immonex Example Plugin
 * Description: A minimal immonex plugin skeleton
 * Version: 1.0.0
 * Author: immonex
 * Author URI: https://immonex.dev/
 * Text Domain: myimmonex-example-plugin
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package myimmonex\ExamplePlugin
 */

namespace myimmonex\ExamplePlugin;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize autoloaders (Composer (optional) AND WP/plugin-specific).
 */
require_once __DIR__ . '/autoload.php';

$myimmonex_example_plugin = new Example_Plugin( basename( __FILE__, '.php' ) );
$myimmonex_example_plugin->init();
