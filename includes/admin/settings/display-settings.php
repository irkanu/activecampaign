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
    $active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], ac_get_settings_tabs() ) ? $_GET['tab'] : 'general';
    ob_start();
    ?>
    <div class="wrap">
        <h2 class="nav-tab-wrapper">
            <img src="<?php echo ACTIVE_CAMPAIGN_URL . 'assets/images/ac_color_symbol_trans.png' ?>" width="64" height="64">
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