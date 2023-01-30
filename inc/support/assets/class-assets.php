<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Support\Assets
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since 		5.4.0
 * @version		5.7.2
 */

namespace WeCodeArt\Support;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Singleton;
use WeCodeArt\Integration;
use WeCodeArt\Config\Traits\Asset;
use WeCodeArt\Conditional\Traits\No_Conditionals;
use function WeCodeArt\Functions\get_prop;

/**
 * The Fonts object.
 */
final class Assets implements Integration {

	use Singleton;
	use Asset;
	use No_Conditionals;

	/**
	 * Asset
	 *
	 * @since  	5.4.5
	 * @var 	object
	 */
	public 	$Asset	= null;

	/**
	 * Contains the scripts hook.
	 *
	 * @since  	5.7.2
	 * @var 	string
	 */
	public $hook = 'wp_enqueue_scripts';

	/**
	 * Contains an array of script handles registered.
	 * 
	 * @since  	5.7.2
	 * @var 	array
	 */
	private static $scripts = [];

	/**
	 * Contains an array of script handles registered.
	 *
	 * @since  	5.7.2
	 * @var 	array
	 */
	private static $styles = [];

	/**
	 * Send to Constructor
	 */
	public function init() {
		$this->Asset	= ( new Assets\Asset( wecodeart_config() ) );

		// Move jQuery to Footer
		\add_action( 'wp_default_scripts', 	[ $this, 'jquery_to_footer'	] );
	}

	/**
	 * Register hooks
	 */
	public function register_hooks() {
		\add_action( $this->hook,	[ $this, 'core'		] );
		\add_action( $this->hook,	[ $this, 'enqueue'	], PHP_INT_MAX );
	}
	
	/**
	 * jQuery to Footer
	 *
	 * @since	3.1.2
	 * @version	5.3.8
	 */
	public function jquery_to_footer( $wp_scripts ) {
		$config = wecodeart_config( 'footer' );

		if ( is_admin() && get_prop( $config, 'jquery' ) !== false ) return;

		$wp_scripts->add_data( 'jquery', 			'group', 1 );
		$wp_scripts->add_data( 'jquery-core', 		'group', 1 );
		$wp_scripts->add_data( 'jquery-migrate', 	'group', 1 );
	}

	/**
	 * Enqueue Front-End Core Assets
	 *
	 * @since	5.7.2
	 * @version	5.7.2
	 */
	public function core(): void {
		global $wp_scripts;

		$wecodeart = [
			'assetsEnqueue' 	=> $wp_scripts->queue, 
			'templateDirectory' => get_template_directory_uri(),
			'isDevMode'			=> wecodeart_if( 'is_dev_mode' ),
			'locale'			=> [
				'skipLink' => esc_html__( 'Skip to content', 'wecodeart' )
			]
		];

		if( is_child_theme() ) {
			$wecodeart['styleDirectory'] = get_stylesheet_directory_uri();
		}

		$wecodeart = apply_filters_deprecated( 'wecodeart/filter/support/assets/localize', [ $wecodeart ], '5.7.2' );

		// Add Core Scripts
		$this->add_script( $this->make_handle(), [
			'path' 		=> $this->get_asset( 'js', 'frontend' ),
			'deps'		=> [ 'wp-hooks' ],
			'locale'	=> $wecodeart,
			'inline'	=> 'document.addEventListener("DOMContentLoaded",function(){(new wecodeart.JSM(wecodeart)).loadEvents();});',
		] );

		// Add Core Styles
		$this->add_style( 'global-styles', [
			'inline'	=> 'file:' . $this->get_asset( 'css', 'frontend' ),
		] );
	}

	/**
     * Update a given asset.
     *
     * @param  string	$handle
     * @param  array   	$data
     * @param  string	$type
     *
     * @return mixed
     */
    public function update( string $handle, array $data = [], string $type = '' ): mixed {
		if( ! in_array( $type, [ 'style', 'script' ] ) ) {
			return _doing_it_wrong(
				__CLASS__, 
				sprintf( esc_html__( 'When using "%s" method you must define the 3rd parameter (style or script).', 'wecodeart' ), __FUNCTION__ ),
				'5.7.2'
			);
		}

		$data = wp_array_slice_assoc( $data, [ 'path', 'deps', 'version', 'media', 'rtl', 'footer', 'inline', 'locale', 'where', 'load' ] );

		if( $type === 'style' ) {
			if( array_key_exists( $handle, self::$styles ) ) {
				return self::$styles[$handle] = wp_parse_args( $data, self::$styles[$handle] );
			}
		}
		
		if( $type === 'script' ) {
			if( array_key_exists( $handle, self::$scripts ) ) {
				return self::$scripts[$handle] = wp_parse_args( $data, self::$scripts[$handle] );
			}
		}

		return null;
	}

	/**
     * Add a given style.
     *
     * @param  string	$handle
     * @param  array   	$data
     *
     * @return void
     */
    public function add_style( string $handle, array $data = [] ): void {
		// Valid Args
		$data = wp_array_slice_assoc( $data, [ 'path', 'deps', 'version', 'media', 'rtl', 'inline', 'load' ] );
		$data = wp_parse_args( [
			'version' 	=> wecodeart( 'version' ),
			'media'		=> 'all',
		], $data );

		// Registration Logic
		$args = [
			$handle,
			get_prop( $data, 'path', false ),
			get_prop( $data, 'deps', [] ),
			get_prop( $data, 'version' ),
			get_prop( $data, 'media' )
		];

		if ( ! in_array( $handle, self::$styles, true ) && ! wp_style_is( $handle, 'registered' ) ) {
			wp_register_style( ...$args );
		}

		self::$styles[$handle] = $data;
	}

	/**
     * Add a given script.
     *
     * @param  string	$handle
     * @param  array   	$data
     *
     * @return void
     */
    public function add_script( string $handle, array $data = [] ): void {
		// Valid Args
		$data = wp_array_slice_assoc( $data, [ 'path', 'deps', 'version', 'footer', 'inline', 'locale', 'where', 'load' ] );
		$data = wp_parse_args( [
			'version' 	=> wecodeart( 'version' ),
			'footer' 	=> true,
		], $data );

		// Registration Logic
		$args = [
			$handle,
			get_prop( $data, 'path', false ),
			get_prop( $data, 'deps', [] ),
			get_prop( $data, 'version' ),
			get_prop( $data, 'footer' )
		];

		if ( ! in_array( $handle, self::$scripts, true ) && ! wp_script_is( $handle, 'registered' ) ) {
			wp_register_script( ...$args );
		}

		self::$scripts[$handle] = $data;
	}

	/**
	 * Load Assets
	 *
	 * @since	5.7.2
	 * @version	5.7.2
	 *
     * @return void
	 */
	public function enqueue(): void {
		$should_load = function( $data ) {
			$condition = get_prop( $data, [ 'load' ], true );

			if( is_callable( $condition ) ) {
				$condition = call_user_func( $condition );
			}

			return (bool) $condition;
		};

		foreach( $this->all( 'scripts' ) as $handle => $data ) {
			// Condition
			if( $should_load( $data ) === false ) continue;

			// Enqueue
			wp_enqueue_script( $handle );

			// Locale JS
			if( $locale = get_prop( $data, [ 'locale' ] ) ) {
				wp_localize_script( $handle, current( explode( '-', $handle ) ), $locale );
			}

			// Inline JS
			if( ! empty( $inline = self::get_inline( $data ) ) ) {
				wp_add_inline_script( $handle, $inline, get_prop( $data, [ 'where' ], 'after' ) );
			}
		}
		
		foreach( $this->all( 'styles' ) as $handle => $data ) {
			// Condition
			if( $should_load( $data ) === false ) continue;

			// Enqueue
			wp_enqueue_style( $handle );

			// RTL?
			if ( get_prop( $data, 'rtl' ) ) {
				wp_style_add_data( $handle, 'rtl', 'replace' );
			}

			// Inline CSS
			if( ! empty( $inline = self::get_inline( $data ) ) ) {
				wp_add_inline_style( $handle, wecodeart( 'styles' )::compress( $inline, [
					'comments' => true
				] ) );
			}
		}
	}

	/**
     * Get inline code
     *
     * @param  array  $data
     *
     * @return string
     */
    public static function get_inline( array $data = [] ): string {
		$inline = get_prop( $data, [ 'inline' ], '' );
		
		if( str_starts_with( $inline, 'file:' ) ) {
			$paths 		= wecodeart_config( 'paths' );
			
			$file_url	= explode( 'file:', $inline );
			$file_path	= wp_normalize_path( str_replace( $paths['uri'], $paths['directory'], end( $file_url ) ) );

			$inline 	= file_exists( $file_path ) ? file_get_contents( $file_path ) : $inline;
		}

		return $inline;
    }

	/**
     * Update Hook
     *
     * @param  string  $hook
     *
     * @return void
     */
    public function hook( string $hook = 'wp_enqueue_scripts' ) {
		$this->hook = $hook;

		return $this;
    }

	/**
     * Removes asset from the container.
     *
     * @param  string  $key
     * @param  string  $type
     *
     * @return void
     */
    public function forget( string $key, string $type ): void {
		if( $type === 'scripts' ) {
			unset( self::$scripts[$key] );
		}

		if( $type === 'styles' ) {
			unset( self::$styles[$key] );
		}

		return;
    }

    /**
     * Get all of the assets for the application.
     *
     * @return array
     */
    public function all( string $type = 'all' ): array {
		if( $type === 'styles' ) {
			return self::$styles;
		}

		if( $type === 'scripts' ) {
			return self::$scripts;
		}

        return [
			'styles' 	=> self::$styles,
			'scripts' 	=> self::$scripts
		];
    }
}