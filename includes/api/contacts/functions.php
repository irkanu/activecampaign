<?php
/**
 * Contact Setting Functions
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
 * Check to see if we should do the contact sync
 *
 * @since   1.0.0
 * @return  void
 */
function ac_maybe_contact_sync() {

    if ( isset( $_REQUEST['ac_action'] ) && $_REQUEST['ac_action'] == 'maybe_contact_sync' ) {
        /**
         * Documented elsewhere TODO
         */
        ac_do_contact_sync();
    }

}
add_action( 'ac_contact_sync', 'ac_maybe_contact_sync' );


/**
 * TODO
 */
function ac_do_contact_sync() {
    global $ac_options;

    // TODO: Verify nonce before we make this call...
    $connector = new ActiveCampaign( $ac_options['api_url'], $ac_options['api_key'] );

    // do the syncage bruh.
    // $user is the top level
    $users = get_users();

    // drill down into each user
    foreach ( $users as $user ) {
        // drill down into their values
        $post = array(
            'first_name'	=>	$user->user_firstname,
            'last_name'		=>	$user->user_lastname,
            'email'         =>  $user->user_email,
            'p[2]'          =>  2
        );
        $connector->api("contact/sync", $post);
    }
    return false;
}