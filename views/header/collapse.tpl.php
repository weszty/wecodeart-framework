<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage  Header Collapse
 * @since	 	4.2.0
 * @version    	4.2.0
 */

defined( 'ABSPATH' ) || exit();

// Menu
echo wp_nav_menu( [
    'theme_location'    => 'primary',
    'menu_class' 	    => 'menu navbar-nav nav',
    'container_class'   => 'ms-auto me-lg-3 mb-2 mb-lg-0',
    'depth' 		    => 2,
] );

// Form
echo get_search_form();

?>