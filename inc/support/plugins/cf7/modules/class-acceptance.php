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
 * Acceptance Fields.
 */
class Acceptance extends Module {

	use Singleton;

	/**
	 * Module vars.
	 *
	 * @var mixed
	 */
	protected $name     = 'acceptance';
	protected $fields   = 'acceptance';

	/**
	 * Return field HTML.
	 *
	 * @param   object $tag
     * 
	 * @return  string Rendered field output.
	 */
	public function get_html( $tag ) {
		if ( empty( $tag->name ) ) {
            return '';
        }
        
        $attrs = [];

        $validation_error   = wpcf7_get_validation_error( $tag->name );
        $class              = explode( ' ', wpcf7_form_controls_class( $tag->type, $tag->has_option( 'switch' ) ? 'form-switch' : '' ) );
        
        if ( $validation_error ) {
            $class[]= 'wpcf7-not-valid';
            $attrs['aria-invalid'] = 'true';
            $attrs['aria-describedby'] = wpcf7_get_validation_error_reference( $tag->name );
        } else {
            $attrs['aria-invalid'] = 'false';
        }
    
        if ( $tag->has_option( 'invert' ) ) {
            $class[] = 'invert';
        }
    
        if ( $tag->has_option( 'optional' ) ) {
            $class[] = 'optional';
        }

        $attrs['id']        = $tag->get_id_option();
        $attrs['class']     = $tag->get_class_option( join( ' ', $class ) );
        $attrs['name']      = $tag->name;
        $attrs['value']     = '1';
        $attrs['tabindex']  = $tag->get_option( 'tabindex', 'signed_int', true );
    
        if ( $tag->has_option( 'default:on' ) ) {
            $attrs['checked'] = 'checked';
        }
        
        $html = Markup::wrap( 'cf7-acceptance-field', [ [
            'tag' 	=> 'span',
            'attrs' => [
                'class' => self::get_wrap_class( $tag )
            ]
        ] ], 'wecodeart_input', [ 'toggle', [
            'type'      => 'checkbox',
            'label'     => trim( empty( $tag->content ) ? (string) reset( $tag->values ) : $tag->content ),
            'attrs' 	=> $attrs,
            'messages' 	=> [
                'invalid' => $validation_error
            ]
        ] ], false );

        return $html;
	}
}
