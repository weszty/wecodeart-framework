<?php namespace WeCodeArt\Core;
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit(); 
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	WeCodeArt\Core\Callbacks
 * @copyright   Copyright (c) 2019, WeCodeArt Framework
 * @since 		v3.5
 * @version		v3.6.2
 */

class Callbacks {
	use \WeCodeArt\Singleton; 

	/**
	 * Checks if the current page is a product archive
	 * @since	1.8.8
	 * @version	3.5
	 * @return 	boolean
	 */
	public static function _is_frontpage() {
		return ( is_front_page() && ! is_home() );
	}

	/**
	 * Check if on default post archive
	 * @since	3.6.1.2
	 * @return 	boolean
	 */
	public static function _is_post_archive() { 
		return ( 'post' === get_post_type() && ( is_home() || is_archive() || is_search() ) );
		return false;
	}
}