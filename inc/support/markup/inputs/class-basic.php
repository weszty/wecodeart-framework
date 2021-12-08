<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework 
 * @subpackage 	Markup\Inputs
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		5.0.0
 * @version		5.3.1
 */

namespace WeCodeArt\Support\Markup\Inputs;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Support\Markup;
use WeCodeArt\Support\Markup\Inputs\Base;
use function WeCodeArt\Functions\get_prop;

/**
 * Standard Inputs Markup
 */
class Basic extends Base {

    /**
	 * Constructor 
	 */
	public function __construct( string $type = 'hidden', array $args = [] ) {
        $this->type         = in_array( $type, self::get_types() ) ? $type : 'hidden';
        $this->style        = get_prop( $args, 'style', 'default' );
        $this->unique_id    = wp_unique_id( 'input-' );
        $this->label        = get_prop( $args, 'label', '' );
        $this->_label       = get_prop( $args, '_label', 'before' );
        $this->attrs        = get_prop( $args, 'attrs', [] );
        $this->messages     = get_prop( $args, 'messages', [] );
    }
	
	/**
	 * Create HTML Inputs
	 *
	 * @since	unknown
	 * @version	5.0.0
	 */
	public function content() {
        ?>
        <input <?php $this->input_attrs(); ?>/>
        <?php
    }

    /**
     * Get types.
     *
     * @since   5.0.0
     */
    public static function get_types() {
        return [
            'url',
            'tel',
            'text',
            'search',
            'email',
            'range',
            'number', 
            'password',
            'submit',
            'radio',
            'checkbox',
            'date',
            'file',
            'color',
            'hidden'
        ];
    }

    /**
     * Render the custom attributes for the control's input element.
     *
     * @param 	array   $ommit Attributes to exclude
     * @since   5.0.0
     */
    public function input_attrs( $ommit = [] ) {
        $attributes = wp_parse_args( $this->attrs, [
            'type'  => $this->type,
            'name'  => $this->unique_id,
            'id'    => $this->unique_id,
            'class' => $this->input_class()
        ] );
        
        $attributes = ! empty( $ommit ) ? array_diff_key( $attributes, array_flip( $ommit ) ) : $attributes;

        // Note to code reviews, generate_attr already escapes attrs
        echo Markup::generate_attr( $this->type, $attributes );
    }

    /**
     * Get input's class.
     *
     * @param 	array
     * @since   5.0.0
     */
    public function input_class() {
        $class = 'form-control';

        if( in_array( $this->type, [ 'radio', 'checkbox' ] ) ) {
            $class = 'form-check-input';
        }

        if( in_array( $this->type, [ 'submit', 'button' ] ) ) {
            $class = 'wp-block-button__link';
        }
        
        if( $this->type === 'range' ) {
            $class = 'form-range';
        }
        
        if( $this->type === 'color' ) {
            $class .= ' form-control-color';
        }
        
        return $class;
    }
}