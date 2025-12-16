<?php
/**
 * Class Plugin_Cache
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\DEV_1;

/**
 * Simple plugin-related common cache.
 */
class Plugin_Cache {

	/**
	 * Plugin prefix
	 *
	 * @var string
	 */
	private $plugin_prefix;

	/**
	 * Cached items
	 *
	 * @var mixed[]
	 */
	private $cache;

	/**
	 * Constructor
	 *
	 * @since 2.6.0
	 *
	 * @param string $plugin_prefix Plugin prefix.
	 */
	public function __construct( $plugin_prefix ) {
		$this->plugin_prefix = $plugin_prefix;
	} // __construct

	/**
	 * Register cache related hooks.
	 *
	 * @since 2.6.0
	 */
	public function init() {
		add_filter( "{$this->plugin_prefix}cache_get", [ $this, 'get' ], 10, 3 );

		add_action( "{$this->plugin_prefix}cache_set", [ $this, 'set' ], 10, 2 );
		add_action( "{$this->plugin_prefix}cache_flush", [ $this, 'flush' ], 10, 2 );
	} // init

	/**
	 * Return the cache item value for the given key if existing, a default
	 * value of false otherwise (filter callback).
	 *
	 * @since 2.6.0
	 *
	 * @param mixed  $value Default value.
	 * @param string $key   Cache item key.
	 *
	 * @return mixed Cache item value, default value or false.
	 */
	public function get( $value, $key ) {
		if ( isset( $this->cache[ $key ] ) ) {
			return $this->cache[ $key ];
		}

		return ! is_null( $value ) ? $value : false;
	} // get

	/**
	 * Set the cache value for the given key (action callback).
	 *
	 * @since 2.6.0
	 *
	 * @param string $key   Cache item key.
	 * @param mixed  $value Cache item value.
	 */
	public function set( $key, $value ) {
		$this->cache[ $key ] = $value;
	} // set

	/**
	 * Delete one cache item value or flush the whole cache (action callback).
	 *
	 * @since 2.6.0
	 *
	 * @param string|bool $key Cache item key or false to flush the whole cache (optional).
	 */
	public function flush( $key = false ) {
		if ( $key && isset( $this->cache[ $key ] ) ) {
			unset( $this->cache[ $key ] );
			return;
		}

		$this->cache = [];
	} // flush

} // Plugin_Cache
