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

/**
 * Gutenberg Embed block.
 */
class Embed extends Dynamic {

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
	protected $block_name = 'embed';

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		return '
		iframe {
			border: 0;
		}
		.wp-block-embed {
			margin-bottom: 1rem;
		}
		.wp-block-embed.wp-embed-aspect-21-9 .wp-block-embed__wrapper::before {
			padding-top: calc(9 / 21 * 100%);
		}
		.wp-block-embed.wp-embed-aspect-18-9 .wp-block-embed__wrapper::before {
			padding-top: calc(9 / 18 * 100%);
		}
		.wp-block-embed.wp-embed-aspect-16-9 .wp-block-embed__wrapper::before {
			padding-top: calc(9 / 16 * 100%);
		}
		.wp-block-embed.wp-embed-aspect-4-3 .wp-block-embed__wrapper::before {
			padding-top: calc(3 / 4 * 100%);
		}
		.wp-block-embed.wp-embed-aspect-1-1 .wp-block-embed__wrapper::before {
			padding-top: calc(1 / 1 * 100%);
		}
		.wp-block-embed.wp-embed-aspect-9-16 .wp-block-embed__wrapper::before {
			padding-top: calc(16 / 9 * 100%);
		}
		.wp-block-embed.wp-embed-aspect-1-2 .wp-block-embed__wrapper::before {
			padding-top: calc(2 / 1 * 100%);
		}
		.wp-embed-responsive .wp-block-embed__wrapper {
			position: relative;
		}
		.wp-embed-responsive .wp-block-embed__wrapper::before {
			content: "";
			display: block;
			padding-top: 50%;
		}
		.wp-embed-responsive .wp-block-embed__wrapper iframe {
			position: absolute;
			top: 0;
			right: 0;
			bottom: 0;
			left: 0;
			width: 100%;
			height: 100%;
		}
		';
	}
}
