<?php
/**
 * API Functions
 *
 * @package     EDD_Purchased_Files_Endpoint
 * @subpackage  API
 * @copyright   Copyright (c) 2017, Chris Klosowski
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// extends the default EDD REST API to provide an endpoint for commissions
class EDD_Purchased_Files_Rest_API {

	private $found_files = array();

	/**
	 * Get things started
	 *
	 * @access      public
	 * @return      void
	 */
	public function __construct() {
		add_filter( 'edd_api_valid_query_modes', array( $this, 'query_modes'  ) );
		add_filter( 'edd_api_output_data',       array( $this, 'user_files' ), 10, 3 );
	}


	/**
	 * Add commissions to the available query modes
	 *
	 * @access      public
	 * @param       array $query_modes The current query modes
	 * @return      array The adjusted query modes
	 */
	public function query_modes( $query_modes ) {
		$query_modes[] = 'my-files';

		return $query_modes;
	}

	/**
	 * Fetch user commission data
	 *
	 * @access      public
	 * @param       array $data
	 * @param       string $query_mode
	 * @param       object $api_object
	 * @return      array
	 */
	public function user_files( $data, $query_mode, $api_object ) {
		if ( 'my-files' != $query_mode ) {
			return $data;
		}

		$user_id      = $api_object->get_user();
		$user_pending = edd_user_pending_verification( $user_id );
		if ( $user_pending ) {
			wp_die( 'User not verified', 'User not verified', 403 );
		}

		$data['files']   = array();
		$purchases = edd_get_users_purchases( $user_id, -1, false );

		if ( $purchases ) {

			foreach ( $purchases as $payment ) {

				$payment   = edd_get_payment( $payment->ID );

				if ( ! empty( $payment->cart_details ) ) {

					foreach ( $payment->cart_details as $key => $item ) {

						$options  = isset( $item['item_number']['options'] ) ? $item['item_number']['options'] : array();
						$price_id = isset( $options['price_id'] ) ? $options['price_id'] : 0;
						$download = new EDD_Download( $item['id'] );
						if ( $download->ID > 0 ) {
							$this->get_download_files( $payment, $download, $price_id );
						}

					}

				}

			}

			$data['files'] = $this->found_files;

			$hours = absint( edd_get_option( 'download_link_expiration', 24 ) );
			$data['link_expiration'] = strtotime( '+' . $hours . 'hours', current_time( 'timestamp') );

		} else {
			wp_die( 'No files found', 'No files found', 404 );
		}

		return $data;
	}

	private function get_download_files( $payment, $download, $price_id ) {

		if ( $download->is_bundled_download() ) {
			foreach ( $download->bundled_downloads as $bundled_download ) {
				$bundled_download = new EDD_Download( $bundled_download );
				$this->get_download_files( $payment, $bundled_download, $price_id );
			}
			return;
		}

		$download_files = $download->get_files( $price_id );

		if ( ! isset( $this->found_files[ $download->ID ] ) ) {
			$this->found_files[ $download->ID ] = array( 'name' => $download->get_name() );
		}

		if ( ! empty( $download_files ) ) {

			foreach ( $download_files as $filekey => $file ) {
				if ( isset( $this->found_files[ $download->ID ][ $filekey ] ) ) {
					continue; // We've already found this file key
				}

				$download_url = edd_get_download_file_url( $payment->key, $payment->email, $filekey, $download->ID, $price_id );
				$this->found_files[ $download->ID ]['files'][ $filekey ] = array(
					'file_name'    => edd_get_file_name( $file ),
					'download_url' => $download_url,
				);

			}

		}

	}

}
new EDD_Purchased_Files_Rest_API;
