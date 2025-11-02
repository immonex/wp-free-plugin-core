<?php
/**
 * Class Cache_Buster
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\V2_6_0;

/**
 * Exclude immonex plugin related JS/CSS from "optimizations" of common caching solutions.
 */
class Cache_Buster {

	/**
	 * Plugin slug
	 *
	 * @var string
	 */
	private $plugin_slug;

	/**
	 * Constructor
	 *
	 * @since 2.4.6
	 *
	 * @param string $plugin_slug Plugin slug.
	 */
	public function __construct( $plugin_slug ) {
		$this->plugin_slug = $plugin_slug;
	} // __construct

	/**
	 * Register cache related hooks.
	 *
	 * @since 2.4.6
	 */
	public function init() {
		/**
		 * LiteSpeed Cache (LSCache)
		 */
		if ( is_plugin_active( 'litespeed-cache/litespeed-cache.php' ) ) {
			add_filter( 'litespeed_optm_uri_exc', [ $this, 'add_lscache_excludes' ] );
			add_filter( 'litespeed_optimize_js_excludes', [ $this, 'add_lscache_excludes' ] );
			add_filter( 'litespeed_optm_js_defer_exc', [ $this, 'add_lscache_excludes' ] );
			add_filter( 'litespeed_optm_gm_js_exc', [ $this, 'add_lscache_excludes' ] );
		}

		/**
		 * Autoptimize
		 */
		if ( is_plugin_active( 'autoptimize/autoptimize.php' ) ) {
			add_filter( 'option_autoptimize_js_exclude', [ $this, 'autoptimize_exclude' ], 10, 2 );
			add_filter( 'option_autoptimize_css_exclude', [ $this, 'autoptimize_exclude' ], 10, 2 );
		}
	} // __construct

	/**
	 * Add plugin folder to Add LiteSpeed Cache (LSCache) JS exclude lists
	 * (filter callback).
	 *
	 * @see https://docs.litespeedtech.com/lscache/lscwp/api/#exclude-javascript-from-optimization
	 *
	 * @since 2.4.6
	 *
	 * @param string[] $excludes Current excludes or empty array.
	 *
	 * @return string[] Extended excludes.
	 */
	public function add_lscache_excludes( $excludes ) {
		return array_unique(
			array_merge(
				$excludes,
				[ $this->plugin_slug ]
			)
		);
	} // add_lscache_excludes

	/**
	 * Exclude plugin JS/CSS from Autoptimize "optimizations".
	 *
	 * @since 2.6.0
	 *
	 * @param string $value  Current exclusion patterns/terms.
	 * @param string $option Option name.
	 *
	 * @return string Extended list of exclusion patterns/terms.
	 */
	public function autoptimize_exclude( $value, $option ) {
		if ( false === strpos( $value, 'immonex' ) ) {
			return implode( ', ', [ $value, 'immonex' ] );
		}

		return $value;
	} // autoptimize_exclude

} // Cache_Buster
