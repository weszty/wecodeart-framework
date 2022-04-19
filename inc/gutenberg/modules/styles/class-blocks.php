<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg CSS Frontend
 * @copyright   Copyright (c) 2022, WeCodeArt Framework
 * @since		5.0.0
 * @version		5.5.5
 */

namespace WeCodeArt\Gutenberg\Modules\Styles;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Support\Styles\Processor;
use function WeCodeArt\Functions\get_prop;

/**
 * Block CSS Processor
 *
 * This class handles all the Gutenberg Core styles from attributes found under style object or theme customs.
 * Any extends of this class, should use process_extra() method for extending the attributes processor.
 */
class Blocks extends Processor {

	/**
	 * Block Name.
	 *
	 * @var 	string
	 */
	protected 	$name 		= '';
	protected 	$element 	= '';

	/**
	 * Block Attrs.
	 *
	 * @var 	array
	 */
	protected 	$attrs = [];

	/**
	 * The class constructor.
	 *
	 * @access 	public
	 * @param 	array 	$args The block args.
	 */
	public function __construct( $args ) {
		$this->name		= get_prop( $args, 'blockName' );
		$this->attrs	= get_prop( $args, 'attrs', [] );

		// Set unique class
		$this->set_element();

		// Process CSS
		$this->process_attributes();
		if( method_exists( $this, 'process_extra' ) ) {
			// Extra attributes
			$this->process_extra();
		}

		// Parse CSS
		$this->parse_output();
		$this->parse_custom();
	}

	/**
	 * Setup element unique class.
	 *
	 * @return 	mixed
	 */
	protected function set_element() {
		$element = wp_unique_id( '.css-' );
		$this->element = apply_filters( 'wecodeart/filter/gutenberg/styles/element', $element, $this->name );
	}

	/**
	 * Parses attributes and creates the styles array for them.
	 *
	 * @return 	void
	 */
	protected function process_attributes() {
		$this->output = [];

		$output 			= [];
		$output['element'] 	= $this->element;

		// Layout - we do not support wide align yet but it will be enabled in a future version!
		if( $layout = get_prop( $this->attrs, 'layout' ) ) {
			$type = get_prop( $layout, 'type', 'default' );

			if( $type === 'default' ) {
				if( get_prop( $layout, 'inherit' ) ) {
					$default_layout = wecodeart_json( [ 'settings', 'layout' ] );
					if ( $default_layout ) {
						$layout = $default_layout;
					}
				}

				$size_default 	= get_prop( $layout, 'contentSize' );
				$size_wide    	= get_prop( $layout, 'wideSize' );
				$size_default 	= $size_default ?: $size_wide;
				$size_wide 		= $size_wide ?: $size_default;

				if( $size_default || $size_wide ) {
					$this->output[] = wp_parse_args( [
						'element'	=> join( '>', [ $this->element, '*' ] ),
						'property' 	=> 'max-width',
						'value'	  	=> $size_default
					], $output );
					$this->output[] = wp_parse_args( [
						'element'	=> join( '>', [ $this->element, '*' ] ),
						'property' 	=> 'margin-left',
						'value'	  	=> 'auto!important'
					], $output );
					$this->output[] = wp_parse_args( [
						'element'	=> join( '>', [ $this->element, '*' ] ),
						'property' 	=> 'margin-right',
						'value'	  	=> 'auto!important'
					], $output );
					$this->output[] = wp_parse_args( [
						'element'	=> join( '>', [ $this->element, '.alignwide' ] ),
						'property' 	=> 'max-width',
						'value'	  	=> $size_wide
					], $output );
					$this->output[] = wp_parse_args( [
						'element'	=> join( '>', [ $this->element, '.alignfull' ] ),
						'property' 	=> 'max-width',
						'value'	  	=> 'none'
					], $output );
				}

				// Block Gap
				if ( $gap = get_prop( $this->attrs, [ 'style', 'spacing', 'blockGap' ] ) ) {
					if ( is_array( $gap ) ) {
						$gap	= get_prop( $gap, [ 'top' ] );
					}

					$gap = $gap ?: 'var( --wp--style--block-gap )';

					$this->output[] = wp_parse_args( [
						'element'	=> implode( '>', [ $this->element, '*' ] ),
						'property'	=> 'margin-block-start',
						'value'		=> 0,
					], $output );
					$this->output[] = wp_parse_args( [
						'element'	=> implode( '>', [ $this->element, '*' ] ),
						'property'	=> 'margin-block-end',
						'value'		=> 0,
					], $output );
					$this->output[] = wp_parse_args( [
						'element'	=> implode( '>', [ $this->element, '*+*' ] ),
						'property'	=> 'margin-block-start',
						'value'		=> $gap,
					], $output );
					$this->output[] = wp_parse_args( [
						'element'	=> implode( '>', [ $this->element, '*+*' ] ),
						'property'	=> 'margin-block-end',
						'value'		=> 0,
					], $output );
				}
			} elseif( $type === 'flex' ) {
				$orientation	= get_prop( $layout, 'orientation', 'horizontal' );
				$justification 	= get_prop( $layout, 'justifyContent' );

				$justify_options 	= [
					'left'   => 'flex-start',
					'right'  => 'flex-end',
					'center' => 'center',
				];

				if ( 'horizontal' === $orientation ) {
					$justify_options += [ 'space-between' => 'space-between' ];
				}
				
				$wrap_options 	= [ 'wrap', 'nowrap' ];
				$flex_wrap 		= get_prop( $layout, 'flexWrap', 'wrap' );
				$flex_wrap 		= in_array( $flex_wrap, $wrap_options ) ? $flex_wrap : 'wrap';

				$this->output[] = wp_parse_args( [
					'property' 	=> 'display',
					'value'	  	=> 'flex'
				], $output );

				$this->output[] = wp_parse_args( [
					'property' 	=> 'flex-wrap',
					'value'	  	=> $flex_wrap
				], $output );
				
				if ( 'horizontal' === $orientation ) {
					$this->output[] = wp_parse_args( [
						'property' 	=> 'align-items',
						'value'	  	=> 'center'
					], $output );

					$direction = 'justify-content';
				} else {
					$this->output[] = wp_parse_args( [
						'property' 	=> 'flex-direction',
						'value'	  	=> 'column'
					], $output );

					$direction = 'align-items';
				}
				
				if ( ! empty( $justification ) && array_key_exists( $justification, $justify_options ) ) {
					$this->output[] = wp_parse_args( [
						'property' 	=> $direction,
						'value'	  	=> $justify_options[ $justification ]
					], $output );
				}

				// BlockGap handled bellow in spacing

				$this->output[] = wp_parse_args( [
					'element'	=> join( '>', [ $this->element, '*' ] ),
					'property' 	=> 'margin',
					'value'	  	=> '0'
				], $output );
			}
		}
		
		// Inline Style
		if( $css_style = get_prop( $this->attrs, 'style' ) ) {
			// Typography
			if ( $typography = get_prop( $css_style, 'typography' ) ) {
				if ( $value = get_prop( $typography, 'fontFamily' ) ) {
					if ( strpos( $value, 'var:preset|font-family' ) !== false ) {
						// Get the name from the string and add proper styles.
						$name 	= substr( $value, strrpos( $value, '|' ) + 1 );
						$this->output[] = wp_parse_args( [
							'property' 	=> 'font-family',
							'value'	  	=> sprintf( 'var(--wp--preset--font-family--%s)', _wp_to_kebab_case( $name ) )
						], $output );
					}
				}

				if ( $value = get_prop( $typography, 'fontSize' ) ) {
					$this->output[] = wp_parse_args( [
						'property' 	=> 'font-size',
						'value'	  	=> $value,
					], $output );
				}
	
				if ( $value = get_prop( $typography, 'fontWeight' ) ) {
					$this->output[] = wp_parse_args( [
						'property' 	=> 'font-weight',
						'value'	  	=> $value
					], $output );
				}

				if ( $value = get_prop( $typography, 'fontStyle' ) ) {
					$this->output[] = wp_parse_args( [
						'property' 	=> 'font-style',
						'value'	  	=> $value
					], $output );
				}
				
				if ( $value = get_prop( $typography, 'lineHeight' ) ) {
					$this->output[] = wp_parse_args( [
						'property' 	=> 'line-height',
						'value'	  	=> $value,
					], $output );
				}
	
				if ( $value = get_prop( $typography, 'textTransform' ) ) {
					$this->output[] = wp_parse_args( [
						'property' 	=> 'text-transform',
						'value'	  	=> $value
					], $output );
				}
	
				if ( $value = get_prop( $typography, 'textDecoration' ) ) {
					$this->output[] = wp_parse_args( [
						'property' 	=> 'text-decoration',
						'value'	  	=> $value
					], $output );
				}
				
				if ( $value = get_prop( $typography, 'letterSpacing' ) ) {
					$this->output[] = wp_parse_args( [
						'property' 	=> 'letter-spacing',
						'value'	  	=> $value
					], $output );
				}
			}

			// Colors
			if ( $color = get_prop( $css_style, 'color' ) ) {
				// Text
				if ( $value = get_prop( $color, 'text' ) ) {
					$this->output[] = wp_parse_args( [
						'property' 	=> 'color',
						'value'	  	=> $value
					], $output );
				}

				// Background
				if ( $value = get_prop( $color, 'background' ) ) {
					$this->output[] = wp_parse_args( [
						'property' 	=> 'background-color',
						'value'	  	=> $value
					], $output );
				}

				// Gradient
				if ( $value = get_prop( $color, 'gradient' ) ) {
					$this->output[] = wp_parse_args( [
						'property' 	=> 'background-image',
						'value'	  	=> $value
					], $output );
				}
				
				// Duotone - temporary disable until I combine them into our styles
				if ( $value = get_prop( $color, 'duotone' ) ) {
					$block_type = \WP_Block_Type_Registry::get_instance()->get_registered( $this->name );

					$duotone_support = false;
					if ( $block_type && property_exists( $block_type, 'supports' ) ) {
						$duotone_support = get_prop( $block_type->supports, [ 'color', '__experimentalDuotone' ], false );
					}

					if( $duotone_support ) {
						$this->output[] = wp_parse_args( [
							'element'	=> implode( ', ', array_map( function ( $selector ) {
								return implode( ' ', [ $this->element, trim( $selector ) ] );
							}, explode( ',', $duotone_support ) ) ),
							'property' 	=> 'filter',
							'value'	  	=> sprintf( 'url(#wp-duotone-%s)', ltrim( $this->element, '.' ) )
						], $output );
					}
				}
			}

			// Spacing
			if( $spacing = get_prop( $css_style, 'spacing' ) ) {
				// Padding
				if ( $padding = get_prop( $spacing, 'padding', [] ) ) {
					if( ! empty( $padding ) ) {
						foreach( $padding as $dir => $val ) {
							$this->output[] = wp_parse_args( [
								'property' 	=> 'padding-' . $dir,
								'value'	  	=> $val
							], $output );
						}
					}
				}

				// Margin
				if ( $margin = get_prop( $spacing, 'margin', [] ) ) {
					if( ! empty( $margin ) ) {
						foreach( $margin as $dir => $val ) {
							$this->output[] = wp_parse_args( [
								'property' 	=> 'margin-' . $dir,
								'value'	  	=> $val
							], $output );
						}
					}
				}

				// Block Gap - only if not inherit
				if ( $gap = get_prop( $spacing, 'blockGap' ) ) {
					if( is_array( $gap ) ) {
						$gap_y	= get_prop( $gap, [ 'top' ], 'var(--wp--custom--gutter, 1rem)' );
						$gap_x	= get_prop( $gap, [ 'left' ], 'var(--wp--custom--gutter, 1rem)' );
						$gap 	= $gap_y === $gap_x ? $gap_y : $gap_y . ' ' . $gap_x;
					}
					
					$this->output[] = wp_parse_args( [
						'property' 	=> 'gap',
						'value'	  	=> get_prop( $this->attrs, [ 'layout', 'inherit' ] ) ? null : $gap
					], $output );
				}
			}

			// Border
			if( $border = get_prop( $css_style, 'border' ) ) {
				if ( $value = get_prop( $border, 'width' ) ) {
					$this->output[] = wp_parse_args( [
						'property' 	=> 'border-width',
						'value'	  	=> $value
					], $output );
				}

				if ( $value = get_prop( $border, 'style' ) ) {
					$this->output[] = wp_parse_args( [
						'property' 	=> 'border-style',
						'value'	  	=> $value
					], $output );
				}

				if ( $value = get_prop( $border, 'radius' ) ) {
					if ( is_array( $value ) ) {
						// We have individual border radius corner values.
						foreach ( $value as $key => $radius ) {
							// Convert CamelCase corner name to kebab-case.
							$corner   = strtolower( preg_replace( '/(?<!^)[A-Z]/', '-$0', $key ) );
							$this->output[] = wp_parse_args( [
								'property' 	=> sprintf( 'border-%s-radius', $corner ),
								'value'	  	=> $radius,
							], $output );
						}
					} else {
						$this->output[] = wp_parse_args( [
							'property' 	=> 'border-radius',
							'value'	  	=> $value,
							'units'		=> is_numeric( $value ) ? 'px' : null
						], $output );
					}
				}
				
				if ( $value = get_prop( $border, 'color' ) ) {
					$this->output[] = wp_parse_args( [
						'property' 	=> 'border-color',
						'value'	  	=> $value
					], $output );
				}
			}

			// Elements
			if( $elements = get_prop( $css_style, 'elements' ) ) {
				if ( $link = get_prop( $elements, 'link' ) ) {
					if ( $color = get_prop( $link, 'color' ) ) {
						if ( $value = get_prop( $color, 'text', false ) ) {
							if ( strpos( $value, 'var:preset|color' ) !== false ) {
								// Get the name from the string and add proper styles.
								$name 	= substr( $value, strrpos( $value, '|' ) + 1 );
								$this->output[] = wp_parse_args( [
									'element'	=> implode( ' ', [ $this->element, 'a' ] ),
									'property' 	=> 'color',
									'value'	  	=> sprintf( 'var(--wp--preset--color--%s)', $name )
								], $output );
							} else {
								$this->output[] = wp_parse_args( [
									'element'	=> implode( ' ', [ $this->element, 'a' ] ),
									'property' 	=> 'color',
									'value'	  	=> $value
								], $output );
							}
						}
					}
				}
			}
		}
	}
	
	/**
	 * Parses custom CSS.
	 *
	 * @return 	void
	 */
	protected function parse_custom() {
		if ( $css_custom = get_prop( $this->attrs, 'customCSS', false ) ) {
			$custom_style 	= wp_strip_all_tags( $css_custom );
			$custom_style 	= str_replace( 'selector', $this->get_element(), $custom_style );
			$custom_style 	= wecodeart( 'styles' )::string_to_array_query( $custom_style );
			// Array replace existing CSS rules - custom overwrites everything
			$this->styles 	= array_replace_recursive( $this->styles, $custom_style );
		}
	}

	/**
	 * Get duotone.
	 *
	 * @return 	array
	 */
	public function get_duotone() {
		$return 	= false;
		$duotone 	= get_prop( $this->attrs, [ 'style', 'color', 'duotone' ], false );

		if( $duotone ) {
			$return = [
				'r' => [],
				'g' => [],
				'b' => [],
				'a' => [],
			];

			foreach ( $duotone as $color ) {
				$color = wp_tinycolor_string_to_rgb( $color );

				$return['r'][] = $color['r'] / 255;
				$return['g'][] = $color['g'] / 255;
				$return['b'][] = $color['b'] / 255;
				$return['a'][] = isset( $color['a'] ) ? $color['a'] : 1;
			}
		}

		return $return;
	}

	/**
	 * Get classnames.
	 *
	 * @return 	array
	 */
	public function get_classes() {
		return array_unique( array_filter( explode( ' ', get_prop( $this->attrs, 'className', '' ) ) ) );
	}
	
	/**
	 * Get uniqueID.
	 *
	 * @return 	array
	 */
	public function get_element() {
		return $this->element;
	}
}