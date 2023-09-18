<?php
/**
 * The library immonex WP Free Plugin Core provides shared basic functionality
 * for free immonex WordPress plugins.
 * Copyright (C) 2014, 2023  inveris OHG / immonex
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
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\V1_8_21;

/**
 * Base class for free immonex WordPress plugins.
 *
 * @version 1.8.21
 */
abstract class Base {

	const CORE_VERSION = '1.8.21';

	/**
	 * Minimun WP capability to access the plugin options page
	 */
	const DEFAULT_PLUGIN_OPTIONS_ACCESS_CAPABILITY = 'manage_options';

	/**
	 * Plugin options array
	 *
	 * @var mixed[]
	 */
	protected $plugin_options = array();

	/**
	 * Plugin options default values
	 *
	 * @var mixed[]
	 */
	protected $default_plugin_options = array();

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
	 * Plugin information and URLs (options footer)
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
	 * Plugin slug
	 *
	 * @var string
	 */
	public $plugin_slug;

	/**
	 * Stable/Release version flag
	 *
	 * @var bool
	 */
	public $is_stable;

	/**
	 * Name of the custom field for storing plugin options
	 *
	 * @var string
	 */
	public $plugin_options_name;

	/**
	 * Settings page name/query
	 *
	 * @var string
	 */
	public $settings_page;

	/**
	 * Gettext textdomain of plugin translations
	 *
	 * @var string
	 */
	public $textdomain;

	/**
	 * Translations loaded flag
	 *
	 * @var bool
	 */
	public $translations_loaded = false;

	/**
	 * Plugin directory (full path)
	 *
	 * @var string
	 */
	public $plugin_dir;

	/**
	 * Plugin filesystem directory (full path), may differ from $this->plugin_dir)
	 *
	 * @var string
	 */
	public $plugin_fs_dir;

	/**
	 * Main plugin file (full path)
	 *
	 * @var string
	 */
	public $plugin_main_file;

	/**
	 * Main plugin file (path relative to WP plugin dir)
	 *
	 * @var string
	 */
	public $plugin_main_file_rel;

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
	 * @var Settings_Helper
	 */
	public $settings_helper;

	/**
	 * Utility object
	 *
	 * @var General_Utils
	 */
	public $general_utils;

	/**
	 * Utility object
	 *
	 * @var String_Utils
	 */
	public $string_utils;

	/**
	 * Utility object
	 *
	 * @var Geo_Utils
	 */
	public $geo_utils;

	/**
	 * Utility object
	 *
	 * @var Template_Utils
	 */
	public $template_utils;

	/**
	 * Utility object
	 *
	 * @var Color_Utils
	 */
	public $color_utils;

	/**
	 * Utility object
	 *
	 * @var Mail_Utils
	 */
	public $mail_utils;

	/**
	 * Utility object
	 *
	 * @var Local_FS_Utils
	 */
	public $local_fs_utils;

	/**
	 * Utility object
	 *
	 * @var Remote_FS_Utils
	 */
	public $remote_fs_utils;

	/**
	 * Utility object
	 *
	 * @var Multilingual_Utils
	 */
	public $ml_utils;

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
	 * CPT hook objects
	 *
	 * @var object[]
	 */
	public $cpt_hooks = array();

	/**
	 * Debug object
	 *
	 * @var Debug
	 */
	public $debug;

	/**
	 * WP_Filesystem object
	 *
	 * @var \WP_Filesystem_Base
	 */
	public $wp_filesystem;

	/**
	 * WP_Filesystem object (alias)
	 *
	 * @var \WP_Filesystem_Base
	 */
	public $fs;

	/**
	 * Has the plugin been activated network-wide?
	 *
	 * @var bool
	 */
	public $is_network_activated;

	/**
	 * Init Flag
	 *
	 * @var bool
	 */
	public $init_done = false;

	/**
	 * Utils Init Flag
	 *
	 * @var bool
	 */
	public $utils_init_done = false;

	/**
	 * Add-on Plugin Flag
	 *
	 * @var bool
	 */
	public $is_addon_plugin = false;

	/**
	 * Parent Plugin Availability Flag
	 *
	 * @var bool
	 */
	public $is_parent_plugin_active = false;

	/**
	 * Constructor: Set plugin slug and dependent variables.
	 *
	 * @since 0.1
	 *
	 * @param string      $plugin_slug Plugin slug.
	 * @param string|bool $textdomain Plugin text domain (optional, plugin slug by default).
	 * @param string|bool $plugin_fs_dir Plugin filesystem directory (optional).
	 *
	 * @throws \Exception Exception thrown if plugin slug is missing.
	 */
	public function __construct( $plugin_slug, $textdomain = false, $plugin_fs_dir = false ) {
		if ( $plugin_slug ) {
			$this->plugin_slug            = $plugin_slug;
			$this->plugin_dir             = WP_PLUGIN_DIR . '/' . $plugin_slug;
			$this->plugin_fs_dir          = $plugin_fs_dir ? $plugin_fs_dir : $this->plugin_dir;
			$this->plugin_main_file       = $this->plugin_dir . '/' . $this->plugin_slug . '.php';
			$this->plugin_main_file_rel   = $this->plugin_slug . '/' . $this->plugin_slug . '.php';
			$this->plugin_options_name    = $plugin_slug . '_options';
			$this->default_plugin_options = $this->plugin_options;

			$this->plugin_infos = array(
				'core_version'     => static::CORE_VERSION,
				'plugin_main_file' => $this->plugin_main_file,
				'name'             => defined( 'static::PLUGIN_NAME' ) ? static::PLUGIN_NAME : '',
				'logo_link_url'    => defined( 'static::PLUGIN_HOME_URL' ) ? static::PLUGIN_HOME_URL : '',
				'prefix'           => defined( 'static::PLUGIN_PREFIX' ) ? static::PLUGIN_PREFIX : '',
				'has_free_license' => ! defined( 'static::FREE_LICENSE' ) || static::FREE_LICENSE,
				'settings_page'    => '',
				'footer'           => array(),
			);
		} else {
			throw new \Exception( 'inveris WP Free Plugin Core: Plugin slug (= directory name) not provided.' );
		}

		add_filter( "{$this->plugin_slug}_plugin_infos", array( $this, 'get_plugin_infos' ) );

		$this->is_stable  = preg_match( '/^[0-9]+\.[0-9]+(\.[0-9]+)?$/', static::PLUGIN_VERSION ) ? true : false;
		$this->textdomain = $textdomain ? $textdomain : $plugin_slug;

		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';

		global $wp_filesystem;
		WP_Filesystem();
		$this->wp_filesystem = $wp_filesystem;
		$this->fs            = $wp_filesystem;

		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$this->is_network_activated = is_plugin_active_for_network( $this->plugin_main_file_rel );

		register_activation_hook( $this->plugin_main_file, array( $this, 'activate_plugin' ) );
		register_deactivation_hook( $this->plugin_main_file, array( $this, 'deactivate_plugin' ) );

		$this->bootstrap_data = array_merge(
			$this->bootstrap_data,
			array(
				'plugin_name'         => static::PLUGIN_NAME,
				'plugin_slug'         => $plugin_slug,
				'plugin_version'      => static::PLUGIN_VERSION,
				'plugin_prefix'       => static::PLUGIN_PREFIX,
				'public_prefix'       => static::PUBLIC_PREFIX,
				'plugin_dir'          => $this->plugin_dir,
				'plugin_fs_dir'       => $this->plugin_fs_dir,
				'plugin_main_file'    => $this->plugin_main_file,
				'plugin_options_name' => $this->plugin_options_name,
				'has_free_license'    => ! defined( 'static::FREE_LICENSE' ) || static::FREE_LICENSE,
			)
		);

		// Eventually add CPT data and setup related backend forms.
		$this->maybe_add_cpt_bootstrap_data();
		if ( ! empty( $this->bootstrap_data['custom_post_types'] ) ) {
			$this->setup_cpt_backend_forms();
		}

		// Set up helpers and utilities.
		$this->init_utils();

		add_action( static::PLUGIN_PREFIX . 'update_plugin_options', array( $this, 'update_plugin_options' ), 10, 2 );
		add_action( static::PLUGIN_PREFIX . 'add_deferred_admin_notice', array( $this, 'add_deferred_admin_notice' ), 10, 4 );
		add_action( static::PLUGIN_PREFIX . 'dismiss_deferred_admin_notice', array( $this, 'dismiss_admin_notice' ), 10 );
		add_action( static::PLUGIN_PREFIX . 'admin_mail', array( $this, 'send_admin_mail' ), 10, 6 );

		add_action( 'wp_ajax_dismiss_admin_notice', array( $this, 'dismiss_admin_notice' ) );
	} // __construct

	/**
	 * Add CPT names an related class names to the bootstrap data array
	 * if a nonempty class constant CUSTOM_POST_TYPES exists.
	 *
	 * @since 1.8.0
	 */
	protected function maybe_add_cpt_bootstrap_data() {
		if (
			defined( get_called_class() . '::CUSTOM_POST_TYPES' )
			&& ! empty( get_called_class()::CUSTOM_POST_TYPES )
			&& ! isset( $this->bootstrap_data['custom_post_types'] )
		) {
			$this->bootstrap_data['custom_post_types'] = array();

			foreach ( get_called_class()::CUSTOM_POST_TYPES as $cpt_base_name => $post_type_name ) {
				$class_base_name = ucwords( $cpt_base_name );

				$this->bootstrap_data['custom_post_types'][ $cpt_base_name ] = array(
					'post_type_name'  => $post_type_name,
					'class_base_name' => $class_base_name,
				);
			}
		}
	} // maybe_add_cpt_bootstrap_data

	/**
	 * Setup backend edit form(s) for plugin related CPT(s).
	 *
	 * @since 1.8.0
	 */
	protected function setup_cpt_backend_forms() {
		$reflection_class = new \ReflectionClass( get_called_class() );
		$namespace        = $reflection_class->getNamespaceName();

		foreach ( $this->bootstrap_data['custom_post_types'] as $cpt_base_name => $cpt ) {
			$class_name = "\\{$namespace}\\{$cpt['class_base_name']}_Backend_Form";

			if ( class_exists( $class_name ) ) {
				new $class_name( $this->bootstrap_data, $this );
			}
		}
	} // setup_cpt_backend_forms

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
		$value = null;

		switch ( $key ) {
			case 'bootstrap_data':
				$value = $this->bootstrap_data;
				break;
			case 'plugin_options':
				$value = $this->plugin_options;
				break;
			case 'plugin_infos':
				// @codingStandardsIgnoreLine
				$value = apply_filters( "{$this->plugin_slug}_plugin_infos", $this->plugin_infos );
				break;
			case 'cpt_hooks':
				$value = $this->cpt_hooks;
				break;
			default:
				if ( isset( $this->plugin_options[ $key ] ) ) {
					$value = $this->plugin_options[ $key ];
				}
				if ( isset( $this->bootstrap_data[ $key ] ) ) {
					$value = $this->bootstrap_data[ $key ];
				}
				if ( isset( $this->plugin_infos[ $key ] ) ) {
					$value = $this->plugin_infos[ $key ];
				}
				if ( isset( $this->utils[ $key ] ) ) {
					$value = $this->utils[ $key ];
				}
		}

		return apply_filters(
			'immonex_core_magic_get_value',
			is_null( $value ) ? false : $value,
			$key,
			! is_null( $value ),
			$this->plugin_slug
		);
	} // __get

	/**
	 * Return the current plugin debug level status (compatibility with legacy
	 * plugin core).
	 *
	 * @since 1.5.3
	 *
	 * @return int Debug level.
	 */
	public function is_debug() {
		return is_object( $this->debug ) ? $this->debug->get_debug_level() : 0;
	} // is_debug

	/**
	 * Perform activation tasks.
	 *
	 * @since 0.9
	 *
	 * @param bool $network_wide Indicate network wide activation.
	 */
	public function activate_plugin( $network_wide = false ) {
		// Pre-loading of translations is required on activation.
		$this->load_translations();

		if ( $network_wide ) {
			global $wpdb;

			// Retrieve all site IDs from this network (SQL query for compatibility reasons).
			// @codingStandardsIgnoreLine
			$site_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs} WHERE site_id = {$wpdb->siteid}" );

			foreach ( $site_ids as $site_id ) {
				switch_to_blog( $site_id );
				$this->activate_plugin_single_site();
				restore_current_blog();
			}
		} else {
			$this->activate_plugin_single_site();
		}
	} // activate_plugin

	/**
	 * Perform activation tasks for a single site.
	 *
	 * @since 1.1.0
	 */
	protected function activate_plugin_single_site() {
		// Fetch plugin options and update version.
		$this->plugin_options = $this->fetch_plugin_options();

		if ( static::PLUGIN_VERSION !== $this->plugin_options['plugin_version'] ) {
			if ( isset( $this->plugin_options['previous_plugin_version'] ) ) {
				$this->plugin_options['previous_plugin_version'] = $this->plugin_options['plugin_version'];
			}

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
	} // activate_plugin_single_site

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
		$plugin_options_access_capability = apply_filters(
			// @codingStandardsIgnoreLine
			"{$this->plugin_slug}_plugin_options_access_capability",
			self::DEFAULT_PLUGIN_OPTIONS_ACCESS_CAPABILITY
		);

		if ( empty( $plugin_options_access_capability ) ) {
			return;
		}

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
					$plugin_options_access_capability,
					$this->plugin_slug . '_settings',
					array( $this->settings_helper, 'render_page' )
				);
			} else {
				// Add options page link as submenu item.
				$options_menu_item = array(
					static::OPTIONS_LINK_MENU_LOCATION,
					$this->options_page_title,
					$this->options_link_title,
					$plugin_options_access_capability,
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

		// Exclude plugin JS/CSS from Autoptimize "optimizations".
		add_filter( 'option_autoptimize_js_exclude', array( $this, 'autoptimize_exclude' ), 10, 2 );
		add_filter( 'option_autoptimize_css_exclude', array( $this, 'autoptimize_exclude' ), 10, 2 );

		// Add filters for modifying the required user/role capability for
		// accessing and updating plugin options.
		add_filter( "{$this->plugin_slug}_plugin_options_access_capability", array( $this, 'get_default_plugin_options_access_capability' ) );
		add_filter( "option_page_capability_{$this->plugin_options_name}", array( $this, 'get_plugin_options_access_capability' ) );

		$enable_option_page          = true;
		$enable_separate_option_page = false;

		if ( defined( get_called_class() . '::PARENT_PLUGIN_MAIN_CLASS' ) ) {
			$this->is_addon_plugin         = true;
			$this->is_parent_plugin_active = class_exists( get_called_class()::PARENT_PLUGIN_MAIN_CLASS );
			$enable_option_page            = $this->is_parent_plugin_active;
		}

		if (
			$enable_option_page &&
			defined( 'static::OPTIONS_LINK_MENU_LOCATION' ) &&
			static::OPTIONS_LINK_MENU_LOCATION
		) {
			if ( 'settings' === static::OPTIONS_LINK_MENU_LOCATION ) {
				$this->settings_page = wp_sprintf(
					'options-general.php?page=%s_settings',
					$this->plugin_slug
				);

				if ( empty( $this->options_link_title ) ) {
					$this->options_link_title = static::PLUGIN_NAME;
				}
			} else {
				$this->settings_page = wp_sprintf(
					'admin.php?page=%s_settings',
					$this->plugin_slug
				);

				if ( empty( $this->options_link_title ) ) {
					$this->options_link_title = __( 'Settings', 'immonex-wp-free-plugin-core' );
				}
			}

			$this->plugin_infos['settings_page'] = $this->settings_page;

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

		if ( ! isset( $this->default_plugin_options['debug_level'] ) ) {
			$this->default_plugin_options['debug_level'] = 0;
		}

		if ( ! isset( $this->default_plugin_options['deferred_admin_notices'] ) ) {
			$this->default_plugin_options['deferred_admin_notices'] = array();
		}
	} // init_base

	/**
	 * Initialize the plugin (common).
	 *
	 * @since 0.1
	 *
	 * @param bool $fire_before_hook Flag to indicate if an action hook should fire
	 *                               before the actual method execution (optional,
	 *                               true by default).
	 * @param bool $fire_after_hook  Flag to indicate if an action hook should fire
	 *                               after the actual method execution (optional,
	 *                               true by default).
	 */
	public function init_plugin( $fire_before_hook = true, $fire_after_hook = true ) {
		if ( $fire_before_hook ) {
			do_action( 'immonex_core_before_init', $this->plugin_slug );
		}

		// Retrieve the plugin options (merge with default values).
		$this->plugin_options = $this->fetch_plugin_options();

		if (
			! $this->plugin_options['plugin_version'] ||
			version_compare( $this->plugin_options['plugin_version'], static::PLUGIN_VERSION, '<' )
		) {
			// Plugin has been updated: redo activation.
			$this->activate_plugin();
		}

		/**
		 * Check/Update the debug level.
		 */
		$this->debug          = new Debug( $this->plugin_options, $this->plugin_options_name, $this->plugin_slug );
		$this->plugin_options = $this->debug->maybe_update_debug_level( $this->plugin_options );
		$this->extend_plugin_infos();

		if ( ! empty( $this->plugin_options['skin'] ) ) {
			// Check if skin folder still exists.
			$skins = $this->utils['template']->get_frontend_skins();
			if ( ! isset( $skins[ $this->plugin_options['skin'] ] ) ) {
				$this->plugin_options['skin'] = 'default';
				update_option( $this->plugin_options_name, $this->plugin_options );
			}
		}

		if ( ! empty( $this->bootstrap_data['custom_post_types'] ) ) {
			// Register plugin-related CPT hooks.
			$this->register_cpt_hooks( array_merge( $this->bootstrap_data, $this->plugin_options ) );
		}

		// Add WP-Cron-based actions.
		add_action( static::PLUGIN_PREFIX . 'do_daily', array( $this, 'do_daily' ) );
		add_action( static::PLUGIN_PREFIX . 'do_weekly', array( $this, 'do_weekly' ) );

		// Enqueue frontend CSS and JS files.
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts_and_styles' ) );

		if ( $fire_after_hook ) {
			do_action( 'immonex_core_after_init', $this->plugin_slug );
		}
	} // init_plugin

	/**
	 * Initialize the plugin (admin/backend only).
	 *
	 * @since 0.1
	 *
	 * @param bool $fire_before_hook Flag to indicate if an action hook should fire
	 *                               before the actual method execution (optional,
	 *                               true by default).
	 * @param bool $fire_after_hook  Flag to indicate if an action hook should fire
	 *                               after the actual method execution (optional,
	 *                               true by default).
	 */
	public function init_plugin_admin( $fire_before_hook = true, $fire_after_hook = true ) {
		$plugin_options_access_capability = apply_filters(
			// @codingStandardsIgnoreLine
			"{$this->plugin_slug}_plugin_options_access_capability",
			static::DEFAULT_PLUGIN_OPTIONS_ACCESS_CAPABILITY
		);

		$is_admin = ! empty( $plugin_options_access_capability )
			&& current_user_can( $plugin_options_access_capability );

		if ( $fire_before_hook ) {
			do_action( 'immonex_core_before_init_admin', $this->plugin_slug );
		}

		// @codingStandardsIgnoreLine
		$script = isset( $_SERVER['SCRIPT_NAME'] ) ? sanitize_file_name( basename( wp_unslash( $_SERVER['SCRIPT_NAME'] ), '.php' ) ) : '';

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts_and_styles' ) );

		if ( $is_admin ) {
			add_action( 'network_admin_notices', array( $this, 'display_network_admin_notices' ) );
			add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );
		}

		// Add a "Settings" link on the plugins page.
		if ( $this->settings_page ) {
			add_filter(
				'plugin_action_links_' . $this->plugin_slug . '/' . $this->plugin_slug . '.php',
				array( $this->settings_helper, 'plugin_settings_link' )
			);
		}

		if (
			$is_admin
			&& ! empty( $this->plugin_options['deferred_admin_notices'] )
			&& is_array( $this->plugin_options['deferred_admin_notices'] )
		) {
			// Show deferred admin notices until dismissed.
			foreach ( $this->plugin_options['deferred_admin_notices'] as $id => $admin_notice ) {
				$this->add_admin_notice( $admin_notice['message'], $admin_notice['type'], $id );
			}
		}

		if ( $fire_after_hook ) {
			do_action( 'immonex_core_after_init_admin', $this->plugin_slug );
		}
	} // init_plugin_admin

	/**
	 * Instantiate plugin-related CPT "hook classes" (affiliated actions and
	 * filters will be registered).
	 *
	 * @since 1.8.0
	 *
	 * @param mixed[] $component_config Merged array of bootstrap data and plugin options.
	 */
	protected function register_cpt_hooks( $component_config ) {
		$reflection_class = new \ReflectionClass( get_called_class() );
		$namespace        = $reflection_class->getNamespaceName();

		foreach ( $this->bootstrap_data['custom_post_types'] as $cpt_base_name => $cpt ) {
			foreach ( array( '_Hooks', '_List_Hooks' ) as $class_name_suffix ) {
				$class_name = "\\{$namespace}\\{$cpt['class_base_name']}{$class_name_suffix}";

				if ( class_exists( $class_name ) ) {
					$config = array_merge(
						$component_config,
						array(
							'class_base_name' => $cpt['class_base_name'],
						)
					);

					$this->cpt_hooks[ $cpt['class_base_name'] . $class_name_suffix ] = new $class_name( $config, $this->utils );
				}
			}
		}
	} // register_cpt_hooks

	/**
	 * Instantiate and set up helper and utility classes.
	 *
	 * @since 1.7.10
	 */
	private function init_utils() {
		if ( $this->utils_init_done ) {
			return;
		}

		$this->general_utils   = new General_Utils();
		$this->string_utils    = new String_Utils();
		$this->settings_helper = new Settings_Helper(
			$this->plugin_dir,
			$this->plugin_slug,
			$this->plugin_options_name,
			$this->string_utils
		);
		$this->geo_utils       = new Geo_Utils();
		$this->template_utils  = new Template_Utils( $this );
		$this->color_utils     = new Color_Utils( $this );
		$this->mail_utils      = new Mail_Utils( $this->bootstrap_data, $this->string_utils, $this->template_utils );
		$this->local_fs_utils  = new Local_FS_Utils( $this );
		$this->remote_fs_utils = new Remote_FS_Utils( $this );
		$this->ml_utils        = new Multilingual_Utils( $this );

		$this->core_utils = array(
			'general'   => $this->general_utils,
			'settings'  => $this->settings_helper,
			'string'    => $this->string_utils,
			'geo'       => $this->geo_utils,
			'mail'      => $this->mail_utils,
			'template'  => $this->template_utils,
			'color'     => $this->color_utils,
			'local_fs'  => $this->local_fs_utils,
			'remote_fs' => $this->remote_fs_utils,
			'ml'        => $this->ml_utils,
			'wp_fs'     => $this->wp_filesystem,
		);

		if ( is_array( $this->utils ) && count( $this->utils ) > 0 ) {
			$this->utils = array_merge(
				$this->core_utils,
				$this->utils
			);
		} else {
			$this->utils = $this->core_utils;
		}

		$this->utils_init_done = true;
	} // init_utils

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
		$css_search_folders = array( 'assets/css', 'assets', 'css' );
		$js_search_folders  = array( 'assets/js', 'assets', 'js' );

		/**
		 * Plugin base CSS
		 */
		$base_css_folder = '';
		foreach ( $css_search_folders as $folder ) {
			if ( file_exists( trailingslashit( $this->plugin_dir ) . "{$folder}/frontend.css" ) ) {
				$base_css_folder = $folder;
				break;
			}
		}

		if ( $base_css_folder ) {
			$this->frontend_base_css_handle = static::PUBLIC_PREFIX . 'frontend';

			wp_enqueue_style(
				$this->frontend_base_css_handle,
				plugins_url( $this->plugin_slug . "/{$base_css_folder}/frontend.css" ),
				array(),
				$this->plugin_version
			);
		}

		/**
		 * Plugin base JS
		 */
		$base_js_folder = '';
		foreach ( $js_search_folders as $folder ) {
			if ( file_exists( trailingslashit( $this->plugin_dir ) . "{$folder}/frontend.js" ) ) {
				$base_js_folder = $folder;
				break;
			}
		}

		if ( $base_js_folder ) {
			$this->frontend_base_js_handle = static::PUBLIC_PREFIX . 'frontend';

			wp_register_script(
				$this->frontend_base_js_handle,
				plugins_url( $this->plugin_slug . "/{$base_js_folder}/frontend.js" ),
				array( 'jquery' ),
				$this->plugin_version,
				true
			);
			wp_enqueue_script( $this->frontend_base_js_handle );
		}

		if ( ! empty( $this->plugin_options['skin'] ) ) {
			$lookup_basenames = array( 'index', 'extend', 'custom', 'frontend', 'skin' );

			foreach ( $lookup_basenames as $basename ) {
				/**
				 * Skin CSS
				 */
				foreach ( $css_search_folders as $folder ) {
					$skin_css = $this->utils['template']->locate_template_file( "{$folder}/{$basename}.css" );
					if ( $skin_css ) {
						break;
					}
				}

				if ( $skin_css ) {
					$skin_css_url = $this->utils['template']->get_template_file_url( $skin_css );

					if ( $skin_css_url ) {
						$skin_css_deps = array();
						if ( wp_style_is( $this->frontend_base_css_handle ) ) {
							$skin_css_deps[] = $this->frontend_base_css_handle;
						}

						$handle = static::PUBLIC_PREFIX . 'skin';
						if ( 'index' !== $basename ) {
							$handle .= "-{$basename}";
						}

						wp_enqueue_style(
							$handle,
							$skin_css_url,
							$skin_css_deps,
							$this->plugin_version
						);
					}
				}

				/**
				 * Skin JS
				 */
				foreach ( $js_search_folders as $folder ) {
					$skin_js = $this->utils['template']->locate_template_file( "{$folder}/{$basename}.js" );
					if ( $skin_js ) {
						break;
					}
				}

				if ( $skin_js ) {
					$skin_js_url = $this->utils['template']->get_template_file_url( $skin_js );

					if ( $skin_js_url ) {
						$skin_js_deps = array( 'jquery' );
						if ( wp_script_is( $this->frontend_base_js_handle ) ) {
							$skin_js_deps[] = $this->frontend_base_js_handle;
						}

						$handle = static::PUBLIC_PREFIX . 'skin';
						if ( 'index' !== $basename ) {
							$handle .= "-{$basename}";
						}

						wp_register_script(
							$handle,
							$skin_js_url,
							$skin_js_deps,
							$this->plugin_version,
							true
						);
						wp_enqueue_script( $handle );
					}
				}
			}
		}
	} // frontend_scripts_and_styles

	/**
	 * Retrieve plugin options and filter out invalid/outdated data.
	 *
	 * @since 0.1
	 *
	 * @param mixed[]|bool $defaults Default/Valid plugin options (optional).
	 *
	 * @return mixed[] Array of current plugin options.
	 */
	public function fetch_plugin_options( $defaults = false ) {
		/**
		 * Cache flush normally not required - reactivate if problems should
		 * occur in the future.
		 * wp_cache_flush();
		 */
		$plugin_options = get_option( $this->plugin_options_name );
		$new_options    = $defaults ? $defaults : $this->default_plugin_options;

		if ( is_array( $plugin_options ) ) {
			foreach ( $plugin_options as $key => $value ) {
				if ( isset( $new_options[ $key ] ) ) {
					$new_options[ $key ] = $value;
				}
			}
		}

		return $new_options;
	} // fetch_plugin_options

	/**
	 * Update changed plugin options (action callback).
	 *
	 * @since 1.2.2
	 *
	 * @param mixed[] $update_options Subset of options to be updated.
	 * @param string  $context        Update context (optional).
	 */
	public function update_plugin_options( $update_options, $context = '' ) {
		// (Re)fetch current plugin options.
		$this->plugin_options = $this->fetch_plugin_options();
		$options_changed      = false;

		foreach ( $this->plugin_options as $key => $old_value ) {
			if (
				isset( $update_options[ $key ] )
				&& $update_options[ $key ] !== $old_value
			) {
				$this->plugin_options[ $key ] = $update_options[ $key ];
				$options_changed              = true;
			}
		}

		if ( $options_changed ) {
			update_option( $this->plugin_options_name, $this->plugin_options );
		}
	} // update_plugin_options

	/**
	 * Return the current plugin info array (filter callback).
	 *
	 * @since 1.3.0
	 *
	 * @return string[] Plugin infos.
	 */
	public function get_plugin_infos() {
		return $this->plugin_infos;
	} // get_plugin_infos

	/**
	 * Get the default capability for accessing and updating plugin options (filter callback).
	 *
	 * @since 1.3.0
	 *
	 * @return string Default or modified capability.
	 */
	public function get_default_plugin_options_access_capability() {
		return self::DEFAULT_PLUGIN_OPTIONS_ACCESS_CAPABILITY;
	} // get_default_plugin_options_access_capability

	/**
	 * Get the default capability for accessing and updating plugin options – possibly
	 * modified by another filter function (filter callback).
	 *
	 * @since 0.9
	 *
	 * @param string $cap Current capability.
	 *
	 * @return string Default or modified capability.
	 */
	public function get_plugin_options_access_capability( $cap ) {
		return apply_filters(
			// @codingStandardsIgnoreLine
			"{$this->plugin_slug}_plugin_options_access_capability",
			self::DEFAULT_PLUGIN_OPTIONS_ACCESS_CAPABILITY
		);
	} // get_plugin_options_access_capability

	/**
	 * Enqueue and localize backend JavaScript and CSS code (callback).
	 *
	 * @since 0.1
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function admin_scripts_and_styles( $hook_suffix ) {
		$ns_split            = explode( '\\', __NAMESPACE__ );
		$core_version_folder = array_pop( $ns_split );
		$core_version_handle = str_replace( '.', '-', static::CORE_VERSION );
		$core_handle         = "{$this->plugin_slug}-backend-core-{$core_version_handle}";

		/**
		 * Load core backend CSS.
		 */
		wp_enqueue_style(
			$core_handle,
			plugins_url( $this->plugin_slug . "/vendor/immonex/wp-free-plugin-core/src/{$core_version_folder}/css/backend.css" ),
			array(),
			static::CORE_VERSION
		);

		/**
		 * Load plugin-specific CSS if existent.
		 */
		$base_css_folder = '';
		foreach ( array( 'assets/css', 'assets', 'css' ) as $folder ) {
			if ( file_exists( trailingslashit( $this->plugin_dir ) . "{$folder}/backend.css" ) ) {
				$base_css_folder = $folder;
				break;
			}
		}

		if ( $base_css_folder ) {
			$this->backend_css_handle = static::PUBLIC_PREFIX . 'backend';

			wp_enqueue_style(
				$this->backend_css_handle,
				plugins_url( $this->plugin_slug . "/{$base_css_folder}/backend.css" ),
				array(),
				$this->plugin_version
			);
		}

		/**
		 * Load core backend JS first.
		 */
		wp_register_script(
			$core_handle,
			plugins_url( $this->plugin_slug . "/vendor/immonex/wp-free-plugin-core/src/{$core_version_folder}/js/backend.js" ),
			array( 'jquery' ),
			static::CORE_VERSION,
			true
		);
		wp_enqueue_script( $core_handle );

		$media_fields = $this->settings_helper->get_media_fields();

		wp_localize_script(
			$core_handle,
			'iwpfpc_params',
			array(
				'core_version'                    => static::CORE_VERSION,
				'plugin_slug'                     => $this->plugin_slug,
				'ajax_url'                        => get_admin_url() . 'admin-ajax.php',
				'media_fields'                    => $media_fields,
				'default_media_frame_title'       => __( 'Image Selection', 'immonex-wp-free-plugin-core' ),
				'default_media_frame_button_text' => __( 'Apply selection', 'immonex-wp-free-plugin-core' ),
			)
		);

		/**
		 * Load plugin-specific backend JS if existent.
		 */
		$base_js_folder = '';
		foreach ( array( 'assets/js', 'assets', 'js' ) as $folder ) {
			if ( file_exists( trailingslashit( $this->plugin_dir ) . "{$folder}/backend.js" ) ) {
				$base_js_folder = $folder;
				break;
			}
		}

		if ( $base_js_folder ) {
			$this->backend_js_handle = static::PUBLIC_PREFIX . 'backend';

			wp_register_script(
				$this->backend_js_handle,
				plugins_url( $this->plugin_slug . "/{$base_js_folder}/backend.js" ),
				array( 'jquery' ),
				$this->plugin_version,
				true
			);
			wp_enqueue_script( $this->backend_js_handle );
		}

		if ( ! empty( $media_fields ) ) {
			wp_enqueue_media();
		}
	} // admin_scripts_and_styles

	/**
	 * Display current messages for the network admin (callback).
	 *
	 * @since 1.5.2
	 */
	public function display_network_admin_notices() {
		$this->display_admin_notices( 'network' );
	} // display_network_admin_notices

	/**
	 * Display current administrative messages (callback).
	 *
	 * @param string $target "network" for network admin only notices, empty by default.
	 *
	 * @since 0.1
	 */
	public function display_admin_notices( $target = '' ) {
		$admin_notices = array_filter(
			$this->admin_notices,
			function ( $notice ) use ( $target ) {
				return $notice['target'] === $target;
			}
		);

		if ( empty( $admin_notices ) ) {
			return;
		}

		foreach ( $admin_notices as $id => $notice ) {
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

			// Remove displayed message.
			unset( $this->admin_notices[ $id ] );

			if ( 'once' === substr( $id, 0, 4 ) ) {
				$this->dismiss_admin_notice( $id );
			}
		}
	} // display_admin_notices

	/**
	 * Sanitize and validate plugin options before saving.
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
				$exists        = isset( $input[ $name ] );
				$float_field   = in_array( $field['type'], [ 'float', 'lat', 'lon' ], true );
				$int_field     = 'int' === $field['type'];
				$numeric_field = $float_field || $int_field;

				if ( 'lat' === $field['type'] ) {
					$field['min'] = -90;
					$field['max'] = 90;
				}
				if ( 'lon' === $field['type'] ) {
					$field['min'] = -180;
					$field['max'] = 180;
				}

				$value = $numeric_field ? 0 : '';
				if ( $exists ) {
					$value = is_string( $input[ $name ] ) ? trim( $input[ $name ] ) : $input[ $name ];

					if ( $float_field && ! is_float( $value ) ) {
						$value = $this->string_utils->get_float( $value );
					}

					if ( $int_field && ! is_int( $value ) ) {
						$value = (int) $this->string_utils->get_float( $value );
					}

					if ( empty( $value ) && isset( $field['default_if_empty'] ) ) {
						$value = $field['default_if_empty'];
					}
				} elseif ( isset( $field['default'] ) ) {
					$value = $field['default'];
				} elseif ( ! in_array( $field['type'], array( 'checkbox', 'checkbox_group' ), true ) ) {
					continue;
				}

				if ( isset( $field['force_type'] ) ) {
					settype( $value, $field['force_type'] );
				}

				if (
					! empty( $field['max_length'] ) &&
					is_string( $value ) &&
					$this->string_utils::mb_str_len( $value ) > $field['max_length']
				) {
					$value = $this->string_utils::mb_sub_str( trim( $value ), 0, $field['max_length'] );
				}

				if (
					isset( $field['min'] ) &&
					! is_array( $value ) &&
					(float) $value < $field['min'] &&
					! ( $field['allow_zero'] && 0 === (int) $value )
				) {
					$value = isset( $field['under_min_default'] ) ? $field['under_min_default'] : $field['min'];

					add_settings_error(
						$field['id'],
						$field['id'] . '_value_error',
						wp_sprintf(
							// translators: %1$s = field label/name, %s2$s = min. value %3$s = max. value, %4$s = "or 0".
							__( '%1$s: Please enter a value between %2$s and %3$s%4$s.', 'immonex-wp-free-plugin-core' ),
							! empty( $field['label'] ) ? $field['label'] : $field['name'],
							$field['min'],
							! empty( $field['max'] ) ? $field['max'] : __( 'unlimited', 'immonex-wp-free-plugin-core' ),
							$field['allow_zero'] ? ' ' . __( 'or 0', 'immonex-wp-free-plugin-core' ) : ''
						)
					);
				}

				if (
					isset( $field['max'] ) &&
					! is_array( $value ) &&
					(float) $value > $field['max']
				) {
					$value = isset( $field['over_max_default'] ) ? $field['over_max_default'] : $field['max'];

					add_settings_error(
						$field['id'],
						$field['id'] . '_value_error',
						wp_sprintf(
							// translators: %1$s = field label/name, %s2$s = min. value %3$s = max. value, %4$s = "or 0".
							__( '%1$s: Please enter a value between %2$s and %3$s%4$s.', 'immonex-wp-free-plugin-core' ),
							! empty( $field['label'] ) ? $field['label'] : $field['name'],
							! isset( $field['min'] ) ? $field['min'] : __( 'unlimited', 'immonex-wp-free-plugin-core' ),
							$field['max'],
							$field['allow_zero'] ? ' ' . __( 'or 0', 'immonex-wp-free-plugin-core' ) : ''
						)
					);
				}

				if ( ! empty( $field['exclude'] ) ) {
					$exclude = is_array( $field['exclude'] ) ?
						$field['exclude'] :
						[ $field['exclude'] ];

					// @codingStandardsIgnoreLine
					if ( in_array( $value, $exclude, false ) ) {
						$value = '';

						add_settings_error(
							$field['id'],
							$field['id'] . '_value_error',
							wp_sprintf(
								// translators: %s = field label/name.
								__( '%s: The entered value is not allowed in this field.', 'immonex-wp-free-plugin-core' ),
								! empty( $field['label'] ) ? $field['label'] : $field['name']
							)
						);
					}
				}

				$show_generic_required_error = false;

				switch ( $field['type'] ) {
					case 'float':
					case 'int':
					case 'lat':
					case 'lon':
						if ( ! $value && ! $field['required'] ) {
							$valid[ $name ] = '';
							break;
						}

						// Conversion has already been done above.
						$valid[ $name ] = $value;
						break;
					case 'select':
						if ( $field['required'] && ! $value ) {
							add_settings_error(
								$field['id'],
								$field['id'] . '_value_error',
								wp_sprintf(
									// translators: %s = field label/name.
									__( '%s: Please select an option.', 'immonex-wp-free-plugin-core' ),
									! empty( $field['label'] ) ? $field['label'] : $field['name']
								)
							);
							break;
						}

						if (
							$exists &&
							isset( $field['options'][ $value ] )
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
						if ( empty( $field['no_sanitize'] ) ) {
							$value = sanitize_textarea_field( $value );
						}
						if ( $field['required'] && ! $value ) {
							$show_generic_required_error = true;
							break;
						}

						$valid[ $name ] = $value;
						break;
					case 'email':
						if ( ! $value && ! $field['required'] ) {
							$valid[ $name ] = '';
							break;
						}

						if ( empty( $field['no_sanitize'] ) ) {
							$value = sanitize_email( $value );
						}
						if ( is_email( $value ) ) {
							$valid[ $name ] = $value;
						} else {
							add_settings_error(
								$field['id'],
								$field['id'] . '_value_error',
								wp_sprintf(
									// translators: %s = field label/name.
									__( '%s: Please enter a valid e-mail address.', 'immonex-wp-free-plugin-core' ),
									! empty( $field['label'] ) ? $field['label'] : $field['name']
								)
							);
						}
						break;
					case 'email_list':
						if ( ! $value && ! $field['required'] ) {
							$valid[ $name ] = '';
							break;
						}

						$email_addresses = explode( ',', $value );
						$valid_addresses = array();
						$invalid_cnt     = 0;

						if ( count( $email_addresses ) ) {
							foreach ( $email_addresses as $email ) {
								$email = empty( $field['no_sanitize'] ) ?
									sanitize_email( trim( $email ) ) :
									trim( $email );
								if ( is_email( $email ) ) {
									$valid_addresses[] = $email;
								} else {
									$invalid_cnt++;
								}
							}
						}

						$valid[ $name ] = implode( ', ', $valid_addresses );

						if ( $invalid_cnt ) {
							add_settings_error(
								$field['id'],
								$field['id'] . '_value_error',
								wp_sprintf(
									// translators: %s = field label/name.
									__( '%s: Please enter only valid e-mail addresses.', 'immonex-wp-free-plugin-core' ),
									! empty( $field['label'] ) ? $field['label'] : $field['name']
								)
							);
						}

						break;
					case 'page_id_or_url':
						if ( empty( $value ) && ! $field['required'] ) {
							$valid[ $name ] = '';
							break;
						}

						if ( empty( $field['no_sanitize'] ) ) {
							$value = is_numeric( $value ) ? (int) $value : esc_url_raw( $value );
						}

						// 999 = Fake page ID for testing.
						if (
							( is_int( $value ) && 999 !== $value && ! get_post( $value ) ) ||
							( $field['required'] && ! $value )
						) {
							add_settings_error(
								$field['id'],
								$field['id'] . '_value_error',
								wp_sprintf(
									// translators: %s = field label/name.
									__( '%s: Please enter a valid page ID or URL.', 'immonex-wp-free-plugin-core' ),
									! empty( $field['label'] ) ? $field['label'] : $field['name']
								)
							);
							break;
						}

						$valid[ $name ] = $value;

						break;
					default:
						// Normal text fields.
						if ( empty( $field['no_sanitize'] ) ) {
							$value = sanitize_text_field( $value );
						}
						if ( $field['required'] && ! $value ) {
							$show_generic_required_error = true;
							break;
						}

						$valid[ $name ] = $value;
				}

				if ( $show_generic_required_error ) {
					add_settings_error(
						$field['id'],
						$field['id'] . '_value_error',
						wp_sprintf(
							// translators: %s = field label/name.
							__( '%s: Please provide a value.', 'immonex-wp-free-plugin-core' ),
							! empty( $field['label'] ) ? $field['label'] : $field['name']
						)
					);
				}
			}
		}

		$options = apply_filters(
			// @codingStandardsIgnoreLine
			$this->plugin_slug . '_options_before_save',
			$this->settings_helper->merge_options( $this->plugin_options, $valid ),
			$this->plugin_options
		);

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
				'settings_page' => $this->settings_page,
				'debug_level'   => $this->is_debug(),
				'footer'        => $this->get_plugin_footer_infos(),
			)
		);
	} // extend_plugin_infos

	/**
	 * Delete a dismissible admin notice (callback).
	 *
	 * @param string $notice_id Notice ID (direct do_action calls only).
	 *
	 * @since 1.0.0
	 */
	public function dismiss_admin_notice( $notice_id = '' ) {
		if ( $notice_id ) {
			// Call with plugin slug in action name.
			$plugin_slug = $this->plugin_slug;
			$is_ajax     = false;
		} else {
			// AJAX request: Get notice ID and plugin slug from POST variables.
			// @codingStandardsIgnoreStart
			$notice_id   = isset( $_POST['notice_id'] ) ? sanitize_key( $_POST['notice_id'] ) : false;
			$plugin_slug = isset( $_POST['plugin_slug'] ) ? sanitize_key( $_POST['plugin_slug'] ) : false;
			// @codingStandardsIgnoreEnd

			$is_ajax = true;
		}
		if ( ! $notice_id || ! $plugin_slug ) {
			return;
		}

		if (
			$plugin_slug === $this->plugin_slug &&
			isset( $this->plugin_options['deferred_admin_notices'][ $notice_id ] )
		) {
			unset( $this->plugin_options['deferred_admin_notices'][ $notice_id ] );
			update_option( $this->plugin_options_name, $this->plugin_options );

			if ( $is_ajax ) {
				wp_die();
			}
		}
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
			$display_version = static::PLUGIN_VERSION;
			if ( defined( 'static::PLUGIN_VERSION_BYNAME' ) && static::PLUGIN_VERSION_BYNAME ) {
				$display_version .= ' "' . static::PLUGIN_VERSION_BYNAME . '"';
			}
			$infos[] = wp_sprintf( 'Version <strong>%s</strong>', $display_version );
		}

		if (
			defined( 'static::PLUGIN_DOC_URLS' ) &&
			count( static::PLUGIN_DOC_URLS ) > 0
		) {
			$infos[] = $this->get_language_link(
				static::PLUGIN_DOC_URLS,
				'<span class="dashicons-before dashicons-book" aria-hidden="true"></span>' . __( 'Documentation', 'immonex-wp-free-plugin-core' )
			);
		}

		if (
			defined( 'static::PLUGIN_SUPPORT_URLS' ) &&
			count( static::PLUGIN_SUPPORT_URLS ) > 0
		) {
			$infos[] = $this->get_language_link(
				static::PLUGIN_SUPPORT_URLS,
				'<span class="dashicons-before dashicons-sos" aria-hidden="true"></span>' . __( 'Support', 'immonex-wp-free-plugin-core' )
			);
		}

		if (
			defined( 'static::PLUGIN_DEV_URLS' ) &&
			count( static::PLUGIN_DEV_URLS ) > 0
		) {
			$infos[] = $this->get_language_link(
				static::PLUGIN_DEV_URLS,
				'<span class="dashicons-before dashicons-admin-tools" aria-hidden="true"></span>' . __( 'Development', 'immonex-wp-free-plugin-core' )
			);
		}

		return $infos;
	} // get_plugin_footer_infos

	/**
	 * Add an administrative message.
	 *
	 * @since 0.1
	 *
	 * @param string $message Message to display.
	 * @param string $type    Message type: "success" (default), "info", "warning", "error" or
	 *                        "network info/warning/error" for network admin notices.
	 * @param string $id      Message ID (required for deferred messages only).
	 */
	protected function add_admin_notice( $message, $type = 'success', $id = '' ) {
		$target = '';

		if ( false !== strpos( $type, ' ' ) ) {
			$type_split = explode( ' ', $type );
			$target     = 'network' === $type_split[0] ? 'network' : '';
			$type       = $type_split[0];
		}

		if ( ! in_array( $type, array( 'success', 'info', 'warning', 'error' ), true ) ) {
			$type = 'info';
		}

		$notice = array(
			'message'        => $message,
			'type'           => $type,
			'is_dismissable' => $id && 'once' !== substr( $id, 0, 4 ) ? true : false,
			'target'         => $target,
		);

		$this->admin_notices[ $id ? $id : uniqid() ] = $notice;
	} // add_admin_notice

	/**
	 * Add a "deferred" administrative message that will be saved as part of the
	 * plugin options (also used as action callback).
	 *
	 * @since 0.3.6
	 *
	 * @param string $message Message to display.
	 * @param string $type    Message type: "success" (default), "info", "warning", "error" or
	 *                        "network info/warning/error" for network admin notices.
	 * @param string $context Message context (e.g. if called as action hook callback; optional).
	 * @param string $id      Optional message ID for the deferred messages or or "once" for one-time display.
	 */
	public function add_deferred_admin_notice( $message, $type = 'success', $context = '', $id = '' ) {
		$raw_type = str_replace( 'network ', '', $type );

		if ( ! in_array( $raw_type, array( 'success', 'info', 'warning', 'error' ), true ) ) {
			$type = 'info';
		}

		// (Re)fetch current plugin options.
		$this->plugin_options = $this->fetch_plugin_options();

		if ( empty( $this->plugin_options['deferred_admin_notices'] ) ) {
			$this->plugin_options['deferred_admin_notices'] = array();
		} else {
			foreach ( $this->plugin_options['deferred_admin_notices'] as $notice ) {
				if ( $notice['message'] === $message ) {
					return;
				}
			}
		}

		if ( 'once' === $id && isset( $this->plugin_options['deferred_admin_notices'][ $id ] ) ) {
			$cnt = 0;
			do {
				$cnt++;
				$id = 'once_' . $cnt;
			} while ( ! isset( $this->plugin_options['deferred_admin_notices'][ $id ] ) );
		}

		$this->plugin_options['deferred_admin_notices'][ $id ? $id : uniqid() ] = array(
			'message' => $message,
			'type'    => $type,
		);

		update_option( $this->plugin_options_name, $this->plugin_options );
	} // add_deferred_admin_notice

	/**
	 * Send an admin info mail (callback).
	 *
	 * @since 1.5.0
	 *
	 * @param string               $subject       Subject.
	 * @param string|string[]      $body          Mail body (plain text only or HTML and text).
	 * @param string[]             $headers       Headers.
	 * @param string[]             $attachments   Attachment files (absolute paths).
	 * @param mixed[]              $template_data Data/Parameters for rendering the default HTML frame template.
	 * @param bool|string|string[] $to            Recipient(s) - optional, defaults to false (site/network admin).
	 */
	public function send_admin_mail( $subject, $body, $headers = array(), $attachments = array(), $template_data = array(), $to = false ) {
		if ( empty( $to ) ) {
			$to = get_option( 'admin_email' );
		}

		$template_data['preset']    = 'admin_info';
		$template_data['bootstrap'] = $this->bootstrap_data;

		$this->mail_utils->send( $to, $subject, $body, $headers, $attachments, $template_data );
	} // send_admin_mail

	/**
	 * Exclude plugin JS/CSS from Autoptimize "optimizations".
	 *
	 * @since 1.3.2
	 *
	 * @param string $value  Current exclusion patterns/terms.
	 * @param string $option Option name.
	 *
	 * @return string Extended list of exclusion patterns/terms.
	 */
	public function autoptimize_exclude( $value, $option ) {
		if ( false === strpos( $value, 'immonex' ) ) {
			return implode( ', ', array( $value, 'immonex' ) );
		}

		return $value;
	} // autoptimize_exclude

	/**
	 * Perform daily tasks.
	 *
	 * @since 1.6.0
	 */
	public function do_daily() {
		do_action( 'immonex_core_do_daily', $this->plugin_slug );
	} // do_daily

	/**
	 * Perform weekly tasks.
	 *
	 * @since 1.6.0
	 */
	public function do_weekly() {
		do_action( 'immonex_core_do_weekly', $this->plugin_slug );
	} // do_daily

	/**
	 * Show error messages during plugin activation.
	 *
	 * @since 0.2
	 *
	 * @param string $message Options form values.
	 * @param int    $errno Error number.
	 */
	protected function br_trigger_error( $message, $errno ) {
		if ( isset( $_GET['action'] ) && 'error_scrape' === $_GET['action'] ) {
			echo $message;
			exit;
		} else {
			// @codingStandardsIgnoreLine
			trigger_error( $message, $errno );
		}
	} // br_trigger_error

	/**
	 * Load translations.
	 *
	 * @param bool $force_reload Maybe force a reload if translations have already
	 *                           been loaded (optional).
	 *
	 * @since 0.1
	 */
	protected function load_translations( $force_reload = false ) {
		if ( $this->translations_loaded && ! $force_reload ) {
			return;
		}

		$locale              = get_user_locale();
		$ns_split            = explode( '\\', __NAMESPACE__ );
		$core_version_folder = array_pop( $ns_split );

		if ( 'de_' === substr( $locale, 0, 3 ) ) {
			// Use default translations for all German locales.
			$locale = 'de_DE';
		}

		/**
		 * Load plugin base translations first.
		 */
		load_textdomain(
			'immonex-wp-free-plugin-core',
			wp_sprintf(
				'%s/%s/vendor/immonex/wp-free-plugin-core/src/%s/languages/immonex-wp-free-plugin-core-%s.mo',
				WP_PLUGIN_DIR,
				$this->plugin_slug,
				$core_version_folder,
				$locale
			)
		);

		/**
		 * Load plugin translations.
		 */
		$always_load_global_translations = apply_filters(
			// @codingStandardsIgnoreLine
			$this->plugin_slug . '_always_load_global_translations',
			false
		);

		if ( $this->is_stable || $always_load_global_translations ) {
			load_plugin_textdomain( $this->textdomain, false, $this->plugin_slug . '/languages' );
		} else {
			$local_mo_file = wp_sprintf(
				'%s/%s/languages/%s-%s.mo',
				WP_PLUGIN_DIR,
				$this->plugin_slug,
				$this->textdomain,
				$locale
			);
			if ( file_exists( $local_mo_file ) ) {
				load_textdomain( $this->textdomain, $local_mo_file );
			}
		}

		$this->translations_loaded = true;
	} // load_translations

	/**
	 * Check if the given hook suffix belongs to a plugin admin page.
	 *
	 * @since 1.8.0
	 *
	 * @param string          $hook_suffix Hook suffix.
	 * @param string|string[] $alternative_parts Alternative string parts as array or RegEx (optional).
	 *
	 * @return bool Comparison result.
	 */
	protected function is_plugin_admin_page( $hook_suffix, $alternative_parts = '' ) {
		$regex_parts = array(
			preg_replace( '/^immonex-/', '', $this->plugin_slug ),
			static::PLUGIN_PREFIX,
		);

		if ( ! empty( $alternative_parts ) && is_array( $alternative_parts ) ) {
			$alternative_parts = implode( '|', $alternative_parts );
		}

		$regex = implode( '|', $regex_parts ) . ( $alternative_parts ? '|' . $alternative_parts : '' );

		return preg_match( "/{$regex}/", $hook_suffix );
	} // is_plugin_admin_page

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

		$lang = substr( get_user_locale(), 0, 2 );
		$href = empty( $urls[ $lang ] ) ? array_values( $urls )[0] : $urls[ $lang ];

		return wp_sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			$href,
			$link_text
		);
	} // get_language_link

} // Base
