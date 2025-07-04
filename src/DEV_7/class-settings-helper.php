<?php
/**
 * Class Settings_Helper
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\DEV_7;

/**
 * Helper class for dealing with the WordPress Settings API.
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
	 * String utilities object
	 *
	 * @var String_Utils
	 */
	private $string_utils;

	/**
	 * Tabs to display on the plugin options page
	 *
	 * @var mixed[]
	 */
	private $option_page_tabs = [];

	/**
	 * Array for storing related "page" url fragments of options sections
	 *
	 * @var mixed[]
	 */
	private $section_page = [];

	/**
	 * Sections to display inside the options page tabs
	 *
	 * @var mixed[]
	 */
	private $sections = [];

	/**
	 * Input elements to display inside the options sections
	 *
	 * @var mixed[]
	 */
	private $fields = [];

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
	 * @param string       $plugin_dir Absolute plugin directory path.
	 * @param string       $plugin_slug Slug of the initiating plugin.
	 * @param string       $plugin_options_name Name used for storing the serialized options array.
	 * @param String_Utils $string_utils String utilities object.
	 */
	public function __construct( $plugin_dir, $plugin_slug, $plugin_options_name, $string_utils ) {
		$this->plugin_dir          = $plugin_dir;
		$this->plugin_slug         = $plugin_slug;
		$this->plugin_options_name = $plugin_options_name;
		$this->string_utils        = $string_utils;

		add_action( 'immonex_plugin_options_add_extension_tabs', [ $this, 'register_extension_tabs' ], 10, 2 );
		add_action( 'immonex_plugin_options_add_extension_sections', [ $this, 'register_extension_sections' ], 10, 2 );
		add_action( 'immonex_plugin_options_add_extension_fields', [ $this, 'register_extension_fields' ], 10, 2 );

		// @codingStandardsIgnoreLine
		$plugin_infos = apply_filters( "{$this->plugin_slug}_plugin_infos", [] );

		if ( isset( $plugin_infos['prefix'] ) ) {
			add_action( "{$plugin_infos['prefix']}render_option_page_header", [ $this, 'render_option_page_header' ] );
			add_action( "{$plugin_infos['prefix']}render_option_page_footer", [ $this, 'render_option_page_footer' ] );
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
		// @codingStandardsIgnoreLine
		$plugin_infos = apply_filters( "{$this->plugin_slug}_plugin_infos", [] );

		if ( empty( $plugin_infos['settings_page'] ) ) {
			return $links;
		}

		$settings_link = wp_sprintf(
			'<a href="%s">%s</a>',
			$plugin_infos['settings_page'],
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
	public function add_tab( $id, $title, $content = '', $attributes = [] ) {
		$this->option_page_tabs[ $id ] = [
			'title'      => $title,
			'content'    => $content,
			'attributes' => $attributes,
		];
	} // add_tab

	/**
	 * Get the current tab ID.
	 *
	 * @since 0.1
	 *
	 * @return string|bool Tab ID or false if not existing.
	 */
	public function get_current_tab() {
		if ( ! empty( $_REQUEST['tab'] ) ) {
			return sanitize_key( $_REQUEST['tab'] );
		}

		if ( ! empty( $this->option_page_tabs ) ) {
			return key( $this->option_page_tabs );
		}

		return false;
	} // get_current_tab

	/**
	 * Get the section definitions added within the given tab.
	 *
	 * @since 1.3.1
	 *
	 * @param string $tab Tab ID.
	 *
	 * @return mixed[] Array of tab section data.
	 */
	public function get_tab_sections( $tab = 'default' ) {
		return array_filter(
			$this->sections,
			function ( $section ) use ( $tab ) {
				return ! empty( $section['tab'] ) && $tab === $section['tab'];
			}
		);
	} // get_tab_sections

	/**
	 * Return all field definitions.
	 *
	 * @since 1.3.5
	 *
	 * @return mixed[] Array of field data.
	 */
	public function get_fields() {
		return $this->fields;
	} // get_fields

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
		return isset( $this->fields[ $tab ] ) ? $this->fields[ $tab ] : [];
	} // get_tab_fields

	/**
	 * Check if the current set of registered fields contains media elements.
	 * If so, return the related data.
	 *
	 * @since 1.3.5
	 *
	 * @return mixed[] Field data or empty array if none exist.
	 */
	public function get_media_fields() {
		$media_fields = [];

		if ( ! empty( $this->fields ) && is_array( $this->fields ) ) {
			foreach ( $this->fields as $tab => $fields ) {
				if ( count( $fields ) > 0 ) {
					foreach ( $fields as $field ) {
						if ( ! empty( $field['type'] ) && 'media_' === substr( $field['type'], 0, 6 ) ) {
							$media_fields[] = $field;
						}
					}
				}
			}
		}

		return $media_fields;
	} // get_media_fields

	/**
	 * Display the tab navigation.
	 *
	 * @since 0.1
	 */
	private function display_tab_nav() {
		if ( ! empty( $this->option_page_tabs ) && is_array( $this->option_page_tabs ) ) {
			echo '<h2 class="nav-tab-wrapper">';

			foreach ( $this->option_page_tabs as $tab_id => $tab ) {
				$classes   = $tab_id === $this->current_tab ? [ 'nav-tab-active' ] : [];
				$post_type = isset( $_GET['post_type'] ) ? 'post_type=' . sanitize_title( wp_unslash( $_GET['post_type'] ) ) . '&' : '';

				$badge         = '';
				$badge_classes = [];
				if ( ! empty( $tab['attributes']['badge'] ) ) {
					$badge = $tab['attributes']['badge'];
				} elseif ( ! empty( $tab['attributes']['is_addon_tab'] ) || false !== strpos( $tab['title'], '[Add-on]' ) ) {
					$badge = 'Add-on';
				}
				if ( $badge ) {
					$classes[]     = 'has-badge';
					$badge_classes = [
						'nav-tab__badge',
						'nav-tab__badge--' . $this->string_utils::slugify( $badge ),
					];
				}

				if ( false !== strpos( $tab['title'], '[Add-on]' ) ) {
					$tab['title'] = trim( str_replace( '[Add-on]', '', $tab['title'] ) );
				}

				echo wp_sprintf(
					'<a class="nav-tab%1$s" href="?%2$spage=%3$s_settings&tab=%4$s">%5$s%6$s</a>',
					! empty( $classes ) ? ' ' . implode( ' ', $classes ) : '',
					$post_type,
					$this->plugin_slug,
					$tab_id,
					$tab['title'],
					$badge ? wp_sprintf( '<div class="%1$s">%2$s</div>', implode( ' ', $badge_classes ), $badge ) : ''
				);
			}
			echo '</h2>' . PHP_EOL;
		}
	} // display_tab_nav

	/**
	 * Display the section tab (sub) navigation.
	 *
	 * @since 1.3.1
	 *
	 * @param string $tab_id ID of the tab to display the sections for.
	 * @param int    $current_section_tab Index of the current active section tab.
	 */
	private function display_section_nav( $tab_id, $current_section_tab = 1 ) {
		$tab_sections = $this->get_tab_sections( $tab_id );
		if ( ! is_array( $tab_sections ) || count( $tab_sections ) < 2 ) {
			return;
		}

		$i = 0;

		echo '<h3 class="nav-tab-wrapper">';
		foreach ( $tab_sections as $section_id => $section ) {
			++$i;
			$class = ( $i === $current_section_tab ) ? ' nav-tab-active' : '';

			echo wp_sprintf(
				'<a id="section-nav-tab-%d" class="nav-tab nav-tab-section%s" href="%s">%s</a>',
				$i,
				$class,
				'javascript:void(0)',
				$section['title']
			);
		}
		echo "</h3>\n";
	} // display_section_nav

	/**
	 * Display the the sections of the given tab.
	 *
	 * @since 1.3.1
	 *
	 * @param string $tab_id ID of the tab to display the sections for.
	 * @param string $section_page "Page" ID (extended tab ID).
	 */
	private function display_tab_sections( $tab_id, $section_page ) {
		$tab_sections = $this->get_tab_sections( $tab_id );

		if (
			! empty( $this->option_page_tabs[ $tab_id ]['attributes']['tabbed_sections'] )
			&& is_array( $tab_sections )
			&& count( $tab_sections ) > 1
		) {
			$current_section_tab = ! empty( $_GET['section_tab'] ) ? (int) $_GET['section_tab'] : 1;

			if ( $current_section_tab > count( $tab_sections ) ) {
				$current_section_tab = 1;
			}

			$this->display_section_nav( $tab_id, $current_section_tab );

			ob_start();

			do_settings_sections( $section_page );

			$sections_html = ob_get_contents();
			$section_count = 0;

			$sections_html = str_replace(
				array( '|CRLF|', '|LF|' ),
				// Take mixed line breaks into account (e.g. due to textarea contents or in Windows environments).
				array( "\r\n", "\n" ),
				preg_replace_callback(
					'/\<h2\>.*?(\<\/table\>|(?=\<h2))/',
					function ( $matches ) use ( &$section_count, $current_section_tab ) {
						$section_count++;
						$section_id = "tab-section-{$section_count}";
						return wp_sprintf(
							'<div id="%s" class="tabbed-section%s">%s</div>',
							$section_id,
							$section_count === $current_section_tab ? ' is-active ' : '',
							$matches[0]
						);
					},
					str_replace(
						array( "\r\n", "\n" ),
						array( '|CRLF|', '|LF|' ),
						$sections_html
					)
				)
			);
			$sections_html = str_replace(
				'<th scope="row"></th><td>',
				'<td colspan="2" class="subsection-header">',
				$sections_html
			);

			ob_end_clean();
			echo $sections_html;
		} else {
			do_settings_sections( $section_page );
		}
	} // display_tab_sections

	/**
	 * Add a form section.
	 *
	 * @since 0.1
	 *
	 * @param string      $id Section ID.
	 * @param string      $title Section title.
	 * @param string|bool $description Description text to be displayed (optional).
	 * @param string|bool $tab_id ID of the tab the section section shall be part of (optional).
	 * @param mixed[]     $args Optional section properties.
	 */
	public function add_section( $id, $title, $description = false, $tab_id = false, $args = [] ) {
		// Use the default options page name if no tab is given.
		$page = $tab_id ? $this->plugin_slug . '_' . $tab_id : $this->plugin_slug . '_settings';

		// Prefix the section with the plugin name (slug) before adding it.
		$section_id = $this->plugin_slug . '_' . $id;

		if (
			! $title
			&& $tab_id
			&& ! empty( $this->option_page_tabs[ $tab_id ]['attributes']['tabbed_sections'] )
		) {
			$title = __( 'General', 'immonex-wp-free-plugin-core' );
		}

		add_settings_section(
			$section_id,
			$title,
			array( $this, 'render_section' ),
			$page
		);

		$this->sections[ $section_id ] = [
			'title'       => $title,
			'description' => $description,
			'tab'         => $tab_id,
			'args'        => $args,
		];

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

		if ( ! empty( $args['class'] ) && ! isset( $args['field_class'] ) ) {
			// Reassign a single class argument to be used in the field tag instead of the parent TR tag.
			$args['field_class'] = $args['class'];
			$args['class']       = '';
		}

		if ( ! empty( $args['doc_url'] ) ) {
			$label .= String_Utils::doc_link( $args['doc_url'] );
			unset( $args['doc_url'] );
		}

		$args_default = [
			'type'        => $type,
			'name'        => $name,
			'id'          => $field_id,
			'label'       => $label,
			'required'    => false,
			'allow_zero'  => false,
			'no_sanitize' => false,
			'option_name' => $this->plugin_options_name,
		];
		$args         = array_merge( $args_default, $args );

		if (
			empty( $args['force_type'] )
			&& isset( $args['min'] )
		) {
			$args['force_type'] = gettype( $args['min'] );
		}

		if (
			empty( $args['force_type'] )
			&& isset( $args['max'] )
		) {
			$args['force_type'] = gettype( $args['max'] );
		}

		add_settings_field(
			$name,
			$label,
			array( $this, 'render_field' ),
			$this->section_page[ $section_id ],
			$section_id,
			$args
		);

		$tab                           = isset( $this->sections[ $section_id ]['tab'] ) ? $this->sections[ $section_id ]['tab'] : 'default';
		$this->fields[ $tab ][ $name ] = $args;
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
		if (
			$extension_plugin_slug !== $this->plugin_slug
			|| ! is_array( $tabs )
		) {
			return;
		}

		if ( count( $tabs ) > 0 ) {
			foreach ( $tabs as $id => $tab ) {
				$this->add_tab(
					$id,
					$tab['title'],
					isset( $tab['content'] ) ? $tab['content'] : '',
					isset( $tab['attributes'] ) ? $tab['attributes'] : []
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
		if (
			$extension_plugin_slug !== $this->plugin_slug
			|| ! is_array( $sections )
		) {
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
		if (
			$extension_plugin_slug !== $this->plugin_slug
			|| ! is_array( $fields )
		) {
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
	public function render_page( $args = [] ) {
		$option_page_template = __DIR__ . '/partials/plugin-options.php';
		// @codingStandardsIgnoreLine
		$page                 = ! empty( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';

		if ( $page && file_exists( $this->plugin_dir . "/partials/{$page}.php" ) ) {
			$option_page_template = $this->plugin_dir . "/partials/{$page}.php";
		} elseif ( file_exists( $this->plugin_dir . '/partials/plugin-options.php' ) ) {
			$option_page_template = $this->plugin_dir . '/partials/plugin-options.php';
		}

		$option_page_template = apply_filters(
			// @codingStandardsIgnoreLine
			$this->plugin_slug . '_option_page_template',
			$option_page_template
		);

		$plugin_options_access_capability = apply_filters(
			// @codingStandardsIgnoreLine
			"{$this->plugin_slug}_plugin_options_access_capability",
			Base::DEFAULT_PLUGIN_OPTIONS_ACCESS_CAPABILITY
		);

		if (
			empty( $plugin_options_access_capability ) ||
			! current_user_can( $plugin_options_access_capability ) ||
			! $option_page_template
		) {
			wp_die( 'You do not have sufficient permissions to access this page. / Sie verfügen nicht über die nötigen Zugriffsrechte, um diese Seite aufzurufen.' );
		}

		// Add hook for modifying tabs before rendering.
		// @codingStandardsIgnoreLine
		$this->option_page_tabs = apply_filters( $this->plugin_slug . '_option_page_tabs', $this->option_page_tabs );

		if ( is_array( $this->option_page_tabs ) && count( $this->option_page_tabs ) > 0 ) {
			// Tabs in use...
			$option_page_tab_keys = array_keys( $this->option_page_tabs );

			// Select current tab bases on related GET variable.
			if ( isset( $_GET['tab'] ) && in_array( $_GET['tab'], array_keys( $this->option_page_tabs ), true ) ) {
				$this->current_tab = sanitize_key( $_GET['tab'] );
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
	 * Action hook callback for inserting the default plugion option page header.
	 *
	 * @since 1.8.0
	 */
	public function render_option_page_header() {
		require_once __DIR__ . '/partials/plugin-options-header.php';
	} // render_option_page_header

	/**
	 * Action hook callback for inserting the default plugion option page footer.
	 *
	 * @since 1.8.0
	 */
	public function render_option_page_footer() {
		require_once __DIR__ . '/partials/plugin-options-footer.php';
	} // render_option_page_footer

	/**
	 * Render an options section.
	 *
	 * @since 0.1
	 *
	 * @param array $args Section properties.
	 */
	public function render_section( $args ) {
		// Make current tab info available after submit.
		echo '<input type="hidden" name="tab" value="' . $this->current_tab . '">' . PHP_EOL;

		$ext_description = '';
		$description     = ! empty( $this->sections[ $args['id'] ]['description'] ) ?
			$this->sections[ $args['id'] ]['description'] : '';

		if ( is_array( $description ) ) {
			$ext_description = ! empty( $description[1] ) ? $description[1] : '';
			$description     = $description[0];
		}

		if ( $description ) {
			echo wp_sprintf(
				'<div class="section-description%1$s">%2$s</div>%3$s' . PHP_EOL,
				$ext_description ? ' has-ext-contents' : '',
				$description,
				$ext_description ? $this->get_extended_description_section( $ext_description, $args['id'] ) : ''
			);
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

		if (
			! empty( $args['render_method'] )
			&& is_callable( $args['render_method'] )
		) {
			$args['render_method']( $args );
		} elseif ( method_exists( $this, "render_{$type}" ) ) {
			$this->{"render_{$type}"}( $args );
		} else {
			$this->render_text( $args );
		}

		if (
			isset( $args['description'] )
			&& 'subsection_header' !== $args['type']
		) {
			echo '<p class="description">' . $args['description'] . '</p>' . PHP_EOL;
		}
	} // render_field

	/**
	 * Create the HTML markup for an extended description section.
	 *
	 * @since 1.4.0
	 *
	 * @param string $content Extended description content.
	 * @param array  $section_id Section ID.
	 *
	 * @return string Extended description HTML markup.
	 */
	private function get_extended_description_section( $content, $section_id ) {
		$args             = $this->sections[ $section_id ]['args'];
		$show_button_text = ! empty( $args['ext_desc_show_button_text'] ) ?
			$args['ext_desc_show_button_text'] :
			__( 'more details', 'immonex-wp-free-plugin-core' );
		$hide_button_text = ! empty( $args['ext_desc_hide_button_text'] ) ?
			$args['ext_desc_hide_button_text'] :
			__( 'hide details', 'immonex-wp-free-plugin-core' );

		return wp_sprintf(
			'<div class="immonex-plugin-options__ext-description">%s%s%s</div>',
			'<div class="immonex-plugin-options__ext-description-show"><button class="button button-primary"><span class="dashicons dashicons-arrow-down-alt2"></span>' .
				$show_button_text . '</button></div>',
			'<div class="immonex-plugin-options__ext-description-hide"><button class="button button-primary"><span class="dashicons dashicons-arrow-up-alt2"></span>' .
				$hide_button_text . '</button></div>',
			'<div class="immonex-plugin-options__ext-description-content">' . $content . '</div>'
		);
	} // get_extended_description_section

	/**
	 * Render a subsection header.
	 *
	 * @since 2.1.11
	 *
	 * @param array $args Section properties.
	 */
	public function render_subsection_header( $args ) {
		$ext_description = '';
		$description     = ! empty( $args['description'] ) ?
			$args['description'] : '';

		if ( is_array( $description ) ) {
			$ext_description = ! empty( $description[1] ) ? $description[1] : '';
			$description     = $description[0];
		}

		if ( ! empty( $args['title'] ) ) {
			printf( '<h3>%s</h3>' . PHP_EOL, $args['title'] );
		}

		if ( $description ) {
			echo wp_sprintf(
				'<div class="section-description%1$s">%2$s</div>%3$s' . PHP_EOL,
				$ext_description ? ' has-ext-contents' : '',
				$description,
				$ext_description ? $this->get_extended_description_section( $ext_description, $args['id'] ) : ''
			);
		}
	} // render_subsection_header

	/**
	 * Render a text field.
	 *
	 * @since 0.1
	 *
	 * @param array $args Field properties.
	 */
	private function render_text( $args ) {
		printf(
			'<input type="text" name="%1$s[%2$s]" id="%3$s"%4$s value="%5$s"%6$s%7$s>%8$s' . PHP_EOL,
			$args['option_name'],
			$args['name'],
			$args['id'],
			$this->get_class_code( $args, 'regular-text' ),
			$args['value'],
			disabled( isset( $args['disabled'] ) && $args['disabled'], true, false ),
			! empty( $args['required'] ) ? ' required' : '',
			isset( $args['field_suffix'] ) && $args['field_suffix'] ? ' ' . $args['field_suffix'] : ''
		);
	} // render_text

	/**
	 * Render a number field.
	 *
	 * @since 1.8.17
	 *
	 * @param array $args Field properties.
	 */
	private function render_number( $args ) {
		printf(
			'<input type="number" name="%1$s[%2$s]" id="%3$s"%4$s value="%5$s"%6$s%7$s%8$s%9$s%10$s>%11$s' . PHP_EOL,
			$args['option_name'],
			$args['name'],
			$args['id'],
			$this->get_class_code( $args, 'small-text' ),
			$args['value'],
			isset( $args['min'] ) ? ' min="' . (int) $args['min'] . '"' : '',
			isset( $args['max'] ) ? ' max="' . (int) $args['max'] . '"' : '',
			! empty( $args['step'] ) ? ' step="' . (int) $args['step'] . '"' : '',
			disabled( isset( $args['disabled'] ) && $args['disabled'], true, false ),
			! empty( $args['required'] ) ? ' required' : '',
			isset( $args['field_suffix'] ) && $args['field_suffix'] ? ' ' . $args['field_suffix'] : ''
		);
	} // render_number

	/**
	 * Render a textarea.
	 *
	 * @since 0.1
	 *
	 * @param array $args Textarea properties.
	 */
	private function render_textarea( $args ) {
		printf(
			'<textarea name="%1$s[%2$s]" id="%3$s" rows="%8$d" cols="%9$d"%7$s%5$s%6$s>%4$s</textarea>' . PHP_EOL,
			$args['option_name'],
			$args['name'],
			$args['id'],
			$args['value'],
			disabled( isset( $args['disabled'] ) && $args['disabled'], true, false ),
			! empty( $args['required'] ) ? ' required' : '',
			$this->get_class_code( $args, 'code large-text' ),
			! empty( $args['rows'] ) && is_numeric( $args['rows'] ) ? $args['rows'] : 10,
			! empty( $args['cols'] ) && is_numeric( $args['cols'] ) ? $args['rows'] : 30
		);
	} // render_textarea

	/**
	 * Render an email field.
	 *
	 * @since 1.7.0
	 *
	 * @param array $args Field properties.
	 */
	private function render_email( $args ) {
		printf(
			'<input type="email" name="%1$s[%2$s]" id="%3$s"%4$s value="%5$s"%6$s%7$s>%8$s' . PHP_EOL,
			$args['option_name'],
			$args['name'],
			$args['id'],
			$this->get_class_code( $args, 'regular-text' ),
			$args['value'],
			disabled( isset( $args['disabled'] ) && $args['disabled'], true, false ),
			! empty( $args['required'] ) ? ' required' : '',
			isset( $args['field_suffix'] ) && $args['field_suffix'] ? ' ' . $args['field_suffix'] : ''
		);
	} // render_email

	/**
	 * Render a WYSIWYG editor.
	 *
	 * @since 0.9
	 *
	 * @param array $args Editor properties.
	 */
	private function render_wysiwyg( $args ) {
		$editor_settings = [
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
		];

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
			'<select name="%1$s[%2$s]" id="%3$s"%7$s%4$s%5$s>%6$s',
			$args['option_name'],
			$args['name'],
			$args['id'],
			disabled( isset( $args['disabled'] ) && $args['disabled'], true, false ),
			! empty( $args['required'] ) ? ' required' : '',
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

		echo '</select>' . PHP_EOL;
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
			'<input type="checkbox" name="%1$s[%2$s]" id="%3$s" value="1"%4$s%7$s%5$s>%6$s' . PHP_EOL,
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
		if ( empty( $args['options'] ) ) {
			return;
		}

		if ( ! isset( $args['wrap'] ) ) {
			$args['wrap'] = '<div>{element}</div>';
		}

		foreach ( $args['options'] as $value => $title ) {
			$checkbox = sprintf(
				'<input type="checkbox" name="%1$s[%2$s][]" id="%3$s_%8$s" value="%8$s"%4$s%7$s%5$s>%9$s%6$s' . PHP_EOL,
				$args['option_name'],
				$args['name'],
				$args['id'],
				checked( is_array( $args['value'] ) && in_array( $value, $args['value'], true ), true, false ),
				disabled( isset( $args['disabled'] ) && $args['disabled'], true, false ),
				isset( $args['field_suffix'] ) && $args['field_suffix'] ? ' ' . $args['field_suffix'] : '',
				$this->get_class_code( $args, '' ),
				$value,
				$title
			);

			if ( isset( $args['wrap'] ) ) {
				$checkbox = str_replace( '{element}', trim( $checkbox ), $args['wrap'] ) . PHP_EOL;
			}

			echo $checkbox;
		}
	} // render_checkbox_group

	/**
	 * Render an image select element.
	 *
	 * @since 1.3.4
	 *
	 * @param array $args Image select properties.
	 */
	private function render_media_image_select( $args ) {
		$image_preview = '';
		if ( $args['value'] ) {
			$attachment_ids = explode( ',', $args['value'] );
			foreach ( $attachment_ids as $attachment_id ) {
				$thumb_url = wp_get_attachment_thumb_url( trim( $attachment_id ) );

				if ( $thumb_url ) {
					$image_preview .= wp_sprintf(
						'<div class="immonex-plugin-options__thumbnail" data-field-id="%1$s" data-att-id="%2$s">' .
							'<img src="%3$s" alt="Thumbnail">' .
							'<div class="immonex-plugin-options__delete-icon"></div>' .
							'</div>' . PHP_EOL,
						$args['id'],
						$attachment_id,
						$thumb_url
					);
				}
			}
		}

		$max_files = ! empty( $args['max_files'] ) && (int) $args['max_files'] > 0 ? (int) $args['max_files'] : 1;
		if ( ! empty( $args['select_button_text'] ) ) {
			$select_button_text = $args['select_button_text'];
		} else {
			$select_button_text = $max_files > 1 ?
				__( 'Select images', 'immonex-wp-free-plugin-core' ) :
				__( 'Select image', 'immonex-wp-free-plugin-core' );
		}

		$image_select = wp_sprintf(
			PHP_EOL . '<div id="%3$s-media-wrapper" class="immonex-plugin-options__media-wrapper">' .
				'%6$s</div>' .
				'<input id="%3$s-select-button" type="button" class="button" value="%5$s">' .
				'<input id="%3$s" type="hidden" name="%1$s[%2$s]" value="%4$s" autocomplete="off">' . PHP_EOL,
			$args['option_name'],
			$args['name'],
			$args['id'],
			$args['value'],
			$select_button_text,
			$image_preview
		);

		if ( isset( $args['wrap'] ) ) {
			$image_select = str_replace( '{element}', trim( $image_select ), $args['wrap'] ) . PHP_EOL;
		}

		echo $image_select;
	} // render_media_image_select

	/**
	 * Render a color picker element.
	 *
	 * @since 2.1.8
	 *
	 * @param array $args Color picker properties.
	 */
	private function render_colorpicker( $args ) {
		printf(
			'<input type="text" name="%1$s[%2$s]" id="%3$s"%4$s value="%5$s">%6$s' . PHP_EOL,
			$args['option_name'],
			$args['name'],
			$args['id'],
			$this->get_class_code( $args, 'immonex-plugin-options__colorpicker' ),
			$args['value'],
			isset( $args['field_suffix'] ) && $args['field_suffix'] ? ' ' . $args['field_suffix'] : ''
		);
	} // render_colorpicker

	/**
	 * [LEGACY PLUGINS ONLY] Render a license status incl. switch button.
	 *
	 * @since 1.8.2
	 *
	 * @param array $args License status related data.
	 */
	private function render_license_status( $args ) {
		$plugin_slug = ! empty( $args['plugin_slug'] ) ? $args['plugin_slug'] : $this->plugin_slug;
		$valid       = in_array( $args['current_status'], [ 'valid', 'active' ], true );

		printf(
			'<div style="margin-top:5px; margin-bottom:16px; color:%1$s; font-weight:bold">%2$s</div>' . PHP_EOL . '%3$s' . PHP_EOL .
			'<input type="hidden" name="edd_license_plugin_slug" value="' . $plugin_slug . '">' . PHP_EOL .
			'<input type="submit" class="button button-primary" name="edd_license_' . ( $valid ? 'de' : '' ) . 'activate" value="%4$s">',
			$valid ? 'green' : '#FF7117',
			$valid ? __( 'active', 'immonex-wp-free-plugin-core' ) : __( 'inactive', 'immonex-wp-free-plugin-core' ),
			wp_nonce_field( 'edd_license_status_change', 'edd_license_nonce', true, false ),
			$valid ? __( 'Deactivate License', 'immonex-wp-free-plugin-core' ) : __( 'Activate License', 'immonex-wp-free-plugin-core' )
		);
	} // render_license_status

	/**
	 * Generate the class code for input elements.
	 *
	 * @since 0.3.3
	 *
	 * @param array  $args Element properties.
	 * @param string $default_class Default class.
	 *
	 * @return string Element class code.
	 */
	private function get_class_code( $args, $default_class ) {
		$classes = isset( $args['field_class'] ) ? $args['field_class'] : '';

		if ( false === $classes ) {
			$code = '';
		} elseif ( $classes ) {
			$code = ' class="' . $classes . '"';
		} elseif ( $default_class ) {
			$code = ' class="' . $default_class . '"';
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

	/**
	 * Generate Twig templating info section contents based on the given intro text,
	 * title and tag descriptions.
	 *
	 * @since 1.9.0
	 *
	 * @param array           $tag_descriptions Descriptions of ALL available tags (tag key => description).
	 * @param array|string    $tags             Keys of tags to display.
	 * @param string          $intro            Intro text ("default" or "default_ext" for a generic description).
	 * @param string|string[] $title            Section title(s) as single string or array of two strings.
	 *
	 * @return string Section output.
	 */
	public function get_twig_templating_info_section( $tag_descriptions, $tags, $intro = '', $title = '' ) {
		if ( ! is_array( $tags ) ) {
			$tags = [ $tags ];
		}

		if ( 'default' === $intro || 'default_ext' === $intro ) {
			$intro = $this->get_twig_templating_default_desc( 'intro' . ( 'default_ext' === $intro ? '_ext' : '' ) );
		}

		$section_output = [];
		if ( $intro ) {
			$section_output[] = $intro;
		}

		if ( is_array( $title ) ) {
			if ( ! empty( $title[0] ) ) {
				$section_output[] = "<h3 class=\"desc-hl\">{$title[0]}</h3>";
			}
			if ( ! empty( $title[1] ) ) {
				$section_output[] = "<h4 class=\"desc-hl\">{$title[1]}</h4>";
			}
		} elseif ( $title ) {
			$section_output[] = "<h4 class=\"desc-hl\">{$title}</h4>";
		}

		$mandatory_contents = false;

		if ( ! empty( $tags ) ) {
			$section_output[] = '<dl>';

			foreach ( $tags as $tag ) {
				if ( ! isset( $tag_descriptions[ $tag ] ) ) {
					continue;
				}

				$tag_out      = $tag;
				$is_mandatory = false;

				if ( '-' === $tag_out[0] ) {
					// Display tag without brackets.
					$tag_out      = substr( $tag_out, 1 );
					$tag_template = '<dt><code>%s</code></dt>';
				} elseif ( '*' === $tag_out[0] ) {
					// Display as mandatory tag.
					$tag_out            = substr( $tag_out, 1 );
					$tag_template       = '<dt><code class="immonex-qformat-source mandatory">{{ %s }}</code>*</dt>';
					$is_mandatory       = true;
					$mandatory_contents = true;
				} elseif ( '[M]' === substr( $tag, -3 ) ) {
					$tag_out      = substr( $tag_out, 0, -3 );
					$tag_template = '<dt><code>{{ %s<em>.key/index.element</em> }}</code></dt>';
				} elseif ( '[]' === substr( $tag, -2 ) ) {
					$tag_out      = substr( $tag_out, 0, -2 );
					$tag_template = '<dt><code>{{ %s<em>.key/index</em> }}</code></dt>';
				} else {
					$tag_template = '<dt><code>{{ %s }}</code></dt>';
				}

				$section_output[] = wp_sprintf( $tag_template, $tag_out );
				$section_output[] = wp_sprintf(
					'<dd>%1$s%2$s</dd>',
					$tag_descriptions[ $tag ],
					$is_mandatory ? ' (<strong>' . __( 'mandatory', 'immonex-wp-free-plugin-core' ) . '</strong>)' : ''
				);
			}

			$section_output[] = '</dl>';

			if ( $mandatory_contents ) {
				$section_output[] = '<p>* ' . __( 'Mandatory contents will be added automatically if missing.', 'immonex-wp-free-plugin-core' ) . '</p>';
			}
		}

		return implode( PHP_EOL, $section_output );
	} // get_twig_templating_info_section

	/**
	 * Return default Twig templating description text of the given type.
	 *
	 * @since 1.9.0
	 *
	 * @param string $type Description type.
	 *
	 * @return string Description text.
	 */
	public function get_twig_templating_default_desc( $type = 'intro' ) {
		$defaults = [
			'intro'     => '<p>' . wp_sprintf(
				/* translators: %s = Twig 3 documentation link "Twig 3 Template Engine". */
				__( 'Mail <strong>subject, body and signature</strong> templates can contain dynamic contents like <em>variables, conditions and loops</em> based on the %s. All variables, basic control structures and examples relevant for the <strong>fields on this page</strong> are listed in the following sections and/or the descriptions of the related input fields.', 'immonex-wp-free-plugin-core' ),
				$this->string_utils->doc_link( 'https://twig.symfony.com/doc/3.x/templates.html', __( 'Twig 3 Template Engine', 'immonex-wp-free-plugin-core' ) )
			) . '</p>',
			'intro_ext' => '<p>' . wp_sprintf(
				/* translators: %s = Twig 3 documentation link "Twig 3 Template Engine". */
				__( 'Mail <strong>subject, body and signature</strong> templates can contain dynamic contents like <em>variables, conditions and loops</em> based on the %s. All variables, basic control structures and examples relevant the <strong>fields on for this page</strong> are listed in the extended description (click button below) and/or the descriptions of the related input fields.', 'immonex-wp-free-plugin-core' ),
				$this->string_utils->doc_link( 'https://twig.symfony.com/doc/3.x/templates.html', __( 'Twig 3 Template Engine', 'immonex-wp-free-plugin-core' ) )
			) . '</p>',
			'control'   => '<h3 class="desc-hl">' . __( 'Control Structures', 'immonex-wp-free-plugin-core' ) . '</h3>'
				. PHP_EOL
				. '<dl>'
				. wp_sprintf(
					'<dt><code>%1$s</code></dt><dd>%2$s</dd>' . PHP_EOL,
					'{% if <em>' . __( 'condition', 'immonex-wp-free-plugin-core' ) . '</em> %} … {% else %} … {% endif %}',
					wp_sprintf(
						/* translators: %s = documentation link "if/else statement". */
						__( '<strong>Conditional display</strong> of contents or an (optional) alternative with an %s', 'immonex-wp-free-plugin-core' ),
						$this->string_utils->doc_link( 'https://twig.symfony.com/doc/3.x/tags/if.html', __( 'if/else statement', 'immonex-wp-free-plugin-core' ) )
					)
				)
				. wp_sprintf(
					'<dt><code>%1$s</code></dt><dd>%2$s</dd>' . PHP_EOL,
					'{% for <em>' . __( 'element_name(s)', 'immonex-wp-free-plugin-core' ) . '</em> in <em>' . __( 'variable_name', 'immonex-wp-free-plugin-core' ) . '</em> % } … {{ element_name }} … {% endfor %}',
					wp_sprintf(
						/* translators: %s = documentation link "for loop". */
						__( 'Displaying elements of an array variable with a %s', 'immonex-wp-free-plugin-core' ),
						$this->string_utils->doc_link( 'https://twig.symfony.com/doc/3.x/tags/for.html', __( 'for loop', 'immonex-wp-free-plugin-core' ) )
					)
				)
				. '</dl>',
		];

		if ( ! isset( $defaults[ $type ] ) ) {
			return '';
		}

		return $defaults[ $type ];
	} // get_twig_templating_default_desc

} // Settings_Helper
