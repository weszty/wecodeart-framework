<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg Patterns
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		4.2.0
 * @version		4.2.0
 */

namespace WeCodeArt\Gutenberg\Modules;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Integration;

/**
 * Handles Gutenberg Theme Patterns Functionality.
 */
class Patterns implements Integration {

	use Singleton;

	const POST_TYPE         = 'wca_pattern';
	const TYPE_TAXONOMY     = 'wca_pattern_type';
	const CATEGORY_TAXONOMY = 'wca_pattern_category';

	/**
	 * Get Conditionals
	 *
	 * @return void
	 */
	public static function get_conditionals() {
		wecodeart( 'conditionals' )->set( [
			'with_gutenberg_patterns' => Patterns\Condition::class,
		] );

		return [ 'with_gutenberg_patterns' ];
	}

	/**
	 * Register Hooks
	 *
	 * @since 	4.2.0
	 *
	 * @return 	void
	 */
	public function register_hooks() {
		$this->register_type();
		$this->register_taxonomy();
		$this->load_block_patterns();
		$this->load_categories();
		add_action( 'rest_insert_' . self::POST_TYPE, [ $this, 'rest_insert_wca_pattern' ], 10, 2 );
	}

	/**
	 * Register the Custom Post Type.
	 */
	public function register_type() { 
		register_post_type( self::POST_TYPE, [
			'label'             => __( 'Block Patterns', 'wecodeart' ),
			'labels'			=> [
				'name'					=> _x( 'Block Patterns', 'Post Type General Name', 'wecodeart' ),
				'singular_name'			=> _x( 'Block Pattern', 'Post Type Singular Name', 'wecodeart' ),
				'menu_name'				=> __( 'Blocks - Patterns', 'wecodeart' ),
				'add_new_item'          => __( 'Add New Pattern', 'wecodeart' ),
				'add_new'               => __( 'Add New', 'wecodeart' ),
				'new_item'              => __( 'New Pattern', 'wecodeart' ),
				'edit_item'             => __( 'Edit Pattern', 'wecodeart' ),
				'update_item'           => __( 'Update Pattern', 'wecodeart' ),
				'view_item'             => __( 'View Pattern', 'wecodeart' ),
				'view_items'            => __( 'View Patterns', 'wecodeart' ),
				'search_items'          => __( 'Search Pattern', 'wecodeart' ),
				'not_found'             => __( 'Not found', 'wecodeart' ),
				'not_found_in_trash'    => __( 'Not found in Trash', 'wecodeart' ),
			],
			'description'       => __( 'Description', 'wecodeart' ),
			'supports'          => [ 'title', 'editor', 'excerpt' ],
			'taxonomies'        => [ self::TYPE_TAXONOMY, self::CATEGORY_TAXONOMY ],
			'show_ui'           => true,
			'rewrite'           => false,
			'menu_position'		=> 15,
			'show_in_rest'      => true,
			'show_in_menu'      => 'themes.php',
			'show_in_admin_bar' => false,
		] );
	}

	/**
	 * Registers the "type" custom taxonomy.
	 *
	 * @uses self::TYPE_TAXONOMY 		= Can be block pattern or full page layout
	 * @uses self::CATEGORY_TAXONOMY 	= Pattern category
	 */
	public function register_taxonomy() {
		register_taxonomy( self::TYPE_TAXONOMY, [ self::POST_TYPE  ], [
			'label'        => __( 'Pattern Type', 'wecodeart' ),
			'hierarchical' => true,
			'rewrite'      => false,
			'show_in_rest' => true,
		] );

		register_taxonomy( self::CATEGORY_TAXONOMY, [ self::POST_TYPE ], [
			'label'        => __( 'Pattern Category', 'wecodeart' ),
			'hierarchical' => true,
			'rewrite'      => false,
			'show_in_rest' => true,
		] );

		if( ! term_exists( 'pattern', self::TYPE_TAXONOMY ) ) {
			wp_insert_term( 'Pattern', self::TYPE_TAXONOMY, [
				'description'	=> __( 'Pattern', 'wecodeart' ),
				'slug' 			=> 'pattern'
			] );
		}
		
		if( ! term_exists( 'layout', self::TYPE_TAXONOMY ) ) {
			wp_insert_term( 'Layout', self::TYPE_TAXONOMY, [
				'description'	=> __( 'Layout', 'wecodeart' ),
				'slug' 			=> 'layout'
			] );
		}
	}

	/**
	 * Set custom taxonomies relationships with the REST API.
	 *
	 * @param 	WP_Post         $post     Inserted or updated post object.
	 * @param 	WP_REST_Request $request  Request object.
	 *
	 * @return 	void
	 */
	public function rest_insert_wca_pattern( $post, $request ) {
		$params = $request->get_json_params();

		if ( array_key_exists( 'terms', $params ) ) {
			foreach ( $params['terms'] as $taxonomy => $terms ) {
				wp_set_object_terms( $post->ID, $terms, $taxonomy );
			}
		}
	}

	/**
	 * Register custom post type posts (with the 'pattern' type) as block patterns.
	 */
	public function load_block_patterns() {
		$block_patterns_query = new \WP_Query( [
			'post_type'              => self::POST_TYPE,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => [
				[
					'taxonomy' => self::TYPE_TAXONOMY,
					'field'    => 'slug',
					'terms'    => 'pattern',
				]
			],
		] );
	
		wp_reset_postdata();

		if ( $block_patterns_query->have_posts() ) {
			foreach( $block_patterns_query->posts as $block_pattern ) {
				$categories = get_the_terms( $block_pattern->ID, self::CATEGORY_TAXONOMY );
	
				register_block_pattern( self::POST_TYPE . '/' . $block_pattern->post_name, [
					'title'       => $block_pattern->post_title,
					'content'     => $block_pattern->post_content,
					'categories'  => empty( $categories ) ? [] : wp_list_pluck( $categories, 'slug' ),
					'description' => $block_pattern->post_excerpt,
				] );
			}
		}
	}

	/**
	 * Merge our "category" taxonomy with the categories already defined elsewhere.
	 *
	 * @return 	void
	 */
	public function load_categories() {
		$pattern_categories           	= get_terms( self::CATEGORY_TAXONOMY );
		$pattern_categories_flattened 	= wp_list_pluck( $pattern_categories, 'name', 'slug' );

		$registry = \WP_Block_Pattern_Categories_Registry::get_instance();

		foreach( $pattern_categories_flattened as $slug => $label ) {
			// Move on if is registered
			if( $registry->is_registered( $slug ) ) continue;
			// Register new pattern category
			register_block_pattern_category( $slug, [
				'label' => $label
			] );
		}
	}

	/**
	 * Merge our custom post type posts (with the 'layout' type) with the layouts already defined elsewhere.
	 *
	 * @param 	array $layouts The existing layouts.
	 *
	 * @return 	array
	 */
	public function load_layouts( $layouts ) {
		$block_patterns_query = new \WP_Query( [
			'post_type'              => self::POST_TYPE,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => [
				[
					'taxonomy' => self::TYPE_TAXONOMY,
					'field'    => 'slug',
					'terms'    => 'layout',
				]
			],
		] );

		wp_reset_postdata();

		foreach ( $block_patterns_query->posts as $block_pattern ) {
			$categories = get_the_terms( $block_pattern->ID, self::CATEGORY_TAXONOMY );

			$layouts[] = [
				'category'    => wp_list_pluck( $categories, 'slug' )[0],
				'postContent' => $block_pattern->post_content,
			];
		}

		return $layouts;
	}
}
