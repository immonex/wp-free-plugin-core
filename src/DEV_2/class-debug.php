<?php
/**
 * Class Debug
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\DEV_2;

/**
 * Debugging-related methods.
 */
class Debug {

	const MAX_DEBUG_LEVEL = 20;

	/**
	 * Plugin options
	 *
	 * @var mixed[]
	 */
	private $plugin_options;

	/**
	 * Plugin options name
	 *
	 * @var string
	 */
	private $plugin_options_name;

	/**
	 * Plugin slug
	 *
	 * @var string
	 */
	private $plugin_slug;

	/**
	 * Constructor
	 *
	 * @since 1.5.3
	 *
	 * @param mixed[] $plugin_options      Current plugin options.
	 * @param string  $plugin_options_name Plugin options name.
	 * @param string  $plugin_slug         Plugin slug.
	 */
	public function __construct( $plugin_options, $plugin_options_name, $plugin_slug ) {
		$this->plugin_options      = $plugin_options;
		$this->plugin_options_name = $plugin_options_name;
		$this->plugin_slug         = $plugin_slug;
	} // __construct

	/**
	 * Check (GET variable) and possibly update the current debug level (plugin options).
	 *
	 * @since 1.5.3
	 *
	 * @param mixed[]|bool $plugin_options Current plugin options or false (default)
	 *                                     to use the corresponding object property.
	 *
	 * @return mixed[] Original or updated plugin options.
	 */
	public function maybe_update_debug_level( $plugin_options = false ) {
		if ( ! $plugin_options ) {
			$plugin_options = $this->plugin_options;
		}

		if ( ! isset( $plugin_options['debug_level'] ) ) {
			$plugin_options['debug_level'] = 0;
		}

		$plugin_settings_page_name        = $this->plugin_slug . '_settings';
		$plugin_options_access_capability = apply_filters(
			// @codingStandardsIgnoreLine
			"{$this->plugin_slug}_plugin_options_access_capability",
			Base::DEFAULT_PLUGIN_OPTIONS_ACCESS_CAPABILITY
		);

		if (
			! isset( $_GET['page'] )
			|| sanitize_key( $_GET['page'] ) !== $plugin_settings_page_name
			|| empty( $plugin_options_access_capability )
			|| ! current_user_can( $plugin_options_access_capability )
		) {
			return $plugin_options;
		}

		if ( ! empty( $_GET['enable-debug'] ) ) {
			$debug_level = (int) $_GET['enable-debug'];

			if (
				( $debug_level && $debug_level <= self::MAX_DEBUG_LEVEL )
				&& (int) $plugin_options['debug_level'] !== $debug_level
			) {
				$plugin_options['debug_level'] = $debug_level;
				update_option( $this->plugin_options_name, $plugin_options );
			}
		} elseif (
			! empty( $_GET['disable-debug'] )
			&& (int) $plugin_options['debug_level'] > 0
		) {
			$plugin_options['debug_level'] = 0;
			update_option( $this->plugin_options_name, $plugin_options );
		}

		$this->plugin_options = $plugin_options;

		return $plugin_options;
	} // maybe_update_debug_level

	/**
	 * Return the current debug level (plugin options) or 0 if not set.
	 *
	 * @since 1.5.3
	 *
	 * @return int Current debug level.
	 */
	public function get_debug_level() {
		return isset( $this->plugin_options['debug_level'] ) ? $this->plugin_options['debug_level'] : 0;
	} // get_debug_level

} // Debug
