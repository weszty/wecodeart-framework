<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		4.0.5
 * @version		5.0.6
 */

namespace WeCodeArt\Gutenberg\Modules;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Integration;
use WeCodeArt\Conditional\Traits\No_Conditionals;
use WeCodeArt\Core\Scripts;

/**
 * Handles Gutenberg Theme Custom Classes Functionality.
 */
class Classes {

	use Singleton;
	use No_Conditionals;
	use Scripts\Base;

	/**
	 * Register Hooks - into styles processor action if enabled
	 *
	 * @since 	5.0.0
	 *
	 * @return 	void
	 */
	public function register_hooks() {
		// Admin
		add_action( 'enqueue_block_editor_assets', 			[ $this, 'block_editor_assets' ] );
		add_filter( 'wecodeart/filter/gutenberg/settings', 	[ $this, 'set_suggest_classes' ], 10, 2 );

		// Child Classes
		Classes\Suggestions::get_instance();
	}

	/**
	 * Editor only.
	 *
	 * @return  void
	 */
	public function block_editor_assets() {
		wp_enqueue_script( $this->make_handle(), $this->get_asset( 'js', 'gutenberg/ext-classes' ), [
			'wecodeart-gutenberg'
		], wecodeart( 'version' ) );
	}

	/**
	 * Add new block editor settings for custom classes.
	 *
	 * @param array  $settings 	The editor settings.
	 * @param object $post 		The post being edited.
	 *
	 * @return array Returns updated editors classes suggestions.
	 */
	public function set_suggest_classes( $settings, $post ) {
		if ( ! isset( $settings[ 'customClasses' ] ) ) {
			$classes = apply_filters( 'wecodeart/filter/gutenberg/settings/classes', [], $post );
			$settings['customClasses'] = array_map( 'sanitize_html_class', $classes );
		}

		return $settings;
	}
}
