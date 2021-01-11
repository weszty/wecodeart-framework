<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	WP-Customizer Output
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since 		4.2.0
 * @version		4.2.0
 */

namespace WeCodeArt\Admin\Customizer\Modules;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Core\Scripts;
use WeCodeArt\Support\Fonts;
use WeCodeArt\Admin\Customizer;
use WeCodeArt\Support\FileSystem;
use function WeCodeArt\Functions\compress_css;

class Styles {

	use Singleton;
	use Scripts\Base;

	/**
	 * The Styles Processor
	 *
	 * @access 	public
	 * @var 	null|object
	 */
	public $styles = null;
	
	/**
	 * The Fonts
	 *
	 * @access 	public
	 * @var 	array
	 */
	public $fonts = null;
	
	/**
	 * WCA FileSystem
	 *
	 * @access 	protected
	 * @since 	4.2.0
	 * @var 	mixed
	 */
	protected 	$FS		= null;
	const 		FILE	= 'customizer.css';

	/**
	 * Init.
	 *
	 * @access public
	 */
	public function init() {
		$this->FS 		= FileSystem::get_instance()->set_folder( 'css' );
		$this->fonts 	= wecodeart( 'integrations' )->get( 'fonts' )::get_instance();
		$this->styles 	= wecodeart( 'integrations' )->get( 'styles' )::get_instance();
		
		// Generate styles and enqueue
		add_action( 'customize_save_after',			[ $this, 'generate_styles' 	], 100 );
		add_action( 'enqueue_block_editor_assets', 	[ $this, 'enqueue_styles' 	], 100 );
		add_action( 'wp_enqueue_scripts',			[ $this, 'enqueue_styles'	], 999 );
		
		if( is_admin() || is_customize_preview() ) return;
		// Remove Customizer inline styles - we add them in our way, compressed!
		remove_action( 'wp_head', 'wp_custom_css_cb', 101 );
	}

	/**
	 * Enqueue the styles.
	 *
	 * @access 	public
	 * @since 	4.2.0
	 * @return 	void
	 */
	public function enqueue_styles() {
		if( $this->has_css_file() === false ) return;

		// Enqueue the dynamic stylesheet.
		wp_enqueue_style(
			$this->make_handle( null, 'wecodeart-dynamic' ),
			$this->get_css_url(),
			[],
			wecodeart( 'version' )
		);
	}

	/**
	 * Loop through all fields and create an array of style definitions.
	 *
	 * @access 	public
	 */
	public function generate_styles( $wp_customize ) {
		$fields = Customizer::get_instance()->get_configurations( $wp_customize );
		$css    = [];

		// Early exit if no fields are found.
		if ( empty( $fields ) ) {
			return;
		}

		foreach( $fields as $control ) {
			// Only continue if $control['output'] is set.
			if ( isset( $control['output'] ) && ! empty( $control['output'] ) ) {
				// Process Font Styles, if any
				$this->fonts->google->generate_font( $control );
				// Get Styles
				$css = array_replace_recursive( $css, self::process_control( $control ) );
			}
		}

		if ( is_array( $css ) ) {
			$css = $this->styles::parse( $this->styles::add_prefixes( $css ) );
		}

		// Get Font Files
		$fonts 			= '';
		$google_fonts 	= $this->fonts->google->process_fonts();
		foreach( $google_fonts as $font ) {
			$fonts .= $this->fonts->google->get_styles( $font );
		}

		// Custom CSS
		$custom = wp_get_custom_css();

		$css = $this->styles::compress( $fonts . $css . $custom );

		if( $css ) {
			return $this->create_css_file( $css );
		}

		return $this->delete_css_file();
	}

	/**
	 * Get the CSS for a field.
	 *
	 * @static
	 * @access 	public
	 * @param 	array $field The field.
	 * @return 	array
	 */
	public static function process_control( $field ) {
		// Find the class that will handle the outpout for this field.
		$classname            = 'WeCodeArt\Admin\Customizer\Modules\Styles\Control';

		$default_classnames   = [
			'wecodeart-fonts' => Styles\Control\Typography::class,
		];

		$field_output_classes = apply_filters( 'wecodeart/filter/customizer/styles/fields', $default_classnames );

		if ( array_key_exists( $field['control'], $field_output_classes ) ) {
			$classname = $field_output_classes[ $field['control'] ];
		}

		$obj = new $classname( $field );

		return $obj->get_styles();
	}

	/**
	 * Get CSS url for post.
	 *
	 * @return  string File url.
	 */
	public function get_css_url() {
		return $this->FS->get_file_url( self::FILE, true );
	}

	/**
	 * Check if we have a CSS file for this post.
	 * @access  public
	 * @since   4.2.0
	 *
	 * @return  bool
	 */
	public function has_css_file() {
		return $this->FS->has_file( self::FILE );
	}

	/**
	 * Function to save CSS into WeCodeArt Folder.
	 *
	 * @access  public
	 * @since   4.2.0
	 * @param   string $css CSS string.
	 *
	 * @return  bool
	 */
	public function create_css_file( $css ) {
		return $this->FS->create_file( self::FILE, $css );
	}

	/**
	 * Function to save CSS into WeCodeArt Folder.
	 *
	 * @access  public
	 * @since   4.2.0
	 *
	 * @return  bool
	 */
	public function delete_css_file() {
		return $this->FS->delete_file( self::FILE );
	}
}