<?php
/**
 * Class Plugin_Cache
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\DEV_8;

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
		add_filter( "{$this->plugin_prefix}cache_get_db_stats", [ $this, 'get_db_transient_stats' ], 10, 2 );

		add_action( "{$this->plugin_prefix}cache_set", [ $this, 'set' ], 10, 2 );
		add_action( "{$this->plugin_prefix}cache_flush", [ $this, 'flush' ], 10, 2 );
		add_action( "{$this->plugin_prefix}cache_set_transient", [ $this, 'set_cache_transient' ], 10, 5 );
		add_action( "{$this->plugin_prefix}cache_delete_db_transients", [ $this, 'delete_db_transients' ] );
		add_action( "{$this->plugin_prefix}cache_clean_up_db_transients", [ $this, 'clean_up_db_transients' ], 10, 2 );
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
	 * @param string      $transient     Transient name.
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
	 * @param string        $transient  Transient name.
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

		$cache      = $this->clean_up_transient( get_transient( $transient ) );
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
			'exp'   => $part_expiration > 0 ? time() + $part_expiration : -1,
		];

		if ( count( $cache ) > $max_items ) {
			array_shift( $cache );
		}

		set_transient( $transient, $cache, $expiration );
	} // set_cache_transient

	/**
	 * Delete all DB-based cache transients with the given name prefixes (action callback).
	 *
	 * @since 2.9.0
	 *
	 * @param string[] $transient_prefixes Transient name prefixes.
	 */
	public function delete_db_transients( $transient_prefixes ) {
		$transient_items = $this->get_db_transients( $transient_prefixes );

		if ( ! empty( $transient_items ) ) {
			foreach ( $transient_items as $item ) {
				delete_transient( $item['name'] );
			}
		}
	} // delete_db_transients

	/**
	 * Delete DB-based cache transients (and expired parts) with the given
	 * name prefix if expired or an optional maximum size was exceeded
	 * (action callback).
	 *
	 * @since 2.11.1
	 *
	 * @param string[] $transient_prefixes Transient name prefixes.
	 * @param int      $max_size_mb        Optional maximum size (MB) of all transients (0 = no size limit).
	 */
	public function clean_up_db_transients( $transient_prefixes, $max_size_mb = 0 ) {
		if ( ! is_array( $transient_prefixes ) ) {
			$transient_prefixes = [ $transient_prefixes ];
		}

		$transient_items = $this->get_db_transients( $transient_prefixes, 'full' );

		if ( empty( $transient_items ) ) {
			return;
		}

		$total_size_bytes = 0;

		foreach ( $transient_items as $i => $item ) {
			if ( empty( $item['name'] ) ) {
				continue;
			}

			if ( ! empty( $item['exp'] ) && $item['exp'] < time() ) {
				delete_transient( $item['name'] );
				unset( $transient_items[ $i ] );
				continue;
			}

			$total_size_bytes += ! empty( $item['size'] ) ? (int) $item['size'] : 0;
			$transient         = get_transient( $item['name'] );

			if ( ! empty( $transient ) ) {
				$transient_part_count = count( $transient );
				$transient            = $this->clean_up_transient( $transient );

				if ( count( $transient ) !== $transient_part_count ) {
					// One or more expired parts have been removed, update the transient.
					$expiration = ! empty( $item['exp'] ) ? (int) $item['exp'] - time() : self::DEFAULT_TRANSIENT_EXPIRATION_SEC;
					set_transient( $item['name'], $transient, $expiration );
				}
			}
		}

		$total_size_mb = ceil( $total_size_bytes / ( 1024 * 1024 ) );

		if ( $max_size_mb && $total_size_mb > $max_size_mb ) {
			/**
			 * Size limit exceeded, delete transients starting with the oldest
			 * until the total size is below the limit.
			 */
			foreach ( $transient_items as $i => $item ) {
				delete_transient( $item['name'] );

				$total_size_bytes -= ! empty( $item['size'] ) ? (int) $item['size'] : 0;
				$total_size_mb     = ceil( $total_size_bytes / ( 1024 * 1024 ) );

				if ( $total_size_mb <= $max_size_mb ) {
					break;
				}
			}
		}
	} // clean_up_db_transients

	/**
	 * Generate a statistic (number/sizes) of the current cache transients by
	 * the given key prefixes (filter callback).
	 *
	 * @since 2.11.1
	 *
	 * @param mixed[]  $stats              Empty array.
	 * @param string[] $transient_prefixes Transient name prefixes.
	 *
	 * @return mixed[] Transient items (arrays with keys 'name', 'size' and 'exp').
	 */
	public function get_db_transient_stats( $stats, $transient_prefixes ) {
		if ( ! is_array( $transient_prefixes ) ) {
			$transient_prefixes = [ $transient_prefixes ];
		}

		$stats = [
			'total_count'   => 0,
			'total_size_kb' => 0,
			'total_size_mb' => 0,
		];

		foreach ( $transient_prefixes as $prefix ) {
			$group_items = $this->get_db_transients( $prefix, 'size' );

			if ( ! empty( $group_items ) ) {
				$plain_prefix = trim( $prefix, '_' );
				$group_size   = 0;

				foreach ( $group_items as $item ) {
					$group_size += $item['size'];
				}

				$stats[ $plain_prefix ] = [
					'count'   => count( $group_items ),
					'size_kb' => ceil( $group_size / 1024 ),
					'size_mb' => ceil( $group_size / ( 1024 * 1024 ) ),
				];

				$stats['total_count']   += $stats[ $plain_prefix ]['count'];
				$stats['total_size_kb'] += $stats[ $plain_prefix ]['size_kb'];
				$stats['total_size_mb'] += $stats[ $plain_prefix ]['size_mb'];
			}
		}

		return $stats;
	} // get_db_transient_stats

	/**
	 * Get DB-based transients by the given name prefixes.
	 *
	 * @since 2.11.1
	 *
	 * @param string[] $transient_prefixes Transient name prefixes.
	 * @param string   $mode               Optional retrieval mode:
	 *                                       - names (default): transient names only
	 *                                       - full: transient names, sizes in bytes and expiration timestamps,
	 *                                               ordered by expiration ascending
	 *
	 * @return mixed[] Transient items (arrays with keys 'name', 'size' and 'exp').
	 */
	public function get_db_transients( $transient_prefixes, $mode = 'names' ) {
		global $wpdb;

		$query = 'full' === $mode ?
			"SELECT o1.option_name, LENGTH(o1.option_value) AS size, o2.option_value AS exp FROM {$wpdb->options} o1 LEFT JOIN {$wpdb->options} o2 ON o2.option_name = REPLACE(o1.option_name, '_transient_', '_transient_timeout_') WHERE o1.option_name LIKE CONCAT('_transient_', '%s') ORDER BY exp ASC, o1.option_id ASC" :
			"SELECT `option_name` FROM {$wpdb->options} WHERE `option_name` LIKE CONCAT('_transient_', %s)";

		if ( ! is_array( $transient_prefixes ) ) {
			$transient_prefixes = [ $transient_prefixes ];
		}

		$transient_items = [];

		foreach ( $transient_prefixes as $prefix ) {
			$transient_like = $prefix . '%';
			$sql            = $wpdb->prepare( $query, $transient_like ); // phpcs:ignore -- WordPress.DB.PreparedSQL.NotPrepared Variable query strings defined above.
			$result         = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore

			if ( ! empty( $result ) ) {
				foreach ( $result as $item ) {
					$transient_items[] = [
						'name' => substr( $item['option_name'], 11 ), // Remove "_transient_" prefix.
						'size' => ! empty( $item['size'] ) ? $item['size'] : null,
						'exp'  => ! empty( $item['exp'] ) ? $item['exp'] : null,
					];
				}
			}
		}

		if ( 'full' === $mode && ! empty( $transient_items ) ) {
			usort(
				$transient_items,
				function ( $a, $b ) {
					if ( empty( $a['exp'] ) && empty( $b['exp'] ) ) {
						return 0;
					} elseif ( empty( $a['exp'] ) ) {
						return 1;
					} elseif ( empty( $b['exp'] ) ) {
						return -1;
					}

					return $a['exp'] < $b['exp'] ? -1 : 1;
				}
			);
		}

		return $transient_items;
	} // get_db_transients

	/**
	 * Clean up a single cache transient (remove expired parts).
	 *
	 * @since 2.11.1
	 *
	 * @param mixed[] $transient_value Transient value (parts) to clean up.
	 *
	 * @return mixed[] Cleaned up transient parts.
	 */
	private function clean_up_transient( $transient_value ) {
		if ( empty( $transient_value ) || ! is_array( $transient_value ) ) {
			return [];
		}

		foreach ( $transient_value as $key => $part ) {
			if (
				! empty( $part['exp'] )
				&& $part['exp'] < time()
			) {
				// Part expired, remove it.
				unset( $transient_value[ $key ] );
			}
		}

		return $transient_value;
	} // clean_up_transient

} // Plugin_Cache
