<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Notifications
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since 		3.8.1
 * @version		4.2.0
 */

namespace WeCodeArt\Admin;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Markup;
use WeCodeArt\Singleton;
use WeCodeArt\Core\Scripts;
use function WeCodeArt\Core\Scripts\get_asset;
\WeCodeArt\Core\Scripts::get_instance();

/**
 * Notifications
 *
 * @since 3.8.1
 */
class Notifications {

	use Singleton;
	use Scripts\Base;

	/**
	 * Notices
	 *
	 * @access 	private
	 * @var 	array Notices.
	 * @since 	3.8.1
	 */
	private static $notices = [];

	/**
	 * Constructor
	 *
	 * @since 3.8.1
	 */
	public function init() {
		add_action( 'wp_ajax_wecodeart_notification_dismiss', [ $this, 'dismiss' ] );
		add_filter( 'wp_kses_allowed_html', 	[ $this, 'add_data_attributes' ], 10, 2 );
		add_action( 'admin_enqueue_scripts', 	[ $this, 'enqueue_scripts' ] );
		add_action( 'admin_notices',			[ $this, 'register_notices' ] );
		add_action( 'admin_notices', 			[ $this, 'render' ], 90 );
	}

	/**
	 * Register Notices
	 *
	 * @since 	3.8.1
	 * @version	4.2.0
	 */
	public static function register_notices() {
		return;
	}

	/**
	 * Filters and Returns a list of allowed tags and attributes for a given context.
	 *
	 * @param 	array  $allow 	Array of allowed tags.
	 * @param 	string $context
	 * @since 	3.8.1
	 * @return 	array
	 */
	public function add_data_attributes( $allow, $context ) {
		$allow['a']['data-repeat'] = true;

		return $allow;
	}

	/**
	 * Add Notice
	 *
	 * @since 	3.8.1
	 * @param 	array	$args 	Notice arguments.
	 * @return 	void
	 */
	public static function add( $args = [] ) {
		self::$notices[] = $args;
	}

	/**
	 * Dismiss Notice
	 *
	 * @since 	3.8.1
	 * @return 	void
	 */
	public function dismiss() {
		$notice_id		= ( isset( $_POST['notice_id'] ) ) ? sanitize_key( $_POST['notice_id'] ) : '';
		$repeat_after 	= ( isset( $_POST['repeat_after'] ) ) ? absint( $_POST['repeat_after'] ) : '';

		// Valid inputs?
		if ( ! empty( $notice_id ) ) {

			if ( ! empty( $repeat_after ) ) {
				set_transient( $notice_id, true, $repeat_after );
			} else {
				update_user_meta( get_current_user_id(), $notice_id, 'notice-dismissed' );
			}

			wp_send_json_success();
		}

		wp_send_json_error();
	}

	/**
	 * Is notice expired?
	 *
	 * @since 3.8.1
	 *
	 * @param  array $notice Notice arguments.
	 * @return boolean
	 */
	private static function is_expired( $notice ) {
		$transient_status = get_transient( $notice['id'] );

		if ( false === $transient_status ) {

			if ( isset( $notice['delay'] ) && false !== $notice['delay'] ) {

				if ( 'delayed-notice' !== get_user_meta( get_current_user_id(), $notice['id'], true ) &&
					'notice-dismissed' !== get_user_meta( get_current_user_id(), $notice['id'], true ) ) {
					set_transient( $notice['id'], 'delayed-notice', $notice['delay'] );
					update_user_meta( get_current_user_id(), $notice['id'], 'delayed-notice' );

					return false;
				}
			}

			// Check the user meta status if current notice is dismissed or delay completed.
			$meta_status = get_user_meta( get_current_user_id(), $notice['id'], true );

			if ( empty( $meta_status ) || 'delayed-notice' === $meta_status ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Enqueue Scripts.
	 *
	 * @since 	3.8.6
	 * @version	4.1.6
	 * @return 	void
	 */
	public function enqueue_scripts() {
		wp_register_script( 
			$this->make_handle(),
			$this->get_asset( 'js', 'admin-notification' ),
			[ 'jquery' ],
			wecodeart( 'version' ),
			true
		);
	}

	/**
	 * Rating priority sort
	 *
	 * @since 	3.8.1
	 * @param 	array $array1 array one.
	 * @param 	array $array2 array two.
	 * @return 	array
	 */
	public function sort_notices( $array1, $array2 ) {
		if ( ! isset( $array1['priority'] ) ) {
			$array1['priority'] = 10;
		}

		if ( ! isset( $array2['priority'] ) ) {
			$array2['priority'] = 10;
		}

		return $array1['priority'] - $array2['priority'];
	}

	/**
	 * Notice classes.
	 *
	 * @since 3.8.1
	 *
	 * @param  array 	$notice Notice arguments.
	 * @return array
	 */
	private static function get_classes( $notice ) {
		$classes   = [ 'wca-notice', 'notice', 'is-dismissible' ];
		$classes[] = $notice['class'];
		if ( isset( $notice['type'] ) && '' !== $notice['type'] ) {
			$classes[] = 'notice-' . $notice['type'];
		}

		return esc_attr( implode( ' ', $classes ) );
	}

	/**
	 * Get Notice ID.
	 *
	 * @since 3.8.1
	 *
	 * @param  array 	$notice Notice arguments.
	 * @param  int   	$key	Notice array index.
	 * @return string
	 */
	private static function get_id( $notice, $key ) {
		if ( isset( $notice['id'] ) && ! empty( $notice['id'] ) ) {
			return $notice['id'];
		}

		return esc_attr( 'wca-notices-id-' . $key );
	}

	/**
	 * Markup Notice.
	 *
	 * @since 	3.8.1
	 * @version	4.0.3
	 *
	 * @param  	array $notice Notice markup.
	 * @return 	void
	 */
	public static function markup( $notice = [] ) {

		do_action( "wecodeart/action/admin/notification/{$notice['id']}/before" );

		Markup::template( 'admin/notification', $notice );

		do_action( "wecodeart/action/admin/notification/{$notice['id']}/after" );
	}

	/**
	 * Render Notifications
	 *
	 * @since 	3.8.1
	 * @version	4.0.3 
	 *
	 * @return 	void
	 */
	public function render() {
		wp_enqueue_script( $this->make_handle() );

		$defaults = array(
			'id'		=> '',      // Optional, Notice ID.
			'type'		=> 'info',  // Optional, Expected [info, warning, notice, error].
			'message'	=> '',		// Optional, Message.
			'show_if'	=> true,    // Optional, E.g. 'show_if' => ( is_admin() ) ? true : false.
			'repeat'	=> '',      // Optional, Dismiss-able notice time. It'll auto show after given time.
			'delay'		=> false,	// Optional, Dismiss-able notice time. It'll delay for a given time.
			'class'		=> '',      // Optional, Additional notice wrapper class.
			'priority'	=> 10,      // Priority of the notice.
			'single'	=> false,	// Show only notice if is single.
		);

		// Count for the notices that are rendered.
		$notices_displayed = 0;

		// Sort the array with priority.
		usort( self::$notices, [ $this, 'sort_notices' ] );

		foreach ( self::$notices as $key => $notice ) {
			// Data.
			$notice 			= wp_parse_args( $notice, $defaults );
			$notice['id'] 		= self::get_id( $notice, $key );
			$notice['classes'] 	= self::get_classes( $notice );

			// Notices visible after transient expire.
			if ( isset( $notice['show_if'] ) && true === $notice['show_if'] ) {

				// Don't display the notice if it is not supposed to be displayed with other notices.
				if ( 1 !== $notices_displayed && true === $notice['single'] ) {
					continue;
				}

				if ( self::is_expired( $notice ) ) {

					self::markup( $notice );
					
					++$notices_displayed;
				}
			}
		}
	}
}