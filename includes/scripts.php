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
 * @return void
 */
function ac_load_admin_scripts() {
    global $ac_options;

    if ( ! apply_filters( 'ac_load_admin_scripts', ac_is_admin_page() ) ) {
        return;
    }

    $js_dir  = ACTIVE_CAMPAIGN_URL . 'assets/js/';
    $css_dir = ACTIVE_CAMPAIGN_URL . 'assets/css/';

    wp_enqueue_script( 'ac-admin-scripts', $js_dir . 'admin-scripts.js', array( 'jquery' ), ACTIVE_CAMPAIGN_VER, false );
    wp_enqueue_script( 'ac-admin-scripts' );

    wp_localize_script( 'ac-admin-scripts', 'ac_vars', array(
        'api_cred_test' => get_transient( md5( 'ac_options_page_' . $ac_options['api_key'] . $ac_options['api_url'] . '_0001' ) ),
        'api_lock'      => isset( $ac_options['api_lock'] ) ? $ac_options['api_lock'] : null,
        'ac_version'    => ACTIVE_CAMPAIGN_VER,
    ));

    wp_register_style( 'ac-admin', $css_dir . 'ac-admin.css', ACTIVE_CAMPAIGN_VER );
    wp_enqueue_style( 'ac-admin' );
}
add_action( 'admin_enqueue_scripts', 'ac_load_admin_scripts', 100 );