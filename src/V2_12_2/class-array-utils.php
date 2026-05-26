<?php
/**
 * Class Array_Utils
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\V2_12_2;

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

} // class Array_Utils
