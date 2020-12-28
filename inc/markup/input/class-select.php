<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework 
 * @subpackage 	Markup\Input
 * @copyright   Copyright (c) 2020, WeCodeArt Framework
 * @since		4.2.0
 * @version		4.2.0
 */

namespace WeCodeArt\Markup\Input;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Markup;
use WeCodeArt\Markup\Input\Base;
use function WeCodeArt\Functions\get_prop;

/**
 * Standard Inputs Markup
 */
class Select extends Base {

    /**
     * Input's Type.
     *
     * @since   4.2.0
     * @var     string
     */
    public $type = 'select';

    /**
     * All choices tied to the control.
     *
     * @since   4.2.0
     * @var     array
     */
    public $choices = [];
    
    /**
	 * Constructor 
	 */
	public function __construct( string $type = 'select', array $args = [] ) {
        $this->unique_id    = wp_unique_id( 'select-' );
        $this->label        = get_prop( $args, 'label', '' );
        $this->attrs        = wp_parse_args( get_prop( $args, 'attrs', [] ), [
            'name'  => $this->unique_id,
            'id'    => $this->unique_id,
            'class' => 'form-select'
        ] );
        $this->choices      = get_prop( $args, 'choices', [] );
        $this->messages     = get_prop( $args, 'messages', [] );
    }

	/**
	 * Create HTML Inputs
	 *
	 * @since	unknown
	 * @version	4.2.0
	 */
	public function content() {
        $placeholder = isset( $this->attrs['placeholder'] ) ? $this->attrs['placeholder'] : false;
        
        ?>
        <select <?php $this->input_attrs( [ 'value', 'placeholder' ] ); ?>>
            <?php if( $placeholder ) {
            
                Markup::wrap( 'select-placeholder', [
                    [
                        'tag' => 'option',
                        'attrs' => [
                            'class'     => false,
                            'value'     => $value,
                            'disabled'  => true,
                            'selected'  => isset( $this->attrs['value'] ) ? null : true,
                        ]
                    ]
                ], $placeholder );

            } ?>
            <?php foreach( $this->choices as $value => $label ) {
            
                Markup::wrap( 'select-option', [
                    [
                        'tag' => 'option',
                        'attrs' => [
                            'class'     => false,
                            'value'     => $value,
                            'selected'  => $this->selected_option( $value ),
                        ]
                    ]
                ], $label );

            } ?>
        </select>
        <?php
    }

    /**
	 * Selected Option
     *
     * @since   4.2.0
	 * @return	boolean
	 */
	public function selected_option( string $value ) {
        return isset( $this->attrs['value'] ) ? (string) $this->attrs['value'] === (string) $value : null;
    }
}