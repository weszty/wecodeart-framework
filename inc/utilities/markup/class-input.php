<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework 
 * @subpackage 	Utilities\Markup\Input
 * @copyright   Copyright (c) 2019, WeCodeArt Framework
 * @since		3.1.2
 * @version		3.8.6
 */

namespace WeCodeArt\Utilities\Markup;

if ( ! defined( 'ABSPATH' ) ) exit(); 

/**
 * Standard Inputs Markup
 */
class Input {	
	/*
	 * Constructor 
	 */
	public function __construct( ) { }
	
	/**
	 * Render the HTML of the input
	 *
	 * @access 	public
	 * @param 	string 		$type		text/number/etc
	 * @param 	string 		$label		Label Text
	 * @param 	array 		$attrs		Input Attributes (id, name, value, etc)
	 *
	 * @return	function	input		Returns the HTML
	 */
	public static function render( $type = 'hidden', $label, $attrs = [], $choices = [], $messages = [] ) {
		self::input( $type, $label, $attrs, $choices, $messages );
	}
	
	/**
	 * Get the HTML of the input
	 *
	 * @access 	public
	 * @since	unknown
	 * @version	3.9.5
	 *
	 * @param 	string 		$type		text/number/etc
	 * @param 	array 		$args		$this->defaults/args
	 *
	 * @return	function	input		Renders the HTML
	 */
	public static function compile( $type = 'hidden', $label, $attrs = [], $choices = [], $messages = [] ) {
		ob_start();
		self::input( $type, $label, $attrs, $choices, $messages );
		return preg_replace( '/(\>)\s*(\<)/m', '$1$2', ob_get_clean() );
	}
	
	/**
	 * Create HTML Inputs
	 *
	 * @since	unknown
	 * @version	3.8.6
	 *
	 * @param  	array	$array	Existing option array if exists (optional)
	 *
	 * @return 	array	$array	Array of options, all standard DOM input options
	 */
	protected static function input( $type, $label, $attrs, $choices, $messages ) {
		// Add default ID
		$attrs = wp_parse_args( $attrs, [
			'type'  => $type,
			'class' => 'form-control',
			'name'	=> isset( $attrs['name'] ) ? $attrs['name'] : uniqid( 'input-' ),
			'id' 	=> isset( $attrs['id'] ) ? $attrs['id'] : isset( $attrs['name'] ) ? $attrs['name'] : uniqid( 'input-' ),
		] );

		// Label.
		$label = is_string( $label ) ? [ 'text' => $label ] : $label;

		// Switch our input type.
		switch( $type ) {
			/**
			 * Text/Number/Email/URL/Password/Range/Submit share same HTML
			 */
			case 'url' : 
			case 'text' : 
			case 'email' : 
			case 'range' : 
			case 'number' : 
			case 'submit' :   
			case 'password' :
				if ( isset( $label['text'] ) ) {
					printf( '<label for="%s">%s</label>', esc_attr( $attrs['id'] ), esc_html( $label['text'] ) );
				}
			?>
				<input <?php echo \WeCodeArt\Utilities\Markup::generate_attr( $type . '-input', $attrs ); ?>/>
			<?php if( ! empty( $messages ) ) self::messages( $messages ); ?>
			<?php break;

			/**
			 * Checkbox / File / Switch
			 */
			case 'checkbox' :
			case 'checkbox-switch' :
				$class = ( $type === 'checkbox-switch' ) ? 'custom-switch' : 'custom-checkbox';
				$classes = array_merge( [ 'custom-control', $class, $attrs['class'] ] );
				$attrs = wp_parse_args( [
					'type'	=> 'checkbox',
					'class' => 'custom-control-input',
				], $attrs ); 
			?>
			<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<input <?php echo \WeCodeArt\Utilities\Markup::generate_attr( $type . '-input', $attrs ); ?>/>
			<?php
				if ( isset( $label['text'] ) ) {
					printf( '<label class="custom-control-label" for="%s">%s</label>', 
						esc_attr( $attrs['id'] ), wp_kses_post( $label['text'] ) 
					);
				}
				if( ! empty( $messages ) ) self::messages( $messages );
			?>
			</div>
			<?php break;
			
			/**
			 * Textarea
			 */
			case 'textarea' :
				unset( $attrs['type'] );
				if ( isset( $label['text'] ) ) {
					printf( '<label for="%s">%s</label>', esc_attr( $attrs['id'] ), esc_html( $label['text'] ) );
				}
			?>
				<textarea <?php echo \WeCodeArt\Utilities\Markup::generate_attr( 'textarea', $attrs ); ?>></textarea>
				<?php if( ! empty( $messages ) ) self::messages( $messages ); ?>
			<?php break;
			
			/**
			 * Select Input
			 */
			case 'select' :
				if ( isset( $label['text'] ) ) {
					printf( '<label for="%s">%s</label>', esc_attr( $attrs['id'] ), esc_html( $label['text'] ) );
				}
				$placeholder = isset( $attrs['placeholder'] ) ? $attrs['placeholder'] : false;
				unset( $attrs['type'] );
				unset( $attrs['placeholder'] );
			?>
				<select <?php echo \WeCodeArt\Utilities\Markup::generate_attr( 'select', $attrs ); ?>>
					<?php if( $placeholder ) { ?>
						<option disabled<?php if( ! isset( $attrs['value'] ) ) : ?> selected<?php endif; ?>><?php 
							echo esc_html( $placeholder ); 
						?></option>
					<?php } ?>
					<?php foreach( $choices as $value => $label ) {
						$option = [
							'value'		=> $value,
							'selected'	=> isset( $attrs['value'] ) ? (string) $attrs['value'] === (string) $value : null
						];
						?>
						<option <?php echo \WeCodeArt\Utilities\Markup::generate_attr( 'select-option', $option ); ?>><?php 
							echo esc_html( $label );
						?></option>
					<?php } ?>
				</select>
				<?php if( ! empty( $messages ) ) self::messages( $messages ); ?>
			<?php break;

			/**
			 * Radio Buttons/Checkbox Group
			 */
			case 'radio' :
			case 'checkbox-group' :
				$ctx = ( $type === 'checkbox-group' ) ? 'checkbox' : $type;

				$classes = explode( ' ', $attrs['class'] );
				if ( ( $key = array_search( 'form-control', $classes ) ) !== false ) unset( $classes[$key] );
				$classes = array_merge( [ 'form-group', 'fieldset', 'fieldset--' . $ctx, 'text-left' ], $classes );
				$fieldset = [ 'class' => implode( ' ', $classes ) ];
				?>
				<fieldset <?php echo \WeCodeArt\Utilities\Markup::generate_attr( 'fieldset-' . $ctx, $fieldset ); ?>>
				<?php if ( isset( $label['text'] ) ) {
					printf( '<legend>%s</legend>', esc_html( $label['text'] ) ); 
				}
				foreach ( (array) $choices as $key => $label ) {
					$wrap_args = [ 'class' => 'form-check custom-control custom-' . $ctx ];
					$label = is_string( $label ) ? [ 'text' => $label ] : $label;
				?>
					<div <?php echo \WeCodeArt\Utilities\Markup::generate_attr( 'fieldset-' . $ctx . '-wrap', $wrap_args ); ?>>
				<?php
					$input_args = [
						'id'		=> $key,
						'type' 		=> $ctx,
						'value'		=> $key,
						'class'		=> 'form-check-input custom-control-input',
						'name' 		=> $ctx === 'checkbox' ? $attrs['name'] . '[' . $key . ']' : $attrs['name'],
						'checked'	=> isset( $attrs['value'] ) ? (string) $attrs['value'] === (string) $key : null,
						'disabled'	=> isset( $label['disabled'] ) && (bool) $label['disabled'] === true ? true : null
					];

					$input_attrs = \WeCodeArt\Utilities\Markup::generate_attr( 'fieldset-' . $ctx . '-input', $input_args );
				?>
					<input <?php echo $input_attrs; // WPCS OK - Escaped in function above. ?>/>
					<?php 
						printf( '<label class="form-check-label custom-control-label" for="%s">%s</label>', 
							esc_attr( $input_args['id'] ), esc_html( $label['text'] ) 
						); ?>
					</div>
				<?php } ?>
				<?php if( ! empty( $messages ) ) self::messages( $messages ); ?>
				</fieldset> 
			<?php break;

			/**
			 * Default/Other Value goes to hidden field
			 */
			default : ?>
				<input <?php echo \WeCodeArt\Utilities\Markup::generate_attr( 'hidden-input', $attrs ); ?>/>
			<?php
		}
	}

	/**
	 * Render the messages HTML of the input
	 *
	 * @access 	protected
	 * @param 	array		$messages	Input messages (id, name, value, etc)
	 *
	 * @return	string
	 */
	protected static function messages( $messages = [], $echo = true ) {
		if( ! $messages ) return;

		$html = '';

		$help = isset( $messages['help'] ) ? $messages['help'] : false;
		if( isset( $messages['help'] ) ) unset( $messages['help'] );

		if( is_string( $help ) ) {
			$html .= sprintf( '<small class="help-text">%s</small>', $help );
		}

		if( $messages ) {
			foreach( $messages as $key => $msg ) {
				$data 		= is_string( $msg ) ? [ 'text' => $msg ] : $msg;
				$message 	= isset( $data['text'] ) ? $data['text'] : '';
				$class		= isset( $data['class'] ) ? $data['class'] : $key . '-tooltip';
				$html .= sprintf( '<div class="%s">%s</div>', esc_attr( $class ), $message );
			}
		}

		if( $echo ) {
			echo $html;
			return;
		}

		return $html;
	}
}