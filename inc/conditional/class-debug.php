<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Conditional\is_dev_mode
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since 		4.0.1
 * @version		4.0.1
 */

namespace WeCodeArt\Conditional;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Conditional\Interfaces\ConditionalInterface;

/**
 * Conditional that is only met when in dev mode.
 */
class Debug implements ConditionalInterface {

	/**
	 * @inheritdoc
	 */
	public function is_met() {
		return \defined( 'WP_DEBUG' ) && \WP_DEBUG || \defined( 'SCRIPT_DEBUG' ) && \SCRIPT_DEBUG;
	}
}