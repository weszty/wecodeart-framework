<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Gutenberg\Modules\Patterns
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since 		5.0.0
 * @version		5.1.3
 */

namespace WeCodeArt\Gutenberg\Modules\Patterns;

defined( 'ABSPATH' ) || exit(); 

use WeCodeArt\Conditional\Interfaces\ConditionalInterface;
use function WeCodeArt\Functions\get_prop;

/**
 * Conditional that is only met when WP Block Patters are added to WP.
 */
class Condition implements ConditionalInterface {

	/**
	 * @inheritdoc
	 */
	public function is_met() {
		return get_prop( wecodeart_config( 'gutenberg', [] ), 'patterns' );
	}
}