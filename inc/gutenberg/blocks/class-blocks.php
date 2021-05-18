<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg Blocks Registry
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		5.0.0
 * @version		5.0.0
 */

namespace WeCodeArt\Gutenberg;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Blocks Registry.
 */
class Blocks implements \ArrayAccess {

	use Singleton;

	/**
	 * The registered Blocks.
	 *
	 * @var Blocks[]
	 */
	protected $items = [];

	/**
	 * Send to Constructor
	 */
	public function init() {
		$this->register( 'core/file',	    Blocks\File::class );
        $this->register( 'core/table',	    Blocks\Table::class );
		$this->register( 'core/search',	    Blocks\Search::class );
		$this->register( 'core/quote',	    Blocks\Quote::class );
		$this->register( 'core/buttons',    Blocks\Buttons::class );
		$this->register( 'core/calendar',   Blocks\Calendar::class );
		$this->register( 'core/site-title', Blocks\Site_Title::class );
		$this->register( 'wca/content',	    Blocks\Content::class );
        
        add_action( 'init', [ $this, 'load' ] );
	}

	/**
	 * Loads all registered integrations if their conditionals are met.
	 *
	 * @return void
	 */
	public function load() {
		foreach( $this->items as $class ) $class::get_instance()->register_block_type();
	}

	/**
     * Set a given module value.
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
     * Set a given module value.
     *
     * @param  array|string  $key
     * @param  mixed   $value
     *
     * @return void
     */
    public function set( $key, $value = null ) {
        $keys = is_array( $key ) ? $key : [ $key => $value ];

        foreach ( $keys as $key => $value ) {
            $this->items[$key] = apply_filters( "wecodeart/gutenberg/blocks/set/{$key}", $value );
        }
	}

	/**
     * Determine if the given Blocks value exists.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function has( $key ) {
        return isset( $this->items[$key] );
    }

    /**
     * Get the specified Blocks value.
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

        return apply_filters( "wecodeart/gutenberg/blocks/get/{$key}", $this->items[$key] );
    }
	
	/**
     * Removes module from the container.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function forget( $key ) {
		unset( $this->items[$key] );
    }

    /**
     * Get all of the module items for the application.
     *
     * @return array
     */
    public function all() {
        return $this->items;
    }

    /**
     * Determine if the given module option exists.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function offsetExists( $key ) {
        return $this->has( $key );
    }

    /**
     * Get a module option.
     *
     * @param  string  $key
     *
     * @return mixed
     */
    public function offsetGet( $key ) {
        return $this->get( $key );
    }

    /**
     * Set a module option.
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
     * Unset a module option.
     *
     * @param  string  $key
     *
     * @return void
     */
    public function offsetUnset( $key ) {
        $this->set( $key, null );
    }
}
