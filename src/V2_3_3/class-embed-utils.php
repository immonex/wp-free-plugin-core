<?php
/**
 * Class Embed_Utils
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\V2_3_3;

/**
 * Embedding related utilities.
 */
class Embed_Utils {

	/**
	 * Retrieve and return (possibly cached) oEmbed data for the given URL.
	 *
	 * @param string $url     URL to retrieve oEmbed data for.
	 * @param int    $post_id ID of the post the URL belongs to (optional).
	 *
	 * @return mixed[]|bool Array with oEmbed data or false if unavailable.
	 */
	public static function get_oembed_data( $url, $post_id = 0 ) {
		if ( $post_id ) {
			$url_id = String_Utils::get_hash( $url );
			$data   = get_post_meta( $post_id, "_immonex_oembed_cache_{$url_id}", true );

			if ( ! empty( $data ) ) {
				return $data;
			}
		}

		$data = _wp_oembed_get_object()->get_data( $url );

		if ( $post_id && ! empty( $data ) ) {
			$url_id = String_Utils::get_hash( $url );
			update_post_meta( $post_id, "_immonex_oembed_cache_{$url_id}", (array) $data );
		}

		return (array) $data;
	} // get_oembed_data

} // class Embed_Utils
