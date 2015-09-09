<?php
/**
 * Plugin Name: Active Campaign
 * Description: A brief description of the Plugin.
 * Version: 1.0
 * Author: Dylan Ryan
 * License: A "Slug" license name e.g. GPL2
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
			// Active Campaign PHP Library
			require_once ACTIVE_CAMPAIGN_DIR . 'vendor/activecampaign/api-php/includes/ActiveCampaign.class.php';
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
			//$this->connector = new ActiveCampaign( $ac_options['api_url'], $ac_options['api_key'] );
			$this->connector = new ActiveCampaign( 'https://dylanryan.api-us1.com', 'b9343b47a317b7adf26d9b6f65689da45c5fb1dce7f30a440931283301aac94ca54edea2' );
			add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		}


		public function get_user_me() {
			$user_me = $this->connector->api("user/me");
			return $user_me;
		}

		public function get_account_view() {
			$account_view = $this->connector->api("user/me");
			$this->print_r_debug($account_view);
			return $account_view;
		}

		public function add_options_page() {
			add_menu_page( 'ActiveCampaign', 'ActiveCampaign', 'manage_options', 'options_page_slug', array( $this, 'settings_page' ) );
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