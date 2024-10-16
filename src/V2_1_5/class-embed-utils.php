<?php
/**
 * Class Embed_Utils
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\V2_1_5;

/**
 * Embedding related utilities.
 */
class Embed_Utils {

	/**
	 * Retrieve and return oEmbed data for the given URL.
	 *
	 * @param string $url    URL to retrieve oEmbed data for.
	 * @param int    $att_id Attachment ID (optional).
	 *
	 * @return mixed[]|bool Array with oEmbed data or false if unavailable.
	 */
	public static function get_oembed_data( $url, $att_id = 0 ) {
		$data = _wp_oembed_get_object()->get_data( $url );

		return $data;
	} // get_oembed_data

} // class Embed_Utils
