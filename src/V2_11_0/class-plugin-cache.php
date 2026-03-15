<?php
/**
 * Class Plugin_Cache
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\V2_11_0;

/**
 * Simple plugin-related common cache.
 */
class Plugin_Cache {

	const DEFAULT_TRANSIENT_EXPIRATION_SEC = DAY_IN_SECONDS;
	const DEFAULT_MAX_ITEMS_PER_TRANSIENT  = 3;
	const DEFAULT_COMPRESSION_THRESHOLD_KB = 20;

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
		add_action( "{$this->plugin_prefix}cache_delete_db_transients", [ $this, 'delete_db_transients' ] );
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
	 * @param mixed       $default_value Default value.
	 * @param string      $transient     Transient key.
	 * @param string|bool $part_key      Part key (optional).
	 *
	 * @return mixed Cache part value, false if nonexistent or expired.
	 */
	public function get_cache_transient( $default_value, $transient, $part_key = false ) {
		$transient_value = get_transient( $transient );
		if ( false === $transient_value ) {
			return $default_value;
		}

		if ( ! $part_key ) {
			$part_key = 'default';
		}

		if ( empty( $transient_value[ $part_key ] ) ) {
			return $default_value;
		}

		if (
			! empty( $transient_value[ $part_key ]['exp'] )
			&& $transient_value[ $part_key ]['exp'] < time()
		) {
			// PART expired, remove it and update the transient.
			unset( $transient_value[ $part_key ] );

			$timeout    = get_option( "_transient_timeout_{$transient}" );
			$expiration = (int) $timeout ? (int) $timeout - time() : self::DEFAULT_TRANSIENT_EXPIRATION_SEC;

			set_transient( $transient, $transient_value, $expiration );

			return $default_value;
		}

		$value = $transient_value[ $part_key ]['value'];
		if ( ! empty( $transient_value[ $part_key ]['compr'] ) ) {
			// phpcs:ignore -- Base64 encoding is required to avoid issues with storing binary data in the database.
			$value = gzinflate( base64_decode( $value ) );
		}

		$value = maybe_unserialize( $value );

		return $value ? $value : $default_value;
	} // get_cache_transient

	/**
	 * Set a cache transient part (action callback).
	 *
	 * @since 2.8.0
	 *
	 * @param string        $transient  Transient key.
	 * @param mixed|mixed[] $value      Value to cache.
	 * @param string|bool   $part_key   Part key (optional).
	 * @param int|bool      $expiration Transient expiration time in seconds (optional).
	 * @param mixed[]       $params     Optional additional parameters:
	 *                                    - part_expiration: part expiration time in seconds
	 *                                      (-1 = never expire)
	 *                                    - max_items: maximum number of cache items per transient
	 *                                    - compress_th: compression threshold in KB
	 *                                      (-1 = no compression)
	 */
	public function set_cache_transient( $transient, $value, $part_key = false, $expiration = false, $params = [] ) {
		if ( ! $part_key ) {
			$part_key = 'default';
		}

		if ( ! $expiration ) {
			$expiration = self::DEFAULT_TRANSIENT_EXPIRATION_SEC;
		}

		$part_expiration = isset( $params['part_expiration'] ) ?
			(int) $params['part_expiration'] : self::DEFAULT_TRANSIENT_EXPIRATION_SEC;
		$max_items       = ! empty( $params['max_items'] ) ?
			$params['max_items'] : self::DEFAULT_MAX_ITEMS_PER_TRANSIENT;
		$compress_th     = isset( $params['compress_th'] ) ?
			(int) $params['compress_th'] : self::DEFAULT_COMPRESSION_THRESHOLD_KB;

		$cache = get_transient( $transient );
		if ( empty( $cache ) || ! is_array( $cache ) ) {
			$cache = [];
		}

		$value      = maybe_serialize( $value );
		$compressed = false;

		if ( $compress_th && strlen( $value ) > $compress_th * 1024 ) {
			// phpcs:ignore -- Base64 encoding is required to avoid issues with storing binary data in the database.
			$value      = base64_encode( gzdeflate( $value ) );
			$compressed = true;
		}

		$cache[ $part_key ] = [
			'value' => $value,
			'compr' => $compressed,
			'exp'   => time() + $part_expiration,
		];

		if ( count( $cache ) > $max_items ) {
			$part_keys = array_reverse( array_keys( $cache ) );
			$new_cache = [];

			$i = 0;
			foreach ( $part_keys as $key ) {
				if ( $i >= $max_items ) {
					break;
				}

				$new_cache[ $key ] = $cache[ $key ];
				++$i;
			}

			$cache = $new_cache;
		}

		set_transient( $transient, $cache, $expiration );
	} // set_cache_transient

	/**
	 * Delete DB-based transients by given transient key prefixes (action callback).
	 *
	 * @since 2.9.0
	 *
	 * @param string[] $transient_prefixes Transient key prefixes.
	 */
	public function delete_db_transients( $transient_prefixes ) {
		global $wpdb;

		if ( ! is_array( $transient_prefixes ) ) {
			$transient_prefixes = [];
		}

		foreach ( $transient_prefixes as $prefix ) {
			$transient_like  = "_transient_{$prefix}%";
			$sql             = $wpdb->prepare(
				"SELECT `option_name` FROM {$wpdb->options} WHERE `option_name` LIKE %s",
				$transient_like
			);
			$transient_items = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore

			if ( ! empty( $transient_items ) ) {
				foreach ( $transient_items as $item ) {
					$transient_name = substr( $item['option_name'], 11 ); // Remove "_transient_" prefix.
					delete_transient( $transient_name );
				}
			}
		}
	} // delete_db_transients

} // Plugin_Cache
