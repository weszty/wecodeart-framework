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
 * @version		5.0.0
 */

namespace WeCodeArt\Support;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Integration;

/**
 * Class Starter
 */
class Starter implements Integration {

	use Singleton;

	const HOME_SLUG  = 'home';
	const BLOG_SLUG  = 'blog';
	const ABOUT_SLUG = 'about';

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
	 * @version	5.0.0
	 */
	public function register_hooks() {
		// Theme Support
		\add_action( 'after_setup_theme', 			[ $this, 'after_setup_theme' ] );
		\add_filter( 'get_theme_starter_content', 	[ $this, 'add_postmeta' ], 10, 2 );
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
	 * Add postmeta to starter content posts
	 * @param  array 	$content  	the starter content array
	 * @param  array 	$config  	[description]
	 * @return array          		[description]
	 */
	public function add_postmeta( $content, $config ) { 
		if ( isset( $content['posts'] ) ) {
			foreach( $content['posts'] as $key => $post ) {
				if( $post['post_type'] !== 'page' ) continue; 
				$content['posts'][$key]['meta_input'] = [
					'_wca_builder_template' => true,
					'_wca_title_hidden' 	=> true,
				];
			}
		}

		return $content;
	}

	/**
	 * Return starter content definition.
	 *
	 * @return mixed|void
	 */
	public function get() {
		$content = [
			'widgets'     => [
				'primary' => [
					'search',
					'text_business_info',
				],
				'footer-1' => [
					'text_about',
				],
				'footer-2' => [
					'recent-posts',
				],
				'footer-3' => [
					'recent-comments',
				]
			],
			'nav_menus'   => [
				'primary' => [
					'items' => [
						'home'       => [
							'type'      => 'post_type',
							'object'    => 'page',
							'object_id' => '{{' . self::HOME_SLUG . '}}',
						],
						'page_about' => [
							'type'      => 'post_type',
							'object'    => 'page',
							'object_id' => '{{' . self::ABOUT_SLUG . '}}',
						],
						'page_blog'  => [
							'type'      => 'post_type',
							'object'    => 'page',
							'object_id' => '{{' . self::BLOG_SLUG . '}}',
						],
					],
				],
			],
			'options'     => [
				'page_on_front'  => '{{' . self::HOME_SLUG . '}}',
				'page_for_posts' => '{{' . self::BLOG_SLUG . '}}',
				'show_on_front'  => 'page',
				'blogname'       => 'WeCodeArt',
			],
			'theme_mods'  => wecodeart_config( 'customizer' ),
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
