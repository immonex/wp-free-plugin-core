<?php
/**
 * Class Test_Plugin
 *
 * @package myimmonex\TestPlugin
 */

namespace myimmonex\TestPlugin;

/**
 * Main test plugin class.
 */
class Test_Plugin extends \immonex\WordPressFreePluginCore\DEV_6\Base {

	const PLUGIN_NAME    = 'My immonex Test Plugin';
	const PLUGIN_PREFIX  = 'testplugin_';
	const PUBLIC_PREFIX  = 'testplugin-';
	const PLUGIN_VERSION = '1.0.0';

	protected
		$plugin_options = array(
			'plugin_version' => self::PLUGIN_VERSION,
		);

} // class Test_Plugin
