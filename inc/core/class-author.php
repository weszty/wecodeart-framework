<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Core\Author
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		3.7.7
 * @version		5.0.0
 */

namespace WeCodeArt\Core;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Markup;
use WeCodeArt\Singleton;
use function WeCodeArt\Functions\get_prop;

/**
 * Adds some output to archive pages
 */
class Author {

	use Singleton;

	/**
     * All of the configuration items for the extension.
     *
     * @var array
     */
	protected $config = [];

	/**
	 * Send to Constructor
	 * @since 3.6.4
	 */
	public function init() {
		$this->config = get_prop( wecodeart_config( 'extensions' ), 'author-box' ); 
	}

	/**
	 * Do author box on archive
	 *
	 * @since	3.7.7
	 * @version	4.1.7
	 */
	public function author_box_archive() {
		$enabled = get_prop( $this->config, 'archive', false );

		if ( ! $enabled || ( ! is_author() || get_query_var( 'paged' ) >= 2 ) ) {
			return;
		}

		self::render_box();
	}

	/**
	 * Do author box on single
	 *
	 * @since	3.7.7
	 * @version	4.1.7
	 */
	public function author_box_single() {
		$enabled = get_prop( $this->config, 'single', false );

		if ( ! $enabled || ( ! is_single() || ! post_type_supports( get_post_type(), 'author' ) ) ) {
			return;
		}

		self::render_box();
	}

	/**
	 * Echo the Author Box
	 *
	 * @since	3.7.7
	 * @version 5.0.0
	 *
	 * @return 	void 
	 */
	public static function render_box() {
		if( empty( self::get_data() ) ) return;

		Markup::template( 'entry/author/box', self::get_data() );
	}

	/**
	 * Get Author Information
	 *
	 * @since 	3.9.1
	 * @version	3.9.5
	 *
	 * @return 	array 
	 */
	public static function get_data() {
		$avatar_size = apply_filters( 'wecodeart/filter/author/box/gravatar_size', 100 );
		$avatar_alt  = sprintf( esc_html__( "%s's gravatar", 'wecodeart' ), get_the_author_meta( 'display_name' ) );

		$author = [
			'intro' 		=> is_author() ? esc_html__( 'All articles by', 'wecodeart' ) : esc_html__( 'About', 'wecodeart' ),
			'name'			=> get_the_author(),
			'avatar' 		=> get_avatar( get_the_author_meta( 'email' ), $avatar_size, '', $avatar_alt ),
			'description'	=> wpautop( get_the_author_meta( 'description' ) ),
			'url'			=> get_author_posts_url( get_the_author_meta( 'ID' ) ),
			'container' 	=> ! is_single() ? get_theme_mod( 'content-layout-container' ) : 'container-full'
		];

		if ( 0 === mb_strlen( $author['name'] ) || 0 === mb_strlen( $author['description'] ) ) {
			return [];
		}

		if ( is_singular() ) {
			$author['name']	= sprintf( 
				'<a href="%s" rel="author nofollow">%s</a>',
				esc_url( $author['url'] ), esc_html( $author['name'] ) 
			);
		}

		return $author;
	} 
}