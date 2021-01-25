<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg\Classes\Columns
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		4.0.5
 * @version		4.2.0
 */

namespace WeCodeArt\Gutenberg\Modules\Classes;

defined( 'ABSPATH' ) || exit();

/**
 * Handles Gutenberg Theme Custom Classes Functionality.
 */
class Columns {

	use \WeCodeArt\Singleton;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	public function init() {
		// Editor Class Settings.
		add_filter( 'wecodeart/filter/gutenberg/settings/columns_classes', [ $this, 'global_classes' ] );
		add_filter( 'wecodeart/filter/gutenberg/settings/columns_classes', [ $this, 'breakpoints_classes' ] );
	}

	/**
	 * Add new block editor settings for custom classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function global_classes( $args ) {
		if( ! isset( $args['global'] ) ) {
			$args['global'] = [];
			$args['global'][] = 'col-auto';

			foreach( range( 1, 12 ) as $number ) {
				$args['global'][] = 'col-' . $number;
			}
		}

		return $args;
	}

	/**
	 * Add new block editor settings for custom classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function breakpoints_classes( $args ) {
		foreach( [ 'sm', 'md', 'lg', 'xl', 'xxl' ] as $breakpoint ) {
			if( ! isset( $args[$breakpoint] ) ) {
				$args[$breakpoint] = [];
				$args[$breakpoint][] = 'col-' . $breakpoint;
				$args[$breakpoint][] = 'col-' . $breakpoint . '-auto';

				foreach( range( 1, 12 ) as $number ) {
					$args[$breakpoint][] = 'col-'. $breakpoint . '-' . $number;
				}
			}
		}

		return $args;
	}
}
