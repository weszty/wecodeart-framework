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
 * @version		5.1.4
 */

namespace WeCodeArt\Gutenberg\Blocks\Navigation;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Markup;
use WeCodeArt\Singleton;
use WeCodeArt\Support\Styles;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use WeCodeArt\Gutenberg\Blocks\Navigation;
use WeCodeArt\Gutenberg\Blocks\Navigation\Link;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Navigation Home Link block.
 */
class Home extends Dynamic {

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
	protected $block_name = 'home-link';

	/**
	 * Shortcircuit Register
	 */
	public function register() {
		add_filter( 'block_type_metadata_settings', [ $this, 'filter_render' ], 10, 2 );
	}

	/**
	 * Filter table markup
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
	 * Dynamically renders the `core/home-link` block.
	 *
	 * @param 	array 	$attributes The block attributes.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( $attributes = [], $content = '', $block = null ) {
		// Don't render the block's subtree if it has no label.
		if ( empty( $attributes['label'] ) ) {
			return '';
		}
		
		$icons = [];
		$attrs 	= $this->get_wrapper_attributes( $attributes, $block, $icons );

		return Markup::wrap( 'nav-item', [ [
			'tag' 	=> 'li',
			'attrs'	=> $attrs
		] ], function( $attributes, $icons ) {
			$is_active 	= wecodeart_if( 'is_front_page' );
			$classes 	= [ 'wp-block-navigation-link__content', 'nav-link' ];

			if( $is_active ) {
				$classes[] = 'active';
			}

			// Nav Link
			Markup::wrap( 'nav-link', [ [
				'tag' 	=> 'a',
				'attrs'	=>	[
					'class' 		=> join( ' ', $classes ),
					'href'			=> esc_url( trailingslashit( home_url() ) ),
					'aria-current'	=> $is_active ? 'page' : null,
				],
			] ], function( $attributes, $icons ) {
				// Icon
				if( ! empty( $icons ) ) {
					printf( '<i class="wp-block-navigation-link__icon %s"></i>', esc_attr( join( ' ', $icons ) ) );
				}
		
				// Label
				Markup::wrap( 'nav-label', [ [
					'tag' 	=> 'span',
					'attrs'	=> [
						'class' => 'wp-block-navigation-link__label'
					]
				] ], function( $attributes ) { 
						echo wp_kses( $attributes['label'], [
							'code'   => [],
							'em'     => [],
							'img'    => [
								'scale' => [],
								'class' => [],
								'style' => [],
								'src'   => [],
								'alt'   => [],
							],
							's'      => [],
							'span'   => [
								'style' => [],
							],
							'strong' => [],
						] );
				}, [ $attributes ] );

			}, [ $attributes, $icons ] );
		}, [ $attributes, $icons ], false );
	}

	/**
	 * Return an array of wrapper attributes.
	 * 
	 * @return 	array
	 */
	public function get_wrapper_attributes( $attributes, $block, &$icons ) {
		$classes		= [ 'wp-block-navigation-link', 'wp-block-navigation-link--home', 'nav-item' ];
		$class_names	= ! empty( $attributes['className'] ) ? explode( ' ', $attributes['className'] ) : false;

		// Fallback - to do, if styles extension is disabled.
		$inline_style 	= '';

		if ( ! empty( $class_names ) ) {
			$classes = array_merge( $classes, $class_names );
		}
		
		$classes = $this->pluck_icon_classes( $classes, $icons );

		return [
			'class'	=> join( ' ', array_filter( $classes ) ),
			'style'	=> $inline_style,
		];
	}

	/**
	 * Find any custom linkmod or icon classes and store in their holder
	 *
	 * Supported linkmods: .disabled, .dropdown-header, .dropdown-divider, .sr-only
	 * Supported iconsets: Font Awesome 4/5, Glypicons
	 *
	 * @param array   $classes		an array of classes currently assigned to the item.
	 * @param array   $icons		an array to hold linkmod classes.
	 *
	 * @return array  $classes		a maybe modified array of classnames.
	 */
	private function pluck_icon_classes( $classes, &$icons ) {
		foreach ( $classes as $key => $class ) {
			// If any special classes are found, store the class in it's
			// holder array and and unset the item from $classes.
			if ( preg_match( '/^fa-(\S*)?|^fa(s|r|l|b)?(\s?)?$/i', $class ) ) {
				$extras[] = $class;
				unset( $classes[ $key ] );
			} elseif ( preg_match( '/^glyphicon-(\S*)?|^glyphicon(\s?)$/i', $class ) ) {
				$extras[] = $class;
				unset( $classes[ $key ] );
			}
		}

		return $classes;
	}
}