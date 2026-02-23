<?php
/**
 * Class Plugin_Cache
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\V2_8_0;

/**
 * Simple plugin-related common cache.
 */
class Plugin_Cache {

	const DEFAULT_TRANSIENT_EXPIRATION = DAY_IN_SECONDS;

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
		add_filter( "{$this->plugin_prefix}cache_get_transient", [ $this, 'get_cache_transient' ], 10, 3 );

		add_action( "{$this->plugin_prefix}cache_set", [ $this, 'set' ], 10, 2 );
		add_action( "{$this->plugin_prefix}cache_flush", [ $this, 'flush' ], 10, 2 );
		add_action( "{$this->plugin_prefix}cache_set_transient", [ $this, 'set_cache_transient' ], 10, 5 );
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

	/**
	 * Get a cache transient part (filter callback).
	 *
	 * @since 2.8.0
	 *
	 * @param mixed       $value     Default value.
	 * @param string      $transient Transient key.
	 * @param string|bool $part_key  Part key (optional).
	 *
	 * @return mixed Cache part value, false if nonexistent or expired.
	 */
	public function get_cache_transient( $value, $transient, $part_key = false ) {
		$transient_value = get_transient( $transient );
		if ( ! $transient_value ) {
			return $value;
		}

		if ( ! $part_key ) {
			$part_key = 'default';
		}

		if ( empty( $transient_value[ $part_key ] ) ) {
			return $value;
		}

		if (
			! empty( $transient_value[ $part_key ]['exp'] )
			&& $transient_value[ $part_key ]['exp'] < time()
		) {
			// PART expired, remove it and update the transient.
			unset( $transient_value[ $part_key ] );

			$timeout    = get_option( "_transient_timeout_{$transient}" );
			$expiration = (int) $timeout ? (int) $timeout - time() : self::DEFAULT_TRANSIENT_EXPIRATION;

			set_transient( $transient, $transient_value, $expiration );

			return $value;
		}

		return ! empty( $transient_value[ $part_key ]['value'] ) ? $transient_value[ $part_key ]['value'] : $value;
	} // get_cache_transient

	/**
	 * Set a cache transient part (action callback).
	 *
	 * @since 2.8.0
	 *
	 * @param string      $transient       Transient key.
	 * @param mixed       $value           Value to cache.
	 * @param string|bool $part_key        Part key (optional).
	 * @param int|bool    $expiration      Transient expiration time in seconds (optional).
	 * @param int|bool    $part_expiration Cache part expiration time in seconds (optional).
	 */
	public function set_cache_transient( $transient, $value, $part_key = false, $expiration = false, $part_expiration = false ) {
		if ( ! $part_key ) {
			$part_key = 'default';
		}

		if ( ! $expiration ) {
			$expiration = self::DEFAULT_TRANSIENT_EXPIRATION;
		}

		$cache = get_transient( $transient );
		if ( empty( $cache ) || ! is_array( $cache ) ) {
			$cache = [];
		}

		$cache[ $part_key ] = [
			'value' => $value,
			'exp'   => (int) $part_expiration ? time() + (int) $part_expiration : false,
		];

		set_transient( $transient, $cache, $expiration );
	} // set_cache_transient

} // Plugin_Cache
