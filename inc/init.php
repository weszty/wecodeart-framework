<?php 
/**
 * WeCodeArt Framework Init.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework.
 * @subpackage  Init
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		1.0
 * @version		4.2
 */

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Activation;
use WeCodeArt\Gutenberg;
use WeCodeArt\Singleton;
use WeCodeArt\Support;
use WeCodeArt\Core;
use WeCodeArt\Admin;
use WeCodeArt\Config;
use WeCodeArt\Conditional;
use WeCodeArt\Config\Exceptions\BindingResolutionException;

// Include the functions and autoloader.
require_once( get_parent_theme_file_path( '/inc/class-autoloader.php' ) );
require_once( get_parent_theme_file_path( '/inc/functions.php' ) );
new WeCodeArt\Autoloader();

/**
 * Final Class
 */
final class WeCodeArt implements \ArrayAccess {

	use Singleton;

	/**
     * Collection of services.
     *
     * @var array
     */
	protected $services = [];
	
    /**
     * Collection of services factories.
     *
     * @var array
     */
	protected $factories = [];
	
    /**
     * Registry of deposited services.
     *
     * @var array
     */
	protected $deposit = [];

	/**
	 * Load Theme Modules
	 *
	 * @since 3.6.2
	 */
	public function load() {
		// Fire Core
		Core		::get_instance();
		// Admin Stuff
		Admin 		::get_instance();
        // Gutenberg
        Gutenberg   ::get_instance();
		// Fire Support Class
        Support		::get_instance();
	}

	/**
     * Bind service into collection.
     *
     * @param  string   $key
     * @param  Closure  $service
     *
     * @return void
     */
    public function bind( $key, Closure $service ) {
        $this->services[$key] = $service;
        return $this;
	}
	
    /**
     * Bind factory into collection.
     *
     * @param  string   $key
     * @param  \Closure $factory
     *
     * @return void
     */
    public function factory( $key, Closure $factory ) {
        $this->factories[$key] = $factory;
        return $this;
	}
	
    /**
     * Resolves service with parameters.
     *
     * @param  mixed $abstract
     * @param  array $parameters
     * @return mixed
     */
    protected function resolve( $abstract, array $parameters ) {
        return call_user_func_array( $abstract, [ $this, $parameters ] );
	}
	
    /**
     * Resolve service form container.
     *
     * @param  string $key
     * @param  array  $parameters
     *
     * @return mixed
     */
    public function get( $key, array $parameters = [] ) {
        // If service is a factory, we should always return new instance of the service.
        if ( isset( $this->factories[$key] ) ) {
            return $this->resolve( $this->factories[$key], $parameters );
		}

        // Otherwise, look for service in services collection.
        if ( isset( $this->services[$key] ) ) {
            if ( ! isset( $this->deposit[$key] ) ) {
                $this->deposit[$key] = $this->resolve( $this->services[$key], $parameters );
			}
			
            return $this->deposit[$key];
		}
		
        throw new BindingResolutionException("Unresolvable resolution. The [{$key}] binding is not registered.");
	}
	
    /**
     * Determine if the given service exists.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function has( $key ) {
        return isset( $this->factories[$key] ) || isset( $this->services[$key] );
	}
	
    /**
     * Removes service from the container.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function forget( $key ) {
        unset( $this->factories[$key], $this->services[$key] );
    }
	
	/**
     * Gets a service.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function offsetGet( $key ) {
        return $this->get( $key );
	}
	
    /**
     * Sets a service.
     *
     * @param string $key
     * @param Closure $service
     *
     * @return void
     */
    public function offsetSet( $key, $service ) {
        if ( ! is_callable( $service ) ) {
            throw new BindingResolutionException("Service definition [{$service}] is not an instance of Closure");
		}
		
        $this->bind( $key, $service );
    }
	
	/**
     * Determine if the given service exists.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function offsetExists( $key ) {
        return $this->has( $key );
	}
	
    /**
     * Unsets a service.
     *
     * @param  string  $key
     *
     * @return void
     */
    public function offsetUnset( $key ) {
        return $this->forget( $key );
    }
}

/**
 * Puts Everything togheter
 *
 * @since	1.0
 * @version	3.9.5
 *
 * @return	void
 */
function wecodeart( $key = null, $parameters = [], WeCodeArt $theme = null ) {
    $theme = $theme ?: WeCodeArt::get_instance();

	if( null !== $key ) {
        return $theme->get( $key, $parameters );
	}

    return $theme;
}

/**
 * Gets theme config instance.
 *
 * @since	3.9.5
 * @version	3.9.5
 *
 * @param   string|array    $key
 * @param   string|null     $default
 *
 * @return  mixed
 */
function wecodeart_config( $key = null, $default = null ) {
    if ( null === $key ) {
        return wecodeart( 'config' );
    }

    if ( is_array( $key ) ) {
        return wecodeart( 'config' )->set( $key );
    }

    return wecodeart( 'config' )->get( $key, $default );
}

/**
 * Gets input instance.
 *
 * @since	4.2.0
 * @version	4.2.0
 *
 * @param   string|array    $key
 * @param   array|null      $args
 * @param   bool            $echo
 *
 * @return  mixed
 */
function wecodeart_input( $key = null, array $args = [], bool $echo = true ) {
    if ( null === $key ) {
        return wecodeart( 'inputs' );
    }

    if ( is_array( $key ) ) {
        return wecodeart( 'inputs' )->register( $key );
    }

    if( $echo ) {
        return wecodeart( 'inputs' )::render( $key, $args );
    }

    return wecodeart( 'inputs' )::compile( $key, $args );
}

/**
 * Echo options from the options database.
 *
 * @since   4.2.0
 *
 * @param   string  $key        Option name.
 * @param   string  $default    Default value.
 * @param   string  $setting    Optional. Settings field name. Eventually defaults to 'wecodeart'.
 * @param   bool    $use_cache  Optional. Whether to use the cache value or not. Default is true.
 */
function wecodeart_option( $key, $default = false, $setting = null, $use_cache = true ) {
    if ( is_array( $key ) ) {
        return Admin::update_options( $key, $setting );
    }

    return Admin::get_option( $key, $default, $setting, $use_cache );
}

/**
 * Check if condition is met.
 *
 * @since	4.0
 * @version	4.2.0
 *
 * @param   string|array    $parameters
 *
 * @return  mixed|null|bool
 */
function wecodeart_if( $parameters, $relation = 'AND' ) {
    if( empty( $parameters ) ) return true;

    $return = false;

    if( in_array( $relation, [ 'AND', 'OR', 'ALL', 'ONE', 'and', 'or', 'all', 'one' ] ) ) {
        $return = in_array( $relation, [ 'OR', 'or', 'one' ] );
    }

    $one_met = true;
    
    foreach ( (array) $parameters as $conditional ) {
        if ( ! wecodeart( 'conditionals' )->has( $conditional ) ) return null;
        $class  = wecodeart( 'conditionals' )->get( $conditional );
        $is_met = ( new $class )->is_met();
        if( $return === false && ! $is_met ) {
            return false;
        }
        if( $return === true && ! $is_met && $one_met !== false ) {
            $one_met = false;
            continue;
        }
    }

    return $one_met;
}

/**
 * App Binding Functions
 */
$theme  = wecodeart();
$config = Config::get_config();

/**
 * Before Setup Hook
 *
 * @since 4.0.1
 */
do_action( 'wecodeart/setup/before', $config );

/**
 * Load Setup
 */
require_once __DIR__ . '/config/setup.php';

/**
 * After Setup Hook
 *
 * @since 4.0.1
 */
do_action( 'wecodeart/setup/after', $theme );

/**
 * Maybe Load the theme if checks are passed
 */
if( Activation::get_instance()->is_ok() ) {
    $theme->load();
    
    /**
     * Theme Loaded Hook
     *
     * @since 4.0.9
     */
    do_action( 'wecodeart/theme/loaded' );

    /**
     * Load Integrations
     */
    wecodeart( 'integrations' )->load();
}

return $theme;