<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Core\Footer
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since 		3.5
 * @version		5.7.2
 */

namespace WeCodeArt\Core;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use function WeCodeArt\Functions\get_prop;

/**
 * This file serves as fallback for old PHP of way of building themes
 * and plugins who calls footer.php template. Uses WP_Query to render a
 * PHP version of the template from the /block-template-parts/ into footer
 */
class Footer {

	use Singleton;

	/**
	 * Send to Constructor
	 */
	public function init() {
		\add_action( 'wecodeart/footer',				[ $this, 'markup' ] );
		\add_filter( 'render_block_core/template-part',	[ $this, 'footer_shortcodes'	], 10, 2 );
	}
	
	/**
	 * Output FOOTER markup function Plugin PHP fallback
	 * 
	 * @since 	1.0
	 * @version	5.6.1
	 *
	 * @return 	HTML 
	 */
	public function markup( $args = [] ) {
		$args 	= wp_parse_args( $args, [
			'theme' 	=> wecodeart( 'name' ),
			'slug' 		=> 'footer',
			'tagName' 	=> 'footer',
			'className' => 'wp-site-footer'
		] );

		$content = '<!-- wp:template-part {"slug":"' . $args['slug'] . '","tagName":"' . $args['tagName'] . '","className":"' . $args['className'] . '","theme":"' . $args['theme'] . '"} /-->';

		echo do_blocks( $content ); 
	}

	/**
	 * Dynamically renders the footer `core/template-part` block to insert some shortcodes.
	 *
	 * @param 	string 	$content 	The block markup.
	 * @param 	array 	$block 		The parsed block.
	 *
	 * @return 	string 	The block markup.
	 */
	public function footer_shortcodes( $content = '', $block = [], $data = null ) {
		$is_footer = get_prop( $block, [ 'attrs', 'tagName' ], '' );

		if( strtolower( $is_footer ) === 'footer' ) {
			$content = str_replace( [
				'[copy]',
				'[year]',
				'[theme]',
			], [
				'&copy;',
				date( 'Y' ),
				sprintf( '<a href="%s" target="_blank">%s</a>', 'https://www.wecodeart.com/', 'WeCodeArt Framework' )
			], $content );
		}

		return $content;
	}
}