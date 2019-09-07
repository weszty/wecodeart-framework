<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Core\Hooks
 * @copyright   Copyright (c) 2019, WeCodeArt Framework
 * @since 		3.0
 * @version		3.9.5
 */

namespace WeCodeArt\Core;

defined( 'ABSPATH' ) || exit(); 

use WeCodeArt\Core\Search;
use WeCodeArt\Core\Content;
use WeCodeArt\Utilities\Callbacks;
use WeCodeArt\Utilities\Markup\SVG;

/**
 * General Hooks
 */
class Hooks {

	use \WeCodeArt\Singleton;

	/**
	 * Send to Constructor
	 * @since 3.6.2
	 */
	public function init() {
		add_filter( 'body_class',		array( $this, 'body_classes'	) );
		add_filter( 'post_class',		array( $this, 'post_classes'	) );
		add_filter( 'get_search_form',	array( $this, 'search_form' 	) );
	}
	
	/**
	 * Adds custom classes to the array of body classes.
	 *
	 * @version 3.9.5
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

		// Add sidebar class/
		if( in_array( 'primary', $modules, true ) || in_array( 'secondary', $modules, true ) ) {
			$classes[] = 'has-sidebar';
		} else {
			$classes[] = 'no-sidebar';
		}
		
		// Singular sidebar class if gutenberg wide/full layout/
		if( Callbacks::is_full_content() ) { 
			$classes = array_diff( $classes, [ 'has-sidebar' ] );
			$classes[] = 'gutenberg-disabled-sidebar'; 
			$classes[] = 'no-sidebar'; 
		} 

		// Return Classes.
		$classes = array_map( 'sanitize_html_class', $classes );

		return $classes;
	}
	
	/**
	 * Filter the Custom Logo markup.
	 *
	 * @return html
	 */
	public function custom_logo() {
		_deprecated_function( __FUNCTION__, '3.9.5' );	
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
		$query_or_placeholder =  esc_attr( 
			apply_filters( 'the_search_query', get_search_query() ) 
		) ?: sprintf( __( 'Search this website %s', wecodeart_config( 'textdomain' ) ), '&#x02026;' );

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
}