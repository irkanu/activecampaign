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
 * Get Settings
 *
 * Retrieves all plugin settings
 *
 * @since 1.0
 * @return array Active_Campaign settings
 */
function ac_get_settings() {
    $settings = get_option( 'ac_settings' );
    if( empty( $settings ) ) {
        // Update old settings with new single option
        $general_settings = is_array( get_option( 'ac_settings_general' ) ) ? get_option( 'ac_settings_general' ) : array();
        $settings = array_merge( $general_settings );
        update_option( 'ac_settings', $settings );
    }
    return apply_filters( 'ac_get_settings', $settings );
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
 * Retrieve the array of plugin settings
 *
 * @since 1.8
 * @return array
 */
function ac_get_registered_settings() {
    /**
     * 'Whitelisted' ac settings, filters are provided for each settings
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
                    'id' => 'api_url',
                    'name' => __( 'API URL', 'ac' ),
                    'desc' => __( 'This is the API URL from your ActiveCampaign settings.', 'ac' ),
                    'type' => 'text',
                    'size' => 'regular',
                    'std'  => 'https://example.api-us1.com'
                ),
                'api_key' => array(
                    'id' => 'api_key',
                    'name' => __( 'API Key', 'ac' ),
                    'desc' => __( 'This is the API key from your ActiveCampaign settings.', 'ac' ),
                    'type' => 'text',
                    'size' => 'regular',
                    'std'  => ''
                ),
            )
        ),
        /** Extension Settings */
/*        'extensions' => apply_filters('ac_settings_extensions',
            array()
        ),
        'licenses' => apply_filters('ac_settings_licenses',
            array()
        ),*/
        /** Misc Settings */
        'misc' => apply_filters('ac_settings_misc',
            array()
        )
    );
    return apply_filters( 'ac_registered_settings', $ac_settings );
}

/**
 * Retrieve a list of all published pages
 *
 * On large sites this can be expensive, so only load if on the settings page or $force is set to true
 *
 * @since 1.9.5
 * @param   bool    $force          Force the pages to be loaded even if not on settings
 * @return  array   $pages_options  An array of the pages
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
 * Retrieve settings tabs
 *
 * @since 1.8
 * @return array $tabs
 */
function ac_get_settings_tabs() {
    $settings = ac_get_registered_settings();
    $tabs             = array();
    $tabs['general']  = __( 'General', 'ac' );
/*    $tabs['gateways'] = __( 'Payment Gateways', 'ac' );
    $tabs['emails']   = __( 'Emails', 'ac' );
    $tabs['styles']   = __( 'Styles', 'ac' );
    $tabs['taxes']    = __( 'Taxes', 'ac' );*/
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
 * Text Callback
 *
 * Renders text fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $ac_options Array of all the Active_Campaign Options
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
 * @global $ac_options Array of all the Active_Campaign Options
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
 * @global $ac_options Array of all the ac Options
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
 * @global $ac_options Array of all the ac Options
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