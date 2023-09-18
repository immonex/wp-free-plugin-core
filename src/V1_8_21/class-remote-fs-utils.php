<?php
/**
 * Class Remote_FS_Utils
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\V1_8_21;

/**
 * Remote filesystems related utilities.
 */
class Remote_FS_Utils {

	/**
	 * Get contents via an URL per cURL or alternatively per file_get_contents
	 * (probably avoid problems if allow_url_fopen is disabled).
	 *
	 * @since 1.8.6
	 *
	 * @param string $url URL.
	 * @param string $useragent User agent signature to submit (optional).
	 *
	 * @return string|bool Output part of response.
	 */
	public static function get_url_contents( $url, $useragent = false ) {
		$output = false;

		$response = wp_remote_get( $url );
		if (
			! is_wp_error( $response )
			&& 200 === wp_remote_retrieve_response_code( $response )
		) {
			$output = wp_remote_retrieve_body( $response );
		}

		// @codingStandardsIgnoreStart
		if ( ! $output && function_exists( 'curl_init' ) ) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 0 );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 15 );
			if ( $useragent ) {
				curl_setopt( $ch, CURLOPT_USERAGENT, $useragent );
			}
			$output = curl_exec( $ch );
			curl_close( $ch );
		}
		// @codingStandardsIgnoreEnd

		if ( ! $output ) {
			// @codingStandardsIgnoreLine
			$output = file_get_contents( $url );
		}

		return $output;
	} // get_url_contents

	/**
	 * Check if a remote file exists.
	 *
	 * @since 1.8.6
	 *
	 * @param string $url URL.
	 *
	 * @return string|bool Output part of response.
	 */
	public static function remote_file_exists( $url ) {
		$file_exists = false;
		$wp_home_url = home_url();
		$is_local    = substr( $url, 0, strlen( $wp_home_url ) ) === $wp_home_url;

		if ( $is_local ) {
			// File in local WP installation: use file_exists.
			$local_file = get_home_path() . substr( $url, strlen( $wp_home_url ) + 1 );
			return file_exists( $local_file );
		}

		$headers = get_headers( $url );
		if ( is_array( $headers ) || count( $headers ) > 0 ) {
			$current_http_status_code = '';

			foreach ( $headers as $line ) {
				$found = preg_match( '|HTTP/\d\.\d\s+(\d+)\s+.*|', $line, $match );
				if ( $found && isset( $match[1] ) ) {
					$current_http_status_code = $match[1];
				};
			}

			$file_exists = '200' === $current_http_status_code;
		}

		if ( $file_exists ) {
			return true;
		}

		// Alternative solution via cURL.
		if ( function_exists( 'curl_init' ) ) {
			// @codingStandardsIgnoreStart
			$ch = curl_init();
			curl_setopt_array(
				$ch,
				array(
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_SSL_VERIFYHOST => false,
					CURLOPT_CONNECTTIMEOUT => 0,
					CURLOPT_TIMEOUT        => 15,
					CURLOPT_URL            => $url,
				)
			);
			curl_exec( $ch );

			$file_exists = 200 === curl_getinfo( $ch, CURLINFO_HTTP_CODE );
			curl_close( $ch );
			// @codingStandardsIgnoreEnd
		}

		return $file_exists;
	} // remote_file_exists

	/**
	 * Get the size of a remote file via GET or HEAD request.
	 *
	 * @since 1.8.6
	 *
	 * @param string $url The remote file/ressource to query.
	 *
	 * @return int|bool Size in bytes of false if it could not be retrieved.
	 */
	public static function get_remote_filesize( $url ) {
		$headers = get_headers( $url, 1 );
		if ( $headers && is_array( $headers ) ) {
			$head = array_change_key_case( $headers );
			$clen = isset( $head['content-length'] ) ? $head['content-length'] : 0;
		}

		if ( ! $clen ) {
			// Content length could not be retrieved, try a HEAD based request.
			stream_context_set_default( array( 'http' => array( 'method' => 'HEAD' ) ) );
			$clen = isset( $head['content-length'] ) ? $head['content-length'] : 0;
			stream_context_set_default( array( 'http' => array( 'method' => 'GET' ) ) );
		}

		if ( is_array( $clen ) ) {
			$clen = (int) array_pop( $clen );
		}

		if ( ! $clen ) {
			// Alternative solution via cURL.
			if ( function_exists( 'curl_init' ) ) {
				// @codingStandardsIgnoreStart
				$ch = curl_init();
				curl_setopt_array(
					$ch,
					array(
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_FOLLOWLOCATION => true,
						CURLOPT_SSL_VERIFYHOST => false,
						CURLOPT_CONNECTTIMEOUT => 0,
						CURLOPT_TIMEOUT        => 15,
						CURLOPT_URL            => $url,
					)
				);
				curl_exec( $ch );

				$clen = curl_getinfo( $ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD );
				curl_close( $ch );
				// @codingStandardsIgnoreEnd
			}
		}

		return $clen;
	} // get_remote_filesize

} // class Remote_FS_Utils
