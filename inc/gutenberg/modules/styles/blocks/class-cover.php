<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg CSS Frontend
 * @copyright   Copyright (c) 2022, WeCodeArt Framework
 * @since		5.0.0
 * @version		5.4.7
 */

namespace WeCodeArt\Gutenberg\Modules\Styles\Blocks;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Gutenberg\Modules\Styles\Blocks as Base;
use function WeCodeArt\Functions\get_prop;

/**
 * Block CSS Processor
 */
class Cover extends Base {
	/**
	 * Parses an output and creates the styles array for it.
	 *
	 * @return 	null
	 */
	protected function process_extra() {		
		$output 			= [];
		$output['element'] 	= $this->element;

		// Block Attributes
		if ( $value = get_prop( $this->attrs, 'focalPoint' ) ) {
			$this->output[] = wp_parse_args( [
				'element'	=> [
					join( '>', [ $this->element, '.wp-block-cover__image-background' ] ), 
					join( '>', [ $this->element, '.wp-block-cover__video-background' ] )
				],
				'property' 	=> 'object-position',
				'value'	  	=> $value
			], $output );
		}

		if ( $value = get_prop( $this->attrs, 'minHeight' ) ) {
			$this->output[] = wp_parse_args( [
				'property' 	=> 'min-height',
				'value'	  	=> $value,
				'units'		=> get_prop( $this->attrs, 'minHeightUnit', 'px' )
			], $output );
		}

		if ( $value = get_prop( $this->attrs, 'url' ) ) {
			$this->output[] = wp_parse_args( [
				'property' 	=> 'background-image',
				'value'	  	=> $value,
			], $output );
		}

		if ( $value = get_prop( $this->attrs, 'customOverlayColor' ) ) {
			$this->output[] = wp_parse_args( [
				'property' 	=> 'background-color',
				'value'	  	=> $value
			], $output );
		}
		
		if ( $value = get_prop( $this->attrs, 'hasParallax' ) ) {
			$this->output[] = wp_parse_args( [
				'property' 	=> 'background-attachment',
				'value'	  	=> 'fixed'
			], $output );
		}

		if ( $value = get_prop( $this->attrs, 'customGradient' ) ) {
			$this->output[] = wp_parse_args( [
				'element'	=> join( '>', [ $this->element, '.has-background-gradient' ] ),
				'property' 	=> 'background-image',
				'value'	  	=> $value,
			], $output );
		}
	}
}