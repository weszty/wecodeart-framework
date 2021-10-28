<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage  Support\Starter
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		5.0.0
 * @version		5.1.6
 */

namespace WeCodeArt\Support;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Integration;
use WeCodeArt\Admin\Notifications;
use WeCodeArt\Admin\Notifications\Notification;

/**
 * Class Starter
 */
class Starter implements Integration {

	use Singleton;

	const HOME_SLUG  	= 'home';
	const BLOG_SLUG  	= 'blog';
	const ABOUT_SLUG 	= 'about';
	const NOTICE_ID 	= 'wecodeart-starter-notice';

	/**
	 * Get Conditionals
	 *
	 * @return void
	 */
	public static function get_conditionals() {
		wecodeart( 'conditionals' )->set( [
			'is_fresh_site' => Starter\Condition::class,
		] );
		
		return [ 'is_fresh_site' ];
	}

	/**
	 * Send to Constructor
	 *
	 * @since 	5.0.0
	 * @version	5.1.6
	 */
	public function register_hooks() {
		// Theme Support
		\add_action( 'admin_notices',		[ $this, 'manage_notice' ] );
		\add_action( 'after_setup_theme',	[ $this, 'after_setup_theme' ] );
	}

	/**
	 * Manage Notice
	 *
	 * @since 	5.0.7
	 * @version	5.0.7
	 */
	public function manage_notice() {
		$notification = new Notification(
			sprintf(
				esc_html__( 'Don`t know where to start? Go to %s to setup starter content. There you can find a couple of pages and section examples created with WeCodeArt Framework.', 'wecodeart' ),
				sprintf( '<a href="%s">%s</a>', esc_url_raw( admin_url( '/customize.php' ) ), __( 'Customizer', 'wecodeart' ) )
			),
			[
				'id'			=> self::NOTICE_ID,
				'type'     		=> Notification::INFO,
				'priority' 		=> 1,
				'class'			=> 'notice is-dismissible',
			]
		);

		if( get_user_option( self::NOTICE_ID ) === 'seen' ) {
			Notifications::get_instance()->remove_notification( $notification );
			set_transient( self::NOTICE_ID, true, WEEK_IN_SECONDS );
			return;
		}

		if( get_transient( self::NOTICE_ID ) === false ) {
			Notifications::get_instance()->add_notification( $notification );
		}
	}

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * @since 	5.0.0
	 * @version	5.0.0
	 */
	public function after_setup_theme() {
		// Starter Content
		\add_theme_support( 'starter-content', $this->get() );
	}

	/**
	 * Return starter content definition.
	 *
	 * @return mixed|void
	 */
	public function get() {
		$content = [
			'attachments' => [
				'logo' => [
					'post_title' 	=> _x( 'Logo', 'Theme starter content', 'wecodeart' ),
					'post_excerpt' 	=> 'WeCodeArt',
					'file' 			=> 'assets/images/logo.png',
				],
			],
			'options'     => [
				'page_on_front'  => '{{' . self::HOME_SLUG . '}}',
				'page_for_posts' => '{{' . self::BLOG_SLUG . '}}',
				'show_on_front'  => 'page',
				'blogname'       => 'WeCodeArt Framework',
			],
			'nav_menus' => [
				'primary' => [
					'name' 	=> esc_html__( 'Primary Menu', 'wecodeart' ),
					'items' => [
						'page_home',
						'page_about',
						'page_blog',
					],
				],
			],
			'theme_mods'  => [
				'custom_logo' => '{{logo}}',
			],
			'posts'       => [
				self::HOME_SLUG  => wp_parse_args( [
					'post_name'  => self::HOME_SLUG,
				], require __DIR__ . '/content/home.php' ),
				self::ABOUT_SLUG => wp_parse_args( [
					'post_name'  => self::ABOUT_SLUG,
				], require __DIR__ . '/content/about.php' ),
				self::BLOG_SLUG  => [
					'post_name'		=> self::BLOG_SLUG,
					'post_type'		=> 'page',
					'post_title'	=> _x( 'Blog', 'Theme starter content', 'wecodeart' ),
				],
			],
		];

		return apply_filters( 'wecodeart/filter/starter', $content );
	}
}
