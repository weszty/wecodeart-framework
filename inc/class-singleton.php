<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework 
 * @subpackage 	Singleton
 * @copyright   Copyright (c) 2020, WeCodeArt Framework
 * @since		3.6.2
 */

namespace WeCodeArt;

defined( 'ABSPATH' ) || exit();

/**
 * Singleton DRY class
 */
trait Singleton { 
	/**
	 * Instance
	 *
	 * @access 	private
	 * @var 	object
	 */
	private static $instance;

	/**
	 * Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) self::$instance = new self; 
		return self::$instance;
	}

	/**
	 * Constructor protected from the outside
	 */
	final private function __construct() {
		$this->init();
	}

	/**
	 * Add init function by default
	 * Implement this method in your child class
	 * If you want to have actions send at construct
	 */
	protected function init() {}

	/**
	 * Prevent the instance from being cloned
	 * @return void
	 */
	final private function __clone() {}

	/**
	 * Prevent from being unserialized 
	 * @return void
	 */
	final private function __wakeup() {}
}