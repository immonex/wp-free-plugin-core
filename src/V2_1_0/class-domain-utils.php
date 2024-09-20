<?php
/**
 * Class Domain_Utils
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\V2_1_0;

/**
 * TLD related utilities.
 */
class Domain_Utils {

	const IANA_TLD_LIST_URL     = 'https://data.iana.org/TLD/tlds-alpha-by-domain.txt';
	const UPDATE_TLD_LIST_AFTER = '-6 months';
	const STANDARD_TLDS         = array(
		'DE',
		'COM',
		'NET',
		'ORG',
		'INFO',
		'BIZ',
		'DEV',
		'ONLINE',
		'STORE',
		'IO',
		'CLOUD',
		'TECH',
		'SITE',
		'WEBSITE',
		'NETWORK',
		'GROUP',
		'WORLD',
		'MUSIC',
		'ONE',
		'RADIO',
		'SPORT',
		'FOOD',
		'DIY',
		'FITNESS',
		'PHOTOS',
		'PICTURES',
		'REISE',
		'STYLE',
		'BIO',
		'ACADEMY',
		'COMPUTER',
		'HOST',
		'MOBI',
		'PAGE',
		'PARTNERS',
		'SEX',
		'UNIVERSITY',
		'EU',
		'AT',
		'CH',
		'LI',
		'NL',
		'BE',
		'LU',
		'FR',
		'IT',
		'ES',
		'PT',
		'PL',
		'CZ',
		'SK',
		'HU',
		'SI',
		'HR',
		'RS',
		'BA',
		'ME',
		'MK',
		'AL',
		'GR',
		'RO',
		'BG',
	);

	/**
	 * Current list of TLDs
	 *
	 * @var string[]
	 */
	private $tld_list = array();

	/**
	 * Check if the given string is a valid top level domain.
	 *
	 * @since 2.1.0
	 *
	 * @param string $tld The TLD to check.
	 *
	 * @return bool True if valid.
	 */
	public function is_valid_tld( $tld ) {
		$tld = strtoupper( trim( $tld ) );

		if ( in_array( $tld, static::STANDARD_TLDS, true ) ) {
			return true;
		}

		if ( in_array( $tld, $this->get_tld_list(), true ) ) {
			return true;
		}

		return false;
	} // is_valid_tld

	/**
	 * Read and return the current TLD list.
	 *
	 * @since 2.1.0
	 *
	 * @return string[] List of TLDs.
	 */
	public function get_tld_list() {
		if ( ! empty( $this->tld_list ) ) {
			return $this->tld_list;
		}

		$this->maybe_update_tld_list();

		$tlds = file( $this->get_tld_file(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );

		if ( ! is_array( $tlds ) || empty( $tlds ) ) {
			return array();
		}

		$this->tld_list = $tlds;

		return $this->tld_list;
	} // get_tld_list

	/**
	 * Retrieve TLD list from IANA and update the local file if it's older than
	 * 6 months.
	 *
	 * @since 2.1.0
	 */
	private function maybe_update_tld_list() {
		global $wp_filesystem;

		$tld_file = $this->get_tld_file();

		if (
			! file_exists( $tld_file )
			|| filemtime( $tld_file ) < strtotime( static::UPDATE_TLD_LIST_AFTER )
		) {
			$response = wp_remote_get( static::IANA_TLD_LIST_URL );
			if ( ! is_wp_error( $response ) ) {
				WP_Filesystem();
				$wp_filesystem->put_contents( $tld_file, wp_remote_retrieve_body( $response ) );
			}
		}
	} // maybe_update_tld_list

	/**
	 * Get the path to the local TLD list file.
	 *
	 * @since 2.1.0
	 *
	 * @return string Path to the TLD list file.
	 */
	private function get_tld_file() {
		return __DIR__ . '/data/' . basename( static::IANA_TLD_LIST_URL );
	} // get_tld_file

} // class Domain_Utils
