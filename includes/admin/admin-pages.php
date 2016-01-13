<?php
/**
 * Admin Pages
 *
 * @package     Active_Campaign
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2015, Dylan Ryan
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the admin submenu pages under the Downloads menu and assigns their
 * links to global variables
 *
 * @since 1.0
 * @global $ac_settings_page
 * @return void
 */
function ac_add_options_link() {
    global $ac_settings_page;
    $ac_settings_page      = add_menu_page( __( 'ActiveCampaign', 'ac' ), __( 'ActiveCampaign', 'ac' ), 'manage_options', 'ac-settings', 'ac_options_page', ACTIVE_CAMPAIGN_URL . 'assets/images/ac_symbol_trans_20.png' );
}
add_action( 'admin_menu', 'ac_add_options_link', 10 );

/**
 *  Determines whether the current admin page is a specific Active_Campaign admin page.
 *
 *  Only works after the `wp_loaded` hook, & most effective
 *  starting on `admin_menu` hook. Failure to pass in $view will match all views of $main_page.
 *  Failure to pass in $main_page will return true if on any Active_Campaign page
 *
 * @since 1.9.6
 *
 * @return bool True if Active_Campaign admin page we're looking for or an Active_Campaign page or if $page is empty,
 * any Active_Campaign page
 */
function ac_is_admin_page() {
    global $pagenow, $ac_settings_page, $ac_add_ons_page;

    $found      = false;
    $page       = isset( $_GET['page'] )       ? strtolower( $_GET['page'] )       : false;
    $view       = isset( $_GET['view'] )       ? strtolower( $_GET['view'] )       : false;

    $admin_pages = apply_filters( 'ac_admin_pages', array( $ac_settings_page, $ac_add_ons_page ) );
    if ( $pagenow == 'admin.php' && 'ac-settings' === $page ) {
        $found = true;
    }
    if ( in_array( $pagenow, $admin_pages ) ) {
        $found = true;
    }
    return (bool) apply_filters( 'ac_is_admin_page', $found, $page, $view );
}