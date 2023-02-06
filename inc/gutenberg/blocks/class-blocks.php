<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg Blocks Registry
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since		5.0.0
 * @version		5.7.2
 */

namespace WeCodeArt\Gutenberg;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use function WeCodeArt\Functions\get_prop;
use WeCodeArt\Config\Interfaces\Configuration;

/**
 * Gutenberg Blocks Registry.
 */
class Blocks implements Configuration {

	use Singleton;

	/**
	 * The registered Blocks.
	 *
	 * @var Blocks[]
	 */
	protected   $items = [];

	/**
	 * Send to Constructor
	 */
	public function init() {
        // Media Blocks
		$this->register( 'core/media-text', Blocks\Media\Text::class );
		$this->register( 'core/file',	    Blocks\Media\File::class );
        $this->register( 'core/image',	    Blocks\Media\Image::class );
        $this->register( 'core/audio',	    Blocks\Media\Audio::class );
        $this->register( 'core/video',	    Blocks\Media\Video::class );
        $this->register( 'core/embed',	    Blocks\Media\Embed::class );
        $this->register( 'core/cover',	    Blocks\Media\Cover::class );
        $this->register( 'core/gallery',    Blocks\Media\Gallery::class );
        // Text Blocks
        $this->register( 'core/code',	    Blocks\Text\Code::class );
        $this->register( 'core/list',	    Blocks\Text\Lists::class );
        $this->register( 'core/table',	    Blocks\Text\Table::class );
		$this->register( 'core/quote',	    Blocks\Text\Quote::class );
		$this->register( 'core/heading',    Blocks\Text\Heading::class );
		$this->register( 'core/paragraph',  Blocks\Text\Paragraph::class );
		$this->register( 'core/pullquote',  Blocks\Text\Pullquote::class );
        // Design Blocks
		$this->register( 'core/buttons',    Blocks\Design\Buttons::class );
		$this->register( 'core/button',     Blocks\Design\Button::class );
		$this->register( 'core/columns',    Blocks\Design\Columns::class );
		$this->register( 'core/spacer',     Blocks\Design\Spacer::class );
		$this->register( 'core/separator',  Blocks\Design\Separator::class );
        // Widget Blocks
		$this->register( 'core/archives',           Blocks\Widgets\Archives::class );
		$this->register( 'core/calendar',           Blocks\Widgets\Calendar::class );
	    $this->register( 'core/rss',                Blocks\Widgets\RSS::class );
	    $this->register( 'core/latest-posts',       Blocks\Widgets\Posts::class );
		$this->register( 'core/latest-comments',    Blocks\Widgets\Comments::class );
		$this->register( 'core/search',	            Blocks\Widgets\Search::class );
		$this->register( 'core/social-links',       Blocks\Widgets\Social::class );
        // Navigation Blocks
		$this->register( 'core/navigation',         Blocks\Navigation::class );
		$this->register( 'core/navigation-link',    Blocks\Navigation\Link::class );
		$this->register( 'core/navigation-submenu', Blocks\Navigation\Menu::class );
		$this->register( 'core/home-link',          Blocks\Navigation\Home::class );
		$this->register( 'core/page-list',          Blocks\Navigation\Pages::class );
        // Post Blocks
		$this->register( 'core/post-date',          Blocks\Post\Date::class );
		$this->register( 'core/post-title',         Blocks\Post\Title::class );
		$this->register( 'core/post-terms',         Blocks\Post\Terms::class );
		$this->register( 'core/post-author',        Blocks\Post\Author::class );
		$this->register( 'core/post-author-name',   Blocks\Post\AuthorName::class );
		$this->register( 'core/post-excerpt',       Blocks\Post\Excerpt::class );
		$this->register( 'core/post-content',       Blocks\Post\Content::class );
		$this->register( 'core/post-template',      Blocks\Post\Template::class );
		$this->register( 'core/post-featured-image',Blocks\Post\Image::class );
		$this->register( 'core/post-comments-link', Blocks\Post\Comments::class );
        // Comment Blocks
		$this->register( 'core/post-comments-form', Blocks\Comment\Form::class );
		$this->register( 'core/comments-title',     Blocks\Comment\Title::class );
		$this->register( 'core/comment-template',   Blocks\Comment\Template::class );
        // Query Blocks
		$this->register( 'core/query-title',	            Blocks\Query\Title::class );
		$this->register( 'core/query-pagination-numbers',   Blocks\Query\Pagination\Numbers::class );
        // Site Blocks
		$this->register( 'core/loginout',       Blocks\Site\Login::class );
		$this->register( 'core/site-logo',      Blocks\Site\Logo::class );
		$this->register( 'core/template-part',  Blocks\Site\Template::class );
        
        // Hooks
        add_action( 'after_setup_theme',        [ $this, 'register_blocks'  ], PHP_INT_MAX );
        add_action( 'wp_print_styles',          [ $this, 'remove_styles'    ], PHP_INT_MAX );
        add_filter( 'should_load_separate_core_block_assets', '__return_true', PHP_INT_MAX );
	}

    /**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register_blocks() {
        foreach( $this->all() as $class ) {
            $class::get_instance()->hooks();
        }
	}

    /**
	 * Remove default styles
	 *
	 * @return void
	 */
	public function remove_styles() {
        wp_dequeue_style( 'wp-block-library' );         // WordPress Core
        wp_dequeue_style( 'wp-block-library-theme' );   // WordPress Core
	}

	/**
     * Set a given module value.
     *
     * @param  array|string  $key
     * @param  mixed   $value
     *
     * @return void
     */
    public function register( $key, $value = null ) {
        $this->set( $key, $value );
	}
	
    /**
     * Set a given module value.
     *
     * @param  array|string  $key
     * @param  mixed   $value
     *
     * @return void
     */
    public function set( $key, $value = null ) {
        $keys = is_array( $key ) ? $key : [ $key => $value ];

        foreach ( $keys as $key => $value ) {
            $this->items[$key] = apply_filters( "wecodeart/gutenberg/blocks/set/{$key}", $value );
        }
	}

	/**
     * Determine if the given Blocks value exists.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function has( $key ) {
        return isset( $this->items[$key] );
    }

    /**
     * Get the specified Blocks value.
     *
     * @param  string  $key
     * @param  mixed   $default
     *
     * @return mixed
     */
    public function get( $key, $default = null ) {
        if ( ! isset( $this->items[$key] ) ) {
            return $default;
        }

        return apply_filters( "wecodeart/gutenberg/blocks/get/{$key}", $this->items[$key] );
    }
	
	/**
     * Removes module from the container.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function forget( $key ) {
		unset( $this->items[$key] );
    }

    /**
     * Get all of the module items for the application.
     *
     * @return array
     */
    public function all() {
        return $this->items;
    }
}