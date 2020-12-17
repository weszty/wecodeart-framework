<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Core\Pagination
 * @copyright   Copyright (c) 2020, WeCodeArt Framework
 * @since 		3.5
 * @version		4.2.0
 */

namespace WeCodeArt\Core;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Markup;

/**
 * Handles Paginations
 * Method calls are not called here, but in their own Classes
 * {
 * - WeCodeArt\Core\Entry       @entry_content()/entry_prev_next()
 * - WeCodeArt\Core\Content     @arhive()
 * - WeCodeArt\Core\Comments    @comments()
 * }
 */
class Pagination {

	use \WeCodeArt\Singleton;

	/**
	 * Send to Constructor
	 * @since 3.6.7
	 */
	public function init() {
        add_filter( 'wecodeart/filter/entry/prev_next_nav/enabled', [ $this, 'filter_prev_next_page' ], 10, 2 );
	}

	/**
     * Display links to previous and next post, from a single post.
     *
     * @since	1.0.0
     * @version 4.2.0
     *
     * @return  string HTML
     */
    public function archive() {
        $args = [
            'mixed' => 'array',
            'type' 	=> 'array',
        ];
        
        $links = paginate_links( $args );
        
        if ( empty( $links ) || is_singular() ) return; 	
        
        /**
         * @since 3.7.1
         */
        Markup::wrap( 'pagination', [
            [
                'tag'   => 'nav',
                'attrs' => [
                    'class'     => 'mb-5',
                    'itemscope' => true,
                    'itemtype'  => 'https://schema.org/SiteNavigationElement'
                ]
            ],
            [
                'tag'   => 'ul',
                'attrs' => [
                    'class'         => 'pagination pagination-sm',
                    'aria-label'    => esc_html__( 'Pagination', 'wecodeart' )
                ]
            ] 
        ], function() use ( $links ) { 
            foreach( $links as $key => $link ) :
                $class = [ 'page-item', 'pagination__item' ];
                if( strpos( $link, 'current' ) !== false ) $class[] = 'pagination__item--current';
                if( strpos( $link, 'current' ) !== false ) $class[] = 'active';
                $class = array_map( 'sanitize_html_class', $class );
            ?>
            <li class="<?php echo esc_attr( trim( implode( ' ', $class ) ) ); ?>">
                <?php echo str_replace( 'page-numbers', 'page-link', $link ); ?>
            </li> 				
            <?php 
            endforeach; 
        } ); 
    }

    /**
     * WP-Link Pages for paginated posts
     *
     * @since	unknown
     * @version 4.2.0
     *
     * @return 	null 	Return early if not a post.
     */
    public function entry_content() {
        // Return only on Single Post.
        if ( ! is_singular( 'post' ) ) return;	
        
        /**
         * @since 3.7.1
         */
        Markup::wrap( 'entry-pagination', [
           [
            'tag'   => 'nav',
            'attrs' => [
                'class'     => 'pagination pagination--entry pb-3',
                'aria-label'=> esc_html__( 'Pagination', 'wecodeart' ),
                'itemscope' => true,
                'itemtype'  => 'https://schema.org/SiteNavigationElement'
            ] 
        ] ], function() {
            wp_link_pages( apply_filters( 'wecodeart/filter/entry/content_nav/args', [
                'before'        => '',
                'after'         => '',
                'link_before'   => '<span class="page-link">',
                'link_after'    => '</span>',
            ] ) );
        } );
    }

    /**
	 * Display links to previous and next post, from a single post.
     *
	 * @since	1.0.0
	 * @version	4.2.0
     *
     * @return  null    Return early if not a post.
	 */
	public function entry_prev_next() {
        // Return only on Single Post.
        $navigation_enabled = apply_filters( 'wecodeart/filter/entry/prev_next_nav/enabled', true, get_post_type() );
        
        if ( ! is_singular() || $navigation_enabled === false ) return;
        
		Markup::wrap( 'entry-navigation', [ 
			[ 
                'tag'   => 'nav', 
                'attrs' => [ 
                    'class'         => 'entry-navigation',
                    'aria-label'    => esc_html__( 'Navigation', 'wecodeart' ),
                    'itemscope'     => true,
                    'itemtype'      => 'https://schema.org/SiteNavigationElement',
                ] 
            ],
			[ 
                'tag'   => 'div', 
                'attrs' => [ 
                    'class' => 'row py-4' 
                ] 
            ]
		], function() { ?>
            <h3 class="screen-reader-text"><?php 
                printf( 
                    esc_html__( '%s Navigation', 'wecodeart' ), 
                    get_post_type_object( get_post_type() )->labels->singular_name
                ); 
            ?></h3>
            <?php 
            
            $args_prev = apply_filters( 'wecodeart/filter/entry/navigation/prev/args', [] );
			$args_next = apply_filters( 'wecodeart/filter/entry/navigation/next/args', [] );

			Markup::wrap( 'entry-navigation-prev', [ [ 
				'tag' 	=> 'div', 
				'attrs' => [
                    'class' => 'col-sm-12 col-md' 
                ]
			] ], 'previous_post_link', $args_prev );
	
			Markup::wrap( 'entry-navigation-next', [ [ 
				'tag' 	=> 'div', 
				'attrs' => [
                    'class' => 'col-sm-12 col-md text-md-end'
                ] 
			] ], 'next_post_link', $args_next );  
        } );  
    }

    /**
	 * Render Coments Pagination
	 *
	 * @since 	3.7.0
	 * @version 4.2.0
	 *
	 * @return 	string|null
	 */
	public function comments() {
		/**
		 * Early Break
		 */
		if( empty( get_previous_comments_link() || get_next_comments_link() ) ) return;

		Markup::wrap( 'comments-nav', [
			[
                'tag'   => 'nav',
                'attrs' => [
                    'class'         => 'comments__nav',
					'aria-label'    => esc_html__( 'Navigation', 'wecodeart' ),
                    'itemscope'     => true,
                    'itemtype'      => 'https://schema.org/SiteNavigationElement',
                ]
            ],
			[
                'tag'   => 'div',
                'attrs' => [
                    'class' => 'row pb-3'
                ]
            ]
		], function() {
			?>
            <h3 class="screen-reader-text"><?php esc_html_e( 'Comments Navigation', 'wecodeart' ); ?></h3>
			<?php 
			
			$args_prev = apply_filters( 'wecodeart/filter/comments/navigation/prev/args', [] );
			$args_next = apply_filters( 'wecodeart/filter/comments/navigation/next/args', [] );

			Markup::wrap( 'comments-prev-link', [ [ 
				'tag' 	=> 'div', 
				'attrs' => [ 
					'class' => 'col-sm-12 col-md'
				] 
			] ], 'previous_comments_link', $args_prev ); 
	
			Markup::wrap( 'comments-next-link', [ [ 
				'tag' 	=> 'div', 
				'attrs' => [
					'class' => 'col-sm-12 col-md text-md-end'
				] 
			] ], 'next_comments_link', $args_next );  
		} );  
    }
    
    /**
     * Filter to disable prev/next nav on pages
     *
     * @since   3.6.0.6
     *
     * @param   boolean     $enabled
     * @param   string      $post_type
     *
     * @return  boolean
     */
    public function filter_prev_next_page( $enabled, $post_type ) {
        if( $post_type === 'page' ) $enabled = false;
        return $enabled;
    }
}