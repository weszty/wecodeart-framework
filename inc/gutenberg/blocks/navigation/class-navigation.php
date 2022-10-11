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
 * @version		5.6.9
 */

namespace WeCodeArt\Gutenberg\Blocks;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Config\Traits\Asset;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Navigation block.
 */
class Navigation extends Dynamic {

	use Singleton;
	use Asset;

	/**
	 * Block static.
	 *
	 * @var mixed
	 */
	protected static $responsive_loaded = null;
	protected static $themes_loaded = [];

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
	protected $block_name = 'navigation';

	/**
	 * Shortcircuit Register
	 */
	public function register() {
		\add_filter( 'block_type_metadata_settings', 			[ $this, 'filter_render' ], 10, 2 );
		\add_filter( 'block_core_navigation_render_fallback', 	[ $this, 'fallback' ] );
	}

	/**
	 * Filter navigation markup
	 *
	 * @param	array 	$settings
	 * @param	array 	$data
	 */
	public function filter_render( $settings, $data ) {
		if ( $this->get_block_type() === $data['name'] ) {
			$settings = wp_parse_args( [
				'render_callback' => [ $this, 'render' ]
			], $settings );
		}
		
		return $settings;
	}

	/**
	 * Dynamically renders the `core/navigation` block.
	 *
	 * @param 	array 	$attributes The block attributes.
	 * @param 	string 	$content 	The block content.
	 * @param 	array 	$block 		The block data.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( $attributes = [], $content = '', $block = null ) {
		if ( $color = get_prop( $attributes, 'rgbTextColor' ) ) {
			$attributes['customTextColor'] = $color;
		}
		
		if ( $color = get_prop( $attributes, 'rgbBackgroundColor' ) ) {
			$attributes['customBackgroundColor'] = $color;
		}

		unset( $attributes['rgbTextColor'], $attributes['rgbBackgroundColor'] );

		$inner_blocks = $block->inner_blocks;
		
		// Ensure that blocks saved with the legacy ref attribute name (navigationMenuId) continue to render.
		if ( array_key_exists( 'navigationMenuId', $attributes ) ) {
			$attributes['ref'] = $attributes['navigationMenuId'];
		}

		// Load inner blocks from the navigation post.
		if ( array_key_exists( 'ref', $attributes ) ) {
			$navigation_post = get_post( $attributes['ref'] );
			
			if ( isset( $navigation_post ) ) { 
				$parsed_blocks = parse_blocks( $navigation_post->post_content );
				$parsed_blocks = block_core_navigation_filter_out_empty_blocks( $parsed_blocks );
				
				$inner_blocks = new \WP_Block_List( $parsed_blocks, $attributes );
			}
		}
		
		// If there are no inner blocks then fallback to rendering an appropriate fallback.
		if ( empty( $inner_blocks ) ) {
			$parsed_blocks 	= block_core_navigation_get_fallback_blocks();

			// Fallback might have been filtered so do basic test for validity.
			if ( empty( $parsed_blocks ) || ! is_array( $parsed_blocks ) ) {
				return '';
			}

			$inner_blocks = new \WP_Block_List( $parsed_blocks, $attributes );
		}

		$block_id	= wp_unique_id( 'wp-navigation-' );
		$attrs		= $this->get_wrapper_attributes( $attributes );

		// This CSS holds the block customization.
		if( ! wp_style_is( 'wp-block-' . $this->block_name . '-custom' ) ) {
			wp_register_style( 'wp-block-' . $this->block_name . '-custom', '', [
				'wp-block-' . $this->block_name
			], wecodeart( 'version' ) );
			wp_enqueue_style( 'wp-block-' . $this->block_name . '-custom' );
		}

		// Add Expand CSS.
		$classname = get_prop( $attrs, [ 'class' ], '' );
		if( strpos( $classname, 'navbar-expand' ) !== false ) {
			wp_add_inline_style( 'wp-block-' . $this->block_name . '-custom', $this->get_responsive_styles( 'expand' ) );
		}

		// Add Themes CSS.
		preg_match( '/navbar-(light|dark)/i', $classname, $navbar_theme );
		if( count( $navbar_theme ) && ! in_array( current( $navbar_theme ), self::$themes_loaded ) ) {

			wp_add_inline_style( 'wp-block-' . $this->block_name . '-custom', $this->get_color_styles( current( $navbar_theme ) ) );
			
			self::$themes_loaded[] = current( $navbar_theme );
		}

		return wecodeart( 'markup' )::wrap( 'navbar', [ [
			'tag' 	=> 'nav',
			'attrs'	=> $attrs
		] ], function( $attributes, $inner_blocks ) use ( $block_id ) {

			// Navbar List HTML
			$html = wecodeart( 'markup' )::wrap( 'navbar-nav', [ [
				'tag' 	=> 'ul',
				'attrs' => [
					'class' => 'wp-block-navigation__container nav navbar-nav',
				]
			] ], function( $inner_blocks ) {
				foreach( $inner_blocks as $inner_block ) echo $this->render_menu_block( $inner_block );
			}, [ $inner_blocks ], false );

			// Is responsive? Render in offcanvas container
			if( get_prop( $attributes, 'overlayMenu' ) !== 'never' ) {

				// Styles
				if( is_null( self::$responsive_loaded ) ) {

					wp_add_inline_style( 'wp-block-' . $this->block_name . '-custom', $this->get_responsive_styles() );
					
					self::$responsive_loaded = true;
				}

				// Scripts
				if( ! wp_script_is( $this->make_handle() ) ) {
					wp_enqueue_script( $this->make_handle(), $this->get_asset( 'js', 'modules/offcanvas' ), [], wecodeart( 'version' ), true );
				}

				// Toggler
				wecodeart_template( 'general/toggler', [
					'id'		=> $block_id,
					'toggle' 	=> 'offcanvas',
					'icon'		=> get_prop( $attributes, 'hasIcon' )
				] );

				// OffCanvas
				wecodeart_template( 'general/offcanvas', [
					'id'		=> $block_id,
					'classes'	=> [ 'offcanvas-start' ],
					'content' 	=> $html,
				] );

				return;
			}

			// Is ok, we use the WP block rendering
			echo $html;

		}, [ $attributes, $inner_blocks ], false );
	}

	/**
	 * Renders menu item wrapper.
	 *
	 * @param 	object 	$block.
	 *
	 * @return 	string 	Rendered block with wrapper block
	 */
	public function render_menu_block( $block ) {
		$html = $block->render();
		
		// For this specific blocks, please wrap them in a <li> for valid markup
		if( in_array( get_prop( $block->parsed_block, 'blockName', '' ), [
			'core/spacer',
			'core/search',
			'core/social-links',
			'core/site-title',
			'core/site-logo',
		] ) ) {
			$classes	= [ 'wp-block-navigation-link', 'nav-item' ];
			$classes[]  = 'nav-item--' . join( '-', explode( '/', get_prop( $block->parsed_block, 'blockName' ) ) );

			return wecodeart( 'markup' )::wrap( 'nav-item', [ [
				'tag' 	=> 'li',
				'attrs'	=> [
					'class'	=> join( ' ', $classes ),
				]
			] ], $html, [], false );
		}

		return $html;
	}

	/**
	 * Get Class Color.
	 *
	 * @param 	array 	$context	Block Context
	 * @param 	string 	$key		Attribute name
	 * 
	 * @return 	string 	HEX color code for pallete class
	 */
	public static function get_class_color( $context, $key = 'background' ) {
		$palette 	= wecodeart_json( [ 'settings', 'color', 'palette', 'default' ], [] );
		$palette 	= wecodeart_json( [ 'settings', 'color', 'palette', 'theme' ], $palette );
		$palette 	= array_merge( $palette, wecodeart_json( [ 'settings', 'color', 'palette', 'custom' ], [] ) );
				
		$_keys 		= [
			'overlay-background' 	=> 'overlayBackgroundColor',
			'overlay-text' 			=> 'overlayTextColor',
			'custom-background'		=> 'customBackgroundColor',
			'background' 			=> 'backgroundColor',
			'text'		 			=> 'textColor',
		];

		// Real attribute name
		$attribute 	= isset( $_keys[$key] ) ? $_keys[$key] : $key;

		// Get custom
		$color 		= get_prop( $context, [ 'style', 'color', $attribute ] );
		
		// If not custom, get named
		if( $color === null ) {
			$color = get_prop( $context, $attribute, $color );
			$color = get_prop( current( wp_list_filter( $palette, [ 'slug' => $color ] ) ), 'color', false );
		}

		// If named not found, fallback to body
		if( $color === false ) {
			$styles 	= wecodeart_json( [ 'styles', 'color', $key ], '' );
			if( strpos( $styles, '#' ) === 0 ) {
				$color = $styles;
			} else {
				if( mb_strpos( $styles, '|' ) !== false ) {
					$slug = explode( '|', $styles );
					$slug = end( $slug );
				} elseif( mb_strpos( $styles, '--' ) !== false ) {
					$slug = explode( '--', $styles );
					$slug = str_replace( ')', '', end( $slug ) );
				}
				$color	= get_prop( current( wp_list_filter( $palette, [
					'slug' => $slug,
				] ) ), 'color', '#ffffff' );
			}
		}

		return $color;
	}

	/**
	 * Build an array with CSS classes and inline styles defining the colors
	 * which will be applied to the navigation markup in the front-end.
	 *
	 * @param  array $attributes 	Navigation block context.
	 * @return array  	 			CSS classes and inline styles.
	 */
	public function get_colors( $attributes ) {
		$colors = [
			'classes'   => [],
			'styles' 	=> '',
		];
	
		// Text color.
		$has_named_text_color  = get_prop( $attributes, 'textColor', false );
		$has_custom_text_color = get_prop( $attributes, 'customTextColor', false );

		// Overlays - added via CSS because of extra specificity
		
		// Background color.
		$has_named_background_color  = get_prop( $attributes, 'backgroundColor', false );
		$has_custom_background_color = get_prop( $attributes, 'customBackgroundColor', false );
	
		// If has text color.
		if ( $has_custom_text_color || $has_named_text_color ) {
			// Add has-text-color class.
			$colors['classes'][] = 'has-text-color';
		}
	
		if ( $has_named_text_color ) {
			// Add the color class.
			$colors['classes'][] = sprintf( 'has-%s-color', $has_named_text_color );
		} elseif ( $has_custom_text_color ) {
			// Add the custom color inline style.
			$colors['styles'] .= sprintf( 'color: %s;', $has_custom_text_color );
			$colors['styles'] .= sprintf( '--wp--custom--color: %s;', $has_custom_text_color );
		}
	
		// If has background color.
		if ( $has_custom_background_color || $has_named_background_color ) {
			// Add has-background class.
			$colors['classes'][] = 'has-background';
		}
	
		if ( $has_named_background_color ) {
			// Add the background-color class.
			$colors['classes'][] = sprintf( 'has-%s-background-color', $has_named_background_color );
		} elseif ( $has_custom_background_color ) {
			// Add the custom background-color inline style.
			$colors['styles'] .= sprintf( 'background-color: %s;', $has_custom_background_color );
			$colors['styles'] .= sprintf( '--wp--custom--background-color: %s;', $has_custom_background_color );
		}
	
		return $colors;
	}

	/**
	 * Build an array with CSS classes and inline styles defining the font sizes
	 * which will be applied to the navigation markup in the front-end.
	 *
	 * @param  array $attributes 	Navigation block context.
	 * @return array 				Font size CSS classes and inline styles.
	 */
	public function get_typography( $attributes ) {
		// CSS classes.
		$typography = [
			'classes'   => [],
			'styles' 	=> '',
		];

		if ( $value = get_prop( $attributes, 'fontSize' ) ) {
			// Add the font size class.
			$typography['classes'][] = sprintf( 'has-%s-font-size', $value );
		} elseif ( $value = get_prop( $attributes, 'customFontSize' ) ) {
			// Add the custom font size inline style.
			$typography['styles'] = sprintf( 'font-size: %spx;--wp--custom--font-size: %spx;', $value, $value );
		}

		return $typography;
	}

	/**
	 * Return an array of wrapper attributes.
	 * 
	 * @return 	array
	 */
	public function get_wrapper_attributes( $attributes ) {
		$colors     = $this->get_colors( $attributes );
		$typography = $this->get_typography( $attributes );
		$background = get_prop( $attributes, 'customBackgroundColor', self::get_class_color( $attributes ) );

		$classes 	= [ 'wp-block-navigation', 'navbar' ];
		$classes[] 	= ( wecodeart( 'styles' )::color_lightness( $background ) < 380 ) ? 'navbar-dark' : 'navbar-light';
		
		if( get_prop( $attributes, [ 'layout', 'orientation' ] ) === 'horizontal' ) {
			$classes[] = 'navbar-expand';
		}
		
		if( $value = get_prop( $attributes, 'overlayMenu' ) ) {
			if( $value !== 'never' ) {
				$classes 	= array_diff( $classes, [ 'navbar-expand' ] );
			}

			if( $value === 'mobile' ) {
				$classes[] 	= $this->get_mobile_breakpoint();
			}
		}
		
		if( get_prop( $attributes, 'openSubmenusOnClick', false ) === false ) {
			$classes[] 	= 'with-hover';
		}

		if( get_prop( $attributes, 'showSubmenuIcon', false ) === false ) {
			$classes[] 	= 'hide-toggle';
		}

		$classnames = explode( ' ',  get_prop( $attributes, 'className', '' ) );

		$classes    	= array_merge( $classes, $colors['classes'], $typography['classes'], $classnames );
		$block_styles 	= get_prop( $attributes, 'styles', '' );

		return [
			'class'	=> join( ' ', array_filter( $classes ) ),
			'style'	=> $block_styles . $colors['styles'] . $typography['styles'],
		];
	}

	/**
	 * Return filter mobile breakpoint class.
	 * 
	 * @return 	array
	 */
	public function get_mobile_breakpoint() {
		return sanitize_html_class( 'navbar-expand-' . wecodeart_json( [ 'settings', 'custom', 'mobileBreakpoint' ], 'lg' ) );
	}

	/**
	 * Fallback arguments.
	 *
	 * @return array
	 */
	public function fallback( $blocks ) {
		$blocks[0]['attrs']['__unstableMaxPages'] = 3;

		return $blocks;
	}

	/**
	 * Generate Styles.
	 *
	 * @return string
	 */
	public function get_responsive_styles( $type = 'default' ) {
		$close		= 'data:image/svg+xml,%3csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="%23000"%3e%3cpath d="M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z"/%3e%3c/svg%3e';

		$breaks 	= wecodeart_json( [ 'settings', 'custom', 'breakpoints' ], [] );
		$filter		= explode( '-', Navigation::get_instance()->get_mobile_breakpoint() );
		$filter		= end( $filter );
		$breakpoint	= get_prop( $breaks, $filter, '992px' ); 

		$inline = '';

		switch( $type ) :
			case 'expand':
				$inline .= "
					.navbar-expand {
						flex-wrap: nowrap;
						justify-content: flex-start;
					}
					.navbar-expand .navbar-nav {
						flex-direction: row;
					}
					.navbar-expand .navbar-nav .dropdown-menu {
						position: absolute;
					}
					.navbar-expand .navbar-nav .nav-link {
						padding-right: 0.5rem;
						padding-left: 0.5rem;
					}
					.navbar-expand .navbar-nav-scroll {
						overflow: visible;
					}
					.navbar-expand .navbar-collapse {
						display: flex !important;
						flex-basis: auto;
					}
					.navbar-expand .navbar-toggler {
						display: none;
					}
					.navbar-expand .offcanvas-header {
						display: none;
					}
					.navbar-expand .offcanvas {
						position: inherit;
						bottom: 0;
						z-index: 1000;
						flex-grow: 1;
						visibility: visible !important;
						background-color: transparent;
						border-right: 0;
						border-left: 0;
						transition: none;
						transform: none;
					}
					.navbar-expand .offcanvas-top,
					.navbar-expand .offcanvas-bottom {
						height: auto;
						border-top: 0;
						border-bottom: 0;
					}
					.navbar-expand .offcanvas-body {
						display: flex;
						flex-grow: 0;
						padding: 0;
						overflow-y: visible;
					}
				";
			break;
			default:
				$inline .= "
					/* Offcanvas */
					.offcanvas {
						position: fixed;
						bottom: 0;
						display: flex;
						flex-direction: column;
						max-width: 100%;
						visibility: hidden;
						background-color: #fff;
						background-clip: padding-box;
						outline: 0;
						transition: transform 0.3s ease-in-out;
						z-index: 1045;
					}
					.offcanvas-backdrop {
						position: fixed;
						top: 0;
						left: 0;
						width: 100vw;
						height: 100vh;
						background-color: #000;
						z-index: 1040;
					}
					.offcanvas-backdrop.fade {
						opacity: 0;
					}
					.offcanvas-backdrop.show {
						opacity: 0.5;
					}
					.offcanvas-header {
						display: flex;
						align-items: center;
						justify-content: space-between;
						padding: 1rem 1rem;
					}
					.offcanvas-header .btn-close {
						padding: 0.5rem 0.5rem;
						margin-top: -0.5rem;
						margin-right: -0.5rem;
						margin-bottom: -0.5rem;
					}
					.offcanvas-title {
						margin-bottom: 0;
						line-height: 1.5;
					} 
					.offcanvas-body {
						flex-grow: 1;
						padding: 1rem 1rem;
						overflow-y: auto;
					}
					.offcanvas-start {
						top: 0;
						left: 0;
						width: 400px;
						border-right: 1px solid rgba(0, 0, 0, 0.2);
						transform: translateX(-100%);
					}
					.offcanvas-end {
						top: 0;
						right: 0;
						width: 400px;
						border-left: 1px solid rgba(0, 0, 0, 0.2);
						transform: translateX(100%);
					}
					.offcanvas-top {
						top: 0;
						right: 0;
						left: 0;
						height: 30vh;
						max-height: 100%;
						border-bottom: 1px solid rgba(0, 0, 0, 0.2);
						transform: translateY(-100%);
					} 
					.offcanvas-bottom {
						right: 0;
						left: 0;
						height: 30vh;
						max-height: 100%;
						border-top: 1px solid rgba(0, 0, 0, 0.2);
						transform: translateY(100%);
					}
					.offcanvas.show {
						transform: none;
					}
					
					/* Close */
					.btn-close {
						box-sizing: content-box;
						width: 1em;
						height: 1em;
						padding: 0.25em 0.25em;
						color: #000;
						background: transparent url('$close') center/1em auto no-repeat;
						border: 0;
						border-radius: 0.25rem;
						opacity: 0.5;
					}
					.btn-close:hover {
						color: #000;
						text-decoration: none;
						opacity: 0.75;
					}
					.btn-close:focus {
						outline: 0;
						box-shadow: 0 0 0 0.25rem rgba(35, 136, 237, 0.25);
						opacity: 1;
					}
					.btn-close:disabled, .btn-close.disabled {
						pointer-events: none;
						user-select: none;
						opacity: 0.25;
					}
					.btn-close-white {
						filter: invert(1) grayscale(100%) brightness(200%);
					}
					.navbar-toggler {
						padding: 0.25rem 0.75rem;
						font-size: 1.25rem;
						line-height: 1;
						background-color: transparent;
						border: 1px solid transparent;
						border-radius: 0.25rem;
						transition: box-shadow 0.15s ease-in-out;
					}
					.navbar-toggler:hover {
						text-decoration: none;
					}
					.navbar-toggler:focus {
						text-decoration: none;
						outline: 0;
						box-shadow: 0 0 0 0.25rem;
					} 
					.navbar-toggler-icon {
						display: inline-block;
						width: 1.5em;
						height: 1.5em;
						vertical-align: middle;
						background-repeat: no-repeat;
						background-position: center;
						background-size: 100%;
					}
				";
				
				foreach( $breaks as $key => $value ) {
					// Skip what we dont need!
					if( $key !== $filter ) continue;

					$inline .= "
					@media (min-width: {$value}) {
						/* Navbar */
						.navbar-expand-{$key} {
							flex-wrap: nowrap;
							justify-content: flex-start;
						}
						.navbar-expand-{$key} .navbar-nav {
							flex-direction: row;
						}
						.navbar-expand-{$key} .navbar-nav .dropdown-menu {
							position: absolute;
						}
						.navbar-expand-{$key} .navbar-nav-scroll {
							overflow: visible;
						}
						.navbar-expand-{$key} .navbar-collapse {
							display: flex !important;
							flex-basis: auto;
						}
						.navbar-expand-{$key} .navbar-toggler {
							display: none;
						}
						.navbar-expand-{$key} .offcanvas-header {
							display: none;
						}
						.navbar-expand-{$key} .offcanvas {
							position: inherit;
							bottom: 0;
							z-index: 1000;
							flex-grow: 1;
							visibility: visible !important;
							background-color: transparent;
							border-right: 0;
							border-left: 0;
							transition: none;
							transform: none;
						}
						.navbar-expand-{$key} .offcanvas-top,
						.navbar-expand-{$key} .offcanvas-bottom {
							height: auto;
							border-top: 0;
							border-bottom: 0;
						}
						.navbar-expand-{$key} .offcanvas-body {
							display: flex;
							flex-grow: 0;
							padding: 0;
							overflow-y: visible;
						}

						/* Dropdowns */
						.dropdown-menu-{$key}-start {
							--wp-position: start;
						}
						.dropdown-menu-{$key}-start[data-bs-popper] {
							right: auto;
							left: 0;
						}
						.dropdown-menu-{$key}-end {
							--wp-position: end;
						}
						.dropdown-menu-{$key}-end[data-bs-popper] {
							right: 0;
							left: auto;
						}

						/* Block */
						.wp-block-navigation.navbar-expand-{$key} :where(.offcanvas,.offcanvas-body,.navbar-nav) {
							flex-direction: inherit;
							justify-content: inherit;
							align-items: inherit;
						}
						.wp-block-navigation.navbar-expand-{$key}.flex-column .navbar-nav {
							flex-direction: column;
						}
						.wp-block-navigation.navbar-expand-{$key} .wp-block-spacer {
							height: 100%;
							width: var(--wp--spacer-width);
						}
						.wp-block-navigation .dropdown-menu .dropdown-toggle::after {
							border-top: 0.3em solid transparent;
							border-right: 0;
							border-bottom: 0.3em solid transparent;
							border-left: 0.3em solid;
						}
					}
					";
				}

				$inline .= "
					/* No Motion */
					@media (prefers-reduced-motion: reduce) {
						.navbar-toggler,
						.offcanvas {
							transition: none;
						}
					}

					/* Block */
					.wp-block-navigation[class*='navbar-expand-'] .offcanvas:not([aria-modal='true']) {
						width: initial;
					}
					.wp-block-navigation[class*='has-background'] :where(.offcanvas,.offcanvas-body) {
						background-color: inherit;
					}
					.wp-block-navigation .offcanvas-start .btn-close {
						margin-left: auto;
					}
				";
			break;
		endswitch;

		return wecodeart( 'styles' )::compress( $inline );
	}

	/**
	 * Color Shade Styles.
	 *
	 * @return string
	 */
	public function get_color_styles( string $color = 'navbar-light' ) {
		$menu_light = 'data:image/svg+xml,%3csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30"%3e%3cpath stroke="rgba%280, 0, 0, 0.55%29" stroke-linecap="round" stroke-miterlimit="10" stroke-width="2" d="M4 7h22M4 15h22M4 23h22"/%3e%3c/svg%3e';
		$menu_dark 	= 'data:image/svg+xml,%3csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30"%3e%3cpath stroke="rgba%28255, 255, 255, 0.55%29" stroke-linecap="round" stroke-miterlimit="10" stroke-width="2" d="M4 7h22M4 15h22M4 23h22"/%3e%3c/svg%3e';

		$inline = '';
		
		switch( $color ) :
			case 'navbar-dark':
				$inline .= "
				.navbar-dark .navbar-brand {
					color: var(--wp--preset--color--white);
				}
				.navbar-dark .navbar-brand:hover, .navbar-dark .navbar-brand:focus {
					color: var(--wp--preset--color--white);
				}
				.navbar-dark .navbar-nav .nav-link {
					color: rgba(255, 255, 255, 0.55);
				}
				.navbar-dark .navbar-nav .nav-link:hover, .navbar-dark .navbar-nav .nav-link:focus {
					color: rgba(255, 255, 255, 0.75);
				}
				.navbar-dark .navbar-nav .nav-link.disabled {
					color: rgba(255, 255, 255, 0.25);
				}
				.navbar-dark .navbar-nav .show > .nav-link,
				.navbar-dark .navbar-nav .nav-link.active {
					color: var(--wp--preset--color--white);
				}
				.navbar-dark .navbar-toggler {
					color: rgba(255, 255, 255, 0.55);
					border-color: rgba(255, 255, 255, 0.1);
				}
				.navbar-dark .navbar-toggler-icon {
					background-image: url('$menu_dark');
				}
				.navbar-dark .navbar-text {
					color: rgba(255, 255, 255, 0.55);
				}
				.navbar-dark .navbar-text a,
				.navbar-dark .navbar-text a:hover,
				.navbar-dark .navbar-text a:focus {
					color: var(--wp--preset--color--white);
				}
				.navbar-dark .btn-close {
					filter: invert(1) grayscale(100%) brightness(200%);
				}
				";
			break;
			default:
				$inline .= "
					.navbar-light .navbar-brand {
						color: rgba(0, 0, 0, 0.9);
					}
					.navbar-light .navbar-brand:hover, .navbar-light .navbar-brand:focus {
						color: rgba(0, 0, 0, 0.9);
					}
					.navbar-light .navbar-nav .nav-link {
						color: rgba(0, 0, 0, 0.55);
					}
					.navbar-light .navbar-nav .nav-link:hover, .navbar-light .navbar-nav .nav-link:focus {
						color: rgba(0, 0, 0, 0.7);
					}
					.navbar-light .navbar-nav .nav-link.disabled {
						color: rgba(0, 0, 0, 0.3);
					}
					.navbar-light .navbar-nav .show > .nav-link,
					.navbar-light .navbar-nav .nav-link.active {
						color: rgba(0, 0, 0, 0.9);
					}
					.navbar-light .navbar-toggler {
						color: rgba(0, 0, 0, 0.55);
						border-color: rgba(0, 0, 0, 0.1);
					}
					.navbar-light .navbar-toggler-icon {
						background-image: url('$menu_light');
					}
					.navbar-light .navbar-text {
						color: rgba(0, 0, 0, 0.55);
					}
					.navbar-light .navbar-text a,
					.navbar-light .navbar-text a:hover,
					.navbar-light .navbar-text a:focus {
						color: rgba(0, 0, 0, 0.9);
					}
				";
			break;
		endswitch;

		return wecodeart( 'styles' )::compress( $inline );
	}

	/**
	 * Extra Styles.
	 *
	 * @return string
	 */
	public function get_extra_styles( string $variation = 'nav-tabs' ) {
		$inline = '';

		switch( $variation ) :
			case 'nav-tabs':
				$inline .= "
					.nav-tabs {
						border-bottom: 1px solid #dee2e6;
					}
					.nav-tabs .nav-link {
						margin-bottom: -1px;
						background: none;
						border: 1px solid transparent;
						border-top-left-radius: 0.25rem;
						border-top-right-radius: 0.25rem;
					}
					.nav-tabs .nav-link:hover, .nav-tabs .nav-link:focus {
						border-color: #e9ecef #e9ecef #dee2e6;
						isolation: isolate;
					}
					.nav-tabs .nav-link.disabled {
						color: #6c757d;
						background-color: transparent;
						border-color: transparent;
					}
					.nav-tabs .nav-link.active,
					.nav-tabs .nav-item.show .nav-link {
						color: #495057;
						background-color: #fff;
						border-color: #dee2e6 #dee2e6 #fff;
					}
					.nav-tabs .dropdown-menu {
						margin-top: -1px;
						border-top-left-radius: 0;
						border-top-right-radius: 0;
					}
					.tab-content > .tab-pane {
						display: none;
					}
					.tab-content > .active {
						display: block;
					}
				";
			break;
			case 'nav-pills':
				$inline .= "
					.nav-pills .nav-link {
						background: none;
						border: 0;
						border-radius: 0.25rem;
					}
					.nav-pills .nav-link.active,
					.nav-pills .show > .nav-link {
						color: var(--wp--preset--color--white);
						background-color: var(--wp--preset--color--primary);
					}	
				";
			break;
		endswitch;

		return wecodeart( 'styles' )::compress( $inline );
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		$extras = $this->get_extra_styles( 'nav-tabs' ) . $this->get_extra_styles( 'nav-pills' );

		return "
		/* Nav */
		.nav {
			display: flex;
			flex-wrap: wrap;
			padding-left: 0;
			margin-bottom: 0;
			list-style: none;
		}
		.nav-fill > .nav-link,
		.nav-fill .nav-item {
			flex: 1 1 auto;
			text-align: center;
		}
		.nav-justified > .nav-link,
		.nav-justified .nav-item {
			flex-basis: 0;
			flex-grow: 1;
			text-align: center;
		}
		.nav-fill .nav-item .nav-link,
		.nav-justified .nav-item .nav-link {
			width: 100%;
		}

		/* Navbar */
		.navbar {
			position: relative;
			display: flex;
			flex-wrap: wrap;
			align-items: center;
			justify-content: space-between;
			padding-top: 0.5rem;
			padding-bottom: 0.5rem;
		}
		.navbar-brand {
			padding-top: 0.3125rem;
			padding-bottom: 0.3125rem;
			margin-right: 1rem;
			font-size: 1.25rem;
			white-space: nowrap;
		}
		.navbar-nav {
			display: flex;
			flex-direction: column;
			padding-left: 0;
			margin-bottom: 0;
			list-style: none;
			min-width: 160px;
		}
		.navbar-nav .nav-link {
			padding: 0;
		}
		.navbar-nav .dropdown-menu {
			position: static;
		}
		.navbar-text {
			padding-top: 0.5rem;
			padding-bottom: 0.5rem;
		}
		.navbar-collapse {
			flex-basis: 100%;
			flex-grow: 1;
			align-items: center;
		}
		.navbar-nav-scroll {
			max-height: var(--wp--scroll-height, 75vh);
			overflow-y: auto;
		}

		/* Extras */
		{$extras}

		/* No Motion */
		@media (prefers-reduced-motion: reduce) {
			.nav-link {
				transition: none;
			}
		}

		/* Block */
		:is(.wp-site-header,.wp-site-footer) .wp-block-navigation {
			padding-top: 0;
			padding-bottom: 0;
		}
		";
	}
}
