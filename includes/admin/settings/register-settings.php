<?php
/**
 * Register Settings
 *
 * @package     Active_Campaign
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2015, Dylan Ryan
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since 1.8.4
 * @return mixed
 */
function ac_get_option( $key = '', $default = false ) {
    global $ac_options;
    $value = ! empty( $ac_options[ $key ] ) ? $ac_options[ $key ] : $default;
    $value = apply_filters( 'ac_get_option', $value, $key, $default );
    return apply_filters( 'ac_get_option_' . $key, $value, $key, $default );
}
/**
 * Update an option
 *
 * Updates an ac setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *          the key from the ac_options array.
 *
 * @since 2.3
 * @param string $key The Key to update
 * @param string|bool|int $value The value to set the key to
 * @return boolean True if updated, false if not.
 */
function ac_update_option( $key = '', $value = false ) {
    // If no key, exit
    if ( empty( $key ) ){
        return false;
    }
    if ( empty( $value ) ) {
        $remove_option = ac_delete_option( $key );
        return $remove_option;
    }
    // First let's grab the current settings
    $options = get_option( 'ac_settings' );
    // Let's let devs alter that value coming in
    $value = apply_filters( 'ac_update_option', $value, $key );
    // Next let's try to update the value
    $options[ $key ] = $value;
    $did_update = update_option( 'ac_settings', $options );
    // If it updated, let's update the global variable
    if ( $did_update ){
        global $ac_options;
        $ac_options[ $key ] = $value;
    }
    return $did_update;
}
/**
 * Remove an option
 *
 * Removes an ac setting value in both the db and the global variable.
 *
 * @since 2.3
 * @param string $key The Key to delete
 * @return boolean True if updated, false if not.
 */
function ac_delete_option( $key = '' ) {
    // If no key, exit
    if ( empty( $key ) ){
        return false;
    }
    // First let's grab the current settings
    $options = get_option( 'ac_settings' );
    // Next let's try to update the value
    if( isset( $options[ $key ] ) ) {
        unset( $options[ $key ] );
    }
    $did_update = update_option( 'ac_settings', $options );
    // If it updated, let's update the global variable
    if ( $did_update ){
        global $ac_options;
        $ac_options = $options;
    }
    return $did_update;
}
/**
 * Get Settings
 *
 * Retrieves all plugin settings
 *
 * @since 1.0
 * @return array EDD settings
 */
function ac_get_settings() {
    $settings = get_option( 'ac_settings' );
    if( empty( $settings ) ) {
        // Update old settings with new single option
        $general_settings = is_array( get_option( 'ac_settings_general' ) )    ? get_option( 'ac_settings_general' )    : array();
        $gateway_settings = is_array( get_option( 'ac_settings_gateways' ) )   ? get_option( 'ac_settings_gateways' )   : array();
        $email_settings   = is_array( get_option( 'ac_settings_emails' ) )     ? get_option( 'ac_settings_emails' )     : array();
        $style_settings   = is_array( get_option( 'ac_settings_styles' ) )     ? get_option( 'ac_settings_styles' )     : array();
        $tax_settings     = is_array( get_option( 'ac_settings_taxes' ) )      ? get_option( 'ac_settings_taxes' )      : array();
        $ext_settings     = is_array( get_option( 'ac_settings_extensions' ) ) ? get_option( 'ac_settings_extensions' ) : array();
        $license_settings = is_array( get_option( 'ac_settings_licenses' ) )   ? get_option( 'ac_settings_licenses' )   : array();
        $misc_settings    = is_array( get_option( 'ac_settings_misc' ) )       ? get_option( 'ac_settings_misc' )       : array();
        $settings = array_merge( $general_settings, $gateway_settings, $email_settings, $style_settings, $tax_settings, $ext_settings, $license_settings, $misc_settings );
        update_option( 'ac_settings', $settings );
    }
    return apply_filters( 'ac_get_settings', $settings );
}
/**
 * Add all settings sections and fields
 *
 * @since 1.0
 * @return void
 */
function ac_register_settings() {
    if ( false == get_option( 'ac_settings' ) ) {
        add_option( 'ac_settings' );
    }
    foreach( ac_get_registered_settings() as $tab => $settings ) {
        add_settings_section(
            'ac_settings_' . $tab,
            __return_null(),
            '__return_false',
            'ac_settings_' . $tab
        );
        foreach ( $settings as $option ) {
            $name = isset( $option['name'] ) ? $option['name'] : '';
            add_settings_field(
                'ac_settings[' . $option['id'] . ']',
                $name,
                function_exists( 'ac_' . $option['type'] . '_callback' ) ? 'ac_' . $option['type'] . '_callback' : 'ac_missing_callback',
                'ac_settings_' . $tab,
                'ac_settings_' . $tab,
                array(
                    'section'     => $tab,
                    'id'          => isset( $option['id'] )          ? $option['id']          : null,
                    'desc'        => ! empty( $option['desc'] )      ? $option['desc']        : '',
                    'name'        => isset( $option['name'] )        ? $option['name']        : null,
                    'size'        => isset( $option['size'] )        ? $option['size']        : null,
                    'options'     => isset( $option['options'] )     ? $option['options']     : '',
                    'std'         => isset( $option['std'] )         ? $option['std']         : '',
                    'min'         => isset( $option['min'] )         ? $option['min']         : null,
                    'max'         => isset( $option['max'] )         ? $option['max']         : null,
                    'step'        => isset( $option['step'] )        ? $option['step']        : null,
                    'chosen'      => isset( $option['chosen'] )      ? $option['chosen']      : null,
                    'placeholder' => isset( $option['placeholder'] ) ? $option['placeholder'] : null,
                    'allow_blank' => isset( $option['allow_blank'] ) ? $option['allow_blank'] : true,
                    'readonly'    => isset( $option['readonly'] )    ? $option['readonly']    : false,
                    'faux'        => isset( $option['faux'] )        ? $option['faux']        : false,
                )
            );
        }
    }
    // Creates our settings in the options table
    register_setting( 'ac_settings', 'ac_settings', 'ac_settings_sanitize' );
}
add_action('admin_init', 'ac_register_settings');
/**
 * Retrieve the array of plugin settings
 *
 * @since 1.8
 * @return array
 */
function ac_get_registered_settings() {
    /**
     * 'Whitelisted' EDD settings, filters are provided for each settings
     * section to allow extensions and other plugins to add their own settings
     */
    $ac_settings = array(
        /** General Settings */
        'general' => apply_filters( 'ac_settings_general',
            array(
                'api_settings' => array(
                    'id' => 'api_settings',
                    'name' => '<strong>' . __( 'API Settings', 'ac' ) . '</strong>',
                    'desc' => '',
                    'type' => 'header'
                ),
                'api_url' => array(
                    'id'   => 'api_url',
                    'name' => __( 'API URL', 'ac' ),
                    'desc' => __( 'This is the API URL from your ActiveCampaign settings.', 'ac' ),
                    'type' => 'text',
                    'size' => 'regular',
                    'std'  => ''
                ),
                'api_key' => array(
                    'id'   => 'api_key',
                    'name' => __( 'API Key', 'ac' ),
                    'desc' => __( 'This is the API Key from your ActiveCampaign settings.', 'ac' ),
                    'type' => 'password',
                    'size' => 'regular',
                    'std'  => ''
                ),
                'branding_settings' => array(
                    'id' => 'branding_settings',
                    'name' => '<strong>' . __( 'Branding Settings', 'ac' ) . '</strong>',
                    'desc' => '',
                    'type' => 'header'
                ),
                'branding_enabled' => array(
                    'id'   => 'branding_enabled',
                    'name' => __( 'Enable Branding', 'ac' ),
                    'desc' => __( 'Check this box to turn on ActiveCampaign branding.', 'ac' ),
                    'type' => 'checkbox'
                ),
                'list_restriction_settings' => array(
                    'id' => 'list_restriction_settings',
                    'name' => '<strong>' . __( 'Restriction Settings', 'ac' ) . '</strong>',
                    'desc' => '',
                    'type' => 'header'
                ),
                'list_restriction_error_message' => array(
                    'id'   => 'list_restriction_error_message',
                    'name' => __( 'Invalid Access Message', 'ac' ),
                    'desc' => __( 'This is the error message shown to users without access.', 'ac' ),
                    'type' => 'rich_editor',
                    //'size' => 'regular',
                    'std'  => 'Sorry, you don\'t have access to view this. Did you forget to login?'
                )
                /*'purchase_page' => array(
                    'id' => 'purchase_page',
                    'name' => __( 'Checkout Page', 'ac' ),
                    'desc' => __( 'This is the checkout page where buyers will complete their purchases. The [download_checkout] short code must be on this page.', 'ac' ),
                    'type' => 'select',
                    'options' => ac_get_pages(),
                    'chosen' => true,
                    'placeholder' => __( 'Select a page', 'ac' )
                ),
                'success_page' => array(
                    'id' => 'success_page',
                    'name' => __( 'Success Page', 'ac' ),
                    'desc' => __( 'This is the page buyers are sent to after completing their purchases. The [ac_receipt] short code should be on this page.', 'ac' ),
                    'type' => 'select',
                    'options' => ac_get_pages(),
                    'chosen' => true,
                    'placeholder' => __( 'Select a page', 'ac' )
                ),
                'failure_page' => array(
                    'id' => 'failure_page',
                    'name' => __( 'Failed Transaction Page', 'ac' ),
                    'desc' => __( 'This is the page buyers are sent to if their transaction is cancelled or fails', 'ac' ),
                    'type' => 'select',
                    'options' => ac_get_pages(),
                    'chosen' => true,
                    'placeholder' => __( 'Select a page', 'ac' )
                ),
                'purchase_history_page' => array(
                    'id' => 'purchase_history_page',
                    'name' => __( 'Purchase History Page', 'ac' ),
                    'desc' => __( 'This page shows a complete purchase history for the current user, including download links', 'ac' ),
                    'type' => 'select',
                    'options' => ac_get_pages(),
                    'chosen' => true,
                    'placeholder' => __( 'Select a page', 'ac' )
                ),
                'base_country' => array(
                    'id' => 'base_country',
                    'name' => __( 'Base Country', 'ac' ),
                    'desc' => __( 'Where does your store operate from?', 'ac' ),
                    'type' => 'select',
                    'options' => ac_get_country_list(),
                    'chosen' => true,
                    'placeholder' => __( 'Select a country', 'ac' )
                ),
                'base_state' => array(
                    'id' => 'base_state',
                    'name' => __( 'Base State / Province', 'ac' ),
                    'desc' => __( 'What state / province does your store operate from?', 'ac' ),
                    'type' => 'shop_states',
                    'chosen' => true,
                    'placeholder' => __( 'Select a state', 'ac' )
                ),
                'currency_settings' => array(
                    'id' => 'currency_settings',
                    'name' => '<strong>' . __( 'Currency Settings', 'ac' ) . '</strong>',
                    'desc' => __( 'Configure the currency options', 'ac' ),
                    'type' => 'header'
                ),
                'currency' => array(
                    'id' => 'currency',
                    'name' => __( 'Currency', 'ac' ),
                    'desc' => __( 'Choose your currency. Note that some payment gateways have currency restrictions.', 'ac' ),
                    'type' => 'select',
                    'options' => ac_get_currencies(),
                    'chosen' => true
                ),
                'currency_position' => array(
                    'id'      => 'currency_position',
                    'name'    => __( 'Currency Position', 'ac' ),
                    'desc'    => __( 'Choose the location of the currency sign.', 'ac' ),
                    'type'    => 'select',
                    'options' => array(
                        'before' => __( 'Before - $10', 'ac' ),
                        'after'  => __( 'After - 10$', 'ac' )
                    )
                ),
                'thousands_separator' => array(
                    'id'   => 'thousands_separator',
                    'name' => __( 'Thousands Separator', 'ac' ),
                    'desc' => __( 'The symbol (usually , or .) to separate thousands', 'ac' ),
                    'type' => 'text',
                    'size' => 'small',
                    'std'  => ','
                ),
                'decimal_separator' => array(
                    'id'   => 'decimal_separator',
                    'name' => __( 'Decimal Separator', 'ac' ),
                    'desc' => __( 'The symbol (usually , or .) to separate decimal points', 'ac' ),
                    'type' => 'text',
                    'size' => 'small',
                    'std'  => '.'
                ),
                'api_settings' => array(
                    'id' => 'api_settings',
                    'name' => '<strong>' . __( 'API Settings', 'ac' ) . '</strong>',
                    'desc' => '',
                    'type' => 'header'
                ),
                'api_allow_user_keys' => array(
                    'id'   => 'api_allow_user_keys',
                    'name' => __( 'Allow User Keys', 'ac' ),
                    'desc' => __( 'Check this box to allow all users to generate API keys. Users with the \'manage_shop_settings\' capability are always allowed to generate keys.', 'ac' ),
                    'type' => 'checkbox'
                ),
                'tracking_settings' => array(
                    'id' => 'tracking_settings',
                    'name' => '<strong>' . __( 'Tracking Settings', 'ac' ) . '</strong>',
                    'desc' => '',
                    'type' => 'header'
                ),
                'allow_tracking' => array(
                    'id'   => 'allow_tracking',
                    'name' => __( 'Allow Usage Tracking?', 'ac' ),
                    'desc' => sprintf(
                        __( 'Allow Easy Digital Downloads to anonymously track how this plugin is used and help us make the plugin better. Opt-in to tracking and our newsletter and immediately be emailed a 20%% discount to the EDD shop, valid towards the <a href="%s" target="_blank">purchase of extensions</a>. No sensitive data is tracked.', 'ac' ),
                        'https://easydigitaldownloads.com/extensions?utm_source=' . substr( md5( get_bloginfo( 'name' ) ), 0, 10 ) . '&utm_medium=admin&utm_term=settings&utm_campaign=EDDUsageTracking'
                    ),
                    'type' => 'checkbox'
                ),
                'uninstall_on_delete' => array(
                    'id'   => 'uninstall_on_delete',
                    'name' => __( 'Remove Data on Uninstall?', 'ac' ),
                    'desc' => __( 'Check this box if you would like EDD to completely remove all of its data when the plugin is deleted.', 'ac' ),
                    'type' => 'checkbox'
                )*/
            )
        ),
        /** Payment Gateways Settings */
        'contacts' => apply_filters('ac_settings_contacts',
            array(
                'contact_settings' => array(
                    'id' => 'contact_settings',
                    'name' => '<strong>' . __( 'Contact Settings', 'ac' ) . '</strong>',
                    'desc' => '',
                    'type' => 'header'
                ),
                'contact_sync' => array(
                    'id'   => 'contact_sync',
                    'name' => __( 'Contact Sync', 'ac' ),
                    'desc' => '',
                    'type' => 'hook'
                ),
                /*'test_mode' => array(
                    'id' => 'test_mode',
                    'name' => __( 'Test Mode', 'ac' ),
                    'desc' => __( 'While in test mode no live transactions are processed. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.', 'ac' ),
                    'type' => 'checkbox'
                ),
                'gateways' => array(
                    'id'      => 'gateways',
                    'name'    => __( 'Payment Gateways', 'ac' ),
                    'desc'    => __( 'Choose the payment gateways you want to enable.', 'ac' ),
                    'type'    => 'gateways',
                    'options' => ac_get_payment_gateways()
                ),
                'default_gateway' => array(
                    'id'      => 'default_gateway',
                    'name'    => __( 'Default Gateway', 'ac' ),
                    'desc'    => __( 'This gateway will be loaded automatically with the checkout page.', 'ac' ),
                    'type'    => 'gateway_select',
                    'options' => ac_get_payment_gateways()
                ),
                'accepted_cards' => array(
                    'id'      => 'accepted_cards',
                    'name'    => __( 'Accepted Payment Method Icons', 'ac' ),
                    'desc'    => __( 'Display icons for the selected payment methods', 'ac' ) . '<br/>' . __( 'You will also need to configure your gateway settings if you are accepting credit cards', 'ac' ),
                    'type'    => 'payment_icons',
                    'options' => apply_filters('ac_accepted_payment_icons', array(
                            'mastercard'      => 'Mastercard',
                            'visa'            => 'Visa',
                            'americanexpress' => 'American Express',
                            'discover'        => 'Discover',
                            'paypal'          => 'PayPal',
                        )
                    )
                ),
                'paypal' => array(
                    'id' => 'paypal',
                    'name' => '<strong>' . __( 'PayPal Settings', 'ac' ) . '</strong>',
                    'desc' => __( 'Configure the PayPal settings', 'ac' ),
                    'type' => 'header'
                ),
                'paypal_email' => array(
                    'id'   => 'paypal_email',
                    'name' => __( 'PayPal Email', 'ac' ),
                    'desc' => __( 'Enter your PayPal account\'s email', 'ac' ),
                    'type' => 'text',
                    'size' => 'regular'
                ),
                'paypal_page_style' => array(
                    'id'   => 'paypal_page_style',
                    'name' => __( 'PayPal Page Style', 'ac' ),
                    'desc' => __( 'Enter the name of the page style to use, or leave blank for default', 'ac' ),
                    'type' => 'text',
                    'size' => 'regular'
                ),
                'disable_paypal_verification' => array(
                    'id'   => 'disable_paypal_verification',
                    'name' => __( 'Disable PayPal IPN Verification', 'ac' ),
                    'desc' => __( 'If payments are not getting marked as complete, then check this box. This forces the site to use a slightly less secure method of verifying purchases.', 'ac' ),
                    'type' => 'checkbox'
                ),*/
            )
        ),
        /** Emails Settings */
        'campaigns' => apply_filters('ac_settings_campaigns',
            array(
                /*'email_template' => array(
                    'id'      => 'email_template',
                    'name'    => __( 'Email Template', 'ac' ),
                    'desc'    => __( 'Choose a template. Click "Save Changes" then "Preview Purchase Receipt" to see the new template.', 'ac' ),
                    'type'    => 'select',
                    'options' => ac_get_email_templates()
                ),
                'email_logo' => array(
                    'id'   => 'email_logo',
                    'name' => __( 'Logo', 'ac' ),
                    'desc' => __( 'Upload or choose a logo to be displayed at the top of the purchase receipt emails. Displayed on HTML emails only.', 'ac' ),
                    'type' => 'upload'
                ),
                'email_settings' => array(
                    'id'   => 'email_settings',
                    'name' => '',
                    'desc' => '',
                    'type' => 'hook'
                ),
                'from_name' => array(
                    'id'   => 'from_name',
                    'name' => __( 'From Name', 'ac' ),
                    'desc' => __( 'The name purchase receipts are said to come from. This should probably be your site or shop name.', 'ac' ),
                    'type' => 'text',
                    'std'  => get_bloginfo( 'name' )
                ),
                'from_email' => array(
                    'id'   => 'from_email',
                    'name' => __( 'From Email', 'ac' ),
                    'desc' => __( 'Email to send purchase receipts from. This will act as the "from" and "reply-to" address.', 'ac' ),
                    'type' => 'text',
                    'std'  => get_bloginfo( 'admin_email' )
                ),
                'purchase_subject' => array(
                    'id'   => 'purchase_subject',
                    'name' => __( 'Purchase Email Subject', 'ac' ),
                    'desc' => __( 'Enter the subject line for the purchase receipt email', 'ac' ),
                    'type' => 'text',
                    'std'  => __( 'Purchase Receipt', 'ac' )
                ),
                'purchase_heading' => array(
                    'id'   => 'purchase_heading',
                    'name' => __( 'Purchase Email Heading', 'ac' ),
                    'desc' => __( 'Enter the heading for the purchase receipt email', 'ac' ),
                    'type' => 'text',
                    'std'  => __( 'Purchase Receipt', 'ac' )
                ),
                'purchase_receipt' => array(
                    'id'   => 'purchase_receipt',
                    'name' => __( 'Purchase Receipt', 'ac' ),
                    'desc' => __('Enter the email that is sent to users after completing a successful purchase. HTML is accepted. Available template tags:', 'ac') . '<br/>' . ac_get_emails_tags_list(),
                    'type' => 'rich_editor',
                    'std'  => __( "Dear", "ac" ) . " {name},\n\n" . __( "Thank you for your purchase. Please click on the link(s) below to download your files.", "ac" ) . "\n\n{download_list}\n\n{sitename}"
                ),
                'sale_notification_header' => array(
                    'id' => 'sale_notification_header',
                    'name' => '<strong>' . __('New Sale Notifications', 'ac') . '</strong>',
                    'desc' => __('Configure new sale notification emails', 'ac'),
                    'type' => 'header'
                ),
                'sale_notification_subject' => array(
                    'id'   => 'sale_notification_subject',
                    'name' => __( 'Sale Notification Subject', 'ac' ),
                    'desc' => __( 'Enter the subject line for the sale notification email', 'ac' ),
                    'type' => 'text',
                    'std'  => 'New download purchase - Order #{payment_id}'
                ),
                'sale_notification' => array(
                    'id'   => 'sale_notification',
                    'name' => __( 'Sale Notification', 'ac' ),
                    'desc' => __( 'Enter the email that is sent to sale notification emails after completion of a purchase. HTML is accepted. Available template tags:', 'ac' ) . '<br/>' . ac_get_emails_tags_list(),
                    'type' => 'rich_editor',
                    'std'  => ac_get_default_sale_notification_email()
                ),
                'admin_notice_emails' => array(
                    'id'   => 'admin_notice_emails',
                    'name' => __( 'Sale Notification Emails', 'ac' ),
                    'desc' => __( 'Enter the email address(es) that should receive a notification anytime a sale is made, one per line', 'ac' ),
                    'type' => 'textarea',
                    'std'  => get_bloginfo( 'admin_email' )
                ),
                'disable_admin_notices' => array(
                    'id'   => 'disable_admin_notices',
                    'name' => __( 'Disable Admin Notifications', 'ac' ),
                    'desc' => __( 'Check this box if you do not want to receive emails when new sales are made.', 'ac' ),
                    'type' => 'checkbox'
                )*/
            )
        ),
        /** Styles Settings */
        'deals' => apply_filters('ac_settings_deals',
            array(
                /*'disable_styles' => array(
                    'id'   => 'disable_styles',
                    'name' => __( 'Disable Styles', 'ac' ),
                    'desc' => __( 'Check this to disable all included styling of buttons, checkout fields, and all other elements.', 'ac' ),
                    'type' => 'checkbox'
                ),
                'button_header' => array(
                    'id' => 'button_header',
                    'name' => '<strong>' . __( 'Buttons', 'ac' ) . '</strong>',
                    'desc' => __( 'Options for add to cart and purchase buttons', 'ac' ),
                    'type' => 'header'
                ),
                'button_style' => array(
                    'id'      => 'button_style',
                    'name'    => __( 'Default Button Style', 'ac' ),
                    'desc'    => __( 'Choose the style you want to use for the buttons.', 'ac' ),
                    'type'    => 'select',
                    'options' => ac_get_button_styles()
                ),
                'checkout_color' => array(
                    'id'      => 'checkout_color',
                    'name'    => __( 'Default Button Color', 'ac' ),
                    'desc'    => __( 'Choose the color you want to use for the buttons.', 'ac' ),
                    'type'    => 'color_select',
                    'options' => ac_get_button_colors()
                )*/
            )
        ),
        /** Taxes Settings */
        'lists' => apply_filters('ac_settings_lists',
            array(
                /*'enable_taxes' => array(
                    'id'   => 'enable_taxes',
                    'name' => __( 'Enable Taxes', 'ac' ),
                    'desc' => __( 'Check this to enable taxes on purchases.', 'ac' ),
                    'type' => 'checkbox',
                ),
                'tax_rates' => array(
                    'id' => 'tax_rates',
                    'name' => '<strong>' . __( 'Tax Rates', 'ac' ) . '</strong>',
                    'desc' => __( 'Enter tax rates for specific regions.', 'ac' ),
                    'type' => 'tax_rates'
                ),
                'tax_rate' => array(
                    'id'   => 'tax_rate',
                    'name' => __( 'Fallback Tax Rate', 'ac' ),
                    'desc' => __( 'Enter a percentage, such as 6.5. Customers not in a specific rate will be charged this rate.', 'ac' ),
                    'type' => 'text',
                    'size' => 'small'
                ),
                'prices_include_tax' => array(
                    'id'   => 'prices_include_tax',
                    'name' => __( 'Prices entered with tax', 'ac' ),
                    'desc' => __( 'This option affects how you enter prices.', 'ac' ),
                    'type' => 'radio',
                    'std'  => 'no',
                    'options' => array(
                        'yes' => __( 'Yes, I will enter prices inclusive of tax', 'ac' ),
                        'no'  => __( 'No, I will enter prices exclusive of tax', 'ac' )
                    )
                ),
                'display_tax_rate' => array(
                    'id'   => 'display_tax_rate',
                    'name' => __( 'Display Tax Rate on Prices', 'ac' ),
                    'desc' => __( 'Some countries require a notice when product prices include tax.', 'ac' ),
                    'type' => 'checkbox',
                ),
                'checkout_include_tax' => array(
                    'id'   => 'checkout_include_tax',
                    'name' => __( 'Display during checkout', 'ac' ),
                    'desc' => __( 'Should prices on the checkout page be shown with or without tax?', 'ac' ),
                    'type' => 'select',
                    'std'  => 'no',
                    'options' => array(
                        'yes' => __( 'Including tax', 'ac' ),
                        'no'  => __( 'Excluding tax', 'ac' )
                    )
                )*/
            )
        ),
        /** Extension Settings */
        'extensions' => apply_filters('ac_settings_extensions',
            array()
        ),
        'licenses' => apply_filters('ac_settings_licenses',
            array()
        ),
        /** Misc Settings */
        'misc' => apply_filters('ac_settings_misc',
            array(
                /*'enable_ajax_cart' => array(
                    'id'   => 'enable_ajax_cart',
                    'name' => __( 'Enable Ajax', 'ac' ),
                    'desc' => __( 'Check this to enable AJAX for the shopping cart.', 'ac' ),
                    'type' => 'checkbox',
                    'std'  => '1'
                ),
                'redirect_on_add' => array(
                    'id'   => 'redirect_on_add',
                    'name' => __( 'Redirect to Checkout', 'ac' ),
                    'desc' => __( 'Immediately redirect to checkout after adding an item to the cart?', 'ac' ),
                    'type' => 'checkbox'
                ),
                'enforce_ssl' => array(
                    'id'   => 'enforce_ssl',
                    'name' => __( 'Enforce SSL on Checkout', 'ac' ),
                    'desc' => __( 'Check this to force users to be redirected to the secure checkout page. You must have an SSL certificate installed to use this option.', 'ac' ),
                    'type' => 'checkbox'
                ),
                'logged_in_only' => array(
                    'id'   => 'logged_in_only',
                    'name' => __( 'Disable Guest Checkout', 'ac' ),
                    'desc' => __( 'Require that users be logged-in to purchase files.', 'ac' ),
                    'type' => 'checkbox'
                ),
                'show_register_form' => array(
                    'id'      => 'show_register_form',
                    'name'    => __( 'Show Register / Login Form?', 'ac' ),
                    'desc'    => __( 'Display the registration and login forms on the checkout page for non-logged-in users.', 'ac' ),
                    'type'    => 'select',
                    'std'     => 'none',
                    'options' => array(
                        'both'         => __( 'Registration and Login Forms', 'ac' ),
                        'registration' => __( 'Registration Form Only', 'ac' ),
                        'login'        => __( 'Login Form Only', 'ac' ),
                        'none'         => __( 'None', 'ac' )
                    ),
                ),
                'item_quantities' => array(
                    'id'   => 'item_quantities',
                    'name' => __('Item Quantities', 'ac'),
                    'desc' => __('Allow item quantities to be changed.', 'ac'),
                    'type' => 'checkbox'
                ),
                'allow_multiple_discounts' => array(
                    'id'   => 'allow_multiple_discounts',
                    'name' => __('Multiple Discounts', 'ac'),
                    'desc' => __('Allow customers to use multiple discounts on the same purchase?', 'ac'),
                    'type' => 'checkbox'
                ),
                'enable_cart_saving' => array(
                    'id'   => 'enable_cart_saving',
                    'name' => __( 'Enable Cart Saving', 'ac' ),
                    'desc' => __( 'Check this to enable cart saving on the checkout.', 'ac' ),
                    'type' => 'checkbox'
                ),
                'field_downloads' => array(
                    'id' => 'field_downloads',
                    'name' => '<strong>' . __( 'File Downloads', 'ac' ) . '</strong>',
                    'desc' => '',
                    'type' => 'header'
                ),
                'download_method' => array(
                    'id'      => 'download_method',
                    'name'    => __( 'Download Method', 'ac' ),
                    'desc'    => sprintf( __( 'Select the file download method. Note, not all methods work on all servers.', 'ac' ), ac_get_label_singular() ),
                    'type'    => 'select',
                    'options' => array(
                        'direct'   => __( 'Forced', 'ac' ),
                        'redirect' => __( 'Redirect', 'ac' )
                    )
                ),
                'symlink_file_downloads' => array(
                    'id'   => 'symlink_file_downloads',
                    'name' => __( 'Symlink File Downloads?', 'ac' ),
                    'desc' => __( 'Check this if you are delivering really large files or having problems with file downloads completing.', 'ac' ),
                    'type' => 'checkbox'
                ),
                'file_download_limit' => array(
                    'id'   => 'file_download_limit',
                    'name' => __( 'File Download Limit', 'ac' ),
                    'desc' => sprintf( __( 'The maximum number of times files can be downloaded for purchases. Can be overwritten for each %s.', 'ac' ), ac_get_label_singular() ),
                    'type' => 'number',
                    'size' => 'small'
                ),
                'download_link_expiration' => array(
                    'id'   => 'download_link_expiration',
                    'name' => __( 'Download Link Expiration', 'ac' ),
                    'desc' => __( 'How long should download links be valid for? Default is 24 hours from the time they are generated. Enter a time in hours.', 'ac' ),
                    'type' => 'number',
                    'size' => 'small',
                    'std'  => '24',
                    'min'  => '0'
                ),
                'disable_redownload' => array(
                    'id'   => 'disable_redownload',
                    'name' => __( 'Disable Redownload?', 'ac' ),
                    'desc' => __( 'Check this if you do not want to allow users to redownload items from their purchase history.', 'ac' ),
                    'type' => 'checkbox'
                ),
                'accounting_settings' => array(
                    'id' => 'accounting_settings',
                    'name' => '<strong>' . __( 'Accounting Settings', 'ac' ) . '</strong>',
                    'desc' => '',
                    'type' => 'header'
                ),
                'enable_skus' => array(
                    'id'   => 'enable_skus',
                    'name' => __( 'Enable SKU Entry', 'ac' ),
                    'desc' => __( 'Check this box to allow entry of product SKUs. SKUs will be shown on purchase receipt and exported purchase histories.', 'ac' ),
                    'type' => 'checkbox'
                ),
                'enable_sequential' => array(
                    'id'   => 'enable_sequential',
                    'name' => __( 'Sequential Order Numbers', 'ac' ),
                    'desc' => __( 'Check this box to enable sequential order numbers.', 'ac' ),
                    'type' => 'checkbox'
                ),
                'sequential_start' => array(
                    'id'   => 'sequential_start',
                    'name' => __( 'Sequential Starting Number', 'ac' ),
                    'desc' => __( 'The number that sequential order numbers should start at.', 'ac' ),
                    'type' => 'number',
                    'size' => 'small',
                    'std'  => '1'
                ),
                'sequential_prefix' => array(
                    'id'   => 'sequential_prefix',
                    'name' => __( 'Sequential Number Prefix', 'ac' ),
                    'desc' => __( 'A prefix to prepend to all sequential order numbers.', 'ac' ),
                    'type' => 'text'
                ),
                'sequential_postfix' => array(
                    'id'   => 'sequential_postfix',
                    'name' => __( 'Sequential Number Postfix', 'ac' ),
                    'desc' => __( 'A postfix to append to all sequential order numbers.', 'ac' ),
                    'type' => 'text',
                ),
                'terms' => array(
                    'id' => 'terms',
                    'name' => '<strong>' . __( 'Terms of Agreement', 'ac' ) . '</strong>',
                    'desc' => '',
                    'type' => 'header'
                ),
                'show_agree_to_terms' => array(
                    'id'   => 'show_agree_to_terms',
                    'name' => __( 'Agree to Terms', 'ac' ),
                    'desc' => __( 'Check this to show an agree to terms on the checkout that users must agree to before purchasing.', 'ac' ),
                    'type' => 'checkbox'
                ),
                'agree_label' => array(
                    'id'   => 'agree_label',
                    'name' => __( 'Agree to Terms Label', 'ac' ),
                    'desc' => __( 'Label shown next to the agree to terms check box.', 'ac' ),
                    'type' => 'text',
                    'size' => 'regular'
                ),
                'agree_text' => array(
                    'id'   => 'agree_text',
                    'name' => __( 'Agreement Text', 'ac' ),
                    'desc' => __( 'If Agree to Terms is checked, enter the agreement terms here.', 'ac' ),
                    'type' => 'rich_editor'
                ),
                'checkout_label' => array(
                    'id'   => 'checkout_label',
                    'name' => __( 'Complete Purchase Text', 'ac' ),
                    'desc' => __( 'The button label for completing a purchase.', 'ac' ),
                    'type' => 'text',
                    'std'  => __( 'Purchase', 'ac' )
                ),
                'add_to_cart_text' => array(
                    'id'   => 'add_to_cart_text',
                    'name' => __( 'Add to Cart Text', 'ac' ),
                    'desc' => __( 'Text shown on the Add to Cart Buttons.', 'ac' ),
                    'type' => 'text',
                    'std'  => __( 'Add to Cart', 'ac' )
                ),
                'buy_now_text' => array(
                    'id' => 'buy_now_text',
                    'name' => __( 'Buy Now Text', 'ac' ),
                    'desc' => __( 'Text shown on the Buy Now Buttons.', 'ac' ),
                    'type' => 'text',
                    'std' => __( 'Buy Now', 'ac' )
                )*/
            )
        )
    );
    return apply_filters( 'ac_registered_settings', $ac_settings );
}
/**
 * Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * At some point this will validate input
 *
 * @since 1.0.8.2
 *
 * @param array $input The value inputted in the field
 *
 * @return string $input Sanitizied value
 */
function ac_settings_sanitize( $input = array() ) {
    global $ac_options;
    if ( empty( $_POST['_wp_http_referer'] ) ) {
        return $input;
    }
    parse_str( $_POST['_wp_http_referer'], $referrer );
    $settings = ac_get_registered_settings();
    $tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';
    $input = $input ? $input : array();
    $input = apply_filters( 'ac_settings_' . $tab . '_sanitize', $input );
    // Loop through each setting being saved and pass it through a sanitization filter
    foreach ( $input as $key => $value ) {
        // Get the setting type (checkbox, select, etc)
        $type = isset( $settings[$tab][$key]['type'] ) ? $settings[$tab][$key]['type'] : false;
        if ( $type ) {
            // Field type specific filter
            $input[$key] = apply_filters( 'ac_settings_sanitize_' . $type, $value, $key );
        }
        // General filter
        $input[$key] = apply_filters( 'ac_settings_sanitize', $input[$key], $key );
    }
    // Loop through the whitelist and unset any that are empty for the tab being saved
    if ( ! empty( $settings[$tab] ) ) {
        foreach ( $settings[$tab] as $key => $value ) {
            // settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
            if ( is_numeric( $key ) ) {
                $key = $value['id'];
            }
            if ( empty( $input[$key] ) ) {
                unset( $ac_options[$key] );
            }
        }
    }
    // Merge our new settings with the existing
    $output = array_merge( $ac_options, $input );
    add_settings_error( 'ac-notices', '', __( 'Settings updated.', 'ac' ), 'updated' );
    return $output;
}
/**
 * Taxes Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * This also saves the tax rates table
 *
 * @since 1.6
 * @param array $input The value inputted in the field
 * @return string $input Sanitizied value
 */
function ac_settings_sanitize_taxes( $input ) {
    if( ! current_user_can( 'manage_options' ) ) {
        return $input;
    }
    $new_rates = ! empty( $_POST['tax_rates'] ) ? array_values( $_POST['tax_rates'] ) : array();
    update_option( 'ac_tax_rates', $new_rates );
    return $input;
}
add_filter( 'ac_settings_taxes_sanitize', 'ac_settings_sanitize_taxes' );
/**
 * Sanitize text fields
 *
 * @since 1.8
 * @param array $input The field value
 * @return string $input Sanitizied value
 */
function ac_sanitize_text_field( $input ) {
    return trim( $input );
}
add_filter( 'ac_settings_sanitize_text', 'ac_sanitize_text_field' );
/**
 * Retrieve settings tabs
 *
 * @since 1.8
 * @return array $tabs
 */
function ac_get_settings_tabs() {
    $settings = ac_get_registered_settings();
    $tabs             = array();
    $tabs['general']  = __( 'General', 'ac' );
    $tabs['contacts'] = __( 'Contacts', 'ac' );
    $tabs['campaigns']   = __( 'Campaigns', 'ac' );
    $tabs['deals']   = __( 'Deals', 'ac' );
    $tabs['lists']    = __( 'Lists', 'ac' );
    if( ! empty( $settings['extensions'] ) ) {
        $tabs['extensions'] = __( 'Extensions', 'ac' );
    }
    if( ! empty( $settings['licenses'] ) ) {
        $tabs['licenses'] = __( 'Licenses', 'ac' );
    }
    $tabs['misc']      = __( 'Misc', 'ac' );
    return apply_filters( 'ac_settings_tabs', $tabs );
}
/**
 * Retrieve a list of all published pages
 *
 * On large sites this can be expensive, so only load if on the settings page or $force is set to true
 *
 * @since 1.9.5
 * @param bool $force Force the pages to be loaded even if not on settings
 * @return array $pages_options An array of the pages
 */
function ac_get_pages( $force = false ) {
    $pages_options = array( '' => '' ); // Blank option
    if( ( ! isset( $_GET['page'] ) || 'ac-settings' != $_GET['page'] ) && ! $force ) {
        return $pages_options;
    }
    $pages = get_pages();
    if ( $pages ) {
        foreach ( $pages as $page ) {
            $pages_options[ $page->ID ] = $page->post_title;
        }
    }
    return $pages_options;
}
/**
 * Header Callback
 *
 * Renders the header.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @return void
 */
function ac_header_callback( $args ) {
    echo '<hr/>';
}
/**
 * Checkbox Callback
 *
 * Renders checkboxes.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $ac_options Array of all the EDD Options
 * @return void
 */
function ac_checkbox_callback( $args ) {
    global $ac_options;
    if ( isset( $args['faux'] ) && true === $args['faux'] ) {
        $name = '';
    } else {
        $name = 'name="ac_settings[' . $args['id'] . ']"';
    }
    $checked = isset( $ac_options[ $args['id'] ] ) ? checked( 1, $ac_options[ $args['id'] ], false ) : '';
    $html = '<input type="checkbox" id="ac_settings[' . $args['id'] . ']"' . $name . ' value="1" ' . $checked . '/>';
    $html .= '<label for="ac_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
    echo $html;
}
/**
 * Multicheck Callback
 *
 * Renders multiple checkboxes.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $ac_options Array of all the EDD Options
 * @return void
 */
function ac_multicheck_callback( $args ) {
    global $ac_options;
    if ( ! empty( $args['options'] ) ) {
        foreach( $args['options'] as $key => $option ):
            if( isset( $ac_options[$args['id']][$key] ) ) { $enabled = $option; } else { $enabled = NULL; }
            echo '<input name="ac_settings[' . $args['id'] . '][' . $key . ']" id="ac_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="' . $option . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
            echo '<label for="ac_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
        endforeach;
        echo '<p class="description">' . $args['desc'] . '</p>';
    }
}
/**
 * Radio Callback
 *
 * Renders radio boxes.
 *
 * @since 1.3.3
 * @param array $args Arguments passed by the setting
 * @global $ac_options Array of all the EDD Options
 * @return void
 */
function ac_radio_callback( $args ) {
    global $ac_options;
    foreach ( $args['options'] as $key => $option ) :
        $checked = false;
        if ( isset( $ac_options[ $args['id'] ] ) && $ac_options[ $args['id'] ] == $key )
            $checked = true;
        elseif( isset( $args['std'] ) && $args['std'] == $key && ! isset( $ac_options[ $args['id'] ] ) )
            $checked = true;
        echo '<input name="ac_settings[' . $args['id'] . ']"" id="ac_settings[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked(true, $checked, false) . '/>&nbsp;';
        echo '<label for="ac_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
    endforeach;
    echo '<p class="description">' . $args['desc'] . '</p>';
}
/**
 * Gateways Callback
 *
 * Renders gateways fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $ac_options Array of all the EDD Options
 * @return void
 */
function ac_gateways_callback( $args ) {
    global $ac_options;
    foreach ( $args['options'] as $key => $option ) :
        if ( isset( $ac_options['gateways'][ $key ] ) )
            $enabled = '1';
        else
            $enabled = null;
        echo '<input name="ac_settings[' . $args['id'] . '][' . $key . ']"" id="ac_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="1" ' . checked('1', $enabled, false) . '/>&nbsp;';
        echo '<label for="ac_settings[' . $args['id'] . '][' . $key . ']">' . $option['admin_label'] . '</label><br/>';
    endforeach;
}
/**
 * Gateways Callback (drop down)
 *
 * Renders gateways select menu
 *
 * @since 1.5
 * @param array $args Arguments passed by the setting
 * @global $ac_options Array of all the EDD Options
 * @return void
 */
function ac_gateway_select_callback($args) {
    global $ac_options;
    echo '<select name="ac_settings[' . $args['id'] . ']"" id="ac_settings[' . $args['id'] . ']">';
    foreach ( $args['options'] as $key => $option ) :
        $selected = isset( $ac_options[ $args['id'] ] ) ? selected( $key, $ac_options[$args['id']], false ) : '';
        echo '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $option['admin_label'] ) . '</option>';
    endforeach;
    echo '</select>';
    echo '<label for="ac_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
}
/**
 * Text Callback
 *
 * Renders text fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $ac_options Array of all the EDD Options
 * @return void
 */
function ac_text_callback( $args ) {
    global $ac_options;
    if ( isset( $ac_options[ $args['id'] ] ) ) {
        $value = $ac_options[ $args['id'] ];
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }
    if ( isset( $args['faux'] ) && true === $args['faux'] ) {
        $args['readonly'] = true;
        $value = isset( $args['std'] ) ? $args['std'] : '';
        $name  = '';
    } else {
        $name = 'name="ac_settings[' . $args['id'] . ']"';
    }
    $readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';
    $size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html     = '<input type="text" class="' . $size . '-text" id="ac_settings[' . $args['id'] . ']"' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"' . $readonly . '/>';
    $html    .= '<label for="ac_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
    echo $html;
}
/**
 * Number Callback
 *
 * Renders number fields.
 *
 * @since 1.9
 * @param array $args Arguments passed by the setting
 * @global $ac_options Array of all the EDD Options
 * @return void
 */
function ac_number_callback( $args ) {
    global $ac_options;
    if ( isset( $ac_options[ $args['id'] ] ) ) {
        $value = $ac_options[ $args['id'] ];
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }
    if ( isset( $args['faux'] ) && true === $args['faux'] ) {
        $args['readonly'] = true;
        $value = isset( $args['std'] ) ? $args['std'] : '';
        $name  = '';
    } else {
        $name = 'name="ac_settings[' . $args['id'] . ']"';
    }
    $max  = isset( $args['max'] ) ? $args['max'] : 999999;
    $min  = isset( $args['min'] ) ? $args['min'] : 0;
    $step = isset( $args['step'] ) ? $args['step'] : 1;
    $size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $size . '-text" id="ac_settings[' . $args['id'] . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"/>';
    $html .= '<label for="ac_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
    echo $html;
}
/**
 * Textarea Callback
 *
 * Renders textarea fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $ac_options Array of all the EDD Options
 * @return void
 */
function ac_textarea_callback( $args ) {
    global $ac_options;
    if ( isset( $ac_options[ $args['id'] ] ) ) {
        $value = $ac_options[ $args['id'] ];
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }
    $html = '<textarea class="large-text" cols="50" rows="5" id="ac_settings[' . $args['id'] . ']" name="ac_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
    $html .= '<label for="ac_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
    echo $html;
}
/**
 * Password Callback
 *
 * Renders password fields.
 *
 * @since 1.3
 * @param array $args Arguments passed by the setting
 * @global $ac_options Array of all the EDD Options
 * @return void
 */
function ac_password_callback( $args ) {
    global $ac_options;
    if ( isset( $ac_options[ $args['id'] ] ) ) {
        $value = $ac_options[ $args['id'] ];
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }
    $size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html = '<input type="password" class="' . $size . '-text" id="ac_settings[' . $args['id'] . ']" name="ac_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';
    $html .= '<label for="ac_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
    echo $html;
}
/**
 * Missing Callback
 *
 * If a function is missing for settings callbacks alert the user.
 *
 * @since 1.3.1
 * @param array $args Arguments passed by the setting
 * @return void
 */
function ac_missing_callback($args) {
    printf( __( 'The callback function used for the <strong>%s</strong> setting is missing.', 'ac' ), $args['id'] );
}
/**
 * Select Callback
 *
 * Renders select fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $ac_options Array of all the EDD Options
 * @return void
 */
function ac_select_callback($args) {
    global $ac_options;
    if ( isset( $ac_options[ $args['id'] ] ) ) {
        $value = $ac_options[ $args['id'] ];
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }
    if ( isset( $args['placeholder'] ) ) {
        $placeholder = $args['placeholder'];
    } else {
        $placeholder = '';
    }
    if ( isset( $args['chosen'] ) ) {
        $chosen = 'class="ac-chosen"';
    } else {
        $chosen = '';
    }
    $html = '<select id="ac_settings[' . $args['id'] . ']" name="ac_settings[' . $args['id'] . ']" ' . $chosen . 'data-placeholder="' . $placeholder . '" />';
    foreach ( $args['options'] as $option => $name ) {
        $selected = selected( $option, $value, false );
        $html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
    }
    $html .= '</select>';
    $html .= '<label for="ac_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
    echo $html;
}
/**
 * Color select Callback
 *
 * Renders color select fields.
 *
 * @since 1.8
 * @param array $args Arguments passed by the setting
 * @global $ac_options Array of all the EDD Options
 * @return void
 */
function ac_color_select_callback( $args ) {
    global $ac_options;
    if ( isset( $ac_options[ $args['id'] ] ) ) {
        $value = $ac_options[ $args['id'] ];
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }
    $html = '<select id="ac_settings[' . $args['id'] . ']" name="ac_settings[' . $args['id'] . ']"/>';
    foreach ( $args['options'] as $option => $color ) {
        $selected = selected( $option, $value, false );
        $html .= '<option value="' . $option . '" ' . $selected . '>' . $color['label'] . '</option>';
    }
    $html .= '</select>';
    $html .= '<label for="ac_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
    echo $html;
}
/**
 * Rich Editor Callback
 *
 * Renders rich editor fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $ac_options Array of all the EDD Options
 * @global $wp_version String WordPress Version
 */
function ac_rich_editor_callback( $args ) {
    global $ac_options, $wp_version;
    if ( isset( $ac_options[ $args['id'] ] ) ) {
        $value = $ac_options[ $args['id'] ];
        if( empty( $args['allow_blank'] ) && empty( $value ) ) {
            $value = isset( $args['std'] ) ? $args['std'] : '';
        }
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }
    $rows = isset( $args['size'] ) ? $args['size'] : 20;
    if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
        ob_start();
        wp_editor( stripslashes( $value ), 'ac_settings_' . $args['id'], array( 'textarea_name' => 'ac_settings[' . $args['id'] . ']', 'textarea_rows' => $rows ) );
        $html = ob_get_clean();
    } else {
        $html = '<textarea class="large-text" rows="10" id="ac_settings[' . $args['id'] . ']" name="ac_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
    }
    $html .= '<br/><label for="ac_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
    echo $html;
}
/**
 * Upload Callback
 *
 * Renders upload fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $ac_options Array of all the EDD Options
 * @return void
 */
function ac_upload_callback( $args ) {
    global $ac_options;
    if ( isset( $ac_options[ $args['id'] ] ) ) {
        $value = $ac_options[$args['id']];
    } else {
        $value = isset($args['std']) ? $args['std'] : '';
    }
    $size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html = '<input type="text" class="' . $size . '-text" id="ac_settings[' . $args['id'] . ']" name="ac_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
    $html .= '<span>&nbsp;<input type="button" class="ac_settings_upload_button button-secondary" value="' . __( 'Upload File', 'ac' ) . '"/></span>';
    $html .= '<label for="ac_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
    echo $html;
}
/**
 * Color picker Callback
 *
 * Renders color picker fields.
 *
 * @since 1.6
 * @param array $args Arguments passed by the setting
 * @global $ac_options Array of all the EDD Options
 * @return void
 */
function ac_color_callback( $args ) {
    global $ac_options;
    if ( isset( $ac_options[ $args['id'] ] ) ) {
        $value = $ac_options[ $args['id'] ];
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }
    $default = isset( $args['std'] ) ? $args['std'] : '';
    $size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html = '<input type="text" class="ac-color-picker" id="ac_settings[' . $args['id'] . ']" name="ac_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '" data-default-color="' . esc_attr( $default ) . '" />';
    $html .= '<label for="ac_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
    echo $html;
}


/**
 * Descriptive text callback.
 *
 * Renders descriptive text onto the settings field.
 *
 * @since 2.1.3
 * @param array $args Arguments passed by the setting
 * @return void
 */
function ac_descriptive_text_callback( $args ) {
    echo wp_kses_post( $args['desc'] );
}
/**
 * Registers the license field callback for Software Licensing
 *
 * @since 1.5
 * @param array $args Arguments passed by the setting
 * @global $ac_options Array of all the EDD Options
 * @return void
 */
if ( ! function_exists( 'ac_license_key_callback' ) ) {
    function ac_license_key_callback( $args ) {
        global $ac_options;
        if ( isset( $ac_options[ $args['id'] ] ) ) {
            $value = $ac_options[ $args['id'] ];
        } else {
            $value = isset( $args['std'] ) ? $args['std'] : '';
        }
        $size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
        $html = '<input type="text" class="' . $size . '-text" id="ac_settings[' . $args['id'] . ']" name="ac_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';
        if ( 'valid' == get_option( $args['options']['is_valid_license_option'] ) ) {
            $html .= '<input type="submit" class="button-secondary" name="' . $args['id'] . '_deactivate" value="' . __( 'Deactivate License',  'ac' ) . '"/>';
        }
        $html .= '<label for="ac_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
        wp_nonce_field( $args['id'] . '-nonce', $args['id'] . '-nonce' );
        echo $html;
    }
}
/**
 * Hook Callback
 *
 * Adds a do_action() hook in place of the field
 *
 * @since 1.0.8.2
 * @param array $args Arguments passed by the setting
 * @return void
 */
function ac_hook_callback( $args ) {
    do_action( 'ac_' . $args['id'], $args );
}
/**
 * Set manage_options as the cap required to save EDD settings pages
 *
 * @since 1.9
 * @return string capability required
 */
function ac_set_settings_cap() {
    return 'manage_options';
}
add_filter( 'option_page_capability_ac_settings', 'ac_set_settings_cap' );