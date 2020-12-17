<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Entry\Meta\More
 * @copyright   Copyright (c) 2020, WeCodeArt Framework
 * @since 		3.9.5
 * @version		4.2.0
 */

defined( 'ABSPATH' ) || exit();

/**
 * @param   string  $permalink  Post Link
 * @param   string  $title      Post Title
 */
?>
<a href="<?php echo esc_url( $permalink ); ?>" class="entry-more btn btn-outline-dark">
    <span><?php esc_html_e( 'Read More', 'wecodeart' ); ?></span>
    <span aria-hidden="true">&#xbb;</span>
    <span class="screen-reader-text"><?php echo esc_html( $title ); ?></span>
</a>