<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage  OffCanvas
 * @since	 	5.0.0
 * @version    	5.0.0
 */

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Markup;

?>
<div class="offcanvas-header py-3">
	<h5 class="offcanvas-title"><?php esc_html_e( 'Menu', 'wecodeart' ); ?></h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
<div class="offcanvas-body"><?php

	// Form
	echo get_search_form();

	// Menu
	echo wp_nav_menu( [
		'theme_location'    => 'primary',
		'menu_class' 	    => 'menu navbar-nav nav',
		'container_class'   => 'my-3',
		'depth' 		    => 2,
	] );

?></div>