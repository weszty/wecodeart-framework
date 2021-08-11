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

namespace WeCodeArt\Gutenberg\Blocks\Post;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Markup;
use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Entry Title block.
 */
class Title extends Dynamic {

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
	protected $block_name = 'post-title';

	/**
	 * Shortcircuit Register
	 */
	public function register_block_type() {
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
	 * Dynamically renders the `core/post-title` block.
	 *
	 * @param 	array 	$attributes The block attributes.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( $attributes = [], $content = '', $block = null ) {
		if ( ! isset( $block->context['postId'] ) ) {
			return '';
		}

		$disabled = apply_filters( 'wecodeart/filter/entry/title/disabled', false, $block->context['postId'] );
		
		if ( $disabled ) {
			return '';
		}

		$tag_name	= 'h2';
		if ( $value = get_prop( $attributes, 'level', false ) ) {
			$tag_name = 0 === $value ? 'p' : 'h' . $value;
		}

		$classnames = [ 'wp-block-post-title' ];

		if( $value = get_prop( $attributes, 'textAlign', false ) ) {
			$classnames[] = 'text-' . $value;
		}

		if( $value = get_prop( $attributes, 'className', false ) ) {
			$classnames[] = $value;
		}

		$wrappers = [
			[
				'tag' 	=> $tag_name,
				'attrs' => [
					'class' => implode( ' ', $classnames )
				]
			]
		];

		if( get_prop( $attributes, 'isLink', false ) ) {
			$wrappers[] = [
				'tag' 	=> 'a',
				'attrs' => [
					'class'		=> 'wp-block-post-title__link',
					'target' 	=> get_prop( $attributes, 'linkTarget', null ),
					'href'		=> get_permalink( $block->context['postId'] )
				]
			];
		}

		return Markup::wrap( 'entry-title', $wrappers, function( $id ) {
			echo get_the_title( $id );
		}, [ $block->context['postId'] ], false );
	}
}
