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
 * @version		5.3.1
 */

namespace WeCodeArt\Support\Plugins\CF7\Modules;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Singleton;

/**
 * Select Fields.
 */
class Select extends Module {

	use Singleton;

	/**
	 * Module vars.
	 *
	 * @var mixed
	 */
	protected $name     = 'select';
	protected $fields   = [ 'select', 'select*' ];

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
        $class              = explode( ' ', wpcf7_form_controls_class( $tag->type, 'form-select' ) );
        
        if ( $validation_error ) {
            $class[] = 'wpcf7-not-valid';
            $attrs['aria-invalid'] = 'true';
            $attrs['aria-describedby'] = wpcf7_get_validation_error_reference( $tag->name );
        } else {
            $attrs['aria-invalid'] = 'false';
        }
        
        $attrs['class']		= $tag->get_class_option( join( ' ', $class ) );
        $attrs['tabindex'] 	= $tag->get_option( 'tabindex', 'signed_int', true );

        if( $tag->get_id_option() ) { 
            $attrs['id']    = $tag->get_id_option();
        }
    
        if ( $tag->is_required() ) {
            $attrs['required'] = true;
            $attrs['aria-required'] = 'true';
        }
    
        $multiple 		= $tag->has_option( 'multiple' );
        $include_blank 	= $tag->has_option( 'include_blank' );
        $placeholder    = $tag->has_option( 'placeholder' );
        $first_as_label = $tag->has_option( 'first_as_label' );
    
        if ( $tag->has_option( 'size' ) ) {
            $size = $tag->get_option( 'size', 'int', true );
    
            if ( $size ) {
                $attrs['size'] = $size;
            } elseif ( $multiple ) {
                $attrs['size'] = 4;
            } else {
                $attrs['size'] = 1;
            }
        }
    
        if ( $data = (array) $tag->get_data_option() ) {
            $tag->values = array_merge( $tag->values, array_values( $data ) );
            $tag->labels = array_merge( $tag->labels, array_values( $data ) );
        }
    
        $values = $tag->values;
        $labels = $tag->labels;
        
        $default_choice = $tag->get_default_option( null, [
            'multiple' 	=> $multiple,
            'shifted' 	=> $include_blank,
        ] );
         
        if ( $first_as_label ) {
            array_shift( $values );
            array_shift( $labels );
        }
        
        if ( $include_blank or empty( $values ) ) {
            array_unshift( $labels, '---' );
            array_unshift( $values, '' );
        } elseif ( $placeholder ) {
            $values[0] = '';
        }
        
        if ( $multiple ) {
            $attrs['multiple'] = 'multiple';
        }
        
        $hangover = wpcf7_get_hangover( $tag->name );
        if ( $hangover ) {
            $selected = $hangover;
        } else {
            $selected = $default_choice;
        }
        
        if( $selected ) {
            $attrs['value'] = $selected;
        }
            
        $label  = $first_as_label ? (string) reset( $tag->values ) : false;
        $choices = [];
        foreach ( $values as $key => $value ) $choices[ $value ] = isset( $labels[$key] ) ? $labels[$key] : $value;
    
        $attrs['name'] = $tag->name . ( $multiple ? '[]' : '' );
    
        $html = wecodeart( 'markup' )::wrap( 'cf7-select-field', [ [
            'tag' 	=> 'span',
            'attrs' => [
                'class' => self::get_wrap_class( $tag )
            ]
        ] ], 'wecodeart_input', [ 'select', [
            'label' 	=> $label,
            'attrs' 	=> $attrs,
            'choices' 	=> $choices,
            'messages' 	=> [
                'invalid' => $validation_error
            ]
        ] ], false );

        return $html;
	}

    /**
	 * Get Module args.
	 *
	 * @return array
	 */
	protected function get_args() {
		return [
			'name-attr' 		=> true,
            'selectable-values' => true,
		];
	}
}
