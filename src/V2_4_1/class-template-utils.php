<?php
/**
 * Class Template_Utils
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\V2_4_1;

/**
 * Utility methods for a very simple kind of templating.
 */
class Template_Utils {

	const INVALID_SKIN_FOLDER_NAMES = [ 'core' ];

	/**
	 * Main plugin instance
	 *
	 * @var Base
	 */
	private $plugin;

	/**
	 * Folder name of current skin
	 *
	 * @var string
	 */
	private $skin;

	/**
	 * Folders and display names of available skins
	 *
	 * @var bool|string[]
	 */
	private $frontend_skins = false;

	/**
	 * Environment for rendering Twig templates
	 *
	 * @var \Twig\Environment
	 */
	public $twig;

	/**
	 * Constructor: Import some required objects/values.
	 *
	 * @since 0.8.3
	 *
	 * @param Base $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	} // __construct

	/**
	 * Fetch a Twig template file and return its rendered content.
	 *
	 * @since 1.3.0
	 *
	 * @param string $filename Template filename (auto locate).
	 * @param array  $template_data Array with any output related contents (optional).
	 *
	 * @return string|bool Parsed template content or false if not found or
	 *                     Twig is unavailable.
	 */
	public function render_twig_template( $filename, $template_data = [] ) {
		if ( ! pathinfo( basename( $filename ), PATHINFO_EXTENSION ) ) {
			$filename .= '.twig';
		}

		$template_data = apply_filters( 'immonex_core_template_data', $template_data, $this->plugin->plugin_slug );
		$template_file = $this->locate_template_file( $filename, $template_data );

		if ( ! $template_file ) {
			return false;
		}

		$twig = $this->get_twig();
		if ( ! $twig ) {
			return false;
		}

		try {
			$twig->getLoader()->setPaths( dirname( $template_file ) );
			$output = $twig->render( basename( $template_file ), $template_data );
		} catch ( \Exception $e ) {
			$output = false;
		}

		return $output;
	} // render_twig_template

	/**
	 * Render a Twig template string.
	 *
	 * @since 1.3.4
	 *
	 * @param string $template_string Template string.
	 * @param array  $template_data Array with any output related contents (optional).
	 *
	 * @return string Parsed template content.
	 */
	public function render_twig_template_string( $template_string, $template_data = [] ) {
		if ( ! is_string( $template_string ) || ! trim( $template_string ) ) {
			return '';
		}

		$twig = $this->get_twig();
		if ( ! $twig ) {
			return '';
		}

		$template_data = apply_filters( 'immonex_core_template_data', $template_data, $this->plugin->plugin_slug );
		$template      = $twig->createTemplate( $template_string );

		return $template->render( $template_data );
	} // render_twig_template_string

	/**
	 * Fetch a PHP template file and return its rendered content via output buffering.
	 *
	 * @since 0.8.3
	 *
	 * @param string $filename Template filename (without path).
	 * @param array  $template_data Array with any output related contents (optional).
	 * @param array  $utils Array of helper objects for output/formatting (optional).
	 *
	 * @return string|bool Parsed template content or false if not found.
	 */
	public function render_php_template( $filename, $template_data = [], $utils = [] ) {
		$template_file = $this->locate_template_file( $filename, $template_data );
		if ( ! $template_file ) {
			return false;
		}

		$template_data = apply_filters( 'immonex_core_template_data', $template_data, $this->plugin->plugin_slug );

		// Alternative variable name for compatibility with legacy templates.
		$template_vars = $template_data;

		/**
		 * Render template content by output buffering.
		 */
		ob_start();
		include $template_file;
		$rendered_content = ob_get_contents();
		ob_end_clean();

		return $rendered_content;
	} // render_php_template

	/**
	 * Fetch a (simple) template file and replace template variables by given values.
	 *
	 * @since 0.8.3
	 *
	 * @param string   $filename Template filename (without path).
	 * @param string[] $template_data Associative array with variable names/contents.
	 *
	 * @return string|bool Parsed template content or false if not found.
	 */
	public function parse_template( $filename, $template_data = [] ) {
		$template = $this->fetch_template( $filename );
		if ( ! $template ) {
			return false;
		}

		$template_data = apply_filters( 'immonex_core_template_data', $template_data, $this->plugin->plugin_slug );

		if ( is_array( $template_data ) && count( $template_data ) > 0 ) {
			foreach ( $template_data as $var_name => $value ) {
				$template = str_replace( "[$var_name]", $value, $template );
			}
		}

		return $template;
	} // parse_template

	/**
	 * Load a given template.
	 *
	 * @since 0.8.3
	 *
	 * @param string $filename Template filename (without path).
	 *
	 * @return string|bool Template content or false if not found.
	 */
	public function fetch_template( $filename ) {
		$file = $this->locate_template_file( $filename );
		if ( ! $file ) {
			return false;
		}

		return $this->plugin->fs->get_contents( $file );
	} // fetch_template

	/**
	 * Locate a plugin template file (default: child theme dir > theme dir > plugin dir).
	 *
	 * @since 0.8.3
	 *
	 * @param string|string[] $filenames Template filename(s).
	 * @param string[]        $add_folders Additional search folders (absolute paths).
	 * @param string          $add_folder_mode Where to insert the additional folders (before, after or override).
	 * @param string|bool     $force_skin Temporary use the given skin instead of the main one.
	 *
	 * @return string|bool Full template file path or false if not found.
	 */
	public function locate_template_file( $filenames, $add_folders = [], $add_folder_mode = 'before', $force_skin = false ) {
		if ( empty( $filenames ) ) {
			return false;
		}

		if ( ! is_array( $filenames ) ) {
			$filenames = [ $filenames ];
		}

		$dirsep = array_unique( [ DIRECTORY_SEPARATOR, '/' ] );

		foreach ( $filenames as $i => $filename ) {
			if ( ! is_string( $filename ) ) {
				unset( $filenames[ $i ] );
				continue;
			}

			$full_ext = preg_match( '/\.([a-z.]+)$/i', $filename, $matches );

			if ( ! $full_ext ) {
				$filename       .= '.php';
				$filenames[ $i ] = $filename;
			}

			if (
				( in_array( $filename[0], $dirsep, true ) || preg_match( '/^[A-Z]:/', $filename ) )
				&& file_exists( $filename )
			) {
				// Return given absolute path of an existing file.
				return $filename;
			}
		}

		if ( empty( $filenames ) ) {
			return false;
		}

		$localized_filenames = [];

		foreach ( $filenames as $filename ) {
			$full_ext  = preg_match( '/\.([a-z.]+)$/i', $filename, $matches );
			$ext_regex = str_replace( '.', '\\.', $matches[1] );

			if ( preg_match( '/[\[]?-([a-z]{2}(_[A-Z]{2})?(_(in)?formal)?)[\]]?\.' . $ext_regex . '/', $filename, $matches ) ) {
				$locale              = $matches[1];
				$localized_filenames = array_merge(
					$localized_filenames,
					array( str_replace( $matches[0], "-{$locale}", $filename ) )
				);

				if ( preg_match( '/_(in)?formal/', $locale ) ) {
					$lang_country          = preg_replace( '/_(in)?formal/', '', $locale );
					$localized_filenames[] = str_replace( $matches[0], "-{$lang_country}", $filename );
				}
				if ( preg_match( '/[a-z]{2}_[A-Z]{2}/', $locale ) ) {
					$lang                  = substr( $locale, 0, 2 );
					$localized_filenames[] = str_replace( $matches[0], "-{$lang}", $filename );
				}
				$localized_filenames[] = str_replace( $matches[0], '', $filename );
			} else {
				$localized_filenames = array_merge(
					$localized_filenames,
					array( $filename )
				);
			}
		}

		if ( is_array( $add_folders ) && ! empty( $add_folders['template_folders'] ) ) {
			$add_folders = $add_folders['template_folders'];
			if ( ! is_array( $add_folders ) ) {
				$add_folders = [ $add_folders ];
			}
		} else {
			$add_folders = [];
		}
		if ( empty( $add_folder_mode ) ) {
			$add_folder_mode = 'before';
		}

		$skin_restore  = $force_skin ? $this->set_skin( $force_skin, false ) : false;
		$template_file = false;

		if (
			'override' === $add_folder_mode
			&& is_array( $add_folders )
			&& count( $add_folders ) > 0
		) {
			$search_folders = $add_folders;
		} else {
			$search_folders = $this->get_default_template_folders();
		}

		$search_folders = array_unique( $search_folders );
		if (
			is_array( $add_folders )
			&& count( $add_folders ) > 0
			&& 'override' !== $add_folder_mode
		) {
			if ( 'before' === $add_folder_mode ) {
				$search_folders = array_merge( $add_folders, $search_folders );
			} elseif ( 'after' === $add_folder_mode ) {
				$search_folders = array_merge( $search_folders, $add_folders );
			}
		}

		foreach ( $search_folders as $folder ) {
			if ( ! is_string( $folder ) ) {
				continue;
			}

			foreach ( $localized_filenames as $filename ) {
				$file = trailingslashit( $folder ) . $filename;

				if ( file_exists( $file ) ) {
					$template_file = $file;
					break 2;
				}
			}
		}

		if ( $skin_restore ) {
			$this->set_skin( $skin_restore, false );
		}

		return $template_file;
	} // locate_template_file

	/**
	 * Return the default folders where plugin template files should reside.
	 *
	 * @since 0.8.3
	 *
	 * @param bool $reverse_order Reverse folder order if required (optional, default false).
	 * @param bool $include_skin_folders Include "skin" subfolders, if a skin name is given (optional, default true).
	 * @param bool $existing_only Return existing folders only (optional, default false).
	 *
	 * @return string[] Array of default template folders.
	 */
	public function get_default_template_folders( $reverse_order = false, $include_skin_folders = true, $existing_only = false ) {
		$skin_folders    = [];
		$default_folders = [
			trailingslashit( get_stylesheet_directory() ) . $this->plugin->plugin_slug,
			trailingslashit( get_template_directory() ) . $this->plugin->plugin_slug,
			trailingslashit( $this->plugin->plugin_dir ) . 'skins',
			trailingslashit( $this->plugin->plugin_dir ) . 'templates',
		];

		$base_folders = apply_filters(
			// @codingStandardsIgnoreLine
			"{$this->plugin->plugin_prefix}template_search_folders",
			array_unique( $default_folders )
		);

		if ( empty( $base_folders ) ) {
			$base_folders = $default_folders;
		}

		if ( $include_skin_folders && $this->skin ) {
			foreach ( $base_folders as $folder ) {
				$skin_folders[] = trailingslashit( $folder ) . $this->skin;
			}
		}

		$default_template_folders = array_unique(
			array_merge(
				$skin_folders,
				$base_folders
			)
		);

		if ( $existing_only ) {
			foreach ( $default_template_folders as $key => $folder ) {
				if ( ! file_exists( $folder ) ) {
					unset( $default_template_folders[ $key ] );
				}
			}
		}

		return $reverse_order ? array_reverse( $default_template_folders ) : $default_template_folders;
	} // get_default_template_folders

	/**
	 * Return the URL of a template file.
	 *
	 * @since 0.8.3
	 *
	 * @param string $file Full or relative file path.
	 *
	 * @return string|bool File URL or false if nonexistent or indeterminable.
	 */
	public function get_template_file_url( $file ) {
		if ( empty( $file ) ) {
			return false;
		}

		if ( DIRECTORY_SEPARATOR !== $file[0] && ':' !== $file[1] ) {
			$file = $this->locate_template_file( $file );

			if ( ! $file ) {
				return false;
			}
		}

		if ( WP_CONTENT_DIR === substr( $file, 0, strlen( WP_CONTENT_DIR ) ) ) {
			$rel_path = substr( $file, strlen( WP_CONTENT_DIR ) );

			if ( '/' !== DIRECTORY_SEPARATOR ) {
				$rel_path = str_replace( DIRECTORY_SEPARATOR, '/', $rel_path );
			}

			return content_url( $rel_path );
		}

		$folder_mappings = apply_filters(
			// @codingStandardsIgnoreLine
			"{$this->plugin->plugin_prefix}template_folder_url_mappings",
			array()
		);

		if ( empty( $folder_mappings ) || ! is_array( $folder_mappings ) ) {
			return false;
		}

		foreach ( $folder_mappings as $fs_folder => $htdocs_folder_or_url ) {
			$fs_folder = trailingslashit( $fs_folder );

			if ( substr( $file, 0, strlen( $fs_folder ) ) === $fs_folder ) {
				$rel_path = substr( $file, strlen( $fs_folder ) );
				$base_url = 'http' === substr( $htdocs_folder_or_url, 0, 4 ) ?
					$htdocs_folder_or_url : home_url( $htdocs_folder_or_url );

				return trailingslashit( $base_url ) . $rel_path;
			}
		}

		return false;
	} // get_template_file_url

	/**
	 * Return a key/value pair string, if value is set.
	 *
	 * @since 0.8.3
	 *
	 * @param string $label Label (key).
	 * @param mixed  $value Value.
	 * @param string $wrap Format string with placeholders (optional).
	 *
	 * @return string|bool Formatted key/value string or false if no value is given.
	 */
	public function format_key_value_if_set( $label, $value, $wrap = "[label]: [value]\n" ) {
		if ( ! $value ) {
			return '';
		}

		$content = str_replace( '[label]', $label, $wrap );
		$content = str_replace( '[value]', $value, $content );

		if ( ': ' === substr( $content, 0, 2 ) ) {
			$content = substr( $content, 2 );
		}

		return $content;
	} // format_key_value_if_set

	/**
	 * Convert template variables to strings usable in HTML tags.
	 *
	 * @since 0.8.3
	 *
	 * @param mixed[]         $template_data Array of template variables.
	 * @param string|string[] $template_var String (variable name) or array with variable levels
	 *   and variable name as last value inside.
	 * @param string          $attr_name Attribute name for output.
	 *
	 * @return string Insertable attribute string.
	 */
	public function get_attr_from_template_var( $template_data, $template_var, $attr_name ) {
		$template_data = apply_filters( 'immonex_core_template_data', $template_data, $this->plugin->plugin_slug );

		$value = $this->get_template_var( $template_data, $template_var );
		return $value ? wp_sprintf( ' %s="%s"', esc_html( $attr_name ), esc_html( $value ) ) : '';
	} // get_attr_from_template_var

	/**
	 * Return the value a specific template variable.
	 *
	 * @since 0.8.3
	 *
	 * @param mixed[]         $template_data Array of template variables.
	 * @param string|string[] $template_var String (variable name) or array with variable levels
	 *   and variable name as last value inside.
	 *
	 * @return string|bool Variable value or false if nonexistent.
	 */
	public function get_template_var( $template_data, $template_var ) {
		$template_data = apply_filters( 'immonex_core_template_data', $template_data, $this->plugin->plugin_slug );

		if ( is_array( $template_var ) ) {
			if ( 0 === count( $template_var ) ) {
				return false;
			}
			$current_level = $template_data;
			$last_key      = array_pop( $template_var );

			foreach ( $template_var as $array_level_name ) {
				if ( isset( $current_level[ $array_level_name ] ) ) {
					$current_level = $current_level[ $array_level_name ];
				} else {
					return false;
				}
			}

			return isset( $current_level[ $last_key ] ) ? $current_level[ $last_key ] : false;
		} else {
			return isset( $template_data[ $template_var ] ) ? $template_data[ $template_var ] : false;
		}
	} // get_template_var

	/**
	 * Generate a list of available frontend "skins".
	 *
	 * @since 0.9
	 *
	 * @return string[] Array of skins (folder basename => display name).
	 */
	public function get_frontend_skins() {
		if ( false !== $this->frontend_skins ) {
			return $this->frontend_skins;
		}

		$template_folders = $this->get_default_template_folders( true, false );

		$folders = [];
		foreach ( $template_folders as $folder ) {
			$temp_folders = glob( trailingslashit( "$folder/*" ), GLOB_ONLYDIR );

			if ( is_array( $temp_folders ) && count( $temp_folders ) > 0 ) {
				foreach ( $temp_folders as $temp_folder ) {
					if ( ! in_array( basename( $temp_folder ), self::INVALID_SKIN_FOLDER_NAMES, true ) ) {
						$folders[ basename( $temp_folder ) ] = $temp_folder;
					}
				}
			}
		}

		$named_folders = [];
		if ( is_array( $folders ) && count( $folders ) > 0 ) {
			foreach ( $folders as $name => $path ) {
				$index_file = $this->locate_template_file( 'index.php', [], 'before', $name );

				if ( file_exists( $index_file ) ) {
					// Extract theme name from index.php (if existent).
					$index_contents = $this->plugin->fs->get_contents( $index_file );
					/* Skin Name: Quiwi */
					$name_exists = preg_match( '/ \* Skin Name: ([a-zA-Z0-9 -_\.,]+)\n/', $index_contents, $matches );
					$skin_name   = $name_exists ? $matches[1] : $name;

					if ( in_array( $skin_name, array_values( $named_folders ), true ) ) {
						$skin_name .= " [$name]";
					}
				} else {
					$skin_name = $name;
				}

				if ( 'default' === $name ) {
					$skin_name .= ' (' . __( 'default', 'immonex-wp-free-plugin-core' ) . ')';
				}

				$named_folders[ $name ] = $skin_name;
			}
		}

		$this->frontend_skins = $named_folders;

		return $named_folders;
	} // get_frontend_skins

	/**
	 * Set the current "skin" folder and (possibly) return the previous one.
	 *
	 * @since 0.9
	 *
	 * @param string $skin              New skin key (equals folder name).
	 * @param bool   $check_skin_folder If true (default), check if the skin folder exists (optional).
	 *
	 * @return string|bool Previous skin key or false if not set or changed.
	 */
	public function set_skin( $skin, $check_skin_folder = true ) {
		if ( empty( $this->skin ) ) {
			$this->skin = $this->plugin->skin;
		}

		if (
			$skin
			&& (
				! $check_skin_folder
				|| in_array( $skin, array_keys( $this->get_frontend_skins() ), true )
			)
			&& $skin !== $this->skin
		) {
			$previous_skin = $this->skin;
			$this->skin    = $skin;

			return $previous_skin;
		}

		return false;
	} // set_skin

	/**
	 * Generate a list of pages based on the given arguments.
	 *
	 * @since 0.9
	 *
	 * @param mixed[] $args Page query constraints
	 *   (see https://codex.wordpress.org/Function_Reference/get_pages).
	 *
	 * @return string[] Array of pages (ID => Title).
	 */
	public function get_page_list( $args = [] ) {
		if ( empty( $args ) ) {
			$args = [
				'post_status' => [ 'publish', 'private' ],
			];
		}

		$all_pages = get_pages( $args );
		$pages     = [];

		if ( is_array( $all_pages ) && count( $all_pages ) > 0 ) {
			foreach ( $all_pages as $page ) {
				$pages[ $page->ID ] = $page->post_title;
			}
		}

		return $pages;
	} // get_page_list

	/**
	 * Add some defaults to the array of variables to be replaced during
	 * template rendering (filter callback).
	 *
	 * @since 1.9.0
	 *
	 * @param mixed[] $template_data Template variables (key-value array).
	 * @param string  $plugin_slug   Related plugin slug.
	 *
	 * @return mixed[] Extended template data array.
	 */
	public function add_default_template_data( $template_data, $plugin_slug ) {
		if ( ! is_array( $template_data ) ) {
			$template_data = [];
		}

		$defaults = [
			'site_title' => get_bloginfo( 'name' ),
			'site_url'   => home_url(),
		];

		return array_merge(
			$defaults,
			$template_data
		);
	} // add_default_template_data

	/**
	 * Create and return a Twig Environment instance.
	 *
	 * @since 1.3.0
	 *
	 * @return \Twig\Environment Twig Environment object.
	 */
	private function get_twig() {
		if ( ! empty( $this->twig ) ) {
			return $this->twig;
		}

		$this->twig = new \Twig\Environment(
			new \Twig\Loader\FilesystemLoader(),
			array(
				'autoescape' => false,
			)
		);

		return $this->twig;
	} // get_twig

} // Template_Utils
