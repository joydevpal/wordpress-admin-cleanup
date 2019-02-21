<?php

/**
 * Remove left admin footer text ("Thank you for creating with WordPress")
 * @link https://developer.wordpress.org/reference/hooks/admin_footer_text/
 */
add_filter( 'admin_footer_text', '__return_empty_string' );


/**
 * Remove right admin footer text (where the WordPress version number is)
 * @link https://developer.wordpress.org/reference/hooks/update_footer/
 */
add_filter( 'update_footer', '__return_empty_string', 11 );
