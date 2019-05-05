<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Admin
 * @copyright   Copyright (c) 2019, WeCodeArt Framework
 * @since 		3.8.1
 * @version		3.8.1
 */

namespace WeCodeArt;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Admin
 *
 * @since 3.8.1
 */
class Admin {

	use \WeCodeArt\Singleton;

	/**
	 * Send to Constructor
	 */
	public function init() {
		// Init notification system.
		Admin\Notifications::get_instance();

		add_action( 'admin_notices', 			[ $this, 'register_notices' ] );
		add_action( 'admin_enqueue_scripts', 	[ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Register Notices
	 *
	 * @since 3.8.1
	 */
	public static function register_notices() {
		if ( false === get_transient( 'wca-notification-theme-options' ) ) {
			Admin\Notifications::add( [
				'id'		=> 'wca-notification-theme-options',
				'type'		=> '',
				'message'	=> sprintf(
					'<h3 class="wca-notice__heading">%1$s</h3>
					<div class="wca-notice__content">
						<p>%2$s</p>
						<p>
							<a href="%3$s" data-repeat="%6$s" class="button-primary">%4$s</a>
							<a href="#" data-repeat="%6$s" class="wca-notice__close button-secondary">%5$s</a>
						</p>
					</div>',
					esc_html__( 'Did you know? WeCodeArt Framework uses Customizer to manage options!', 'wecodeart' ),
					esc_html__( 'Go to WP Customizer and see the options. Each CPT has it\'s own options, based on page/archive you are currently viewing in customizer.', 'wecodeart' ),
					esc_url( admin_url( '/customize.php' ) ),
					esc_html__( 'Awesome, show me the options', 'wecodeart' ),
					esc_html__( 'Thanks, but I already know', 'wecodeart' ),
					WEEK_IN_SECONDS
				),
				'repeat'	=> WEEK_IN_SECONDS,
				'priority'	=> 5,
			] );
		}

		if ( false === get_transient( 'wca-notification-theme-rate' ) ) {
			Admin\Notifications::add( [
				'id'		=> 'wca-notification-theme-rate',
				'type'		=> '',
				'class'		=> 'wca-notice--theme-rating',
				'message'	=> sprintf(
					'<div class="wca-notice__image">
						<img src="%1$s" class="custom-logo" alt="WeCodeArt Framework" itemprop="logo">
					</div> 
					<div class="wca-notice__content">
						<h3 class="wca-notice__heading">%2$s</h3>
						<div class="wca-notice__container wca-notice__container--column">
							<p>%3$s</p>
							<p>
								<a href="%4$s" class="wca-notice__close button-primary" target="_blank">%5$s</a>
								<a href="#" data-repeat="%8$s" class="wca-notice__close">
									<span class="dashicons dashicons-calendar"></span>
									%6$s
								</a>
								<a href="#" class="wca-notice__close">
									<span class="dashicons dashicons-smiley"></span>
									%7$s
								</a>
							</p>
						</div>
					</div>',
					'https://www.wecodeart.com/wp-content/uploads/2019/01/cropped-wecodeart-logo-2.png',
					esc_html__( 'Hello! I\'m glad you use WeCodeArt Framework to build this website - Thanks a ton!', 'wecodeart' ),
					esc_html__( 'Could you please do me a BIG favor and give it a 5-star rating on WordPress? This would boost my motivation and help other users make a comfortable decision while choosing the WeCodeArt Framework.', 'wecodeart' ),
					'https://wordpress.org/support/theme/wecodeart/reviews/?filter=5#new-post',
					esc_html__( 'Ok, you deserve it', 'wecodeart' ),
					esc_html__( 'Remind me later', 'wecodeart' ),
					esc_html__( 'I already did', 'wecodeart' ),
					WEEK_IN_SECONDS
				),
				'repeat'	=> WEEK_IN_SECONDS,
				'priority'	=> 10,
			] );
		}
	}

	/**
	 * Admin CSS
	 *
	 * @since 3.8.1
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 
			strtolower( str_replace( '\\', '-', __CLASS__ ) ), 
			get_parent_theme_file_uri( '/assets/css/admin/style.css' ), 
			[],
			'3.8.1'
		);
	}
}