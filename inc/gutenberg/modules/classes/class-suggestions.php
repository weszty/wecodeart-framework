<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg\Classes\Suggestions
 * @copyright   Copyright (c) 2020, WeCodeArt Framework
 * @since		4.0.5
 * @version		4.0.7
 */

namespace WeCodeArt\Gutenberg\Modules\Classes;

defined( 'ABSPATH' ) || exit();

/**
 * Handles Gutenberg Theme Custom Classes Functionality.
 */
class Suggestions {

	use \WeCodeArt\Singleton;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	public function init() {
		// Editor Class Settings.
		add_filter( 'wecodeart/filter/gutenberg/settings', [ $this, 'set_classes' ], 10, 2 );
		add_filter( 'wecodeart/filter/gutenberg/settings/custom_classes', [ $this, 'font_classes' ] );
		add_filter( 'wecodeart/filter/gutenberg/settings/custom_classes', [ $this, 'flex_classes' ] );
		add_filter( 'wecodeart/filter/gutenberg/settings/custom_classes', [ $this, 'display_classes' ] );
		add_filter( 'wecodeart/filter/gutenberg/settings/custom_classes', [ $this, 'spacing_classes' ] );
		add_filter( 'wecodeart/filter/gutenberg/settings/custom_classes', [ $this, 'offset_order_classes' ] );
	}

	/**
	 * Add new block editor settings for custom classes.
	 *
	 * @param array  $settings 	The editor settings.
	 * @param object $post 		The post being edited.
	 *
	 * @return array Returns updated editors customClasses settings.
	 */
	public function set_classes( $settings, $post ) {
		if ( ! isset( $settings[ 'customClasses' ] ) ) {
			$classes  = apply_filters( 'wecodeart/filter/gutenberg/settings/custom_classes', [], $post );
			$settings[ 'customClasses' ] = array_map( 'sanitize_html_class', $classes );
		}

		return $settings;
	}

	/**
	 * Add new block editor settings for custom classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function font_classes( $args ) {
		foreach( [ 'normal', 'lighter', 'light', 'bold', 'bolder' ] as $weight ) {
			$args[] = 'font-weight-'. $weight;
		}
		
		foreach( [ 'monospace', 'decoration-none', 'reset', 'lowercase', 'uppercase' ] as $type ) {
			$args[] = 'text-' . $type;
		}

		foreach( [ 'muted', 'primary', 'secondary', 'dark', 'light', 'warning', 'danger', 'success' ] as $color ) {
			$args[] = 'text-' . $color;
		}

		foreach( [ 'sm', 'md', 'lg', 'xl' ] as $break ) {
			foreach( [ 'left', 'right', 'center', 'justify' ] as $align ) {
				$args[] = 'text-' . $break . '-' . $align;
			}
		}

		foreach( range( 1, 5 ) as $nr ) {
			$args[] = 'display-'. $nr;
		}

		return wp_parse_args( [
			'lead',
			'text-left',
			'text-center',
			'text-right',
			'text-justify',
		], $args );
	}

	/**
	 * Add new block editor settings for custom classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function display_classes( $args ) {
		foreach( [ 'sm', 'md', 'lg', 'xl' ] as $break ) {
			foreach( [ 'none', 'block', 'inline-block', 'flex', 'inline-flex', 'table', 'table-cell', 'table-row' ] as $type ) {
				$args[] = 'd-' . $break . '-' . $type;
			}
		}

		return wp_parse_args( [
			'd-none',
			'd-block',
			'd-inline-block',
			'd-flex',
			'd-inline-flex',
			'd-table',
			'd-table-row',
			'd-table-cell',
		], $args );
	}

	/**
	 * Add new block editor settings for custom classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function spacing_classes( $args ) {
		foreach( [ 'm', 'p' ] as $space ) {
			foreach( [ 'x', 'y', 't', 'b', 'l', 'r' ] as $dir ) {
				foreach( [ '0', '1', '2', '3', '4', '5', 'auto' ] as $size ) {
					if( in_array( $dir, [ 't', 'b', 'y' ] ) && $size === 'auto' ) continue;
					$args[] = implode( '-', [ $space . $dir, $size ] );
				}
				foreach( [ 'sm', 'md', 'lg', 'xl' ] as $break ) {
					foreach( [ '0', '1', '2', '3', '4', '5', 'auto' ] as $size ) {
						if( in_array( $dir, [ 't', 'b', 'y' ] ) && $size === 'auto' ) continue;
						$args[] = implode( '-', [ $space . $dir, $break, $size ] );
					}
				}
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
	public function offset_order_classes( $args ) {
		foreach( range( 1, 12 ) as $column ) {
			$args[] = 'offset-' . $column;
			$args[] = 'order-' . $column;
		}
		
		foreach( [ 'sm', 'md', 'lg', 'xl' ] as $break ) {
			foreach( range( 1, 12 ) as $column ) {
				$args[] = 'order-' . $break . '-first';
				$args[] = 'order-' . $break . '-last';
				$args[] = 'order-' . $break . '-' . $column;
				$args[] = 'offset-' . $break . '-' . $column;
			}
		}

		return wp_parse_args( [
			'order-first',
			'order-last'
		], $args );
	}

	/**
	 * Add new block editor settings for custom classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function flex_classes( $args ) {
		foreach( [ 'sm', 'md', 'lg', 'xl' ] as $break ) {

			foreach( [ 'row', 'row-reverse', 'column', 'column-reverse' ] as $dir ) {
				$args[] = 'flex-' . $break . '-' . $dir ;
			}

			foreach( [ 'nowrap', 'wrap', 'wrap-reverse' ] as $wrap ) {
				$args[] = 'flex-' . $break . '-' . $wrap ;
			}

			foreach( [ 'grow', 'shrink' ] as $grow ) {
				$args[] = 'flex-' . $break . '-' . $grow . '-0';
				$args[] = 'flex-' . $break . '-' . $grow . '-1';
			}

			foreach( [ 'start', 'end', 'center', 'between', 'around' ] as $justify ) {
				$args[] = 'justify-content-' . $break . '-' . $justify;
			}

			foreach( [ 'items', 'self' ] as $align ) {
				foreach( [ 'start', 'end', 'center', 'baseline', 'stretch' ] as $type ) {
					$args[] = 'align-' . $align . '-' . $break . '-' . $type ;
				}
			}
			
			foreach( [ 'start', 'end', 'center', 'around', 'stretch' ] as $type ) {
				$args[] = 'align-content-' . $break . '-' . $type ;
			}
		}

		return wp_parse_args( [
			// Direction
			'flex-row',
			'flex-row-reverse',
			'flex-column',
			'flex-column-reverse',
			// SIze
			'flex-grow-0',
			'flex-grow-1',
			// Wrap
			'flex-nowrap',
			'flex-wrap',
			'flex-wrap-reverse',
			// Justify
			'justify-content-start',
			'justify-content-end',
			'justify-content-center',
			'justify-content-between',
			'justify-content-around',
			// Align - Items
			'align-items-start',
			'align-items-end',
			'align-items-center',
			'align-items-baseline',
			'align-items-stretch',
			// Align - Self
			'align-self-start',
			'align-self-end',
			'align-self-center',
			'align-self-baseline',
			'align-self-stretch',
			// Align - Content
			'align-content-start',
			'align-content-end',
			'align-content-center',
			'align-content-around',
			'align-content-stretch',
		], $args );
	}
}
