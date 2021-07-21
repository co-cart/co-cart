<?php
/**
 * CoCart Server
 *
 * Responsible for loading the REST API and all REST API namespaces.
 *
 * @author   Sébastien Dumont
 * @package  CoCart\Classes
 * @since    1.0.0
 * @version  3.0.0
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API class.
 */
class CoCart_REST_API {

	/**
	 * Setup class.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 3.0.0
	 */
	public function __construct() {
		// REST API was included starting WordPress 4.4.
		if ( ! class_exists( 'WP_REST_Server' ) ) {
			return;
		}

		// If WooCommerce does not exists then do nothing!
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$this->maybe_load_cart();
		$this->rest_api_includes();

		// Hook into WordPress ready to init the REST API as needed.
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ), 10 );

		// Prevent CoCart from being cached with WP REST API Cache plugin (https://wordpress.org/plugins/wp-rest-api-cache/).
		add_filter( 'rest_cache_skip', array( $this, 'prevent_cache' ), 10, 2 );

		// Sends the cart key to the header.
		add_filter( 'rest_authentication_errors', array( $this, 'cocart_key_header' ), 20, 1 );
	} // END __construct()

	/**
	 * Register REST API routes.
	 *
	 * @access public
	 */
	public function register_rest_routes() {
		foreach ( $this->get_rest_namespaces() as $namespace => $controllers ) {
			foreach ( $controllers as $controller_name => $controller_class ) {
				if ( class_exists( $controller_class ) ) {
					$this->controllers[ $namespace ][ $controller_name ] = new $controller_class();
					$this->controllers[ $namespace ][ $controller_name ]->register_routes();
				}
			}
		}
	}

	/**
	 * Get API namespaces - new namespaces should be registered here.
	 *
	 * @access protected
	 * @return array List of Namespaces and Main controller classes.
	 */
	protected function get_rest_namespaces() {
		return apply_filters(
			'cocart_rest_api_get_rest_namespaces',
			array(
				'wc/v2'     => $this->get_legacy_controller(),
				'cocart/v1' => $this->get_v1_controllers(),
				'cocart/v2' => $this->get_v2_controllers(),
			)
		);
	}

	/**
	 * List of controllers in the wc/v2 namespace.
	 *
	 * @access protected
	 * @return array
	 */
	protected function get_legacy_controller() {
		return array(
			'wc-rest-cart' => 'WC_REST_Cart_Controller',
		);
	}

	/**
	 * List of controllers in the cocart/v1 namespace.
	 *
	 * @access protected
	 * @return array
	 */
	protected function get_v1_controllers() {
		return array(
			'cocart-v1-cart'        => 'CoCart_API_Controller',
			'cocart-v1-add-item'    => 'CoCart_Add_Item_Controller',
			'cocart-v1-calculate'   => 'CoCart_Calculate_Controller',
			'cocart-v1-clear-cart'  => 'CoCart_Clear_Cart_Controller',
			'cocart-v1-count-items' => 'CoCart_Count_Items_Controller',
			'cocart-v1-item'        => 'CoCart_Item_Controller',
			'cocart-v1-logout'      => 'CoCart_Logout_Controller',
			'cocart-v1-totals'      => 'CoCart_Totals_Controller',
		);
	}

	/**
	 * List of controllers in the cocart/v2 namespace.
	 *
	 * @access protected
	 * @return array
	 */
	protected function get_v2_controllers() {
		return array(
			'cocart-v2-store'             => 'CoCart_Store_V2_Controller',
			'cocart-v2-cart'              => 'CoCart_Cart_V2_Controller',
			'cocart-v2-cart-add-item'     => 'CoCart_Add_Item_v2_Controller',
			'cocart-v2-cart-add-items'    => 'CoCart_Add_Items_v2_Controller',
			'cocart-v2-cart-item'         => 'CoCart_Item_v2_Controller',
			'cocart-v2-cart-items'        => 'CoCart_Items_v2_Controller',
			'cocart-v2-cart-items-count'  => 'CoCart_Count_Items_v2_Controller',
			'cocart-v2-cart-update-item'  => 'CoCart_Update_Item_v2_Controller',
			'cocart-v2-cart-remove-item'  => 'CoCart_Remove_Item_v2_Controller',
			'cocart-v2-cart-restore-item' => 'CoCart_Restore_Item_v2_Controller',
			'cocart-v2-cart-calculate'    => 'CoCart_Calculate_v2_Controller',
			'cocart-v2-cart-clear'        => 'CoCart_Clear_Cart_v2_Controller',
			'cocart-v2-cart-totals'       => 'CoCart_Totals_v2_Controller',
			'cocart-v2-login'             => 'CoCart_Login_v2_Controller',
			'cocart-v2-logout'            => 'CoCart_Logout_v2_Controller',
			'cocart-v2-session'           => 'CoCart_Session_V2_Controller',
			'cocart-v2-sessions'          => 'CoCart_Sessions_V2_Controller',
		);
	}

	/**
	 * Loads the cart, session and notices should it be required.
	 *
	 * @access  private
	 * @since   2.0.0
	 * @version 3.0.0
	 */
	private function maybe_load_cart() {
		if ( CoCart_Authentication::is_rest_api_request() ) {
			// WooCommerce is greater than v3.6 or less than v4.5.
			if ( CoCart_Helpers::is_wc_version_gte_3_6() && CoCart_Helpers::is_wc_version_lt_4_5() ) {
				require_once WC_ABSPATH . 'includes/wc-cart-functions.php';
				require_once WC_ABSPATH . 'includes/wc-notice-functions.php';

				// Initialize session.
				$this->initialize_session();

				// Initialize cart.
				$this->initialize_cart();
			}

			// WooCommerce is greater than v4.5 or equal.
			if ( CoCart_Helpers::is_wc_version_gte_4_5() ) {
				if ( is_null( WC()->cart ) && function_exists( 'wc_load_cart' ) ) {
					wc_load_cart();
				}
			}

			// Identify if user has switched.
			if ( $this->has_user_switched() ) {
				$this->user_switched();
			}
		}
	} // END maybe_load_cart()

	/**
	 * If the current customer ID in session does not match,
	 * then the user has switched.
	 *
	 * @access  protected
	 * @since   2.1.0
	 * @version 2.7.2
	 * @return  null|boolean
	 */
	protected function has_user_switched() {
		if ( ! WC()->session instanceof CoCart_Session_Handler ) {
			return;
		}

		// Get cart cookie... if any.
		$cookie = WC()->session->get_session_cookie();

		// Current user ID. If user is NOT logged in then the customer is a guest.
		$current_user_id = strval( get_current_user_id() );

		// Does a cookie exist?
		if ( $cookie ) {
			$customer_id = $cookie[0];

			// If the user is logged in and does not match ID in cookie then user has switched.
			if ( $customer_id !== $current_user_id && 0 !== $current_user_id ) {
				/* translators: %1$s is previous ID, %2$s is current ID. */
				CoCart_Logger::log( sprintf( __( 'User has changed! Was %1$s before and is now %2$s', 'cart-rest-api-for-woocommerce' ), $customer_id, $current_user_id ), 'info' );

				return true;
			}
		}

		return false;
	} // END has_user_switched()

	/**
	 * Allows something to happen if a user has switched.
	 *
	 * @access public
	 * @since  2.1.0
	 */
	public function user_switched() {
		do_action( 'cocart_user_switched' );
	} // END user_switched()

	/**
	 * Initialize session.
	 *
	 * @access public
	 * @since  2.1.0
	 */
	public function initialize_session() {
		// CoCart session handler class.
		$session_class = 'CoCart_Session_Handler';

		if ( is_null( WC()->session ) || ! WC()->session instanceof $session_class ) {
			// Prefix session class with global namespace if not already namespaced.
			if ( false === strpos( $session_class, '\\' ) ) {
				$session_class = '\\' . $session_class;
			}

			// Initialize new session.
			WC()->session = new $session_class();
			WC()->session->init();
		}
	} // END initialize_session()

	/**
	 * Initialize cart.
	 *
	 * @access public
	 * @since  2.1.0
	 */
	public function initialize_cart() {
		if ( is_null( WC()->customer ) || ! WC()->customer instanceof WC_Customer ) {
			$customer_id = strval( get_current_user_id() );

			WC()->customer = new WC_Customer( $customer_id, true );

			// Customer should be saved during shutdown.
			add_action( 'shutdown', array( WC()->customer, 'save' ), 10 );
		}

		if ( is_null( WC()->cart ) || ! WC()->cart instanceof WC_Cart ) {
			WC()->cart = new WC_Cart();
		}
	} // END initialize_cart()

	/**
	 * Include CoCart REST API controllers.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 3.0.0
	 */
	public function rest_api_includes() {
		// Only include Legacy REST API if WordPress is v5.4.2 or lower.
		if ( CoCart_Helpers::is_wp_version_lt( '5.4.2' ) ) {
			// Legacy - WC Cart REST API v2 controller.
			include_once dirname( __FILE__ ) . '/api/legacy/wc-v2/class-wc-rest-cart-controller.php';
		}

		// CoCart REST API v1 controllers.
		include_once dirname( __FILE__ ) . '/api/cocart/v1/class-cocart-controller.php';
		include_once dirname( __FILE__ ) . '/api/cocart/v1/class-cocart-add-item-controller.php';
		include_once dirname( __FILE__ ) . '/api/cocart/v1/class-cocart-clear-cart-controller.php';
		include_once dirname( __FILE__ ) . '/api/cocart/v1/class-cocart-calculate-controller.php';
		include_once dirname( __FILE__ ) . '/api/cocart/v1/class-cocart-count-controller.php';
		include_once dirname( __FILE__ ) . '/api/cocart/v1/class-cocart-item-controller.php';
		include_once dirname( __FILE__ ) . '/api/cocart/v1/class-cocart-logout-controller.php';
		include_once dirname( __FILE__ ) . '/api/cocart/v1/class-cocart-totals-controller.php';

		// CoCart REST API v2 controllers.
		include_once dirname( __FILE__ ) . '/api/class-cocart-store-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-cocart-cart-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-cocart-add-item-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-cocart-add-items-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-cocart-item-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-cocart-items-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-cocart-clear-cart-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-cocart-calculate-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-cocart-count-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-cocart-update-item-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-cocart-remove-item-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-cocart-restore-item-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-cocart-login-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-cocart-logout-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-cocart-totals-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-cocart-session-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-cocart-sessions-controller.php';

		do_action( 'cocart_rest_api_controllers' );
	} // rest_api_includes()

	/**
	 * Prevents CoCart from being cached.
	 *
	 * @access public
	 * @since  2.1.2
	 * @param  bool   $skip ( default: WP_DEBUG ).
	 * @param  string $request_uri Requested REST API.
	 * @return bool   $skip Results to WP_DEBUG or true if CoCart requested.
	 */
	public function prevent_cache( $skip, $request_uri ) {
		$rest_prefix = trailingslashit( rest_get_url_prefix() );

		if ( strpos( $request_uri, $rest_prefix . 'cocart/' ) !== false ) {
			return true;
		}

		return $skip;
	} // END prevent_cache()

	/**
	 * Sends the cart key to the header if a cart exists.
	 *
	 * @access  public
	 * @since   2.7.0
	 * @version 3.0.0
	 * @param   WP_Error|null|true $result WP_Error if authentication error, null if authentication
	 *                                      method wasn't used, true if authentication succeeded.
	 * @return  WP_Error|true $result WP_Error if authentication error, true if authentication succeeded.
	 */
	public function cocart_key_header( $result ) {
		if ( ! empty( $result ) ) {
			return $result;
		}

		// Check that the CoCart session handler has loaded.
		if ( ! WC()->session instanceof CoCart_Session_Handler ) {
			return $result;
		}

		// Customer ID used as the cart key by default.
		$cart_key = WC()->session->get_customer_id();

		// Get cart cookie... if any.
		$cookie = WC()->session->get_session_cookie();

		// If a cookie exist, override cart key.
		if ( $cookie ) {
			$cart_key = $cookie[0];
		}

		// Check if we requested to load a specific cart.
		$cart_key = isset( $_REQUEST['cart_key'] ) ? trim( sanitize_key( wp_unslash( $_REQUEST['cart_key'] ) ) ) : $cart_key; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Send cart key in the header if it's not empty or ZERO.
		if ( ! empty( $cart_key ) && '0' !== $cart_key ) {
			rest_get_server()->send_header( 'X-CoCart-API', $cart_key );
		}

		return true;
	} // END cocart_key_header()

} // END class

return new CoCart_REST_API();
