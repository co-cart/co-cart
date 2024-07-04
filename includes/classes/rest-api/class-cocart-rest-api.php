<?php
/**
 * CoCart Server
 *
 * Responsible for loading the REST API and all REST API namespaces.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   1.0.0 Introduced.
 * @version 4.1.0
 */

use WC_Customer as Customer;
use WC_Cart as Cart;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Responsible for loading the REST API and cache handling.
 *
 * @since 1.0.0 Introduced.
 */
class CoCart_REST_API {

	/**
	 * REST API namespaces and endpoints.
	 *
	 * @var array
	 */
	protected $namespaces = array();

	/**
	 * Controllers registered.
	 *
	 * @var array
	 */
	protected $controllers = array();

	/**
	 * Setup class.
	 *
	 * @access public
	 *
	 * @since 1.0.0 Introduced.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function __construct() {
		// If WooCommerce does not exists then do nothing!
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		// Register API namespaces.
		$this->rest_api_includes();
		$this->namespaces = $this->get_rest_namespaces();

		// Initialize cart.
		$this->maybe_load_cart();

		// Register REST routes.
		$this->register_rest_routes();

		// Prevents certain routes from being cached with WP REST API Cache plugin (https://wordpress.org/plugins/wp-rest-api-cache/).
		add_filter( 'rest_cache_skip', array( $this, 'prevent_cache' ), 10, 2 );

		// Prevent certain routes from being added to browser cache.
		add_filter( 'rest_post_dispatch', array( $this, 'send_cache_control' ), 12, 2 );

		// Cache Control.
		add_filter( 'rest_pre_serve_request', array( $this, 'cache_control' ), 0, 4 );
	} // END __construct()

	/**
	 * Register REST API routes.
	 *
	 * @access public
	 */
	public function register_rest_routes() {
		foreach ( $this->namespaces as $namespace => $controllers ) {
			foreach ( $controllers as $controller_name => $controller_class ) {
				if ( class_exists( $controller_class ) ) {
					$this->controllers[ $namespace ][ $controller_name ] = new $controller_class();
					$this->controllers[ $namespace ][ $controller_name ]->register_routes();
				}
			}
		}
	} // END register_rest_routes()

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
			)
		);
	} // END get_rest_namespaces()

	/**
	 * List of controllers in the cocart/v1 namespace.
	 *
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_v1_controllers() {
		return array(
			'cocart-v1-cart'                    => 'CoCart_API_Controller',
			'cocart-v1-add-item'                => 'CoCart_Add_Item_Controller',
			'cocart-v1-calculate'               => 'CoCart_Calculate_Controller',
			'cocart-v1-clear-cart'              => 'CoCart_Clear_Cart_Controller',
			'cocart-v1-count-items'             => 'CoCart_Count_Items_Controller',
			'cocart-v1-item'                    => 'CoCart_Item_Controller',
			'cocart-v1-logout'                  => 'CoCart_Logout_Controller',
			'cocart-v1-totals'                  => 'CoCart_Totals_Controller',
			'cocart-v1-product-attributes'      => 'CoCart_Product_Attributes_Controller',
			'cocart-v1-product-attribute-terms' => 'CoCart_Product_Attribute_Terms_Controller',
			'cocart-v1-product-categories'      => 'CoCart_Product_Categories_Controller',
			'cocart-v1-product-reviews'         => 'CoCart_Product_Reviews_Controller',
			'cocart-v1-product-tags'            => 'CoCart_Product_Tags_Controller',
			'cocart-v1-products'                => 'CoCart_Products_Controller',
			'cocart-v1-product-variations'      => 'CoCart_Product_Variations_Controller',
		);
	} // END get_v1_controllers()

	/**
	 * List of controllers in the cocart/v2 namespace.
	 *
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_v2_controllers() {
		return array(
			'cocart-v2-store'                   => 'CoCart_REST_Store_V2_Controller',
			'cocart-v2-cart'                    => 'CoCart_REST_Cart_V2_Controller',
			'cocart-v2-cart-add-item'           => 'CoCart_REST_Add_Item_V2_Controller',
			'cocart-v2-cart-add-items'          => 'CoCart_REST_Add_Items_V2_Controller',
			'cocart-v2-cart-item'               => 'CoCart_REST_Item_V2_Controller',
			'cocart-v2-cart-items'              => 'CoCart_REST_Items_V2_Controller',
			'cocart-v2-cart-items-count'        => 'CoCart_REST_Count_Items_V2_Controller',
			'cocart-v2-cart-update-item'        => 'CoCart_REST_Update_Item_V2_Controller',
			'cocart-v2-cart-remove-item'        => 'CoCart_REST_Remove_Item_V2_Controller',
			'cocart-v2-cart-restore-item'       => 'CoCart_REST_Restore_Item_V2_Controller',
			'cocart-v2-cart-calculate'          => 'CoCart_REST_Calculate_V2_Controller',
			'cocart-v2-cart-clear'              => 'CoCart_REST_Clear_Cart_V2_Controller',
			'cocart-v2-cart-update'             => 'CoCart_REST_Update_Cart_V2_Controller',
			'cocart-v2-cart-totals'             => 'CoCart_REST_Totals_V2_Controller',
			'cocart-v2-login'                   => 'CoCart_REST_Login_V2_Controller',
			'cocart-v2-logout'                  => 'CoCart_REST_Logout_V2_Controller',
			'cocart-v2-session'                 => 'CoCart_REST_Session_V2_Controller',
			'cocart-v2-sessions'                => 'CoCart_REST_Sessions_V2_Controller',
			'cocart-v2-product-attributes'      => 'CoCart_REST_Product_Attributes_V2_Controller',
			'cocart-v2-product-attribute-terms' => 'CoCart_REST_Product_Attribute_Terms_V2_Controller',
			'cocart-v2-product-categories'      => 'CoCart_REST_Product_Categories_V2_Controller',
			'cocart-v2-product-reviews'         => 'CoCart_REST_Product_Reviews_V2_Controller',
			'cocart-v2-product-tags'            => 'CoCart_REST_Product_Tags_V2_Controller',
			'cocart-v2-products'                => 'CoCart_REST_Products_V2_Controller',
			'cocart-v2-product-variations'      => 'CoCart_REST_Product_Variations_V2_Controller',
		);
	} // END get_v2_controllers()

	/**
	 * Controls the hooks that should be initialized for the current cart session.
	 *
	 * Thanks to a PR submitted to WooCommerce we now have more control on what is
	 * initialized for the cart session to improve performance.
	 *
	 * We prioritize the filter at "100" to make sure we don't interfere with
	 * any other plugins that may have already done the same at a lower priority.
	 *
	 * We are also filtering only during a CoCart REST API request not natively.
	 *
	 * @link https://github.com/woocommerce/woocommerce/pull/34156
	 *
	 * @access private
	 *
	 * @since 4.2.0 Introduced.
	 */
	private function initialize_cart_session() {
		add_filter( 'woocommerce_cart_session_initialize', function ( $must_initialize, $session ) {
			add_action( 'woocommerce_cart_emptied', array( $session, 'destroy_cart_session' ) );
			add_action( 'woocommerce_after_calculate_totals', array( $session, 'set_session' ), 1000 );
			add_action( 'woocommerce_cart_loaded_from_session', array( $session, 'set_session' ) );
			add_action( 'woocommerce_removed_coupon', array( $session, 'set_session' ) );

			return false;
		}, 100, 2 );
	} // END initialize_cart_session()

	/**
	 * Loads the session, customer and cart.
	 *
	 * Prevents initializing if none are required for the requested API endpoint.
	 *
	 * @access private
	 *
	 * @since 2.0.0 Introduced.
	 * @since 4.1.0 Initialize customer separately.
	 */
	private function maybe_load_cart() {
		if ( CoCart::is_rest_api_request() ) {

			// Check if we should prevent the requested route from initializing the session and cart.
			if ( $this->prevent_routes_from_initializing() ) {
				return;
			}

			// Require WooCommerce functions.
			require_once WC_ABSPATH . 'includes/wc-cart-functions.php';
			require_once WC_ABSPATH . 'includes/wc-notice-functions.php';

			// Initialize session.
			$this->initialize_session();

			// Initialize customer.
			$this->initialize_customer();

			// Initialize cart.
			$this->initialize_cart_session();
			$this->initialize_cart();
		}
	} // END maybe_load_cart()

	/**
	 * If the current customer ID in session does not match,
	 * then the user has switched.
	 *
	 * @access protected
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @deprecated 4.1.0 No replacement.
	 *
	 * @return null|boolean
	 */
	protected function has_user_switched() {
		cocart_deprecated_function( 'CoCart_REST_API::has_user_switched', __( 'User switching is now deprecated.', 'cart-rest-api-for-woocommerce' ), '4.1.0' );

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
				CoCart_Logger::log(
					sprintf(
						/* translators: %1$s is previous ID, %2$s is current ID. */
						__( 'User has changed! Was %1$s before and is now %2$s', 'cart-rest-api-for-woocommerce' ),
						$customer_id,
						$current_user_id
					),
					'info'
				);

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
	 * @since 2.1.0 Introduced.
	 *
	 * @deprecated 4.1.0 No replacement.
	 */
	public function user_switched() {
		cocart_deprecated_function( 'CoCart_REST_API::user_switched', __( 'User switching is now deprecated.', 'cart-rest-api-for-woocommerce' ), '4.1.0' );

		cocart_do_deprecated_action( 'cocart_user_switched', '4.1.0', null );
	} // END user_switched()

	/**
	 * Initialize session.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 */
	public function initialize_session() {
		if ( class_exists( 'WC_Session_Handler' ) ) {
			require_once COCART_FILE_PATH . '/includes/classes/class-cocart-session-handler.php';
		}

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
	 * Initialize customer.
	 *
	 * This allows us to control which customer is assigned to the session.
	 *
	 * @access public
	 *
	 * @since 4.1.0 Introduced.
	 */
	public function initialize_customer() {
		if ( is_null( WC()->customer ) || ! WC()->customer instanceof Customer ) {
			/**
			 * Filter allows to set the customer ID.
			 *
			 * @since 4.1.0 Introduced.
			 *
			 * @param int Current user ID.
			 */
			$customer_id = apply_filters( 'cocart_set_customer_id', get_current_user_id() );

			WC()->customer = new Customer( $customer_id, true );

			// Customer should be saved during shutdown.
			add_action( 'shutdown', array( WC()->customer, 'save' ), 10 );
		}
	} // END initialize_customer()

	/**
	 * Initialize cart.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 */
	public function initialize_cart() {
		if ( is_null( WC()->cart ) || ! WC()->cart instanceof Cart ) {
			WC()->cart = new Cart();
		}
	} // END initialize_cart()

	/**
	 * Include CoCart REST API controllers.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 3.1.0
	 */
	public function rest_api_includes() {
		// CoCart REST API v1 controllers.
		require_once __DIR__ . '/controllers/v1/cart/class-cocart-controller.php';
		require_once __DIR__ . '/controllers/v1/cart/class-cocart-add-item-controller.php';
		require_once __DIR__ . '/controllers/v1/cart/class-cocart-clear-cart-controller.php';
		require_once __DIR__ . '/controllers/v1/cart/class-cocart-calculate-controller.php';
		require_once __DIR__ . '/controllers/v1/cart/class-cocart-count-controller.php';
		require_once __DIR__ . '/controllers/v1/cart/class-cocart-item-controller.php';
		require_once __DIR__ . '/controllers/v1/cart/class-cocart-logout-controller.php';
		require_once __DIR__ . '/controllers/v1/cart/class-cocart-totals-controller.php';
		require_once __DIR__ . '/controllers/v1/products/class-cocart-abstract-terms-controller.php';
		require_once __DIR__ . '/controllers/v1/products/class-cocart-product-attribute-terms-controller.php';
		require_once __DIR__ . '/controllers/v1/products/class-cocart-product-attributes-controller.php';
		require_once __DIR__ . '/controllers/v1/products/class-cocart-product-categories-controller.php';
		require_once __DIR__ . '/controllers/v1/products/class-cocart-product-reviews-controller.php';
		require_once __DIR__ . '/controllers/v1/products/class-cocart-product-tags-controller.php';
		require_once __DIR__ . '/controllers/v1/products/class-cocart-products-controller.php';
		require_once __DIR__ . '/controllers/v1/products/class-cocart-product-variations-controller.php';

		// CoCart REST API v2 controllers.
		require_once __DIR__ . '/controllers/v2/others/class-cocart-store-controller.php';
		require_once __DIR__ . '/controllers/v2/others/class-cocart-login-controller.php';
		require_once __DIR__ . '/controllers/v2/others/class-cocart-logout-controller.php';
		require_once __DIR__ . '/controllers/v2/cart/class-cocart-cart-controller.php';
		require_once __DIR__ . '/controllers/v2/cart/class-cocart-add-item-controller.php';
		require_once __DIR__ . '/controllers/v2/cart/class-cocart-add-items-controller.php';
		require_once __DIR__ . '/controllers/v2/cart/class-cocart-item-controller.php';
		require_once __DIR__ . '/controllers/v2/cart/class-cocart-items-controller.php';
		require_once __DIR__ . '/controllers/v2/cart/class-cocart-clear-cart-controller.php';
		require_once __DIR__ . '/controllers/v2/cart/class-cocart-calculate-controller.php';
		require_once __DIR__ . '/controllers/v2/cart/class-cocart-count-controller.php';
		require_once __DIR__ . '/controllers/v2/cart/class-cocart-update-item-controller.php';
		require_once __DIR__ . '/controllers/v2/cart/class-cocart-remove-item-controller.php';
		require_once __DIR__ . '/controllers/v2/cart/class-cocart-restore-item-controller.php';
		require_once __DIR__ . '/controllers/v2/cart/class-cocart-totals-controller.php';
		require_once __DIR__ . '/controllers/v2/cart/class-cocart-update-cart-controller.php';
		require_once __DIR__ . '/controllers/v2/admin/class-cocart-session-controller.php';
		require_once __DIR__ . '/controllers/v2/admin/class-cocart-sessions-controller.php';
		require_once __DIR__ . '/controllers/v2/products/class-cocart-abstract-terms-controller.php';
		require_once __DIR__ . '/controllers/v2/products/class-cocart-product-attribute-terms-controller.php';
		require_once __DIR__ . '/controllers/v2/products/class-cocart-product-attributes-controller.php';
		require_once __DIR__ . '/controllers/v2/products/class-cocart-product-categories-controller.php';
		require_once __DIR__ . '/controllers/v2/products/class-cocart-product-reviews-controller.php';
		require_once __DIR__ . '/controllers/v2/products/class-cocart-product-tags-controller.php';
		require_once __DIR__ . '/controllers/v2/products/class-cocart-products-controller.php';
		require_once __DIR__ . '/controllers/v2/products/class-cocart-product-variations-controller.php';

		do_action( 'cocart_rest_api_controllers' );
	} // END rest_api_includes()

	/**
	 * Prevents certain routes from being cached.
	 *
	 * @access public
	 *
	 * @since 2.1.2 Introduced.
	 * @since 4.1.0 Check against allowed routes to determine if we should cache.
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
		/**
		 * Filter allows you set a path to which will prevent from being added to browser cache.
		 *
		 * @since 3.6.0 Introduced.
		 *
		 * @param array Default patterns.
		 */
		$regex_path_patterns = apply_filters(
			'cocart_send_cache_control_patterns',
			array(
				'#^/cocart/v2/cart?#',
				'#^/cocart/v2/logout?#',
				'#^/cocart/v2/store?#',
				'#^/cocart/v1/get-cart?#',
				'#^/cocart/v1/logout?#',
			)
		);

		foreach ( $regex_path_patterns as $regex_path_pattern ) {
			if ( ! empty( $_SERVER['REQUEST_URI'] ) && preg_match( $regex_path_pattern, sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) ) {
				if ( method_exists( $server, 'send_header' ) ) {
					$server->send_header( 'Cache-Control', 'no-cache, must-revalidate, max-age=0' );
				}
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
	 * @since 4.1.0 Check against allowed routes to determine if we should cache.
	 *
	 * @param bool             $served  Whether the request has already been served. Default false.
	 * @param WP_HTTP_Response $result  Result to send to the client. Usually a WP_REST_Response.
	 * @param WP_REST_Request  $request The request object.
	 * @param WP_REST_Server   $server  Server instance.
	 *
	 * @return null|bool
	 */
	public function cache_control( $served, $result, $request, $server ) {
		$regex_path_patterns = $this->allowed_regex_pattern_routes_to_cache();

		foreach ( $regex_path_patterns as $regex_path_pattern ) {
			if ( ! preg_match( $regex_path_pattern, $request->get_route() ) ) {
				if ( method_exists( $server, 'send_headers' ) ) {
					$headers['Expires']       = 'Thu, 01-Jan-70 00:00:01 GMT';
					$headers['Last-Modified'] = gmdate( 'D, d M Y H:i:s' ) . ' GMT';
					$headers['Cache-Control'] = 'post-check=0, pre-check=0';
					$headers['Cache-Control'] = 'no-store, no-cache, must-revalidate';
					$headers['Pragma']        = 'no-cache';

					$server->send_headers( $headers );
				}
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
			'cocart/v2/logout',
			'cocart/v1/products',
			'cocart/v2/products',
			'cocart/v2/sessions',
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
	 * @since 4.1.0 Introduced.
	 *
	 * @return array $routes Routes that can be cached.
	 */
	protected function allowed_regex_pattern_routes_to_cache() {
		return array(
			'#^/cocart/v2/products?#',
			'#^/cocart/v1/products?#',
		);
	} // END allowed_regex_pattern_routes_to_cache()
} // END class

return new CoCart_REST_API();
