<?php
/**
 * Class MyPlugin
 *
 * @package my-immonex-plugin
 */

namespace myimmonex\MyPlugin;

/**
 * Main plugin class.
 */
class My_Plugin extends \immonex\WordPressFreePluginCore\V1_1_1\Base {

	const PLUGIN_NAME                = 'My immonex Plugin';
	const PLUGIN_PREFIX              = 'myplugin_';
	const PUBLIC_PREFIX              = 'myplugin-';
	const PLUGIN_VERSION             = '1.0.0';
	const OPTIONS_LINK_MENU_LOCATION = 'settings';

	/**
	 * Plugin options
	 *
	 * @var mixed[]
	 */
	protected $plugin_options = array(
		'plugin_version' => self::PLUGIN_VERSION,
		'test_option'    => '',
	);

	/**
	 * Perform plugin initialization tasks.
	 *
	 * @since 1.0.0
	 */
	public function init_plugin() {
		parent::init_plugin();

		$this->options_link_title = __( 'MyPlugin Options', 'my-immonex-plugin' );

		// ...more initialization stuff here...
	} // init_plugin

	/**
	 * Register plugin settings.
	 *
	 * @since 1.0.0
	 */
	public function register_plugin_settings() {
		parent::register_plugin_settings();

		// Tabs (extendable by filter function).
		$tabs = apply_filters(
			// @codingStandardsIgnoreLine
			$this->plugin_slug . '_option_tabs',
			array(
				'tab_general' => array(
					'title'      => __( 'General', 'my-immonex-plugin' ),
					'content'    => '',
					'attributes' => array(),
				),
			)
		);
		foreach ( $tabs as $id => $tab ) {
			$this->settings_helper->add_tab(
				$id,
				$tab['title'],
				isset( $tab['content'] ) ? $tab['content'] : '',
				isset( $tab['attributes'] ) ? $tab['attributes'] : array()
			);
		}

		// Sections (extendable by filter function).
		$sections = apply_filters(
			// @codingStandardsIgnoreLine
			$this->plugin_slug . '_option_sections',
			array(
				'section_demo' => array(
					'title'       => __( 'Demo Section', 'my-immonex-plugin' ),
					'description' => '',
					'tab'         => 'tab_general',
				),
			)
		);
		foreach ( $sections as $id => $section ) {
			$this->settings_helper->add_section(
				$id,
				isset( $section['title'] ) ? $section['title'] : '',
				isset( $section['description'] ) ? $section['description'] : '',
				$section['tab']
			);
		}

		// Fields (extendable by filter function).
		$fields = apply_filters(
			// @codingStandardsIgnoreLine
			$this->plugin_slug . '_option_fields',
			array(
				array(
					'name'    => 'test_option',
					'type'    => 'text',
					'label'   => __( 'Test Option', 'my-immonex-plugin' ),
					'section' => 'section_demo',
					'args'    => array(
						'description' => __( 'Just a demo...', 'my-immonex-plugin' ),
						'class'       => '',
						'max_length'  => 8,
					),
				),
			)
		);

		foreach ( $fields as $field ) {
			$args = array(
				'value' => isset( $this->plugin_options[ $field['name'] ] ) ?
					$this->plugin_options[ $field['name'] ] :
					'',
			);
			if ( isset( $field['args'] ) ) {
				$args = array_merge( $args, $field['args'] );
			}

			$this->settings_helper->add_field(
				$field['name'],
				$field['type'],
				$field['label'],
				$field['section'],
				$args
			);
		}
	} // register_plugin_settings

} // class My_Plugin
