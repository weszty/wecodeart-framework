<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework 
 * @subpackage 	Markup\Inputs
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		5.0.0
 * @version		5.3.3
 */

namespace WeCodeArt\Support\Markup\Inputs;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Support\Markup\Inputs\Basic;
use function WeCodeArt\Functions\get_prop;

/**
 * Standard Inputs Markup
 */
class Button extends Basic {

    /**
     * Input's Type / Label Position.
     *
     * @since   5.0.0
     * @var     string
     */
    public $type    = 'submit';
    public $_label  = 'none';

    /**
	 * Constructor 
	 */
	public function __construct( string $type = 'button', array $args = [] ) {
        $this->label    = get_prop( $args, 'label', '' );
        $this->attrs    = get_prop( $args, 'attrs', [] );
    }
	
	/**
	 * Create HTML Inputs
	 *
	 * @since	unknown
	 * @version	5.0.0
	 */
	public function content() {
        ?>
        <button <?php $this->input_attrs(); ?>><?php
        
            // Unfiltered to allow HTML (in the theme we are not passing unsafe data, so yes, is safe)
            echo $this->label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        
        ?></button>
        <?php
    }
}