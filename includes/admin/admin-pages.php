<?php
/**
 * Admin Pages
 *
 * @package     Active_Campaign
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2015, Dylan Ryan
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the admin submenu pages under the Downloads menu and assigns their
 * links to global variables
 *
 * @since 1.0
 * @global $ac_discounts_page
 * @global $ac_payments_page
 * @global $ac_customers_page
 * @global $ac_settings_page
 * @global $ac_reports_page
 * @global $ac_add_ons_page
 * @global $ac_settings_export
 * @global $ac_upgrades_screen
 * @return void
 */
function ac_add_options_link() {
    //global $ac_discounts_page, $ac_payments_page, $ac_settings_page, $ac_reports_page, $ac_add_ons_page, $ac_settings_export, $ac_upgrades_screen, $ac_tools_page, $ac_customers_page;
    global $ac_settings_page;
    //$ac_payment            = get_post_type_object( 'ac_payment' );
    //$ac_payments_page      = add_submenu_page( 'edit.php?post_type=download', $ac_payment->labels->name, $ac_payment->labels->menu_name, 'edit_shop_payments', 'ac-payment-history', 'ac_payment_history_page' );
    $ac_settings_page      = add_menu_page( __( 'ActiveCampaign', 'ac' ), __( 'ActiveCampaign', 'ac' ), 'manage_options', 'ac-settings', 'ac_options_page', ACTIVE_CAMPAIGN_URL . 'assets/images/ac_symbol_trans_20.png' );
    //$ac_tools_page         = add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Download Info and Tools', 'ac' ), __( 'Tools', 'ac' ), 'install_plugins', 'ac-tools', 'ac_tools_page' );
    //$ac_add_ons_page       = add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Download Extensions', 'ac' ), __( 'Extensions', 'ac' ), 'install_plugins', 'ac-addons', 'ac_add_ons_page' );
    //$ac_upgrades_screen    = add_submenu_page( null, __( 'AC Upgrades', 'ac' ), __( 'AC Upgrades', 'ac' ), 'manage_shop_settings', 'ac-upgrades', 'ac_upgrades_screen' );
}
add_action( 'admin_menu', 'ac_add_options_link', 10 );