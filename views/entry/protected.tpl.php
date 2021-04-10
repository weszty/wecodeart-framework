<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Protected Template
 * @since 	    3.5
 * @version	    4.2
 */

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Markup\SVG;

SVG::add_icon( 'key', [
    'viewBox'	=> '0 0 512 512',
    'paths' 	=> 'M336 32c79.529 0 144 64.471 144 144s-64.471 144-144 144c-18.968 0-37.076-3.675-53.661-10.339L240 352h-48v64h-64v64H32v-80l170.339-170.339C195.675 213.076 192 194.968 192 176c0-79.529 64.471-144 144-144m0-32c-97.184 0-176 78.769-176 176 0 15.307 1.945 30.352 5.798 44.947L7.029 379.716A24.003 24.003 0 0 0 0 396.686V488c0 13.255 10.745 24 24 24h112c13.255 0 24-10.745 24-24v-40h40c13.255 0 24-10.745 24-24v-40h19.314c6.365 0 12.47-2.529 16.971-7.029l30.769-30.769C305.648 350.055 320.693 352 336 352c97.184 0 176-78.769 176-176C512 78.816 433.231 0 336 0zm48 108c11.028 0 20 8.972 20 20s-8.972 20-20 20-20-8.972-20-20 8.972-20 20-20m0-28c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48z'
] );

SVG::add_icon( 'unlock', [
    'viewBox'	=> '0 0 318 512',
    'paths' 	=> 'M336 256H96v-96c0-70.6 25.4-128 96-128s96 57.4 96 128v20c0 6.6 5.4 12 12 12h8c6.6 0 12-5.4 12-12v-18.5C320 73.1 280.9.3 192.5 0 104-.3 64 71.6 64 160v96H48c-26.5 0-48 21.5-48 48v160c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48V304c0-26.5-21.5-48-48-48zm16 208c0 8.8-7.2 16-16 16H48c-8.8 0-16-7.2-16-16V304c0-8.8 7.2-16 16-16h288c8.8 0 16 7.2 16 16v160z',
] );

/**
 * @param   string  $headline       Contains the translatable string
 * @param   string  $unique_id      Contains the input unique ID
 */
?>
<form action="<?php echo esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ); ?>"
    method="post" class="alert border-soft shadow-soft py-4">
    <h4><?php
    
        esc_html_e( 'This post is password protected. To view this post, enter the password below!', 'wecodeart' );
        
    ?></h4>
    <div class="input-group pt-3 pb-2"> 
        <span class="input-group-text"><?php 
        
            SVG::render( 'unlock', [
                'class' => 'fa-fw'
            ] );
            
        ?></span><?php
            
            wecodeart_input( 'password', [
                'attrs' => [
                    'id'            => wp_unique_id( 'pwbox-' ),
                    'name'          => 'post_password',
                    'size'          => 20,
                    'required'      => true,
                    'placeholder'   => esc_attr__( 'Enter password', 'wecodeart' )
                ]
            ] );

        ?><button type="submit" class="btn btn-outline-dark"><?php
            
            SVG::render( 'key', [
                'class' => 'me-2'
            ] );

            ?><span><?php
            
                esc_html_e( 'Unlock', 'wecodeart' );
                
            ?></span>
        </button> 
    </div>
</form>
		