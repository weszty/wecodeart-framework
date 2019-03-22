<?php namespace WeCodeArt\Core;
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit();
// Use
use WeCodeArt\Utilities\Helpers;
use WeCodeArt\Utilities\Markup;
use WeCodeArt\Utilities\Markup\SVG;
use WeCodeArt\Support\WooCommerce\Callbacks;
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Archive Intro Class
 * @copyright   Copyright (c) 2019, WeCodeArt Framework
 * @since 		v3.5
 * @version		v3.6.2
 */

class Archive {
	use \WeCodeArt\Singleton;

	/**
	 * Send to Constructor
	 * @since 3.6.2
	 */
	public function __construct() {
		add_filter( 'get_the_archive_title', 		[ $this, 'filter_cat_title' 	] );
		add_action( 'wecodeart/hook/inner/top',		[ $this, 'render_intro_markup' 	], 15 );
		add_action( 'wecodeart/hook/inner/top', 	[ $this, 'render_author_box'	], 20 ); 
	}
	
	/**
	 * Echo the Archive Intro Markup
	 * @since 	unknown
	 * @version	3.5
	 * @return 	HTML 
	 */
	public function render_intro_markup() {
		// Don't enable on author archive / WooCommerce
		if( ! is_archive() && ! is_search() || is_author() || 
			( Helpers::detectplugin( [ 'classes' => [ 'woocommerce' ] ] ) && is_woocommerce() )
		) return;

		$options = Content::get_contextual_options(); 

		$wrappers = [
			[ 'tag' => 'div', 'attrs' => [ 'class' => 'archive-intro' ] ],
			[ 'tag' => 'div', 'attrs' => [ 'class' => $options['container'] ] ],
			[ 'tag' => 'div', 'attrs' => [ 'class' => 'row' ] ],
			[ 'tag' => 'div', 'attrs' => [ 'class' => 'col' ] ]
		];

		Markup::wrap( 'archive-intro-wrappers', $wrappers, 'the_archive_title' );
		
	}

	/**
	 * Echo the Archive Intro Markup
	 * @since 	unknown
	 * @version	v3.5
	 * @return 	string HTML 
	 */
	public function render_author_box() {
		// Filter to give posibility for disable
		if( apply_filters( 'wecodeart/filter/author-box/archive', true ) ) { 
			if( is_author() ) get_template_part( 'views/author/author' );
		}
	}

	/**
	 * Filter category title 
	 * @since 	v3.5
	 * @version	v3.5
	 * @return 	string HTML 
	 */
	public function filter_cat_title() {
		$output = '<span class="archive-intro__svg">' . SVG::compile( 'icon--folder' ) . '</span> ';
		$title_template = '<span class="archive-intro__title">%s</span>';
		if ( is_search() ) {
			$output = sprintf( $title_template, sprintf( __( 'Search Results for "%s"', 'wecodeart' ), '<span>' .  get_search_query() . '</span>' ) );
		} elseif ( is_category() ) {
			$output .= sprintf( $title_template, __( 'Category Archives: ', 'wecodeart' ) . ' ' . single_term_title( '', false ) );
		} elseif ( is_tag() ) {
			$output .= sprintf( $title_template, __( 'Tag Archives: ', 'wecodeart' ) . ' ' . single_term_title( '', false ) );
		} elseif ( is_year() ) {
			$output .= sprintf( $title_template, __( 'Yearly Archives: ', 'wecodeart' ) . ' ' . get_the_date( _x( 'Y', 'yearly archives date format', 'wecodeart' ) ) );
		} elseif ( is_month() ) {
			$output .= sprintf( $title_template, __( 'Monthly Archives: ', 'wecodeart' ) . ' ' . get_the_date( _x( 'F Y', 'monthly archives date format', 'wecodeart' ) ) );
		} elseif ( is_day() ) {
			$output .= sprintf( $title_template, __( 'Daily Archives: ', 'wecodeart' ) . ' ' . get_the_date() );
		} elseif ( is_post_type_archive() ) {
			$output .= sprintf( $title_template, __( 'Post Type Archives: ', 'wecodeart' ) . ' ' . post_type_archive_title( '', false ) );
		} elseif ( is_tax() ) {
			$tax = get_taxonomy( get_queried_object()->taxonomy );
			/* translators: %s: Taxonomy singular name */
			$output .= sprintf( esc_html__( '%s Archives:', 'wecodeart' ), $tax->labels->singular_name );
		} else {
			$output .= __( 'Archives:', 'wecodeart' );
		} 

		return trim( $output );
	} 
}