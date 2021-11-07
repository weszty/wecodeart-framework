<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Core\Entry\Comments
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		3.5
 * @version		5.2.2
 */

namespace WeCodeArt\Core;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Markup;
use WeCodeArt\Markup\SVG;
use WeCodeArt\Markup\Input;
use WeCodeArt\Markup\Walkers\Comment as CommentWalker;

/**
 * Handles Comments Functionality
 */
class Comments {

	use Singleton;

	/**
	 * Send to Constructor
	 * @since 3.7.0
	 */
	public function init() {
		// WP Core
		add_filter( 'comment_form_fields',		[ $this, 'comment_form_fields' 		], 90 );
		add_filter( 'comment_form_defaults',	[ $this, 'comment_form_defaults' 	], 90 );
		add_action( 'pre_comment_on_post',  	[ $this, 'validate_cookies'			] );
	}

	/**
	 * Move Comment Field Bellow Name/Email/Website.
	 *
	 * @since	unknown
	 * @version 5.0.0
	 *
	 * @param 	array $fields
	 *
	 * @return 	array
	 */
	public function comment_form_fields( $fields ) {
		$comment_field = $fields['comment'];
		$cookies_field = $fields['cookies'];
		unset( $fields['comment'], $fields['cookies'] );
		
		$fields['comment'] = $comment_field;
		$fields['cookies'] = $cookies_field;
		return $fields;
	}

	/**
	 * Filter Comment Respond Args.
	 *
	 * @since	unknown
	 * @version	5.1.8
	 *
	 * @return 	array
	 */
	public function comment_form_defaults( $defaults ) {
		// Fields escapes all the data for us. 	
		$commenter = wp_get_current_commenter();
		$req       = get_option( 'require_name_email' );

		$inputs = [
			'name' => wecodeart_input( 'floating', [
				'type' 	=> 'text',
				'label' => esc_html__( 'Name *', 'wecodeart' ),
				'attrs' => [
					'id' 	=> 'comment-author',
					'name' 	=> 'author',
					'required' 		=> ( $req ) ? 'required' : NULL,
					'size' 			=> 30,
					'maxlength' 	=> 245,
					'placeholder'	=> 'John Doe',
					'value' 		=> $commenter['comment_author']
				]
			], false ),
			'email' => wecodeart_input( 'floating', [
				'type' 	=> 'email',
				'label' => esc_html__( 'Email *', 'wecodeart' ),
				'attrs' => [
					'id' 	=> 'comment-email',
					'name' 	=> 'email',
					'required' 		=> ( $req ) ? 'required' : NULL,
					'size' 			=> 30,
					'maxlength' 	=> 100,
					'placeholder'	=> 'name@example.com',
					'value' 		=> $commenter['comment_author_email']
				]
			], false ),
			'url' => wecodeart_input( 'floating', [
				'type' 	=> 'url',
				'label' => esc_html__( 'Website', 'wecodeart' ),
				'attrs' => [
					'id' 	=> 'comment-url',
					'name' 	=> 'url',
					'size' 		 => 30, 
					'maxlength'  => 200,
					'placeholder'=> 'www.example.com',
					'value' 	 => $commenter['comment_author_url']  
				]
			], false ),
			'comment' => wecodeart_input( 'floating', [
				'type'	=> 'textarea',
				'label' => esc_html__( 'Comment *', 'wecodeart' ),
				'attrs' => [
					'id' 	=> 'comment',
					'name' 	=> 'comment',
					'rows'	=> 8,
					'cols'  => 45,
					'style'	=> 'min-height:150px;',
					'placeholder'	=> esc_html__( 'Comment *', 'wecodeart' ),
					'required'		=> ( $req ) ? 'required' : NULL,
				]
			], false )
		];

		$author_name	= Markup::wrap( 'comment-author-name', [ [
			'tag' 	=> 'div',
			'attrs' => [
				'class' => 'mb-3 comment-form-author col-md-7'
			]
		] ], $inputs['name'], null, false );

		// Author Email.
		$author_email = Markup::wrap( 'comment-author-email', [ [
			'tag' 	=> 'div',
			'attrs' => [
				'class' => 'mb-3 comment-form-email col-md-7'
			]
		] ], $inputs['email'], null, false );

		// Author URL.
		$author_url	= Markup::wrap( 'comment-author-url', [ [
			'tag' 	=> 'div',
			'attrs' => [
				'class' => 'mb-3 comment-form-url col-md-7'
			]
		] ], $inputs['url'], null, false );

		// The Comment.
		$author_comment	= Markup::wrap( 'comment-field', [ [
			'tag' 	=> 'div',
			'attrs' => [
				'class' => 'mb-3 comment-form-comment'
			]
		] ], $inputs['comment'], null, false );

		// Cookies
		$cookies = false;
		$privacy_policy = get_option( 'wp_page_for_privacy_policy' );

		if( $privacy_policy && get_post_status( $privacy_policy ) === 'publish' ) {
			$cookies = Markup::wrap( 'comment-cookies', [ [
				'tag' 	=> 'div',
				'attrs' => [
					'class' => 'mb-3 comment-form-cookies'
				]
			] ], wecodeart_input( 'toggle', [
				'type'	=> 'checkbox',
				'label' => sprintf( __( 'By commenting you accept the %s.', 'wecodeart' ), sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url( get_privacy_policy_url() ),
					esc_html( get_the_title( $privacy_policy ) ) )
				),
				'attrs' => [
					'class'		=> 'form-switch',
					'id' 		=> 'comment-cookies',
					'name' 		=> 'comment-cookies',
					'required'  => ( $req ) ? 'required' : NULL,
					'aria-required'  => 'true',
				]
			], false ), null, false );
		}

		$args = [
			'format'			 	=> 'html5',
			'class_container'		=> 'wp-block-post-comments-form__wrap mt-3 mb-5',
			'class_form' 			=> 'wp-block-post-comments-form__form comment-form needs-validation',
			'title_reply' 			=> SVG::compile( 'comment-dots', [ 'class' => 'fa-2x me-2' ] ) . esc_html__( 'Speak Your Mind', 'wecodeart' ),
			'title_reply_before' 	=> '<h3 id="reply-title" class="wp-block-post-comments-form__headline mb-3">',
			'title_reply_after'  	=> '</h3>',
			'cancel_reply_before'  => '<span class="has-small-font-size my-2 float-end">',
			'cancel_reply_after'   => '</span>',
			'cancel_reply_link'    => esc_html__( 'Cancel reply', 'wecodeart' ),
			'comment_field' 		=> $author_comment,
			'comment_notes_before' 	=> Markup::wrap( 'comment-notes-before', [ [
				'tag' 	=> 'div',
				'attrs' => [
					'class' => 'mb-3 comment-form-notes'
				]
			] ], function() use ( $req ) {
				$string = esc_html__( 'Your email address will not be published.', 'wecodeart' );
				if( $req ) {
					$string .= ' ' . sprintf( esc_html__( 'Required fiels are marked "%s".', 'wecodeart' ), 
						'<span class="required">*</span>' 
					);
				}
				echo wp_kses_post( $string );
			}, [], false ),
			'submit_field'	=> '<div class="mb-3 comment-form-submit">%1$s %2$s</div>',
			'submit_button'	=> '<button name="%1$s" type="submit" id="%2$s" class="%3$s">' . SVG::compile( 'comment-dots', [
				'class' => 'me-1'
			] ) . '<span>%4$s</span></button>',
			'class_submit'	=> 'btn btn-outline-dark',
			'fields' => [
				'author' 	=> $author_name,
				'email'  	=> $author_email,
				'url'    	=> $author_url,
				'cookies'	=> $cookies,
			]
		];

		return wp_parse_args( $args, $defaults );
	}

	/**
	 * Validate Cookie field.
	 *
	 * @since	5.0.0
	 * @since	5.0.5
	 *
	 * @return 	void
	 */
	public function validate_cookies() {
		$privacy_policy = get_option( 'wp_page_for_privacy_policy' );

		if( get_post_status( $privacy_policy ) !== 'publish' || is_user_logged_in() === false ) return;

		if( ! filter_input( INPUT_POST, 'comment-cookies' ) ) {
			wp_die( sprintf( esc_html__( 'You must accept %s to comment!', 'wecodeart' ), sprintf(
				'<a href="%1$s" target="_blank">%2$s</a>',
				esc_url( get_privacy_policy_url() ),
				esc_html( get_the_title( $privacy_policy ) )
			) ) );
		}
	}
}