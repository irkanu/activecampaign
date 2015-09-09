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
			global $ac_options;
			$this->connector = new ActiveCampaign( $ac_options['api_url'], $ac_options['api_key'] );
			add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		}


		public function get_user_me() {
			$user_me = $this->connector->api("user/me");
			return $user_me;
		}

		public function get_account_view() {
			$account_view = $this->connector->api("contact/list_");
			$this->print_r_debug($account_view);
			return $account_view;
		}

		public function add_options_page() {
			add_menu_page( 'Debug', 'Debug', 'manage_options', 'debug', array( $this, 'settings_page' ) );
		}

		public function settings_page() {
			$this->get_account_view();
		}

		public function print_r_debug( $val ) {
			echo "<pre>";
			//$result = json_decode(json_encode($val),true);
			//print_r($result);
			print_r($val);
			//var_dump($val);
			echo "</pre>";
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
//hey qt