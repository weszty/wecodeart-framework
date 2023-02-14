<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework 
 * @subpackage 	Markup\Inputs
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since		5.0.0
 * @version		5.7.2
 */

namespace WeCodeArt\Support\Markup\Inputs;

defined( 'ABSPATH' ) || exit();

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
        $this->type         = $type;
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
     *
     * @return  array
     */
    public static function get_types(): array {
        return [
            'url',
            'tel',
            'text',
            'search',
            'email',
            'number', 
            'password',
            'submit',
            'radio',
            'checkbox',
            'date',
            'hidden',
            // 'file',  // Extended
            // 'range', // Extended
            // 'color', // Extended
        ];
    }

    /**
     * Get input's class.
     * 
     * @since   5.0.0
     * @version 5.7.2
     *
     * @param 	string
     */
    public function input_class(): string {
        $class = 'form-control';

        if( in_array( $this->type, [ 'radio', 'checkbox' ] ) ) {
            $class = 'form-check-input';
        }

        if( in_array( $this->type, [ 'submit', 'button' ] ) ) {
            $class = 'wp-element-button';
        }
        
        return $class;
    }

    /**
	 * Input styles.
	 *
	 * @return 	string
	 */
	public static function styles(): string {
		return '
            /* Controls */
            .form-control {
                display: block;
                width: 100%;
                padding: 0.375rem 0.75rem;
                font-size: 1rem;
                font-weight: 400;
                line-height: 1.5;
                color: var(--wp--preset--color--dark);
                background-color: var(--wp--preset--color--white);
                background-clip: padding-box;
                border: var(--wp--input--border);
                border-radius: var(--wp--input--border-radius);
                transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
                appearance: none;
            }
            .form-control:focus {
                color: var(--wp--preset--color--dark);
                background-color: var(--wp--preset--color--white);
                border-color: #91c4f6;
                box-shadow: 0 0 0 0.25rem rgba(35, 136, 237, 0.25);
                outline: 0;
            }
            .form-control::placeholder {
                color: #6c757d;
                opacity: 1;
            }
            .form-control:disabled {
                background-color: #e9ecef;
                opacity: 1;
            }
            .form-control-sm {
                min-height: calc(1.5em + 0.5rem + 2px);
                padding: 0.25rem 0.5rem;
                font-size: 0.875rem;
                border-radius: calc(0.75 * var(--wp--input--border-radius));
            }
            .form-control-lg {
                min-height: calc(1.5em + 1rem + 2px);
                padding: 0.5rem 1rem;
                font-size: 1.25rem;
                border-radius: calc(1.25 * var(--wp--input--border-radius));
            }
            .form-control-plaintext {
                display: block;
                width: 100%;
                padding: 0.375rem 0;
                margin-bottom: 0;
                line-height: 1.5;
                color: var(--wp--preset--color--dark);
                background-color: transparent;
                border: solid transparent;
                border-width: 1px 0;
            }
            .form-control-plaintext:focus {
                outline: 0;
            }
            .form-control-plaintext.form-control-sm,
            .form-control-plaintext.form-control-lg {
                padding-right: 0;
                padding-left: 0;
            }

            /* Validation */
            .was-validated .form-control:valid,
            .form-control.is-valid {
                border-color: #7dc855;
                padding-right: calc(1.5em + 0.75rem);
                background-image: url("data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 8 8%27%3e%3cpath fill=%27%237dc855%27 d=%27M2.3 6.73.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z%27/%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right calc(0.375em + 0.1875rem) center;
                background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            }
            .was-validated .form-control:valid:focus,
            .form-control.is-valid:focus {
                border-color: #7dc855;
            }
            .was-validated .form-control:invalid,
            .form-control.is-invalid {
                border-color: #dc3545;
                padding-right: calc(1.5em + 0.75rem);
                background-image: url("data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 12 12%27 width=%2712%27 height=%2712%27 fill=%27none%27 stroke=%27%23dc3545%27%3e%3ccircle cx=%276%27 cy=%276%27 r=%274.5%27/%3e%3cpath stroke-linejoin=%27round%27 d=%27M5.8 3.6h.4L6 6.5z%27/%3e%3ccircle cx=%276%27 cy=%278.2%27 r=%27.6%27 fill=%27%23dc3545%27 stroke=%27none%27/%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right calc(0.375em + 0.1875rem) center;
                background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            }
            .was-validated .form-control:invalid:focus,
            .form-control.is-invalid:focus {
                border-color: #dc3545;
                box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
            }
                
            @media (prefers-reduced-motion: reduce) {
                .form-control {
                    transition: none;
                }
            }
        ';
	}
}