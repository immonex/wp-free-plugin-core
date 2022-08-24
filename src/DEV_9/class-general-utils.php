<?php
/**
 * Class General_Utils
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\DEV_9;

/**
 * General (mostly WordPress related) utility methods.
 *
 * @todo Split into separate classes with clear purpose (e.g. Remote_FS).
 */
class General_Utils {

	/**
	 * Reset post data (incl. deletion of all relations, metadata and images).
	 *
	 * @since 0.3.2
	 *
	 * @param string $post_id Post ID.
	 * @param array  $taxonomies Taxonomies for deletion of post term relations (optional).
	 * @param array  $overwrite_defaults Data for overwriting/extending the post defaults (optional).
	 * @param array  $exclude_meta Array or meta keys that shall be excluded from resetting (optional).
	 * @param array  $special_args Array of special processing arguments etc. (optional).
	 *
	 * @return int|bool|WP_Error Post-ID on success, false if given ID does not exist,
	 *     otherwise WP_Error object.
	 */
	public static function reset_post( $post_id, $taxonomies = false, $overwrite_defaults = array(), $exclude_meta = array(), $special_args = array() ) {
		global $user_ID;

		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}

		$args = array(
			'taxonomies'         => $taxonomies,
			'overwrite_defaults' => $overwrite_defaults,
			'exclude_meta'       => $exclude_meta,
			'special_args'       => $special_args,
		);

		do_action( 'inveris_base_before_post_reset', $post_id, $args );
		do_action( 'immonex_base_before_post_reset', $post_id, $args );

		$default_language = substr( get_user_locale(), 0, 2 );

		if ( isset( $special_args['keep_attachments'] ) && $special_args['keep_attachments'] ) {
			$keep_attachments = true;
		} else {
			$keep_attachments = false;
		}

		if ( isset( $special_args['keep_featured_image'] ) && $special_args['keep_featured_image'] ) {
			$exclude_meta[]      = '_thumbnail_id';
			$keep_featured_image = true;
		} else {
			$keep_featured_image = false;
		}

		if (
			is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) &&
			function_exists( 'wpml_delete_translatable_content' )
		) {
			// Delete the translation data if WPML is in use.
			$post_type = 'post_' . $post->post_type;
			wpml_delete_translatable_content( $post_type, $post_id );
		}

		if (
			is_plugin_active( 'polylang/polylang.php' ) &&
			function_exists( 'pll_set_post_language' ) &&
			function_exists( 'pll_save_post_translations' )
		) {
			// Set post language to default and reset translation associations
			// if Polylang is in use.
			pll_set_post_language( $post_id, $default_language );
			pll_save_post_translations( array( $default_language => $post_id ) );
		}

		/**
		 * Delete all taxonomy term relations.
		 */
		if ( false === $taxonomies ) {
			$taxonomies = get_taxonomies();
		}
		if ( count( $taxonomies ) > 0 ) {
			wp_delete_object_term_relationships( $post_id, $taxonomies );
		}

		/**
		 * Delete post metadata.
		 */
		$meta = get_post_meta( $post_id );
		if ( count( $meta ) > 0 ) {
			foreach ( $meta as $meta_key => $meta_value ) {
				if ( 0 === count( $exclude_meta ) || ! in_array( $meta_key, $exclude_meta, true ) ) {
					delete_post_meta( $post_id, $meta_key );
				}
			}
		}

		/**
		 * Delete post attachments.
		 */
		if ( ! $keep_attachments ) {
			$args = array(
				'post_type'   => 'attachment',
				'numberposts' => -1,
				'post_status' => 'any',
				'post_parent' => $post_id,
				'lang'        => '',
			);

			if ( $keep_featured_image ) {
				$args['exclude'] = get_post_thumbnail_id( $post_id );
			}

			$attachments = get_posts( $args );
			if ( count( $attachments ) > 0 ) {
				foreach ( $attachments as $att ) {
					wp_delete_attachment( $att->ID, true );
				}
			}
		}

		/**
		 * Reset post record.
		 */
		$post_defaults = array(
			'post_status'           => 'draft',
			'post_type'             => 'post',
			'post_author'           => isset( $user_ID ) ? $user_ID : 0,
			'post_date'             => gmdate( 'Y-m-d' ),
			'post_date_gmt'         => gmdate( 'Y-m-d' ),
			'post_modified'         => gmdate( 'Y-m-d' ),
			'post_modified_gmt'     => gmdate( 'Y-m-d' ),
			'ping_status'           => get_option( 'default_ping_status' ),
			'post_parent'           => 0,
			'post_title'            => 'Title',
			'post_name'             => null,
			'post_content'          => 'Content',
			'menu_order'            => 0,
			'to_ping'               => '',
			'pinged'                => '',
			'post_password'         => '',
			'guid'                  => '',
			'post_content_filtered' => '',
			'post_excerpt'          => '',
			'import_id'             => 0,
		);

		if ( $overwrite_defaults && is_array( $overwrite_defaults ) ) {
			$post_defaults = array_merge( $post_defaults, $overwrite_defaults );
		}

		return wp_update_post( array_merge( array( 'ID' => $post_id ), $post_defaults ) );
	} // reset_post

	/**
	 * Get contents via an URL per cURL or alternatively per file_get_contents
	 * (probably avoid problems if allow_url_fopen is disabled).
	 *
	 * @since 0.4.5
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
	 * @since 0.6.4
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
	 * @since 0.9
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

	/**
	 * Send a POST request with given parameters.
	 *
	 * @since 0.7
	 *
	 * @param string $url URL.
	 * @param array  $data Data to send.
	 *
	 * @return string|bool Output part of response or false on error.
	 */
	public static function post( $url, $data ) {
		if ( function_exists( 'curl_init' ) ) {
			// Use cURL if available...
			$post_data = '';

			foreach ( $data as $key => $value ) {
				$post_data .= $key . '=' . $value . '&';
			}
			$post_data = rtrim( $post_data, '&' );

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
					CURLOPT_HEADER         => false,
					CURLOPT_POST           => true,
					CURLOPT_POSTFIELDS     => $post_data,
					CURLOPT_URL            => $url,
				)
			);

			$result = curl_exec( $ch );
			curl_close( $ch );
			// @codingStandardsIgnoreEnd

			return $result;
		} else {
			// Fallback method via file_get_contents.
			$options = array(
				'http' => array(
					'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
					'method'  => 'POST',
					'content' => http_build_query( $data ),
				),
			);
			$context = stream_context_create( $options );

			// @codingStandardsIgnoreLine
			return file_get_contents( $url, false, $context );
		}
	} // post

	/**
	 * Recursively search for files in given folder and its subfolders.
	 *
	 * @since 0.7.3
	 *
	 * @param string $pattern Search pattern.
	 * @param int    $flags Search flags (glob function).
	 *
	 * @return string[]|bool Array of found files or false on error.
	 */
	public static function glob_recursive( $pattern, $flags = 0 ) {
		$files = glob( $pattern, $flags );

		foreach ( glob( dirname( $pattern ) . '/*', GLOB_ONLYDIR | GLOB_NOSORT ) as $dir ) {
			$files = array_merge( $files, self::glob_recursive( $dir . '/' . basename( $pattern ), $flags ) );
		}

		return $files;
	} // glob_recursive

	/**
	 * Transform WP error message and related data into a single string.
	 *
	 * @since 0.7.9
	 *
	 * @param \WP_Error $error WP_Error object.
	 *
	 * @return string Error message and related data (nicely serialized).
	 */
	public static function get_error_description( $error ) {
		if ( ! is_wp_error( $error ) ) {
			return '';
		}

		$error_descr = $error->get_error_message();

		if ( count( $error->error_data ) > 0 ) {
			$error_descr .= ' (';

			foreach ( $error->error_data as $key => $value ) {
				$error_descr .= "$key: $value";
			}

			$error_descr  = trim( $error_descr );
			$error_descr .= ')';
		}

		return $error_descr;
	} // get_error_description

	/**
	 * Return current post/page ID depending on the loop status.
	 *
	 * @since 1.0.1
	 *
	 * @return int Current post/page ID.
	 */
	// @codingStandardsIgnoreLine
	public static function get_the_ID() {
		return is_single() ? $GLOBALS['wp_the_query']->get_queried_object_id() : get_the_ID();
	} // get_the_ID

} // General_Utils
