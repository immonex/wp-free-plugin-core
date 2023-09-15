<?php
/**
 * Class Multilingual_Utils
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\DEV_2;

/**
 * Multilingual environment related utilities.
 */
class Multilingual_Utils {

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

} // class Multilingual_Utils
