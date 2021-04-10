<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Customizer\Configs\Design
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		4.2.0
 * @version		4.2.0
 */

namespace WeCodeArt\Admin\Customizer\Configs;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Admin\Customizer\Config;
use function WeCodeArt\Functions\get_color_palette;

/**
 * Customizer Config Design
 */
class Design extends Config {
	/**
	 * Register Site Layout Customizer Configurations
	 *
	 * @param 	array                $configurations 
	 * @param 	WP_Customize_Manager $wp_customize instance of WP_Customize_Manager.
	 * @since 	4.2
	 *
	 * @return 	array 
	 */
	public function register( $configurations, $wp_customize ) {
		$_configs = [
			[
				'name'			=> 'general-design-palette',
				'type' 			=> 'control',
				'control'  		=> 'wecodeart-palette',
				'section'		=> 'general-design',
				'title' 		=> esc_html__( 'Site Palette', 'wecodeart' ),
				'transport'		=> 'postMessage',
				'default' 		=> get_color_palette(),
				'output'		=> [
					[
						'element'  	=> 'body',
						'property' 	=> 'background-color',
						'key'		=> 'site'
					],
					[
						'element'  	=> 'body',
						'property' 	=> 'color',
						'key'		=> 'text'
					],
					[
						'element'  	=> ':root .has-dark-background-color',
						'property' 	=> 'color',
						'key'		=> 'text-dark'
					],
					[
						'element'  	=> ':root .has-dark-background-color',
						'property' 	=> 'background-color',
						'key'		=> 'dark'
					],
					[
						'element'  	=> ':root .has-light-background-color',
						'property' 	=> 'background-color',
						'key'		=> 'light'
					],
					[
						'element'  	=> ':root',
						'property' 	=> '--wca-primary',
						'key'		=> 'primary'
					],
					[
						'element'  	=> ':root',
						'property' 	=> '--wca-secondary',
						'key'		=> 'secondary'
					],
					[
						'element'  	=> 'h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6',
						'property' 	=> 'color',
						'key'		=> 'heading'
					],
					[
						'element'  	=> 'a',
						'property' 	=> 'color',
						'key'		=> 'link'
					]
				]
			],
		];

		return array_merge( $configurations, $_configs );
	}
}
