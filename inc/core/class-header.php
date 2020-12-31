<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Header Class
 * @copyright   Copyright (c) 2020, WeCodeArt Framework
 * @since		3.5
 * @version		4.2.0
 */

namespace WeCodeArt\Core;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Markup;

/**
 * Framework Header
 */
class Header {

	use \WeCodeArt\Singleton;

	/**
	 * Send to Constructor
	 * @since 3.6.2
	 */
	public function init() {
		add_action( 'wp_head',	[ $this, 'meta_charset' 	], 0 );
		add_action( 'wp_head',	[ $this, 'meta_viewport' 	], 0 );
		add_action( 'wp_head',	[ $this, 'meta_profile' 	], 0 );
		add_action( 'wp_head',	[ $this, 'meta_pingback'	], 0 );
		add_filter( 'wecodeart/filter/attributes/body', [ $this, 'body_attrs' 	] );
		add_action( 'wecodeart/header/markup', 			[ $this, 'markup' 		] );
	}
	
	/**
	 * Output HEADER markup function
	 *
	 * @uses	WeCodeArt\Markup::wrap()
	 * @since 	unknown
	 * @version	4.2.0
	 *
	 * @return 	void 
	 */
	public function markup() {
		Markup::wrap( 'header', [ [
			'tag' 	=> 'header',
			'attrs' => [
				'id' 		=> 'header', 
				'class'		=> 'header', 
				'itemscope' => 'itemscope',
				'itemtype' 	=> 'https://schema.org/WPHeader'
			]
		] ], function() {
			/** 
			 * @hook	'wecodeart/hook/header/top'
			 */
			do_action( 'wecodeart/hook/header/top' );

			/**
			 * Render Header Bar
			 */
			self::render_header_bar();

			/** 
			 * @hook	'wecodeart/hook/header/bottom'
			 */	 
			do_action( 'wecodeart/hook/header/bottom' );
		} );
	}

	/**
	 * Header Branding View
	 *
	 * @uses	WeCodeArt\Markup::wrap()
	 * @since 	???
	 * @version	4.0.6
	 *
	 * @return 	void
	 */
	public static function display_branding() {
		Markup::wrap( 'header-branding', [ [
			'tag' 	=> 'div',
			'attrs' => [
				'id' 	=> 'bar-branding',
				'class' => 'header-bar__branding col col-lg-auto'
			] 
		], [
			'tag' 	=> 'div',
			'attrs' => [
				'class' => 'row no-gutters align-items-center'
			]
		] ], 'WeCodeArt\Markup::template', [ 'header/bar-branding' ] );
	}

	/**
	 * Header Menu View
	 *
	 * @uses	WeCodeArt\Markup::wrap()
	 * @since 	unknown
	 * @version	4.0.5
	 *
	 * @return 	void 
	 */
	public static function display_menu() { 
		Markup::wrap( 'header-menu', [ [
			'tag' 	=> 'div',
			'attrs' => [
				'id' 	=> 'bar-menu',
				'class' => 'header-bar__menu col-12 col-lg'
			]
		] ], 'wp_nav_menu', [ apply_filters( 'wecodeart/filter/menu/main', [
			'theme_location' => 'primary',
			'menu_class' 	 => 'menu nav justify-content-end',
			'depth' 		 => 10,
		] ) ] );
	}

	/**
	 * Header Search View
	 *
	 * @uses	WeCodeArt\Markup::wrap()
	 * @since 	unknown
	 * @version	4.1.4
	 * 
	 * @return 	void
	 */
	public static function display_search() { 
		Markup::wrap( 'header-search', [ [
			'tag' 	=> 'div',
			'attrs' => [
				'id' 	=> 'bar-search',
				'class' => 'header-bar__search col-12 col-lg-4 ml-auto'
			]
		] ], 'get_search_form' ); 
	}

	/**
	 * Generate Body Attrs
	 *
	 * @since 	3.5
	 * @version 3.5
	 *
	 * @return 	array 
	 */
	public function body_attrs( $args = [] ) {
		// Set up blog variable.
		$blog = ( is_home() || is_archive() || is_attachment() || is_tax() || is_single() ) ? true : false;
		// Set up default itemtype.
		$itemtype = 'WebPage';
		// Get itemtype for the blog.
		$itemtype = ( $blog ) ? 'Blog' : $itemtype;
		// Get itemtype for search results.
		$itemtype = ( is_search() ) ? 'SearchResultsPage' : $itemtype;

		$defaults = [
			'class'		=> implode( ' ', get_body_class() ),
			'itemscope' => true,
			'itemtype' 	=> 'https://schema.org/' . $itemtype
		];

		$args = wp_parse_args( $args, $defaults );

		return $args;
	}

	/**
	 * Variable that holds the Header Modules and Options
	 *
	 * @since	1.5
	 * @version	3.9.9
	 *
	 * @return 	array
	 */
	public static function nav_bar_modules() {
		$defaults = [
			'branding' => [
				'label'    => esc_html__( 'Site Branding', 'wecodeart' ),
				'callback' => [ __CLASS__, 'display_branding' ]
			],
			'menu' => [
				'label'    => esc_html__( 'Primary Menu', 'wecodeart' ),
				'callback' => [ __CLASS__, 'display_menu' ]
			],
			'search' => [
				'label'    => esc_html__( 'Search Form', 'wecodeart' ),
				'callback' => [ __CLASS__, 'display_search' ]
			],
		];

		return apply_filters( 'wecodeart/filter/header/bar/modules', $defaults ); 
	}

	/**
	 * Returns the inner markp with wrapper based on user options
	 *
	 * @uses	WeCodeArt\Markup::wrap()
	 * @uses	WeCodeArt\Markup::sortable()
	 * @since 	unknown
	 * @version	4.2.0
	 *
	 * @return 	void
	 */
	public static function render_header_bar() {
		Markup::wrap( 'header-bar', [
			[ 'tag' => 'div', 'attrs' => [ 'class' => 'header__bar header-bar', 'id' => 'header-bar' ] ],
			[ 'tag' => 'div', 'attrs' => [ 'class' => get_theme_mod( 'header-bar-container' ) ] ],
			[ 'tag' => 'div', 'attrs' => [ 'class' => 'row align-items-center' ] ]
		], 'WeCodeArt\Markup::sortable', [
			self::nav_bar_modules(),
			get_theme_mod( 'header-bar-modules' )
		] );  
	}

	/**
	 * Add a pingback url auto-discovery wp_head for singularly identifiable articles
	 *
	 * @since	2.2
	 * @version	4.2.0
	 */
	public function meta_pingback() {
		if ( is_singular() && pings_open( get_queried_object() ) ) {
			printf( '<link rel="pingback" href="%s" />' . "\n", get_bloginfo( 'pingback_url' ) );
		}
	}

	/**
	 * Add a meta viewport printed in wp_head
	 *
	 * @since	2.2.x
	 * @version 3.9.5
	 */
	public function meta_viewport() {
		$viewport = apply_filters( 'wecodeart/filter/viewport', 'width=device-width, initial-scale=1' );
		printf( '<meta name="viewport" content="%s" />' . "\n", esc_attr( $viewport ) );
	}
	
	/**
	 * Add a meta charset printed in wp_head
	 *
	 * @since	4.2.0
	 * @version 4.2.0
	 */
	public function meta_charset() {
		$charset = apply_filters( 'wecodeart/filter/meta/charset', get_bloginfo( 'charset' ) );
		printf( '<meta charset="%s" />' . "\n", esc_attr( $charset ) );
	}
	
	/**
	 * Add a meta profile printed in wp_head
	 *
	 * @since	4.2.0
	 * @version 4.2.0
	 */
	public function meta_profile() {
		$profile = apply_filters( 'wecodeart/filter/meta/profile', 'http://gmpg.org/xfn/11' );
		printf( '<link rel="profile" href="%s" />' . "\n", esc_attr( $profile ) );
	}
}