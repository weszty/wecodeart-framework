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

namespace WeCodeArt\Gutenberg\Blocks\Text;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Table block.
 */
class Table extends Dynamic {

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
	protected $block_name = 'table';

	/**
	 * Shortcircuit Register
	 */
	public function register() {
		add_filter( 'render_block_core/table', [ $this, 'render' ], 10, 2 );
	}

	/**
	 * Dynamically renders the `core/table` block.
	 *
	 * @param 	string 	$content 	The block markup.
	 * @param 	array 	$block 		The parsed block.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( $content = '', $block = [], $data = null ) {
		$attributes = get_prop( $block, 'attrs', [] );

		$exclude = [
			'table-bordered',
			'table-borderless',
			'table-dark',
			'table-sm',
			'table-lg',
			'table-striped',
			'table-hover',
		];
		
		$classes = explode( ' ', get_prop( $attributes, 'className', '' ) );

		$border = get_prop( $attributes, [ 'style', 'border' ], [] );

		$matched = array_intersect( $classes, $exclude );
		$ommited = array_diff( $classes, $exclude );

		$doc = $this->load_html( $content );

		// Wrapper Changes
		$wrapper	= $doc->getElementsByTagName( 'figure' )->item(0);
		$classname 	= $wrapper->getAttribute( 'class' );
		$wrapper->setAttribute( 'class', join( ' ', array_filter( array_merge( explode( ' ', $classname ), $ommited ) ) ) );
		
		// Table Changes
		$table 		= $wrapper->getElementsByTagName( 'table' )->item(0);
		$table_cls  = [ 'table' ];
		
		if( $value = get_prop( $border, 'style', 'none' ) ) {
			$table_cls[] = $value === 'none' ? 'table-borderless' : 'table-bordered';
		}

		if( in_array( 'is-style-stripes', $classes, true ) ) {
			$table_cls[] = 'table-striped';
		}
		
		if( in_array( 'is-style-hover', $classes, true ) ) {
			$table_cls[] = 'table-hover';
		}

		if( ! empty( $matched ) ) {
			$table_cls = array_merge( $table_cls, $matched );
		}
		
		if( $dom_cls = $table->getAttribute( 'class' ) ) {
			$table_cls[] = $dom_cls;
		}

		// Set Table Classname
		$table->setAttribute( 'class', join( ' ', $table_cls ) );

		return $this->save_html( $doc->saveHTML() );
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public static function styles() {
		return "
		table {
			--wp--table-border-width: 1px;
			--wp--table-padding-x: .5rem;
			--wp--table-padding-y: .5rem;
			--wp--table-bg: transparent;
			--wp--table-accent-bg: transparent;
			--wp--table-striped-color: var(--wp--dark);
			--wp--table-striped-bg: rgba(0, 0, 0, 0.05);
			--wp--table-active-color: var(--wp--dark);
			--wp--table-active-bg: rgba(0, 0, 0, 0.1);
			--wp--table-hover-color: var(--wp--dark);
			--wp--table-hover-bg: rgba(0, 0, 0, 0.075);
		  
			width: 100%;
			margin-bottom: 1rem;
			vertical-align: middle;
			border-color: var(--wp--light);
		}
		table > thead {
			vertical-align: inherit;
		}
		table > tbody {
			vertical-align: inherit;
		}
		table > :not(caption) > * > * {
			padding: var(wp--table-padding-y) var(wp--table-padding-x);
			background-color: var(--wp--table-bg);
			border-bottom-width: var(--wp--table-border-width);
			box-shadow: inset 0 0 0 9999px var(--wp--table-accent-bg);
		}
		table > :not(:first-child) {
			border-top: calc(2 * 1px) solid currentColor;
		}
		.table-bordered > :not(caption) > * {
			border-width: var(--wp--table-border-width) 0;
		}
		.table-bordered > :not(caption) > * > * {
			border-width: 0 var(--wp--table-border-width);
		}
		.table-borderless > :not(caption) > * {
			border-bottom-width: 0;
		}
		.table-borderless > :not(:first-child) {
			border-top-width: 0;
		}
		.table-striped > tbody > tr:nth-of-type(2n) > * {
			--wp--table-accent-bg: var(--wp--table-striped-bg);
			color: var(--wp--table-striped-color);
		}
		.table-active {
			--wp--table-accent-bg: var(--wp--table-active-bg);
			color: var(--wp--table-active-color);
		}
		.table-hover > tbody > tr:hover > * {
			--wp--table-accent-bg: var(--wp--table-hover-bg);
			color: var(--wp--table-hover-color);
		}
		.caption-top {
			caption-side: top;
		}

		.wp-block-table.alignleft,
		.wp-block-table.aligncenter,
		.wp-block-table.alignright {
			display: table;
			width: auto;
		}
		.wp-block-table .has-fixed-layout {
			table-layout: fixed;
		}
		.wp-block-table thead,
		.wp-block-table tbody,
		.wp-block-table tfoot,
		.wp-block-table .table-bordered > :not(caption) > *,
		.wp-block-table .table-bordered > :not(caption) > * > * {
			border-color: inherit;
			border-style: inherit;
			border-width: inherit;
		}
		.wp-block-table td,
		.wp-block-table th {
			word-break: break-word;
		}
		.wp-block-table .table-striped tbody tr {
			--wp--table-striped-color: inherit;
		}
		";
	}
}
