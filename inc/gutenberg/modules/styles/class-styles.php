<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg CSS Module
 * @copyright   Copyright (c) 2022, WeCodeArt Framework
 * @since		4.0.3
 * @version		5.5.1
 */

namespace WeCodeArt\Gutenberg\Modules;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Integration;
use WeCodeArt\Config\Traits\Asset;
use function WeCodeArt\Functions\get_prop;

/**
 * Handles Gutenberg Theme CSS Functionality.
 */
class Styles implements Integration {

	use Singleton;
	use Asset;

	/**
	 * The CSS ID for registered style.
	 *
	 * @var string
	 */
    const CSS_ID 	= 'wecodeart-blocks-custom';

	/**
	 * The Styles Processor
	 *
	 * @access 	public
	 * @var 	null|object
	 */
	public $CSS		= null;

	/**
	 * The blocks styles
	 *
	 * @access 	public
	 * @var 	array
	 */
	public $styles	= [];

	/**
	 * The blocks duotone
	 *
	 * @access 	public
	 * @var 	array
	 */
	public $filters	= [];

	/**
	 * The blocks classes
	 *
	 * @access 	public
	 * @var 	array
	 */
	public $classes	= [];

	/**
	 * Current processed blocks
	 *
	 * @access 	public
	 * @var 	array
	 */
	public static $processed = [];

	/**
	 * Get Conditionals
	 *
	 * @return void
	 */
	public static function get_conditionals() {
		wecodeart( 'conditionals' )->set( [
			'with_blocks_styles' => Styles\Condition::class,
		] );

		return [ 'with_blocks_styles' ];
	}

	/**
	 * Register Hooks - into styles processor action if enabled
	 *
	 * @since 	5.0.0
	 *
	 * @return 	void
	 */
	public function register_hooks() {
		$this->CSS	= wecodeart( 'styles' );

		// Custom Style Attributes support
		\WP_Block_Supports::get_instance()->register( 'styleCustom', [
			'register_attribute' => [ $this, 'register_attribute' ],
		] );

		// Hooks
		add_filter( 'should_load_separate_core_block_assets', '__return_false' );
		add_action( 'enqueue_block_editor_assets',	[ $this, 'block_editor_assets' 	], 20, 1 );
		add_filter( 'render_block',					[ $this, 'filter_render' 		], 20, 2 );
		add_action( 'wp_enqueue_scripts',			[ $this, 'register_styles'		], 20, 1 );
		add_action( 'wp_body_open',					[ $this, 'output_duotone'		], 20, 1 );
		add_action( 'admin_init',					[ $this, 'editor_styles' 		], 20, 1 );
		
		// Remove WP/GB plugins hooks - we dont need this anymore!
		remove_filter( 'render_block', 'wp_render_spacing_gap_support', 10, 2 );
		remove_filter( 'render_block', 'wp_render_layout_support_flag', 10, 2 );
		remove_filter( 'render_block', 'wp_render_elements_support', 	10, 2 );
		remove_filter( 'render_block', 'wp_render_duotone_support',		10, 2 );
		
		// Eventually it will be removed - 1 check since they are all from GB.
		if( function_exists( 'gutenberg_render_layout_support_flag' ) ) {
			remove_filter( 'render_block', 'gutenberg_render_spacing_gap_support', 	10, 2 );
			remove_filter( 'render_block', 'gutenberg_render_layout_support_flag', 	10, 2 );
			remove_filter( 'render_block', 'gutenberg_render_elements_support', 	10, 2 );
			remove_filter( 'render_block', 'gutenberg_render_duotone_support', 		10, 2 );
			remove_action( 'wp_body_open', 'gutenberg_experimental_global_styles_render_svg_filters' );
		}
	}

	/**
	 * Generate Utilities CSS on admin.
	 *
	 * @return  void
	 */
	public function editor_styles() {
		$filesystem = wecodeart( 'files' );
		$filesystem->set_folder( 'cache' );

		add_editor_style( $filesystem->get_file_url( $this->CSS->Utilities::CACHE_FILE, true ) );
		
		$filesystem->set_folder( '' );
	}

	/**
	 * Editor only.
	 *
	 * @return  void
	 */
	public function block_editor_assets() {
		// Javascript
		wp_enqueue_script( $this->make_handle(), $this->get_asset( 'js', 'gutenberg/ext-styles' ), [
			'wecodeart-gutenberg-inline'
		], wecodeart( 'version' ) );
	}

	/**
	 * Adds the `customCSS` attributes to all blocks, to avoid `Invalid parameter(s): attributes` error.
	 *
	 * @since   5.2.x
	 *
	 * @return 	void
	 */
	public function register_attribute( $block ) {
		// Currently we suport only core blocks
		if( ! in_array( $block->name, self::core_blocks() ) ) return;

		$block->attributes['customCSS'] = [
			'type'    => 'string',
			'default' => null,
		];
	}

	/**
	 * Filter render_block
	 *
	 * @param	string 	$block_content
	 * @param	array 	$block
	 *
	 * @return 	string 	HTML
	 */
	public function filter_render( $content, $block ) {
		$block_name	= get_prop( $block, 'blockName' );

		// Skip nulls
		if( ! $block_name ) return $content;

		// Process a block
		$processed 	= self::process_block( $block );

		$block_id	= ltrim( $processed->get_element(), '.' );

		if( in_array( $block_id, self::$processed ) ) return $content;

		$styles 	= $processed->get_styles();
		$classes	= $processed->get_classes();
		$filters	= $processed->get_duotone();
		
		// Remove styles, where needed.
		// I'm not happy with this way but there is no other way to remove style attributes that I know, on PHP.
		// It would be ok with JS but that is time consuming to manage deprecation etc.
		if ( in_array( $block_name, self::core_blocks( true ) ) ) {
			// Target anything for most of the blocks.
			$regex		= '/(<[^>]+) style="([^"]*)"/i';
			$passes 	= 1;
			
			// Target only main wrapper for specific blocks - especialy the ones that can have innerBlocks/formatting.
			if( in_array( $block_name, [
				// Blocks with innerBlocks
				'core/cover',
				'core/column',
				'core/columns',
				'core/group',
				'core/media-text',
				// Blocks that render others
				'core/template-part',
				'core/post-template',
				// Here we need "cancel reply" link to be hidden
				'core/post-comments-form',
				// Blocks with inline formatting (text blocks)
				'core/code',
				'core/quote',
				'core/pullquote',
				'core/verse',
				'core/table',
				'core/preformatted'
				// for others (h/p/ul/ol) see bellow (they dont have autogenerated classes)
			] ) ) {
				$block_ = explode( '/', $block_name );
				$regex 	= '/(<[^>]*wp-block-' . end( $block_ ) . '[^"]*") style="([^"]*)"/i';
			}

			// Allow heading/paragraph/lists inline styles for mark
			if( in_array( $block_name, [ 'core/heading', 'core/paragraph', 'core/list' ] ) ) {
				$regex 	= '/(<(h[1-6]|p|ul|ol)[^>]+) style="([^"]*)"/i';
			}

			// Remove style attrs for first child elements on some blocks.
			if( in_array( $block_name, [ 'core/cover', 'core/media-text' ] ) ) {
				$passes	= 2;
			}

			$content 	= preg_replace( $regex, '$1', $content, $passes );
		}

		// Add necessary class - exclude blocks without wrappers, eg wp:pattern (commented out in core_blocks method).
		// We should have the possibility to directly change the block class, not with methods like this.
		// Currently php filter only works for PHP Server side rendered blocks.
		$content	= preg_replace( '/' . preg_quote( 'class="', '/' ) . '/', 'class="' . $block_id . ' ', $content, 1 );

		// Process CSS, add prefixes and convert to string!
		if( $styles ) {
			$this->styles[$block_id] = $styles;
		}
		
		// Process Duotone SVG filters
		if( $filters ) {
			$this->filters[$block_id] = $filters;
		}
		
		// Utilities CSS
		if( $classes ) {
			$this->classes = wp_parse_args( $classes, $this->classes );
		}

		// This is processed so next time we skipp it (avoid issues like multiple calls of this filter, if any)
		self::$processed[] = $block_id;

		return $content;
	}

	/**
	 * Output styles.
	 *
	 * @return 	string
	 */
	public function register_styles() {
		global $_wp_current_template_content;
		$inline_css = '';

		// Global styles
		$this->global_styles();

		// Collect Template Classes
		$blocks		= parse_blocks( $_wp_current_template_content );
		$classes	= self::collect_classes( _flatten_blocks( $blocks ) );

		$this->classes = array_merge( $this->classes, $classes );

		// Process Utilities
		if( ! empty( $this->classes ) ) {
			$this->CSS->Utilities->load( $this->classes );
		}

		// Process Attributes
		if( ! empty( $this->styles ) ) {
			foreach( $this->styles as $styles ) {
				$processed 	= $this->CSS::parse( $this->CSS::add_prefixes( $styles ) );
				$inline_css .= $this->CSS::compress( $processed );
			}
		}

		if( empty( $inline_css ) ) return;

		wp_register_style( self::CSS_ID, false, [], true, true );
		wp_add_inline_style( self::CSS_ID, $inline_css );
		wp_enqueue_style( self::CSS_ID );
	}
	
	/**
	 * Global styles.
	 *
	 * @return 	void
	 */
	public function global_styles() {
		$style  	= '';

		$palette 	= wecodeart_json( [ 'settings', 'color', 'palette', 'default' ], [] );
		$palette 	= wecodeart_json( [ 'settings', 'color', 'palette', 'theme' ], $palette );
		$palette 	= wecodeart_json( [ 'settings', 'color', 'palette', 'user' ], $palette );

		foreach( $palette as $item ) {
			$slug 	= get_prop( $item, [ 'slug' ] );
			$value 	= $this->CSS::hex_to_rgba( get_prop( $item, [ 'color' ] ) );
			$value  = preg_match( '/\((.*?)\)/', $value, $color );
			
			$style .= sprintf( '.has-%s-color{--wp--color--rgb:%s}', $slug, $color[1] );
			$style .= sprintf( '.has-%s-background-color{--wp--background--rgb:%s}', $slug, $color[1] );
		}

		$link_color = wecodeart_json( [ 'styles', 'elements', 'link', 'color', 'text' ], false );

		// Is WP way of saved color
		if( mb_strpos( $link_color, '|' ) !== false ) {
			$slug = explode( '|', $link_color );
			$slug = end( $slug );
		// Or is a CSS variable
		} elseif( mb_strpos( $link_color, '--' ) !== false ) {
			$slug = explode( '--', $link_color );
			$slug = str_replace( ')', '', end( $slug ) );
		}
		
		if ( isset( $slug  ) ) {
			// Otherwhise is a normal Hex color
			$link_color	= get_prop( current( wp_list_filter( $palette, [
				'slug' => $slug,
			] ) ), 'color', '#0088cc' );
		}

		// Darken the pallete link color (hex) on hover
		// Sanitized because we are not using CSS::parse method which does that by default (for arrays)
		$link_color = $this->CSS::hex_brightness( $this->CSS->Sanitize::color( $link_color ), -25 );

		$style .= "a:hover{color:${link_color}}";

		wp_add_inline_style( 'global-styles', $style );
	}

	/**
	 * Output duotone in footer.
	 *
	 * @return 	string
	 */
	public function output_duotone() {
		if( empty( $this->filters ) ) return;
		?>
		<svg xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 0 0" focusable="false" role="none" class="visually-hidden">
			<defs>
			<?php foreach( $this->filters as $block_id => $filter ) : ?>
				<filter id="wp-duotone-<?php echo esc_attr( $block_id ); ?>">
					<feColorMatrix
						type="matrix"
						color-interpolation-filters="sRGB"
						values=" .299 .587 .114 0 0 .299 .587 .114 0 0 .299 .587 .114 0 0 .299 .587 .114 0 0"
					/>
					<feComponentTransfer color-interpolation-filters="sRGB">
						<feFuncR type="table" tableValues="<?php echo esc_attr( implode( ' ', $filter['r'] ) ); ?>" />
						<feFuncG type="table" tableValues="<?php echo esc_attr( implode( ' ', $filter['g'] ) ); ?>" />
						<feFuncB type="table" tableValues="<?php echo esc_attr( implode( ' ', $filter['b'] ) ); ?>" />
						<feFuncA type="table" tableValues="<?php echo esc_attr( implode( ' ', $filter['a'] ) ); ?>" />
					</feComponentTransfer>
					<feComposite in2="SourceGraphic" operator="in" />
				</filter>
			<?php endforeach; ?>
			</defs>
		</svg>
		<?php
	}

	/**
	 * Core blocks.
	 *
	 * @param	bool	$exclude Whether to exclude blocks that should be skipped
	 *
	 * @return 	array
	 */
	public static function core_blocks( $exclude = false ) {
		// Exclude this blocks from styles extenstions for various reasons
		// like: no wrapper, renders html or other blocks or simply it should not remove style (core/post-content)
		$excludes = [
			'core/freeform',
			'core/html',
			'core/more',
			'core/block',
			'core/missing',
			'core/next-page',
			'core/pattern',
			'core/shortcode',
			'core/post-content'
		];

		$blocks = apply_filters( 'wecodeart/filter/gutenberg/styles/core', [
			'core/archive',
			'core/audio',
			'core/buttons',
			'core/block',
			'core/calendar',
			'core/categories',
			'core/code',
			'core/column',
			'core/columns',
			'core/cover',
			'core/comment-author-avatar',
			'core/comment-author-name',
			'core/comment-content',
			'core/comment-date',
			'core/comment-edit-link',
			'core/comment-reply-link',
			'core/comment-template',
			'core/comments-query-loop',
			'core/comments-pagination',
			'core/comments-pagination-next',
			'core/comments-pagination-previous',
			'core/comments-pagination-numbers',
			'core/embed',
			'core/file',
			'core/freeform',
			'core/gallery',
			'core/group',
			'core/heading',
			'core/home-link',
			'core/html',
			'core/image',
			'core/latest-comments',
			'core/latest-posts',
			'core/list',
			'core/loginout',
			'core/media-text',
			'core/missing',
			'core/more',
			'core/navigation',
			'core/navigation-link',
			'core/navigation-submenu',
			'core/next-page',
			'core/pattern',
			'core/page-list',
			'core/paragraph',
			'core/preformatted',
			'core/pullquote',
			'core/post-author',
			'core/post-author-name',
			'core/post-author-biography',
			'core/post-title',
			'core/post-terms',
			'core/post-date',
			'core/post-excerpt',
			'core/post-content',
			'core/post-featured-image',
			'core/post-navigation-link',
			'core/post-template',
			'core/post-comment',
			'core/post-comments',
			'core/post-comments-link',
			'core/post-comments-form',
			'core/post-comments-count',
			'core/quote',
			'core/query',
			'core/query-title',
			'core/query-pagination',
			'core/query-pagination-next',
			'core/query-pagination-previous',
			'core/query-pagination-numbers',
			'core/read-more',
			'core/rss',
			'core/search',
			'core/separator',
			'core/shortcode',
			'core/site-logo',
			'core/site-title',
			'core/site-tagline',
			'core/social-links',
			'core/social-link',
			'core/spacer',
			'core/table',
			'core/table-of-contents',
			'core/tag-cloud',
			'core/template-part',
			'core/term-description',
			'core/text-columns',
			'core/verse',
			'core/video',
		] );

		if( (bool) $exclude ) {
			$blocks = array_diff( $blocks, $excludes );
		}

		return $blocks;
	}

	/**
	 * Get classNames.
	 *
	 * @param	array	$blocks  List with all blocks
	 *
	 * @return 	array
	 */
	public static function collect_classes( array $blocks = [] ) {
		$return = [];

		foreach( $blocks as $block ) {
			// Dont bother with empty blocks from parse_blocks
			if( ! get_prop( $block, [ 'blockName' ] ) ) continue;

			$classes	= get_prop( $block, [ 'attrs', 'className' ] );
			$classes 	= array_filter( explode( ' ', $classes ) );

			if( ! empty( $classes ) ) {
				$return = array_merge( $return, $classes );
			}
		}

		return array_unique( $return );
	}

	/**
	 * Process a block.
	 *
	 * @param 	array 	$block 	The block data.
	 *
	 * @return 	object
	 */
	public static function process_block( $block = [] ) {
		// Find the class that will handle the output for this block.
		$classname	= Styles\Blocks::class;
		$defaults   = [
			'core/button' 			=> Styles\Blocks\Button::class,
			'core/cover' 			=> Styles\Blocks\Cover::class,
			'core/column' 			=> Styles\Blocks\Column::class,
			'core/image' 			=> Styles\Blocks\Image::class,
			'core/media-text' 		=> Styles\Blocks\Media::class,
			'core/navigation' 		=> Styles\Blocks\Navigation::class,
			'core/pullquote' 		=> Styles\Blocks\PullQuote::class,
			'core/search' 			=> Styles\Blocks\Search::class,
			'core/social-links'		=> Styles\Blocks\Social::class,
			'core/separator' 		=> Styles\Blocks\Separator::class,
			'core/spacer' 			=> Styles\Blocks\Spacer::class,
			'core/table' 			=> Styles\Blocks\Table::class,
			'core/post-featured-image'	=> Styles\Blocks\Featured::class,
		];

		$output_classes = apply_filters( 'wecodeart/filter/gutenberg/styles/processor', $defaults );

		if ( array_key_exists( $block['blockName'], $output_classes ) ) {
			$classname = $output_classes[ $block['blockName'] ];
		}
		
		if( class_exists( $classname ) ) {
			return ( new $classname( $block ) );
		};
	}
}