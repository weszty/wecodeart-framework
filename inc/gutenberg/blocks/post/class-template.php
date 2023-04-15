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
 * @version		6.1.1
 */

namespace WeCodeArt\Gutenberg\Blocks\Post;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Post Template block.
 */
class Template extends Dynamic {

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
	protected $block_name = 'post-template';

	/**
	 * Init.
	 */
	public function init() {
		\add_filter( 'post_class', [ $this, 'post_classes' ] );
	}

	/**
	 * Block args.
	 *
	 * @param	array $current	Existing register args
	 *
	 * @return 	array
	 */
	public function block_type_args( $current ): array {
		$supports 	= get_prop( $current, [ 'supports' ], [] );

		return [
			'render_callback' => [ $this, 'render' ],
			'supports' => wp_parse_args( [
				'spacing' => [
					'padding'	=> true,
					'margin'	=> true,
					'blockGap' 	=> true // For list view only (avoid using with grid)
				],
			], $supports )
		];
	}

	/**
	 * Dynamically renders the `core/post-template` block.
	 *
	 * @param 	array 	$attributes	The attributes.
	 * @param 	string 	$content 	The block markup.
	 * @param 	string 	$block 		The block data.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( array $attributes = [], string $content = '', $block = null ) {
		$page 	= isset( $block->context['queryId'] ) ? 'query-' . $block->context['queryId'] . '-page' : 'query-page';
		$page	= (int) get_prop( $_GET, $page, 1 );
		
		// Override the custom query with the global query if needed.
		if ( get_prop( $block->context, [ 'query', 'inherit' ], false ) ) {
			global $wp_query;
			$query = clone $wp_query;
		} else {
			$args	= build_query_vars_from_query_block( $block, $page );
			$query = new \WP_Query( $args );
		}

		if ( block_core_post_template_uses_featured_image( $block->inner_blocks ) ) {
			update_post_thumbnail_cache( $query );
		}

		$classnames = [ 'wp-block-post-template' ];
		if ( isset( $block->context['displayLayout'] ) && isset( $block->context['query'] ) ) {
			if ( get_prop( $block->context, [ 'displayLayout', 'type' ] ) === 'flex' ) {
				$classnames = array_merge( $classnames, [ 'wp-block-post-template--grid', 'grid' ] );
			}
		}

		$item_class = [ 'wp-block-post' ];
		if( get_prop( $block->context, [ 'displayLayout', 'columns' ] ) ) {
			$columns 	= ( 12 / get_prop( $block->context, [ 'displayLayout', 'columns' ], 3 ) );
			$item_class = array_merge( $item_class, [ 'span-12', 'span-md-6', 'span-lg-' . $columns ] );
		}

		if( $value = get_prop( $attributes, 'className' ) ) {
			$classnames[] = $value;
		}

		$classnames[] = 'list-unstyled';

		return wecodeart( 'markup' )::wrap( 'wp-block-query', [
			[
				'tag' 	=> 'ul',
				'attrs'	=> [
					'class' => implode( ' ', $classnames )
				]
			]
		], function( $query, $block, $item_class ) {

			while ( $query->have_posts() ) {
				$query->the_post();

				// Set the block name to one that does not correspond to an existing registered block.
				// This ensures that for the inner instances of the Post Template block, we do not render any block supports.
				$block_instance = $block->parsed_block;
				$block_instance['blockName'] = 'core/null';

				wecodeart( 'markup' )::wrap( 'wp-block-post', [
					[
						'tag' 	=> 'li',
						'attrs'	=> [
							'class' => implode( ' ', get_post_class( implode( ' ', $item_class ) ) )
						]
					]
				], function( $block ) {
					echo ( new \WP_Block( $block, [
						'postType' => get_post_type(),
						'postId'   => get_the_ID(),
					] ) )->render( [ 'dynamic' => false ] );
				}, [ $block_instance ] );
			}
	
			wp_reset_postdata();

		}, [ $query, $block, $item_class ], false );
	}

	/**
	 * Filter classes to the array of post classes.
	 *
	 * @param 	array $classes Classes for the post.
	 * 
	 * @return 	array
	 */
	public function post_classes( $classes ) {
		if ( is_admin() ) {
			return $classes;
		}
		
		// Add 'entry to the post class array.
		$classes[] = 'entry';

		// Remove 'hentry' from post class array.
		$classes = array_diff( $classes, [ 'hentry' ] );
		
		return $classes;
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		return "
		.wp-block-post-template--grid .wp-block-post + .wp-block-post {
			margin-top: 0;
		}
		";
	}
}
