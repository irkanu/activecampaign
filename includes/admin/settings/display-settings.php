<?php
/**
 * Admin Options Page
 *
 * @package     Active_Campaign
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2015, Dylan Ryan
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Options Page
 *
 * Renders the options page contents.
 *
 * @since 1.0
 * @return void
 */
function ac_options_page() {
    global $ac_options;
    $active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], ac_get_settings_tabs() ) ? $_GET['tab'] : 'general';

    // Initiate a connection to the API.
    $connector = new ActiveCampaign( $ac_options['api_url'], $ac_options['api_key'] );

    // Pull the image from branding end point if the user enables branding.
    if ( isset( $ac_options['branding_enabled'] ) && $ac_options['branding_enabled'] == true ) {
        $brand_image = $connector->api("design/view")->site_logo_small;
    } else {
        $brand_image = ACTIVE_CAMPAIGN_URL . 'assets/images/ac_color_symbol_trans.png';
    }

    // Test the credentials on page load and set green if good, red if bad.
    // TODO: Let the user choose when to test creds.
    if ( !(int)$connector->credentials_test() ) {
        // We failed, set input bg to red.
        $cred_check = 'rgba(255, 0, 0, 0.15)';
    } else {
        // We succeeded, set input bg to green.
        $cred_check = 'rgba(0, 255, 20, 0.15)';
    }

    ob_start();
    ?>
    <style>
        .toplevel_page_ac-settings input[name="ac_settings[api_url]"],
        .toplevel_page_ac-settings input[name="ac_settings[api_key]"]{
            background-color: <?php echo esc_attr($cred_check); ?>;
        }
    </style>
    <div class="wrap">
        <h2 class="nav-tab-wrapper">
            <img class="branding_icon" src="<?php echo esc_attr($brand_image); ?>" width="64" height="64">
            <?php
            foreach( ac_get_settings_tabs() as $tab_id => $tab_name ) {
                $tab_url = add_query_arg( array(
                    'settings-updated' => false,
                    'tab' => $tab_id
                ) );
                $active = $active_tab == $tab_id ? ' nav-tab-active' : '';
                echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">';
                echo esc_html( $tab_name );
                echo '</a>';
                do_action( 'ac_admin_page' );
            }
            ?>
        </h2>
        <div id="tab_container">
            <form method="post" action="options.php">
                <table class="form-table">
                    <?php
                    settings_fields( 'ac_settings' );
                    do_settings_fields( 'ac_settings_' . $active_tab, 'ac_settings_' . $active_tab );
                    ?>
                </table>
                <?php submit_button(); ?>
            </form>
        </div><!-- #tab_container-->
    </div><!-- .wrap -->
    <?php
    echo ob_get_clean();
}