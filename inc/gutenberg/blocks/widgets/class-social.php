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

namespace WeCodeArt\Gutenberg\Blocks\Widgets;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Markup;
use WeCodeArt\Markup\SVG;
use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Social block.
 */
class Social extends Dynamic {

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
	protected $block_name = 'social-links';

	/**
	 * Shortcircuit Register
	 */
	public function register() {
		add_filter( 'render_block_core/social-links', [ $this, 'render' ], 10, 2 );
	}

	/**
	 * Dynamically renders the `core/social-links` block.
	 *
	 * @param 	string 	$content 	The block markup.
	 * @param 	array 	$block 		The parsed block.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( $content = '', $block = [], $data = null ) {
		$attributes = get_prop( $block, 'attrs', [] );

		// Replace justification class
		// $content = preg_replace( '/(?=\S*[-])([a-zA-Z-justified]+)/', '', $content, 1 );
		
		// Replace icon size with font size class
		// $content = preg_replace( "/(?=\S*['-])([a-zA-Z'-icon-size]+)/", get_prop( $attributes, 'size' ), $content, 1 );

		return $content;
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		return "
		.wp-block-social-links {
			display: flex;
			flex-wrap: wrap;
			justify-content: flex-start;
			align-items: center;
			padding-left: 0;
			padding-right: 0;
		}
		.wp-block-social-links.aligncenter {
			display: flex;
			justify-content: center;
		}
		.navbar .wp-block-social-links {
			margin-top: 1rem;
		}
		.wp-block-social-links.is-style-pill-shape {
			width: auto;
		}
		.wp-block-social-links.is-style-pill-shape .wp-social-link a {
			padding-left: calc((2.5/3) * 1em);
			padding-right: calc((2.5/3) * 1em);
		}
		.wp-block-social-links.is-style-square-shape .wp-social-link a {
			border-radius: 0;
		}
		.wp-block-social-links.is-style-logos-only .wp-social-link a {
			background: none;
			padding: 0;
		}
		.wp-social-link {
			display: block;
			background-color: transparent!important;
		}
		.wp-social-link:not(:last-child) {
			margin-right: calc((2.5/3) * 1em);
		}
		.wp-social-link a {
			display: flex;
			flex-direction: column;
			justify-content: center;
			align-items: center;
			text-align: center;
			border-radius: 9999px;
			transition: all 0.1s ease;
			padding: 0.35em;
			line-height: 0;
			text-decoration: none;
			border: none;
			box-shadow: none;
		}
		@media (min-width: 992px) {
			.navbar .wp-block-social-links {
				margin-top: 0;
			  	margin-bottom: 0;
			}
			.navbar .wp-block-social-links:not(:last-child) {
			  	margin-right: 10px;
			}
		}
		";
	}
}
