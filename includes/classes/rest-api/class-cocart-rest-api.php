<?php
/**
 * CoCart Server
 *
 * Responsible for loading the REST API and all REST API namespaces.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   1.0.0 Introduced.
 * @version 5.0.0
 * @license GPL-3.0
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
	protected $routes = array();

	/**
	 * This stores routes registered to prevent them from registering again by mistake.
	 */
	protected $registered_routes = array();

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

		// Register API routes.
		$this->rest_api_includes();
		$this->routes = $this->get_rest_namespaces();

		// Initialize cart.
		$this->maybe_load_cart();

		// Register REST routes.
		$this->register_all_routes();

		// Prevents certain routes from being cached with WP REST API Cache plugin (https://wordpress.org/plugins/wp-rest-api-cache/).
		add_filter( 'rest_cache_skip', array( $this, 'prevent_cache' ), 10, 2 );

		// Set Cache Headers.
		add_filter( 'rest_pre_serve_request', array( $this, 'set_cache_control_headers' ), 2, 4 );

		// Enhanced error handling and allows for final modifications to the response before returning.
		add_filter( 'rest_request_after_callbacks', array( $this, 'handle_rest_response' ), 10, 3 );
	} // END __construct()

	/**
	 * Get API namespaces - Namespaces should be registered here.
	 *
	 * @access protected
	 *
	 * @return array List of Namespaces and Main controller classes.
	 */
	protected function get_rest_namespaces() {
		/**
		 * Filter the list of REST API controllers to load.
		 *
		 * @since 3.0.0 Introduced.
		 *
		 * @param array $controllers List of $namespace => $controllers to load.
		 */
		$namespaces = apply_filters(
			'cocart_rest_api_get_rest_namespaces',
			array(
				'cocart/v1' => cocart_rest_should_load_namespace( 'cocart/v1' ) ? $this->get_v1_controllers() : array(),
				'cocart/v2' => cocart_rest_should_load_namespace( 'cocart/v2' ) ? $this->get_v2_controllers() : array(),
			)
		);

		return $namespaces;
	} // END get_rest_namespaces()

	/**
	 * Register all CoCart API routes.
	 *
	 * @access protected
	 */
	protected function register_all_routes() {
		$this->register_routes( 'v1' );
		$this->register_routes( 'v2' );
		$this->register_routes( 'batch' );

		$this->register_rest_routes(); // Old method. Registers remaining routes with no specific version.
	} // END register_all_routes();

	/**
	 * Register defined list of routes with WordPress.
	 *
	 * @access protected
	 *
	 * @param string $version API Version being registered. Default is the current supported API Version.
	 */
	protected function register_routes( $version = 'v2' ) {
		// If no routes for the version exist return nothing.
		if ( ! isset( $this->routes[ CoCart::get_api_namespace() . $version ] ) ) {
			return;
		}

		// Set the route namespace outside the controller.
		$route_namespace = CoCart::get_api_namespace() . '/' . $version;

		$routes = $this->routes[ CoCart::get_api_namespace() . $version ];

		foreach ( $routes as $route_identifier => $route_class ) {
			$skip_route = false;

			$route = $this->routes[ CoCart::get_api_namespace() . $version ][ $route_identifier ] ?? false;

			if ( ! $route ) {
				error_log( esc_html( "{$route_class} route does not exist" ) );
				$skip_route = true;
			}

			if ( ! method_exists( $route_class, 'get_path_regex' ) ) {
				error_log( esc_html( "{$route_class} route does not have a get_path_regex method" ) );
				$skip_route = true;
			}

			$path = '';
			if ( ! $skip_route ) {
				$route_instance = new $route();
				$path           = $route_instance->get_path_regex();
			}

			if ( ! $skip_route && ! isset( $this->registered_routes[ $route_class ] ) && array_search( $path, $this->registered_routes ) === false ) {
				register_rest_route(
					$route_namespace,
					$path,
					method_exists( $route_class, 'get_args' ) ? $route_instance->get_args() : array()
				);

				// Set route as registered so the old method skips from trying to register again.
				$this->registered_routes[ $route_class ] = $path;
			}
		}
	} // END register_routes()

	/**
	 * Register REST API routes.
	 *
	 * This registers remaining routes that are not version specific.
	 *
	 * @access public
	 */
	public function register_rest_routes() {
		foreach ( $this->routes as $version => $controllers ) {
			foreach ( $controllers as $controller_name => $route_class ) {
				$skip_route = false;

				// If already registered then skip to the next one.
				if ( isset( $this->registered_routes[ $route_class ] ) ) {
					$skip_route = true;
				}

				$route = $this->routes[ $version ][ $controller_name ] ?? false;

				if ( ! $route ) {
					error_log( esc_html( "{$route_class} {$version} route does not exist" ) );
					$skip_route = true;
				}

				if ( ! $skip_route ) {
					if ( ! method_exists( $route_class, 'get_path_regex' ) ) {
						error_log( esc_html( "{$route} possibly needs to be updated for version CoCart v5." ) );
					}

					// Registers if class exists to prevent fatal error from happening.
					if ( class_exists( $route ) && method_exists( $route_class, 'register_routes' ) ) {
						$route_instance = new $route();
						$route_instance->register_routes();
					}
				}
			}
		}
	} // END register_rest_routes()

	/**
	 * List of controllers for version 1.
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
	 * List of controllers for version 2.
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
			'cocart-v2-cart-create'             => 'CoCart_REST_Create_Cart_V2_Controller',
			'cocart-v2-cart-update'             => 'CoCart_REST_Update_Cart_V2_Controller',
			'cocart-v2-cart-totals'             => 'CoCart_REST_Totals_V2_Controller',
			'cocart-v2-login'                   => 'CoCart_REST_Login_V2_Controller',
			'cocart-v2-logout'                  => 'CoCart_REST_Logout_V2_Controller',
			'cocart-v2-session'                 => 'CoCart_REST_Session_V2_Controller',
			'cocart-v2-sessions'                => 'CoCart_REST_Sessions_V2_Controller',
			'cocart-v2-product-attributes'      => 'CoCart_REST_Product_Attributes_V2_Controller',
			'cocart-v2-product-attribute-terms' => 'CoCart_REST_Product_Attribute_Terms_V2_Controller',
			'cocart-v2-product-brands'          => 'CoCart_REST_Product_Brands_V2_Controller',
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
	 * @since 4.6.0 Deprecated hooking `persistent_cart_update` function below WC v10.0.
	 * @since 5.0.0 Get the cart data from session and validate cart contents.
	 */
	private function initialize_cart_session() {
		// Return nothing if accessing the index route only.
		if ( ! isset( $GLOBALS['wp']->query_vars['rest_route'] ) || preg_match( '#^/' . CoCart::get_api_namespace() . '/v[12]$#', $GLOBALS['wp']->query_vars['rest_route'] ) ) {
			return;
		}

		add_filter( 'woocommerce_cart_session_initialize', function ( $must_initialize, $session ) {
			/**
			 * Fires when cart is loaded from session.
			 *
			 * @since 3.2.0
			 */
			do_action( 'woocommerce_load_cart_from_session' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

			// Use ReflectionClass to access the protected property.
			$reflection = new ReflectionClass( $session );
			$property   = $reflection->getProperty( 'cart' );
			$property->setAccessible( true ); // Make the property accessible.

			// Get the value of the protected property.
			$cart_from_session = $property->getValue( $session );

			$cart        = (array) array_filter( WC()->session->get( 'cart', array() ) );
			$cart_totals = WC()->session->get( 'cart_totals', null );

			// Set cart-related data from session.
			$cart_from_session->set_totals( $cart_totals );
			$cart_from_session->set_applied_coupons( WC()->session->get( 'applied_coupons', array() ) );
			$cart_from_session->set_coupon_discount_totals( WC()->session->get( 'coupon_discount_totals', array() ) );
			$cart_from_session->set_coupon_discount_tax_totals( WC()->session->get( 'coupon_discount_tax_totals', array() ) );
			$cart_from_session->set_removed_cart_contents( WC()->session->get( 'removed_cart_contents', array() ) );

			// Flag to indicate the stored cart should be updated. If cart totals are null, this will be true to calculate totals.
			$update_cart_session = is_null( $cart_totals );

			// Prime caches to reduce future queries.
			if ( is_callable( '_prime_post_caches' ) ) {
				_prime_post_caches( wp_list_pluck( $cart, 'product_id' ) );
			}

			$cart_contents = array();

			// Process cart items.
			foreach ( $cart as $key => $values ) {
				// Prevent customize preview items from being added to the cart.
				if ( ! is_customize_preview() && 'customize-preview' === $key ) {
					continue;
				}

				$product = wc_get_product( $values['variation_id'] ? $values['variation_id'] : $values['product_id'] );

				if ( empty( $product ) || ! $product->exists() || 0 >= $values['quantity'] || 'trash' === $product->get_status() ) {
					continue;
				}

				/**
				 * Allow 3rd parties to validate this item before it's added to cart and add their own notices.
				 *
				 * @ignore Hook ignored when parsed into Code Reference.
				 *
				 * @since 3.6.0 Introduced in WooCommerce.
				 *
				 * @param bool       $remove_cart_item_from_session If true, the item will not be added to the cart. Default: false.
				 * @param string     $key                           Cart item key.
				 * @param array      $values                        Cart item values e.g. quantity and product_id.
				 * @param WC_Product $product                       The product being added to the cart.
				 */
				if ( apply_filters( 'woocommerce_pre_remove_cart_item_from_session', false, $key, $values, $product ) ) { // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
					$update_cart_session = true;

					/**
					 * Fires when cart item is removed from the session.
					 *
					 * @ignore Hook ignored when parsed into Code Reference.
					 *
					 * @since 3.6.0 Introduced in WooCommerce.
					 *
					 * @param string     $key     Cart item key.
					 * @param array      $values  Cart item values e.g. quantity and product_id.
					 * @param WC_Product $product The product being added to the cart.
					 */
					do_action( 'woocommerce_remove_cart_item_from_session', $key, $values, $product ); // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

					/**
					 * Allow 3rd parties to override this item's is_purchasable() result with cart item data.
					 *
					 * @ignore Hook ignored when parsed into Code Reference.
					 *
					 * @since 7.0.0 Introduced in WooCommerce.
					 *
					 * @param bool       $is_purchasable If false, the item will not be added to the cart. Default: product's is_purchasable() status.
					 * @param string     $key            Cart item key.
					 * @param array      $values         Cart item values e.g. quantity and product_id.
					 * @param WC_Product $product        The product being added to the cart.
					 */
				} elseif ( ! apply_filters( 'woocommerce_cart_item_is_purchasable', $product->is_purchasable(), $key, $values, $product ) ) { // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
					$update_cart_session = true;

					wc_add_notice(
						sprintf(
							/* translators: %s = Product name */
							__( '%s has been removed from your cart because it can no longer be purchased.', 'cocart-core' ),
							$product->get_name()
						),
						'error'
					);

					/**
					 * Fires when cart item is removed from the session.
					 *
					 * @ignore Hook ignored when parsed into Code Reference.
					 *
					 * @since 3.6.0 Introduced in WooCommerce.
					 *
					 * @param string $key    Cart item key.
					 * @param array  $values Cart item values e.g. quantity and product_id.
					 */
					do_action( 'woocommerce_remove_cart_item_from_session', $key, $values ); // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
				} elseif ( ! empty( $values['data_hash'] ) && ! hash_equals( $values['data_hash'], wc_get_cart_item_data_hash( $product ) ) ) { // phpcs:ignore PHPCompatibility.PHP.NewFunctions.hash_equalsFound
					$update_cart_session = true;

					wc_add_notice(
						sprintf(
							/* translators: %s: product name */
							__( '%s has been removed from your cart because it has since been modified.', 'cocart-core' ),
							$product->get_name()
						),
						'notice'
					);

					/**
					 * Fires when cart item is removed from the session.
					 *
					 * @since 3.6.0 Introduced in WooCommerce.
					 *
					 * @param string $key    Cart item key.
					 * @param array  $values Cart item values e.g. quantity and product_id.
					 */
					do_action( 'woocommerce_remove_cart_item_from_session', $key, $values ); // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
				} else {
					// Put session data into array. Run through filter so other plugins can load their own session data.
					$session_data = array_merge(
						$values,
						array(
							'data' => $product,
						)
					);

					/**
					 * Filter to modify or add session data to the cart contents.
					 *
					 * @ignore Hook ignored when parsed into Code Reference.
					 *
					 * @since 3.2.0 Introduced in WooCommerce.
					 *
					 * @param array  $session_data Data for an item in the cart.
					 * @param array  $values       Data for an item in the cart, without the product object.
					 * @param string $key          The cart item hash.
					 */
					$cart_contents[ $key ] = apply_filters( 'woocommerce_get_cart_item_from_session', $session_data, $values, $key ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

					if ( ! isset( $cart_contents[ $key ]['data'] ) || ! $cart_contents[ $key ]['data'] instanceof WC_Product ) {
						// If the cart contents is missing the product object after filtering, something is wrong.
						wc_doing_it_wrong(
							__METHOD__,
							'When filtering cart items with woocommerce_get_cart_item_from_session, each item must have a data key containing a product object.',
							'9.8.0'
						);

						// Add the product back in.
						$cart_contents[ $key ]['data'] = $product;
					}

					// Add to cart right away so the product is visible in woocommerce_get_cart_item_from_session hook.
					$cart_from_session->set_cart_contents( $cart_contents );
				}
			}

			// If it's not empty, it's been already populated by the loop above.
			if ( ! empty( $cart_contents ) ) {
				/**
				 * Filter the cart contents.
				 *
				 * @ignore Hook ignored when parsed into Code Reference.
				 *
				 * @since 3.2.0 Introduced in WooCommerce.
				 *
				 * @param array $cart_contents The cart contents.
				 */
				$cart_from_session->set_cart_contents( apply_filters( 'woocommerce_cart_contents_changed', $cart_contents ) );
			}

			/**
			 * Fires when cart is loaded from session.
			 *
			 * @ignore Hook ignored when parsed into Code Reference.
			 *
			 * @since 3.2.0 Introduced in WooCommerce.
			 *
			 * @param WC_Cart $cart The cart object.
			 */
			do_action( 'woocommerce_cart_loaded_from_session', $cart_from_session );

			$cart_for_session = $cart_from_session->get_cart_for_session();

			if ( empty( $cart_for_session ) ) {
				WC()->session->set( 'cart', null );
				$update_cart_session = true;
			}

			// Update cart session if needed.
			if ( $update_cart_session ) {
				$cart_from_session->calculate_totals();
			}

			// Destroy cart session when cart emptied.
			add_action( 'woocommerce_cart_emptied', array( $session, 'destroy_cart_session' ) );

			// Update session when the cart is updated.
			add_action( 'woocommerce_after_calculate_totals', array( $session, 'set_session' ), 1000 );
			add_action( 'woocommerce_cart_loaded_from_session', array( $session, 'set_session' ) );
			add_action( 'woocommerce_removed_coupon', array( $session, 'set_session' ) );

			// Persistent cart stored to usermeta. Only supported for WC users below v10. @todo Remove hooks below in future.
			if ( method_exists( $session, 'persistent_cart_update' ) && version_compare( WC_VERSION, '10.0', '<' ) ) {
				add_action( 'woocommerce_add_to_cart', array( $session, 'persistent_cart_update' ) );
				add_action( 'woocommerce_cart_item_removed', array( $session, 'persistent_cart_update' ) );
				add_action( 'woocommerce_cart_item_restored', array( $session, 'persistent_cart_update' ) );
				add_action( 'woocommerce_cart_item_set_quantity', array( $session, 'persistent_cart_update' ) );
			}

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
			 * @param int $current_user_id Current user ID.
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
	 * @access public
	 *
	 * @since 1.0.0 Introduced.
	 * @since 3.1.0 Added cart callback support and Products API.
	 * @since 5.0.0 Added create cart route, brands, monetary, response and pagination utilities.
	 */
	public function rest_api_includes() {
		require_once __DIR__ . '/utilities/class-cocart-rest-utilities-monetary-formatting.php';
		require_once __DIR__ . '/utilities/class-cocart-rest-utilities-cart-response.php';
		require_once __DIR__ . '/utilities/class-cocart-rest-utilities-pagination.php';

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
		require_once __DIR__ . '/controllers/class-cocart-cart-controller.php';
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
		require_once __DIR__ . '/controllers/v2/cart/class-cocart-create-cart-controller.php';
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
		require_once __DIR__ . '/controllers/v2/products/class-cocart-product-brands-controller.php';
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
	 * @param bool   $skip        Default: WP_DEBUG.
	 * @param string $request_uri Requested REST API.
	 *
	 * @return bool $skip Results to WP_DEBUG or true if CoCart requested.
	 */
	public function prevent_cache( $skip, $request_uri ) {
		$regex_path_patterns = $this->get_cacheable_route_patterns();

		foreach ( $regex_path_patterns as $regex_path_pattern ) {
			if ( ! preg_match( $regex_path_pattern, $request_uri ) ) {
				return true;
			}
		}

		return $skip;
	} // END prevent_cache()

	/**
	 * Helps prevent CoCart from being cached on most routes and returns results quicker.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 * @since 4.1.0 Check against allowed routes to determine if we should cache.
	 * @since 5.0.0 Allow for set API namespace to be used for control patterns.
	 *
	 * @param bool             $served  Whether the request has already been served. Default false.
	 * @param WP_HTTP_Response $result  Result to send to the client. Usually a WP_REST_Response.
	 * @param WP_REST_Request  $request The request object.
	 * @param WP_REST_Server   $server  Server instance.
	 *
	 * @return null|bool
	 */
	public function set_cache_control_headers( $served, $result, $request, $server ) {
		/**
		 * Filter allows you set a path to which will prevent from being added to browser cache.
		 *
		 * @since 3.6.0 Introduced.
		 * @since 5.0.0 Added API Namespace as new parameter.
		 *
		 * @param array  $cache_control_patterns Cache control patterns.
		 * @param string $api_namespace          API Namespace
		 */
		$regex_path_patterns = apply_filters(
			'cocart_send_cache_control_patterns',
			array(
				'/^' . CoCart::get_api_namespace() . '\/v2\/cart/',
				'/^' . CoCart::get_api_namespace() . '\/v2\/logout/',
				'/^' . CoCart::get_api_namespace() . '\/v2\/store/',
				'/^' . CoCart::get_api_namespace() . '\/v1\/get-cart/',
				'/^' . CoCart::get_api_namespace() . '\/v1\/logout/',
			),
			CoCart::get_api_namespace()
		);

		$cache_control = ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() )
		? 'no-cache, must-revalidate, max-age=0, no-store, private'
		: 'no-cache, must-revalidate, max-age=0, no-store';

		foreach ( $regex_path_patterns as $regex_path_pattern ) {
			if ( preg_match( $regex_path_pattern, ltrim( wp_unslash( $request->get_route() ), '/' ) ) ) {
				if ( method_exists( $server, 'send_header' ) ) {
					$server->send_header( 'Expires', 'Thu, 01-Jan-70 00:00:01 GMT' );
					$server->send_header( 'Cache-Control', $cache_control );
					$server->send_header( 'Pragma', 'no-cache' );
				}
			}
		}

		// Routes that can be cached will set the Last-Modified header.
		foreach ( $this->get_cacheable_route_patterns() as $regex_path_pattern ) {
			if ( preg_match( $regex_path_pattern, ltrim( wp_unslash( $request->get_route() ), '/' ) ) ) {
				if ( method_exists( $server, 'send_headers' ) ) {
					// Identify the product ID when accessing the Products API.
					$product_id    = empty( $request->get_param( 'id' ) ) ? 0 : wc_clean( wp_unslash( $request->get_param( 'id' ) ) );
					$product_id    = CoCart_Utilities_Product_Helpers::get_product_id( $product_id );
					$last_modified = null;

					// Product is found so let's get the last modified date.
					if ( ! empty( $product_id ) && $product_id > 0 ) {
						$last_modified = get_post_field( 'post_modified_gmt', $product_id );
					}

					if ( $last_modified ) {
						// Create a DateTime object in GMT.
						$gmt_date = new DateTime( $last_modified, new DateTimeZone( 'GMT' ) );

						// Determine the site's timezone.
						$timezone_string = get_option( 'timezone_string' );
						$gmt_offset      = get_option( 'gmt_offset' );

						if ( ! empty( $timezone_string ) ) {
							$site_timezone = new DateTimeZone( $timezone_string );
						} elseif ( is_numeric( $gmt_offset ) ) {
							$offset_hours   = (int) $gmt_offset;
							$offset_minutes = abs( $gmt_offset - $offset_hours ) * 60;
							$site_timezone  = new DateTimeZone( sprintf( '%+03d:%02d', $offset_hours, $offset_minutes ) );
						} else {
							$site_timezone = new DateTimeZone( 'UTC' );
						}

						// Convert to WordPress site timezone.
						$gmt_date->setTimezone( $site_timezone );
					} else {
						$gmt_date = new DateTime( 'now', new DateTimeZone( 'GMT' ) );
					}

					$last_modified = $gmt_date->format( 'D, d M Y H:i:s' ) . ' GMT';

					$server->send_header( 'Last-Modified', $last_modified );
				}
			}
		}

		return $served;
	} // END set_cache_control_headers()

	/**
	 * Prevents certain routes from initializing the session and cart.
	 *
	 * @access protected
	 *
	 * @since 3.1.0 Introduced.
	 * @since 5.0.0 Allow for set API namespace to be used for control patterns.
	 *
	 * @return bool Returns true if route matches.
	 */
	protected function prevent_routes_from_initializing() {
		$rest_prefix = trailingslashit( rest_get_url_prefix() );
		$request_uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated

		$routes = array(
			CoCart::get_api_namespace() . '/v2/login',
			CoCart::get_api_namespace() . '/v2/logout',
			CoCart::get_api_namespace() . '/v1/products',
			CoCart::get_api_namespace() . '/v2/products',
			CoCart::get_api_namespace() . '/v2/sessions',
			CoCart::get_api_namespace() . '/v2/store',
		);

		foreach ( $routes as $route ) {
			if ( ( false !== strpos( $request_uri, $rest_prefix . $route ) ) ) {
				return true;
			}
		}

		return false;
	} // END prevent_routes_from_initializing()

	/**
	 * Returns routes that can be cached as a regex pattern.
	 *
	 * @access protected
	 *
	 * @since 4.1.0 Introduced.
	 * @since 5.0.0 Allow for set API namespace to be used for control patterns.
	 *
	 * @return array Routes that can be cached.
	 */
	protected function get_cacheable_route_patterns() {
		return array(
			'/^' . CoCart::get_api_namespace() . '\/v2\/products/',
			'/^' . CoCart::get_api_namespace() . '\/v1\/products/',
		);
	} // END get_cacheable_route_patterns()

	/**
	 * Handle REST API errors with enhanced debugging information and
	 * allows for final modifications to the response before returning.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param WP_REST_Response|WP_Error $response Result to send to the client.
	 * @param array                     $handler  Route handler used for the request.
	 * @param WP_REST_Request           $request  Request used to generate the response.
	 *
	 * @return WP_REST_Response|WP_Error Enhanced error response if applicable.
	 */
	public function handle_rest_response( $response, $handler, $request ) {
		// If the route does not match a CoCart request then just return the response.
		if ( ! preg_match( '#^/' . CoCart::get_api_namespace() . '/#', $request->get_route() ) ) {
			return $response;
		}

		if ( is_wp_error( $response ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// Ignore 'rest_invalid_param' error code as it is expected when a parameter is missing.
			if ( $response->get_error_code() === 'rest_invalid_param' ) {
				return $response;
			}

			$error_data = array(
				'error_data' => array(
					'trace'   => array_map(
						function ( $item ) {
							return array(
								'file'     => isset( $item['file'] ) ? $item['file'] : '',
								'line'     => isset( $item['line'] ) ? $item['line'] : '',
								'function' => isset( $item['function'] ) ? $item['function'] : '',
								'class'    => isset( $item['class'] ) ? $item['class'] : '',
							);
						},
						debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 5 )
					),
				),
			);

			return new \WP_Error(
				$response->get_error_code(),
				$response->get_error_message(),
				array_merge( $response->get_error_data(), $error_data )
			);
		}

		/**
		 * Filter is to be used as a final straw for changing the response based on the request made.
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @param WP_REST_Response $response Result to send to the client.
		 * @param WP_REST_Request  $request  The request object.
		 */
		$response = apply_filters( 'cocart_rest_response', $response, $request ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

		// Check if response is a WP_REST_Response object before calling get_data().
		if ( ! $response instanceof WP_REST_Response ) {
			return $response; // Return raw response if not a REST Response object.
		}

		if ( empty( $response->get_data() ) ) {
			return new \WP_Error(
				'cocart_response_returned_empty',
				sprintf(
					/* translators: %s: REST API URL */
					__( 'Request returned nothing for "%s"!', 'cocart-core' ),
					rest_url( $request->get_route() )
				),
				404
			);
		}

		return $response;
	} // END handle_rest_response()
} // END class

return new CoCart_REST_API();
