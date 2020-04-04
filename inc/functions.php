<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework 
 * @subpackage 	Functions
 * @copyright   Copyright (c) 2020, WeCodeArt Framework
 * @since		4.1.5
 * @version     4.1.54
 */

namespace WeCodeArt\Functions;

defined( 'ABSPATH' ) || exit();

/**
 * Helper function to encode an array to an excaped json string
 * Useful to use it for placing encoded object in html attrs
 *
 * @since	3.5
 * @param 	array $json
 *
 * @return 	string 
 */
function toJSON( $json = array() ) {
    if( ! is_array( $json ) ) return null;
    $json = str_replace( '"', "'", json_encode( $json ) );
    return htmlspecialchars( $json, ENT_QUOTES, 'UTF-8' );
}

/**
 * Detect active plugin by constant, class or function existence.
 *
 * @since 	3.5
 * @param 	array 	$plugins 	Array of array for constants, classes and / or functions to check for plugin existence
 * 
 * @return 	boolean
 */
function detect_plugin( array $plugins ) {
    // Check for classes.
    if ( isset( $plugins['classes'] ) ) foreach ( $plugins['classes'] as $name ) if ( class_exists( $name ) ) return true;

    // Check for functions.
    if ( isset( $plugins['functions'] ) ) foreach ( $plugins['functions'] as $name ) if ( function_exists( $name ) ) return true;

    // Check for constants.
    if ( isset( $plugins['constants'] ) ) foreach ( $plugins['constants'] as $name ) if ( defined( $name ) ) return true;

    // No class, function or constant found to exist.
    return false;
}

/**
 * Trim CSS
 *
 * @since 	3.7.7
 * @param 	string $css CSS content to trim.
 *
 * @return 	string
 */
function trim_css( $css = '' ) {

    // Trim white space for faster page loading.
    if ( ! empty( $css ) ) {
        $css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );
        $css = str_replace( [ "\r\n", "\r", "\n", "\t", '  ', '    ', '    ' ], '', $css );
        $css = str_replace( ', ', ',', $css );
    }

    return $css;
}

/**
 * Get a specific property of an array
 *
 * @since  	3.8.1
 *
 * @return null|string|mixed The value
 */
function get_prop( $array, $prop, $default = null ) {
    if ( ! is_array( $array ) && ! ( is_object( $array ) && $array instanceof ArrayAccess ) ) return $default; 
    if ( isset( $array[ $prop ] ) ) $value = $array[ $prop ];
    else $value = ''; 
    return empty( $value ) && null !== $default ? $default : $value;
}
    
/**
 * Kses SVG
 *
 * @param 	string 	$data
 *
 * @return 	string
 */
function kses_svg( string $data ) {
    return wp_kses( $data, [ 
        'svg' => [
            'class'  		=> true,
            'aria-hidden'  	=> true,
            'role' 			=> true,
            'focusable'    	=> true,
            'xmlns'    		=> true,
            'viewbox' 		=> true,
        ],
        'g' 	=> [],
        'path' 	=> [
            'd' 	=> true,
            'class' => true,
            'fill'	=> true
        ]
    ] );
}

/**
 * WP_Parse_Args Reccursive
 *
 * @param 	array 	$a
 * @param 	array 	$b
 *
 * @return 	array
 */
function wp_parse_args_r( &$a, $b ) {
    $a = (array) $a;
    $b = (array) $b;
    $r = $b;

    foreach ( $a as $k => &$v ) {
        if ( is_array( $v ) && isset( $r[ $k ] ) ) {
            $r[ $k ] = wp_parse_args_r( $v, $r[ $k ] );
        } else {
            $r[ $k ] = $v;
        }
    }

    return $r;
}

/**
 * Calc FN
 *
 * @param 	int 	$number1
 * @param 	string 	$action
 * @param 	int 	$number2
 * @param 	bool 	$round
 * @param 	int 	$decimals
 * @param 	int 	$precision
 *
 * @return 	array
 */
function calc( $number1, $action, $number2, $round = false, $decimals = 0, $precision = 10 ) {
    static $bc;

    if ( ! is_scalar( $number1 ) || ! is_scalar( $number2 ) ) {
        return false;
    }

    if ( ! isset( $bc ) ) {
        $bc = extension_loaded( 'bcmath' );
    }

    if ( $bc ) {
        $number1 = number_format( $number1, 10, '.', '' );
        $number2 = number_format( $number2, 10, '.', '' );
    }

    $result  = null;
    $compare = false;

    switch ( $action ) {
    case '+':
    case 'add':
    case 'addition':
        $result = ( $bc ) ? bcadd( $number1, $number2, $precision ) /* string */ : ( $number1 + $number2 );
        break;

    case '-':
    case 'sub':
    case 'subtract':
        $result = ( $bc ) ? bcsub( $number1, $number2, $precision ) /* string */ : ( $number1 - $number2 );
        break;

    case '*':
    case 'mul':
    case 'multiply':
        $result = ( $bc ) ? bcmul( $number1, $number2, $precision ) /* string */ : ( $number1 * $number2 );
        break;

    case '/':
    case 'div':
    case 'divide':
        if ( $bc ) {
            $result = bcdiv( $number1, $number2, $precision ); // String, or NULL if right_operand is 0.
        } elseif ( $number2 != 0 ) {
            $result = ( $number1 / $number2 );
        }

        if ( ! isset( $result ) ) {
            $result = 0;
        }
        break;

    case '%':
    case 'mod':
    case 'modulus':
        if ( $bc ) {
            $result = bcmod( $number1, $number2 ); // String, or NULL if modulus is 0.
        } elseif ( $number2 != 0 ) {
            $result = ( $number1 % $number2 );
        }

        if ( ! isset( $result ) ) {
            $result = 0;
        }
        break;

    case '=':
    case 'comp':
    case 'compare':
        $compare = true;
        if ( $bc ) {
            $result = bccomp( $number1, $number2, $precision ); // Returns int 0, 1 or -1.
        } else {
            $result = ( $number1 == $number2 ) ? 0 : ( ( $number1 > $number2 ) ? 1 : - 1 );
        }
        break;
    }

    if ( isset( $result ) ) {
        if ( $compare === false ) {
            if ( $round === true ) {
                $result = round( floatval( $result ), $decimals );
                if ( $decimals === 0 ) {
                    $result = (int) $result;
                }
            } else {
                $result = ( intval( $result ) == $result ) ? intval( $result ) : floatval( $result );
            }
        }

        return $result;
    }

    return false;
}