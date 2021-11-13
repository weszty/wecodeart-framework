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
 * @version		5.2.2
 */

namespace WeCodeArt\Gutenberg\Blocks\Post;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Markup;
use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Post Excerpt block.
 */
class Excerpt extends Dynamic {

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
	protected $block_name = 'post-excerpt';

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
	 * Dynamically renders the `core/post-excerpt` block.
	 *
	 * @param 	array 	$attributes The block attributes.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( $attributes = [], $content = '', $block = null ) {
		if ( ! isset( $block->context['postId'] ) ) {
			return '';
		}

		$post_id = $block->context['postId'];

		$more_text = Markup::wrap( 'entry-more', [
			[
				'tag' 	=> 'a',
				'attrs' => [
					'class' => 'wp-block-post-excerpt__more-btn',
					'href'	=> esc_url( get_the_permalink( $post_id ) ),
				]
			]
		], function( $attributes, $key, $default ) {
			echo wp_kses_post( get_prop( $attributes, $key, $default ) );
		}, [ $attributes, 'moreText', '' ], false );

		$filter_excerpt_more = function( $more ) use ( $more_text ) {
			return empty( $more_text ) ? $more : '';
		};

		add_filter( 'excerpt_more', $filter_excerpt_more );
		$html	= '<p class="wp-block-post-excerpt__text">' . get_the_excerpt( $post_id );
		if ( get_prop( $attributes, 'showMoreOnNewLine', false ) && ! empty( $more_text ) ) {
			$html .= '</p><p class="wp-block-post-excerpt__more">' . $more_text . '</p>';
		} else {
			$html .= " $more_text</p>";
		}
		remove_filter( 'excerpt_more', $filter_excerpt_more );

		return Markup::wrap( 'entry-excerpt', [
			[
				'tag' 	=> 'div',
				'attrs' => [
					'class' => 'wp-block-post-excerpt'
				]
			]
		], $html, [], false );
	}
}
