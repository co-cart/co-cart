<?php
/**
 * CoCart - WooCommerce Admin: 6 things you can do CoCart Products API.
 *
 * Adds a note for the client giving a helping hand with accessing products via API.
 *
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart/Admin/WooCommerce Admin/Notes
 * @since    2.3.0
 * @license  GPL-2.0+
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
	 * @param $note_name  Note name.
	 * @param $seconds    How many seconds since CoCart was installed before the notice is shown.
	 * @param $source     Source of the note.
	 */
	public static function add_note( $note_name = '', $seconds = '', $source = 'cocart' ) {
		parent::add_note( $note_name, $seconds, $source );

		$args = self::get_note_args();

		// If no arguments return then we cant create a note.
		if ( is_array( $args ) && empty( $args ) ) {
			return;
		}

		// Don't add note if there are products.
		$query    = new \WC_Product_Query(
			array(
				'limit'    => 1,
				'paginate' => true,
				'return'   => 'ids',
				'status'   => array( 'publish' ),
			)
		);

		$products = $query->get_products();
		$count    = $products->total;

		if ( 0 !== $count ) {
			return;
		}

		// Otherwise, create new note.
		self::create_new_note( $args );
	} // END add_note()

	/**
	 * Get note arguments.
	 *
	 * @access public
	 * @static
	 * @return array
	 */
	public static function get_note_args() {
		$args = array(
			'title'   => sprintf( __( '6 things you can do %s', 'cart-rest-api-for-woocommerce' ), 'CoCart Products' ),
			'content' => sprintf( __( 'Fetching your products via the REST API should be easy with no authentication issues. Learn more about the six things you can do with %1$s to help your development with %2$s.', 'cart-rest-api-for-woocommerce' ), 'CoCart Products', 'CoCart' ),
			'type'    => Automattic\WooCommerce\Admin\Notes\WC_Admin_Note::E_WC_ADMIN_NOTE_MARKETING,
			'name'    => self::NOTE_NAME,
			'actions' => array(
				array(
					'name'    => 'cocart-learn-more-products',
					'label'   => __( 'Learn more', 'cart-rest-api-for-woocommerce' ),
					'url'     => 'https://cocart.xyz/6-things-you-can-do-with-cocart-products/?utm_source=inbox',
					'status'  => Automattic\WooCommerce\Admin\Notes\WC_Admin_Note::E_WC_ADMIN_NOTE_UNACTIONED,
					'primary' => true
				)
			)
		);

		return $args;
	} // END get_note_args()

} // END class

return new CoCart_WC_Admin_Do_With_Products_Note();