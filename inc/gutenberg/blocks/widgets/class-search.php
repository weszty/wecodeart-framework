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
 * @version		5.4.8
 */

namespace WeCodeArt\Gutenberg\Blocks\Widgets;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Search block.
 */
class Search extends Dynamic {

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
	protected $block_name = 'search';

	/**
	 * Shortcircuit Register
	 */
	public function register() {
		add_filter( 'block_type_metadata_settings', [ $this, 'filter_render' ], 10, 2 );
	}

	/**
	 * Filter Render
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
	 * Dynamically renders the `core/search` block.
	 *
	 * @param 	array 	$attributes The block attributes.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( $attributes = [], $content = '', $block = null ) {
		static $instance_id = 0;

		$attributes = wp_parse_args( $attributes, [
			'label'      => __( 'Search', 'wecodeart' ),
			'buttonText' => __( 'Search', 'wecodeart' ),
		] );

		$input_id 	= 'wp-block-search__input-' . ++$instance_id;
		$form_class = [ 'wp-block-search__form', 'needs-validation' ];

		// Width utility.
		if( get_prop( $attributes, 'widthUnit', 'px' ) === '%' ) {
			if( in_array( get_prop( $attributes, 'width' ), [ 25, 50, 75, 100 ] ) ) {
				$utility = 'w-' . get_prop( $attributes, 'width' );
				
				wecodeart( 'styles' )->Utilities->load( $utility );
				
				$form_class[] = $utility;
			}
		}

		// Queue block for assets.
		$storage = Blocks::get_instance();
		$storage::load( [ 'core/buttons', 'core/button' ] );

		$content = wecodeart( 'markup' )::wrap( 'wp-block-search', [
			[
				'tag' 	=> 'div',
				'attrs' => [
					'class' => $this->get_classname( $attributes )
				]
			],
			[
				'tag' 	=> 'form',
				'attrs' => [
					'class'		=> join( ' ', $form_class ),
					'role'		=> 'search',
					'method' 	=> 'get',
					'action' 	=> home_url( '/' ),
					'novalidate'=> 'novalidate',
				]
			],
		], function() use( $attributes, $input_id ) {
			$wrapper   = [ 'wp-block-search__fields' ];
			$wrapper[] = get_prop( $attributes, 'buttonPosition' ) === 'button-inside' ? 'input-group' : '';
			// Add Label
			if ( get_prop( $attributes, 'showLabel' ) !== false && ! empty( $label = get_prop( $attributes, 'label' ) ) ) {
			?>
			<label class="form-label" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $label ); ?></label>
			<?php
			}
			?>
			<div class="<?php echo esc_attr( implode( ' ', array_filter( $wrapper ) ) ); ?>">
			<?php

			// Add search input
			wecodeart_input( 'search', [
				'attrs' => [
					'id' 			=> $input_id,
					'name'			=> 's',
					'class' 		=> $this->get_classname( $attributes, 'field' ),
					'value'			=> get_search_query(),
					'placeholder' 	=> get_prop( $attributes, 'placeholder' ),
					'required' 		=> true,
				]
			] );

			// Maybe add submit button
			if ( get_prop( $attributes, 'buttonPosition', 'button-outside' ) !== 'no-button' ) {
				$icon  = get_prop( $attributes, 'buttonUseIcon' );
				$label = $icon ? wecodeart( 'markup' )->SVG::compile( 'search' ) : get_prop( $attributes, 'buttonText' );

				wecodeart_input( 'button', [
					'label' => $label,
					'attrs' => [
						'type'	=> 'submit',
						'class' => $this->get_classname( $attributes, 'button' ),
					]
				] );
			}
			?>
			</div>
			<?php
		}, [], false );

		return $content;
	}

	/**
	 * Builds the correct top level classnames for the 'core/search' block.
	 *
	 * @param 	array 	$attributes The block attributes.
	 *
	 * @return 	string 	The classnames used in the block.
	 */
	public function get_classname( $attributes, $wrapper = 'wrapper' ) {
		$classnames = [];

		if( $wrapper === 'wrapper' ) {
			$classnames[] = 'wp-block-search';
			$position = get_prop( $attributes, 'buttonPosition', 'button-outside' );
			$classnames[] = 'wp-block-search--' . $position;
	
			if ( get_prop( $attributes, 'buttonUseIcon', false ) ) {
				if ( 'no-button' !== $position ) {
					$classnames[] = 'wp-block-search--with-icon';
				}
			}
		}

		if( $wrapper === 'button' ) {
			$classnames = [ 'wp-block-button__link' ];
			
			if( $value = get_prop( $attributes, [ 'gradient' ] ) ) {
				$classnames[] = 'has-' . $value . '-gradient-background';
			}

			if( $value = get_prop( $attributes, [ 'backgroundColor' ] ) ) {
				$classnames[] = 'has-' . $value . '-background-color';
			}

			if( $value = get_prop( $attributes, [ 'textColor' ] ) ) {
				$classnames[] = 'has-text-color';
				$classnames[] = 'has-' . $value . '-color';
			}
		}
		
		if( $wrapper === 'field' ) {
			$classnames = [ 'wp-block-search__input', 'form-control' ];
		}
		
		if( $value = get_prop( $attributes, [ 'borderColor' ] ) ) {
			$classnames[] = 'has-border-color';
			$classnames[] = 'has-' . $value . '-border-color';
		}

		return implode( ' ', $classnames );
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public static function styles() {
		return "
		.wp-block-search {
			margin-bottom: 1rem;
		}
		.wp-block-search--button-outside .wp-block-button__link {
			margin-left: 1rem;
		}
		.wp-block-search__fields {
			display: flex;
		}
		.navbar .wp-block-search {
			margin-bottom: 0;
		}
		";
	}
}
