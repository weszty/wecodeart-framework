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
		add_filter( 'render_block_core/latest-comments', [ $this, 'render' ], 20, 2 );
	}

	/**
	 * Dynamically renders the `core/latest-comments` block.
	 *
	 * @param 	string 	$content 	The block markup.
	 * @param 	array 	$block 		The parsed block.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( $content = '', $block = [], $data = null ) {
		$attributes = get_prop( $block, 'attrs', [] );

		$comments = get_comments(
			/** This filter is documented in wp-includes/widgets/class-wp-widget-recent-comments.php */
			apply_filters( 'widget_comments_args', [
				'number'      => get_prop( $attributes, [ 'commentsToShow' ], 5 ),
				'status'      => 'approve',
				'post_status' => 'publish',
			] )
		);

		if ( empty( $comments ) ) return '';

		// Prime the cache for associated posts. This is copied from \WP_Widget_Recent_Comments::widget().
		$post_ids = array_unique( wp_list_pluck( $comments, 'comment_post_ID' ) );
		_prime_post_caches( $post_ids, strpos( get_option( 'permalink_structure' ), '%category%' ), false );

		$classnames = [ 'wp-block-latest-comments' ];

		if ( get_prop( $attributes, [ 'displayAvatar' ], true ) ) {
			$classnames[] = 'has-avatars';
		}
		if ( get_prop( $attributes, [ 'displayDate' ], true ) ) {
			$classnames[] = 'has-dates';
		}
		if ( get_prop( $attributes, [ 'displayExcerpt' ], true ) ) {
			$classnames[] = 'has-excerpts';
		}

		$classnames[] = 'list-unstyled';

		return Markup::wrap( 'wp-block-latest-comments', [
			[
				'tag' 	=> 'ul',
				'attrs'	=> [
					'class' => implode( ' ', $classnames )
				]
			]
		], function( $comments, $attributes ) {
			$list_items_markup = '';
	
			foreach ( $comments as $comment ) {
				$list_items_markup .= '<li class="wp-block-latest-comments__comment">';
				$list_items_markup .= '<div class="row gx-3">';
				// Avatar
				if ( get_prop( $attributes, [ 'displayAvatar' ], true ) ) {
					$avatar = get_avatar( $comment, 50, '', '', [
						'class' => 'wp-block-latest-comments__comment-avatar d-block p-1 border shadow-sm',
					] );
					if ( $avatar ) {
						$list_items_markup .= sprintf( '<div class="col-auto">%s</div>', $avatar );
					}
				}

				$list_items_markup .= '<div class="col">';
				// Meta
				$list_items_markup .= '<div class="wp-block-latest-comments__comment-meta">';
				$author_url         = get_comment_author_url( $comment );
				if ( empty( $author_url ) && ! empty( $comment->user_id ) ) {
					$author_url = get_author_posts_url( $comment->user_id );
				}

				// Author
				$author_markup = '';
				if ( $author_url ) {
					$author_markup .= '<a class="wp-block-latest-comments__comment-author" href="' . esc_url( $author_url ) . '">' . get_comment_author( $comment ) . '</a>';
				} else {
					$author_markup .= '<span class="wp-block-latest-comments__comment-author">' . get_comment_author( $comment ) . '</span>';
				}

				$post_title = '<a class="wp-block-latest-comments__comment-post" href="' . esc_url( get_comment_link( $comment ) ) . '">' . wp_latest_comments_draft_or_post_title( $comment->comment_post_ID ) . '</a>';
				$list_items_markup .= sprintf( __( '%1$s on %2$s', 'wecodeart' ), $author_markup, $post_title );

				// Date
				if ( get_prop( $attributes, [ 'displayDate' ], true ) ) {
					$list_items_markup .= sprintf(
						'<time datetime="%1$s" class="wp-block-latest-comments__comment-date d-block text-muted fs-6">%2$s</time>',
						esc_attr( get_comment_date( 'c', $comment ) ),
						date_i18n( get_option( 'date_format' ), get_comment_date( 'U', $comment ) )
					);
				}
				$list_items_markup .= '</div>';
				
				// Excerpt
				if ( get_prop( $attributes, [ 'displayExcerpt' ], true ) ) {
					$list_items_markup .= '<div class="wp-block-latest-comments__comment-excerpt">' . wpautop( get_comment_excerpt( $comment ) ) . '</div>';
				}

				$list_items_markup .= '</div>';
				$list_items_markup .= '</div>';
				$list_items_markup .= '</li>';
			}
	
			echo $list_items_markup;
		}, [ $comments, $attributes ], false );
	}
}
