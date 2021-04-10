<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Core\Archive
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		3.5
 * @version		4.2.0
 */

namespace WeCodeArt\Core;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Markup;
use WeCodeArt\Singleton;
use WeCodeArt\Markup\SVG;
use function WeCodeArt\Functions\get_prop;

/**
 * Adds some output to archive pages
 */
class Archive {

	use Singleton;

	/**
	 * Send to Constructor
	 * @since 3.9.3
	 */
	public function init() {
		add_filter( 'get_the_archive_title',	[ $this, 'filter_cat_title' 	] );
		add_action( 'wecodeart/hook/inner/top',	[ $this, 'render_intro_markup' 	], 15 );
		add_action( 'wecodeart/hook/inner/top',	[ Author::get_instance(), 'author_box_archive' ], 20 );
	}
	
	/**
	 * Echo the Archive Intro Markup
	 *
	 * @since 	unknown
	 * @version	4.2.0
	 *
	 * @return 	void 
	 */
	public function render_intro_markup() {
		// Don't enable on author archive / WooCommerce.
		if( ! is_archive() && ! is_search() || is_author() || wecodeart_if( 'is_woocommerce_archive' ) ) return;

		$options = Content::get_contextual_options(); 

		$wrappers = [
			[ 'tag' => 'div', 'attrs' => [ 'class' => 'archive-intro' ] ],
			[ 'tag' => 'div', 'attrs' => [ 'class' => get_prop( $options, 'container' ) ] ],
			[ 'tag' => 'div', 'attrs' => [ 'class' => 'row' ] ],
			[ 'tag' => 'div', 'attrs' => [ 'class' => 'col my-5' ] ]
		];

		Markup::wrap( 'archive-intro', $wrappers, 'the_archive_title' );
		
	} 

	/**
	 * Filter category title
	 *
	 * @since	3.5
	 * @version	4.2.0
	 *
	 * @return 	string
	 */
	public function filter_cat_title() {
		$output = sprintf( '<span class="archive-intro__svg d-inline-block align-middle h2">%s</span>', SVG::compile( 'folder' , [
			'class' => 'd-block me-3 fa-5x'
		] ) );

		$title_template = '<span class="archive-intro__title d-inline-block align-middle h2 mb-0">%s</span>';

		if ( is_search() ) {
			$output = sprintf( $title_template, sprintf( 
				esc_html__( 'Search Results for "%s"', 'wecodeart' ), '<span>' .  get_search_query() . '</span>' 
			) );
		} elseif ( is_category() ) {
			$output .= sprintf( $title_template, sprintf(
				esc_html__( 'Category Archives: %s', 'wecodeart' ), single_term_title( '', false ) 
			) );
		} elseif ( is_tag() ) {
			$output .= sprintf( $title_template, sprintf(
				esc_html__( 'Tag Archives: %s', 'wecodeart' ), single_term_title( '', false ) 
			) );
		} elseif ( is_year() ) {
			$output .= sprintf( $title_template, sprintf(
				esc_html__( 'Yearly Archives: %s', 'wecodeart' ), 
				get_the_date( _x( 'Y', 'yearly archives date format', 'wecodeart' ) ) 
			) );
		} elseif ( is_month() ) {
			$output .= sprintf( $title_template, sprintf(
				esc_html__( 'Monthly Archives: %s', 'wecodeart' ), 
				get_the_date( _x( 'F Y', 'monthly archives date format', 'wecodeart' ) ) 
			) );
		} elseif ( is_day() ) {
			$output .= sprintf( $title_template, sprintf( 
				esc_html__( 'Daily Archives: %s', 'wecodeart' ), get_the_date() 
			) );
		} elseif ( is_post_type_archive() ) {
			$output .= sprintf( $title_template, sprintf(
				esc_html__( 'Post Type Archives: %s', 'wecodeart' ), post_type_archive_title( '', false ) 
			) );
		} elseif ( is_tax() ) {
			$tax = get_taxonomy( get_queried_object()->taxonomy );
			/* translators: %s: Taxonomy singular name */
			$output .= sprintf( esc_html__( '%s Archives', 'wecodeart' ), $tax->labels->singular_name );
		} else {
			$output .= esc_html__( 'Archives', 'wecodeart' );
		} 

		return trim( $output );
	} 
}