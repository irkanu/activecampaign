<?php
/**
 * Plugin Name:	Active Campaign
 * Description: A brief description of the Plugin.
 * Version:		1.0
 * Author:		Dylan Ryan
 * License:		A "Slug" license name e.g. GPL2
 *
 * @package         Active_Campaign
 * @author          Dylan Ryan
 * @copyright       Copyright (c) Dylan Ryan
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'Active_Campaign' ) ) {


	/**
	 * Main Active_Campaign class
	 *
	 * @since 1.0.0
	 */
	class Active_Campaign {


		/**
		 * @var     ActiveCampaign
		 * @since   1.0.0
		 */
		private $connector;


		/**
		 * @var         Active_Campaign $instance The one true Active_Campaign
		 * @since       1.0.0
		 */
		private static $instance;


		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      object self::$instance The one true Active_Campaign
		 */
		public static function instance() {
			if( !self::$instance ) {
				self::$instance = new Active_Campaign();
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->hooks();
			}
			return self::$instance;
		}


		/**
		 * Setup plugin constants
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function setup_constants() {
			// Plugin version
			define( 'ACTIVE_CAMPAIGN_VER', '1.0.0' );

			// Plugin path
			define( 'ACTIVE_CAMPAIGN_DIR', plugin_dir_path( __FILE__ ) );

			// Plugin URL
			define( 'ACTIVE_CAMPAIGN_URL', plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Include necessary files
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function includes() {
			global $ac_options;

			require_once ACTIVE_CAMPAIGN_DIR . 'includes/admin/settings/register-settings.php';
			$ac_options = ac_get_settings();

			// Active Campaign PHP Library
			require_once ACTIVE_CAMPAIGN_DIR . 'vendor/activecampaign/api-php/includes/ActiveCampaign.class.php';

			require_once ACTIVE_CAMPAIGN_DIR . 'includes/scripts.php';

			if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
				require_once ACTIVE_CAMPAIGN_DIR . 'includes/admin/admin-pages.php';
				require_once ACTIVE_CAMPAIGN_DIR . 'includes/admin/settings/display-settings.php';
				require_once ACTIVE_CAMPAIGN_DIR . 'includes/api/contacts/actions.php';
				require_once ACTIVE_CAMPAIGN_DIR . 'includes/api/contacts/functions.php';
			}
		}


		/**
		 * Run action and filter hooks
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function hooks() {
			add_action( 'admin_menu', array( $this, 'add_options_page' ) );
			add_shortcode( 'ac_restrict', array( $this, 'add_shortcode' ) );
		}


		public function get_user_me() {
			global $ac_options;

			$user_me_transient = md5( __FUNCTION__ . '_' . ACTIVE_CAMPAIGN_VER . '_' . $ac_options['api_key'] . '_' . '0004' );

			// set user_me equal to transient
			$user_me = get_transient( $user_me_transient );

			// check if the transient has stuffies
			if ( ! empty ( $user_me ) ) {
				// return all the stuffies
				return $user_me;
			} else {
				// if we don't, let's populate the transient
				$query_args = array(
					'api_action'	=>	'user_me',
					'api_key'		=>	$ac_options['api_key'],
					'api_output'	=>	'serialize'
				);
				// escape our add_query_args
				// https://make.wordpress.org/plugins/2015/04/20/fixing-add_query_arg-and-remove_query_arg-usage/
				$user_me_response = wp_safe_remote_get( esc_url_raw( add_query_arg( $query_args, $ac_options['api_url'] . '/admin/api.php?' ) ) );

				// grab the body
				$user_me = unserialize( $user_me_response['body'] );

				// Save the API response so we don't have to call again until tomorrow.
				set_transient( $user_me_transient, $user_me, DAY_IN_SECONDS );

				// return the api body
				return $user_me;
			}
		}

		public function get_account_view() {
			$current_user = wp_get_current_user();
			$account_view = $this->connector->api("list/view?id=1");
			// list/list ?ids=all allows you to pull all lists.
			// &filters[KEY]=VALUE let's you search a key for a value.
			//$all_lists = $this->connector->api("list/list?ids=all&full=1");
			$user_email = $this->connector->api("contact/view?email=" . $current_user->user_email);
			$this->print_r_debug($user_email->tags);
			return $account_view;
		}

		public function do_contact_sync() {
			global $ac_options;
			$this->connector = new ActiveCampaign( $ac_options['api_url'], $ac_options['api_key'] );
			// do the syncage bruh.
			// there is a lot we can do here
			// http://www.activecampaign.com/api/example.php?call=contact_sync
/*			$query_args = array(
				'api_action'	=>	'contact_add',
				'api_key'		=>	$ac_options['api_key'],
				'api_output'	=>	'serialize',
				'email'         =>  'test@gmail.com',
				'p[2]'          =>  2
			);*/

			// escape our add_query_args
			// https://make.wordpress.org/plugins/2015/04/20/fixing-add_query_arg-and-remove_query_arg-usage/
			//$contact_sync_response = wp_safe_remote_post( add_query_arg( $query_args, $ac_options['api_url'] . '/admin/api.php?' ) );

			// grab the body
			//$contact_sync = unserialize( $contact_sync_response['body'] );

			//?email=test@test.com&p[1]=1&last_name=Boobs

/*			$users = get_users();

			foreach ( $users as $user ) {
				$post = array(
					'first_name'	=>	$user->user_firstname,
					'last_name'		=>	$user->user_lastname,
					'email'         =>  $user->user_email,
					'p[2]'          =>  2
				);
				$contact_sync = $this->connector->api("contact/sync", $post);
			}*/

			$contact_sync = '';

			return $contact_sync;
		}

		public function add_options_page() {
			add_menu_page( 'Debug', 'Debug', 'manage_options', 'debug', array( $this, 'settings_page' ) );
		}

		public function settings_page() {
			global $ac_options;
			//$this->connector = new ActiveCampaign( $ac_options['api_url'], $ac_options['api_key'] );
			//$this->get_account_view();
			//$response = wp_safe_remote_get( $ac_options['api_url'] . '/admin/api.php?api_action=user_me&api_key=' . $ac_options['api_key'] . '&api_output=serialize' );
/*			if( is_array($response) ) {
				$body = unserialize($response['body']); // use the content
			}*/
			$this->print_r_debug($this->do_contact_sync());
		}

		public function print_r_debug( $val ) {
			echo "<pre>";
			//$result = json_decode(json_encode($val),true);
			//print_r($result);
			print_r($val);
			//var_dump($val);
			echo "</pre>";
		}

		public function add_shortcode( $atts, $content = null ) {
				extract( shortcode_atts( array(
					'tag' => 'none',
				), $atts ) );

			global $ac_options;

			$current_user = wp_get_current_user();

			$this->connector = new ActiveCampaign( $ac_options['api_url'], $ac_options['api_key'] );
			$user_email = $this->connector->api("contact/view?email=" . $current_user->user_email);

			$restrict_tag = $user_email->tags;

			//TODO: needle haystack search
			if ($tag = $restrict_tag && is_user_logged_in()) {
				return do_shortcode($content);
			} else {
				return '<span style="color: red;">'. $ac_options['list_restriction_error_message'] . '</span>';
			}
		}
	}
} // End if class_exists check


/**
 * The main function responsible for returning the one true Active_Campaign
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \Active_Campaign The one true Active_Campaign
 */
function AC_load_instance() {
	return Active_Campaign::instance();
}
add_action( 'plugins_loaded', 'AC_load_instance' );