<?php
/**
 * Class Color_Utils
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\DEV_2;

/**
 * Utility methods for color calculations.
 */
class Color_Utils {

	/**
	 * Main plugin instance
	 *
	 * @var Base
	 */
	private $plugin;

	/**
	 * Constructor: Import some required objects/values.
	 *
	 * @since 0.9
	 *
	 * @param Base $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	} // __construct

	/**
	 * Calculate the luminance value of a color.
	 *
	 * @since 0.9
	 *
	 * @param string $hex Color as hex value.
	 *
	 * @return float|bool Luminance value or false on invalid hex color value.
	 */
	public function get_luminance( $hex ) {
		$rgb = $this->hex2rgb( $hex );
		if ( ! $rgb ) {
			return false;
		}

		$luminance = 0.2126 * pow( $rgb[0] / 255, 2.2 ) +
			0.7152 * pow( $rgb[1] / 255, 2.2 ) +
			0.0722 * pow( $rgb[2] / 255, 2.2 );

		return $luminance;
	} // get_luminance

	/**
	 * Calculate the luminance difference of two colors.
	 *
	 * @since 0.9
	 *
	 * @param string $hex1 First color as hex value.
	 * @param string $hex2 First color as hex value.
	 *
	 * @return float|bool Difference value (>= 5 is recommended for a suitable
	 *   optical contrast) or false on invalid hex color value(s).
	 */
	public function get_luminance_difference( $hex1, $hex2 ) {
		$luminance1 = $this->get_luminance( $hex1 );
		if ( ! $luminance1 ) {
			return false;
		}

		$luminance2 = $this->get_luminance( $hex2 );
		if ( ! $luminance2 ) {
			return false;
		}

		if ( $luminance1 > $luminance2 ) {
			return ( $luminance1 + 0.05 ) / ( $luminance2 + 0.05 );
		} else {
			return ( $luminance2 + 0.05 ) / ( $luminance1 + 0.05 );
		}
	} // get_luminance_difference

	/**
	 * Lighten/Darken a given color.
	 *
	 * @since 0.9
	 *
	 * @param string $hex The original color as hex value.
	 * @param float  $percent_dec Decimal adjust value (0.2 = lighten by 20%, -0.4 = darken by 40%).
	 *
	 * @return string|bool Hex value of lightened/darkened color or false on
	 *   invalid hex color strings.
	 */
	public function adjust_luminance( $hex, $percent_dec ) {
		$hex = $this->expand_hex_color_value( $hex, false );
		if ( ! $hex ) {
			return false;
		}

		if ( $percent_dec < -100 || $percent_dec > 100 ) {
			return $hex;
		}

		if ( $percent_dec < -1 || $percent_dec > 1 ) {
			// Convert percentage to decimal value.
			$percent_dec = $percent_dec / 100;
		}

		$new_hex = '#';

		// Convert to decimal and change luminosity.
		for ( $i = 0; $i < 3; $i++ ) {
			$dec      = hexdec( substr( $hex, $i * 2, 2 ) );
			$dec      = min( max( 0, $dec + $dec * $percent_dec ), 255 );
			$new_hex .= str_pad( dechex( $dec ), 2, 0, STR_PAD_LEFT );
		}

		return $new_hex;
	} // adjust_luminance

	/**
	 * Set/Adjust the lightness of a given color.
	 *
	 * @since 0.9
	 *
	 * @param string $hex The original color as hex value.
	 * @param float  $percent_dec Decimal value of percentage of lightness adjustment
	 *    (e.g. 0.2 or 20 = lighten by 20%, -0.4 or -40 = darken by 40%).
	 * @param bool   $relative Calculate new lightness relative to the current
	 *     value (optional).
	 * @return string|bool Hex value of new color or false on
	 *   invalid hex color strings.
	 */
	public function set_lightness( $hex, $percent_dec, $relative = false ) {
		$hex = $this->expand_hex_color_value( $hex, false );
		if ( ! $hex ) {
			return false;
		}

		// Convert original color to HSL.
		$hsl = $this->hex2hsl( $hex );

		$new = $this->adjust_hsl_value( $hsl, 'lightness', $percent_dec, $relative );

		return $this->hsl2hex( $new );
	} // set_lightness

	/**
	 * Set/Adjust the saturation of a given color.
	 *
	 * @since 0.9
	 *
	 * @param string $hex The original color as hex value.
	 * @param float  $percent_dec Decimal value or percentage of saturation adjustment
	 *    (e.g. 0.2 or 20 = saturate by 20%, -0.4 or -40 = desaturate by 40%).
	 * @param bool   $relative Calculate new saturation relative to the current value (optional).
	 *
	 * @return string|bool Hex value of new color or false on
	 *   invalid hex color strings.
	 */
	public function set_saturation( $hex, $percent_dec, $relative = false ) {
		$hex = $this->expand_hex_color_value( $hex, false );
		if ( ! $hex ) {
			return false;
		}

		// Convert original color to HSL.
		$hsl = $this->hex2hsl( $hex );

		$new = $this->adjust_hsl_value( $hsl, 'saturation', $percent_dec, $relative );

		return $this->hsl2hex( $new );
	} // set_saturation

	/**
	 * Set/Adjust HSL values of a given color.
	 *
	 * @since 0.9
	 *
	 * @param string   $hex The original color as hex value.
	 * @param string[] $adjustments Associative array of adjustment values/percentages.
	 * @param bool     $relative Calculate new saturation relative to the current value (optional).
	 *
	 * @return string|bool Hex value of new color or false on
	 *   invalid hex color strings.
	 */
	public function set_hsl( $hex, $adjustments, $relative = false ) {
		$hex = $this->expand_hex_color_value( $hex, false );
		if ( ! $hex ) {
			return false;
		}

		// Convert original color to HSL.
		$hsl = $this->hex2hsl( $hex );

		if ( is_array( $adjustments ) && count( $adjustments ) > 0 ) {
			foreach ( $adjustments as $key => $value ) {
				$hsl = $this->adjust_hsl_value( $hsl, $key, $value, $relative );
			}
		}

		return $this->hsl2hex( $hsl );
	} // set_hsl

	/**
	 * Calculate the brightness of a color.
	 *
	 * @since 0.9
	 *
	 * @param string $hex Color as hex value.
	 * @param bool   $percent Return percentage.
	 *
	 * @return int|bool Brightness value (0 - 255) or percentage (0 - 100).
	 */
	public function get_brightness( $hex, $percent = false ) {
		$rgb = $this->hex2rgb( $hex );
		if ( ! $rgb ) {
			return false;
		}

		$brightness = ( 299 * $rgb[0] + 587 * $rgb[1] + 114 * $rgb[2] ) / 1000;

		return (int) ( $percent && $brightness > 0 ? $brightness * 100 / 255 : $brightness );
	} // get_brightness_difference

	/**
	 * Calculate the brightness difference of two colors.
	 *
	 * @since 0.9
	 *
	 * @param string $hex1 First color as hex value.
	 * @param string $hex2 First color as hex value.
	 *
	 * @return int|bool Difference value (>= 125 is recommended for a suitable
	 *   optical contrast) or false on invalid hex color value(s).
	 */
	public function get_brightness_difference( $hex1, $hex2 ) {
		$brightness1 = $this->get_brightness( $hex1 );
		if ( ! $brightness1 ) {
			return false;
		}

		$brightness2 = $this->get_brightness( $hex2 );
		if ( ! $brightness2 ) {
			return false;
		}

		return (int) abs( $brightness1 - $brightness2 );
	} // get_brightness_difference

	/**
	 * Calculate the contrast percentage between two colors.
	 *
	 * @since 0.9
	 *
	 * @param string $hex1 First color as hex value.
	 * @param string $hex2 First color as hex value.
	 *
	 * @return float|bool Contrast value (percent) or false on invalid
	 *   hex color value(s).
	 */
	public function get_contrast_pct( $hex1, $hex2 ) {
		$rgb1 = $this->hex2rgb( $hex1 );
		if ( ! $rgb1 ) {
			return false;
		}

		$rgb2 = $this->hex2rgb( $hex2 );
		if ( ! $rgb2 ) {
			return false;
		}

		$r = ( max( $rgb1[0], $rgb2[0] ) - min( $rgb1[0], $rgb2[0] ) ) * 299;
		$g = ( max( $rgb1[1], $rgb2[1] ) - min( $rgb1[1], $rgb2[1] ) ) * 587;
		$b = ( max( $rgb1[2], $rgb2[2] ) - min( $rgb1[2], $rgb2[2] ) ) * 114;

		return ( $r + $g + $b ) / 1000 / 2.55;
	} // get_contrast_pct

	/**
	 * Mix two colors.
	 *
	 * @since 0.9
	 *
	 * @param string $hex1 First color as hex value.
	 * @param string $hex2 First color as hex value.
	 * @param string $weight Weighting.
	 *
	 * @return float|bool Hex value of mixed color or false on invalid
	 *   hex color value(s).
	 */
	public function mix( $hex1, $hex2, $weight = 0.5 ) {
		$rgb1 = $this->hex2rgb( $hex1 );
		if ( ! $rgb1 ) {
			return false;
		}

		$rgb2 = $this->hex2rgb( $hex2 );
		if ( ! $rgb2 ) {
			return false;
		}

		$f = function( $x ) use ( $weight ) {
			return $weight * $x;
		};
		$g = function( $x ) use ( $weight ) {
			return ( 1 - $weight ) * $x;
		};
		$h = function( $x, $y ) {
			return round( $x + $y );
		};

		$new_color_rgb = array_map( $h, array_map( $f, $rgb1 ), array_map( $g, $rgb2 ) );

		return $this->rgb2hex( $new_color_rgb );
	} // mix

	/**
	 * Convert an RGB array to a hex color string.
	 *
	 * @since 0.9
	 *
	 * @param int[] $rgb Array of RGB values.
	 *
	 * @return string|bool Hex string starting with a hash or false on invalid
	 *  RGB array.
	 */
	public function rgb2hex( $rgb ) {
		if ( ! is_array( $rgb ) || 3 !== count( $rgb ) ) {
			return false;
		}

		$hex = '#';

		for ( $i = 0; $i < 3; $i++ ) {
			$hex .= str_pad( dechex( $rgb[ $i ] ), 2, 0, STR_PAD_LEFT );
		}

		return $hex;
	} // hex2rgb

	/**
	 * Convert a hex color string to an RGB array.
	 *
	 * @since 0.9
	 *
	 * @param string $hex Color as hex value.
	 *
	 * @return int[] Array of RGB color values.
	 */
	public function hex2rgb( $hex ) {
		$hex = $this->expand_hex_color_value( $hex );
		if ( ! $hex ) {
			return false;
		}

		return sscanf( $hex, '#%02x%02x%02x' );
	} // hex2rgb

	/**
	 * Set/Adjust color values of a given HSL array.
	 *
	 * @since 0.9
	 *
	 * @param float[] $hsl Associative array of HSL color values.
	 * @param string  $type Key/Name of HSL color value to be changed.
	 * @param float   $percent_dec Decimal value or percentage.
	 * @param bool    $relative Calculate new saturation relative to the current
	 *      value (optional).
	 *
	 * @return int[] Associative array of HSL color values or false on
	 *   invalid HSL arrays.
	 */
	public function adjust_hsl_value( $hsl, $type, $percent_dec, $relative = false ) {
		if (
			! is_array( $hsl ) ||
			! isset( $hsl['h'] ) ||
			! isset( $hsl['s'] ) ||
			! isset( $hsl['l'] )
		) {
			return false;
		}

		if ( $percent_dec < -100 || $percent_dec > 100 ) {
			return $hsl;
		}

		if ( $percent_dec < -1 || $percent_dec > 1 ) {
			// Convert percentage to decimal value.
			$percent_dec = $percent_dec / 100;
		}

		switch ( strtolower( $type ) ) {
			case 'h':
			case 'hue':
				$key = 'h';
				break;
			case 's':
			case 'saturation':
				$key = 's';
				break;
			case 'l':
			case 'lightness':
				$key = 'l';
				break;
			default:
				return $hsl;
		}

		if ( $relative ) {
			$hsl[ $key ] = $hsl[ $key ] * ( 1 + $percent_dec );
		} else {
			$hsl[ $key ] = $percent_dec;
		}

		$hsl[ $key ] = min( max( $hsl[ $key ], 0 ), 1 );

		return $hsl;
	} // adjust_hsl_value

	/**
	 * Convert a hex color string to an HSL array.
	 *
	 * @since 0.9
	 *
	 * @param string $hex Color as hex value.
	 *
	 * @return int[] Associative array of HSL color values or false on
	 *   invalid hex value.
	 */
	public function hex2hsl( $hex ) {
		$rgb = $this->hex2rgb( $hex );
		if ( ! $rgb ) {
			return false;
		}

		$r = $rgb[0] / 255;
		$g = $rgb[1] / 255;
		$b = $rgb[2] / 255;

		$min     = min( $r, $g, $b );
		$max     = max( $r, $g, $b );
		$del_max = $max - $min;

		$l = ( $max + $min ) / 2;
		// @codingStandardsIgnoreLine
		if ( 0 == $del_max ) {
			$h = 0;
			$s = 0;
		} else {
			$div = $l < 0.5 ? $max + $min : 2 - $max - $min;
			// @codingStandardsIgnoreLine
			$s   = $div != 0 ? $del_max / $div : $del_max;

			$del_r = ( ( ( $max - $r ) / 6 ) + ( $del_max / 2 ) ) / $del_max;
			$del_g = ( ( ( $max - $g ) / 6 ) + ( $del_max / 2 ) ) / $del_max;
			$del_b = ( ( ( $max - $b ) / 6 ) + ( $del_max / 2 ) ) / $del_max;

			if ( $r === $max ) {
				$h = $del_b - $del_g;
			} elseif ( $g === $max ) {
				$h = ( 1 / 3 ) + $del_r - $del_b;
			} elseif ( $b === $max ) {
				$h = ( 2 / 3 ) + $del_g - $del_r;
			}

			if ( $h < 0 ) {
				$h++;
			}
			if ( $h > 1 ) {
				$h--;
			}
		}

		$hsl = array(
			'h' => $h * 360,
			's' => $s,
			'l' => $l,
		);

		return $hsl;
	} // hex2hsl

	/**
	 * Convert an HSL array to a hex string.
	 *
	 * @since 0.9
	 *
	 * @param int[] $hsl Associative array of HSL color values.
	 *
	 * @return string|bool Hex color string or false on invalid HSL array.
	 */
	public function hsl2hex( $hsl = array() ) {
		if (
			! is_array( $hsl ) ||
			! isset( $hsl['h'] ) ||
			! isset( $hsl['s'] ) ||
			! isset( $hsl['l'] )
		) {
			return;
		}

		list( $h, $s, $l ) = array( $hsl['h'] / 360, $hsl['s'], $hsl['l'] );

		if ( 0 === $s ) {
			$r = $l * 255;
			$g = $l * 255;
			$b = $l * 255;
		} else {
			if ( $l < 0.5 ) {
				$v2 = $l * ( 1 + $s );
			} else {
				$v2 = ( $l + $s ) - ( $s * $l );
			}

			$v1 = 2 * $l - $v2;
			$r  = round( 255 * $this->hue2rgb( $v1, $v2, $h + ( 1 / 3 ) ) );
			$g  = round( 255 * $this->hue2rgb( $v1, $v2, $h ) );
			$b  = round( 255 * $this->hue2rgb( $v1, $v2, $h - ( 1 / 3 ) ) );
		}

		$hex = $this->rgb2hex( array( $r, $g, $b ) );

		return $hex;
	} // hsl2hex

	/**
	 * Convert a hue to its corresponding RGB value.
	 *
	 * @since 0.9
	 *
	 * @param int $v1 Value 1.
	 * @param int $v2 Value 2.
	 * @param int $vh Hue value.
	 *
	 * @return int
	 */
	public function hue2rgb( $v1, $v2, $vh ) {
		if ( $vh < 0 ) {
			$vh++;
		}
		if ( $vh > 1 ) {
			$vh--;
		}
		if ( ( 6 * $vh ) < 1 ) {
			return ( $v1 + ( $v2 - $v1 ) * 6 * $vh );
		}
		if ( ( 2 * $vh ) < 1 ) {
			return $v2;
		}
		if ( ( 3 * $vh ) < 2 ) {
			return ( $v1 + ( $v2 - $v1 ) * ( ( 2 / 3 ) - $vh ) * 6 );
		}

		return $v1;
	} // hue2rgb

	/**
	 * Validate/Expand an hex color value string.
	 *
	 * @since 0.9
	 *
	 * @param string $hex Original hex color string.
	 * @param bool   $hash Return value starting with a hash.
	 *
	 * @return string|bool Possibly expanded string (# + six characters) or false
	 *   on invalid hex color strings.
	 */
	public function expand_hex_color_value( $hex, $hash = true ) {
		if ( ! is_string( $hex ) ) {
			return false;
		}

		$hex = preg_replace( '/[^0-9a-fA-F]/i', '', $hex );
		if ( 3 !== strlen( $hex ) && 6 !== strlen( $hex ) ) {
			return false;
		}

		// Expand shortened hex string.
		if ( strlen( $hex ) < 6 ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		return ( $hash ? '#' : '' ) . $hex;
	} // expand_hex_color_value

} // Color_Utils
