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
 * @since		3.1.2
 * @version		5.0.0
 */

namespace WeCodeArt\Markup;

defined( 'ABSPATH' ) || exit();

use ArrayAccess;
use WeCodeArt\Singleton;

/**
 * Standard Inputs Markup
 */
class Inputs implements ArrayAccess {

	use Singleton;

	/**
     * All of the configuration items.
     *
     * @var array
     */
    protected $items = [];

    /**
	 * Send to Constructor
	 */
	public function init() {
		// Register Default Inputs
		$this->register( 'basic',		Inputs\Basic::class     );
		$this->register( 'button',		Inputs\Button::class 	);
		$this->register( 'select',		Inputs\Select::class 	);
		$this->register( 'textarea',	Inputs\TextArea::class 	);
		$this->register( 'toggle',		Inputs\Toggle::class 	);
		$this->register( 'radio',		Inputs\Toggle::class 	);
		$this->register( 'checkbox',	Inputs\Toggle::class 	);
        $this->register( 'fieldset',	Inputs\Fieldset::class 	);
        $this->register( 'floating',	Inputs\Floating::class 	);
	}

	/**
	 * Render the HTML of the input
	 *
	 * @access 	public
	 * @param 	string 		$type		text/number/etc
	 * @param 	array 		$args		Input Args
	 *
	 * @return	void
	 */
	public static function render( string $type = 'hidden', ...$input_args ) {
		echo self::compile( $type, ...$input_args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get the HTML of the input
	 *
	 * @access 	public
	 * @since	unknown
	 * @version	5.0.0
	 *
	 * @param 	string 		$type		text/number/etc
	 * @param 	array 		$args		Input Args
	 *
	 * @return	string
	 */
	public static function compile( string $type = 'hidden', ...$input_args ) {
		if( in_array( $type, Inputs\Basic::get_types() ) ) {
			$class_type = 'basic';
		} else {
			$class_type = $type;
		}

		$storage = Inputs::get_instance(); 
		$input_class = $storage->get( $class_type );
		if( $input_class ) {
			$input = new $input_class( $type, ...$input_args );
			return $input->get_content(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
	
	/**
     * Determine if the given configuration value exists.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function has( $key ) {
        return isset( $this->items[$key] );
    }

    /**
     * Get the specified configuration value.
     *
     * @param  string  $key
     * @param  mixed   $default
     *
     * @return mixed
     */
    public function get( $key, $default = null ) {
        if ( ! isset( $this->items[$key] ) ) {
            return $default;
        }

        return apply_filters( "wecodeart/inputs/get/{$key}", $this->items[$key] );
	}
	
	/**
     * Set a given integration value.
     *
     * @param  array|string  $key
     * @param  mixed   $value
     *
     * @return void
     */
    public function register( $key, $value = null ) {
        $this->set( $key, $value );
	}

    /**
     * Set a given configuration value.
     *
     * @param  array|string  $key
     * @param  mixed   $value
     *
     * @return void
     */
    public function set( $key, $value = null ) {
        $keys = is_array( $key ) ? $key : [ $key => $value ];

        foreach ( $keys as $key => $value ) {
            $this->items[$key] = apply_filters( "wecodeart/inputs/set/{$key}", $value );
        }
    }

    /**
     * Forget a given configuration value.
     *
     * @param string  $key
     *
     * @return void
     */
    public function forget( $key ) {
        unset( $this->items[$key] );
    }

    /**
     * Get all of the configuration items for the application.
     *
     * @return array
     */
    public function all() {
        return $this->items;
    }

    /**
     * Determine if the given configuration option exists.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function offsetExists( $key ) {
        return $this->has( $key );
    }

    /**
     * Get a configuration option.
     *
     * @param  string  $key
     *
     * @return mixed
     */
    public function offsetGet( $key ) {
        return $this->get( $key );
    }

    /**
     * Set a configuration option.
     *
     * @param  string  $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function offsetSet( $key, $value ) {
        $this->set( $key, $value );
    }

    /**
     * Unset a configuration option.
     *
     * @param  string  $key
     *
     * @return void
     */
    public function offsetUnset( $key ) {
        $this->set( $key, null );
    }
}