<?php
/**
 * CoCart Rest API Server.
 *
 * Responsible for loading the REST API, cache handling and headers.
 *
 * @author  Sébastien Dumont
 * @package CoCart\RestApi
 * @since   1.0.0
 * @version 4.0.0
 */

namespace CoCart\RestApi;

use CoCart\RestApi\Authentication;
use CoCart\Logger;
use CoCart\Help;
use CoCart\Session\Handler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API class.
 */
class Server {

	/**
	 * REST API namespaces and endpoints.
	 *
	 * @var array
	 */
	protected $_namespaces = array();

	/**
	 * Setup class.
	 *
	 * @access public
	 *
	 * @since   1.0.0 Introduced.
	 * @version 4.0.0
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

		// Do not cache specific routes.
		$this->do_not_cache();

		$this->maybe_load_cart();
		$this->rest_api_includes();

		$this->_namespaces = $this->get_rest_namespaces();

		// Hook into WordPress ready to init the REST API as needed.
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ), 10 );

		// Prevents certain routes from being cached with WP REST API Cache plugin (https://wordpress.org/plugins/wp-rest-api-cache/).
		add_filter( 'rest_cache_skip', array( $this, 'prevent_cache' ), 10, 2 );

		// Prevent certain routes from being added to browser cache.
		add_filter( 'rest_post_dispatch', array( $this, 'send_cache_control' ), 12, 2 );

		// Cache Control.
		add_filter( 'rest_pre_serve_request', array( $this, 'cache_control' ), 0, 4 );

		// Sends the cart key and customer ID to the header.
		add_filter( 'rest_authentication_errors', array( $this, 'cocart_key_header' ), 20, 1 );
		add_filter( 'rest_authentication_errors', array( $this, 'cocart_requested_customer_header' ), 20, 1 );
	} // END __construct()

	/**
	 * Register REST API routes.
	 *
	 * @access public
	 */
	public function register_rest_routes() {
		foreach ( $this->_namespaces as $namespace => $controllers ) {
			foreach ( $controllers as $controller_name => $controller_class ) {
				if ( class_exists( $controller_class ) ) {
					$this->controllers[ $namespace ][ $controller_name ] = new $controller_class();

					// Register the legacy way.
					if ( method_exists( $this->controllers[ $namespace ][ $controller_name ], 'register_routes' ) ) {
						$this->controllers[ $namespace ][ $controller_name ]->register_routes();
					}

					// Register the new way temporarily here.
					// Todo: Remove this when stable.
					if ( $namespace === 'cocart/v3' ) {
						if (
							method_exists( $this->controllers[ $namespace ][ $controller_name ], 'get_endpoint' ) &&
							method_exists( $this->controllers[ $namespace ][ $controller_name ], 'get_path' ) &&
							method_exists( $this->controllers[ $namespace ][ $controller_name ], 'get_args' )
						) {
							$endpoint = $this->controllers[ $namespace ][ $controller_name ]->get_endpoint();
							$path     = $this->controllers[ $namespace ][ $controller_name ]->get_path();
							$args     = $this->controllers[ $namespace ][ $controller_name ]->get_args();
							// cocart_register_rest_route( $endpoint, $path, $args );
						}
					}
				}
			}
		}
	}

	/**
	 * Get API namespaces - new namespaces should be registered here.
	 *
	 * @access protected
	 *
	 * @return array List of Namespaces and Main controller classes.
	 */
	protected function get_rest_namespaces() {
		return apply_filters(
			'cocart_rest_api_get_rest_namespaces',
			array(
				'cocart/v1' => $this->get_v1_controllers(),
				'cocart/v2' => $this->get_v2_controllers(),
				'cocart/v3' => $this->get_v3_controllers(),
			)
		);
	}

	/**
	 * List of controllers in the cocart/v1 namespace.
	 *
	 * @access protected
	 *
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
	 *
	 * @return array
	 */
	protected function get_v2_controllers() {
		return array(
			'cocart-v2-store'             => 'CoCart_REST_Store_V2_Controller',
			'cocart-v2-cart'              => 'CoCart_REST_Cart_V2_Controller',
			'cocart-v2-cart-add-item'     => 'CoCart_REST_Add_Item_v2_Controller',
			'cocart-v2-cart-add-items'    => 'CoCart_REST_Add_Items_v2_Controller',
			'cocart-v2-cart-item'         => 'CoCart_REST_Item_v2_Controller',
			'cocart-v2-cart-items'        => 'CoCart_REST_Items_v2_Controller',
			'cocart-v2-cart-items-count'  => 'CoCart_REST_Count_Items_v2_Controller',
			'cocart-v2-cart-update-item'  => 'CoCart_REST_Update_Item_v2_Controller',
			'cocart-v2-cart-remove-item'  => 'CoCart_REST_Remove_Item_v2_Controller',
			'cocart-v2-cart-restore-item' => 'CoCart_REST_Restore_Item_v2_Controller',
			'cocart-v2-cart-calculate'    => 'CoCart_REST_Calculate_v2_Controller',
			'cocart-v2-cart-clear'        => 'CoCart_REST_Clear_Cart_v2_Controller',
			'cocart-v2-cart-update'       => 'CoCart_REST_Update_Cart_v2_Controller',
			'cocart-v2-cart-totals'       => 'CoCart_REST_Totals_v2_Controller',
			'cocart-v2-login'             => 'CoCart_REST_Login_v2_Controller',
			'cocart-v2-logout'            => 'CoCart_REST_Logout_v2_Controller',
		);
	}

	/**
	 * List of controllers in the cocart/v3 namespace.
	 *
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_v3_controllers() {
		return array(
			'cocart-v3-test' => 'CoCart_Test_v3_Controller',
		);
	}

	/**
	 * Loads the cart, session and notices should it be required.
	 *
	 * @access private
	 *
	 * @since   2.0.0 Introduced.
	 * @version 4.0.0
	 */
	private function maybe_load_cart() {
		if ( Authentication::is_rest_api_request() ) {

			// Check if we should prevent the requested route from initializing the session and cart.
			if ( $this->prevent_routes_from_initializing() ) {
				return;
			}

			// WooCommerce is greater than v3.6 or less than v4.5.
			if ( Help::is_wc_version_gte( '3.6' ) && Help::is_wc_version_lt( '4.5' ) ) {
				require_once WC_ABSPATH . 'includes/wc-cart-functions.php';
				require_once WC_ABSPATH . 'includes/wc-notice-functions.php';

				// Initialize session.
				$this->initialize_session();

				// Initialize cart.
				$this->initialize_cart();
			}

			// WooCommerce is greater than v4.5 or equal.
			if ( Help::is_wc_version_gte( '4.5' ) ) {
				if ( is_null( WC()->cart ) && function_exists( 'wc_load_cart' ) ) {
					wc_load_cart();
				}
			}
		}
	} // END maybe_load_cart()

	/**
	 * If the current customer ID in session does not match,
	 * then the user has switched.
	 *
	 * @access protected
	 *
	 * @since      2.1.0 Introduced.
	 * @deprecated 4.0.0 No replacement.
	 * @version    4.0.0
	 *
	 * @return null|boolean
	 */
	protected function has_user_switched() {
		_deprecated_function( __FUNCTION__, 'User switching is now deprecated and will be removed in the future.', '4.0.0' );

		if ( ! WC()->session instanceof Handler ) {
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
				Logger::log( sprintf( __( 'User has changed! Was %1$s before and is now %2$s', 'cart-rest-api-for-woocommerce' ), $customer_id, $current_user_id ), 'info' );

				return true;
			}
		}

		return false;
	} // END has_user_switched()

	/**
	 * Allows something to happen if a user has switched.
	 *
	 * @access public
	 *
	 * @since      2.1.0 Introduced.
	 * @deprecated 4.0.0 No replacement.
	 */
	public function user_switched() {
		_deprecated_function( __FUNCTION__, 'User switching is now deprecated and will be removed in the future.', '4.0.0' );

		_deprecated_hook( 'cocart_user_switched', '4.0.0', '', '"cocart_user_switched" hook no longer used and will be removed in the future.' );

		do_action( 'cocart_user_switched' );
	} // END user_switched()

	/**
	 * Initialize session.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 */
	public function initialize_session() {
		if ( is_null( WC()->session ) || ! WC()->session instanceof Handler ) {
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
	 *
	 * @since 2.1.0 Introduced.
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
	 * @access public
	 *
	 * @since 1.0.0 Introduced.
	 * @since 4.0.0 Use autoloader generated by Composer to load REST API controllers instead.
	 */
	public function rest_api_includes() {
		do_action( 'cocart_rest_api_controllers' );
	} // rest_api_includes()

	/**
	 * Prevents certain routes from being cached.
	 *
	 * @access public
	 *
	 * @since 2.1.2 Introduced.
	 * @since 4.0.0 Check against allowed routes to determine if we should cache.
	 *
	 * @param bool   $skip ( default: WP_DEBUG ).
	 * @param string $request_uri Requested REST API.
	 *
	 * @return bool $skip Results to WP_DEBUG or true if CoCart requested.
	 */
	public function prevent_cache( $skip, $request_uri ) {
		$rest_prefix = trailingslashit( rest_get_url_prefix() );

		$regex_path_patterns = $this->allowed_regex_pattern_routes_to_cache();

		foreach ( $regex_path_patterns as $regex_path_pattern ) {
			if ( ! preg_match( $regex_path_pattern, $request_uri ) ) {
				return true;
			}
		}

		return $skip;
	} // END prevent_cache()

	/**
	 * Sends the cart key to the header if a cart exists.
	 *
	 * @access public
	 *
	 * @since   2.7.0 Introduced.
	 * @version 4.0.0
	 *
	 * @param WP_Error|null|true $result WP_Error if authentication error, null if authentication
	 *                                    method wasn't used, true if authentication succeeded.
	 *
	 * @return WP_Error|true $result WP_Error if authentication error, true if authentication succeeded.
	 */
	public function cocart_key_header( $result ) {
		if ( ! empty( $result ) ) {
			return $result;
		}

		// Check that the CoCart session handler has loaded.
		if ( ! class_exists( 'CoCart\Session\Handler' ) || ! WC()->session instanceof Handler ) {
			return $result;
		}

		$cart_key = WC()->session->get_cart_key();

		// Send cart key in the header if it's not empty or ZERO.
		if ( ! empty( $cart_key ) && '0' !== $cart_key ) {
			rest_get_server()->send_header( 'CoCart-API-Cart-Key', $cart_key );
		}

		return true;
	} // END cocart_key_header()

	/**
	 * Sends the requested customer ID to the header.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param WP_Error|null|true $result WP_Error if authentication error, null if authentication
	 *                                    method wasn't used, true if authentication succeeded.
	 *
	 * @return WP_Error|true $result WP_Error if authentication error, true if authentication succeeded.
	 */
	public function cocart_requested_customer_header( $result ) {
		if ( ! empty( $result ) ) {
			return $result;
		}

		// Check that the CoCart session handler has loaded.
		if ( ! class_exists( 'CoCart\Session\Handler' ) || ! WC()->session instanceof Handler ) {
			return $result;
		}

		$customer_id = WC()->session->get_requested_customer();

		// Send customer ID in the header if it's not empty or ZERO.
		if ( ! empty( $customer_id ) && '0' !== $customer_id ) {
			rest_get_server()->send_header( 'CoCart-API-Customer', $customer_id );
		}

		return true;
	} // END cocart_requested_customer_header()

	/**
	 * Helps prevent certain routes from being added to browser cache.
	 *
	 * @access public
	 *
	 * @since 3.6.0 Introduced.
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param object           $server   The REST server.
	 *
	 * @return WP_REST_Response $response The response object.
	 **/
	public function send_cache_control( $response, $server ) {
		$regex_path_patterns = apply_filters( 'cocart_send_cache_control_patterns', array(
			'#^/cocart/v2/cart?#',
			'#^/cocart/v2/logout?#',
			'#^/cocart/v2/store?#',
			'#^/cocart/v1/get-cart?#',
			'#^/cocart/v1/logout?#',
		) );

		foreach ( $regex_path_patterns as $regex_path_pattern ) {
			if ( preg_match( $regex_path_pattern, $_SERVER['REQUEST_URI'] ) ) {
				$server->send_header( 'Cache-Control', 'no-cache, must-revalidate, max-age=0' );
			}
		}

		return $response;
	} // END send_cache_control()

	/**
	 * Helps prevent CoCart from being cached on most routes and returns results quicker.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 * @since 4.0.0 Check against allowed routes to determine if we should cache.
	 *
	 * @param bool             $served  Whether the request has already been served. Default false.
	 * @param WP_HTTP_Response $result  Result to send to the client. Usually a WP_REST_Response.
	 * @param WP_REST_Request  $request Request used to generate the response.
	 * @param WP_REST_Server   $server  Server instance.
	 *
	 * @return null|bool
	 */
	public function cache_control( $served, $result, $request, $server ) {
		$regex_path_patterns = $this->allowed_regex_pattern_routes_to_cache();

		foreach ( $regex_path_patterns as $regex_path_pattern ) {
			if ( ! preg_match( $regex_path_pattern, $request->get_route() ) ) {
				header( 'Expires: Thu, 01-Jan-70 00:00:01 GMT' );
				header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
				header( 'Cache-Control: no-store, no-cache, must-revalidate' );
				header( 'Cache-Control: post-check=0, pre-check=0', false );
				header( 'Pragma: no-cache' );
			}
		}

		return $served;
	} // END cache_control()

	/**
	 * Prevents certain routes from initializing the session and cart.
	 *
	 * @access protected
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @return bool
	 */
	protected function prevent_routes_from_initializing() {
		$rest_prefix = trailingslashit( rest_get_url_prefix() );
		$request_uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated

		$routes = array(
			'cocart/v2/login',
			'cocart/v1/products',
			'cocart/v2/products',
			'cocart/v2/sessions',
			'cocart/v2/session',
			'cocart/v2/store',
		);

		foreach ( $routes as $route ) {
			if ( ( false !== strpos( $request_uri, $rest_prefix . $route ) ) ) {
				return true;
			}
		}
	} // END prevent_routes_from_initializing()

	/**
	 * Returns routes that can be cached as a regex pattern.
	 *
	 * @access protected
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @return array $routes Routes that can be cached.
	 */
	protected function allowed_regex_pattern_routes_to_cache() {
		return array(
			'#^/cocart/v2/products?#',
			'#^/cocart/v1/products?#',
		);
	} // END allowed_regex_pattern_routes_to_cache()

	/**
	 * Does not cache specific routes.
	 *
	 * @access protected
	 *
	 * @since 4.0.0 Introduced.
	 */
	protected function do_not_cache() {
		$regex_path_patterns = $this->allowed_regex_pattern_routes_to_cache();

		foreach ( $regex_path_patterns as $regex_path_pattern ) {
			if ( ! preg_match( $regex_path_pattern, $_SERVER['REQUEST_URI'] ) ) {
				if ( ! defined( 'DONOTCACHEPAGE' ) ) {
					define( 'DONOTCACHEPAGE', true ); // Play nice with WP-Super-Cache plugin (https://wordpress.org/plugins/wp-super-cache/).
				}

				/**
				 * This hook "cocart_do_not_cache" allows third party plugins to inject no caching.
				 */
				do_action( 'cocart_' . __FUNCTION__ );
			}
		}
	} // END do_not_cache()

} // END class

return new Server();
