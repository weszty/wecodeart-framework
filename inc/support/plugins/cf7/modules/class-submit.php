<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Support\CF7\Modules\Module
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since 		5.0.0
 * @version		5.0.0
 */

namespace WeCodeArt\Support\Plugins\CF7\Modules;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Markup;
use WeCodeArt\Singleton;

/**
 * Submit Fields.
 */
class Submit extends Module {

	use Singleton;

	/**
	 * Module vars.
	 *
	 * @var mixed
	 */
	protected $name     = 'submit';
	protected $fields   = 'submit';

	/**
	 * Return field HTML.
	 *
	 * @param   object $tag
     * 
	 * @return  string Rendered field output.
	 */
	public function get_html( $tag ) {
		$class = wpcf7_form_controls_class( $tag->type ) . ' btn';
        $class = array_filter( explode( ' ', $tag->get_class_option( $class ) ) );

        if( ! count( array_filter( $class, function( $i ) {
            return substr( $i, 0, 4 ) === 'btn-';
        } ) ) ) {
            $class[] = 'btn-primary';
        }

        $attrs = [];
        $attrs['type']      = 'submit';
        $attrs['class']		= join( ' ', $class );
        $attrs['id'] 		= $tag->get_id_option();
        $attrs['tabindex'] 	= $tag->get_option( 'tabindex', 'signed_int', true );

        $value = isset( $tag->values[0] ) ? $tag->values[0] : '';

        if ( empty( $value ) ) {
            $value = __( 'Send', 'wecodeart' );
        }

        return wecodeart_input( 'button', [
            'label' => $value,
            'attrs' => $attrs
        ], false );
	}
}
