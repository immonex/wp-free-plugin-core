<?php
/**
 * Plugin Name: My immonex Plugin
 * Description: A minimal immonex plugin skeleton
 * Version: 1.0.0
 * Author: immonex
 * Author URI: https://immonex.dev/
 * Text Domain: my-immonex-plugin
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace myimmonex\MyPlugin;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Initialize Composer autoloader and instantiate plugin main class.
 */
require __DIR__ . '/vendor/autoload.php';

$my_immonex_plugin = new My_Plugin( basename( __FILE__, '.php' ) );
$my_immonex_plugin->init();
