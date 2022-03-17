<?php
/**
 * CoCart - WooCommerce Admin: 6 things you can do with Products REST API.
 *
 * Adds a note for the client giving a helping hand with accessing products via API.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\WooCommerce Admin\Notes
 * @since   2.3.0
 * @version 3.2.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CoCart_WC_Admin_Do_With_Products_Note extends CoCart_WC_Admin_Notes {

	/**
	 * Name of the note for use in the database.
	 */
	const NOTE_NAME = 'cocart-wc-admin-do-with-products';

	/**
	 * Constructor
	 */
	public function __construct() {
		self::add_note( self::NOTE_NAME );
	}

	/**
	 * Add note.
	 *
	 * @access public
	 * @static
	 * @param string $note_name Note name.
	 * @param string $seconds   How many seconds since CoCart was installed before the notice is shown.
	 * @param string $source    Source of the note.
	 */
	public static function add_note( $note_name = '', $seconds = '', $source = 'cocart' ) {
		parent::add_note( $note_name, $seconds, $source );

		$args = self::get_note_args();

		// If no arguments return then we cant create a note.
		if ( is_array( $args ) && empty( $args ) ) {
			return;
		}

		// Don't add note if there are products.
		$query = new \WC_Product_Query(
			array(
				'limit'    => 1,
				'paginate' => true,
				'return'   => 'ids',
				'status'   => array( 'publish' ),
			)
		);

		$products = $query->get_products();
		$count    = $products->total;

		if ( $count <= 0 ) {
			return;
		}

		// Otherwise, create new note.
		self::create_new_note( $args );
	} // END add_note()

	/**
	 * Get note arguments.
	 *
	 * @access  public
	 * @static
	 * @since   2.3.0 Introduced.
	 * @since   3.2.0 Dropped support for WooCommerce less than version 4.8
	 * @version 3.2.0
	 * @return  array
	 */
	public static function get_note_args() {
		$type   = Automattic\WooCommerce\Admin\Notes\Note::E_WC_ADMIN_NOTE_MARKETING;
		$status = Automattic\WooCommerce\Admin\Notes\Note::E_WC_ADMIN_NOTE_UNACTIONED;

		$campaign_args = CoCart_Helpers::cocart_campaign(
			array(
				'utm_campaign' => 'wc-admin',
				'utm_content'  => 'wc-inbox',
			)
		);

		$args = array(
			'title'   => __( '6 things you can do with Products REST API', 'cart-rest-api-for-woocommerce' ),
			'content' => sprintf(
				/* translators: %s: CoCart */
				__( 'Fetching your products via the REST API is now easy. Learn more about the six things you can do with the products API to help your development with %s.', 'cart-rest-api-for-woocommerce' ),
				'CoCart'
			),
			'type'    => $type,
			'layout'  => 'thumbnail',
			'image'   => esc_url( COCART_STORE_URL . 'wp-content/uploads/2020/03/rwmibqmoxry-128x214.jpg' ),
			'name'    => self::NOTE_NAME,
			'actions' => array(
				array(
					'name'    => 'cocart-learn-more-products',
					'label'   => __( 'Learn more', 'cart-rest-api-for-woocommerce' ),
					'url'     => CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( COCART_STORE_URL . '6-things-you-can-do-with-cocart-products/' ) ) ),
					'status'  => $status,
					'primary' => true,
				),
			),
		);

		return $args;
	} // END get_note_args()

} // END class

return new CoCart_WC_Admin_Do_With_Products_Note();
