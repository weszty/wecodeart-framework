<?php namespace WeCodeArt\Support;
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
// Use
use WeCodeArt\Utilities\Markup;
use WeCodeArt\Utilities\Helpers;
use WeCodeArt\Core\Callbacks; 
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Support\Yoast SEO
 * @copyright   Copyright (c) 2019, WeCodeArt Framework
 * @since 		v3.5
 * @version		v3.6.2
 */
class WordPressSeo {
	use \WeCodeArt\Singleton; 

	/**
	 * Send to Constructor
	 * @since 3.6.2
	 */
	public function init() {
		if( apply_filters( 'wecodeart/filter/support/breadcrumbs/woocommerce-yoast', true )
		&& Helpers::detectplugin( [ 'classes' => [ 'woocommerce' ] ] ) ) {
			remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
		}

		add_action( 'wecodeart/hook/inner/top', [ $this, 'render_yoast_breadcrumbs' ], 30 );
	}

	/**
	 * Yoast BreadCrumbs
	 * @since   3.5
	 * @version 3.6.0.4
	 * @return  void
	 */
	public function render_yoast_breadcrumbs() {
		if( ! function_exists( 'yoast_breadcrumb' ) || Callbacks::_is_frontpage() ) return;

		$options = \WeCodeArt\Core\Content::get_contextual_options(); 

		$wrappers = [
			[ 'tag' => 'div', 'attrs' => [ 'class' => 'breadcrumbs' ] ],
			[ 'tag' => 'div', 'attrs' => [ 'class' => $options['container'] ] ],
			[ 'tag' => 'div', 'attrs' => [ 'class' => 'row' ] ],
			[ 'tag' => 'div', 'attrs' => [ 'class' => 'col' ] ]
		];
		
		Markup::wrap( 
			'breadcrumbs', 	// Context
			$wrappers, 		// Wrappers
			// Function to wrap
			'yoast_breadcrumb',
			// Function Arguments
			[ '<div class="breadcrumbs__list">', '</div>' ]
		); 
	}
}