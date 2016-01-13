<?php
/**
 * Contact Actions
 *
 * @package     Active_Campaign
 * @subpackage  Contacts
 * @copyright   Copyright (c) 2015, Dylan Ryan
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trigger the contact sync
 *
 * @since 1.0.0
 */
function ac_do_contact_sync() {
	global $ac_options;

	if ( ! isset ( $_GET['doing_contact_sync'] ) ) {
		return false;
	}

	$this->connector = new ActiveCampaign( $ac_options['api_url'], $ac_options['api_key'] );

	// do the syncage bruh.
	$users = get_users();

	foreach ( $users as $user ) {
		$post = array(
			'first_name' => $user->user_firstname,
			'last_name'  => $user->user_lastname,
			'email'      => $user->user_email,
			'p[4]'       => 4
		);
		$this->connector->api( "contact/sync", $post );
	}

}

add_action( 'ac_contact_sync', 'ac_do_contact_sync' );
