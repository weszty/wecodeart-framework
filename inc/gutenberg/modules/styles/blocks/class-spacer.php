<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg CSS Frontend
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		5.0.0
 * @version		5.1.5
 */

namespace WeCodeArt\Gutenberg\Modules\Styles\Blocks;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Modules\Styles\Blocks as Base;
use function WeCodeArt\Functions\get_prop;

/**
 * Block CSS Processor
 */
class Spacer extends Base {
	/**
	 * Parses an output and creates the styles array for it.
	 *
	 * @return 	null
	 */
	protected function process_extra() {	
		$output 			= [];
		$output['element'] 	= $this->element;

		// Height
		if ( $value = get_prop( $this->attrs, 'height', 100 ) ) {
			// Mobile Spacer can be reduced using custom CSS variable
			$this->output[] = wp_parse_args( [
				'property' 	=> '--wp--spacer-height',
				'value'	  	=> $value . 'px',
			], $output );
		}
	}
}