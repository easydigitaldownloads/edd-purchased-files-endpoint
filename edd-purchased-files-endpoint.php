<?php
/*
Plugin Name: Easy Digital Downloads - Purchased Files API Endpoint
Plugin URI: http://easydigitaldownloads.com/
Description: Creates an API endpoint that allows the user associated with the API Key to see their purchased products in a JSON object
Author: Easy Digital Downloads
Author URI: https://easydigitaldownloads.com
Version: 0.1
Text Domain: edd-purchased-files-endpoint
Domain Path: languages
*/


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'EDD_Purchased_Files_Endpoint' ) ) {

	/**
	 * Main EDD_Purchased_Files_Endpoint class
	 *
	 * @since       0.1
	 */
	class EDD_Purchased_Files_Endpoint {

		/**
		 * @var         EDD_Purchased_Files_Endpoint $instance The one true EDD_Purchased_Files_Endpoint
		 * @since       0.1
		 */
		private static $instance;


		private function __construct() {
			if ( ! class_exists( 'Easy_Digital_Downloads' ) ){
				return;
			}

			$this->setup_constants();
			$this->includes();
			$this->hooks();
		}
		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       0.1
		 * @return      object self::$instance The one true EDD_Purchased_Files_Endpoint
		 */
		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = new EDD_Purchased_Files_Endpoint();
			}

			return self::$instance;
		}


		/**
		 * Setup plugin constants
		 *
		 * @access      private
		 * @since       0.1
		 * @return      void
		 */
		private function setup_constants() {
			// Plugin version
			define( 'EDD_PURCHASED_FILES_VERSION', '0.1' );

			// Plugin folder url
			define( 'EDD_PURCHASED_FILES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

			// Plugin folder path
			define( 'EDD_PURCHASED_FILES_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

			// Plugin root file
			define( 'EDD_PURCHASED_FILES_PLUGIN_FILE', __FILE__ );
		}


		/**
		 * Include necessary files
		 *
		 * @access      private
		 * @since       0.1
		 * @return      void
		 */
		private function includes() {

			require_once EDD_PURCHASED_FILES_PLUGIN_DIR . 'includes/classes/class-rest-api.php';

		}


		/**
		 * Run action and filter hooks
		 *
		 * @access      private
		 * @since       0.1
		 * @return      void
		 */
		private function hooks() {}

	}
}


/**
 * The main function responsible for returning the one true EDD_Purchased_Files_Endpoint
 * instance to functions everywhere
 *
 * @since       0.1
 * @return      \EDD_Purchased_Files_Endpoint The one true EDD_Purchased_Files_Endpoint
 */
function edd_purchased_files() {
	return EDD_Purchased_Files_Endpoint::instance();
}
add_action( 'plugins_loaded', 'edd_purchased_files', 1 );