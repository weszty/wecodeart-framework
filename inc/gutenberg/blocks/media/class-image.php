<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg\Blocks
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since		5.0.0
 * @version		5.7.2
 */

namespace WeCodeArt\Gutenberg\Blocks\Media;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;
use function WeCodeArt\Functions\get_dom_element;
use function WeCodeArt\Functions\get_placeholder_source;

/**
 * Gutenberg Image block.
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
	protected $block_name = 'image';

	/**
	 * Shortcircuit Register
	 */
	public function register() {
		\add_filter( 'register_block_type_args',				[ $this, 'block_type_args'	], 20, 2 );
		\add_filter( 'render_block_' . $this->get_block_type(),	[ $this, 'render'			], 20, 2 );
	}

	/**
	 * Block args
	 *
	 * @since	5.7.2
	 * @version	5.7.2
	 *
	 * @return 	array
	 */
	public function block_type_args( $args, $block_name ) {
		if ( $block_name === $this->get_block_type() || $block_name === 'core/avatar' /* Same markup */ ) {
			$selectors = array_merge( (array) get_prop( $args, [ 'supports', '__experimentalSelector' ], [] ), [ ' :where(img,svg)' ] );
			$args['supports']['__experimentalSelector'] = implode( ',', array_filter( $selectors ) );
		}

		return $args;
	}

	/**
	 * Filter Image
	 * 
	 * @param 	string 	$content
	 * @param 	array 	$block
	 * 
	 * @return 	string
	 */
	public function render( string $content = '', $block = [] ): string {
		$dom	= $this->dom( $content );
		$div  	= get_dom_element( 'figure', $dom );
		$link 	= get_dom_element( 'a', $div );
		$img  	= get_dom_element( 'img', $link ?? $div );

		// If no image, use placeholder.
		if ( $img && ! $img->getAttribute( 'src' ) ) {
			$img->setAttribute( 'class', 'wp-block-image__placeholder' );
			$img->setAttribute( 'src', get_placeholder_source() );
			$img->setAttribute( 'alt', esc_attr__( 'Placeholder', 'wecodeart' ) );

			return $dom->saveHTML();
		}

		// If image is SVG, import it.
		if ( str_contains( $content, '.svg' ) ) {
			$file = str_replace( content_url(), dirname( dirname( get_template_directory() ) ), $img->getAttribute( 'src' ) );
	
			if ( ! file_exists( $file ) ) {
				return $content;
			}
	
			$svg = $dom->importNode( $this->dom( file_get_contents( $file ) )->documentElement, true );
	
			if ( ! method_exists( $svg, 'setAttribute' ) ) {
				return $content;
			}
	
			$svg->setAttribute( 'class', $img->getAttribute( 'class' ) );

			if( $value = $img->getAttribute( 'width' ) ) {
				$svg->setAttribute( 'width', $value );
			}

			if( $value = $img->getAttribute( 'height' ) ) {
				$svg->setAttribute( 'height', $value );
			}

			if( $value = $img->getAttribute( 'alt' ) ) {
				$svg->setAttribute( 'aria-label', $value );
			}
	
			( $link ?? $div )->removeChild( $img );
			( $link ?? $div )->appendChild( $svg );
		}

		return $dom->saveHTML();
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		return '
		/* Block */
		.wp-block-image {
			margin: 0;
		}
		.wp-block-image.alignfull {
			max-width: initial;
		}
		.wp-block-image.aligncenter {
			text-align: center;
		}
		.wp-block-image:where(.alignfull,.alignwide) :where(img,svg) {
			width: 100%;
		}
		.wp-block-image.is-style-rounded {
			border-radius: 9999px;
		}
		.wp-block-image > a,
		.wp-block-image :where(img,svg) {
			border-radius: inherit;
		}
		.wp-block-image figcaption {
			font-size: var(--wp--preset--font-size--small);
			font-style: italic;
		}
		';
	}
}
