<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg\Blocks
 * @copyright   Copyright (c) 2022, WeCodeArt Framework
 * @since		5.0.0
 * @version		5.5.8
 */

namespace WeCodeArt\Gutenberg\Blocks\Widgets;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Query Latest Comments block.
 */
class Comments extends Dynamic {

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
	protected $block_name = 'latest-comments';

	/**
	 * Shortcircuit Register
	 */
	public function register() {
		\wp_deregister_style( 'wp-block-' . $this->block_name );
		
		\add_filter( 'block_type_metadata_settings', [ $this, 'filter_render' ], 10, 2 );
	}

	/**
	 * Filter block markup
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
	 * Dynamically renders the `core/latest-comments` block.
	 *
	 * @param 	string 	$attributes	The block attrs.
	 * @param 	string 	$content 	The block markup.
	 * @param 	array 	$block 		The parsed block.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( $attributes = [], $content = '', $block = null ) {
		$comments = get_comments(
			/** This filter is documented in wp-includes/widgets/class-wp-widget-recent-comments.php */
			apply_filters( 'widget_comments_args', [
				'number'      => get_prop( $attributes, [ 'commentsToShow' ], 5 ),
				'status'      => 'approve',
				'post_status' => 'publish',
			] )
		);

		if ( count( $comments ) === 0 ) return '';

		// Prime the cache for associated posts. This is copied from \WP_Widget_Recent_Comments::widget().
		$post_ids = array_unique( wp_list_pluck( $comments, 'comment_post_ID' ) );
		_prime_post_caches( $post_ids, strpos( get_option( 'permalink_structure' ), '%category%' ), false );

		$classnames = [ 'wp-block-latest-comments' ];

		if( get_prop( $attributes, [ 'displayAvatar' ], true ) ) {
			$classnames[] = 'has-avatars';
		}

		if( get_prop( $attributes, [ 'displayDate' ], true ) ) {
			$classnames[] = 'has-dates';
		}

		if( get_prop( $attributes, [ 'displayExcerpt' ], true ) ) {
			$classnames[] = 'has-excerpts';
		}
		
		if( $classname = get_prop( $attributes, [ 'className' ], '' ) ) {
			$classnames[] = array_merge( $classnames, explode( ' ', $classname ) );
		}

		$classnames[] = 'list-unstyled';

		// Template
		$template 	= '';

		// Comment Author Avatar
		if ( get_prop( $attributes, [ 'displayAvatar' ], true ) ) {
			$template .= '<!-- wp:avatar {"className":"float-start rounded-circle overflow-hidden me-3","size":50} /-->';
		}
		
		$template .= '<!-- wp:group {"className":"gap-1 clearfix"} -->';
		$template .= '<div class="wp-block-group gap-1 clearfix">';

		// Comment Author
		$template .= '<!-- wp:comment-author-name {"isLink":true} /-->';

		// Comment Date
		if ( get_prop( $attributes, [ 'displayDate' ], true ) ) {
			$template .= '<!-- wp:comment-date {"isLink":false} /-->';
		}
		$template .= '</div>';
		$template .= '<!-- /wp:group -->';

		// Comment Content
		if ( get_prop( $attributes, [ 'displayExcerpt' ], true ) ) {
			$template .= '<!-- wp:comment-content {"backgroundColor":"white","className":"p-3 my-3 border rounded"} /-->';
		}

		// Allow users to change this template
		$template = apply_filters( 'wecodeart/filter/gutenberg/latest-comments/template', parse_blocks( $template ), $attributes );

		return wecodeart( 'markup' )::wrap( 'wp-block-latest-comments', [
			[
				'tag' 	=> 'ul',
				'attrs'	=> [
					'class' => implode( ' ', $classnames )
				]
			]
		], function( $comments, $template ) {
			$content 	= '';
			
			foreach( $comments as $comment ) {
				$blocks 	= new \WP_Block_List( $template, [
					'commentId' => $comment->comment_ID
				] );

				// Same context as default comment block for filters
				$content	.= wecodeart( 'markup' )::wrap( 'wp-block-comment', [
					[
						'tag' 	=> 'li',
						'attrs'	=> [
							// To avoid duplicate ID if used on same post with latest comment
							// We will give a different ID with "lcomment-" prefix
							'id'	=> 'lcomment-' . $comment->comment_ID,
							'class' => implode( ' ', get_comment_class( '', $comment ) )
						]
					]
				], function( $blocks ) {
					
					$content = '';

					foreach( $blocks as $block ) $content .= $block->render( $block );

					echo $content;

				}, [ $blocks ], false );
			}

			echo $content;

		}, [ $comments, $template ], false );
	}
}
