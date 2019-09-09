<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Customizer\Configs\Content
 * @copyright   Copyright (c) 2019, WeCodeArt Framework
 * @since 		3.5
 * @version		3.9.6
 */

namespace WeCodeArt\Customizer\Configs;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Customizer\Config;
use WeCodeArt\Customizer\Formatting;
use WeCodeArt\Utilities\Callbacks;
use WeCodeArt\Support\WooCommerce\Callbacks as WooCB;

/**
 * Customizer Config initial setup
 */
class Content extends Config {
	/**
	 * Register Site Layout Customizer Configurations
	 *
	 * @param 	array                $configurations 
	 * @param 	WP_Customize_Manager $wp_customize instance of WP_Customize_Manager.
	 * @since 	3.6
	 *
	 * @return 	array 
	 */
	public function register( $configurations, $wp_customize ) {
		// A handy class for formatting theme mods.
		$formatting = Formatting::get_instance();
		$callbacks	= WooCB::get_instance();

		// Content Modules Choices.
		$c_modules = wp_list_pluck( \WeCodeArt\Core\Content::content_modules(), 'label' );

		$_configs = array( 
			array(
				'name'			=> 'content-layout-container',
				'type' 			=> 'control',
				'control'  		=> 'select',
				'section'		=> 'content-layout',
				'title' 		=> esc_html__( 'Container Type', 'wecodeart' ),
				'description' 	=> esc_html__( 'Choose the type of the container class.', 'wecodeart' ),
				'choices'  		=> [
					'container'			=> esc_html__( 'Container', 'wecodeart' ),
					'container-fluid' 	=> esc_html__( 'Container Fluid', 'wecodeart' ),
				], 
				'priority' 		=> 5, 
				'sanitize_callback'    => [ $formatting, 'sanitize_choices' ],
				'transport' 		   => 'postMessage'
			),
			array(
				'name'			=> 'content-layout-modules',
				'type'        	=> 'control',
				'control'  		=> 'wecodeart-sortable',
				'section'		=> 'content-layout',
				'title'			=> esc_html__( 'Content Modules: Default', 'wecodeart' ),
				'description'	=> esc_html__( 'Enable and reorder Site Inner modules.', 'wecodeart' ),
				'priority'		=> 10, 
				'choices'		=> $c_modules, 
				'transport'		=> 'postMessage',
				'partial'		=> [
					'selector'        		=> '.content-area',
					'render_callback' 		=> [ 'WeCodeArt\Core\Content', 'render_modules' ],
					'container_inclusive' 	=> true
				]
			),
			array(
				'name'			=> 'content-layout-container-blog',
				'type' 			=> 'control',
				'control'  		=> 'select',
				'section'		=> 'content-layout',
				'title'			=> esc_html__( 'Container Type: Blog Page', 'wecodeart' ) ,
				'description' 	=> esc_html__( 'Choose the type of the container class. Affects only current page.', 'wecodeart' ),
				'choices'  		=> [
					'container'			=> esc_html__( 'Container', 'wecodeart' ),
					'container-fluid' 	=> esc_html__( 'Container Fluid', 'wecodeart' ),
				],  
				'priority' 		=> 10,  
				'sanitize_callback'    => [ $formatting, 'sanitize_choices' ],
				'active_callback'	   => 'is_home',
				'transport' 		   => 'postMessage'
			),
			array(
				'name'			=> 'content-layout-modules-blog',
				'type'        	=> 'control',
				'control'  		=> 'wecodeart-sortable',
				'section'		=> 'content-layout',
				'title'			=> esc_html__( 'Content Modules: Blog Page', 'wecodeart' ),
				'description'	=> esc_html__( 'Enable and reorder Site Inner modules. This will affect only the page you are currently viewing.', 'wecodeart' ),
				'priority'		=> 15, 
				'choices'		=> $c_modules, 
				'transport'		=> 'postMessage',
				'active_callback' => 'is_home',
				'partial'		=> [
					'selector'        		=> '.content-area',
					'render_callback' 		=> [ 'WeCodeArt\Core\Content', 'render_modules' ],
					'container_inclusive' 	=> true
				]
			)
		);

		$configurations = array_merge( $configurations, $_configs );

		// Page specific Mods
		$pages = get_pages();
		foreach( $pages as $page ) {
			$title = $page->post_title;
			$ID = $page->ID;
			$config = array(
				array(
					'name'			=> 'content-layout-container-page-' . $ID,
					'type'        	=> 'control',
					'control'  		=> 'select',
					'section'		=> 'content-layout',
					'title' 		=> sprintf( esc_html__( 'Container Type: %s', 'wecodeart' ), $title ),
					'description' 	=> esc_html__( 'Choose the type of the container class.', 'wecodeart' ),
					'choices'  		=> [
						'container'			=> esc_html__( 'Container', 'wecodeart' ),
						'container-fluid' 	=> esc_html__( 'Container Fluid', 'wecodeart' ),
					], 
					'priority'             => 20,
					'default'              => 'container',
					'active_callback'	   => function() use ( $ID ) { return is_page( $ID ); },
					'transport' 		   => 'postMessage'
				),
				array(
					'name'			=> 'content-layout-modules-page-' . $ID,
					'type'        	=> 'control',
					'control'  		=> 'wecodeart-sortable',
					'section'		=> 'content-layout',
					'title'			=> sprintf( esc_html__( 'Content Modules: %s', 'wecodeart' ), $title ),
					'description'	=> esc_html__( 'Enable and reorder Site Inner modules. This will affect only the page you are currently viewing.', 'wecodeart' ),
					'priority'		=> 30,
					'default'		=> [ 'content', 'primary' ],
					'choices'		=> $c_modules,
					'active_callback'	=> function() use ( $ID ) { return is_page( $ID ); },
					'transport'		=> 'postMessage',
					'partial'		=> [
						'selector'        		=> '.content-area',
						'render_callback' 		=> [ 'WeCodeArt\Core\Content', 'render_modules' ],
						'container_inclusive'	=> true
					]
				) 	 	
			);
			$configurations = array_merge( $configurations, $config );
		}

		// Post Types Archives And Singular Context Mods 
		$public_posts = wecodeart( 'public_post_types' ); 
		if( isset( $public_posts['product'] ) ) {
			unset( $public_posts['product'] );
		}

		foreach( $public_posts as $type ) { 
			$type_label = get_post_type_object( $type )->labels->singular_name;
			$config = array(
				array(
					'name'			=> 'content-layout-container-' . $type . '-archive',
					'type'        	=> 'control',
					'control'  		=> 'select',
					'section'		=> 'content-layout',
					'title' 		=> sprintf( esc_html__( 'Container Type: %s Archive', 'wecodeart' ), $type_label ),
					'description' 	=> esc_html__( 'Choose the type of the container class.', 'wecodeart' ),
					'choices'  		=> [
						'container'			=> esc_html__( 'Container', 'wecodeart' ),
						'container-fluid' 	=> esc_html__( 'Container Fluid', 'wecodeart' ),
					], 
					'priority'             => 20, 
					'active_callback'	   => function() use ( $type ) { return is_post_type_archive( $type ); },
					'transport' 		   => 'postMessage'
				),
				array(
					'name'			=> 'content-layout-modules-' . $type . '-archive',
					'type'        	=> 'control',
					'control'  		=> 'wecodeart-sortable',
					'section'		=> 'content-layout',
					'title'			=> sprintf( esc_html__( 'Content Modules: %s Archive', 'wecodeart' ), $type_label ),
					'description'	=> esc_html__( 'Enable and reorder Site Inner modules. This will affect only the page you are currently viewing.', 'wecodeart' ),
					'priority'		=> 30, 
					'choices'		=> $c_modules,
					'active_callback'	=> function() use( $type ) { return is_post_type_archive( $type ); }, 
					'transport'		=> 'postMessage',
					'partial'		=> [
						'selector'        => '.content-area',
						'render_callback' => [ 'WeCodeArt\Core\Content', 'render_modules' ],
						'container_inclusive' => true
					]
				),
				array(
					'name'			=> 'content-layout-container-' . $type . '-singular',
					'type'        	=> 'control',
					'control'  		=> 'select',
					'section'		=> 'content-layout',
					'title' 		=> sprintf( esc_html__( 'Container Type: %s Single', 'wecodeart' ), $type_label ),
					'description' 	=> esc_html__( 'Choose the type of the container class.', 'wecodeart' ),
					'choices'  		=> array(
						'container'			=> esc_html__( 'Container', 'wecodeart' ),
						'container-fluid' 	=> esc_html__( 'Container Fluid', 'wecodeart' ),
					), 
					'priority'             => 25, 
					'active_callback'	   => function() use ( $type ) { return is_singular( $type ); },
					'transport' 		   => 'postMessage'
				),
				array(
					'name'			=> 'content-layout-modules-' . $type . '-singular',
					'type'        	=> 'control',
					'control'  		=> 'wecodeart-sortable',
					'section'		=> 'content-layout',
					'title'			=> sprintf( esc_html__( 'Content Modules: %s Single', 'wecodeart' ), $type_label ),
					'description'	=> esc_html__( 'Enable and reorder Site Inner modules. This will affect only the page you are currently viewing.', 'wecodeart' ),
					'priority'		=> 35, 
					'choices'		=> $c_modules,
					'active_callback'	=> function() use( $type ) { return is_singular( $type ); }, 
					'transport'		=> 'postMessage',
					'partial'		=> [
						'selector'        		=> '.content-area',
						'render_callback' 		=> [ 'WeCodeArt\Core\Content', 'render_modules' ],
						'container_inclusive' 	=> true
					]
				)	 	
			);
			$configurations = array_merge( $configurations, $config );
		}
		
		/**
		 * Entry Configurable Meta Info
		 * @since 3.6 
		 */
		$meta_modules = wp_list_pluck( \WeCodeArt\Core\Entry\Meta::modules(), 'label' );

		foreach( $public_posts as $type ) { 
			if( ! post_type_supports( $type, 'wecodeart-post-info' ) ) {
				continue;
			}

			$type_label = get_post_type_object( $type )->labels->singular_name;
			$meta_config = array(  
				array(
					'name'			=> 'content-entry-meta-' . $type . '-archive',
					'type'        	=> 'control',
					'control'  		=> 'wecodeart-sortable',
					'section'		=> 'content-entry',
					'title'			=> sprintf( esc_html__( 'Meta Modules: %s Archive', 'wecodeart' ), $type_label ),
					'description'	=> esc_html__( 'Enable/Disable/Reorder Entry Meta information modules for current post type - archive.', 'wecodeart' ),
					'priority'		=> 5, 
					'choices'		=> $meta_modules,  
					'transport'		=> 'postMessage',
					'active_callback' => function() use ( $type ) {
						if( $type === 'post' && Callbacks::is_post_archive() ) return true;
						return is_post_type_archive( $type );
					},
					'partial'		=> [
						'selector'        		=> '.entry-meta',
						'render_callback' 		=> [ 'WeCodeArt\Core\Entry\Meta', 'render' ],
						'container_inclusive' 	=> true
					]
				),
				array(
					'name'			=> 'content-entry-meta-' . $type . '-singular',
					'type'        	=> 'control',
					'control'  		=> 'wecodeart-sortable',
					'section'		=> 'content-entry',
					'title'			=> sprintf( esc_html__( 'Meta Modules: %s Single', 'wecodeart' ), $type_label ),
					'description'	=> esc_html__( 'Enable/Disable/Reorder Entry Meta information modules for current post type - single.', 'wecodeart' ),
					'priority'		=> 10, 
					'choices'		=> $meta_modules,  
					'transport'		=> 'postMessage',
					'active_callback' => function() use( $type ) { return is_singular( $type ); },
					'partial'		=> [
						'selector'        		=> '.entry-meta',
						'render_callback' 		=> [ 'WeCodeArt\Core\Entry\Meta', 'render' ],
						'container_inclusive' 	=> true
					]
				)
			); 
			// Merge to main config
			$configurations = array_merge( $configurations, $meta_config );
		}

		return $configurations;
	}
}