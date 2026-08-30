<?php
/**
 * Class Array_Utils
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\V2_13_7;

/**
 * Array-related helpers.
 */
class Array_Utils {

	/**
	 * Check if an array contains an non-empty element with the given key
	 * (incl. wildcards, e.g. 'foo*' matches 'foobar').
	 *
	 * @since 2.11.4
	 *
	 * @param string  $key Element key to search for.
	 * @param mixed[] $arr Array to be searched.
	 *
	 * @return bool True if the element sought exists.
	 */
	public static function array_has( $key, $arr ) {
		if ( empty( $arr ) || ! is_array( $arr ) ) {
			return false;
		}

		$starts_with = '*' === substr( $key, -1 ) ? substr( $key, 0, -1 ) : false;
		$ends_with   = '*' === $key[0] ? substr( $key, 1 ) : false;

		foreach ( $arr as $el_key => $value ) {
			if ( empty( $value ) ) {
				continue;
			}

			if (
				( $starts_with && 0 === strpos( $el_key, $starts_with ) )
				|| ( $ends_with && substr( $el_key, -strlen( $ends_with ) ) === $ends_with )
				|| $key === $el_key
			) {
				return true;
			}
		}

		return false;
	} // array_has

	/**
	 * Recursive version of array_diff_assoc().
	 *
	 * @since 2.13.4
	 *
	 * @return mixed[] The difference between the arrays.
	 */
	public function array_diff_assoc_recursive() {
		$args = func_get_args();
		$diff = array();

		foreach ( array_shift( $args ) as $key => $val ) {
			for ( $i = 0, $j = 0, $tmp = array( $val ), $count = count( $args ); $i < $count; $i++ ) {
				if ( is_array( $val ) ) {
					if ( ! isset( $args[ $i ][ $key ] ) || ! is_array( $args[ $i ][ $key ] ) || empty( $args[ $i ][ $key ] ) ) {
						++$j;
					} else {
						$tmp[] = $args[ $i ][ $key ];
					}
				} elseif ( ! array_key_exists( $key, $args[ $i ] ) || $args[ $i ][ $key ] !== $val ) {
					++$j;
				}
			}
			if ( is_array( $val ) ) {
				$tmp = call_user_func_array( __METHOD__, $tmp );

				if ( ! empty( $tmp ) ) {
					$diff[ $key ] = $tmp;
				} elseif ( $j === $count ) {
					$diff[ $key ] = $val;
				}
			} elseif ( $j === $count && $count ) {
				$diff[ $key ] = $val;
			}
		}

		return $diff;
	} // array_diff_assoc_recursive

} // class Array_Utils
