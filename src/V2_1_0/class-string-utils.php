<?php
/**
 * Class String_Utils.
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\V2_1_0;

/**
 * String related utility methods.
 */
class String_Utils {

	const SPECIAL_CHAR_MAP = array(
		'['  => '-!SQBL!-',
		']'  => '-!SQBR!-',
		'<'  => '-!ANBL!-',
		'>'  => '-!ANBR!-',
		'('  => '-!ROBL!-',
		')'  => '-!ROBR!-',
		'{'  => '-!CUBL!-',
		'}'  => '-!CUBR!-',
		'/'  => '-!FSL-',
		'\\' => '-!BSL!-',
		"'"  => '-!SQT!-',
		'"'  => '-!DQT!-',
		'`'  => '-!GRAV!-',
	);

	/**
	 * Create a slug.
	 *
	 * @since 0.3
	 *
	 * @param string $text String to create a slug from.
	 *
	 * @return string Slugified string.
	 */
	public static function slugify( $text ) {
		$text = self::transliterate_non_ascii( $text );
		$text = self::lowercase_ascii( $text );
		$text = self::remove_doubles( $text );

		return sanitize_title_with_dashes( $text );
	} // slugify

	/**
	 * Transliterate non-ASCII characters.
	 *
	 * @since 0.3
	 *
	 * @param string $text Source string.
	 * @param bool   $german Use German translations for specific replacements.
	 *
	 * @return string Transliterated String.
	 */
	public static function transliterate_non_ascii( $text, $german = false ) {
		$trans = array(
			'Ä'  => 'Ae',
			'ä'  => 'ae',
			'Æ'  => 'Ae',
			'æ'  => 'ae',
			'À'  => 'A',
			'à'  => 'a',
			'Á'  => 'A',
			'á'  => 'a',
			'Â'  => 'A',
			'â'  => 'a',
			'Ã'  => 'A',
			'ã'  => 'a',
			'Å'  => 'A',
			'å'  => 'a',
			'ª'  => 'a',
			'ₐ'  => 'a',
			'ā'  => 'a',
			'Ć'  => 'C',
			'ć'  => 'c',
			'Ç'  => 'C',
			'ç'  => 'c',
			'Ð'  => 'D',
			'đ'  => 'd',
			'È'  => 'E',
			'è'  => 'e',
			'É'  => 'E',
			'é'  => 'e',
			'Ê'  => 'E',
			'ê'  => 'e',
			'Ë'  => 'E',
			'ë'  => 'e',
			'ₑ'  => 'e',
			'ƒ'  => 'f',
			'ğ'  => 'g',
			'Ğ'  => 'G',
			'Ì'  => 'I',
			'ì'  => 'i',
			'Í'  => 'I',
			'í'  => 'i',
			'Î'  => 'I',
			'î'  => 'i',
			'Ï'  => 'Ii',
			'ï'  => 'ii',
			'ī'  => 'i',
			'ı'  => 'i',
			'I'  => 'I',
			'Ñ'  => 'N',
			'ñ'  => 'n',
			'ⁿ'  => 'n',
			'Ò'  => 'O',
			'ò'  => 'o',
			'Ó'  => 'O',
			'ó'  => 'o',
			'Ô'  => 'O',
			'ô'  => 'o',
			'Õ'  => 'O',
			'õ'  => 'o',
			'Ø'  => 'O',
			'ø'  => 'o',
			'ₒ'  => 'o',
			'Ö'  => 'Oe',
			'ö'  => 'oe',
			'Œ'  => 'Oe',
			'œ'  => 'oe',
			'ß'  => 'ss',
			'Š'  => 'S',
			'š'  => 's',
			'ş'  => 's',
			'Ş'  => 'S',
			'™'  => 'TM',
			'Ù'  => 'U',
			'ù'  => 'u',
			'Ú'  => 'U',
			'ú'  => 'u',
			'Û'  => 'U',
			'û'  => 'u',
			'Ü'  => 'Ue',
			'ü'  => 'ue',
			'Ý'  => 'Y',
			'ý'  => 'y',
			'ÿ'  => 'y',
			'Ž'  => 'Z',
			'ž'  => 'z',
			'¢'  => 'Cent',
			'€'  => 'Euro',
			'‰'  => 'promille',
			'№'  => 'Nr',
			'$'  => 'Dollar',
			'℃'  => 'Grad Celsius',
			'°C' => $german ? 'Grad Celsius' : 'degrees Centigrade',
			'℉'  => $german ? 'Grad Fahrenheit' : 'degrees Fahrenheit',
			'°F' => $german ? 'Grad Fahrenheit' : 'degrees Fahrenheit',
			'⁰'  => '0',
			'¹'  => '1',
			'²'  => '2',
			'³'  => '3',
			'⁴'  => '4',
			'⁵'  => '5',
			'⁶'  => '6',
			'⁷'  => '7',
			'⁸'  => '8',
			'⁹'  => '9',
			'₀'  => '0',
			'₁'  => '1',
			'₂'  => '2',
			'₃'  => '3',
			'₄'  => '4',
			'₅'  => '5',
			'₆'  => '6',
			'₇'  => '7',
			'₈'  => '8',
			'₉'  => '9',
			'±'  => 'plusminus',
			'×'  => 'x',
			'₊'  => 'plus',
			'₌'  => '=',
			'⁼'  => '=',
			'⁻'  => '-',
			'₋'  => '-',
			'–'  => '-',
			'—'  => '-',
			'‑'  => '-',
			'․'  => '.',
			'‥'  => '..',
			'…'  => '...',
			'‧'  => '.',
			' '  => '-',
			' '  => '-',
			'А'  => 'A',
			'Б'  => 'B',
			'В'  => 'V',
			'Г'  => 'G',
			'Д'  => 'D',
			'Е'  => 'E',
			'Ё'  => 'YO',
			'Ж'  => 'ZH',
			'З'  => 'Z',
			'И'  => 'I',
			'Й'  => 'Y',
			'К'  => 'K',
			'Л'  => 'L',
			'М'  => 'M',
			'Н'  => 'N',
			'О'  => 'O',
			'П'  => 'P',
			'Р'  => 'R',
			'С'  => 'S',
			'Т'  => 'T',
			'У'  => 'U',
			'Ф'  => 'F',
			'Х'  => 'H',
			'Ц'  => 'TS',
			'Ч'  => 'CH',
			'Ш'  => 'SH',
			'Щ'  => 'SCH',
			'Ъ'  => '',
			'Ы'  => 'YI',
			'Ь'  => '',
			'Э'  => 'E',
			'Ю'  => 'YU',
			'Я'  => 'YA',
			'а'  => 'a',
			'б'  => 'b',
			'в'  => 'v',
			'г'  => 'g',
			'д'  => 'd',
			'е'  => 'e',
			'ё'  => 'yo',
			'ж'  => 'zh',
			'з'  => 'z',
			'и'  => 'i',
			'й'  => 'y',
			'к'  => 'k',
			'л'  => 'l',
			'м'  => 'm',
			'н'  => 'n',
			'о'  => 'o',
			'п'  => 'p',
			'р'  => 'r',
			'с'  => 's',
			'т'  => 't',
			'у'  => 'u',
			'ф'  => 'f',
			'х'  => 'h',
			'ц'  => 'ts',
			'ч'  => 'ch',
			'ш'  => 'sh',
			'щ'  => 'sch',
			'ъ'  => '',
			'ы'  => 'yi',
			'ь'  => '',
			'э'  => 'e',
			'ю'  => 'yu',
			'я'  => 'ya',
		);

		return trim( strtr( $text, $trans ), '-' );
	} // transliterate_non_ascii

	/**
	 * Convert a string to lowercase and remove all non-ASCII characters.
	 *
	 * @since 0.3
	 *
	 * @param string $text Source string.
	 *
	 * @return string Converted string.
	 */
	public static function lowercase_ascii( $text ) {
		return preg_replace( '~([^a-z\d_.-])~', '', strtolower( $text ) );
	} // lowercase_ascii

	/**
	 * Reduces repeated meta characters (-=+.) to one.
	 *
	 * @since 0.3
	 *
	 * @param string $text Source string.
	 *
	 * @return string Converted string.
	 */
	public static function remove_doubles( $text ) {
		return preg_replace( '~([=+.-])\\1+~', '\1', $text );
	} // remove_doubles

	/**
	 * Encode a string in accordance to RFC 3986.
	 *
	 * @since 0.3.5
	 *
	 * @param string $string Source string.
	 *
	 * @return string Encoded string.
	 */
	public static function urlencode_special( $string ) {
		$entities     = array( '%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D' );
		$replacements = array( '!', '*', "'", '(', ')', ';', ':', '@', '&', '=', '+', '$', ',', '/', '?', '%', '#', '[', ']' );

		return str_replace( $entities, $replacements, rawurlencode( $string ) );
	} // urlencode_special

	/**
	 * Generate a text excerpt based on character count.
	 *
	 * @since 0.3.5
	 *
	 * @param string $source_text Source string.
	 * @param int    $length Maximum excerpt length (optional, default: 120).
	 * @param string $suffix String to add if the source text is longer than the excerpt (optional).
	 *
	 * @return string Text excerpt.
	 */
	public static function get_excerpt( $source_text, $length = 120, $suffix = '' ) {
		$raw   = wp_strip_all_tags( $source_text );
		$words = preg_split( '/[\r\n\s]+/u', $raw, -1, PREG_SPLIT_NO_EMPTY );
		if ( empty( $words ) ) {
			return '';
		}

		$text = '';
		foreach ( $words as $word ) {
			if ( strlen( $text ) + 1 + strlen( $word ) <= $length ) {
				$text .= ' ' . $word;
			} else {
				break;
			}
		}

		if ( $suffix && strlen( $text ) < strlen( $raw ) ) {
			$text .= $suffix;
		}

		return sanitize_text_field( trim( $text ) );
	} // function get_excerpt

	/**
	 * Convert a number string into a float value.
	 *
	 * @since 0.4.1
	 *
	 * @param string $num Source number string.
	 *
	 * @return float Converted float value.
	 */
	public static function get_float( $num ) {
		global $wp_locale;

		$period_pos = strrpos( $num, '.' );
		$comma_pos  = strrpos( $num, ',' );
		$sep        = ( ( $period_pos > $comma_pos ) && $period_pos ) ?
			$period_pos :
			( ( ( $comma_pos > $period_pos ) && $comma_pos ) ? $comma_pos : false );

		if ( ! $sep ) {
			return floatval( preg_replace( '/[^0-9]/', '', $num ) );
		}

		return floatval(
			preg_replace( '/[^0-9-]/', '', substr( $num, 0, $sep ) ) . '.' .
			preg_replace( '/[^0-9]/', '', substr( $num, $sep + 1, strlen( $num ) ) )
		);
	} // get_float

	/**
	 * Format a number with variable (or no) decimals.
	 *
	 * @since 0.9
	 *
	 * @param int|float $value Source value.
	 *
	 * @return float Formatted number.
	 */
	public static function get_nice_number( $value ) {
		return str_replace( '.', ',', floatval( $value ) );
	} // get_nice_number

	/**
	 * Perform an "extended" and localized number value formatting (incl. optionally
	 * appending/prepending a unit or returning an alternative string if the value
	 * string is zero).
	 *
	 * @since 2.0.1
	 *
	 * @param int|float|string $value Source value.
	 * @param int              $decimals Number of decimal places (optional, default 2):
	 *                                     - 9: delete all trailing zeros after the decimal point,
	 *                                          examples:
	 *                                            - 12.20 -> 12,2
	 *                                            - 12.00 -> 12
	 *                                     - 99: format integer-style values without decimal places,
	 *                                           floats with two, examples:
	 *                                             - 12.0 -> 12
	 *                                             - 12.123 -> 12,12
	 * @param string           $unit Unit string to append or prepend (optional).
	 * @param mixed[]          $args Additional arguments (optional):
	 *                                 - if_zero: String to return if the value is zero.
	 *                                 - unit_pos: Unit position ("before" or "after" the value).
	 *                                 - unit_sep: Unit spacing character or string (default: no-break space).
	 *
	 * @return float Formatted number or zero string.
	 */
	public static function format_number( $value, $decimals = 2, $unit = '', $args = array() ) {
		global $wp_locale;

		if ( ! $value && ! empty( $args['if_zero'] ) ) {
			return $args['if_zero'];
		}

		if ( ! is_numeric( $value ) ) {
			$value = self::get_float( $value );
		}

		if ( ! is_numeric( $value ) ) {
			return $value;
		}

		if ( empty( $value ) ) {
			return '';
		}

		if ( 99 === $decimals ) {
			// Format integer values without decimal places, floats with two.
			$whole    = (int) $value;
			$fraction = round( $value - $whole, 2 );
			$decimals = $fraction > 0 ? 2 : 0;
		}

		$formatted = number_format_i18n( $value, 9 === $decimals ? 2 : (int) $decimals );

		if ( 9 === $decimals ) {
			// Remove all trailing zeros.
			$dec_sep = ! empty( $wp_locale->number_format['decimal_point'] ) ?
				$wp_locale->number_format['decimal_point'] : ',';
			if ( '.' === $dec_sep ) {
				$dec_sep = '\.';
			}
			$formatted = preg_replace( "/(({$dec_sep}([0]+)?[1-9]{1,})|{$dec_sep})0+$/", '$2', $formatted );
		}

		if ( $unit ) {
			$sep       = ! empty( $args['unit_sep'] ) ? $args['unit_sep'] : ' ';
			$unit_pos  = ! empty( $args['unit_pos'] ) ? $args['unit_pos'] : 'after';
			$formatted = 'after' === $unit_pos ?
				$formatted . $sep . $unit :
				$unit . $sep . $formatted;
		}

		return $formatted;
	} // format_number

	/**
	 * "Smooth" a value by rounding depending on its lenght and the number of
	 * zeros to the left of the decimal point.
	 *
	 * @since 1.5.2
	 *
	 * @param int|float $value Source value.
	 * @param bool      $round_down True if the value should be rounded down
	 *                              (optional, false by default).
	 * @param int[]     $smooth_zero_map Map of zero counts in relation to the
	 *                                   (integer) value length (optional).
	 *
	 * @return float Formatted number.
	 */
	public static function smooth_round( $value, $round_down = false, $smooth_zero_map = array() ) {
		$value = intval( $value );
		if ( empty( $smooth_zero_map ) || ! is_array( $smooth_zero_map ) ) {
			$smooth_zero_map = array( 0, 0, 1, 1, 2, 3, 3, 4 );
		}

		$smooth_zeros_count = strlen( (string) $value ) < count( $smooth_zero_map ) ?
			$smooth_zero_map[ strlen( (string) $value ) ] :
			$smooth_zero_map[ count( $smooth_zero_map ) - 1 ];
		$base               = (int) 1 . str_repeat( '0', $smooth_zeros_count );

		return $round_down ?
			(int) floor( $value / $base ) * $base :
			(int) ceil( $value / $base ) * $base;
	} // smooth_round

	/**
	 * Split a MIME type string and return an array of its parts.
	 *
	 * @since 0.6.9
	 *
	 * @param string $mime_type MIME type.
	 *
	 * @return array|bool Associative array of MIME type parts or false on invalid type string.
	 */
	public static function get_mime_type_parts( $mime_type ) {
		if ( 1 === substr_count( $mime_type, '/' ) ) {
			// Split MIME type.
			$type = explode( '/', trim( $mime_type ) );

			return array(
				'type'    => $type[0],
				'subtype' => $type[1],
			);
		} else {
			return false;
		}
	} // get_mime_type_parts

	/**
	 * Check if an URL belongs to a video (YouTube/Vimeo) and extract the video ID.
	 *
	 * @since 0.4.6
	 *
	 * @param string $url URL to check.
	 *
	 * @return array|bool Array with video type and ID or false if it's not a video URL.
	 */
	public static function is_video_url( $url ) {
		$video_type = false;

		if ( false !== strpos( strtolower( $url ), 'youtu' ) ) {
			// Seems to be a YouTube URL: extract the video ID.
			$search = '/https?:\/\/(?:[0-9A-Z-]+\.)?(?:youtu\.be\/|youtube(?:-nocookie)?\.com\S*[^\w\s-])([\w-]{11})(?=[^\w-]|$)(?![?=&+%\w.-]*(?:[\'"][^<>]*>|<\/a>))[?=&+%\w.-]*/i';
			$count  = preg_match( $search, $url, $matches );
			if ( $count > 0 && isset( $matches[1] ) ) {
				$video_type = 'youtube';
				$video_id   = $matches[1];
			} else {
				$video_type = false;
			}
		} elseif ( false !== strpos( strtolower( $url ), 'vimeo' ) ) {
			// Seems to be a Vimeo URL: extract the video ID.
			$search = '/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/';
			$count  = preg_match( $search, $url, $matches );
			if ( $count > 0 && isset( $matches[5] ) ) {
				$video_type = 'vimeo';
				$video_id   = $matches[5];
			} else {
				$video_type = false;
			}
		}

		if ( $video_type ) {
			return array(
				'type' => $video_type,
				'id'   => $video_id,
			);
		} else {
			return false;
		}
	} // is_video_url

	/**
	 * Check if an URL belongs to a virtual tour.
	 *
	 * @since 0.8
	 *
	 * @param string   $url URL to check.
	 * @param string[] $additional_search_terms Additional URL parts for identifying virtual tour URLs.
	 *
	 * @return bool True if the given URL is a virtual tour address.
	 */
	public static function is_virtual_tour_url( $url, $additional_search_terms = array() ) {
		if ( ! is_array( $additional_search_terms ) ) {
			$additional_search_terms = array( $additional_search_terms );
		}
		if ( count( $additional_search_terms ) > 0 ) {
			$additional_search_terms = implode( '|', $additional_search_terms );
		}

		return 'http' === strtolower( substr( $url, 0, 4 ) ) && preg_match( '/(ogulo|immoviewer|matterport|mpskin|archilogic|feelestate|virtualtours\.immobilienscout24|immo\.tours|ricohtours|ricoh360' . ( $additional_search_terms ? '|' . $additional_search_terms : '' ) . ')/', strtolower( $url ) );
	} // is_virtual_tour_url

	/**
	 * Convert URLs and mail addresses to Links.
	 *
	 * @since 0.9
	 *
	 * @param string $text Plain text source string.
	 *
	 * @return string String with (possibly) included link tags.
	 */
	public static function convert_urls( $text ) {
		return preg_replace_callback( '#(?<=^|\s)(?i)(http|https)?(://)?(([-\w^@]{2,}\.)+([a-zA-Z]{2,16})(?:/[^,.\s\<\>\"\']*|))(?=\s|$)#', self::class . '::convert_urls_cb', $text );
	} // convert_urls

	/**
	 * Callback method for converting URLs and mail addresses to links.
	 *
	 * @since 0.9
	 *
	 * @param string[] $m Found text fragments.
	 *
	 * @return string HTML link.
	 */
	public static function convert_urls_cb( $m ) {
		$tld          = array_slice( $m, -1 )[0];
		$domain_utils = new Domain_Utils();

		if ( ! $domain_utils->is_valid_tld( $tld ) ) {
			return $m[0];
		}

		$m_str = $m[1] . $m[2] . $m[3];

		if ( preg_match( '#([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#', $m_str ) ) {
			// "URL" is an email address.
			return '<a href="mailto:' . $m[2] . $m[3] . '">' . $m[1] . $m[2] . $m[3] . '</a>';
		} else {
			$http = ( ! preg_match( '#(https://)#', $m_str ) ) ? 'http://' : 'https://';
			return '<a href="' . $http . $m[3] . '" target="_blank">' . $m[1] . $m[2] . $m[3] . '</a>';
		}
	} // convert_urls_cb

	/**
	 * Convert A tags to plain text (link text + URL).
	 *
	 * @since 1.5.0
	 *
	 * @param string $html HTML source string.
	 *
	 * @return string String with (possibly) converted links.
	 */
	public static function convert_link_tags_to_plain_text( $html ) {
		$plain = preg_replace( '/<a\s(?:.(?!=href))*?href="([^"]*)"[^>]*?>(.*?)<\/a>/i', '$2 ($1)', $html );
		$plain = preg_replace( '/\(mailto:(.*?)\)/', '($1)', $plain );

		return $plain;
	} // convert_link_tags_to_plain_text

	/**
	 * Extract alt and title attributes of IMG tags.
	 *
	 * @since 1.5.0
	 *
	 * @param string $html    HTML source string.
	 * @param string $replace Replace parameter for preg_replace (optional, defaults to $1).
	 *
	 * @return string String with (possibly) converted links.
	 */
	public static function convert_img_tag_alt_to_plain_text( $html, $replace = '$1' ) {
		$plain = preg_replace( '/<img\s(?:.(?!=alt))*?alt="([^"]*)"[^>]*?>/i', $replace, $html );
		$plain = preg_replace( '/<img\s(?:.(?!=title))*?title="([^"]*)"[^>]*?>/i', $replace, $plain );

		return $plain;
	} // convert_img_tag_alt_to_plain_text

	/**
	 * Return the current page URL without /page/X or ?paged=X.
	 *
	 * @since 0.9
	 *
	 * @param bool $separate_query_vars True if an array with URL and separate
	 *                                  query vars shall be returned.
	 *
	 * @return string|mixed[] Current URL without page number or
	 *                        array (URL + vars).
	 */
	public static function get_nopaging_url( $separate_query_vars = false ) {
		global $wp;

		$current_url = home_url( add_query_arg( array(), $wp->request ) );
		$query_vars  = array();

		if ( ! empty( $wp->query_vars ) ) {
			foreach ( $wp->query_vars as $var_name => $value ) {
				if ( $value && ! in_array( $var_name, array( 'page', 'paged' ), true ) ) {
					$query_vars[ $var_name ] = $value;
				}
			}
		}

		if ( ! $wp->request && ! empty( $query_vars ) ) {
			// Add GET vars if pretty permalink URLs are NOT activated.
			$current_url = add_query_arg( $query_vars, trailingslashit( $current_url ) );
		}

		$nopaging_url = preg_replace( '/page\\/[0-9]+(\\/)?/i', '', $current_url );

		if ( $separate_query_vars ) {
			return array(
				'url'        => $nopaging_url,
				'query_vars' => $query_vars,
			);
		} else {
			return $nopaging_url;
		}
	} // get_nopaging_url

	/**
	 * Split strings containing one or multiple mail addresses and return
	 * extracted addresses.
	 *
	 * @since 1.1.0
	 *
	 * @param string $string String containing the mail addresses.
	 * @param string $divider String/Character used to separate addresses.
	 *
	 * @return string[] Extracted mail addresses.
	 */
	public static function split_mail_address_string( $string, $divider = ',' ) {
		$mail_addresses = array();
		$string_parts   = explode( $divider, $string );

		if ( count( $string_parts ) > 0 ) {
			foreach ( $string_parts as $part ) {
				$address = sanitize_email( $part );

				if ( $address ) {
					$mail_addresses[] = $address;
				}
			}
		}

		return $mail_addresses;
	} // split_mail_address_string

	/**
	 * Very simple two-way string encryption/obfuscation for use cases where
	 * security is NOT a really important issue.
	 *
	 * @since 1.1.0
	 *
	 * @param string $string String to encrypt/decrypt.
	 * @param string $key    Encryption/Decryption key.
	 *
	 * @return string Encrypted string.
	 */
	public static function xor_string( $string, $key ) {
		$len = strlen( $string );

		for ( $i = 0; $i < $len; $i++ ) {
			$string[ $i ] = ( $string[ $i ] ^ $key[ $i % strlen( $key ) ] );
		}

		return $string;
	} // xor_string

	/**
	 * Multibyte version of str_pad.
	 *
	 * @since 1.3.4
	 *
	 * @param string $str        The string to be padded.
	 * @param int    $length     The length of the resultant padded string.
	 * @param string $pad_string The string to use as padding. Defaults to space.
	 * @param int    $pad_type   The type of padding. Defaults to STR_PAD_RIGHT.
	 * @param string $encoding   The encoding to use, defaults to UTF-8 (empty = auto detect).
	 *
	 * @return string Padded string.
	 */
	public static function mb_str_pad( $str, $length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT, $encoding = 'UTF-8' ) {
		if ( ! extension_loaded( 'mbstring' ) ) {
			return str_pad( $str, $length, $pad_string, $pad_type );
		}

		if ( ! $encoding ) {
			$encoding = mb_detect_encoding( $str );
		}
		if ( ! $encoding ) {
			$encoding = 'UTF-8';
		}

		$pad_required = $length - mb_strlen( $str, $encoding );
		if ( $pad_required <= 0 ) {
			return $str;
		}

		switch ( $pad_type ) {
			case STR_PAD_LEFT:
				return mb_substr( str_repeat( $pad_string, $pad_required ), 0, $pad_required, $encoding ) . $str;
			case STR_PAD_BOTH:
				$left_pad_len  = floor( $pad_required / 2 );
				$right_pad_len = $pad_required - $left_pad_len;
				return mb_substr( str_repeat( $pad_string, $left_pad_len ), 0, $left_pad_len, $encoding ) . $str .
					mb_substr( str_repeat( $pad_string, $right_pad_len ), 0, $right_pad_len, $encoding );
			default:
				return $str . mb_substr( str_repeat( $pad_string, $pad_required ), 0, $pad_required, $encoding );
		}
	} // mb_str_pad

	/**
	 * Multibyte version of (mb_)strlen with additional module check.
	 *
	 * @since 1.8.0
	 *
	 * @param string $str      The string being measured for length.
	 * @param string $encoding The encoding to use, defaults to UTF-8 (empty = auto detect).
	 *
	 * @return string String length.
	 */
	public static function mb_str_len( $str, $encoding = 'UTF-8' ) {
		if ( ! extension_loaded( 'mbstring' ) ) {
			return strlen( $str );
		}

		if ( ! $encoding ) {
			$encoding = mb_detect_encoding( $str );
		}
		if ( ! $encoding ) {
			$encoding = 'UTF-8';
		}

		return mb_strlen( $str, $encoding );
	} // mb_str_len

	/**
	 * Multibyte version of (mb_)substr with additional module check.
	 *
	 * @since 1.8.0
	 *
	 * @param string $str      The original string.
	 * @param int    $start    Start position.
	 * @param int    $length   Maximum number of characters.
	 * @param string $encoding The encoding to use, defaults to UTF-8 (empty = auto detect).
	 *
	 * @return string Specified string portion.
	 */
	public static function mb_sub_str( $str, $start, $length, $encoding = 'UTF-8' ) {
		if ( ! extension_loaded( 'mbstring' ) ) {
			return substr( $str, $start, $length );
		}

		if ( ! $encoding ) {
			$encoding = mb_detect_encoding( $str );
		}
		if ( ! $encoding ) {
			$encoding = 'UTF-8';
		}

		return mb_substr( $str, $start, $length, $encoding );
	} // mb_sub_str

	/**
	 * Simple HTML to plain text conversion using the Html2Text library.
	 *
	 * @see https://github.com/mtibben/html2text
	 *
	 * @since 1.3.5
	 *
	 * @param string      $html        HTML string.
	 * @param bool|string $list_bullet List item bullet character (optional; defaults
	 *                                 to an empty string for no bullet points; true
	 *                                 for "-" or an arbitrary other character as
	 *                                 alternative; false for the Html2Text default
	 *                                 character (*) and indentation.
	 * @param mixed[]     $options     Html2Text options (optional) + add_colon for adding
	 *                                 colons to DT titles if missing (default: true).
	 *
	 * @return string Plain text version.
	 */
	public static function html_to_plain_text( $html, $list_bullet = '', $options = array() ) {
		$options = array_merge(
			array( 'add_colon' => true ),
			$options
		);

		$plain = trim( stripslashes( $html ) );
		if ( false === strpos( $plain, '<' ) ) {
			// Return stripslashed/decoded original content if it doesn't contain any HTML tags.
			return html_entity_decode( $plain, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
		}

		/**
		 * Remove STRONG/EM/I tags, wrap single-line A and IMG tags in P tags and
		 * convert TH to TD tags before regular conversion (special cases).
		 */

		$pre_convert_adjustments = array(
			'/<(strong|em|i)>(.*)<\/(strong|em|i)>/i' => '$2',
			'/(\r\n\r\n|\n\n)?(?<!<p>)(<a[^>]*>(.*)<\/a>)(\r\n\r\n|\n\n)?/im' => '$1<p>$2</p>$4',
			'/(\r\n\r\n|\n\n)?(?<!<p>)(<img[^>]*>)(\r\n\r\n|\n\n)?/im' => '$1<p>$2</p>$3',
			'/<th[^>]*>(.*)<\/th>/im'                 => '<td>$1</td>',
		);

		foreach ( $pre_convert_adjustments as $regex => $replace ) {
			$plain = preg_replace( $regex, $replace, $plain );
		}

		$plain = preg_replace_callback(
			'/(<dt[^>]*>)(.*)(<\/dt>)/',
			function ( $matches ) {
				$title = trim( str_replace( '&nbsp;', '', $matches[2] ) );
				if (
					! empty( $options['add_colon'] )
					&& ':' !== substr( $title, -1 )
				) {
					$title .= ':';
				}
				return "{$matches[1]}{$title}{$matches[3]}";
			},
			$plain
		);

		try {
			$h2t       = new \Html2Text\Html2Text( $plain, $options );
			$plain_h2t = $h2t->getText();
			if ( empty( $plain_h2t ) ) {
				return $plain;
			}

			$plain = $plain_h2t;
		} catch ( Exception $e ) {
			return $plain;
		}

		$list_bullet_repl = '';
		if ( false !== $list_bullet ) {
			// Replace list bullets and remove indentation.
			if ( $list_bullet ) {
				$list_bullet_repl = true === $list_bullet ? '- ' : $list_bullet[0] . ' ';
			}

			$plain = preg_replace( '/^[ \t]+\*[ ]?(.*)[\r\n]{0,2}/m', "{$list_bullet_repl}$1", $plain );
		}

		// Remove space + double tab indents on line beginnings.
		$plain = preg_replace( '/^ \t\t/im', '', $plain );

		$list_bullet = false === $list_bullet ? '*' : '';
		if ( $list_bullet_repl ) {
			$list_bullet = $list_bullet_repl[0];
		}

		if ( ! $list_bullet ) {
			return $plain;
		}

		// Remove unintended line breaks directly after list bullets.
		$plain = preg_replace( "/^([ \t]{0,}[{$list_bullet}])[\r\n]{1,2}/m", '$1 ', $plain );

		/**
		 * Add indentation to following list item lines.
		 */
		$plain_lines = explode( PHP_EOL, $plain );
		if ( count( $plain_lines ) > 0 ) {
			$in_li_indent = false;
			foreach ( $plain_lines as $i => $line ) {
				if ( preg_match( "/^([ \t]{0,})[{$list_bullet}]/", $line, $matches ) ) {
					$in_li_indent = $matches[1];
					continue;
				} elseif ( '' === trim( $line ) ) {
					$in_li_indent = false;
					continue;
				}

				if ( false !== $in_li_indent ) {
					$plain_lines[ $i ] = $in_li_indent . '  ' . $plain_lines[ $i ];
				}
			}

			$plain = implode( PHP_EOL, $plain_lines );
		}

		return $plain;
	} // html_to_plain_text

	/**
	 * Check if the given string/filename begins with a date/time statement and
	 * return the resulting timestamp, if so. (Paths will be reduced to filenames
	 * in advance.)
	 *
	 * @since 1.7.17
	 *
	 * @param string $str Filename, full path or arbitrary string.
	 *
	 * @return int|bool Timestamp or false if not determinable.
	 */
	public static function get_leading_timestamp( $str ) {
		if ( false !== strpos( $str, DIRECTORY_SEPARATOR ) ) {
			$str = basename( $str );
		}

		if ( preg_match( '/^([0-9]{4})[-_ ]?([0-9]{2})[-_ ]?([0-9]{2})(([-_ ]?([0-9]{2})[-__: ]?([0-9]{2})[-_: ]?([0-9]{2})?))?([-_ \.]|$)/', $str, $matches ) ) {
			$y = $matches[1];
			$m = $matches[2];
			$d = $matches[3];
			$h = $matches[6];
			$i = $matches[7];
			$s = $matches[8];

			if ( 1699 >= (int) $y || 2101 <= (int) $y ) {
				return false;
			}

			if ( 0 === (int) $m || 13 <= (int) $m ) {
				return false;
			}

			if ( 0 === (int) $d || 32 <= (int) $d ) {
				return false;
			}

			if ( ! $h || 24 <= (int) $h ) {
				$h = '00';
			}

			if ( ! $i || 60 <= (int) $i ) {
				$i = '00';
			}

			if ( ! $s || 60 <= (int) $s ) {
				$s = '00';
			}

			return strtotime( "$y-$m-$d $h:$i:$s" );
		}

		return false;
	} // get_leading_timestamp

	/**
	 * Convert a UTC/GMT timestamp or date/time string to a local timestamp or date/time string.
	 *
	 * @since 1.8.23
	 *
	 * @param int|string $utc_time UTC timestamp or date/time string.
	 * @param string     $format   Optional return date/time format string
	 *                             (default: empty = return timestamp).
	 *
	 * @return int|string Local timestamp or date/time string.
	 */
	public static function utc_to_local_time( $utc_time, $format = '' ) {
		$utc_datetime = date_create(
			is_numeric( $utc_time ) ? "@{$utc_time}" : $utc_time,
			new \DateTimeZone( 'UTC' )
		);
		if ( ! $utc_datetime ) {
			return $utc_time;
		}

		if ( ! $format ) {
			return strtotime( wp_date( 'Y-m-d H:i:s', $utc_datetime->getTimestamp() ) );
		}

		return $utc_datetime->setTimezone( wp_timezone() )->format( $format );
	} // utc_to_local_time

	/**
	 * Replace certain characters by special codes (e.g. for use in shortcode attributes).
	 *
	 * @since 1.7.18
	 *
	 * @param string|string[] $source_text Source string(s).
	 *
	 * @return string|string[] Text(s) with (possibly) replaced special characters.
	 */
	public static function encode_special_chars( $source_text ) {
		return str_replace( array_keys( self::SPECIAL_CHAR_MAP ), array_values( self::SPECIAL_CHAR_MAP ), $source_text );
	} // encode_special_chars

	/**
	 * Decode special codes (e.g. in shortcode attributes).
	 *
	 * @since 1.7.18
	 *
	 * @param string|string[] $source_text Source string(s).
	 *
	 * @return string|string[] Text(s) with (possibly) replaced special characters.
	 */
	public static function decode_special_chars( $source_text ) {
		return str_replace( array_values( self::SPECIAL_CHAR_MAP ), array_keys( self::SPECIAL_CHAR_MAP ), $source_text );
	} // encode_special_chars

	/**
	 * Shorten one or multiple filesystem paths for output purposes.
	 *
	 * @since 1.8.0
	 *
	 * @param string|string[] $source_paths Source path(s).
	 * @param string          $start_pattern Pattern to start shortening from (optional, defaults to "wp-content/").
	 * @param string          $prefix        Prefix to prepend to shortened paths (optional, default "…").
	 *
	 * @return string|string[] Shortened paths.
	 */
	public static function shorten_paths( $source_paths, $start_pattern = 'wp-content/', $prefix = '…' ) {
		$return_string = is_string( $source_paths );
		$paths         = array();

		if ( $return_string ) {
			$source_paths = array( $source_paths );
		}

		if ( ! empty( $source_paths ) ) {
			if ( '/' !== DIRECTORY_SEPARATOR ) {
				$start_pattern = str_replace( '/', DIRECTORY_SEPARATOR, $start_pattern );
			}
			$start_pattern = preg_quote( $start_pattern, '/' );

			foreach ( $source_paths as $i => $path ) {
				$paths[ $i ] = preg_replace( "/^.*({$start_pattern}.*)/", "{$prefix}$1", $path );
			}
		}

		return $return_string ? $paths[0] : $paths;
	} // shorten_paths

	/**
	 * Convert php.ini style memory statements (10k, 20m, 2g) into bytes.
	 *
	 * @since 1.8.0
	 *
	 * @param string $value  Value to convert.
	 * @param bool   $format True if the result value shall be formatted
	 *                       (optional, false by default).
	 *
	 * @return int|string Value in bytes.
	 */
	public static function get_bytes( $value, $format = false ) {
		$value = trim( $value );
		$last  = strtolower( $value[ strlen( $value ) - 1 ] );

		$bytes = (int) $value;

		switch ( $last ) {
			case 'g':
				$bytes *= 1024;
				// Fall-through is intentional here.
			case 'm':
				$bytes *= 1024;
				// Fall-through is intentional here.
			case 'k':
				$bytes *= 1024;
		}

		return $format ? number_format( $bytes, 0, '', '.' ) : $bytes;
	} // get_bytes

	/**
	 * Generate a documentation link tag (dashicon only).
	 *
	 * @since 1.8.0
	 *
	 * @param string $url       Link destination URL.
	 * @param string $link_text Link text.
	 * @param string $dashicon  Optional WP Dashicon name (info-outline (default), info,
	 *                          help, book, sos).
	 *
	 * @return string Link tag.
	 */
	public static function doc_link( $url, $link_text = '', $dashicon = 'info-outline' ) {
		if ( ! in_array( $dashicon, array( 'info-outline', 'info', 'help', 'book', 'sos' ), true ) ) {
			$dashicon = 'info-outline';
		}

		return wp_sprintf(
			'<a href="%1$s" class="immonex-doc-link immonex-doc-link--dashicon--%2$s" target="_blank"%3$s>%4$s</a>',
			esc_url( $url ),
			$dashicon,
			$link_text ? '' : ' aria-label="' . __( 'Documentation', 'immonex-wp-free-plugin-core' ) . '"',
			$link_text
		);
	} // doc_link

	/**
	 * Remove counters, sizes and other extensions from filenames (e.g. for
	 * comparison purposes etc.).
	 *
	 * @since 1.8.4
	 *
	 * @param string $filename Source filename.
	 * @param string $type     Removal type: counter (default) or counter+size.
	 *
	 * @return string Plain filename.
	 */
	public static function get_plain_filename( $filename, $type = 'counter' ) {
		switch ( $type ) {
			case 'counter+size':
				return preg_replace( '/((-[0-9]{1,2})?(-[0-9]{1,4}x[0-9]{1,4})(-scaled)?)(\.[a-zA-Z]{3})/', '$5', $filename );
			default:
				return preg_replace( '/(-[0-9]{1,2})?(-scaled)?(\.[a-zA-Z]{3,4})?$/', '$3', $filename );
		}
	} // get_plain_filename

	/**
	 * Get a sanitized unzip folder name based on ZIP filename.
	 *
	 * @since 1.8.4
	 *
	 * @param string $filename Source ZIP filename.
	 *
	 * @return string Plain folder name.
	 */
	public static function get_plain_unzip_folder_name( $filename ) {
		$path_info = pathinfo( $filename );
		$filename  = ! empty( $path_info['filename'] ) ? $path_info['filename'] : basename( $filename );

		return self::slugify( strtolower( $filename ) );
	} // get_plain_unzip_folder_name

	/**
	 * Unify the directory separators in a path and optionally add or remove
	 * a trailing (back)slash.
	 *
	 * @since 1.8.20
	 *
	 * @param string $path            Source path.
	 * @param int    $trailingslashit Add (1) or remove (-1) a trailing (back)slash (optional,
	 *                                defaults to 0 = no change).
	 * @param string $dirsep          Directory separator (optional, defaults to the system one).
	 *
	 * @return string Possibly modified path.
	 */
	public static function unify_dirsep( $path, $trailingslashit = 0, $dirsep = DIRECTORY_SEPARATOR ) {
		if ( 1 === $trailingslashit ) {
			$path = trailingslashit( $path );
		} elseif ( -1 === $trailingslashit ) {
			$path = untrailingslashit( $path );
		}

		return str_replace( array( '/', '\\' ), $dirsep, $path );
	} // unify_dirsep

	/**
	 * Basic HTML/Twig source code formatting for output purposes (e.g. in plugin
	 * option description sections).
	 *
	 * @since 1.9.0
	 *
	 * @param string $source Source code.
	 * @param string $class  Optional CSS class name for the code tag
	 *                       (defaults to immonex-qformat-source)
	 *
	 * @return string Rendered HTML output.
	 */
	public static function quick_format_source_code( $source, $class = 'immonex-qformat-source' ) {
		$formatted = trim( $source );

		$replace = array(
			array(
				'/\t/' => '    ',
				'/</'  => '&lt;',
				'/>/'  => '&gt;',
			),
			array(
				'/&lt;(?!\!-- )(.*)&gt;/'            => '<span class="html-tag">&lt;$1&gt;</span>',
				'/&lt;!-- (.*) --&gt;/'              => '<span class="comment">&lt;-- <span>$1</span> --&gt;</span>',
				'/(\/[*]{1,2}|\s+\*)(.*)(\r\n|\n)/m' => '<span class="comment">$1<span>$2</span>$3</span>',
				'/([{]?# )(.*)/'                     => '<span class="comment">$1<span>$2</span></span>',
				'/{{ ([^}]*) }}/'                    => '<span class="twig-var">{{ <span>$1</span> }}</span>',
				'/{% ([^%]*) %}/'                    => '<span class="twig-control">{% <span>$1</span> %}</span>',
			),
		);

		foreach ( $replace as $level ) {
			foreach ( $level as $regex => $replacement ) {
				$formatted = preg_replace( $regex, $replacement, $formatted );
			}
		}

		return wp_sprintf(
			'<pre><code class="%1$s">%2$s</code></pre>' . PHP_EOL,
			$class,
			$formatted
		);
	} // quick_format_source_code

	/**
	 * Add text to the beginning or end of a string if it is not included already.
	 *
	 * @since 1.9.0
	 *
	 * @param string          $source    Source text.
	 * @param string|string[] $add_texts Text(s) to add if not included.
	 * @param string          $separator Separator to add between source and
	 *                                   additional text (optional).
	 * @param bool            $before    Add additional text before source text
	 *                                   (optional, default false = append text).
	 *
	 * @return string Possibly extended source text.
	 */
	public function add_text_if_missing( $source, $add_texts, $separator = ' ', $before = false ) {
		if ( empty( $add_texts ) ) {
			return $source;
		}

		if ( ! is_array( $add_texts ) ) {
			$add_texts = (string) $add_texts;
		}

		foreach ( $add_texts as $add_text ) {
			if ( false === strpos( $source, $add_text ) ) {
				$source = $before ?
					$add_text . $separator . $source :
					$source . $separator . $add_text;
			}
		}

		return trim( $source );
	} // add_text_if_missing


	/**
	 * Split a comma-separated list string – "intelligently".
	 *
	 * @since 1.9.14
	 *
	 * @param string $source        Source list string.
	 * @param string $mode          Optional split/return mode selection:
	 *                                  - "auto" (default): automatic list type detection
	 *                                  - "list": plain value array
	 *                                  - "list_or_single": plain value array or single value as string
	 *                                  - "key_value": associative key-value array (possibly nested)
	 *                                  - "key_value_non_empty": like "key_value", but without empty elements
	 * @param string $valid_chars   Valid key/value characters as RegEx (optional).
	 * @param string $divider_chars Key/Value divider characters (optional, default: =:).
	 * @param bool   $convert_bool  Convert "true" and "false" strings to real boolean values
	 *                              (optional, true by default).
	 *
	 * @return mixed[]|string List elements as array or string (if single).
	 */
	public function split_list_string( $source, $mode = 'auto', $valid_chars = false, $divider_chars = '=:', $convert_bool = true ) {
		if (
			! is_string( $source )
			|| ! empty( json_decode( $source ) )
		) {
			return $source;
		}

		if ( 'auto' === $mode ) {
			if ( false !== strpos( $source, '=' ) ) {
				$mode = 'key_value_non_empty';
			} elseif ( false !== strpos( $source, ',' ) ) {
				$mode = 'list';
			} else {
				$mode = 'list_or_single';
			}
		}

		if ( empty( $valid_chars ) ) {
			$valid_chars = 'a-z0-9äöüÄÖÜß_ {}\[\]\'"\.:;*#\-!$%&\/|?<>';
		}
		if ( 'key_value' === substr( $mode, 0, 9 ) ) {
			$valid_chars = preg_replace( "/[$divider_chars]/", '', $valid_chars );
		}

		$empty_return_value = 'list_or_single' === $mode ? '' : array();
		$source             = preg_replace( '/^(\s*)?\((\s*)?(.*?)(\s*)?(?<!\\\)\)(\s*)?$/', '$3', $source );
		$source             = preg_replace(
			'/^(\s*)?|(?<=[,=({\[])\s*|\s*(?=[,=)}\]\\\])|(\s*)?$/',
			'',
			$source
		);

		if ( 'list' === substr( $mode, 0, 4 ) ) {
			$matches = preg_split( '/(\s*)?(?<!\\\),(\s*)?/', $source, -1, PREG_SPLIT_NO_EMPTY );

			if ( empty( $matches ) ) {
				return $empty_return_value;
			}

			return 'list_or_single' === $mode && 1 === count( $matches ) ? $matches[0] : $matches;
		}

		$replace_masked = function ( $value, $divider_chars, $decode = false ) {
			$mapping = array(
				'(' => '|--',
				')' => '--|',
				'=' => '|--|',
				',' => '|_|',
			);

			$divider_chars_length = strlen( $divider_chars );
			for ( $i = 0; $i < $divider_chars_length; $i++ ) {
				$mapping[ $divider_chars[ $i ] ] = "|-{$i}-|";
			}

			if ( ! $decode ) {
				$masked_keys = array_map(
					function ( $key ) {
						return "\\{$key}";
					},
					array_keys( $mapping )
				);
			}

			return $decode ?
				str_replace( array_values( $mapping ), array_keys( $mapping ), $value ) :
				str_replace( $masked_keys, array_values( $mapping ), $value );
		}; // replace_masked

		$source  = $replace_masked( $source, $divider_chars );
		$pattern = "/\s*(?P<key>[{$valid_chars}]+)\s*([{$divider_chars}]\s*(?P<value>[{$valid_chars}]+|(?P<list>\(([^()]|(?P>list))*\)))\s*)?(?:[,)]|$)/i";
		$result  = array();

		$found = preg_match_all( $pattern, $source, $matches, PREG_SET_ORDER );

		if ( ! $found ) {
			return $empty_return_value;
		}

		foreach ( $matches as $match ) {
			$key   = trim( $match['key'] );
			$value = isset( $match['value'] ) ? $match['value'] : '';

			if ( isset( $match['list'] ) ) {
				$sub_mode = preg_match( '/[{$divider_chars}]/', $value ) ? 'key_value_non_empty' : 'list';
				$value    = $this->split_list_string( $value, $sub_mode, $valid_chars );
			}

			if ( is_string( $value ) && $convert_bool ) {
				if ( 'true' === strtolower( $value ) ) {
					$value = true;
				} elseif ( 'false' === strtolower( $value ) ) {
					$value = false;
				}
			}

			if ( ! $value && 'key_value_non_empty' === $mode ) {
				continue;
			}

			$result_key   = $replace_masked( $key, $divider_chars, true );
			$result_value = is_array( $value ) ?
				array_map(
					function ( $value_element ) use ( $replace_masked, $divider_chars ) {
						return $replace_masked( $value_element, $divider_chars, true );
					},
					$value
				) :
				$replace_masked( $value, $divider_chars, true );

			$result[ $result_key ] = $result_value;
		}

		return $result;
	} // split_list_string

	/**
	 * Perform an extended string comparison: RegEx based if $compare begins with
	 * a slash, one to one comparison (lowercase strings) otherwise.
	 *
	 * @since 1.9.17
	 *
	 * @param string $compare String or regular expression to compare the source
	 *                        string with.
	 * @param string $source  Source string.
	 *
	 * @return bool True if lowercase strings are equal or the regular expression
	 *              matches, false if the strings are not equal or the RegEx is
	 *              invalid.
	 */
	public function ext_compare( $compare, $source ) {
		if ( '/' === $compare[0] ) {
			try {
				$match = preg_match( $compare, $source );

				return $match ? true : false;
			} catch ( \Exception $e ) {
				return false;
			}
		}

		return strtolower( $source ) === strtolower( $compare );
	} // ext_compare

	/**
	 * Check if a key only contains valid characters.
	 *
	 * @since 1.9.21
	 *
	 * @param string $key Key to check.
	 *
	 * @return string|bool Original key or false if invalid.
	 */
	public function validate_key( $key ) {
		$key = trim( $key );

		if ( ! is_scalar( $key ) ) {
			return false;
		}

		$sanitized_key = preg_replace( '/[^a-zA-Z0-9_\-]/', '', $key );

		return $sanitized_key === $key ? $key : false;
	} // validate_key

	/**
	 * Split a string containing a key, (optional) arguments in brackets and value.
	 *
	 * Examples:
	 *   - foo=bar
	 *   - foo('XYZ', bar=1, baz='WTF?!')
	 *   - foo[ bar=1 ] = baz
	 *
	 * @since 2.0.0
	 *
	 * @param string $source Source string.
	 *
	 * @return mixed[]|bool Associative array with key and args or false if the
	 *                      can't be parsed that way.
	 */
	public function split_key_args_value( $source ) {
		$split = [
			'key'   => '',
			'value' => '',
			'args'  => [],
		];

		$parts = preg_split(
			'/=(?=(?:[^\[(]*[\\[(][^\])]*[\])])*[^\])]*$)/',
			$source,
			2
		);
		$key   = trim( $parts[0] );

		if ( empty( $key ) ) {
			return false;
		}

		$split['value'] = isset( $parts[1] ) ? trim( $parts[1] ) : '';
		$key_parts      = preg_split( '/[\[(]/', $key, 2 );

		if ( 1 === count( $key_parts ) ) {
			$split['key'] = $key;

			return $split;
		}

		$split['key'] = trim( $key_parts[0] );
		$raw_args     = preg_split(
			'/,(?=(?:[^"\']*["\'][^"\']*["\'])*[^"\']*$)/',
			preg_replace( '/[\])]$/', '', $key_parts[1] )
		);

		if ( empty( $raw_args ) ) {
			return $split;
		}

		$unnamed_arg_cnt = 0;
		foreach ( $raw_args as $i => $arg ) {
			$arg_parts = explode( '=', $arg, 2 );

			if ( 1 === count( $arg_parts ) ) {
				$arg_parts = [
					$unnamed_arg_cnt,
					$arg_parts[0],
				];
				$unnamed_arg_cnt++;
			}

			$split['args'][ trim( $arg_parts[0], " \n\r\t\v\x00\"'" ) ] = trim( $arg_parts[1], " \n\r\t\v\x00\"'" );
		}

		return $split;
	} // split_key_args_value

} // String_Utils
