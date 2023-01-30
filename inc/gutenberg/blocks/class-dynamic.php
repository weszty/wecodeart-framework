<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg\Blocks
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since		5.0.0
 * @version		5.7.2
 */

namespace WeCodeArt\Gutenberg\Blocks;

defined( 'ABSPATH' ) || exit();

use function WeCodeArt\Functions\get_prop;
use function WeCodeArt\Functions\dom;

/**
 * Gutenberg Abstract Dynamic block.
 */
abstract class Dynamic {

	/**
	 * Block namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wca';

	/**
	 * Block namespace.
	 *
	 * @var string
	 */
	protected $block_name = '';

	/**
	 * Initialize this block type.
	 *
	 * - Hook into WP lifecycle.
	 * - Register the block with WordPress.
	 */
	public function hooks() {
		if ( empty( $this->block_name ) ) {
			_doing_it_wrong( __METHOD__, esc_html__( 'Block name is required.', 'wecodeart' ), '5.6.3' );
			return false;
		}
		
		$this->register();
		add_action( 'init', [ $this, 'enqueue_styles' ] ); // wp_enqueue_block_styles goes to init hook instead of the usual one.
	}

	/**
	 * Registers the block type with WordPress.
	 */
	protected function register() {
		// Like this to pass theme check - however, in the theme, this acts as an abstract method
		// and its overwritten to add_filter for the blocks that we change the markup
		call_user_func_array( 'register_block_type', [ $this->get_block_type(), [
			'render_callback' => [ $this, 'get_render_callback' ],
			'attributes'      => [ $this, 'get_attributes' ],
			'supports'        => [],
		] ] );
	}

	/**
	 * Include and render a dynamic block.
	 *
	 * @return	string Block Markup.
	 */
	protected function render() {}

	/**
	 * Load and manipulate HTML with DOMDocument.
	 *
	 * @param 	string $content    Block content. 		Default empty string.
	 * 
	 * @return 	object $doc.
	 */
	protected function markup( $content = '' ) {
		$doc = dom( $content );

		return $doc;
	}

	/**
	 * Load and manipulate HTML with DOMDocument.
	 *
	 * @param 	string $content    Block content. 		Default empty string.
	 * 
	 * @return 	object $doc.
	 */
	protected function load_html( $content = '' ) {
		return $this->markup( $doc );
	}
	
	/**
	 * Save HTML with DOMDocument.
	 *
	 * @param 	string $content    Block content. 		Default empty string.
	 * 
	 * @return 	string $content.
	 */
	protected function save_html( $content = '' ) {
		return $content;
	}

	/**
	 * Get block wrapper attributes.
	 *
	 * @return array
	 */
	protected function get_block_wrapper_attributes( $extra_attributes = [] ) {
		$new_attributes = \WP_Block_Supports::get_instance()->apply_block_supports();
	
		if ( empty( $new_attributes ) && empty( $extra_attributes ) ) {
			return [];
		}
	
		// This is hardcoded on purpose.
		// We only support a fixed list of attributes.
		$attributes_to_merge = [ 'class', 'style' ];
		$attributes          = [];
		foreach ( $attributes_to_merge as $attribute_name ) {
			if ( empty( $new_attributes[ $attribute_name ] ) && empty( $extra_attributes[ $attribute_name ] ) ) {
				continue;
			}
	
			if ( empty( $new_attributes[ $attribute_name ] ) ) {
				$attributes[ $attribute_name ] = $extra_attributes[ $attribute_name ];
				continue;
			}
	
			if ( empty( $extra_attributes[ $attribute_name ] ) ) {
				$attributes[ $attribute_name ] = $new_attributes[ $attribute_name ];
				continue;
			}
	
			$attributes[ $attribute_name ] = $extra_attributes[ $attribute_name ] . ' ' . $new_attributes[ $attribute_name ];
		}
	
		foreach ( $extra_attributes as $attribute_name => $value ) {
			if ( ! in_array( $attribute_name, $attributes_to_merge, true ) ) {
				$attributes[ $attribute_name ] = $value;
			}
		}
	
		if ( empty( $attributes ) ) {
			return [];
		}
	
		return $attributes;
	}

	/**
	 * Get the block type.
	 *
	 * @return string
	 */
	protected function get_block_type() {
		return $this->namespace . '/' . $this->block_name;
	}

	/**
	 * Get block render_callback
	 * 
	 * @return 	string.
	 */
	protected function get_render_callback() {
		return [ $this, 'render' ];
	}

	/**
	 * Get block attributes.
	 *
	 * @return array
	 */
	protected function get_attributes() {
		return [];
	}
	
	/**
	 * Get the schema for a value.
	 *
	 * @param  string 	$default  The default value.
	 *
	 * @return array 	Property definition.
	 */
	protected function get_schema( string $type, $default ) {
		$type 	= in_array( $type, [ 'string', 'number', 'boolean', 'list', 'align' ] ) ? $type : 'string';

		$schema = [
			'type'	=> $type,
		];

		switch( $type ) :
			case 'boolean':
				$schema = wp_parse_args( [
					'default' => $default ?: true
				], $schema );
			break;
			case 'string':
				$schema = wp_parse_args( [
					'default' => $default ?: ''
				], $schema );
			break;
			case 'align':
				$schema = wp_parse_args( [
					'type' => 'string',
					'enum' => [ 'left', 'center', 'right', 'wide', 'full' ],
				], $schema );
			break;
			case 'list':
				$schema = wp_parse_args( [
					'type'    => 'array',
					'items'   => [
						'type' => 'number',
					],
					'default' => $default ?: []
				], $schema );
			break;
			default;
		endswitch;

		return $schema;
	}

	/**
	 * Block styles.
	 *
	 * @return 	string Block CSS.
	 */
	public function enqueue_styles() {
		// These are cached styles, so we only generate minified version.
		$styles = wecodeart( 'styles' )::compress( $this->styles() );
		
		if( empty( $styles ) ) return;

		global $wp_styles;

		$filesystem = wecodeart( 'files' );
		$filesystem->set_folder( 'cache' );

		$block_handle 	= 'wp-block-' . $this->block_name;
		$block_css 		= 'block-' . $this->block_name . '.css';

		if( ! $filesystem->has_file( $block_css ) ) {
			$filesystem->create_file( $block_css, $styles );
		}

		$registered = is_object( $wp_styles ) ? get_prop( $wp_styles->registered, $block_handle ) : false;
		$deps		= $registered ? $registered->deps : [];

		// Deregister Core
		wp_deregister_style( $block_handle );

		// Register Custom
		wp_register_style( $block_handle, $filesystem->get_file_url( $block_css, true ), $deps, wecodeart( 'version' ) );
			
		// Enqueue Custom
		wp_enqueue_block_style( $this->get_block_type(), [
			'handle'	=> $block_handle,
			'src'		=> $filesystem->get_file_url( $block_css, true ),
			'path'		=> wp_normalize_path( $filesystem->get_file_url( $block_css ) ),
			'deps'		=> $deps,
			'ver'		=> wecodeart( 'version' )
		] );

		$filesystem->set_folder( '' );
	}

	/**
	 * Block styles.
	 *
	 * @return 	string Block CSS.
	 */
	public function styles() {
		return '';
	}
}
