<?php
/**
 * This file contains a utility class for simple templating.
 */
namespace immonex\WordPressFreePluginCore\V1_0;

/**
 * Utility methods for a very simple kind of templating.
 *
 * @package immonex-wp-free-plugin-core
 */
class Template_Utils {

	const
		INVALID_SKIN_FOLDER_NAMES = array( 'core' );

	/** @var \immonex\WordPressFreePluginCore\V0_9\Base Main plugin instance */
	private $plugin;

	/** @var string Folder name of current skin */
	private $skin;

	/** @var string[] Folders and display names of available skins */
	private $frontend_skins = array();

	/**
	 * Constructor: Import some required objects/values.
	 *
	 * @since 0.8.3
	 *
	 * @param \immonex\WordPressFreePluginCore\V0_9\Base $plugin Main plugin object.
	 * @param string $skin Skin subfolder name.
	 *
	 * @return string/bool Full template file path or false if not found.
	 */
	public function __construct( $plugin, $skin = '' ) {
		$this->plugin = $plugin;
		$this->set_skin( $skin );
	} // __construct

	/**
	 * Fetch a PHP template file and return it's rendered content via output buffering.
	 *
	 * @since 0.8.3
	 *
	 * @param string $filename Template filename (without path).
	 * @param array $template_data Array with any output related contents.
	 * @param array $utils Array of helper objects for output/formatting.
	 *
	 * @return string/bool Parsed template content or false if not found.
	 */
	public function render_php_template( $filename, $template_data, $utils = array() ) {
		$add_template_folders = isset( $template_data['template_folders'] ) ?
			$template_data['template_folders'] : array();
		if ( ! is_array( $add_template_folders ) ) {
			$add_template_folders = array( $add_template_folders );
		}
		$template_file = $this->locate_template_file( $filename, $add_template_folders );
		if ( ! $template_file ) return false;

		/**
		 * Render template content by output buffering.
		 */
		ob_start();
		include( $template_file );
		$rendered_content = ob_get_contents();
		ob_end_clean();

		return $rendered_content;
	} // render_php_template

	/**
	 * Fetch a (simple) template file and replace template variables by given values.
	 *
	 * @since 0.8.3
	 *
	 * @param string $filename Template filename (without path).
	 * @param string[] $template_data Associative array with variable names/contents.
	 *
	 * @return string/bool Parsed template content or false if not found.
	 */
	public function parse_template( $filename, $template_data = array() ) {
		$template = $this->fetch_template( $filename );
		if ( ! $template ) return false;

		if ( count( $template_data ) > 0 ) {
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
	 * @return string/bool Template content or false if not found.
	 */
	public function fetch_template( $filename ) {
		$file = $this->locate_template_file( $filename );
		if ( ! $file ) return false;

		return file_get_contents( $file );
	} // fetch_template

	/**
	 * Locate a plugin template file (default: child theme dir > theme dir > plugin dir).
	 *
	 * @since 0.8.3
	 *
	 * @param string $filename Template filename.
	 * @param string[] $add_folders Additional search folders (absolute paths).
	 * @param string $add_folder_mode Where to insert the additional folders (before, after or override).
	 * @param string|bool $force_skin Temporary use the given skin instead of the main one.
	 *
	 * @return string/bool Full template file path or false if not found.
	 */
	public function locate_template_file( $filename, $add_folders = array(), $add_folder_mode = 'before', $force_skin = false ) {
		if ( ! is_string( $filename ) ) return false;

		if ( empty( $add_folders ) ) $add_folders = array();
		if ( empty( $add_folder_mode ) ) $add_folder_mode = 'before';

		if ( $force_skin ) {
			$skin_restore = $this->skin;
			$this->skin = $force_skin;
		}

		$template_file = false;
		$path_parts = pathinfo( $filename );
		if ( ! isset( $path_parts['extension'] ) || ! $path_parts['extension'] ) $filename .= '.php';

		if (
			'override' === $add_folder_mode &&
			count( $add_folders ) > 0
		) {
			$search_folders = $add_folders;
		} else {
			$search_folders = $this->get_default_template_folders();
		}

		$search_folders = array_unique( $search_folders );

		if ( count( $add_folders ) > 0 ) {
			if ( 'before' === $add_folder_mode )
				$search_folders = array_merge( $add_folders, $search_folders );
			elseif ( 'after' === $add_folder_mode )
				$search_folders = array_merge( $search_folders, $add_folders );
		}

		foreach ( $search_folders as $folder ) {
			$file = trailingslashit( $folder ) . $filename;
			if ( file_exists( $file ) ) {
				$template_file = $file;
				break;
			}
		}

		if ( isset( $skin_restore ) ) {
			$this->skin = $skin_restore;
		}

		return $template_file;
	} // locate_template_file

	/**
	 * Return the default folders where plugin template files should reside.
	 *
	 * @since 0.8.3
	 *
	 * @param bool $reverse_order Reverse folder order if required.
	 * @param bool $include_skin_folders Include "skin" subfolders, if a skin name is given.
	 *
	 * @return string[] Array of default template folders.
	 */
	public function get_default_template_folders( $reverse_order = false, $include_skin_folders = true ) {
		if ( $include_skin_folders && $this->skin ) {
			$skin_folders = array(
				trailingslashit( get_stylesheet_directory() ) . $this->plugin->plugin_slug . '/' . $this->skin,
				trailingslashit( get_template_directory() ) . $this->plugin->plugin_slug . '/' . $this->skin,
				trailingslashit( $this->plugin->plugin_dir ) . 'skins/' . $this->skin,
				trailingslashit( $this->plugin->plugin_dir ) . 'templates/' . $this->skin
			);
		} else {
			$skin_folders = array();
		}

		$default_template_folders = array_merge(
			$skin_folders,
			array(
				trailingslashit( get_stylesheet_directory() ) . $this->plugin->plugin_slug,
				trailingslashit( get_template_directory() ) . $this->plugin->plugin_slug,
				trailingslashit( $this->plugin->plugin_dir ) . 'skins',
				trailingslashit( $this->plugin->plugin_dir ) . 'templates'
			)
		);

		$default_template_folders = array_unique( $default_template_folders );

		return $reverse_order ? array_reverse( $default_template_folders ) : $default_template_folders;
	} // get_default_template_folders

	/**
	 * Return the URL of a template file.
	 *
	 * @since 0.8.3
	 *
	 * @param string $file Full file path.
	 *
	 * @return string File URL.
	 */
	public function get_template_file_url( $file ) {
		$path_parts = pathinfo( $file );

		$file_dir = isset( $path_parts['dirname'] ) ? $path_parts['dirname'] : '';
		$theme_dir = trailingslashit( get_template_directory() ) . $this->plugin->plugin_slug;
		$child_theme_dir = trailingslashit( get_stylesheet_directory() ) . $this->plugin->plugin_slug;
		$template_root_folder_name = false === strpos( $file, '/skins/' ) ? 'templates' : 'skins';
		$plugin_dir = trailingslashit( $this->plugin->plugin_dir ) . $template_root_folder_name;

		if ( substr( $file_dir, 0, strlen( $theme_dir ) ) === $theme_dir ) {
			// Theme folder.
			$file_part = substr( $file, strlen( $theme_dir ) );
			return trailingslashit( get_template_directory_uri() ) . $this->plugin->plugin_slug . $file_part;
		} elseif ( substr( $file_dir, 0, strlen( $child_theme_dir ) ) === $child_theme_dir ) {
			// Child theme folder.
			$file_part = substr( $file, strlen( $child_theme_dir ) );
			return trailingslashit( get_stylesheet_directory_uri() ) . $this->plugin->plugin_slug . $file_part;
		} elseif ( substr( $file_dir, 0, strlen( $plugin_dir ) ) === $plugin_dir ) {
			// Plugin folder.
			$file_part = substr( $file, strlen( $plugin_dir ) );
			return plugins_url( $template_root_folder_name . '/' . $file_part,
				$this->plugin->plugin_dir . '/' . $this->plugin->plugin_slug . '.php' );
		}

		return false;
	} // get_template_file_url

	/**
	 * Return a key/value pair string, if value is set.
	 *
	 * @since 0.8.3
	 *
	 * @param string $label Label (key).
	 * @param mixed $value Value.
	 * @param string $wrap Format string with placeholders (optional).
	 *
	 * @return string|bool Formatted key/value string or false if no value is given.
	 */
	public function format_key_value_if_set( $label, $value, $wrap = "[label]: [value]\n" ) {
		if ( ! $value ) return '';

		$content = str_replace( '[label]', $label, $wrap );
		$content = str_replace( '[value]', $value, $content );

		if ( ': ' === substr( $content, 0, 2 ) ) $content = substr( $content, 2 );

		return $content;
	} // format_key_value_if_set

	/**
	 * Convert template variables to strings usable in HTML tags.
	 *
	 * @since 0.8.3
	 *
	 * @param mixed[] $template_data Array of template variables.
	 * @param string|string[] $var String (variable name) or array with variable levels
	 *   and variable name as last value inside.
	 * @param string $attr_name Attribute name for output.
	 *
	 * @return string Insertable attribute string.
	 */
	public function get_attr_from_template_var( $template_data, $var, $attr_name ) {
		$value = $this->get_template_var( $template_data, $var );
		return $value ? wp_sprintf( ' %s="%s"', esc_html( $attr_name ), esc_html( $value ) ) : '';
	} // get_attr_from_template_var

	/**
	 * Return the value a specific template variable.
	 *
	 * @since 0.8.3
	 *
	 * @param mixed[] $template_data Array of template variables.
	 * @param string|string[] $var String (variable name) or array with variable levels
	 *   and variable name as last value inside.
	 *
	 * @return string|bool Variable value or false if nonexistent.
	 */
	public function get_template_var( $template_data, $var ) {
		if ( is_array( $var ) ) {
			if ( 0 === count( $var ) ) return false;
			$current_level = $template_data;
			$last_key = array_pop( $var );

			foreach ( $var as $array_level_name ) {
				if ( isset( $current_level[$array_level_name] ) ) $current_level = $current_level[$array_level_name];
				else return false;
			}

			return isset( $current_level[$last_key] ) ? $current_level[$last_key] : false;
		} else {
			return isset( $template_data[$var] ) ? $template_data[$var] : false;
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
		if ( ! empty( $this->frontend_skins ) ) {
			return $this->frontend_skins;
		}

		$template_folders = $this->get_default_template_folders( true, false );

		$folders = array();
		foreach ( $template_folders as $folder ) {
			$temp_folders = glob( trailingslashit( "$folder/*" ), GLOB_ONLYDIR );

			if ( count( $temp_folders ) > 0 ) {
				foreach ( $temp_folders as $temp_folder ) {
					if ( ! in_array( basename( $temp_folder ), self::INVALID_SKIN_FOLDER_NAMES ) ) {
						$folders[basename( $temp_folder )] = $temp_folder;
					}
				}
			}
		}

		$named_folders = array();
		if ( count($folders) > 0 ) {
			foreach ( $folders as $name => $path ) {
				$index_file = $this->locate_template_file( 'index.php', array(), 'before', $name );

				if ( file_exists( $index_file ) ) {
					// Extract theme name from index.php (if existent).
					$index_contents = file_get_contents( $index_file );
					/* Skin Name: Quiwi */
					$name_exists = preg_match( '/ \* Skin Name: ([a-zA-Z0-9 -_\.,]+)\n/', $index_contents, $matches );
					$skin_name = $name_exists ? $matches[1] : $name;

					if ( in_array( $skin_name, array_values( $named_folders ) ) ) $skin_name .= " [$name]";
				} else {
					$skin_name = $name;
				}

				if ( 'default' === $name ) $skin_name .= ' (' . __( 'default', 'immonex-wp-free-plugin-core' ) . ')';

				$named_folders[$name] = $skin_name;
			}
		}

		return $named_folders;
	} // get_frontend_skins

	/**
	 * Generate a list of available frontend "skin" folders.
	 *
	 * @since 0.9
	 *
	 * @param string $skin New skin key (equals folder name).
	 *
	 * @return string|bool Previous skin key or false if not set or changed.
	 */
	public function set_skin( $skin ) {
		if (
			$skin &&
			in_array( $skin, array_keys( $this->get_frontend_skins() ) ) &&
			$skin !== $this->skin
		) {
			$previous_skin = $this->skin;
			$this->skin = $skin;
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
	public function get_page_list( $args = array() ) {
		$all_pages = get_pages( $args );
		$pages = array();

		if ( count( $all_pages ) > 0 ) {
			foreach ( $all_pages as $page ) {
				$pages[$page->ID] = $page->post_title;
			}
		}

		return $pages;
	} // get_page_list

} // Template_Utils
