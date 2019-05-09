<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Core\Comments
 * @copyright   Copyright (c) 2019, WeCodeArt Framework
 * @since		3.5
 * @version		3.8.1
 */

namespace WeCodeArt\Core;

if ( ! defined( 'ABSPATH' ) ) exit();

use WeCodeArt\Core\Pagination;
use WeCodeArt\Utilities\Markup;
use WeCodeArt\Utilities\Markup\SVG;
use WeCodeArt\Utilities\Markup\Input;
use WeCodeArt\Walkers\Comment as CommentWalker;

/**
 * Handles Comments Functionality
 */
class Comments {

	use \WeCodeArt\Singleton;

	/**
	 * Send to Constructor
	 * @since 3.7.0
	 */
	public function init() {
		// WP Core
		add_filter( 'comment_form_fields',	[ $this, 'comment_form_fields' 		] );
		add_filter( 'comment_reply_link',	[ $this, 'replace_reply_link_class' ] );
		add_filter( 'comment_form_defaults',[ $this, 'comment_form_defaults' 	] );

		// WeCodeArt Core
		add_action( 'wecodeart_comments', [ $this, 'render_meta'		], 10 );
		add_action( 'wecodeart_comments', [ Pagination::get_instance(), 'comments' ], 15 ); 
		add_action( 'wecodeart_comments', [ $this, 'render_comments'	], 20 );
		add_action( 'wecodeart_comments', [ $this, 'render_pings'		], 30 );
		add_action( 'wecodeart_comments', [ Pagination::get_instance(), 'comments' ], 35 ); 
		add_action( 'wecodeart_comments', [ $this, 'render_respond'		], 40 );
		
		add_action( 'wecodeart/hook/entry/footer', [ $this, 'get_comments_template' ], 30 );
	}
	
	/**
	 * Get the comments template
	 *
	 * @since 	unknown
	 * @version	3.7.0
	 *
	 * @return 	void 
	 */
	public function get_comments_template() {
		// Only if CPT supports and singular entry
		if ( post_type_supports( get_post_type(), 'comments' ) && is_singular() ) comments_template( null, true ); 
		return;
	}

	/**
	 * Render Comments Info
	 *
	 * @since	3.7.3
	 * @version	3.8.3
	 *
	 * @return 	string
	 */
	public function get_comments_info( $echo = true ) {

		$comments_number = intval( get_comments_number() );

		$defaults = [
			'icon' 		=> SVG::compile( 'icon--comments' ) . ' ',
			'empty' 	=> esc_html__( 'No comments, so go and ...', 'wecodeart' ),
			'closed'	=> false,
			'add_one'	=> esc_html__( 'add one', 'wecodeart' ) 
		]; 

		$args = apply_filters( 'wecodeart/filter/comments/get_comments_info/args', $defaults, get_post_type() );

		$icon_html 	= '';
		$header_tx 	= '';
		if( comments_open() || $comments_number !== 0 ) {
			$icon_html = $args['icon'];
			if ( 0 !== $comments_number ) {
				$header_tx = sprintf(
					_nx( '%1$s comment', '%1$s comments', $comments_number, 'comments title', 'wecodeart' ),
					number_format_i18n( $comments_number )
				);
			} else {
				$header_tx = $args['empty']; 
			} 
		} else {
			if( $args['closed'] !== false && is_string( $args['closed'] ) ) $header_tx = $args['closed']; 
		} 

		// Prepare HTML output
		$output = ''; 
		$output .= $icon_html;
		$output .= $header_tx; 

		// Append `add comment` link
		if( comments_open() ) {
			$output .= sprintf( '<a class="float-right" href="#respond" rel="nofollow">%s</a>', $args['add_one'] ); 
		}

		$output = apply_filters( 'wecodeart/filter/comments/get_comments_info/output', trim( $output ) );

		if( $echo ) echo $output;
		return $output; 
	} 

	/**
	 * Render Comments List
	 *
	 * @since	unknown
	 * @version 3.7.0
	 *
	 * @return 	void
	 */
	public function render_comments() {  
		if ( have_comments() ) {  
			Markup::wrap( 'comments-list', [ [ 
				'tag' 	=> 'ol', 
				'attrs' => [ 
					'id' 	=> 'comments-list', 
					'class' => 'comments__list unstyled pl-0' 
				] 
			] ], function() { // Required as this to be read by theme check
				wp_list_comments( apply_filters( 'wecodeart/filter/comments/list/args', [
					'type' 		 	=> 'comment',
					'avatar_size' 	=> 60,
					'walker'		=> new CommentWalker,
				] ) ); 
			} );  
		} 
		return;
	}

	/**
	 * Render Pings List.
	 *
	 * @since	unknown
	 * @version 3.7.8
	 *
	 * @return 	void
	 */
	public function render_pings() {
		global $wp_query;
		
		// If have pings.
		if ( ! empty( $wp_query->comments_by_type['pings'] ) ) {  
			Markup::wrap( 'pings-list', [ [ 
				'tag' 	=> 'ol', 
				'attrs' => [ 
					'id' 	=> 'pings-list', 
					'class' => 'comments__pings unstyled pl-0 pings-list' 
				] 
			] ], 'wp_list_comments', [ apply_filters( 'wecodeart/filter/comments/pings/args', [
				'type' 		 => 'pings',
				'short_ping' => true,
				'walker'	 => new CommentWalker,
			] ) ] ); 
		}
	}

	/**
	 * Render Comments Info
	 *
	 * @since	3.7.0
	 *
	 * @return	string	HTML
	 */
	public function render_meta() {  
		Markup::wrap( 'comments-info', [ [ 
			'tag' 	=> 'h3', 
			'attrs' => [ 
				'id' 	=> 'comments-title', 
				'class' => 'comments__headline' 
			] 
		] ], [ $this, 'get_comments_info' ] );
	}

	/**
	 * Render Comment Form.
	 *
	 * @since	unknown
	 *
	 * @version 3.6
	 */
	public function render_respond() {
		// Bail if comments are closed for this post type.
		if ( ( is_singular() && ! comments_open() ) ) return;
	
		$args = apply_filters( 'wecodeart/filter/comments/form/args',[
			'format'			 	=> 'html5',
			'title_reply_before' 	=> '<h3 id="reply-title" class="headline"> ' . SVG::compile( 'icon--comment-dots' ) . ' ',
			'title_reply_after'  	=> '</h3>',
			'class_form' 			=> 'comment-form row no-gutters',
			'class_submit' 			=> 'btn btn-primary',
			'cancel_reply_before' 	=> '<span class="float-right"><small>',
			'cancel_reply_after' 	=> '</small></span>'
		] );

		comment_form( $args );
	} 

	/**
	 * Move Comment Field Bellow Name/Email/Website.
	 *
	 * @since	unknown
	 * @version 3.5
	 *
	 * @param 	array $fields
	 *
	 * @return 	array
	 */
	public function comment_form_fields( $fields ) {
		$comment_field = $fields['comment'];
		unset( $fields['comment'] );
		
		$fields['comment'] = $comment_field;
		return $fields;
	}

	/**
	 * Filter Comment Respond Args.
	 *
	 * @since	unknown
	 * @version	3.8.6
	 *
	 * @return 	array
	 */
	public function comment_form_defaults( $defaults ) {
		// Fields escapes all the data for us. 	
		$commenter = wp_get_current_commenter();
		$req       = get_option( 'require_name_email' );
		
		$author_name	= '<div class="form-group comment-form-author col-12 col-md-7">' .
			Input::compile( 'text', esc_html__( 'Name *', 'wecodeart' ), array( 
				'id' 	=> 'comment-author',
				'class'	=> 'form-control',
				'name' 	=> 'author', 
				'required' 	=> ( $req ) ? 'required' : NULL, 
				'size' 		=> absint( 30 ), 
				'maxlength' => absint( 245 ),
				'value' 	=> $commenter['comment_author'] 
				) 
			)
		. '</div>';
		
		$author_email	= '<div class="form-group comment-form-email col-12 col-md-7">' .
			Input::compile( 'email', esc_html__( 'Email *', 'wecodeart' ), array( 			
				'id' 	=> 'comment-email',
				'class'	=> 'form-control',
				'name' 	=> 'email',
				'required' 	=> ( $req ) ? 'required' : NULL, 
				'size' 		=> absint( 30 ), 
				'maxlength' => absint( 100 ),
				'value' 	=> $commenter['comment_author_email'] 
				)
			) 
		. '</div>';
		
		$author_url		= '<div class="form-group comment-form-url col-12 col-md-7">' .
			Input::compile( 'url', esc_html__( 'Website', 'wecodeart' ), array( 
				'id' 	=> 'comment-url',
				'class'	=> 'form-control',
				'name' 	=> 'url',
				'size' 		 => absint( 30 ), 
				'maxlength'  => absint( 200 ),
				'value' 	 => $commenter['comment_author_url'] 
				)
			) 
		. '</div>';

		$author_comment	= '<div class="form-group comment-form-comment w-100">' .
			Input::compile( 'textarea', esc_html__( 'Comment*', 'wecodeart' ), array( 
				'id' 	=> 'comment',
				'class'	=> 'form-control',
				'name' 	=> 'comment',
				'rows'	=> absint( 8 ), 
				'cols'  => absint( 45 ), 
				'aria-required'  => 'true' 
				)
			) 
		. '</div>';
		
		$required_text 	= sprintf( ' ' . esc_html__( 'Required fiels are marked %s', 'wecodeart' ), '<span class="required">*</span>' );
		$notes_before 	= '<div class="form-group comment-form-notes">' . esc_html__( 'Your email address will not be published.', 'wecodeart' ) . ( $req ? $required_text : '' ) . '</div>';
		$notes_after 	= '<div class="form-group comment-form-allowed-tags">' . sprintf( __( 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes: %s', 'wecodeart' ), ' <code>' . allowed_tags() . '</code>' ) . '</div>';

		$args = [
			'title_reply' 			=> esc_html__( 'Speak Your Mind', 'wecodeart' ),
			'comment_field' 		=> $author_comment,
			'comment_notes_before' 	=> $notes_before,
			'comment_notes_after' 	=> $notes_after,
			'submit_field'         	=> '<div class="form-group comment-form-submit">%1$s %2$s</div>',
			'submit_button'         => '<button name="%1$s" type="submit" id="%2$s" class="%3$s">%4$s</button>',
			'class_submit'         	=> 'btn btn-primary',
			'fields' => [
				'author' => $author_name,
				'email'  => $author_email,
				'url'    => $author_url
			]
		];

		// Merge $args with $defaults
		$args = wp_parse_args( $args, $defaults );

		// Return filterable array of $args, along with other optional variables
		return apply_filters( 'wecodeart/filter/comments/respond/args', $args, $commenter, $req ); 
	}

	/**
	 * Replace Comment Reply Button class.
	 *
	 * @since	unknown
	 * @version 3.7.7
	 *
	 * @param 	string $class
	 *
	 * @return 	string
	 */
	public function replace_reply_link_class( $class ) {
		$class = str_replace( "class='comment-reply-link", "class='comment-reply-link btn btn-primary btn-sm", $class );
		return $class;
	} 
}