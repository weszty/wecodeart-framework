<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Support\Fonts
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since 		5.7.1
 * @version		5.7.2
 */

namespace WeCodeArt\Support\Fonts;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Singleton;
use WeCodeArt\Support\Fonts;
use WeCodeArt\Admin\Request;
use function WeCodeArt\Functions\get_prop;
use function WeCodeArt\Functions\set_settings_array;

/**
 * Manages the way Google Fonts are enqueued.
 */
final class Google {

	use Singleton;

	/**
	 * The Cache IDs.
	 *
	 * @var string
	 */
	const CACHE_ID = 'wecodeart/fonts/google/remote';
	const CACHE_WP = 'wecodeart/fonts/google/local';

	/**
	 * The array of fonts
	 *
	 * @var	array
	 */
	public	$fonts = [];

	/**
	 * An array of all google fonts.
	 *
	 * @var 	array
	 */
	private	static $items = [];

	/**
	 * Init.
	 */
	public function init() {
		self::get_fonts(); // Load Google Fonts

		$fonts = get_prop( wecodeart_config( 'fonts' ), [ 'google' ], [] );
		array_map( [ $this, 'add_font' ], $fonts );

		add_filter( 'wp_theme_json_data_theme', [ $this, 'register_json' 	] );
		add_action( 'wp_enqueue_scripts', 		[ $this, 'register_styles' 	] );
		add_action( 'admin_init', 				[ $this, 'register_styles' 	] );
	}

	/**
	 * Adds Google Font
	 *
	 * @param 	array $value The field arguments.
	 *
	 * @return 	boolean true if added / false if invalid
	 */
	public function add_font( $value ) {
		$value = wp_parse_args( $value, [
			'family' 	=> '',
			'variants' 	=> [ 'regular' ]
		] );

		// If is not a Google Font, don`t bother!
		if( ! self::is_valid( $value['family'] ) ) return false;

		$sanitized = sanitize_title_with_dashes( $value['family'] );
		// If is new, just add it.
		if ( ! isset( $this->fonts[ $sanitized ] ) ) {
			$this->fonts[ $sanitized ] = [
				'family' 	=> $value['family'],
				'variants' 	=> $value['variants'],
			];

			return true;
		}

		// If already exists, just update it`s variants.
		$variants = array_merge( $this->fonts[ $sanitized ]['variants'], $value['variants'] );
		
		$this->fonts[ $sanitized ]['variants'] = $variants;

		return true;
	}
	
	/**
	 * Determines the validity of the selected font as well as its properties.
	 *
	 * @since 		5.7.1
	 *
	 * @return 		void
	 */
	public function process() {
		// Early exit if font-family is empty.
		if ( empty( $this->fonts ) ) {
			return;
		}	
		
		foreach ( $this->fonts as $font => $data ) {
			// Determine if this is indeed a google font or not.
			if ( ! self::is_valid( $data['family'] ) ) {
				unset( $this->fonts[ $font ] );
				continue;
			}

			// Get all valid font variants for this font.
			$google_font = current( wp_list_filter( self::get_fonts(), [ 'family' => $data['family'] ] ) );
			$this->fonts[ $font ]['variants'] = array_intersect( $data['variants'], $google_font['variants'] );
		}

		$this->fonts = array_map( function( $font ) {
			return wp_parse_args( [
				'variants' 	=> array_map( function( $val ) {
					if ( 'italic' === $val ) return '400i';
					return str_replace( [ 'regular', 'bold', 'italic' ], [ '400', '', 'i'  ], $val );
				}, $font['variants'] ),
				'subsets'	=> [ 'latin', 'latin-ext' ]
			], $font );
		}, $this->fonts );
	}

	/**
	 * Register JSON.
	 *
	 * @return 	object
	 */
	public function register_json( $object ) {
		$data = $object->get_data();

		foreach( $this->fonts as $slug => $font ) {
			$data['settings']['typography']['fontFamilies']['theme'][] = [
				'fontFamily' 	=> $font['family'],
				'slug'			=> $slug,
				'name'			=> $font['family']
			];
		}

		$object->update_with( $data );

		return $object;
	}
	
	/**
	 * Register styles.
	 *
	 * @return 	void
	 */
	public function register_styles() {
		$inline_css = $this->get_styles();

		if( empty( $inline_css ) ) return;

		if( ! wp_style_is( 'wp-webfonts' ) ) {
			wp_register_style( 'wp-webfonts', '' );
			wp_enqueue_style( 'wp-webfonts' );
		}

		wp_add_inline_style( is_admin() ? 'wp-block-library' : 'wp-webfonts', $inline_css );
	}

	/**
	 * Get styles from URL.
	 *
	 * @since 	5.7.1
	 * @param 	string 		$font 	The URL.
	 *
	 * @return 	string
	 */
	public function get_styles() {
		$this->process();

		$style = '';

		foreach( $this->fonts as $font ) {
			$remote	= self::get_font_url( $font );
            $style .= $this->get_local_font_styles( $this->get_cached_url_contents( $remote ) );
        }

        if( wecodeart_if( 'is_dev_mode' ) === false ) {
            $style	= wecodeart( 'styles' )::compress( $style );
        }

        return $style;
	}

	/**
	 * Get styles with fonts downloaded locally.
	 *
	 * @since 	5.7.1
	 * @param 	string 		$css 	The styles.
	 *
	 * @return 	string
	 */
	protected function get_local_font_styles( $css ) {
		$files = $this->get_local_files_from_css( $css );

		// Convert paths to URLs.
		foreach ( $files as $remote => $local ) {
			$files[ $remote ] = wp_normalize_path( str_replace( WP_CONTENT_DIR, content_url(), $local ) );
		}

		return str_replace( array_keys( $files ), array_values( $files ), $css );
	}

	/**
	 * Download files mentioned in our CSS locally.
	 *
	 * @since 	5.7.1
	 * @param 	string		$css 	The CSS we want to parse.
	 *
	 * @return 	array      			Returns an array of remote URLs and their local counterparts.
	 */
	protected function get_local_files_from_css( $css ) {
		wecodeart( 'files' )->set_folder( Fonts::FOLDER );

		$font_files = $this->get_files_from_css( $css );
		$stored     = get_option( Fonts::OPTION, [] );
		$change     = false; // If in the end this is true, we need to update the cache option.

		foreach( $font_files as $font_family => $files ) {
			// The folder path for this font-family.
			wecodeart( 'files' )->set_folder( join( '/', [ Fonts::FOLDER, $font_family ] ) );

			foreach ( $files as $url ) {
				// Get the filename and build path.
				$filename  = basename( wp_parse_url( $url, PHP_URL_PATH ) );
				$font_path = join( '/', [ wecodeart( 'files' )->folder, $filename ] );

				if ( file_exists( $font_path ) ) {
					// Skip if already cached.
					if ( isset( $stored[ $url ] ) ) continue;
					$stored[ $url ] = $font_path;
					$change         = true;
				}

				// Download file to temporary location.
				if ( ! function_exists( 'download_url' ) ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
				}
				
				$tmp_path = download_url( $url );

				// Make sure there were no errors.
				if ( is_wp_error( $tmp_path ) ) continue;

				// Move temp file to final destination.
				$success = rename( $tmp_path, $font_path );
				if ( $success ) {
					$stored[ $url ] = $font_path;
					$change         = true;
				}
			}
		}

		if ( $change ) {
			update_option( Fonts::OPTION, $stored );
		}

		// Revert back the Path
		wecodeart( 'files' )->set_folder( '' );

		return $stored;
	}

	/**
	 * Get font files from the CSS.
	 *
	 * @since 	5.7.1
	 * @param 	string 	$css 	The CSS we want to parse.
	 *
	 * @return 	array      		Returns an array of font-families and the font-files used.
	 */
	protected function get_files_from_css( $css ) {
		$font_faces = explode( '@font-face', $css );
		$result = [];

		// Loop all our font-face declarations.
		foreach( $font_faces as $font_face ) {
			// Make sure we only process styles inside this declaration.
			$style = explode( '}', $font_face )[0];

			// Sanity check.
			if ( false === strpos( $style, 'font-family' ) ) continue;

			// Get an array of our font-families.
			preg_match_all( '/font-family.*?\;/', $style, $font_families );

			// Get an array of our font-files.
			preg_match_all( '/url\(.*?\)/i', $style, $font_files );

			// Get the font-family name.
			$font_family = 'unknown';
			if ( isset( $font_families[0] ) && isset( $font_families[0][0] ) ) {
				$font_family = rtrim( ltrim( $font_families[0][0], 'font-family:' ), ';' );
				$font_family = trim( str_replace( array( "'", ';' ), '', $font_family ) );
				$font_family = sanitize_key( strtolower( str_replace( ' ', '-', $font_family ) ) );
			}

			// Make sure the font-family is set in our array.
			if ( ! isset( $result[ $font_family ] ) ) {
				$result[ $font_family ] = [];
			}

			// Get files for this font-family and add them to the array.
			foreach( $font_files as $match ) {
				// Sanity check.
				if ( ! isset( $match[0] ) ) continue;

				// Add the file URL.
				$result[ $font_family ][] = rtrim( ltrim( $match[0], 'url(' ), ')' );
			}

			// Make sure we have unique items.
			// We're using array_flip here instead of array_unique for improved performance.
			$result[ $font_family ] = array_flip( array_flip( $result[ $font_family ] ) );
		}

		return $result;
	}

	/**
	 * Get cached url contents.
	 * If a cache doesn't already exist, get the URL contents from remote and cache the result.
	 *
	 * @since 	5.7.1
	 * @param 	string 	$url        The URL we want to get the contents from.
	 * @param 	string 	$user_agent	The user-agent to use for our request.
	 *
	 * @return 	string            	Returns the remote URL contents.
	 */
	protected function get_cached_url_contents( $url = '', $user_agent = null ) {
		// Try to retrieved cached response from the gfonts API.
		$contents         = false;
		$cached_responses = get_transient( self::CACHE_WP );
		$cached_responses = ( $cached_responses && is_array( $cached_responses ) ) ? $cached_responses : [];

		if ( isset( $cached_responses[ md5( $url . $user_agent ) ] ) ) {
			return $cached_responses[ md5( $url . $user_agent ) ];
		}

		// Get the contents from remote.
		$contents = $this->get_url_contents( $url, $user_agent );

		// If we got the contents successfully, store them in a transient.
		if ( $contents ) {
			$cached_responses[ md5( $url . $user_agent ) ] = $contents;
			set_transient( self::CACHE_WP, $cached_responses, WEEK_IN_SECONDS );
		}

		return $contents;
	}

	/**
	 * Get remote file contents.
	 *
	 * @since 	5.7.1
	 * @param 	string 	$url		The URL we want to get the contents from.
	 * @param 	string 	$user_agent	The user-agent to use for our request.
	 *
	 * @return 	string            	Returns the remote URL contents.
	 */
	protected function get_url_contents( $url = '', $user_agent = null ) {
		if ( ! $user_agent ) {
			/**
			 * The user-agent we want to use.
			 *
			 * For woff2 format, use'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:73.0) Gecko/20100101 Firefox/73.0'.
			 * The default user-agent is the only one compatible with woff (not woff2)
			 * which also supports unicode ranges.
			 */
			// $user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8';
			$user_agent = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:73.0) Gecko/20100101 Firefox/73.0';
		}

		// Get the response.
		$request = new Request( $url, [ 'user-agent' => $user_agent ] );
		$request->send( $request::METHOD_GET );

		return $request->get_response_body();
	}

	/**
	 * Get remote file URL.
	 *
	 * @since 	5.7.1
	 * @param 	array 	$font
	 *
	 * @return 	mixed 	Returns the remote URL or false if is not google font.
	 */
	public static function get_font_url( $font ) {
		$font = wp_parse_args( $font, [
			'family' 	=> '',
			'variants' 	=> ''
		] );

		if( self::is_valid( trim( $font['family'] ) === false ) ) return false;

		$_italics = array_map( function( $item ) {
			return $item . 'i';
		}, range( 100, 900, 100 ) );

		// Font Family
		$family	= str_replace( ' ', '+', trim( $font['family'] ) );

		// Font Styles
		$normal = array_diff( $font['variants'], $_italics );
		$italic = array_intersect( $font['variants'], $_italics );

		// Setup Variants
		$sets 	= implode( ',', array_filter( [ count( $italic ) ? 'ital' : '', 'wght' ] ) );
		$wght 	= count( $italic ) ? implode( ';', [
			implode( ';', array_map( function( $i ) {
				return '0,' . filter_var( $i, FILTER_SANITIZE_NUMBER_INT );
			}, $normal ) ),
			implode( ';', array_map( function( $i ) {
				return '1,' . filter_var( $i, FILTER_SANITIZE_NUMBER_INT );
			}, $italic ) )
		] ) : implode( ';', $normal );

		// Create CSS2 Url
		return esc_url_raw( add_query_arg( [
			'family' => implode( ':', [ $family, implode( '@', [ $sets, $wght ] ) ] ),
			'display'=> 'swap'
		], 'https://fonts.googleapis.com/css2' ) );
	}

	/**
	 * Determine if a font-name is a valid google font or not.
	 *
	 * @param   string 	$fontname The name of the font we want to check.
	 *
	 * @return  bool
	 */
	public static function is_valid( $fontname ) {
		return ( count( wp_list_filter( self::get_fonts(), [ 'family' => $fontname ] ) ) );
	}

	/**
	 * Return an array of all available Google Fonts.
	 *
	 * @return array    All Google Fonts.
	 */
	public static function get_fonts() {
        if ( false === ( $results = get_transient( self::CACHE_ID ) ) ) {
			// It wasn't there, so regenerate the data and save the transient
			$request_url    = add_query_arg( [
                'key' => 'AIzaSyBwIX97bVWr3-6AIUvGkcNnmFgirefZ6Sw'
            ], 'https://www.googleapis.com/webfonts/v1/webfonts' );
			$request        = new Request( $request_url, [] );
			$request->send( $request::METHOD_GET );

            $results = $request->get_response_body();
            $results = json_decode( $results, true );

			if( json_last_error() === JSON_ERROR_NONE ) {
				$results = array_map( function( $item ) {
					return wp_array_slice_assoc( $item, [ 'family', 'variants', 'subsets' ] );
				}, get_prop( $results, 'items', [] ) );
	
				set_transient( self::CACHE_ID, $results, WEEK_IN_SECONDS );   
			}
        }

        self::$items = $results;

        return self::$items;
	}

	/**
	 * Clear Cache.
	 *
	 * @return boolean.
	 */
	public static function clear_cache() {
		delete_transient( self::CACHE_ID );
		delete_transient( self::CACHE_WP );

		return true;
	}
}