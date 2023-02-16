<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Support\Plugins\CF7\is_cf7_active
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since 		5.0.0
 * @version		5.7.2
 */

namespace WeCodeArt\Support\Plugins\CF7;

defined( 'ABSPATH' ) || exit(); 

use WeCodeArt\Conditional\Interfaces\ConditionalInterface;

/**
 * Conditional that is only met when plugin is active.
 */
class Condition implements ConditionalInterface {

	/**
	 * @inheritdoc
	 */
	public function is_met() {
		return defined( 'WPCF7_VERSION' );
	}
}