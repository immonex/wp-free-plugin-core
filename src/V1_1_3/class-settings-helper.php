<?php
/**
 * Class Settings_Helper
 *
 * @package immonex-wp-free-plugin-core
 */

namespace immonex\WordPressFreePluginCore\V1_1_3;

/**
 * Helper class for dealing with the WordPress Settings API.
 *
 * @package immonex-wp-free-plugin-core
 */
class Settings_Helper {

	/**
	 * Plugin directory (full path)
	 *
	 * @var string
	 */
	private $plugin_dir;

	/**
	 * Plugin slug
	 *
	 * @var string
	 */
	private $plugin_slug;

	/**
	 * Name of the custom field for storing plugin options
	 *
	 * @var string
	 */
	private $plugin_options_name;

	/**
	 * Minimun WP capability to access the plugin options page
	 *
	 * @var string
	 */
	private $plugin_options_access_capability = 'manage_options';

	/**
	 * Plugin information and URLs for displaying in the options footer
	 *
	 * @var string
	 */
	private $plugin_infos;

	/**
	 * Tabs to display on the plugin options page
	 *
	 * @var mixed[]
	 */
	private $option_page_tabs = array();

	/**
	 * Array for storing related "page" url fragments of options sections
	 *
	 * @var mixed[]
	 */
	private $section_page = array();

	/**
	 * Sections to display inside the options page tabs
	 *
	 * @var mixed[]
	 */
	private $sections = array();

	/**
	 * Input elements to display inside the options sections
	 *
	 * @var mixed[]
	 */
	private $fields = array();

	/**
	 * Current options page tab
	 *
	 * @var string
	 */
	private $current_tab;

	/**
	 * Constructor: Set some class properties.
	 *
	 * @since 0.1
	 *
	 * @param string $plugin_dir Absolute plugin directory path.
	 * @param string $plugin_slug Slug of the initiating plugin.
	 * @param string $plugin_options_name Name used for storing the serialized options array.
	 * @param array  $plugin_infos Additional information for output on options page.
	 * @param string $plugin_options_access_capability Min user/role capability required for
	 *                                                 modifying plugin options (optional).
	 */
	public function __construct(
		$plugin_dir,
		$plugin_slug,
		$plugin_options_name,
		$plugin_infos,
		$plugin_options_access_capability = false
	) {
		$this->plugin_dir          = $plugin_dir;
		$this->plugin_slug         = $plugin_slug;
		$this->plugin_options_name = $plugin_options_name;
		$this->plugin_infos        = $plugin_infos;

		add_action( 'immonex_plugin_options_add_extension_tabs', array( $this, 'register_extension_tabs' ), 10, 2 );
		add_action( 'immonex_plugin_options_add_extension_sections', array( $this, 'register_extension_sections' ), 10, 2 );
		add_action( 'immonex_plugin_options_add_extension_fields', array( $this, 'register_extension_fields' ), 10, 2 );

		if ( $plugin_options_access_capability ) {
			$this->plugin_options_access_capability = $plugin_options_access_capability;
		}
	} // __construct

	/**
	 * Add "Settings" link on the plugins page.
	 *
	 * @since 0.1
	 *
	 * @param array $links Current link array.
	 *
	 * @return array Extended link array.
	 */
	public function plugin_settings_link( $links ) {
		if ( empty( $this->plugin_infos['settings_page'] ) ) {
			return $links;
		}

		$settings_link = wp_sprintf(
			'<a href="%s">%s</a>',
			$this->plugin_infos['settings_page'],
			__( 'Settings', 'immonex-wp-free-plugin-core' )
		);
		array_unshift( $links, $settings_link );

		return $links;
	} // plugin_settings_link

	/**
	 * Add a tab.
	 *
	 * @since 0.1
	 *
	 * @param string $id Tab ID.
	 * @param string $title Tab title.
	 * @param string $content Tab content - default form output will be disabled if set (optional).
	 * @param array  $attributes Additional tab attributes.
	 */
	public function add_tab( $id, $title, $content = '', $attributes = array() ) {
		$this->option_page_tabs[ $id ] = array(
			'title'      => $title,
			'content'    => $content,
			'attributes' => $attributes,
		);
	} // add_tab

	/**
	 * Get the current tab ID.
	 *
	 * @since 0.1
	 *
	 * @return string|bool Tab ID or false if not existing.
	 */
	public function get_current_tab() {
		if ( isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] ) {
			return $_REQUEST['tab'];
		} else {
			return false;
		}
	} // get_current_tab

	/**
	 * Get the field definitions added within the given tab.
	 *
	 * @since 0.9
	 *
	 * @param string $tab Tab ID.
	 *
	 * @return mixed[] Array of tab field data.
	 */
	public function get_tab_fields( $tab = 'default' ) {
		return isset( $this->fields[ $tab ] ) ? $this->fields[ $tab ] : array();
	} // get_tab_fields

	/**
	 * Display the tab navigation.
	 *
	 * @since 0.1
	 */
	private function display_tab_nav() {
		if ( count( $this->option_page_tabs ) > 0 ) {
			echo '<h2 class="nav-tab-wrapper">';
			foreach ( $this->option_page_tabs as $tab_id => $tab ) {
				$class     = ( $tab_id === $this->current_tab ) ? ' nav-tab-active' : '';
				$post_type = isset( $_GET['post_type'] ) ? 'post_type=' . sanitize_title( $_GET['post_type'] ) . '&' : '';

				echo wp_sprintf(
					'<a class="nav-tab%1$s" href="?%2$spage=%3$s_settings&tab=%4$s">%5$s</a>',
					$class,
					$post_type,
					$this->plugin_slug,
					$tab_id,
					$tab['title']
				);
			}
			echo "</h2>\n";
		}
	} // display_tab_nav

	/**
	 * Add a form section.
	 *
	 * @since 0.1
	 *
	 * @param string $id Section ID.
	 * @param string $title Section title.
	 * @param string $description Description text to be displayed.
	 * @param string $tab Tab for section display (optional).
	 */
	public function add_section( $id, $title, $description = false, $tab = false ) {
		// Use the default options page name if no tab is given.
		$page = $tab ? $this->plugin_slug . '_' . $tab : $this->plugin_slug . '_settings';

		// Prefix the section with the plugin name (slug) before adding it.
		$section_id = $this->plugin_slug . '_' . $id;

		add_settings_section(
			$section_id,
			$title,
			array( $this, 'render_section' ),
			$page
		);

		$this->sections[ $section_id ] = array(
			'title'       => $title,
			'description' => $description,
			'tab'         => $tab,
		);

		$this->section_page[ $section_id ] = $page;
	} // add_section

	/**
	 * Add a settings field.
	 *
	 * @since 0.1
	 *
	 * @param string $name Field name.
	 * @param string $type Type of the input field (text, textarea, select...).
	 * @param string $label Field label.
	 * @param string $section Name of the section the field shall be added to.
	 * @param array  $args Field properties to be added to the defaults.
	 */
	public function add_field( $name, $type, $label, $section, $args ) {
		$field_id   = ( ! empty( $args['plugin_slug'] ) ? $args['plugin_slug'] : $this->plugin_slug ) . '_' . $name;
		$section_id = $this->plugin_slug . '_' . $section;

		$args_default = array(
			'type'        => $type,
			'name'        => $name,
			'id'          => $field_id,
			'option_name' => $this->plugin_options_name,
		);
		$args         = array_merge( $args_default, $args );

		add_settings_field(
			$name,
			$label,
			array( $this, 'render_field' ),
			$this->section_page[ $section_id ],
			$section_id,
			$args
		);

		$tab                           = isset( $this->sections[ $section_id ]['tab'] ) ? $this->sections[ $section_id ]['tab'] : 'default';
		$this->fields[ $tab ][ $name ] = array_merge(
			array( 'id' => $field_id ),
			$args
		);
	} // add_field

	/**
	 * Locally register the parent plugin's own extension tabs for later
	 * processing.
	 *
	 * @since 0.9
	 *
	 * @param string  $extension_plugin_slug Slug of plugin that extends the
	 *      option tabs.
	 * @param mixed[] $tabs Array of tab data.
	 */
	public function register_extension_tabs( $extension_plugin_slug, $tabs ) {
		if ( $extension_plugin_slug !== $this->plugin_slug ) {
			return;
		}

		if ( count( $tabs ) > 0 ) {
			foreach ( $tabs as $id => $tab ) {
				$this->add_tab(
					$id,
					$tab['title'],
					isset( $tab['content'] ) ? $tab['content'] : '',
					isset( $tab['attributes'] ) ? $tab['attributes'] : array()
				);
			}
		}
	} // register_extension_tabs

	/**
	 * Locally register the parent plugin's own extension sections for later
	 * processing.
	 *
	 * @since 0.9
	 *
	 * @param string  $extension_plugin_slug Slug of plugin that extends the
	 *      option sections.
	 * @param mixed[] $sections Array of section data.
	 */
	public function register_extension_sections( $extension_plugin_slug, $sections ) {
		if ( $extension_plugin_slug !== $this->plugin_slug ) {
			return;
		}

		if ( count( $sections ) > 0 ) {
			foreach ( $sections as $id => $section ) {
				$this->add_section(
					$id,
					isset( $section['title'] ) ? $section['title'] : '',
					isset( $section['description'] ) ? $section['description'] : '',
					$section['tab']
				);
			}
		}
	} // register_extension_sections

	/**
	 * Locally register the parent plugin's own extension fields for later
	 * processing.
	 *
	 * @since 0.9
	 *
	 * @param string  $extension_plugin_slug Slug of plugin that extends the
	 *      option sections.
	 * @param mixed[] $fields Array of field data.
	 */
	public function register_extension_fields( $extension_plugin_slug, $fields ) {
		if ( $extension_plugin_slug !== $this->plugin_slug ) {
			return;
		}

		if ( count( $fields ) > 0 ) {
			foreach ( $fields as $field ) {
				$this->add_field(
					$field['name'],
					$field['type'],
					$field['label'],
					$field['section'],
					$field['args']
				);
			}
		}
	} // register_extension_fields

	/**
	 * Render the settings page.
	 *
	 * @since 0.1
	 *
	 * @param array $args Additional information for page rendering (e.g. plugin name and version).
	 */
	public function render_page( $args = array() ) {
		$option_page_template = apply_filters(
			// @codingStandardsIgnoreLine
			$this->plugin_slug . '_option_page_template',
			file_exists( $this->plugin_dir . '/partials/plugin-options.php' ) ?
				$this->plugin_dir . '/partials/plugin-options.php' :
				__DIR__ . '/partials/plugin-options.php'
		);

		if (
			! current_user_can( $this->plugin_options_access_capability ) ||
			! $option_page_template
		) {
			wp_die( 'You do not have sufficient permissions to access this page. / Sie verfügen nicht über die nötigen Zugriffsrechte, um diese Seite aufzurufen.' );
		}

		// Add hook for modifying tabs before rendering.
		// @codingStandardsIgnoreLine
		$this->option_page_tabs = apply_filters( $this->plugin_slug . '_option_page_tabs', $this->option_page_tabs );

		if ( count( $this->option_page_tabs ) > 0 ) {
			// Tabs in use...
			$option_page_tab_keys = array_keys( $this->option_page_tabs );

			// Select current tab bases on related GET variable.
			if ( isset( $_GET['tab'] ) && in_array( $_GET['tab'], array_keys( $this->option_page_tabs ) ) ) {
				$this->current_tab = $_GET['tab'];
			} else {
				$this->current_tab = $option_page_tab_keys[0];
			}

			// Generate "page" name for section display.
			$section_page = $this->plugin_slug . '_' . $this->current_tab;
		} else {
			// No tabs: Use main options page name for section display.
			$section_page = $this->plugin_slug . '_settings';
		}

		require_once $option_page_template;
	} // render_page

	/**
	 * Render an options section.
	 *
	 * @since 0.1
	 *
	 * @param array $args Section properties.
	 */
	public function render_section( $args ) {
		// Make current tab info available after submit.
		echo '<input type="hidden" name="tab" value="' . $this->current_tab . '">' . "\n";

		if ( ! empty( $this->sections[ $args['id'] ]['description'] ) ) {
			echo '<p class="section-description">' . $this->sections[ $args['id'] ]['description'] . "</p>\n";
		}
	} // render_section

	/**
	 * Invoke field render function based on its type.
	 *
	 * @since 0.1
	 *
	 * @param array $args Field properties.
	 */
	public function render_field( $args ) {
		$type = isset( $args['type'] ) ? $args['type'] : 'text';

		if ( method_exists( $this, "render_$type" ) ) {
			$this->{"render_$type"}( $args );
		}

		if ( isset( $args['description'] ) ) {
			echo '<p class="description">' . $args['description'] . "</p>\n";
		}
	} // render_field

	/**
	 * Render a text field.
	 *
	 * @since 0.1
	 *
	 * @param array $args Field properties.
	 */
	private function render_text( $args ) {
		printf(
			'<input type="text" name="%1$s[%2$s]" id="%3$s"%4$s value="%5$s"%6$s>%7$s' . "\n",
			$args['option_name'],
			$args['name'],
			$args['id'],
			$this->get_class_code( $args, 'regular-text' ),
			$args['value'],
			disabled( isset( $args['disabled'] ) && $args['disabled'], true, false ),
			isset( $args['field_suffix'] ) && $args['field_suffix'] ? ' ' . $args['field_suffix'] : ''
		);
	} // render_text

	/**
	 * Render a textarea.
	 *
	 * @since 0.1
	 *
	 * @param array $args Textarea properties.
	 */
	private function render_textarea( $args ) {
		printf(
			'<textarea name="%1$s[%2$s]" id="%3$s" rows="10" cols="30"%6$s%5$s>%4$s</textarea>' . "\n",
			$args['option_name'],
			$args['name'],
			$args['id'],
			$args['value'],
			disabled( isset( $args['disabled'] ) && $args['disabled'], true, false ),
			$this->get_class_code( $args, 'code large-text' )
		);
	} // render_textarea

	/**
	 * Render a WYSIWYG editor.
	 *
	 * @since 0.9
	 *
	 * @param array $args Editor properties.
	 */
	private function render_wysiwyg( $args ) {
		$editor_settings = array(
			'wpautop'           => true,
			'media_buttons'     => false,
			'default_editor'    => '',
			'drag_drop_upload'  => false,
			'textarea_name'     => $args['option_name'] . '[' . $args['name'] . ']',
			'textarea_rows'     => 8,
			'tabindex'          => '',
			'tabfocus_elements' => ':prev,:next',
			'editor_css'        => '',
			'editor_class'      => 'large-text',
			'teeny'             => false,
			'tinymce'           => true,
			'quicktags'         => false,
		);

		if ( isset( $args['editor_settings'] ) ) {
			$editor_settings = array_merge(
				$editor_settings,
				$args['editor_settings']
			);
		}

		wp_editor( $args['value'], $args['id'], $editor_settings );
	} // render_wysiwyg

	/**
	 * Render a select box.
	 *
	 * @since 0.1
	 *
	 * @param array $args Select properties.
	 */
	private function render_select( $args ) {
		printf(
			'<select name="%1$s[%2$s]" id="%3$s"%6$s%4$s>%5$s',
			$args['option_name'],
			$args['name'],
			$args['id'],
			disabled( isset( $args['disabled'] ) && $args['disabled'], true, false ),
			isset( $args['field_suffix'] ) && $args['field_suffix'] ? ' ' . $args['field_suffix'] : '',
			$this->get_class_code( $args, '' )
		);

		foreach ( $args['options'] as $value => $title ) {
			printf(
				'<option value="%1$s" %2$s>%3$s</option>',
				$value,
				selected( $value, $args['value'], false ),
				$title
			);
		}

		echo "</select>\n";
	} // render_select

	/**
	 * Render a checkbox.
	 *
	 * @since 0.1
	 *
	 * @param array $args Checkbox properties.
	 */
	private function render_checkbox( $args ) {
		printf(
			'<input type="checkbox" name="%1$s[%2$s]" id="%3$s" value="1"%4$s%7$s%5$s>%6$s' . "\n",
			$args['option_name'],
			$args['name'],
			$args['id'],
			checked( 1, $args['value'], false ),
			disabled( isset( $args['disabled'] ) && $args['disabled'], true, false ),
			isset( $args['field_suffix'] ) && $args['field_suffix'] ? ' ' . $args['field_suffix'] : '',
			$this->get_class_code( $args, '' )
		);
	} // render_checkbox

	/**
	 * Render a group of checkboxes.
	 *
	 * @since 0.4.8
	 *
	 * @param array $args Checkbox properties.
	 */
	private function render_checkbox_group( $args ) {
		if ( ! isset( $args['options'] ) || 0 === count( $args['options'] ) ) {
			return;
		}

		if ( ! isset( $args['wrap'] ) ) {
			$args['wrap'] = '<div>{element}</div>';
		}

		foreach ( $args['options'] as $value => $title ) {
			$checkbox = sprintf(
				'<input type="checkbox" name="%1$s[%2$s][]" id="%3$s_%8$s" value="%8$s"%4$s%7$s%5$s>%9$s%6$s' . "\n",
				$args['option_name'],
				$args['name'],
				$args['id'],
				checked( is_array( $args['value'] ) && in_array( $value, $args['value'] ), true, false ),
				disabled( isset( $args['disabled'] ) && $args['disabled'], true, false ),
				isset( $args['field_suffix'] ) && $args['field_suffix'] ? ' ' . $args['field_suffix'] : '',
				$this->get_class_code( $args, '' ),
				$value,
				$title
			);

			if ( isset( $args['wrap'] ) ) {
				$checkbox = str_replace( '{element}', trim( $checkbox ), $args['wrap'] ) . "\n";
			}

			echo $checkbox;
		}
	} // render_checkbox_group

	/**
	 * Generate the class code for input elements.
	 *
	 * @since 0.3.3
	 *
	 * @param array  $args Element properties.
	 * @param string $default Default class.
	 *
	 * @return string Element class code.
	 */
	private function get_class_code( $args, $default ) {
		$classes = isset( $args['class'] ) ? $args['class'] : '';

		if ( false === $classes ) {
			$code = '';
		} elseif ( $classes ) {
			$code = ' class="' . $classes . '"';
		} elseif ( $default ) {
			$code = ' class="' . $default . '"';
		} else {
			$code = '';
		}

		return $code;
	} // get_class_code

	/**
	 * Merge user inputs into current options array.
	 *
	 * @since 0.1
	 *
	 * @param array $current_options Current plugin options array.
	 * @param array $inputs User inputs from options page/tab.
	 *
	 * @return array Updated options array.
	 */
	public function merge_options( $current_options, $inputs ) {
		if (
			is_array( $current_options ) && count( $current_options ) > 0 &&
			is_array( $inputs ) && count( $inputs ) > 0
		) {
			foreach ( $current_options as $key => $value ) {
				if ( isset( $inputs[ $key ] ) ) {
					// Replace old option value by user input.
					$current_options[ $key ] = $inputs[ $key ];
				}
			}
		}

		return $current_options;
	} // merge_options

} // Settings_Helper
