<?php
/**
 * The library immonex WP Free Plugin Core provides shared basic functionality
 * for free immonex WordPress plugins.
 * Copyright (C) 2014, 2020  inveris OHG / immonex
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 *
 * This file contains the base class for deriving the main classes of
 * immonex plugins.
 *
 * @package immonex-wp-free-plugin-core
 */

namespace immonex\WordPressFreePluginCore\V1_0_0;

/**
 * Base class for free immonex WordPress plugins.
 *
 * @package immonex-wp-free-plugin-core
 * @version 1.0.0
 */
abstract class Base {

	const BASE_VERSION = '1.0';

	/**
	 * Name of the custom field for storing plugin options
	 *
	 * @var string
	 */
	protected $plugin_options_name;

	/**
	 * Name of the custom field for storing plugin options
	 *
	 * @var mixed[]
	 */
	protected $plugin_options = array();

	/**
	 * Minimun WP capability to access the plugin options page
	 *
	 * @var string
	 */
	protected $plugin_options_access_capability = 'manage_options';

	/**
	 * Does the plugin has its own options page?
	 *
	 * @var bool
	 */
	protected $enable_separate_option_page = false;

	/**
	 * Name of the Link that leads to the plugin's options page
	 *
	 * @var string
	 */
	protected $options_link_title = '';

	/**
	 * Title (HTML head) of the plugin's options page (if any)
	 *
	 * @var string
	 */
	protected $options_page_title = '';

	/**
	 * Plugin information and URLs for displaying in the options footer
	 *
	 * @var string
	 */
	protected $plugin_infos = array();

	/**
	 * Priority for the init action (init_plugin method)
	 *
	 * @var string
	 */
	protected $init_plugin_priority;

	/**
	 * Set of core plugin data (name, slug, version etc.)
	 *
	 * @var string[]
	 */
	protected $bootstrap_data = array();

	/**
	 * Admin notices to display
	 *
	 * @var mixed[]
	 */
	protected $admin_notices = array();

	/**
	 * Translations already loaded?
	 *
	 * @var bool
	 */
	protected $translations_loaded = false;

	/**
	 * Plugin slug
	 *
	 * @var string
	 */
	public $plugin_slug;

	/**
	 * Gettext textdomain of plugin translations
	 *
	 * @var string
	 */
	public $textdomain;

	/**
	 * Plugin directory (full path)
	 *
	 * @var string
	 */
	public $plugin_dir;

	/**
	 * Main plugin file (full path)
	 *
	 * @var string
	 */
	public $plugin_main_file;

	/**
	 * Handle for enqueuing the main backend CSS file
	 *
	 * @var string
	 */
	public $backend_css_handle;

	/**
	 * Handle for enqueuing the main backend JS file
	 *
	 * @var string
	 */
	public $backend_js_handle;

	/**
	 * Handle for enqueuing the main frontend CSS file
	 *
	 * @var string
	 */
	public $frontend_base_css_handle;

	/**
	 * Handle for enqueuing the main frontend JS file
	 *
	 * @var string
	 */
	public $frontend_base_js_handle;

	/**
	 * Utility object
	 *
	 * @var \immonex\WordPressFreePluginCore\V1_0_0\Settings_Helper
	 */
	public $settings_helper;

	/**
	 * Utility object
	 *
	 * @var \immonex\WordPressFreePluginCore\V1_0_0\General_Utils
	 */
	public $general_utils;

	/**
	 * Utility object
	 *
	 * @var \immonex\WordPressFreePluginCore\V1_0_0\String_Utils
	 */
	public $string_utils;

	/**
	 * Utility object
	 *
	 * @var \immonex\WordPressFreePluginCore\V1_0_0\Geo_Utils
	 */
	public $geo_utils;

	/**
	 * Utility object
	 *
	 * @var \immonex\WordPressFreePluginCore\V1_0_0\Template_Utils
	 */
	public $template_utils;

	/**
	 * Set of all utility class instances mentioned above
	 *
	 * @var object[]
	 */
	public $core_utils;

	/**
	 * Additional utility objects (will be merged with core utils)
	 *
	 * @var object[]
	 */
	public $utils = array();

	/**
	 * WP_Filesystem object
	 *
	 * @var \WP_Filesystem_Base
	 */
	public $wp_filesystem;

	/**
	 * Has the plugin been activated network-wide?
	 *
	 * @var bool
	 */
	public $is_network_activated;

	/**
	 * Constructor: Set plugin slug and dependent variables.
	 *
	 * @since 0.1
	 *
	 * @param string      $plugin_slug Plugin slug.
	 * @param string|bool $textdomain Plugin text domain.
	 *
	 * @throws \Exception Exception thrown if plugin slug is missing.
	 */
	public function __construct( $plugin_slug, $textdomain = false ) {
		if ( $plugin_slug ) {
			$this->plugin_slug          = $plugin_slug;
			$this->plugin_dir           = WP_PLUGIN_DIR . '/' . $plugin_slug;
			$this->plugin_main_file     = $this->plugin_dir . '/' . $this->plugin_slug . '.php';
			$this->plugin_main_file_rel = $this->plugin_slug . '/' . $this->plugin_slug . '.php';
			$this->plugin_options_name  = $plugin_slug . '_options';

			$this->plugin_infos = array(
				'plugin_main_file' => $this->plugin_main_file,
				'settings_page'    => '',
			);
		} else {
			throw new \Exception( 'inveris WP Free Plugin Core: Plugin slug (= directory name) not provided.' );
		}

		$this->textdomain = $textdomain;

		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';

		global $wp_filesystem;
		WP_Filesystem();
		$this->wp_filesystem = $wp_filesystem;

		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$this->is_network_activated = is_plugin_active_for_network( $this->plugin_main_file_rel );

		register_activation_hook( $this->plugin_main_file, array( $this, 'activate_plugin' ) );
		register_deactivation_hook( $this->plugin_main_file, array( $this, 'deactivate_plugin' ) );

		$this->bootstrap_data = array_merge(
			$this->bootstrap_data,
			array(
				'plugin_name'      => static::PLUGIN_NAME,
				'plugin_slug'      => $plugin_slug,
				'plugin_version'   => static::PLUGIN_VERSION,
				'plugin_prefix'    => static::PLUGIN_PREFIX,
				'public_prefix'    => static::PUBLIC_PREFIX,
				'plugin_dir'       => $this->plugin_dir,
				'plugin_main_file' => $this->plugin_main_file,
			)
		);

		add_action( 'wp_ajax_dismiss_admin_notice', array( $this, 'dismiss_admin_notice' ) );
	} // __construct

	/**
	 * Get values/objects from plugin options, bootstrap data, plugin infos
	 * or utils.
	 *
	 * @since 0.9
	 *
	 * @param string $key Option/Object name.
	 *
	 * @return mixed Requested Value/Object or false if nonexistent.
	 */
	public function __get( $key ) {
		switch ( $key ) {
			case 'bootstrap_data':
				return $this->bootstrap_data;
			case 'plugin_infos':
				return $this->plugin_infos;
			case 'utils':
				return $this->utils;
			default:
				if ( isset( $this->plugin_options[ $key ] ) ) {
					return $this->plugin_options[ $key ];
				}
				if ( isset( $this->bootstrap_data[ $key ] ) ) {
					return $this->bootstrap_data[ $key ];
				}
				if ( isset( $this->plugin_infos[ $key ] ) ) {
					return $this->plugin_infos[ $key ];
				}
				if ( isset( $this->utils[ $key ] ) ) {
					return $this->utils[ $key ];
				}
		}

		return false;
	} // __get

	/**
	 * Perform activation tasks.
	 *
	 * @since 0.9
	 */
	public function activate_plugin() {
		// Pre-loading of translations is required on activation.
		$this->load_translations();

		// Fetch plugin options and update version.
		$this->plugin_options = $this->fetch_plugin_options( $this->plugin_options );
		if ( static::PLUGIN_VERSION !== $this->plugin_options['plugin_version'] ) {
			$this->plugin_options['plugin_version'] = static::PLUGIN_VERSION;
			update_option( $this->plugin_options_name, $this->plugin_options );
		}

		// Schedule frequent tasks.
		if ( ! wp_get_schedule( static::PLUGIN_PREFIX . 'do_daily' ) ) {
			wp_schedule_event( time(), 'daily', static::PLUGIN_PREFIX . 'do_daily' );
		}
		if ( ! wp_get_schedule( static::PLUGIN_PREFIX . 'do_weekly' ) ) {
			wp_schedule_event( time(), 'weekly', static::PLUGIN_PREFIX . 'do_weekly' );
		}
	} // activate_plugin

	/**
	 * Perform deactivation tasks.
	 *
	 * @since 0.9
	 */
	public function deactivate_plugin() {
		wp_clear_scheduled_hook( static::PLUGIN_PREFIX . 'do_daily' );
		wp_clear_scheduled_hook( static::PLUGIN_PREFIX . 'do_weekly' );
	} // deactivate_plugin

	/**
	 * Register plugin settings.
	 *
	 * @since 0.9
	 */
	public function register_plugin_settings() {
		// All plugin options are stored in one serialized array.
		register_setting(
			$this->plugin_options_name,
			$this->plugin_options_name,
			array(
				'sanitize_callback' => array( $this, 'sanitize_plugin_options' ),
			)
		);

		if ( $this->enable_separate_option_page ) {
			if ( 'settings' === static::OPTIONS_LINK_MENU_LOCATION ) {
				// Add options page link in WP default settings menu.
				add_options_page(
					$this->options_page_title,
					$this->options_link_title,
					$this->plugin_options_access_capability,
					$this->plugin_slug . '_settings',
					array( $this->settings_helper, 'render_page' )
				);
			} else {
				// Add options page link as submenu item.
				$options_menu_item = array(
					static::OPTIONS_LINK_MENU_LOCATION,
					$this->options_page_title,
					$this->options_link_title,
					$this->plugin_options_access_capability,
					$this->plugin_slug . '_settings',
					array( $this->settings_helper, 'render_page' ),
					900,
				);

				call_user_func_array( 'add_submenu_page', $options_menu_item );
			}
		}
	} // register_plugin_settings

	/**
	 * Start plugin initialization.
	 *
	 * @param int $init_plugin_priority Priority for the init action
	 *                                  (init_plugin method).
	 *
	 * @since 0.1
	 */
	public function init( $init_plugin_priority = 10 ) {
		$this->init_plugin_priority = $init_plugin_priority;
		add_action( 'plugins_loaded', array( $this, 'init_base' ) );
	} // init

	/**
	 * Perform core initialization tasks.
	 *
	 * @since 0.1
	 */
	public function init_base() {
		$this->load_translations();

		add_action( 'init', array( $this, 'init_plugin' ), $this->init_plugin_priority );
		add_action( 'widgets_init', array( $this, 'init_plugin_widgets' ) );
		add_action( 'admin_init', array( $this, 'init_plugin_admin' ) );
		add_action( 'admin_menu', array( $this, 'register_plugin_settings' ) );

		// Add a filter for modifying the required user/role capability for
		// accessing and updating plugin options.
		add_filter( 'option_page_capability_' . $this->plugin_options_name, array( $this, 'get_plugin_options_access_capability' ) );

		$enable_separate_option_page = false;

		if (
			defined( 'static::OPTIONS_LINK_MENU_LOCATION' ) &&
			static::OPTIONS_LINK_MENU_LOCATION
		) {
			if ( 'settings' === static::OPTIONS_LINK_MENU_LOCATION ) {
				$this->plugin_infos['settings_page'] = wp_sprintf(
					'options-general.php?page=%s_settings',
					$this->plugin_slug
				);

				if ( empty( $this->options_link_title ) ) {
					$this->options_link_title = static::PLUGIN_NAME;
				}
			} else {
				$this->plugin_infos['settings_page'] = wp_sprintf(
					'admin.php?page=%s_settings',
					$this->plugin_slug
				);

				if ( empty( $this->options_link_title ) ) {
					$this->options_link_title = __( 'Settings', 'immonex-wp-free-plugin-core' );
				}
			}

			if ( empty( $this->options_page_title ) ) {
				$this->options_page_title = static::PLUGIN_NAME . ' - ' .
					__( 'Settings', 'immonex-wp-free-plugin-core' );
			}

			$enable_separate_option_page = true;
		}

		$this->enable_separate_option_page = apply_filters(
			// @codingStandardsIgnoreLine
			$this->plugin_slug . '_enable_separate_option_page',
			$enable_separate_option_page
		);

		if ( ! isset( $this->plugin_options['deferred_admin_notices'] ) ) {
			$this->plugin_options['deferred_admin_notices'] = array();
		}
	} // init_base

	/**
	 * Initialize the plugin (common).
	 *
	 * @since 0.1
	 */
	public function init_plugin() {
		// Retrieve the plugin options (merge with default values).
		$this->plugin_options = $this->fetch_plugin_options( $this->plugin_options );

		$this->extend_plugin_infos();

		$this->plugin_options_access_capability = apply_filters(
			// @codingStandardsIgnoreLine
			$this->plugin_slug . 'plugin_options_access_capability',
			$this->plugin_options_access_capability
		);

		/**
		 * Include and instantiate helper and utility classes.
		 */
		$this->general_utils   = new General_Utils();
		$this->settings_helper = new Settings_Helper(
			$this->plugin_dir,
			$this->plugin_slug,
			$this->plugin_options_name,
			$this->plugin_infos,
			$this->plugin_options_access_capability
		);
		$this->string_utils    = new String_Utils();
		$this->geo_utils       = new Geo_Utils();
		$this->template_utils  = new Template_Utils(
			$this,
			! empty( $this->plugin_options['skin'] ) ?
				$this->plugin_options['skin'] :
				''
		);
		$this->color_utils     = new Color_Utils( $this );

		$this->core_utils = array(
			'general'  => $this->general_utils,
			'settings' => $this->settings_helper,
			'string'   => $this->string_utils,
			'geo'      => $this->geo_utils,
			'template' => $this->template_utils,
			'color'    => $this->color_utils,
		);

		if ( is_array( $this->utils ) && count( $this->utils ) > 0 ) {
			$this->utils = array_merge(
				$this->core_utils,
				$this->utils
			);
		} else {
			$this->utils = $this->core_utils;
		}

		if ( ! empty( $this->plugin_options['skin'] ) ) {
			// Check if skin folder still exists.
			$skins = $this->utils['template']->get_frontend_skins();
			if ( ! in_array( $this->plugin_options['skin'], array_keys( $skins ) ) ) {
				$this->plugin_options['skin'] = 'default';
				update_option( $this->plugin_options_name, $this->plugin_options );
			}
		}

		// Add base WP-Cron intervals.
		add_filter( 'cron_schedules', array( $this, 'add_base_wp_cron_intervals' ) );

		// Enqueue frontend CSS and JS files.
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts_and_styles' ) );
	} // init_plugin

	/**
	 * Initialize the plugin (admin/backend only).
	 *
	 * @since 0.1
	 */
	public function init_plugin_admin() {
		if (
			( isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'] ) &&
			( isset( $_GET['page'] ) && $this->plugin_slug . '_settings' === $_GET['page'] ) &&
			'options-general' !== basename( $_SERVER['SCRIPT_NAME'], '.php' )
		) {
			$this->add_admin_notice( __( 'Settings updated.', 'immonex-wp-free-plugin-core' ) );
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts_and_styles' ) );
		add_action(
			$this->is_network_activated ? 'network_admin_notices' : 'admin_notices',
			array( $this, 'display_admin_notices' )
		);

		// Add a "Settings" link on the plugins page.
		if ( $this->enable_separate_option_page ) {
			add_filter(
				'plugin_action_links_' . $this->plugin_slug . '/' . $this->plugin_slug . '.php',
				array( $this->settings_helper, 'plugin_settings_link' )
			);
		}

		if (
			is_array( $this->plugin_options['deferred_admin_notices'] ) &&
			count( $this->plugin_options['deferred_admin_notices'] ) > 0
		) {
			if (
				is_array( $this->plugin_options['deferred_admin_notices'] ) &&
				count( $this->plugin_options['deferred_admin_notices'] ) > 0
			) {
				// Show deferred admin notices until dismissed.
				foreach ( $this->plugin_options['deferred_admin_notices'] as $id => $admin_notice ) {
					$this->add_admin_notice( $admin_notice['message'], $admin_notice['type'], $id );
				}
			}
		}

		if (
			! $this->plugin_options['plugin_version'] ||
			version_compare( $this->plugin_options['plugin_version'], static::PLUGIN_VERSION, '<' )
		) {
			// Plugin has been updated: redo activation.
			$this->activate_plugin();
		}
	} // init_plugin_admin

	/**
	 * Add base WP-Cron interval(s).
	 *
	 * @param mixed[] $schedules Current schedules.
	 *
	 * @since 0.7
	 */
	public function add_base_wp_cron_intervals( $schedules ) {
		if ( ! isset( $schedules['weekly'] ) ) {
			$schedules['weekly'] = array(
				'interval' => 604800, // Time in seconds.
				'display'  => __( 'weekly', 'immonex-wp-free-plugin-core' ),
			);
		}

		return $schedules;
	} // add_base_wp_cron_intervals

	/**
	 * Load and register widgets (to be overwritten in derived classes).
	 *
	 * @since 0.1
	 */
	public function init_plugin_widgets() {}

	/**
	 * Enqueue and localize frontend scripts and styles.
	 *
	 * @since 0.9
	 */
	public function frontend_scripts_and_styles() {
		/**
		 * Plugin base CSS
		 */
		if ( file_exists( trailingslashit( $this->plugin_dir ) . 'css/frontend.css' ) ) {
			$this->frontend_base_css_handle = static::PUBLIC_PREFIX . 'frontend';

			wp_enqueue_style(
				$this->frontend_base_css_handle,
				plugins_url( $this->plugin_slug . '/css/frontend.css' ),
				array(),
				$this->plugin_version
			);
		}

		/**
		 * Plugin base JS
		 */
		if ( file_exists( trailingslashit( $this->plugin_dir ) . 'js/frontend.js' ) ) {
			$this->frontend_base_js_handle = static::PUBLIC_PREFIX . 'frontend-js';

			wp_register_script(
				$this->frontend_base_js_handle,
				plugins_url( $this->plugin_slug . '/js/frontend.js' ),
				array( 'jquery' ),
				$this->plugin_version,
				true
			);
			wp_enqueue_script( $this->frontend_base_js_handle );
		}

		if ( ! empty( $this->plugin_options['skin'] ) ) {
			/**
			 * Skin CSS
			 */
			$skin_css = $this->utils['template']->locate_template_file( 'css/index.css' );
			if ( $skin_css ) {
				$skin_css_url = $this->utils['template']->get_template_file_url( $skin_css );

				if ( $skin_css_url ) {
					$skin_css_deps = array();
					if ( wp_style_is( $this->frontend_base_css_handle ) ) {
						$skin_css_deps[] = $this->frontend_base_css_handle;
					}

					wp_enqueue_style(
						static::PUBLIC_PREFIX . 'skin',
						$skin_css_url,
						$skin_css_deps,
						$this->plugin_version
					);
				}
			}

			$skin_js = $this->utils['template']->locate_template_file( 'js/index.js' );
			if ( $skin_js ) {
				$skin_js_url = $this->utils['template']->get_template_file_url( $skin_js );

				if ( $skin_js_url ) {
					$skin_js_deps = array( 'jquery' );
					if ( wp_script_is( $this->frontend_base_js_handle ) ) {
						$skin_js_deps[] = $this->frontend_base_js_handle;
					}

					wp_register_script(
						static::PUBLIC_PREFIX . 'skin-js',
						$skin_js_url,
						$skin_js_deps,
						$this->plugin_version,
						true
					);
					wp_enqueue_script( static::PUBLIC_PREFIX . 'skin-js' );
				}
			}
		}
	} // frontend_scripts_and_styles

	/**
	 * Retrieve plugin options and filter out invalid/outdated data.
	 *
	 * @since 0.1
	 *
	 * @param array $valid_options Default plugin options.
	 *
	 * @return array Array of current plugin options.
	 */
	public function fetch_plugin_options( $valid_options ) {
		/**
		 * Cache flush normally not required - reactivate if problems should
		 * occur in the future.
		 * wp_cache_flush();
		 */
		$plugin_options = get_option( $this->plugin_options_name );
		$new_options    = $valid_options;

		if ( is_array( $plugin_options ) ) {
			foreach ( $plugin_options as $key => $value ) {
				if ( isset( $valid_options[ $key ] ) ) {
					$new_options[ $key ] = $value;
				}
			}
		}

		return $new_options;
	} // fetch_plugin_options

	/**
	 * Possibly set a new capability for accessing and updating plugin options.
	 *
	 * @since 0.9
	 *
	 * @param string $cap Current capability.
	 *
	 * @return string (Possibly) updated capability.
	 */
	public function get_plugin_options_access_capability( $cap ) {
		return $this->plugin_options_access_capability;
	} // get_plugin_options_access_capability

	/**
	 * Enqueue and localize backend JavaScript and CSS code (callback).
	 *
	 * @since 0.1
	 *
	 * @param string $hook_suffix The current admin page..
	 */
	public function admin_scripts_and_styles( $hook_suffix ) {
		if ( ! is_admin() ) {
			return;
		}

		/**
		 * Load plugin-specific CSS if existent.
		 */
		if ( file_exists( trailingslashit( $this->plugin_dir ) . 'css/backend.css' ) ) {
			$this->backend_css_handle = static::PUBLIC_PREFIX . 'backend';

			wp_enqueue_style(
				$this->backend_css_handle,
				plugins_url( $this->plugin_slug . '/css/backend.css' ),
				array(),
				$this->plugin_version
			);
		}

		/**
		 * Load core backend JS first.
		 */
		$ns_split            = explode( '\\', __NAMESPACE__ );
		$core_version        = array_pop( $ns_split );
		$core_version_handle = str_replace( '_', '-', substr( $core_version, 1 ) );
		$core_version_semver = str_replace( '-', '.', $core_version_handle );
		$core_js_handle      = static::PUBLIC_PREFIX . "backend-js-core-{$core_version_handle}";

		wp_register_script(
			$core_js_handle,
			plugins_url( $this->plugin_slug . "/vendor/immonex/wp-free-plugin-core/src/{$core_version}/js/backend.js" ),
			array( 'jquery' ),
			$core_version_semver,
			true
		);
		wp_enqueue_script( $core_js_handle );

		wp_localize_script(
			$core_js_handle,
			'iwpfpc_params',
			array(
				'core_version' => $core_version_semver,
				'plugin_slug'  => $this->plugin_slug,
				'ajax_url'     => get_admin_url() . 'admin-ajax.php',
			)
		);

		/**
		 * Load plugin-specific backend JS if existent.
		 */
		if ( file_exists( trailingslashit( $this->plugin_dir ) . 'js/backend.js' ) ) {
			$this->backend_js_handle = static::PUBLIC_PREFIX . 'backend-js';

			wp_register_script(
				$this->backend_js_handle,
				plugins_url( $this->plugin_slug . '/js/backend.js' ),
				array( 'jquery' ),
				$this->plugin_version,
				true
			);
			wp_enqueue_script( $this->backend_js_handle );
		}
	} // admin_scripts_and_styles

	/**
	 * Display current administrative messages (callback).
	 *
	 * @since 0.1
	 */
	public function display_admin_notices() {
		if ( count( $this->admin_notices ) > 0 ) {
			// Display admin messages.
			$dismissables_cnt = 0;
			foreach ( $this->admin_notices as $id => $notice ) {
				if ( $notice['is_dismissable'] ) {
					$dismissables_cnt++;
				}
				if ( $dismissables_cnt > 1 ) {
					break;
				}
			}

			foreach ( $this->admin_notices as $id => $notice ) {
				$message = $notice['message'];
				$classes = array(
					'notice',
					'notice-' . $notice['type'],
					$this->plugin_slug . '-notice',
				);

				if ( $notice['is_dismissable'] ) {
					$classes[] = 'is-dismissible';
				}

				echo wp_sprintf(
					'<div class="%s" data-notice-id="%s"><p>%s</p></div>' . PHP_EOL,
					implode( ' ', $classes ),
					$id,
					$message
				);
			}
		}

		// Reset admin messages after display.
		$this->admin_notices = array();
	} // display_admin_notices

	/**
	 * Sanitize plugin options before saving.
	 *
	 * @since 0.9
	 *
	 * @param array $input Submitted form data.
	 *
	 * @return array Valid inputs.
	 */
	public function sanitize_plugin_options( $input ) {
		$valid       = array();
		$current_tab = $this->settings_helper->get_current_tab();
		$tab_fields  = $this->settings_helper->get_tab_fields( $current_tab );

		if ( count( $tab_fields ) > 0 ) {
			foreach ( $tab_fields as $name => $field ) {
				$exists = isset( $input[ $name ] );

				$value = '';
				if ( $exists ) {
					$value = $input[ $name ];
				} elseif ( isset( $field['default'] ) ) {
					$value = $field['default'];
				}

				if ( isset( $field['force_type'] ) ) {
					settype( $value, $field['force_type'] );
				}

				if (
					! empty( $field['max_length'] ) &&
					is_string( $value ) &&
					strlen( $value ) > $field['max_length']
				) {
					$value = substr( trim( $value ), 0, $field['max_length'] );
				}

				if (
					isset( $field['min'] ) &&
					is_numeric( $value ) &&
					$value < $field['min']
				) {
					$value = $field['min'];
				}

				if (
					isset( $field['max'] ) &&
					is_numeric( $value ) &&
					$value > $field['max']
				) {
					$value = $field['max'];
				}

				switch ( $field['type'] ) {
					case 'select':
						if (
							$exists &&
							in_array( $value, array_keys( $field['options'] ) )
						) {
							$valid[ $name ] = $value;
						} elseif ( isset( $field['options'][0] ) ) {
							$valid[ $name ] = array_keys( $field['options'] )[0];
						}
						break;
					case 'checkbox':
						$valid[ $name ] = $exists;
						break;
					case 'checkbox_group':
						if ( $exists ) {
							$valid[ $name ] = $value;
						} else {
							$valid[ $name ] = array();
						}
						break;
					case 'wysiwyg':
						$valid[ $name ] = wp_kses_post( trim( $value ) );
						break;
					case 'textarea':
						$valid[ $name ] = sanitize_textarea_field( $value );
						break;
					default:
						// Normal text fields.
						$valid[ $name ] = sanitize_text_field( $value );
				}
			}
		}

		$options = $this->settings_helper->merge_options( $this->plugin_options, $valid );

		return $options;
	} // sanitize_plugin_options

	/**
	 * Extend plugin information for displaying on the options page/tab.
	 *
	 * @since 0.9
	 */
	public function extend_plugin_infos() {
		$this->plugin_infos = array_merge(
			$this->plugin_infos,
			array(
				'name'          => defined( 'static::PLUGIN_NAME' ) ? static::PLUGIN_NAME : '',
				'prefix'        => defined( 'static::PLUGIN_PREFIX' ) ? static::PLUGIN_PREFIX : '',
				'logo_link_url' => defined( 'static::PLUGIN_HOME_URL' ) ? static::PLUGIN_HOME_URL : '',
				'footer'        => array(),
			)
		);

		$this->plugin_infos['footer'] = $this->get_plugin_footer_infos();
	} // extend_plugin_infos

	/**
	 * Delete a dismissible admin notice (AJAX callback).
	 *
	 * @since 1.0.0
	 */
	public function dismiss_admin_notice() {
		$notice_id   = sanitize_key( $_POST['notice_id'] );
		$plugin_slug = sanitize_key( $_POST['plugin_slug'] );
		if ( ! $notice_id || ! $plugin_slug ) {
			wp_die( '', '', array( 'response' => 400 ) );
		}

		if (
			$plugin_slug === $this->plugin_slug &&
			isset( $this->plugin_options['deferred_admin_notices'][ $notice_id ] )
		) {
			unset( $this->plugin_options['deferred_admin_notices'][ $notice_id ] );
			update_option( $this->plugin_options_name, $this->plugin_options );
		}

		wp_die();
	} // dismiss_admin_notice

	/**
	 * Compile arbitrary plugin information and doc/support links if given.
	 *
	 * @since 0.9
	 *
	 * @return string[] Array of info/link elements.
	 */
	protected function get_plugin_footer_infos() {
		$infos = array();

		if ( defined( 'static::PLUGIN_VERSION' ) ) {
			$infos[] = 'Version <strong>' . static::PLUGIN_VERSION . '</strong>';
		}

		if (
			defined( 'static::PLUGIN_DOC_URLS' ) &&
			count( static::PLUGIN_DOC_URLS ) > 0
		) {
			$infos[] = $this->get_language_link(
				static::PLUGIN_DOC_URLS,
				__( 'Documentation', 'immonex-wp-free-plugin-core' )
			);
		}

		if (
			defined( 'static::PLUGIN_SUPPORT_URLS' ) &&
			count( static::PLUGIN_SUPPORT_URLS ) > 0
		) {
			$infos[] = $this->get_language_link(
				static::PLUGIN_SUPPORT_URLS,
				'Support'
			);
		}

		if (
			defined( 'static::PLUGIN_DEV_URLS' ) &&
			count( static::PLUGIN_DEV_URLS ) > 0
		) {
			$infos[] = $this->get_language_link(
				static::PLUGIN_DEV_URLS,
				__( 'Development', 'immonex-wp-free-plugin-core' )
			);
		}

		return $infos;
	} // get_plugin_footer_infos

	/**
	 * Add a "deferred" administrative message that will be saved as part of the
	 * plugin configuration.
	 *
	 * @since 0.3.6
	 *
	 * @param string $message Message to display.
	 * @param string $type Message type: "success" (default), "info", "warning", "error".
	 */
	protected function add_deferred_admin_notice( $message, $type = 'success' ) {
		$notice_id = uniqid();

		if ( ! in_array( $type, array( 'success', 'info', 'warning', 'error' ) ) ) {
			$type = 'info';
		}

		// (Re)fetch current plugin options.
		$this->plugin_options = $this->fetch_plugin_options( $this->plugin_options );

		if ( count( $this->plugin_options['deferred_admin_notices'] ) ) {
			foreach ( $this->plugin_options['deferred_admin_notices'] as $notice ) {
				if ( $notice['message'] === $message ) {
					return;
				}
			}
		}

		$this->plugin_options['deferred_admin_notices'][ $notice_id ] = array(
			'message' => $message,
			'type'    => $type,
		);

		update_option( $this->plugin_options_name, $this->plugin_options );
	} // add_deferred_admin_notice

	/**
	 * Add an administrative message.
	 *
	 * @since 0.1
	 *
	 * @param string $message Message to display.
	 * @param string $type Message type: "success" (default), "info", "warning", "error".
	 * @param string $id Message ID (required for deferred messages only).
	 */
	protected function add_admin_notice( $message, $type = 'success', $id = false ) {
		if ( ! in_array( $type, array( 'success', 'info', 'warning', 'error' ) ) ) {
			$type = 'info';
		}

		$this->admin_notices[ $id ? $id : uniqid() ] = array(
			'message'        => $message,
			'type'           => $type,
			'is_dismissable' => $id ? true : false,
		);
	} // add_admin_notice

	/**
	 * Show error messages during plugin activation.
	 *
	 * @since 0.2
	 *
	 * @param string $message Options form values.
	 * @param int    $errno Error number.
	 */
	protected function br_trigger_error( $message, $errno ) {
		if ( isset( $_GET['action'] ) && 'error_scrape' == $_GET['action'] ) {
			echo '<strong>' . $message . '</strong>';
			exit;
		} else {
			trigger_error( $message, $errno );
		}
	} // br_trigger_error

	/**
	 * Load translations.
	 *
	 * @since 0.1
	 */
	protected function load_translations() {
		if ( $this->translations_loaded ) {
			return;
		}

		$domain = $this->textdomain ? $this->textdomain : $this->plugin_slug;
		$locale = get_locale();

		// Load plugin base translations first.
		load_plugin_textdomain( 'immonex-wp-free-plugin-core', false, $this->plugin_slug . '/vendor/immonex/wp-free-plugin-core/languages' );

		// Load plugin translations.
		if ( file_exists( trailingslashit( WP_LANG_DIR ) . 'plugins/' . $this->plugin_slug . '-' . $locale . '.mo' ) ) {
			load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . 'plugins/' . $this->plugin_slug . '-' . $locale . '.mo' );
		} else {
			load_plugin_textdomain( $domain, false, $this->plugin_slug . '/languages' );
		}

		$this->translations_loaded;
	} // load_translations

	/**
	 * Get a link tag related to the current language (or the default url).
	 *
	 * @since 0.9
	 *
	 * @param string[] $urls Array of URLs with language code as keys.
	 * @param string   $link_text Link text.
	 *
	 * @return string HTML link tag.
	 */
	private function get_language_link( $urls, $link_text ) {
		if ( empty( $urls ) ) {
			return '';
		}

		$lang = substr( get_locale(), 0, 2 );
		$href = empty( $urls[ $lang ] ) ? array_values( $urls )[0] : $urls[ $lang ];

		return wp_sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			$href,
			$link_text
		);
	} // get_language_link

} // Base
