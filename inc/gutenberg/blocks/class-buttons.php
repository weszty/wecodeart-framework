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
 * @version		5.0.0
 */

namespace WeCodeArt\Gutenberg\Blocks;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Markup;
use WeCodeArt\Markup\SVG;
use WeCodeArt\Singleton;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Buttons blocks.
 */
class Buttons extends Dynamic {

	use Singleton;

	/**
	 * Shortcircuit Register
	 */
	public function register_block_type() {
		add_filter( 'render_block', [ $this, 'filter_render' ], 10, 2 );
	}

	/**
	 * Filter buttons markup
	 *
	 * @param	string 	$block_content
	 * @param	array 	$block
	 */
	public function filter_render( $block_content, $block ) {		
		if ( 'core/button' !== $block['blockName'] ) {
			return $block_content;
		}

		return $this->render( get_prop( $block, 'attrs', [] ), $block_content );
	}

	/**
	 * Dynamically renders the `core/file` block.
	 *
	 * @param 	array 	$attributes The block attributes.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( $attributes = [], $content = '' ) {
		$classes = explode( ' ', get_prop( $attributes, 'className', '' ) );

		$doc = new \DOMDocument();
		// See https://github.com/WordPress/gutenberg/blob/trunk/packages/block-library/src/table-of-contents/index.php
		libxml_use_internal_errors( true );
		$doc->loadHTML( htmlspecialchars_decode(
			utf8_decode( htmlentities( '<html><body>' . $content . '</body></html>', ENT_COMPAT, 'UTF-8', false ) ),
			ENT_COMPAT
		) );
		libxml_use_internal_errors( false );

		$gb_palette = wp_list_pluck( current( get_theme_support( 'editor-color-palette' ) ), 'slug' );
		$color_cls	= [ 'has-background' ];
		foreach( $gb_palette as $slug ) $color_cls[] = 'has-' . $slug . '-background-color';

		// Button Changes
		$link		= $doc->getElementsByTagName( 'a' )->item(0);
		$color 		= get_prop( $attributes, 'backgroundColor', false );
		$classname  = array_merge( explode( ' ', $link->getAttribute( 'class' ) ), [ 'btn' ] );

		if( in_array( 'is-style-outline', $classes ) ) {
			$color_cls[] = 'has-text-color';
			$color 	= get_prop( $attributes, 'textColor', false );
			if( $color ) {
				foreach( $gb_palette as $slug ) $color_cls[] = 'has-' . $slug . '-color';
				$classname[] = 'btn-outline-' . $color;
			}
		} elseif( $color ) {
			$classname[] = 'btn-' . $color;
		}
		
		$classname	= array_diff( $classname, $color_cls );
		$link->setAttribute( 'class', join( ' ', $classname ) );

		return $doc->saveHTML();
	}
}
