<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Customizer\Configs\Header
 * @copyright   Copyright (c) 2020, WeCodeArt Framework
 * @since 		3.5
 * @version		3.9.9
 */

namespace WeCodeArt\Customizer\Configs;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Customizer\Config;
use WeCodeArt\Customizer\Formatting;

/**
 * Customizer Config initial setup
 */
class Header extends Config {
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

		$_configs = array( 
			array(
				'name'			=> 'header-bar-container',
				'type' 			=> 'control',
				'control'  		=> 'select',
				'section'		=> 'header-bar',
				'title' 		=> esc_html__( 'Grid Type', 'wecodeart' ),
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
				'name'			=> 'header-bar-modules',
				'type'        	=> 'control',
				'control'  		=> 'wecodeart-sortable',
				'section'		=> 'header-bar',
				'title'        	=> esc_html__( 'Header Bar Modules', 'wecodeart' ),
				'description'	=> esc_html__( 'Enable and reorder Header Bar modules.', 'wecodeart' ),
				'priority'   	=> 10, 
				'choices'		=> wp_list_pluck( \WeCodeArt\Core\Header::nav_bar_modules(), 'label' ),
				'transport'		=> 'postMessage',
				'partial'		=> [
					'selector'        		=> '.header__bar.header-bar',
					'render_callback' 		=> [ 'WeCodeArt\Core\Header', 'render_header_bar' ],
					'container_inclusive' 	=> true
				]
			)
		);

		return array_merge( $configurations, $_configs );
	}
}
