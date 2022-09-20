<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg\Blocks
 * @copyright   Copyright (c) 2022, WeCodeArt Framework
 * @since		5.0.0
 * @version		5.6.7
 */

namespace WeCodeArt\Gutenberg\Blocks\Widgets;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Social block.
 */
class Social extends Dynamic {

	use Singleton;

	/**
	 * Block namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'core';

	/**
	 * Block name.
	 *
	 * @var string
	 */
	protected $block_name = 'social-links';

	/**
	 * Shortcircuit Register
	 */
	public function register() {
		\add_filter( 'render_block_core/' . $this->block_name, [ $this, 'render' ], 10, 2 );
	}

	/**
	 * Dynamically renders the `core/social` block.
	 *
	 * @param 	string 	$content 	The block markup.
	 * @param 	array 	$block 		The parsed block.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( $content = '', $block = [], $data = null ) {
		$attributes = get_prop( $block, 'attrs', [] );
		$services 	= wp_list_pluck( wp_list_pluck( get_prop( $block, 'innerBlocks', [] ), 'attrs' ), 'service' );
		
		if( ! empty( $services ) ) {
			$inline_css = '';
			
			$classnames = explode( ' ', get_prop( $attributes, 'className', '' ) );
			$classnames = array_filter( $classnames, function( $val ) {
				return strpos( $val, 'is-style-' ) === 0;
			} );

			static $loaded_styles = [
				'classes'	=> [],
				'services' 	=> []
			];

			foreach( $services as $service ) {
				$classname = current( $classnames );

				if( 
					in_array( $classname, $loaded_styles['classes'], true ) &&
					in_array( $service, $loaded_styles['services'], true )
				) continue;

				$inline_css .= $this->get_inline_style( $classname , $service );
				$loaded_styles['classes'][] = $classname;
				$loaded_styles['services'][] = $service;
			}
			
			add_action( 'wp_print_styles', function() use ( $inline_css ) {
				wp_add_inline_style( 'wp-block-' . $this->block_name, $inline_css );
			} );
		}

		return $content;
	}

	/**
	 * Get Styles
	 *
	 * @param 	string  $style		CSS classname
	 * @param 	string  $service 	Social Service name
	 *
	 * @return 	string 	Sanitized/Compressed CSS
	 */
	public function get_inline_style( string $style = 'default', string $service = '' ) {
		$inline_css = '';

		if( empty( $service ) ) return $inline_css;

		$colors = [
			'link'		=> '', // Preserve link color
			'amazon' 	=> '#f90',
			'bandcamp' 	=> '#1ea0c3',
			'behance' 	=> '#0757fe',
			'codepen' 	=> '#1e1f26',
			'deviantart'=> '#02e49b',
			'dribbble' 	=> '#e94c89',
			'dropbox' 	=> '#4280ff',
			'etsy' 		=> '#f45800',
			'facebook' 	=> '#1977f2',
			'fivehundredpx'	=> '#000',
			'flickr' 	=> '#0461dd',
			'foursquare'=> '#e65678',
			'github' 	=> '#24292d',
			'goodreads'	=> '#eceadd',
			'google' 	=> '#ea4434',
			'instagram'	=> '#f00075',
			'lastfm' 	=> '#e21b24',
			'linkedin' 	=> '#0577b5',
			'mastodon' 	=> '#3288d4',
			'meetup' 	=> '#02ab6c',
			'pinterest'	=> '#e60122',
			'pocket' 	=> '#ef4155',
			'reddit' 	=> '#fe4500',
			'skype' 	=> '#0478d7',
			'snapchat' 	=> '#fefc00',
			'soundcloud'=> '#ff5600',
			'spotify' 	=> '#1bd760',
			'tumblr' 	=> '#011835',
			'twitch' 	=> '#6440a4',
			'twitter' 	=> '#21a1f3',
			'vk' 		=> '#4680c2',
			'vimeo' 	=> '#1eb7ea',
			'wordpress'	=> '#3499cd',
			'whatsapp'	=> '#25d366',
			'yelp' 		=> '#d32422',
			'youtube'	=> '#ff0100',
		];

		$selector 	= '.wp-block-social-links a';
		$properties = [
			'color' => isset( $colors[$service] ) ? $colors[$service] : 'inherit'
		];

		// Logos only
		if( $style === 'is-style-logos-only' ) {
			$selector = '.wp-block-social-links.is-style-logos-only .wp-social-link-' . $service . ' a';
			$properties = [
				'color'	=> $colors[$service]
			];

			if( $service === 'goodreads' ) {
				$properties = wp_parse_args( [
					'color' => '#382110'
				], $properties );
			}

			if( $service === 'snapchat' ) {
				$properties = wp_parse_args( [
					'color' 	=> 'white',
				], $properties );
			}
			
			if( $service === 'yelp' ) {
				$properties = wp_parse_args( [
					'background-color' => $colors[$service],
					'color' => 'white',
				], $properties );
			}
		// Default style
		} else {
			$selector = '.wp-block-social-links:not(.is-style-logos-only) .wp-social-link-' . $service . ' a';
			$properties = [
				'background-color' => $colors[$service],
				'color'	=> '#fff'
			];

			if( $service === 'goodreads' ) {
				$properties = wp_parse_args( [
					'color' => '#382110'
				], $properties );
			}
		}
		
		// Snapchat has 2 pair ping-pong balls :D
		if( $service === 'snapchat' ) {
			$properties = wp_parse_args( [
				'stroke' => '#000'
			], $properties );
		}

		$css_array = [];
		$css_array['global'][$selector] = $properties;

		$inline_css = wecodeart( 'styles' )::parse( $css_array, 'wp-social-link-' . $service );
		$inline_css = wecodeart( 'styles' )::compress( $inline_css );

		return $inline_css;
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		return "
		.wp-block-social-links {
			display: flex;
			flex-wrap: wrap;
			align-items: center;
			padding-left: 0;
			padding-right: 0;
			gap: var(--wp--style--block-gap,1em);
			font-size: 1.25rem;
		}
		.wp-block-social-links.aligncenter {
			display: flex;
			justify-content: center;
		}
		.wp-block-social-links.is-style-pill-shape .wp-social-link {
			width: auto;
		}
		.wp-block-social-links.is-style-pill-shape .wp-social-link a {
			padding-left: calc((2.5/3) * 1em);
			padding-right: calc((2.5/3) * 1em);
		}
		.wp-block-social-links.is-style-square-shape .wp-social-link a {
			border-radius: 0;
		}
		.wp-block-social-links.is-style-logos-only .wp-social-link a {
			background: none;
			padding: 0;
		}
		.wp-social-link {
			display: block;
			background-color: transparent;
		}
		.wp-social-link a {
			display: flex;
			flex-direction: column;
			justify-content: center;
			align-items: center;
			text-align: center;
			border-radius: 9999px;
			transition: all 0.1s ease;
			padding: 0.35em;
			line-height: 0;
			text-decoration: none;
			border: none;
			box-shadow: none;
		}
		.wp-social-link svg {
			width: 1em;
			height: 1em;
		}
		.navbar .wp-block-social-links {
			margin-top: 0;
			margin-bottom: 0;
		}
		";
	}
}
