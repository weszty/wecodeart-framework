<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Entry\Meta\Comments
 * @copyright   Copyright (c) 2019, WeCodeArt Framework
 * @since 		3.9.5
 * @version		3.9.6
 */

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Utilities\Helpers;
use WeCodeArt\Utilities\Markup\SVG;

/**
 * @param   array   $i18n   Contains the translatable strings
 * @param   int     $number Contains the comments number
 */
$classnames = [ 'entry-comments' ];

if( 0 === $number ) {
    $classnames[] = 'entry-comments--none';
} elseif ( 1 === $number ) {
    $classnames[] = 'entry-comments--one';
} elseif ( 1 < $number ) {
    $classnames[] = 'entry-comments--multiple';
}

if( post_password_required() ) {
    $classnames[] = 'entry-comments--protected';
}
?>
<span class="<?php echo esc_attr( implode( ' ', $classnames ) ); ?>">
    <span class="d-inline-block mr-1"><?php
    
        echo Helpers::kses_svg( SVG::compile( 'comments' ) );
    
    ?></span><?php
    
        comments_popup_link( 
            $i18n['zero'], 
            $i18n['one'], 
            $i18n['more'], 
            'entry-comments__link', 
            $i18n['closed'] 
        );

?></span>