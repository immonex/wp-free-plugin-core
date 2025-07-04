<?php
/**
 * Class Local_FS_Utils
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\V2_4_1;

/**
 * Local filesystem related utilities.
 */
class Local_FS_Utils {

	/**
	 * Scan a folder and return its contents based the given params and flags.
	 *
	 * @since 1.8.0
	 *
	 * @param string|string[] $directories   Single directory or array of multiple directories to scan (absolute path(s)).
	 * @param mixed[]         $params        Query parameters/flags (optional)
	 *     $params = [
	 *         'scope'                       => 'files',        // "files" (default), "folders" or "files_and_folders"
	 *         'file_extensions'             => [],             // Array of file extensions to consider (case insensitive)
	 *         'exclude'                     => [],             // Names of files and folders that should be omitted
	 *         'apply_exclude_in_subfolders' => false,          // Consider folder exclude list in subfolders, too?
	 *         'exclude_regex'               => '',             // ...will be generated automatically
	 *         'mtime'                       => '',             // Filter by file modification time (e.g. ">2023-18-10" or "<1698924857")
	 *         'filenname_ts_mode'           => 'primary',      // Mode for evaluating filename-based timestamps: "primary" (default) or "only" (see get_mtime())
	 *         'max_depth'                   => 0,              // Maximum recursion level (0 = no recursion/subfolder processing)
	 *         'skip_dotfiles'               => true,           // Exclude dotfiles from returned lists?
	 *         'return_paths'                => false,          // Return results as path strings instead of objects?
	 *         'order_by'                    => 'filename asc', // Sort order (filename/basename/mtime + asc/desc)
	 *     ]
	 * @param int             $current_level Current subfolder recursion level (optional, default 0).
	 *
	 * @return \SplFileInfo[]|string[] Directory contents based on the given parameters.
	 */
	public function scan_dir( $directories, $params = [], $current_level = 0 ) {
		$defaults = [
			'scope'                       => 'files',
			'file_extensions'             => [],
			'exclude'                     => [],
			'apply_exclude_in_subfolders' => false,
			'exclude_regex'               => '',
			'mtime'                       => '',
			'filename_ts_mode'            => 'primary',
			'max_depth'                   => 0,
			'skip_dotfiles'               => true,
			'return_paths'                => false,
			'order_by'                    => 'filename asc',
		];
		$params   = array_merge( $defaults, $params );
		$files    = [];

		if ( ! is_array( $directories ) ) {
			$directories = [ $directories ];
		}

		foreach ( $directories as $dir ) {
			try {
				$it = new \FilesystemIterator( $dir );
			} catch ( \Exception $e ) {
				continue;
			}

			if ( is_string( $params['file_extensions'] ) ) {
				$params['file_extensions'] = array_map( 'trim', explode( ',', $params['file_extensions'] ) );
			}
			$params['file_extensions'] = array_map( 'strtolower', $params['file_extensions'] );

			if ( is_string( $params['exclude'] ) ) {
				$params['exclude'] = array_filter( array_map( 'trim', explode( ',', $params['exclude'] ) ) );
			}

			$exclude = array_filter( $params['exclude'] );
			if ( 0 === $current_level && ! empty( $exclude ) ) {
				$params['exclude_regex'] = $this->get_exclude_regex( $exclude );
			}

			$compare_operator = false;
			$compare_mtime    = false;
			if (
				is_string( $params['mtime'] )
				&& strlen( $params['mtime'] ) > 5
				&& in_array( $params['mtime'][0], [ '<', '>' ], true )
			) {
				$compare_operator = $params['mtime'][0];
				$compare_mtime    = substr( $params['mtime'], 1 );
				if ( ! is_numeric( $compare_mtime ) ) {
					$compare_mtime = strtotime( $compare_mtime );
				}
			}

			foreach ( $it as $path => $file_info ) {
				$is_dir   = $file_info->isDir();
				$filename = $file_info->getFilename();

				if ( ! $is_dir && 'folders' === $params['scope'] ) {
					continue;
				}

				if (
					in_array( $filename, $params['exclude'], true )
					&& ( ! $is_dir || ( 0 === $current_level || $params['apply_exclude_in_subfolders'] ) )
				) {
					continue;
				}

				if (
					( $params['exclude_regex'] && preg_match( $params['exclude_regex'], $filename ) )
					&& ( ! $is_dir || ( 0 === $current_level || $params['apply_exclude_in_subfolders'] ) )
				) {
					continue;
				}

				if ( ! $is_dir && $compare_mtime ) {
					$mtime = String_Utils::utc_to_local_time( $this->get_mtime( $file_info, $params['filename_ts_mode'] ) );

					if ( false === $mtime ) {
						continue;
					}

					if ( '<' === $compare_operator && $mtime >= $compare_mtime ) {
						continue;
					}

					if ( '>' === $compare_operator && $mtime <= $compare_mtime ) {
						continue;
					}
				}

				if ( $is_dir ) {
					if ( $current_level === $params['max_depth'] && 'files' === $params['scope'] ) {
						continue;
					}

					if ( 'files' !== $params['scope'] ) {
						$files[ $file_info->getRealPath() ] = $file_info;
					}

					if ( $current_level < $params['max_depth'] ) {
						$subfolder_files = $this->scan_dir( $path, $params, $current_level + 1 );
						$files           = array_merge( $files, $subfolder_files );
					}
					continue;
				}

				if ( '.' === $filename[0] && $params['skip_dotfiles'] ) {
					continue;
				}

				if (
					! empty( $params['file_extensions'] )
					&& ! in_array( strtolower( $file_info->getExtension() ), $params['file_extensions'], true )
				) {
					continue;
				}

				$files[ $file_info->getRealPath() ] = $file_info;
			}

			uasort(
				$files,
				function ( $a, $b ) use ( $params ) {
					return $this->compare_files( $a, $b, $params['order_by'] );
				}
			);
		}

		if (
			0 === $current_level
			&& is_array( $files )
			&& count( $files ) > 0
			&& $params['return_paths']
		) {
			return array_keys( $files );
		}

		return $files;
	} // scan_dir

	/**
	 * Compare two files (sort callback).
	 *
	 * @since 1.8.0
	 *
	 * @param \SplFileInfo    $a        File A.
	 * @param \SplFileInfo    $b        File B.
	 * @param string|string[] $order_by Sort order as string or array (filename/basename/mtime + asc/desc, optional).
	 *
	 * @return int Comparison result (-1/0/1).
	 */
	private function compare_files( $a, $b, $order_by = [ 'filename', 'asc' ] ) {
		if ( is_string( $order_by ) ) {
			$order_by = explode( ' ', $order_by );
			if ( 1 === count( $order_by ) ) {
				$order_by[] = 'asc';
			}
		}

		if ( 'mtime' === $order_by[0] ) {
			$ac = self::get_mtime( $a );
			$bc = self::get_mtime( $b );

			if ( ! $ac || ! $bc ) {
				// Fallback comparison.
				$ac = $a->getRealPath();
				$bc = $b->getRealPath();
			}
		} elseif ( 'basename' === $order_by[0] ) {
			$ac = $a->getBasename();
			$bc = $b->getBasename();
		} else {
			$ac = $a->getRealPath();
			$bc = $b->getRealPath();
		}

		if ( $ac === $bc ) {
			return 0;
		}

		if ( 'desc' === $order_by[1] ) {
			return $ac > $bc ? -1 : 1;
		} else {
			return $ac > $bc ? 1 : -1;
		}
	} // compare_files

	/**
	 * Extract or create regular expressions for file/folder filtering.
	 *
	 * @since 1.8.0
	 *
	 * @param string[] $filter_list Filter keyword and/or expression list.
	 *
	 * @return string RegEx or empty string.
	 */
	private function get_exclude_regex( &$filter_list ) {
		$exclude_regex    = '';
		$full_regex_found = false;

		foreach ( $filter_list as $i => $expr ) {
			if ( empty( $expr ) ) {
				continue;
			}

			$first_char = $expr[0];
			$last_char  = substr( $expr, -1 );

			if ( '//' === $first_char . $last_char ) {
				unset( $filter_list[ $i ] );
				$exclude_regex    = $expr;
				$full_regex_found = true;
			}

			/**
			 * Convert wildcard characters (*) to regular expressions.
			 */

			if ( '*' === $first_char ) {
				if ( ! $full_regex_found ) {
					$exclude_regex .= wp_sprintf( '((%s)$)|', substr( $expr, 1 ) );
				}
				unset( $filter_list[ $i ] );
			} elseif ( '*' === $last_char ) {
				if ( ! $full_regex_found ) {
					$exclude_regex .= wp_sprintf( '(^(%s))|', substr( $expr, 0, -1 ) );
				}
				unset( $filter_list[ $i ] );
			}
		}

		if ( $exclude_regex && ! $full_regex_found ) {
			$exclude_regex = '/' . rtrim( $exclude_regex, '|' ) . '/';
		}

		return $exclude_regex;
	} // get_exclude_regex

	/**
	 * Get a file's last modification time, either based on a date/time statement in the
	 * filename or its filesystem modification (content, primary) or change (fallback) time.
	 *
	 * @since 1.8.0
	 *
	 * @param \SplFileInfo|string $file             File object or full path.
	 * @param string              $filename_ts_mode Mode for evaluating filename-based timestamps:
	 *                                              "primary" (default), "only" or empty string (fallback only).
	 *
	 * @return int|bool UNIX Timestamp of last modification or false on error.
	 */
	public function get_mtime( $file, $filename_ts_mode = 'primary' ) {
		if ( ! $file instanceof \SplFileInfo ) {
			$file = new \SplFileInfo( $file );
		}

		if ( ! $file->isFile() ) {
			return false;
		}

		$filename_ts = String_Utils::get_leading_timestamp( $file->getBasename() );
		if ( false !== $filename_ts && ! empty( $filename_ts_mode ) ) {
			return $filename_ts;
		}

		if ( 'only' === $filename_ts_mode ) {
			return false;
		}

		$mtime = $file->getMTime();
		if ( $mtime ) {
			return $mtime;
		}

		$mtime = $file->getCTime();
		if ( $mtime ) {
			// Return change time as fallback value.
			return $mtime;
		}

		return false;
	} // get_mtime

	/**
	 * Check all directory paths in the given list, filter out nonexistent and maybe
	 * add required ones.
	 *
	 * @since 1.8.0
	 *
	 * @param string[]      $folders  List of directory paths.
	 * @param bool|string[] $default_folders Optional default array if $folders list is empty or false
	 *                                       if empty lists are allowed.
	 * @param string[]      $required Optional list of required directory paths.
	 *
	 * @return string[] Filtered list of directory paths.
	 */
	public function validate_dir_list( $folders, $default_folders = false, $required = [] ) {
		if ( ! is_array( $folders ) ) {
			$folders = [ $folders ];
		}

		foreach ( $folders as $i => $path ) {
			if ( ! is_string( $path ) || ! $path || ! is_dir( $path ) ) {
				unset( $folders[ $i ] );
			}
		}

		if ( false !== $default_folders && empty( $folders ) ) {
			$folders = $default_folders;
		}

		if ( ! empty( $required ) ) {
			foreach ( $required as $path ) {
				if ( ! in_array( $path, $folders, true ) ) {
					$folders[] = $path;
				}
			}
		}

		return array_values( array_unique( $folders ) );
	} // validate_dir_list

	/**
	 * Get the plugin base directory (URL-based approach as recommended in the
	 * WP development guidelines).
	 *
	 * @since 2.2.1
	 *
	 * @return string Plugin base directory path.
	 */
	public function get_plugin_base_dir() {
		if ( defined( 'WP_SITEURL' ) ) {
			$base_url = WP_SITEURL;
		} else {
			$base_url = get_option( 'siteurl' );
		}

		$base_url    = preg_replace( '/^https?:\/\//', '', $base_url );
		$plugins_url = preg_replace( '/^https?:\/\//', '', plugins_url() );

		return str_replace( trailingslashit( $base_url ), ABSPATH, $plugins_url );
	} // get_plugin_base_dir

} // class Local_FS_Utils
