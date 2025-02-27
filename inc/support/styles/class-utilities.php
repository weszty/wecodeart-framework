<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Styles Utilities
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since		5.2.4
 * @version		6.0.0
 */

namespace WeCodeArt\Support\Styles;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Support\FileSystem;
use WeCodeArt\Config\Interfaces\Configuration;
use function WeCodeArt\Functions\get_prop;

/**
 * Handles Utilities.
 */
class Utilities implements Configuration {

	use Singleton;

	/**
	 * The registered utilities.
	 *
	 * @var Utilities[]
	 */
	protected $items    = [];

    /**
	 * The utilities classes to load
	 *
	 * @access 	public
	 * @var 	array
	 */
	protected $classes  = [];

    /**
	 * The CSS ID for registered style.
	 *
	 * @var string
	 */
    const CSS_ID    = 'wecodeart-utilities';

    /**
	 * The CSS cache file.
	 *
	 * @var string
	 */
    const CACHE_FILE    = 'utilities.css';
    const CACHE_KEY     = 'wecodeart/gutenberg/utilities';

    /**
	 * Send to Constructor
	 */
	public function init() {
        // Avoid get_instance() due to infinite loop - we only need static methods.
		$this->CSS = wecodeart( 'integrations' )->get( 'styles' );

        // Require, cache and load CSS
		add_action( 'init',             [ $this, 'require'  ], 90   );
        add_action( 'init',             [ $this, 'cache'    ], 95   );
        add_action( 'wp_print_styles',  [ $this, 'frontend_css' ], 20, 1 );
        add_action( 'admin_init',       [ $this, 'editor_css'   ], 20, 1 );

        // Add to editor Advanced class suggestion
        add_filter( 'wecodeart/filter/gutenberg/settings/classes', 	[ $this, 'suggestions' ] );
	}

	/**
     * Load Utils
     *
     * @since 	5.4.8
     * @version 5.4.8
     *
     * @return 	void
     */
    public function require() {
		require_once( __DIR__ . '/utilities.php' );
    }

    /**
     * Load.
     *
     * @param  string|array
     *
     * @return void
     */
    public function load( $key ) {
        return $this->classes = wp_parse_args( (array) $key, $this->classes );
	}

    /**
	 * Generate Utilities CSS on admin.
	 *
	 * @return  void
	 */
	public function cache() {
		// Stylesheet
		$inline_css = '';
		$array_css 	= [];

		foreach( $this->all() as $utility ) {
			$array_css = array_merge_recursive( $array_css, $utility );
		}

		if( ! empty( $array_css ) ) {
            $array_css  = $this->CSS::sort_breakpoints( $array_css );
			$processed 	= $this->CSS::parse( $this->CSS::add_prefixes( $array_css ) );
			$inline_css .= $this->CSS::compress( $processed );
		}

		if( empty( $inline_css ) ) return;
		
		$filesystem = wecodeart( 'files' );
		$filesystem->set_folder( 'cache' );

		if( ! $filesystem->has_file( self::CACHE_FILE ) || false === get_transient( self::CACHE_KEY ) ) {
			$filesystem->create_file( self::CACHE_FILE, $inline_css );
			set_transient( self::CACHE_KEY, true, 5 * MINUTE_IN_SECONDS );
		}

		$filesystem->set_folder( '' );
	}

    /**
	 * Load Utilities CSS on admin.
	 *
	 * @return  void
	 */
	public function editor_css() {
		$filesystem = wecodeart( 'files' );
		$filesystem->set_folder( 'cache' );

		add_editor_style( $filesystem->get_file_url( self::CACHE_FILE, true ) );
		
		$filesystem->set_folder( '' );
	}

    /**
	 * Output frontend styles.
	 *
	 * @return 	string
	 */
	public function frontend_css() {
        if( empty( $this->classes ) ) return;

		$inline_css = '';
		$array_css	= [];

		foreach( array_unique( $this->classes ) as $utility ) {
            // If we dont have utility, move on.
            if( ! $this->has( $utility ) ) continue;
            // Merge breakpoints.
            $array_css = array_merge_recursive( $array_css, $this->get( $utility ) );
        }
        
        if( ! empty( $array_css ) ) {
            $array_css  = $this->CSS::sort_breakpoints( $array_css );
            $processed  = $this->CSS::parse( $this->CSS::add_prefixes( $array_css ) );
            $inline_css .= $this->CSS::compress( $processed );
        }

		if( empty( $inline_css ) ) return;

		wp_register_style( self::CSS_ID, false, [], true, true );
		wp_add_inline_style( self::CSS_ID, $inline_css );
		wp_enqueue_style( self::CSS_ID );
	}

	/**
	 * Add new classes.
	 *
	 * @param 	array  	$classes
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function suggestions( $classes ) {
		return array_merge( array_keys( $this->all() ), $classes );
	}

	/**
     * Set a given module value.
     *
     * @param  array|string  $key
     * @param  mixed   $value
     *
     * @return void
     */
    public function register( $key, $value = null ) {
        $this->set( $key, $value );
	}
	
    /**
     * Set a given module value.
     *
     * @param  array|string  $key
     * @param  mixed   $value
     *
     * @return void
     */
    public function set( $key, $value = null ) {
        $keys = is_array( $key ) ? $key : [ $key => $value ];

        foreach ( $keys as $key => $value ) {
            $this->items[$key] = apply_filters( "wecodeart/styles/utilities/set/{$key}", $value );
        }
	}

	/**
     * Determine if the given module value exists.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function has( $key ) {
        return isset( $this->items[$key] );
    }

    /**
     * Get the specified module value.
     *
     * @param  string  $key
     * @param  mixed   $default
     *
     * @return mixed
     */
    public function get( $key, $default = null ) {
        if ( ! isset( $this->items[$key] ) ) {
            return $default;
        }

        return apply_filters( "wecodeart/styles/utilities/get/{$key}", $this->items[$key] );
    }
	
	/**
     * Removes module from the container.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function forget( $key ) {
		unset( $this->items[$key] );
    }

    /**
     * Get all of the module items for the application.
     *
     * @return array
     */
    public function all() {
        return $this->items;
    }
}