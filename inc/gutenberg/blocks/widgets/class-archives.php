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
 * @since		5.5.1
 * @version		5.7.2
 */

namespace WeCodeArt\Gutenberg\Blocks\Widgets;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Archives block.
 */
class Archives extends Dynamic {

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
	protected $block_name = 'archives';

	/**
	 * Block args.
	 *
	 * @return 	array
	 */
	public function block_type_args(): array {
		return [
			'render_callback' => [ $this, 'render' ]
		];
	}

	/**
	 * Dynamically renders the `core/search` block.
	 *
	 * @param 	array 	$attributes	The attributes.
	 * @param 	string 	$content 	The block markup.
	 * @param 	string 	$block 		The block data.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( array $attributes = [], string $content = '', $block = null ): string {
		$args = apply_filters( 'wecodeart/filter/gutenberg/archives', [
			'format' 			=> 'custom',
			'type'				=> get_prop( $attributes, [ 'type' ], 'monthly' ),
			'before' 			=> '',
			'after' 			=> '|',
			'echo' 				=> 0,
			'show_post_count' 	=> get_prop( $attributes, [ 'showPostCounts' ] ),
		] );

		$archives 	= wp_get_archives( $args );
		$parsed		= self::parse( $archives );

		if( get_prop( $attributes, [ 'displayAsDropdown' ] ) ) {
			switch ( $args['type'] ) {
				case 'yearly':
					$label = __( 'Select Year', 'wecodeart' );
					break;
				case 'monthly':
					$label = __( 'Select Month', 'wecodeart' );
					break;
				case 'daily':
					$label = __( 'Select Day', 'wecodeart' );
					break;
				case 'weekly':
					$label = __( 'Select Week', 'wecodeart' );
					break;
				default:
					$label = __( 'Select Post', 'wecodeart' );
					break;
			}

			// Render as select input
			$content = wecodeart_input( 'select', [
				'label' 	=> __( 'Archives', 'wecodeart' ),
				'choices' 	=> $parsed,
				'attrs' 	=> [
					'id' 			=> wp_unique_id( 'wp-block-archives-' ),
					'name'			=> 'archive-dropdown',
					'onchange'		=> 'document.location.href=this.options[this.selectedIndex].value;',
					'placeholder' 	=> $label,
				]
			], false );
		} else {
			if ( empty( $archives ) ) {
				$content = __( 'No archives to show.', 'wecodeart' );
			} else {
				// Process parsed.
				array_walk( $parsed, function( &$item, $url ) {
					$item = [
						'blockName'	=> 'core/navigation-link',
						'attrs'		=> [
							'kind' 	=> 'custom',
							'label' => $item,
							'url'	=> $url,
							'isTopLevelLink' => true,
						]
					];
				} );

				// Render as navigation.
				$content .= ( new \WP_Block( [
					'blockName' 	=> 'core/navigation',
					'attrs'			=> apply_filters( 'wecodeart/filter/gutenberg/archives/nav', [
						'textColor'			=> 'primary',
						'overlayMenu'		=> 'never',
						'showSubmenuIcon' 	=> false,
						'hasIcon'			=> false,
						'style'	=> [
							'spacing'			=> [
								'blockGap'	=> '.5rem'
							]
						]
					] ),
					'innerBlocks' 	=> $parsed,
				] ) )->render();
			}
		}

		return wecodeart( 'markup' )::wrap( 'wp-block-archives', [
			[
				'tag' 	=> 'div',
				'attrs' => $this->get_block_wrapper_attributes()
			]
		], $content, [], false );
	}

	/**
	 * Parse Archive
	 *
	 * @param	string  wp_get_archives() content
	 *
	 * @return 	array 	The options as array.
	 */
	public static function parse( $content ) {
		$data = [];
		$array = array_filter( explode( '|', trim( $content ) ) );
	
		foreach( $array as $item ) {
			preg_match( '/href=["\']?([^"\'>]+)/', $item, $item_vars );
		
			if ( ! empty( $item_vars ) ) {
				$data[$item_vars[1]] = wp_strip_all_tags( $item );
			}
		}

		return $data;
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		return "
		.wp-block-archives {
			margin-bottom: 1rem;
		}
		";
	}
}
