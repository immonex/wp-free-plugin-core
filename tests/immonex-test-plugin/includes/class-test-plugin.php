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
class Test_Plugin extends \immonex\WordPressFreePluginCore\V1_8_25\Base {

	const PLUGIN_NAME    = 'My immonex Test Plugin';
	const PLUGIN_PREFIX  = 'testplugin_';
	const PUBLIC_PREFIX  = 'testplugin-';
	const PLUGIN_VERSION = '1.0.0';

	protected $plugin_options = [
		'plugin_version'     => self::PLUGIN_VERSION,
		'foo_float'          => 1.23,
		'foo_lat'            => 49.86000855,
		'foo_lon'            => 6.791990062584404,
		'foo_page_id_or_url' => '',
		'foo_text'           => '',
	];

	/**
	 * Register plugin settings.
	 *
	 * @since 1.0.0
	 */
	public function register_plugin_settings() {
		parent::register_plugin_settings();

		$this->settings_helper->add_tab( 'tab_foo', 'Foo', '', [] );
		$this->settings_helper->add_section( 'section_bar', 'Bar', '', 'tab_foo' );

		$fields = apply_filters(
			"{$this->plugin_slug}_option_fields",
			[
				[
					'name'    => 'foo_float',
					'type'    => 'float',
					'label'   => 'Float Test',
					'section' => 'section_bar',
				],
				[
					'name'    => 'foo_lat',
					'type'    => 'lat',
					'label'   => 'Latitude Test',
					'section' => 'section_bar',
				],
				[
					'name'    => 'foo_lon',
					'type'    => 'lon',
					'label'   => 'Longitude Test',
					'section' => 'section_bar',
				],
				[
					'name'    => 'foo_page_id_or_url',
					'type'    => 'page_id_or_url',
					'label'   => 'Page ID or URL Test',
					'section' => 'section_bar',
				],
				[
					'name'    => 'foo_text',
					'type'    => 'text',
					'label'   => 'Text Test',
					'section' => 'section_bar',
					'args'    => [
						'exclude' => [ 'foobar' ],
					],
				],
			]
		);

		foreach ( $fields as $field ) {
			$args = [
				'value' => isset( $this->plugin_options[$field['name']] ) ? $this->plugin_options[$field['name']] : ''
			];
			if ( ! empty( $field['args'] ) ) {
				$args = array_merge( $args, $field['args'] );
			}

			$this->settings_helper->add_field(
				$field['name'],
				$field['type'],
				$field['label'],
				$field['section'],
				$args,
			);
		}
	} // register_plugin_settings

} // class Test_Plugin
