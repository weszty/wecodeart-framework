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
 * @since		5.2.2
 * @version		5.4.8
 */

namespace WeCodeArt\Gutenberg\Blocks\Comment;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Comment Form block.
 */
class Form extends Dynamic {

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
	protected $block_name = 'post-comments-form';

	/**
	 * Shortcircuit Register
	 */
	public function register() {
		add_filter( 'comment_form_fields',		[ $this, 'comment_form_fields' 		], 90 );
		add_filter( 'comment_form_defaults',	[ $this, 'comment_form_defaults' 	], 90 );
		add_action( 'pre_comment_on_post',  	[ $this, 'validate_privacy'			] );
	}

	/**
	 * Move Comment Field Bellow Name/Email/Website.
	 *
	 * @since	unknown
	 * @version 5.3.3
	 *
	 * @param 	array $fields
	 *
	 * @return 	array
	 */
	public function comment_form_fields( $fields ) {
		$comment_field = $fields['comment'];
		$privacy_field = isset( $fields['privacy'] ) ? $fields['privacy'] : null;

		unset( $fields['comment'], $fields['privacy'] );
		
		$fields['comment'] = $comment_field;
		$fields['privacy'] = $privacy_field;

		return $fields;
	}

	/**
	 * Filter Comment Respond Args.
	 *
	 * @since	unknown
	 * @version	5.3.3
	 *
	 * @return 	array
	 */
	public function comment_form_defaults( $defaults ) {
		$dots= wecodeart( 'markup' )->SVG::compile( 'comment-dots' );

		return wp_parse_args( [
			'format'			 	=> 'html5',
			'class_container'		=> 'wp-block-post-comments-form__wrap',
			'class_form' 			=> 'wp-block-post-comments-form__form comment-form needs-validation',
			'title_reply' 			=> $dots . esc_html__( 'Speak Your Mind', 'wecodeart' ),
			'title_reply_before' 	=> '<h3 class="wp-block-post-comments-form__headline" id="reply-title">',
			'title_reply_after'  	=> '</h3>',
			'cancel_reply_before'  	=> '<span class="has-small-font-size float-end">',
			'cancel_reply_after'   	=> '</span>',
			'cancel_reply_link'    	=> esc_html__( 'Cancel reply', 'wecodeart' ),
			'comment_field' 		=> $this->get_input( 'comment' ),
			'comment_notes_before' 	=> $this->get_form_notes(),
			'submit_field'			=> '<div class="comment-form-field">%1$s %2$s</div>',
			'submit_button'			=> $this->get_input( 'submit' ),
			'class_submit'			=> 'comment-form-submit',
			'fields' 				=> [
				'author' 	=> $this->get_input( 'name' ),
				'email'  	=> $this->get_input( 'email' ),
				'url'    	=> $this->get_input( 'url' ),
				'privacy'	=> $this->get_input( 'privacy' ),
			]
		], $defaults );
	}

	/**
	 * Get Name Input
	 *
	 * @since	5.2.2
	 * @since	5.3.3
	 *
	 * @return 	void
	 */
	public function get_input( $type = '' ) {
		$commenter = wp_get_current_commenter();
		$req       = get_option( 'require_name_email' );

		switch( $type ) {
			case 'name':
				return wecodeart( 'markup' )::wrap( 'comment-author-name', [ [
					'tag' 	=> 'div',
					'attrs' => [
						'class' => 'comment-form-field comment-form-author col-md-7'
					]
				] ], 'wecodeart_input', [ 'floating', [
					'type' 	=> 'text',
					'label' => esc_html__( 'Name *', 'wecodeart' ),
					'attrs' => [
						'id' 	=> 'comment-author',
						'name' 	=> 'author',
						'size' 			=> 30,
						'maxlength' 	=> 245,
						'placeholder'	=> 'John Doe',
						'value' 		=> $commenter['comment_author'],
						'required' 		=> ( $req ) ? 'required' : NULL,
					]
				] ], false );
			break;

			case 'email':
				return wecodeart( 'markup' )::wrap( 'comment-author-email', [ [
					'tag' 	=> 'div',
					'attrs' => [
						'class' => 'comment-form-field comment-form-email col-md-7'
					]
				] ], 'wecodeart_input', [ 'floating', [
					'type' 	=> 'email',
					'label' => esc_html__( 'Email *', 'wecodeart' ),
					'attrs' => [
						'id' 	=> 'comment-email',
						'name' 	=> 'email',
						'size' 			=> 30,
						'maxlength' 	=> 100,
						'placeholder'	=> 'name@example.com',
						'value' 		=> $commenter['comment_author_email'],
						'required' 		=> ( $req ) ? 'required' : NULL,
					]
				] ], false );
			break;

			case 'url':
				return wecodeart( 'markup' )::wrap( 'comment-author-url', [ [
					'tag' 	=> 'div',
					'attrs' => [
						'class' => 'comment-form-field comment-form-url col-md-7'
					]
				] ], 'wecodeart_input', [ 'floating', [
					'type' 	=> 'url',
					'label' => esc_html__( 'Website', 'wecodeart' ),
					'attrs' => [
						'id' 	=> 'comment-url',
						'name' 	=> 'url',
						'size' 		 => 30, 
						'maxlength'  => 200,
						'placeholder'=> 'www.example.com',
						'value' 	 => $commenter['comment_author_url'] ,
					]
				] ], false );
			break;

			case 'comment':
				return wecodeart( 'markup' )::wrap( 'comment-author-comment', [ [
					'tag' 	=> 'div',
					'attrs' => [
						'class' => 'comment-form-field comment-form-comment'
					]
				] ], 'wecodeart_input', [ 'floating', [
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
				] ], false );
			break;

			case 'submit':
				$markup = '';

				$markup .= '<button name="%1$s" type="submit" id="%2$s" class="%3$s">';
				$markup .= wecodeart( 'markup' )->SVG::compile( 'comment-dots' );
				$markup .= '<span>%4$s</span>';
				$markup .= '</button>';

				return apply_filters( 'wecodeart/filter/comments/submit', $markup );
			break;

			case 'privacy':
				$content = '';
				$privacy = get_option( 'wp_page_for_privacy_policy' );

				if( (bool) $privacy && get_post_status( $privacy ) === 'publish' ) {
					$permalink = sprintf(
						'<a href="%1$s">%2$s</a>',
						esc_url( get_privacy_policy_url() ),
						esc_html( get_the_title( $privacy ) )
					);
					
					$content = wecodeart( 'markup' )::wrap( 'comment-privacy', [ [
						'tag' 	=> 'div',
						'attrs' => [
							'class' => 'comment-form-field comment-form-privacy-consent'
						]
					] ], 'wecodeart_input', [ 'toggle', [
						'type'	=> 'checkbox',
						'label' => sprintf( __( 'By commenting you accept the %s.', 'wecodeart' ), $permalink ),
						'attrs' => [
							'class'		=> 'form-switch',
							'id' 		=> 'comment-privacy',
							'name' 		=> 'comment-privacy',
							'required'  => ( $req ) ? 'required' : NULL,
						]
					] ], false );
				}

				return $content;
			break;

			default:
				return '';
		}
	}

	/**
	 * Get Form Notes
	 *
	 * @since	5.2.2
	 * @since	5.3.1
	 *
	 * @return 	void
	 */
	public function get_form_notes() {
		return wecodeart( 'markup' )::wrap( 'comment-notes-before', [ [
			'tag' 	=> 'div',
			'attrs' => [
				'class' => 'comment-form-field comment-form-notes'
			]
		] ], function() {

			$string = esc_html__( 'Your email address will not be published.', 'wecodeart' );

			if( get_option( 'require_name_email' ) ) {
				$string .= ' ' . sprintf( esc_html__( 'Required fiels are marked "%s".', 'wecodeart' ), 
					'<span class="required">*</span>' 
				);
			}

			echo wp_kses_post( $string );

		}, [], false );
	}

	/**
	 * Validate Privacy field.
	 *
	 * @since	5.0.0
	 * @since	5.3.7
	 *
	 * @return 	void
	 */
	public function validate_privacy() {
		$privacy = get_option( 'wp_page_for_privacy_policy' );

		if( get_post_status( $privacy ) !== 'publish' || is_user_logged_in() ) return;

		if( ! filter_input( INPUT_POST, 'comment-privacy' ) ) {
			wp_die( sprintf( esc_html__( 'You must accept %s to comment!', 'wecodeart' ), sprintf(
				'<a href="%1$s" target="_blank">%2$s</a>',
				esc_url( get_privacy_policy_url() ),
				esc_html( get_the_title( $privacy ) )
			) ) );
		}
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public static function styles() {
		return "
		.wp-block-post-comments-form:empty {
			display: none;
		}
		.wp-block-post-comments-form__headline svg,
		.comment-form-submit svg {
			margin-right: .5rem;
		}
		.comment-form-field {
			margin-bottom: 1rem;
		}
		.comment-form-submit {
			display: inline-block;
			vertical-align: middle;
			padding: 0.5rem 0.75rem;
			color: var(--wp--white);
			font-size: 1rem;
			font-weight: 400;
			text-align: center;
			line-height: 1.5;
			background-color: var(--wp--dark);
			border: 1px solid transparent;
			border-radius: .25rem;
			transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
			user-select: none;
			cursor: pointer;
		}
		";
	}
}
