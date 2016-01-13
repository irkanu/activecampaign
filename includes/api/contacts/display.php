<?php
/**
 * Contact Setting Callbacks
 *
 * @package     Active_Campaign
 * @subpackage  Contacts
 * @copyright   Copyright (c) 2015, Dylan Ryan
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display Contact Sync button
 *
 * @access  private
 * @since   1.0.0
 */
function ac_display_contact_sync() {
    if( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ob_start();
    ?>
        <a href="<?php echo wp_nonce_url( add_query_arg( array( 'ac_action' => 'maybe_contact_sync' ) ), 'ac-contact-sync' ); ?>" class="button-secondary" title="<?php _e( 'Sync Contacts', 'ac' ); ?> "><?php _e( 'Sync Contacts', 'ac' ); ?></a>
        <label><?php _e( 'This button syncs your WordPress users with ActiveCampaign. If an entry doesn\'t exist, then one is created for that email address.', 'ac' ); ?></label>
    <?php
    echo ob_get_clean();
}
// this needs to be ac_contact_sync to display our button
add_action( 'ac_contact_sync', 'ac_display_contact_sync' );