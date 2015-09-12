<?php
/**
 * Scripts
 *
 * @package     Active_Campaign
 * @subpackage  Functions
 * @copyright   Copyright (c) 2015, Dylan Ryan
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Load Admin Scripts
 *
 * Enqueues the required admin scripts.
 *
 * @since 1.0
 * @global $post
 * @param string $hook Page hook
 * @return void
 */
function ac_load_admin_scripts( $hook ) {

    $css_dir = ACTIVE_CAMPAIGN_URL . 'assets/css/';

    wp_register_style( 'ac-admin', $css_dir . 'ac-admin.css', ACTIVE_CAMPAIGN_VER );
    wp_enqueue_style( 'ac-admin' );
}
add_action( 'admin_enqueue_scripts', 'ac_load_admin_scripts', 100 );