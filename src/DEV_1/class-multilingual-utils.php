<?php
/**
 * Class Multilingual_Utils
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\DEV_1;

/**
 * Multilingual environment related utilities.
 */
class Multilingual_Utils {

	/**
	 * Plugin Bootstrap Data
	 *
	 * @var mixed¢[]
	 */
	private $bootstrap_data;

	/**
	 * Plugin Slug
	 *
	 * @var string
	 */
	private $plugin_slug;

	/**
	 * Constructor
	 *
	 * @since 2.14.0
	 *
	 * @param mixed[] $bootstrap_data Plugin bootstrap data.
	 */
	public function __construct( $bootstrap_data ) {
		$this->bootstrap_data = $bootstrap_data;
		$this->plugin_slug    = $bootstrap_data['plugin_slug'];
	} // __construct

	/**
	 * Register WPML/Polylang related hooks.
	 *
	 * @since 2.14.0
	 */
	public function init() {
		$plugin_prefix = $this->bootstrap_data['plugin_prefix'];

		add_filter( "{$plugin_prefix}wpml_open_language_switch", [ $this, 'wpml_open_language_switch' ] );
		add_filter( "{$plugin_prefix}wpml_default_language", [ $this, 'wpml_default_language' ] );

		add_action( "{$plugin_prefix}wpml_close_language_switch", [ $this, 'wpml_close_language_switch' ] );
	} // init

	/**
	 * Check if Polylang (Pro) is installed and active.
	 *
	 * @since 1.8.7
	 *
	 * @return bool True if Polylang (Pro) is active.
	 */
	public static function is_polylang_active() {
		if ( function_exists( 'pll_current_language' ) ) {
			return true;
		}

		return is_plugin_active( 'polylang/polylang.php' )
			|| is_plugin_active( 'polylang-pro/polylang.php' );
	} // is_polylang_active

	/**
	 * Check if WPML is installed and active.
	 *
	 * @since 1.8.7
	 *
	 * @return bool True if WPML is active.
	 */
	public static function is_wpml_active() {
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return true;
		}

		return is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' );
	} // is_wpml_active

	/**
	 * Check if WPML OR Polylang (Pro) is installed and active.
	 *
	 * @since 1.8.7
	 *
	 * @return bool True in multilingual environments.
	 */
	public static function is_ml_env() {
		return self::is_polylang_active() || self::is_wpml_active();
	} // is_ml_env

	/**
	 * Opens a scope and returns the value for the close helper,
	 * or false if none opened (filter callback).
	 *
	 * @see https://wpml.org/errata/restoring-the-language-by-name-leaves-wpml-switched/
	 * @since 2.14.0
	 *
	 * @param string $language_code The language code to switch to.
	 *
	 * @return string|bool The previous language code, or false if none opened.
	 */
	public function wpml_open_language_switch( $language_code ) {
		// '', false and 0 open a scope without changing the language. Only null closes one.
		if ( ! $language_code ) {
			return false;
		}

		$previous_language_code = apply_filters( 'wpml_current_language', '' );

		do_action( 'wpml_switch_language', $language_code );

		return $previous_language_code;
	} // wpml_open_language_switch

	/**
	 * Closes the scope the open helper opened. WPML 4.x has no stack: there null
	 * means "the language before the FIRST switch", so restore by name.
	 *
	 * @see https://wpml.org/errata/restoring-the-language-by-name-leaves-wpml-switched/
	 * @since 2.14.0
	 *
	 * @param string $previous_language_code The language code to switch back to.
	 *
	 * @return string|bool The previous language code, or false if none opened.
	 */
	public function wpml_close_language_switch( $previous_language_code ) {
		if ( ! $previous_language_code ) {
			return;
		}

		$has_stack = defined( 'ICL_SITEPRESS_VERSION' )
			&& version_compare( ICL_SITEPRESS_VERSION, '5.0', '>=' );

		do_action( 'wpml_switch_language', $has_stack ? null : $previous_language_code );
	} // wpml_close_language_switch

	/**
	 * Get the default language code (2-letter) for WPML if active, otherwise the
	 * one of the site's default locale (filter callback).
	 *
	 * @since 2.14.0
	 *
	 * @param string|null $default_language Default language code or null.
	 *
	 * @return string Default 2-letter language code.
	 */
	public function wpml_default_language( $default_language ) {
		if ( empty( $default_language ) ) {
			$default_language = substr( get_locale(), 0, 2 );
		}

		return substr( apply_filters( 'wpml_default_language', $default_language ), 0, 2 );
	} // wpml_default_language

} // class Multilingual_Utils
