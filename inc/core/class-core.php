<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Core
 * @copyright   Copyright (c) 2020, WeCodeArt Framework
 * @since 		3.0
 * @version		4.2
 */

namespace WeCodeArt;

defined( 'ABSPATH' ) || exit(); 

use WeCodeArt\Singleton;
use WeCodeArt\Core\Search;
use WeCodeArt\Core\Content;
use WeCodeArt\Markup\SVG;
use WeCodeArt\Markup\Walkers\Menu;

/**
 * General Hooks
 */
class Core {

	use Singleton;

	/**
	 * Send to Constructor
	 *
	 * @since 3.6.2
	 */
	public function init() {
		add_filter( 'body_class',		[ $this, 'body_classes' ] );
		add_filter( 'post_class',		[ $this, 'post_classes' ] );
		add_filter( 'get_search_form',	[ $this, 'search_form' 	] );
		add_filter( 'wp_nav_menu_args', [ $this, 'menu_args' 	] );

		Core\Header		::get_instance();
		Core\Content	::get_instance();
		Core\Archive	::get_instance();
		Core\Blog		::get_instance();
		Core\Scripts	::get_instance();
		Core\Entry		::get_instance();
		Core\Pagination	::get_instance();
		Core\Footer		::get_instance();
	}
	
	/**
	 * Adds custom classes to the array of body classes.
	 *
	 * @version 4.0.6
	 *
	 * @param 	array 	$classes Classes for the body element.
	 *
	 * @return 	array
	 */
	public function body_classes( $classes ) {
		// Add a class of hfeed to non-singular pages.
		if ( ! is_singular() ) {
			$classes[] = 'hfeed';
		}
		
		// Adds a class of group-blog to blogs with more than 1 published author.
		if ( is_multi_author() ) {
			$classes[] = 'group-blog';
		}
		
		$modules = Content::get_contextual_options()['modules'];

		// Add sidebar class
		if( in_array( 'primary', $modules, true ) || in_array( 'secondary', $modules, true ) ) {
			$classes[] = 'page--has-sidebar';
		} else {
			$classes[] = 'page--no-sidebar';
		}
		
		// Singular sidebar class if gutenberg wide/full layout/
		if( wecodeart_if( 'is_full_layout' ) ) { 
			$classes = array_diff( $classes, [ 'has-sidebar', 'page--has-sidebar' ] );
			$classes[] = 'page--full-width';
		} 

		// Return Classes.
		$classes = array_map( 'sanitize_html_class', $classes );

		return $classes;
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
		
		// Add "entry" to the post class array.
		$classes[] = 'entry';

		// Remove "hentry" from post class array.
		$classes = array_diff( $classes, [ 'hentry' ] );
		
		return $classes;
	}
	
	/**
	 * Filter Search form HTML Markup.
	 * 
	 * @since 	unknown
	 * @version 3.9.5
	 * 
	 * @return 	string $form
	 */
	public function search_form() {
		/**
		 * Allow the default form args to be filtered.
		 *
		 * @since 	3.9.3
		 * @version	3.9.5
		 *
		 * @param string The form args.
		 */
		$query_or_placeholder = esc_attr(
			apply_filters( 'the_search_query', get_search_query() )
		) ?: sprintf( __( 'Search this website %s', 'wecodeart' ), '&#x02026;' );

		$form = new Search( apply_filters( 'wecodeart/filter/search_form/i18n', [
			'input'		=> $query_or_placeholder,
			'button'	=> SVG::compile( 'search' ),
		] ) );

		/**
		 * Allow the form output to be filtered.
		 *
		 * @since 3.9.3
		 *
		 * @param string The form markup.
		 */
		return apply_filters( 'wecodeart/filter/search_form/html', $form->get_form() );
	}

	/**
	 * Adds Walker to WP Menus by default.
	 *
	 * @since 	4.0.5
	 * @version 4.2.0
	 *
	 * @param 	array 	$args.
	 *
	 * @return 	array
	 */
	public function menu_args( $args ) {
		return wp_parse_args( [
			'container' 	 => 'nav',
			'walker' 		 => new Menu,
			'fallback_cb'	 => 'WeCodeArt\Markup\Walkers\Menu::fallback'
		], $args );
	}
}