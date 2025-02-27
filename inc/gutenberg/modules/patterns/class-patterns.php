<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg Patterns
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since		5.0.0
 * @version		6.0.0
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

	/**
	 * Folder.
	 *
	 * @var string
	 */
	const FOLDER = 'patterns';

	/**
	 * The indexed patterns.
	 *
	 * @var 	array
	 */
	private $patterns	= [];

	/**
	 * Get Conditionals
	 *
	 * @return void
	 */
	public static function get_conditionals() {
		wecodeart( 'conditionals' )->set( [
			'with_block_patterns' => Patterns\Condition::class,
		] );

		return [ 'with_block_patterns' ];
	}

	/**
	 * Register Hooks
	 *
	 * @since 	5.0.0
	 *
	 * @return 	void
	 */
	public function register_hooks() {
		// Register.
		$this->register_categories();
		$this->register_patterns();
	}

	/**
	 * Register block patterns categories.
	 *
	 * @return 	void
	 */
	public function register_categories() {
		$data 		= [];
		$themes     = [];
		$stylesheet = get_stylesheet();
		$template   = get_template();

		if ( $stylesheet !== $template ) {
			$themes[] = wp_get_theme( $stylesheet );
		}

		$themes[] = wp_get_theme( $template );
		
		foreach ( $themes as $theme ) {
			$dir	= $theme->get_stylesheet_directory();
			$file	= wp_normalize_path( $dir . DIRECTORY_SEPARATOR . self::FOLDER . DIRECTORY_SEPARATOR . '_categories.json' );
			if ( file_exists( $file ) ) {
				$data = array_merge( $data, json_decode( file_get_contents( $file ), true ) );
			}
		}

		if( empty( $data ) ) return;

		( new Patterns\Categories( $data ) )->register();
	}

	/**
	 * Register block patterns.
	 *
	 * @return 	void
	 */
	public function register_patterns() {
		$themes     = [];
		$stylesheet = get_stylesheet();
		$template   = get_template();

		if ( $stylesheet !== $template ) {
			$themes[] = wp_get_theme( $stylesheet );
		}

		$themes[] = wp_get_theme( $template );

		foreach ( $themes as $theme ) {
			$dir		= $theme->get_stylesheet_directory();
			$dirpath 	= wp_normalize_path( $dir . DIRECTORY_SEPARATOR . self::FOLDER . DIRECTORY_SEPARATOR );

			// Skip if not readable.
			if ( ! is_dir( $dirpath ) || ! is_readable( $dirpath ) ) {
				continue;
			}
			
			// If exists, continue.
			if ( file_exists( $dirpath ) ) {
				// Merge index data if exists.
				$index	= $dirpath . '_index.json';
				if ( file_exists( $index ) ) {
					$this->patterns = array_merge( $this->patterns, json_decode( file_get_contents( $index ), true ) );
				}
				// Loop HTML files and register.
				$files = glob( $dirpath . '*.html' );
				if ( $files ) {
					foreach ( $files as $file ) {
						$this->register_from_file( wp_parse_args( [
							'theme' => $theme->get( 'TextDomain' ),
							'path' 	=> $file,
						], 	pathinfo( $file ) ) );
					}
				}
			}
		}
	}

	/**
	 * Build a unified template object based on a theme file.
	 *
	 * @param 	array 	$file 	Theme file.
	 *
	 * @return 	Patterns\Pattern Template.
	 */
	public function register_from_file( $file ) {
		$args = [
			'title' 	=> ucwords( implode( ' ', explode( '-', $file['filename'] ) ) ),
			'content' 	=> file_get_contents( $file['path'] ),
			'slug'		=> $file['filename'],
			'theme'		=> $file['theme'],
		];

		$has_json = current( wp_list_filter( $this->patterns, [ 'slug' => $args['slug'] ] ) );
		
		if( $has_json ) {
			$args = wp_parse_args( $has_json, $args );
		}

		return ( new Patterns\Pattern( $args ) )->register();
	}
}
