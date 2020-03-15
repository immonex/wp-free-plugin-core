<?php
/**
 * Class Test_Plugin
 *
 * @package immonex-test-plugin
 */

namespace myimmonex\TestPlugin;

/**
 * Main test plugin class.
 */
class Test_Plugin extends \immonex\WordPressFreePluginCore\V1_0\Base {

	const
		PLUGIN_NAME = 'immonex Test Plugin',
		PLUGIN_PREFIX = 'testplugin_',
		PUBLIC_PREFIX = 'testplugin-',
		PLUGIN_VERSION = '1.0.0';

	protected
		$plugin_options = array(
			'plugin_version' => self::PLUGIN_VERSION
		);

} // class Test_Plugin
