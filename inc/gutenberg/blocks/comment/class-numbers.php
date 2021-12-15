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
 * @since		5.3.3
 * @version		5.3.3
 */

namespace WeCodeArt\Gutenberg\Blocks\Comment;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use WeCodeArt\Gutenberg\Blocks\Comment\Template;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Pagination Numbers block.
 */
class Numbers extends Dynamic {

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
	protected $block_name = 'comments-pagination-numbers';

	/**
	 * Shortcircuit Register
	 */
	public function register() {
		add_filter( 'block_type_metadata_settings', [ $this, 'filter_render' ], 10, 2 );
	}

	/**
	 * Filter template markup
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
	 * Dynamically renders the `core/comments-pagination-numbers` block.
	 *
	 * @param 	array 	$attributes The block attributes.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( $attributes = [], $content = '', $block = null ) {
		// Get the post ID from which comments should be retrieved.
		$post_id = get_prop( $block->context, [ 'postId' ], get_the_id() );

		if ( ! $post_id ) {
			return '';
		}

		// Get the what we need.
		$comments 	= new \WP_Comment_Query( Template::build_query( $block ) );
		$total    	= $comments->max_num_pages;

		// Get the current comment page from the URL.
		$current 	= get_query_var( 'cpage' ) ?: 'newest' === get_option( 'default_comments_page' ) ? $total : 1;

		return wecodeart( 'markup' )::wrap( 'wp-block-comments-pagination-numbers', [ [
			'tag' 	=> 'div',
			'attrs' => [ 
				'class' => 'wp-block-comments-pagination-numbers'
			] 
		] ], 'paginate_comments_links', [
			'total'     => $total,
			'current'   => $current,
			'prev_next' => false,
		], false );
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		return "";
	}
}
