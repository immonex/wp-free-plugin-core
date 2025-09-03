<?php
/**
 * Class Cache_Buster
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\DEV_4;

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

} // Cache_Buster
