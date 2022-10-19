<?php
/**
 * Class Geo_Utils
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\DEV_2;

/**
 * Geocoding related utility methods.
 */
class Geo_Utils {

	const EARTH_RADIUS                       = 6378.388;
	const NOMINATIM_BASE_URL                 = 'https://nominatim.openstreetmap.org/search';
	const PHOTON_BASE_URL                    = 'https://photon.komoot.io/api/';
	const PHOTO_LOCATION_BIAS_LAT            = 51.163375; // Latitude of the geographical center of Germany.
	const PHOTO_LOCATION_BIAS_LNG            = 10.447683; // Longitude of the geographical center of Germany.
	const GOOGLE_MAPS_API_WITH_KEY_BASE_URL  = 'https://maps.googleapis.com/maps/api/geocode/';
	const BING_MAPS_API_BASE_URL             = 'https://dev.virtualearth.net/REST/v1/Locations';
	const USERAGENT                          = 'immonexWpPluginGeocoder/0.9';
	const COUNTRYCODES_GERMANY_AND_NEIGHBORS = 'de,dk,nl,be,lu,fr,ch,at,cz,pl';
	const COUNTRYCODES_EU                    = 'be,bg,cz,dk,de,ee,ie,el,es,fr,hr,it,cy,lv,lt,lu,hu,mt,nl,at,pl,pt,ro,si,sk,fi,se';
	const COUNTRYCODES_EUROPE                = 'ad,al,at,ba,be,bg,by,ch,cy,cz,de,dk,ee,es,fi,fo,fr,gg,gi,gr,hr,hu,ie,im,is,it,je,li,lt,lu,lv,mc,md,mk,mt,nl,no,pl,pt,ro,ru,se,si,sj,sk,sm,tr,ua,uk,va,yu';

	/**
	 * Supported geocoding providers
	 *
	 * @var mixed[]
	 */
	private static $providers = array(
		'nominatim'   => array(
			'name'         => 'Nominatim/OSM',
			'key_required' => false,
		),
		'photon'      => array(
			'name'         => 'Photon/OSM',
			'key_required' => false,
		),
		'google_maps' => array(
			'name'         => 'Google Maps',
			'key_required' => true,
		),
		'bing_maps'   => array(
			'name'         => 'Bing Maps',
			'key_required' => true,
		),
	);

	/**
	 * Calculate the distance (beeline) in km between two coordinate pairs.
	 *
	 * @param float $lat1 Location 1 latitude.
	 * @param float $lng1 Location 1 longitude.
	 * @param float $lat2 Location 2 latitude.
	 * @param float $lng2 Location 2 longitude.
	 *
	 * @return float Distance in km.
	 */
	public static function calculate_distance_km( $lat1, $lng1, $lat2, $lng2 ) {
		$lat1_rad = deg2rad( $lat1 );
		$lng1_rad = deg2rad( $lng1 );
		$lat2_rad = deg2rad( $lat2 );
		$lng2_rad = deg2rad( $lng2 );

		return static::EARTH_RADIUS * acos(
			sin( $lat1_rad ) * sin( $lat2_rad )
			+ cos( $lat1_rad ) * cos( $lat2_rad )
			* cos( $lng2_rad - $lng1_rad )
		);
	} // calculate_distance_km

	/**
	 * Get geo information for a given address.
	 *
	 * @param string        $address Address for geocoding.
	 * @param string        $return_type "full" or "compact" (the latter one only returns coordinates).
	 * @param string[]|bool $use_providers Geocoding API to use in given order until a valid result is
	 *     returned (false = use default provider list).
	 * @param array         $keys Array of API keys (if required).
	 * @param string        $language ISO-2 Language code for results (if supported, optional, default "de").
	 * @param string|bool   $countrycodes Comma separated list of ISO-2 country codes for limiting the geo query
	 *       ("germany_and_neighbors", "eu" or "europe" for respective default lists), false for no country limiting.
	 *
	 * @return object|array|bool Geo information/coordinates, false if geocoding failed.
	 */
	public static function geocode( $address, $return_type = 'compact', $use_providers = false, $keys = array(), $language = 'de', $countrycodes = false ) {
		if ( $address ) {
			if ( is_string( $use_providers ) ) {
				$use_providers = array( $use_providers );
			}

			if ( $use_providers && is_array( $use_providers ) && count( $use_providers ) > 0 ) {
				$providers = array();
				foreach ( $use_providers as $provider ) {
					if ( isset( self::$providers[ $provider ] ) ) {
						$providers[ $provider ] = self::$providers[ $provider ];
					}
				}
			} else {
				$providers = self::$providers;
			}

			if ( count( $providers ) > 0 ) {
				if ( $countrycodes ) {
					$countrycodes = self::parse_countrycodes( $countrycodes );
				}

				foreach ( $providers as $provider => $api_attribs ) {
					if (
						$api_attribs['key_required'] &&
						(
							! isset( $keys[ $provider ] ) ||
							! $keys[ $provider ]
						)
					) {
						// Skip provider if no required key has been provided.
						continue;
					}

					$provider_geocode_method = "geocode_$provider";
					$provider_result_method  = "get_result_$provider";

					$geocode_response = self::$provider_geocode_method( $address, isset( $keys[ $provider ] ) ? $keys[ $provider ] : false, $language, $countrycodes );
					if ( $geocode_response ) {
						$geocode_response = json_decode( $geocode_response );
					}

					if ( $geocode_response ) {
						$geocode_result = self::$provider_result_method( $geocode_response, $return_type );
						if ( $geocode_result ) {
							if ( 'compact' === $return_type ) {
								$geocode_result['provider'] = isset( $api_attribs['name'] ) ? $api_attribs['name'] : '';
							}

							return $geocode_result;
						}
					}
				}
			}
		}

		// Geocoding failed.
		return false;
	} // function getGeoPosition

	/**
	 * Get the geocoding status for a given address.
	 *
	 * @since 0.5.1
	 *
	 * @param string      $address Address for geocoding.
	 * @param bool|string $use_provider Geocoding API to use (false = try one by one
	 *     until a valid result is returned).
	 * @param array       $keys Array of API keys (if required).
	 * @param string      $language ISO-2 Language code for results (if supported, optional, default "de").
	 * @param string|bool $countrycodes Comma separated list of ISO-2 country codes for limiting the geo query
	 *     ("germany_and_neighbors", "eu" or "europe" for respective default lists), false for no country limiting.
	 *
	 * @return string|bool Status information or false on retrieval error.
	 */
	public static function get_geocoding_status( $address = 'Platz der Republik 1, Berlin, Germany', $use_provider = false, $keys = array(), $language = 'de', $countrycodes = false ) {
		if ( $use_provider && isset( self::$providers[ $use_provider ] ) ) {
			$providers = array( $use_provider => self::$providers[ $use_provider ] );
		} else {
			$providers = self::$providers;
		}

		if ( $countrycodes ) {
			$countrycodes = self::parse_countrycodes( $countrycodes );
		}

		$provider_states = array();

		foreach ( $providers as $provider => $api_attribs ) {
			if (
				$api_attribs['key_required'] &&
				(
					! isset( $keys[ $provider ] )
					|| ! $keys[ $provider ]
				)
			) {
				// Skip provider if no required key has been provided.
				continue;
			}

			$provider_geocode_method = "geocode_{$provider}";
			$provider_status_method  = "get_status_{$provider}";

			$geocode_response = self::$provider_geocode_method( $address, isset( $keys[ $provider ] ) ? $keys[ $provider ] : false, $language, $countrycodes );
			if ( $geocode_response ) {
				$geocode_decoded = json_decode( $geocode_response );
				if ( null !== $geocode_decoded ) {
					$geocode_response = $geocode_decoded;
				}
			}

			if ( $geocode_response ) {
				$provider_status = self::$provider_status_method( $geocode_response );
			}

			$provider_states[] = wp_sprintf(
				'[%s] %s',
				$api_attribs['name'],
				! empty( $provider_status ) && is_string( $provider_status ) ? $provider_status : __( 'unknown', 'immonex-wp-free-plugin-core' )
			);
		}

		return ! empty( $provider_states ) ? implode( ', ', $provider_states ) : false;
	} // get_geocoding_status

	/**
	 * Geocode a given address using Nominatim (OpenStreetMap).
	 *
	 * @since 0.9
	 *
	 * @param string      $address Address for geocoding.
	 * @param string      $key API key not required for this service.
	 * @param string      $language ISO-2 Language code for results (if supported, optional, default "de").
	 * @param string|bool $countrycodes Comma separated list of ISO-2 country codes for limiting the geo query
	 *     or false (default) for no limiting.
	 *
	 * @return string|bool Geo data or false on error.
	 */
	private static function geocode_nominatim( $address, $key = false, $language = 'de', $countrycodes = false ) {
		$url = self::NOMINATIM_BASE_URL . '?q=' . rawurlencode( $address ) . '&format=json&accept-language=' . $language;
		if ( $countrycodes ) {
			$url .= '&countrycodes=' . $countrycodes;
		}

		return General_Utils::get_url_contents( $url, self::USERAGENT );
	} // geocode_nominatim

	/**
	 * Check/return Nominatim geocoding results.
	 *
	 * @since 0.9
	 *
	 * @param object $response Raw response object.
	 * @param string $return_type "full" or "compact" (the latter one only returns coordinates).
	 *
	 * @return string|bool Geo data or false on error.
	 */
	private static function get_result_nominatim( $response, $return_type ) {
		if (
			is_array( $response ) &&
			count( $response ) > 0
		) {
			foreach ( $response as $geo_object ) {
				if (
					isset( $geo_object->lat ) &&
					isset( $geo_object->lon )
				) {
					if ( 'compact' === $return_type ) {
						return array(
							'lat'     => $geo_object->lat,
							'lng'     => $geo_object->lon,
							'lat_rad' => $geo_object->lat * ( pi() / 180 ),
							'lng_rad' => $geo_object->lon * ( pi() / 180 ),
						);
					} else {
						return $geo_object;
					}
				}
			}
		} else {
			return false;
		}
	} // get_result_nominatim

	/**
	 * Check/return status information for a Nominatim geocoding request.
	 *
	 * @since 0.9
	 *
	 * @param object|string $response Raw response object or string.
	 *
	 * @return string|bool Status or false on error.
	 */
	private static function get_status_nominatim( $response ) {
		if ( ! is_string( $response ) ) {
			return false;
		}

		$title = preg_match( '/\<title\>(.*)\<\/title\>/', $response, $matches );
		return $title ? '[Nominatim] ' . $matches[1] : false;
	} // get_status_nominatim

	/**
	 * Geocode a given address using Photon (OpenStreetMap).
	 *
	 * @since 0.9
	 *
	 * @param string      $address Address for geocoding.
	 * @param string      $key API key not required for this service.
	 * @param string      $language ISO-2 Language code for results (if supported, optional, default "de").
	 * @param string|bool $countrycodes Comma separated list of ISO-2 country codes for limiting the geo query
	 *     or false (default) for no limiting (NOT SUPPORTED HERE YET).
	 *
	 * @return string|bool Geo data or false on error.
	 */
	private static function geocode_photon( $address, $key = false, $language = 'de', $countrycodes = false ) {
		$url = self::PHOTON_BASE_URL . '?q=' . rawurlencode( $address ) . '&lang=' . $language .
			'&lat=' . self::PHOTO_LOCATION_BIAS_LAT . '&lon=' . self::PHOTO_LOCATION_BIAS_LNG . '&limit=100';

		return General_Utils::get_url_contents( $url, self::USERAGENT );
	} // geocode_photon

	/**
	 * Check/return Photon geocoding results.
	 *
	 * @since 0.9
	 *
	 * @param object $response Raw response object.
	 * @param string $return_type "full" or "compact" (the latter one only returns coordinates).
	 *
	 * @return string|bool Geo data or false on error.
	 */
	private static function get_result_photon( $response, $return_type ) {
		if (
			isset( $response->features ) &&
			count( $response->features ) > 0
		) {
			foreach ( $response->features as $geo_object ) {
				if ( isset( $geo_object->geometry->coordinates ) ) {
					if ( 'compact' === $return_type ) {
						return array(
							'lat'     => $geo_object->geometry->coordinates[1],
							'lng'     => $geo_object->geometry->coordinates[0],
							'lat_rad' => $geo_object->geometry->coordinates[1] * ( pi() / 180 ),
							'lng_rad' => $geo_object->geometry->coordinates[0] * ( pi() / 180 ),
						);
					} else {
						return $geo_object;
					}
				}
			}
		} else {
			return false;
		}
	} // get_result_photon

	/**
	 * Check/return status information for a Photon geocoding request.
	 *
	 * @since 0.9
	 *
	 * @param object $response Raw response object.
	 *
	 * @return string|bool Status or false on error.
	 */
	private static function get_status_photon( $response ) {
		if ( isset( $response->message ) ) {
			return '[Photon] ' . $response->message;
		} else {
			return false;
		}
	} // get_status_photon

	/**
	 * Geocode a given address using the Google Maps API.
	 *
	 * @since 0.5.1
	 *
	 * @param string      $address Address for geocoding.
	 * @param string      $key Google Maps API key.
	 * @param string      $language ISO-2 Language code for results (if supported, optional, default "de").
	 * @param string|bool $countrycodes Comma separated list of ISO-2 country codes for limiting the geo query
	 *     or false (default) for no limiting (NOT SUPPORTED HERE YET).
	 *
	 * @return string|bool Geo data or false on error.
	 */
	private static function geocode_google_maps( $address, $key = false, $language = 'de', $countrycodes = false ) {
		if ( ! $key ) {
			return false;
		}

		$url  = self::GOOGLE_MAPS_API_WITH_KEY_BASE_URL;
		$url .= 'json?address=' . rawurlencode( $address ) . '&sensor=false&language=' . $language . ( $key ? '&key=' . trim( $key ) : '' );

		return General_Utils::get_url_contents( $url, self::USERAGENT );
	} // geocode_google_maps

	/**
	 * Check/return Google Maps geocoding results.
	 *
	 * @since 0.5.1
	 *
	 * @param object $response Raw response object.
	 * @param string $return_type "full" or "compact" (the latter one only returns coordinates).
	 *
	 * @return string|bool Geo data or false on error.
	 */
	private static function get_result_google_maps( $response, $return_type ) {
		if (
			isset( $response->status ) &&
			'OK' === $response->status &&
			isset( $response->results[0]->geometry->location )
		) {
			if ( 'compact' === $return_type ) {
				return array(
					'lat'     => $response->results[0]->geometry->location->lat,
					'lng'     => $response->results[0]->geometry->location->lng,
					'lat_rad' => $response->results[0]->geometry->location->lat * ( pi() / 180 ),
					'lng_rad' => $response->results[0]->geometry->location->lng * ( pi() / 180 ),
				);
			} else {
				return $response;
			}
		} else {
			return false;
		}
	} // get_result_google_maps

	/**
	 * Check/return status information for a Google Maps geocoding request.
	 *
	 * @since 0.5.1
	 *
	 * @param object $response Raw response object.
	 *
	 * @return string|bool Status or false on error.
	 */
	private static function get_status_google_maps( $response ) {
		if ( isset( $response->status ) ) {
			return '[Google Maps] ' . $response->status;
		} else {
			return false;
		}
	} // get_status_google_maps

	/**
	 * Geocode a given address using the Bing Maps API.
	 *
	 * @since 0.5.1
	 *
	 * @param string      $address Address for geocoding.
	 * @param string      $key Bing Maps API key (required).
	 * @param string      $language ISO-2 Language code for results (if supported, optional, default "de").
	 * @param string|bool $countrycodes Comma separated list of ISO-2 country codes for limiting the geo query
	 *     or false (default) for no limiting (NOT SUPPORTED HERE YET).
	 *
	 * @return string|bool Geo data or false on error.
	 */
	private static function geocode_bing_maps( $address, $key, $language = 'de', $countrycodes = false ) {
		$url = self::BING_MAPS_API_BASE_URL . '?q=' . rawurlencode( $address ) . '&maxResults=1&includeNeighborhood=0&key=' . trim( $key );

		return General_Utils::get_url_contents( $url, self::USERAGENT );
	} // geocode_bing_maps

	/**
	 * Check/return Bing Maps geocoding results.
	 *
	 * @since 0.5.1
	 *
	 * @param object $response Raw response object.
	 * @param string $return_type "full" or "compact" (the latter one only returns coordinates).
	 *
	 * @return string|bool Geo data or false on error.
	 */
	private static function get_result_bing_maps( $response, $return_type ) {
		// @codingStandardsIgnoreLine
		if ( isset( $response->resourceSets[0]->resources[0]->geocodePoints[0]->coordinates ) ) {
			if ( 'compact' === $return_type ) {
				// @codingStandardsIgnoreLine
				$lat = $response->resourceSets[0]->resources[0]->geocodePoints[0]->coordinates[0];
				// @codingStandardsIgnoreLine
				$lng = $response->resourceSets[0]->resources[0]->geocodePoints[0]->coordinates[1];

				return array(
					'lat'     => $lat,
					'lng'     => $lng,
					'lat_rad' => $lat * ( pi() / 180 ),
					'lng_rad' => $lng * ( pi() / 180 ),
				);
			} else {
				return $response;
			}
		} else {
			return false;
		}
	} // get_result_bing_maps

	/**
	 * Check/return status information for a Bing Maps geocoding request.
	 *
	 * @since 0.5.1
	 *
	 * @param object $response Raw response object.
	 *
	 * @return string|bool Status or false on error.
	 */
	private static function get_status_bing_maps( $response ) {
		$status = '';
		// @codingStandardsIgnoreStart
		if ( isset( $response->authenticationResultCode ) ) {
			$status .= 'Authentication: ' . $response->authenticationResultCode;
		}
		if ( isset( $response->errorDetails ) ) {
			if ( $status ) {
				$status .= ', ';
			}
			$status .= 'Error(s): ' . implode( $response->errorDetails );
		}
		// @codingStandardsIgnoreEnd

		return trim( $status ) ? '[Bing Maps] ' . $status : false;
	} // get_status_google_maps

	/**
	 * Parse and check a given country code list or alias string.
	 *
	 * @since 0.9
	 *
	 * @param string $countrycodes Comma separated code list or alias for a default code set.
	 *
	 * @return string|bool Parsed/Checked list or false on invalid strings.
	 */
	private static function parse_countrycodes( $countrycodes ) {
		if ( ! $countrycodes ) {
			return false;
		}

		// Replace default code sets if the related name is stated.
		switch ( $countrycodes ) {
			case 'germany_and_neighbors':
				$countrycodes = self::COUNTRYCODES_GERMANY_AND_NEIGHBORS;
				break;
			case 'eu':
				$countrycodes = self::COUNTRYCODES_EU;
				break;
			case 'europe':
				$countrycodes = self::COUNTRYCODES_EUROPE;
		}

		// Extract and recompile only valid string elements.
		$check_codes  = preg_match_all( '/[ ]?([a-z]{2})[ ]?,/', strtolower( $countrycodes ), $matches );
		$countrycodes = $check_codes > 0 ? implode( ',', $matches[1] ) : false;

		return $countrycodes;
	} // parse_countrycodes

} // Geo_Utils
