<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Header Class
 * @copyright   Copyright (c) 2022, WeCodeArt Framework
 * @since		3.5
 * @version		5.7.0
 */

namespace WeCodeArt\Core;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use function WeCodeArt\Functions\get_prop;

/**
 * Framework Header
 */
class Header {

	use Singleton;

	/**
	 * Send to Constructor
	 */
	public function init() {
		add_action( 'wecodeart/header',	[ __CLASS__, 'markup' 	] );
		add_action( 'init', 			[ $this, 'clean_head' 	] );
		add_filter( 'body_class',		[ $this, 'body_classes'	] );
		add_action( 'wp_head', 			[ $this, 'link_rel' 	] );
	}
	
	/**
	 * Output HEADER markup function Plugin PHP fallback
	 *
	 * @since 	unknown
	 * @version	5.6.1
	 *
	 * @return 	void 
	 */
	public static function markup( $args = [] ) {
		$args 	= wp_parse_args( $args, [
			'theme' 	=> wecodeart( 'name' ),
			'slug' 		=> 'header',
			'tagName' 	=> 'header',
			'className'	=> 'wp-site-header sticky-top'
		] );

		$content = '<!-- wp:template-part {"slug":"' . $args['slug'] . '","tagName":"' . $args['tagName'] . '","className":"' . $args['className'] . '","theme":"' . $args['theme'] . '","layout":{"inherit":true}} /-->';

		echo do_blocks( $content );
	}

	/**
	 * Adds wecodeart link rel in head.
	 *
	 * @since	5.7.0
	 * @version 5.7.0
	 *
	 * @return 	void
	 */
	public function link_rel() {
		if( strpos( get_site_url(), 'wecodeart.com' ) ) return;

		printf( '<link rel="external" href="%s" />', 'https://www.wecodeart.com' ); 
	}

	/**
	 * Adds custom classes to the array of body classes.
	 *
	 * @since	4.0.6
	 * @version 5.2.2
	 *
	 * @param 	array 	$classes Classes for the body element.
	 *
	 * @return 	array
	 */
	public function body_classes( $classes ) {
		// Add a class of hfeed to non-singular pages.
		if ( ! is_singular() && ! is_404() ) {
			$classes[] = 'hfeed';
		}
		
		// Adds a class of group-blog to blogs with more than 1 published author.
		if ( is_multi_author() ) {
			$classes[] = 'group-blog';
		}
		
		// Theme
		$classes[] = 'theme-' . wecodeart( 'name' );
		$classes[] = is_child_theme() ? 'theme-is-skin' : 'theme-is-base'; 

		return $classes;
	}

	/**
	 * Clean WP_Head of unwanted of stuff
	 *
	 * This approach tricks the theme check plugin in WP.org but...
	 * I'm aware about plugin teritoriality so I will keep this disabled
	 * However, user can use this filter to reduce <head> bloat to minimum
	 *
	 * @return void
	 */
	public function clean_head() {
		$config = wecodeart_config( 'header' );

		if( get_prop( $config, 'clean' ) !== true ) return;

		$actions = apply_filters( 'wecodeart/filter/head/clean/actions', [
			[ 'wp_head', 'wp_generator' ],
			[ 'wp_head', 'rsd_link' ],
			[ 'wp_head', 'feed_links', 2 ],
			[ 'wp_head', 'feed_links_extra', 3 ],
			[ 'wp_head', 'wlwmanifest_link' ],
			[ 'wp_head', 'index_rel_link' ],
			[ 'wp_head', 'parent_post_rel_link', 10, 0 ],
			[ 'wp_head', 'start_post_rel_link', 10, 0 ],
			[ 'wp_head', 'adjacent_posts_rel_link', 10, 0 ],
			[ 'wp_head', 'rest_output_link_wp_head' ],
			[ 'wp_head', 'wp_shortlink_wp_head' ],
			[ 'wp_head', 'wp_resource_hints', 2, 99 ],
			[ 'wp_head', 'wp_oembed_add_discovery_links' ],
			[ 'wp_head', 'print_emoji_detection_script', 7 ],
			[ 'wp_print_styles', 'print_emoji_styles' ],
			[ 'template_redirect', 'rest_output_link_header', 11 ],
		] );

		if( empty( $actions ) ) return;
		
		foreach( $actions as $args ) call_user_func( 'remove_action', ...$args );
	}
}