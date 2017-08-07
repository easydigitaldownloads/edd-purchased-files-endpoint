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
		add_filter( 'edd_api_output_data',       array( $this, 'member_downloads' ), 10, 3 );
	}


	/**
	 * Add purchased products to the available query modes
	 *
	 * @access      public
	 * @param       array $query_modes The current query modes
	 * @return      array The adjusted query modes
	 */
	public function query_modes( $query_modes ) {
		$query_modes[] = 'my-files';

		if ( is_callable( 'rcp_edd_member_downloads_member_at_limit' ) ) {
			$query_modes[] = 'member-downloads';
		}

		return $query_modes;
	}

	/**
	 * Fetch user purchased products data
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

		$data['purchased_files']   = array();
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
			$data['purchased_files'] = $this->found_files;

			$hours = absint( edd_get_option( 'download_link_expiration', 24 ) );
			$data['link_expiration'] = strtotime( '+' . $hours . 'hours', current_time( 'timestamp') );

		} else {
			return array( 'error' => 'no-files-found', 'message' => 'No files found', 'status' => 404 );
		}

		return $data;
	}

	/**
	 * Fetch user rcp edd member downloads data
	 *
	 * @access      public
	 * @param       array $data
	 * @param       string $query_mode
	 * @param       object $api_object
	 * @return      array
	 */
	public function member_downloads( $data, $query_mode, $api_object ) {
		if ( 'member-downloads' != $query_mode ) {
			return $data;
		}

		$user_id      = $api_object->get_user();
		$user_pending = edd_user_pending_verification( $user_id );
		if ( $user_pending ) {
			wp_die( 'User not verified', 'User not verified', 403 );
		}

		if ( rcp_edd_member_downloads_member_at_limit( $user_id ) ) {
			return array( 'error' => 'member-at-limit', 'message' => 'Member has hit their download limit for the period.', 'status' => 404 );
		}

		$member = new RCP_Member( $user_id );

		$data['available_files']   = array();
		$query = array(
			'post_type'      => 'download',
			'orderby'        => 'title',
			'order'          => 'ASC',
			'posts_per_page' => -1,
			'paged'          => false,

		);
		$downloads   = new WP_Query( $query );
		$payment_key = get_user_meta( $user_id, 'has_membership_api_payment', true );

		if ( empty( $payment_key ) ) {

			remove_action( 'edd_complete_purchase', 'edd_trigger_purchase_receipt', 999, 3 );

			$download = get_page_by_title( 'RCP/EDD Member Download - API', OBJECT, 'download' );

			if ( empty( $download ) ) {
				$download_id = wp_insert_post( array(
					'post_type'   => 'download',
					'post_title'  => 'RCP/EDD Member Download - API',
					'post_status' => 'private',
				) );

			} else {

				$download_id = $download->ID;

			}

			$payment  = new EDD_Payment();
			$payment->add_download( $download_id );
			$payment->status  = 'publish';
			$payment->user_id = $member->ID;
			$payment->email   = $member->user_email;

			$payment->save();

			update_user_meta( $user_id, 'has_membership_api_payment', $payment->key );
			$payment_key = $payment->key;
		}

		if ( $downloads->have_posts() ) {

			while ( $downloads->have_posts() ) {
				$downloads->the_post();
				$download = new EDD_Download( get_the_ID() );

				// RCP EDD Member Downloads does not support bundles or variable pricing, yet.
				if ( $download->is_bundled_download() || $download->has_variable_prices() ) {
					continue;
				}

				$download_files = $download->get_files();

				if ( ! empty( $download_files ) ) {

					foreach ( $download_files as $filekey => $file ) {

						$download_url = edd_get_download_file_url( $payment_key, $member->user_email , $filekey, $download->ID );
						$data['available_files'][ $download->ID ]['files'][ $filekey ] = array(
							'file_name'    => edd_get_file_name( $file ),
							'download_url' => $download_url,
						);

					}

				}
			}

			$hours = absint( edd_get_option( 'download_link_expiration', 24 ) );
			$data['link_expiration'] = strtotime( '+' . $hours . 'hours', current_time( 'timestamp') );

		} else {
			return array( 'error' => 'no-files-found', 'message' => 'No files found' );
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
