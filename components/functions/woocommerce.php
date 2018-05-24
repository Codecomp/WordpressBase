<?php

/* ===================================================================
	Global Updates
   ==================================================================*/

// Declare WooCommerce Support
add_action('after_setup_theme', 'woocommerce_support');

/**
 * Declare WooCommerce Support
 */
function woocommerce_support()
{
    add_theme_support('woocommerce');
}
