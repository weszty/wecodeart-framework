<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg CSS Frontend
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since		5.0.0
 * @version		5.7.2
 */

namespace WeCodeArt\Gutenberg\Modules\Styles;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Gutenberg\Modules\Styles;
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
	protected 	$block_id 	= '';

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
		$this->block_id	= wp_unique_id( 'css-' );
		
		if( method_exists( $this, 'process_extra' ) ) {
			// Process extra attributes
			$this->process_extra();
		}

		// Process Styles
		$this->process_style();

		// Parse Styles
		$this->parse_output();
		$this->parse_custom();
	}

	/**
	 * Get uniqueID.
	 *
	 * @return 	array
	 */
	public function get_id(): string {
		return $this->block_id;
	}
	
	/**
	 * Get classnames.
	 *
	 * @return 	array
	 */
	public function get_classes(): array {
		return array_unique( array_filter( explode( ' ', get_prop( $this->attrs, 'className', '' ) ) ) );
	}

	/**
	 * Get duotone.
	 *
	 * @return 	mixed
	 */
	public function get_duotone(): mixed {
		$return 	= false;
		$duotone 	= get_prop( $this->attrs, [ 'style', 'color', 'duotone' ], false );

		if( $duotone ) {
			$return = wecodeart( 'styles' )::get_duotone( [ 'colors' => $duotone ] );
		}

		return $return;
	}

	/**
	 * Get selector.
	 *
	 * @param	string	$prefix		Append to selector
	 * @param	bool	$support	Import block supports selectors
	 *
	 * @return 	string
	 */
	public function get_selector( string $prefix = '', bool $support = true ): string {
		$excludes = [ 'core/heading', 'core/paragraph' ]; // Exclude this.
		$selector = '';
		
		if( $support === true && ! in_array( $this->name, $excludes ) ) {
			$block_type	= \WP_Block_Type_Registry::get_instance()->get_registered( $this->name );
			$selector 	= get_prop( $block_type->supports, [ '__experimentalSelector' ] );
		}

		$selector 	= join( '', array_filter( [ '.', $this->get_id(), '.', $this->get_id(), $selector, $prefix ] ) );

		return apply_filters( 'wecodeart/filter/gutenberg/styles/selector', $selector, $this->name );
	}
	
	/**
	 * Load styles.
	 *
	 * @return 	void
	 */
	public function add_declarations( array $declarations, $selector = false ): void {
		\WP_Style_Engine::store_css_rule( Styles::CONTEXT, $selector ?: $this->get_selector(), $declarations );
	}

	/**
	 * Load styles.
	 *
	 * @return 	void
	 */
	private function process_style(): void {
		$style_attr = get_prop( $this->attrs, [ 'style' ], [] );

		if( empty( $style_attr ) ) {
			return;
		}

		// Add Duotone to inline CSS instead of WP Default.
		if( $duotone = get_prop( $style_attr, [ 'color', 'duotone' ] ) ) {
			$this->output[] = [
				'element' 	=> $this->get_selector( ' :where(img,video)', '', false ),
				'property'	=> 'filter',
				'value'		=> sprintf( 'url(#wp-duotone-%s)', $this->get_id() )
			];
		}

		// Process block attributes.
		wp_style_engine_get_styles( $style_attr, [
			'selector' 	=> $this->get_selector(),
			'context'	=> Styles::CONTEXT
		] );
	}

	/**
	 * Parses custom CSS.
	 *
	 * @return 	void
	 */
	private function parse_custom(): void {
		if ( $css_custom = get_prop( $this->attrs, 'customStyle', get_prop( $this->attrs, 'customCSS' ) ) ) {
			$custom_style 	= wp_strip_all_tags( $css_custom );
			$custom_style 	= str_replace( 'selector', $this->get_selector( '', false ) , $custom_style );
			$custom_style 	= wecodeart( 'styles' )::string_to_array_query( $custom_style );

			// Array replace existing CSS rules - custom overwrites everything
			$this->styles 	= array_replace_recursive( $this->styles, $custom_style );
		}
	}
}