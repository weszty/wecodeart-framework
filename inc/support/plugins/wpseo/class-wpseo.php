<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Support\Yoast SEO
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since 		3.5
 * @version		5.3.1
 */

namespace WeCodeArt\Support\Plugins;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Singleton;
use WeCodeArt\Integration;
use WeCodeArt\Admin\Notifications;
use WeCodeArt\Admin\Notifications\Notification;
use function WeCodeArt\Functions\get_prop;

/**
 * WPSEO Integration
 */
class WPSeo implements Integration {

	use Singleton; 

	const NOTICE_ID = 'wecodeart-yoast-notice';

	/**
     * All of the configuration items for the extension.
     *
     * @var array
     */
	protected $config = [];
	
	/**
	 * Send to Constructor
	 */
	public function init() {
		$this->config = get_prop( wecodeart_config( 'extensions' ), 'wpseo' );
	}

	/**
	 * Get Conditionals
	 *
	 * @return void
	 */
	public static function get_conditionals() {
		wecodeart( 'conditionals' )->set( [
			'is_yoast_active' => WPSeo\Condition::class,
		] );
		
		return [ 'is_yoast_active' ];
	}

	/**
	 * Send to Constructor
	 */
	public function register_hooks() {
		// Notices
		add_action( 'admin_notices',	[ $this, 'manage_notice' ] );

		// Restricted Blocks
		add_filter( 'wecodeart/filter/gutenberg/restricted',	[ $this, 'restricted_gutenberg_blocks' ] );

		// Terms Template Context
		add_filter( 'wecodeart/filter/template/context', 		[ $this, 'filter_category_context' ], 10, 2 );

		// Author Template Context
		if( get_prop( $this->config, 'author-social', false ) !== false ) {
			/**
			 * Extend Author box data with Social
			 */
			add_filter( 'wecodeart/filter/template/context', [ $this, 'filter_author_context' ], 10, 2 );
		}
	}

	/**
	 * Manage Notice
	 *
	 * @since 	5.0.0
	 * @version	5.0.0
	 */
	public function manage_notice() {
		$notification = new Notification(
			esc_html__( 'YoastSEO support is enabled! Our theme works seamlessly with the best SEO plugin.', 'wecodeart' ),
			[
				'id'			=> self::NOTICE_ID,
				'type'     		=> Notification::INFO,
				'priority' 		=> 1,
				'class'			=> 'notice is-dismissible',
				'capabilities' 	=> 'activate_plugins',
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
	 * Extend Author Box with Yoast's Social
	 *
	 * @since	3.9.3
	 * @version 5.1.9
	 *
	 * @return 	array
	 */
	public function filter_author_context( $args, $name ) {
		$config	= get_prop( $this->config, 'author-social', false );

		if( $name !== 'meta/author-box.php' || $config === false ) {
			return $args;
		}

		$args['attributes'] = wp_parse_args( [
			'avatarSize' =>  90
		], $args['attributes'] );

		$author	= get_prop( $args, [ 'author' ] );

		$social_icons = [
			'facebook' => [
				'viewBox'	=> '0 0 448 512',
				'paths' 	=> 'M400 32H48A48 48 0 0 0 0 80v352a48 48 0 0 0 48 48h137.25V327.69h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.27c-30.81 0-40.42 19.12-40.42 38.73V256h68.78l-11 71.69h-57.78V480H400a48 48 0 0 0 48-48V80a48 48 0 0 0-48-48z'
			],
			'twitter' => [
				'viewBox'	=> '0 0 448 512',
				'paths' 	=> 'M400 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48zm-48.9 158.8c.2 2.8.2 5.7.2 8.5 0 86.7-66 186.6-186.6 186.6-37.2 0-71.7-10.8-100.7-29.4 5.3.6 10.4.8 15.8.8 30.7 0 58.9-10.4 81.4-28-28.8-.6-53-19.5-61.3-45.5 10.1 1.5 19.2 1.5 29.6-1.2-30-6.1-52.5-32.5-52.5-64.4v-.8c8.7 4.9 18.9 7.9 29.6 8.3a65.447 65.447 0 0 1-29.2-54.6c0-12.2 3.2-23.4 8.9-33.1 32.3 39.8 80.8 65.8 135.2 68.6-9.3-44.5 24-80.6 64-80.6 18.9 0 35.9 7.9 47.9 20.7 14.8-2.8 29-8.3 41.6-15.8-4.9 15.2-15.2 28-28.8 36.1 13.2-1.4 26-5.1 37.8-10.2-8.9 13.1-20.1 24.7-32.9 34z'
			],
			'youtube' => [
				'viewBox' 	=> '0 0 448 512',
				'paths'		=> 'M186.8 202.1l95.2 54.1-95.2 54.1V202.1zM448 80v352c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V80c0-26.5 21.5-48 48-48h352c26.5 0 48 21.5 48 48zm-42 176.3s0-59.6-7.6-88.2c-4.2-15.8-16.5-28.2-32.2-32.4C337.9 128 224 128 224 128s-113.9 0-142.2 7.7c-15.7 4.2-28 16.6-32.2 32.4-7.6 28.5-7.6 88.2-7.6 88.2s0 59.6 7.6 88.2c4.2 15.8 16.5 27.7 32.2 31.9C110.1 384 224 384 224 384s113.9 0 142.2-7.7c15.7-4.2 28-16.1 32.2-31.9 7.6-28.5 7.6-88.1 7.6-88.1z'
			],
			'linkedin' => [
				'viewBox' 	=> '0 0 448 512',
				'paths'		=> 'M416 32H31.9C14.3 32 0 46.5 0 64.3v383.4C0 465.5 14.3 480 31.9 480H416c17.6 0 32-14.5 32-32.3V64.3c0-17.8-14.4-32.3-32-32.3zM135.4 416H69V202.2h66.5V416zm-33.2-243c-21.3 0-38.5-17.3-38.5-38.5S80.9 96 102.2 96c21.2 0 38.5 17.3 38.5 38.5 0 21.3-17.2 38.5-38.5 38.5zm282.1 243h-66.4V312c0-24.8-.5-56.7-34.5-56.7-34.6 0-39.9 27-39.9 54.9V416h-66.4V202.2h63.7v29.2h.9c8.9-16.8 30.6-34.5 62.9-34.5 67.2 0 79.7 44.3 79.7 101.9V416z'
			],
			'instagram' => [
				'viewBox' 	=> '0 0 448 512',
				'paths'		=> 'M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z'
			],
			'pinterest' => [
				'viewBox' 	=> '0 0 448 512',
				'paths'		=> 'M448 80v352c0 26.5-21.5 48-48 48H154.4c9.8-16.4 22.4-40 27.4-59.3 3-11.5 15.3-58.4 15.3-58.4 8 15.3 31.4 28.2 56.3 28.2 74.1 0 127.4-68.1 127.4-152.7 0-81.1-66.2-141.8-151.4-141.8-106 0-162.2 71.1-162.2 148.6 0 36 19.2 80.8 49.8 95.1 4.7 2.2 7.1 1.2 8.2-3.3.8-3.4 5-20.1 6.8-27.8.6-2.5.3-4.6-1.7-7-10.1-12.3-18.3-34.9-18.3-56 0-54.2 41-106.6 110.9-106.6 60.3 0 102.6 41.1 102.6 99.9 0 66.4-33.5 112.4-77.2 112.4-24.1 0-42.1-19.9-36.4-44.4 6.9-29.2 20.3-60.7 20.3-81.8 0-53-75.5-45.7-75.5 25 0 21.7 7.3 36.5 7.3 36.5-31.4 132.8-36.1 134.5-29.6 192.6l2.2.8H48c-26.5 0-48-21.5-48-48V80c0-26.5 21.5-48 48-48h352c26.5 0 48 21.5 48 48z'
			]
		];

		$args = wp_parse_args( [
			'social' => array_map( function( $item ) use( $author, $social_icons, $config ) {
				$item  	= strtolower( $item );
				$icon  	= $item;
				$value 	= trim( get_the_author_meta( $item, $author->id ) );
				$target = '_blank';

				switch( $item ) {	
					case 'twitter':
						if( ! empty( $value ) ) {
							$value = "https://twitter.com/${value}";
						}
						break;
					case 'url':
						$icon  	= 'globe';
						$target	= $value ? $target : '_self';
						$value 	= $value ?: $author->url;
						break;
				}

				if( isset( $social_icons[$item] ) ) {
					/**
					 * Add extra Social Icon.
					 *
					 * @since	3.9.4
					 */
					wecodeart( 'markup' )->SVG::add_icon( $item, $social_icons[$item] );
				}

				return [
					'title' 	=> $config[$item],
					'url'		=> $value,
					'icon'		=> $icon,
					'target'	=> $target
				];

			}, array_keys( $config ) )
		], $args );

		$args['social'] = array_filter( $args['social'], function( $item ) {
			return( isset( $item['url'] ) && $item['url'] !== '' );
		} );

		return $args;
	}

	/**
	 * Extend Category with Yoast's Primary Term
	 *
	 * @since	4.1.52
	 * @version	5.0.0
	 *
	 * @return 	array
	 */
	public function filter_category_context( $args, $name ) {
		if( $name !== 'entry/meta/terms.php' ) {
			return $args;
		}
		
		if( $meta = get_post_meta( get_prop( $args, 'post_id' ), '_yoast_wpseo_primary_category', true ) ) {
			$args['primary'] = (int) $meta;
		}

		return $args;
	}

	/**
	 * Filter - Restricted Yoast Blocks from theme code
	 *
	 * @since	5.0.0
	 * @version	5.0.0
	 *
	 * @return 	array
	 */
	public function restricted_gutenberg_blocks( $blocks ) {
		return wp_parse_args( [
			'yoast-seo/breadcrumbs',
		], $blocks );
	}
}