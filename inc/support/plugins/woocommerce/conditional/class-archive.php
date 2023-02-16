<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Support\Plugins\WooCommerce\Conditional\is_woocommerce_archive
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since 		4.0.1
 * @version		5.7.2
 */

namespace WeCodeArt\Support\Plugins\WooCommerce\Conditional;

defined( 'ABSPATH' ) || exit(); 

use WeCodeArt\Conditional\Interfaces\ConditionalInterface;

/**
 * Conditional that is only met when in the front page.
 */
class Archive implements ConditionalInterface {

	/**
	 * @inheritdoc
	 */
	public function is_met() {
		if ( wecodeart_if( 'is_woocommerce_active' ) ) {
			if ( \is_shop() || \is_product_taxonomy() || \is_product_category() || \is_product_tag() ) {
				return true;
			}
		}

		return false;
	}
}