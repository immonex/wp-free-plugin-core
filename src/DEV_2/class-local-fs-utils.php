<?php
/**
 * Class Local_FS_Utils
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\DEV_2;

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
				function( $a, $b ) use ( $params ) {
					return $this->compare_files( $a, $b, $params['order_by'] );
				}
			);
		}

		if (
			0 === $current_level
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
	 * @param string[] $list Filter keyword and/or expression list.
	 *
	 * @return string RegEx or empty string.
	 */
	private function get_exclude_regex( &$list ) {
		$exclude_regex    = '';
		$full_regex_found = false;

		foreach ( $list as $i => $expr ) {
			if ( empty( $expr ) ) {
				continue;
			}

			$first_char = $expr[0];
			$last_char  = substr( $expr, -1 );

			if ( '//' === $first_char . $last_char ) {
				unset( $list[ $i ] );
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
				unset( $list[ $i ] );
			} elseif ( '*' === $last_char ) {
				if ( ! $full_regex_found ) {
					$exclude_regex .= wp_sprintf( '(^(%s))|', substr( $expr, 0, -1 ) );
				}
				unset( $list[ $i ] );
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
	 * @param bool|string[] $default  Optional default array if $folders list is empty or false
	 *                                if empty lists are allowed.
	 * @param string[]      $required Optional list of required directory paths.
	 *
	 * @return string[] Filtered list of directory paths.
	 */
	public function validate_dir_list( $folders, $default = false, $required = array() ) {
		if ( ! is_array( $folders ) ) {
			$folders = array( $folders );
		}

		foreach ( $folders as $i => $path ) {
			if ( ! is_string( $path ) || ! $path || ! is_dir( $path ) ) {
				unset( $folders[ $i ] );
			}
		}

		if ( false !== $default && empty( $folders ) ) {
			$folders = $default;
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

} // class Local_FS_Utils
