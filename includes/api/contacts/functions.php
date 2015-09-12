<?php
/**
 * Contact Functions
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
    <form action="<?php echo admin_url( 'admin-post.php' ); ?>">
        <input type="hidden" name="ac_action" value="contact_sync">
<!--        <a href="<?php /*echo wp_nonce_url( add_query_arg( array( 'ac_action' => 'contact_sync' ) ), 'ac-contact-sync' ); */?>" class="button-secondary" title="<?php /*_e( 'Sync Contacts', 'ac' ); */?> "><?php /*_e( 'Sync Contacts', 'ac' ); */?></a>
-->
        <input type="submit" class="button-secondary" value="<?php _e( 'Sync Contacts', 'ac' ); ?>" />
        <label><?php _e( 'This button syncs your WordPress users with ActiveCampaign. If an entry doesn\'t exist, then one is created for that email address.', 'ac' ); ?></label>
    </form>
    <?php
    echo ob_get_clean();
}
add_action( 'ac_contact_sync', 'ac_display_contact_sync' );