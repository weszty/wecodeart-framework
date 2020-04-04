<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Customizer/Extends
 * @copyright   Copyright (c) 2020, WeCodeArt Framework
 * @since 		3.5
 * @version		3.5
 */

namespace WeCodeArt\Customizer\Extender;

defined( 'ABSPATH' ) || exit();

use WP_Customize_Panel;

if ( class_exists( 'WeCodeArt\Customizer\Extender\Panel' ) ) return NULL;

/**
 * Custom Panel
 */
class Panel extends WP_Customize_Panel {
	/**
	 * Panel
	 *
	 * @since 3.5
	 * @var string
	 */
	public $panel;

	/**
	 * Control type.
	 *
	 * @since  	3.5
	 * @var 	string
	 */
	public $type = 'wecodeart-panel';

	/**
	 * Get panel parameters for JS.
	 *
	 * @since 	3.5
	 *
	 * @return 	array Exported parameters.
	 */
	public function json() {
		$array                   = wp_array_slice_assoc( (array) $this, array( 'id', 'description', 'priority', 'type', 'panel' ) );
		$array['title']          = html_entity_decode( $this->title, ENT_QUOTES, get_bloginfo( 'charset' ) );
		$array['content']        = $this->get_content();
		$array['active']         = $this->active();
		$array['instanceNumber'] = $this->instance_number;

		return $array;
	}
}