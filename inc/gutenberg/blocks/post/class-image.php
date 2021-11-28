<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg\Blocks
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		5.0.0
 * @version		5.3.1
 */

namespace WeCodeArt\Gutenberg\Blocks\Post;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Post Image block.
 */
class Image extends Dynamic {

	use Singleton;

	/**
	 * Block namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'core';

	/**
	 * Block name.
	 *
	 * @var string
	 */
	protected $block_name = 'post-featured-image';

	/**
	 * Shortcircuit Register
	 */
	public function register() {
		set_post_thumbnail_size( 1920, 1080 );
		add_filter( 'wecodeart/filter/gutenberg/featured/ratio', '__return_true' );
		add_filter( 'post_thumbnail_html',			[ $this, 'filter_html'		], 20, 3 );
		add_filter( 'block_type_metadata_settings', [ $this, 'filter_render'	], 20, 2 );
	}

	/**
	 * Filter image markup
	 *
	 * @param	array 	$settings
	 * @param	array 	$data
	 */
	public function filter_render( $settings, $data ) {
		if ( $this->get_block_type() === $data['name'] ) {
			$settings = wp_parse_args( [
				'render_callback' => [ $this, 'render' ]
			], $settings );
		}
		
		return $settings;
	}

	/**
	 * Dynamically renders the `core/post-featured-image` block.
	 *
	 * @param 	array 	$attributes The block attributes.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( $attributes = [], $content = '', $block = null ) {
		if ( ! isset( $block->context['postId'] ) ) {
			return '';
		}
	
		$post_ID	= $block->context['postId'];
		$link 		= get_prop( $attributes, 'isLink', false );
		$context	= 'wp-block-post-featured-image';
		$classnames = [ $context ];

		if( self::use_ratio() ) {
			$classnames[] = 'ratio';
		}

		if( $align = get_prop( $attributes, 'align' ) ) {
			$classnames[] = 'align' . $align;
		}

		if( $classes = get_prop( $attributes, 'className', '' ) ) {
			$classnames = array_merge( $classnames, array_filter( explode( ' ', $classes ) ) );
		}

		$dimmensions	= wp_array_slice_assoc( $attributes, [ 'width', 'height' ] );
		$thumb_size		= apply_filters( 'post_thumbnail_size', 'post-thumbnail', $post_ID );
		$custom_sizes 	= [];

		if( isset( $dimmensions['width'] ) && strpos( $dimmensions['width'], 'px' ) ) {
			$custom_sizes['width'] = preg_replace( "/[^0-9.]/", "",  $dimmensions['width'] );
		}
		if( isset( $dimmensions['height'] ) && strpos( $dimmensions['height'], 'px' ) ) {
			$custom_sizes['height'] = preg_replace( "/[^0-9.]/", "",  $dimmensions['height'] );
		}

		$dummy_sizes	= wp_parse_args( $custom_sizes, self::get_image_sizes( $thumb_size ) );
		$dummy_ratio	= absint( $dummy_sizes['height'] ) / absint( $dummy_sizes['width'] );
		$dummy_ratio 	= number_format( $dummy_ratio * 100, 3 ) . '%';

		if( $link ) {
			$classnames = array_diff( $classnames, [ 'ratio' ] );
			$filter = function( $wrappers ) use ( $post_ID, $context, $dummy_ratio ) {
				$wrappers = array_merge( $wrappers, [
					[
						'tag' 	=> 'a',
						'attrs'	=> [
							'href' 	=> get_permalink( $post_ID ),
							'class'	=> $context . '__link ratio',
							'style'	=> sprintf( '--wp--aspect-ratio:%s;', $dummy_ratio )
						]
					]
				] );

				return $wrappers;
			};

			add_filter( 'wecodeart/filter/wrappers/' . $context, $filter );
		}

		$image = wecodeart( 'markup' )::wrap( $context, [ [
			'tag' 	=> 'figure',
			'attrs' => [
				'class' => join( ' ', $classnames ),
				'style'	=> ! $link ? sprintf( '--wp--aspect-ratio:%s;', $dummy_ratio ) : null
			]
		] ], 'the_post_thumbnail', [ $post_ID ], false );

		if( $link ) {
			remove_filter( 'wecodeart/filter/wrappers/' . $context, $filter );
		}

		return $image;
	}

	/**
	 * Add placeholder
	 *
	 * @param 	string 	$html 		The image HTML.
	 * @param 	string 	$post 		The post ID.
	 * @param 	string 	$thumbnail 	The thumbnail ID.
	 *
	 * @return 	string 	The placeholder markup.
	 */
	public function filter_html( $html, $post, $thumbnail ) {
		if( $html === '' && $thumbnail === 0 ) {
			$placeholder_text	= apply_filters( 'wecodeart/filter/gutenberg/featured/placeholder', esc_attr__( 'Placeholder', 'wecodeart' ) );
			$placeholder_url 	= "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300' preserveAspectRatio='none'%3E%3Cpath fill='%23EEE' d='M0 0h300v300H0z'/%3E%3Ctext x='50%25' y='50%25' fill='%23aaa' dominant-baseline='middle' text-anchor='middle' font-family='Arial,Helvetica,Open Sans,sans-serif,monospace' font-size='20' %3E $placeholder_text %3C/text%3E%3C/svg%3E";

			$html = sprintf(
				'<img class="%2$s" src="%1$s" alt="%3$s"/>',
				$placeholder_url,
				'wp-block-post-featured-image__src',
				esc_html__( 'Placeholder image', 'wecodeart' )
			);
		}

		return preg_replace( '/(width|height)="\d+"\s/', "", $html );
	}

	/**
	 * Get information about available image sizes
	 *
	 * @since	5.2.8
	 * @version	5.2.8
	 * 
	 * @param 	string 	$size
	 *
	 * @return 	mixed
	 */
	public static function get_image_sizes( $size = '' ) {
		global $_wp_additional_image_sizes;
	
		$sizes = array();
		$get_intermediate_image_sizes = get_intermediate_image_sizes();
	
		// Create the full array with sizes and crop info
		foreach( $get_intermediate_image_sizes as $_size ) {
			if ( in_array( $_size, [ 'thumbnail', 'medium', 'medium_large', 'large' ] ) ) {
				$sizes[ $_size ]['width'] 	= get_option( $_size . '_size_w' );
				$sizes[ $_size ]['height'] 	= get_option( $_size . '_size_h' );
				$sizes[ $_size ]['crop'] 	= (bool) get_option( $_size . '_crop' );
			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
				$sizes[ $_size ] = array( 
					'width' 	=> $_wp_additional_image_sizes[ $_size ]['width'],
					'height' 	=> $_wp_additional_image_sizes[ $_size ]['height'],
					'crop' 		=> $_wp_additional_image_sizes[ $_size ]['crop']
				);
			}
		}
	
		// Get only 1 size if found
		if ( $size ) {
			if( isset( $sizes[ $size ] ) ) {
				return $sizes[ $size ];
			} else {
				return false;
			}
		}

		// Get all
		return $sizes;
	}

	/**
	 * Use aspect ratio
	 *
	 * @since	5.2.8
	 * @version	5.2.8
	 *
	 * @return 	boolean
	 */
	public static function use_ratio() {
		return apply_filters( 'wecodeart/filter/gutenberg/featured/ratio', false );
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		$ratio_css = '';

		if( self::use_ratio() ) {
			$dummy_sizes	= Image::get_image_sizes( apply_filters( 'post_thumbnail_size', 'post-thumbnail', get_the_ID() ) );
			$dummy_ratio	= absint( $dummy_sizes['height'] ) / absint( $dummy_sizes['width'] );

			$ratio_css .= "
				--wp--width: {$dummy_sizes['width']};
				--wp--height: {$dummy_sizes['height']};
				--wp--aspect-ratio: calc(var(--wp--height) / var(--wp--width) * 100% );
			";
		}

		return "
		.wp-block-post-featured-image {
			{$ratio_css}
			position: relative;
			overflow: hidden;
		}
		.wp-block-post-featured-image__link {
			display: block;
		}
		.wp-block-post-featured-image :where(img) {
			width: 100%;
			height: 100%;
			object-fit: cover;
		}
		.wp-block-post-featured-image img[data-placeholder-resp] {
			transition: filter .5s cubic-bezier(.6,-.28,.735,.045);
			filter: blur(5px);
		}	
		.wp-block-post-featured-image img.litespeed-loaded {
			filter: none;
		}
		";
	}
}
