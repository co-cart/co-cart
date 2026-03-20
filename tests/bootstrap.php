<?php
/**
 * Bootstrap class
 *
 * @author  Sébastien Dumont
 * @package CoCart
 */

/**
 * The test suite bootstrap.
 */
class CoCart_Unit_Tests_Bootstrap {

	/**
	 * The instance.
	 *
	 * @var CoCart_Unit_Tests_Bootstrap
	 */
	protected static $instance = null;

	/**
	 * The ID of the plugin.
	 *
	 * @var string
	 */
	private $plugin_id = 'cocart-core.php';

	/**
	 * The plugin tests directory.
	 *
	 * @var string
	 */
	private $tests_dir;

	/**
	 * The WP tests library directory.
	 *
	 * @var string
	 */
	private $wp_tests_dir;

	/**
	 * The required plugins directory.
	 *
	 * @var string
	 */
	private $wp_plugins_dir;

	/**
	 * Get the single class instance.
	 *
	 * @return CoCart_Unit_Tests_Bootstrap
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructs the bootstrap class.
	 */
	public function __construct() {
		define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php' );

		$this->tests_dir    = __DIR__;
		$this->wp_tests_dir = getenv( 'WP_TESTS_DIR' ) ? getenv( 'WP_TESTS_DIR' ) : '/tmp/wordpress-tests-lib';

		$wp_core_dir          = getenv( 'WP_CORE_DIR' ) ? rtrim( getenv( 'WP_CORE_DIR' ), '/' ) : '/tmp/wordpress';
		$this->wp_plugins_dir = $wp_core_dir . '/wp-content/plugins';

		define( 'WP_PLUGIN_DIR', $this->wp_plugins_dir );

		require_once $this->wp_tests_dir . '/includes/functions.php';

		tests_add_filter( 'plugins_loaded', array( $this, 'load_plugins' ) );

		// Setup CoCart Session Handler.
		tests_add_filter( 'woocommerce_session_handler', array( $this, 'session_handler' ) );

		// Setup WooCommerce.
		tests_add_filter( 'woocommerce_loaded', array( $this, 'woocommerce' ) );

		// Load REST API.
		tests_add_filter( 'rest_api_init', array( $this, 'load_rest_api' ) );

		// Default configurations.
		tests_add_filter( 'woocommerce_admin_disabled', '__return_true' );

		// Load the CoCart testing environment.
		require $this->wp_tests_dir . '/includes/bootstrap.php';

		$this->includes();
	}

	/**
	 * Loads the required files.
	 *
	 * Only framework base classes are loaded here. Test files are discovered
	 * and loaded automatically by PHPUnit via the <directory> entries in phpunit.xml.
	 */
	private function includes() {
		// Test case base.

		// Framework (load order: base → rest → api → v1 → v2).
		// These must be loaded before PHPUnit scans test files, since test classes extend them.
		require_once $this->tests_dir . '/framework/class-cocart-unit-test-case.php';
		require_once $this->tests_dir . '/framework/class-cocart-rest-test-case.php';
		require_once $this->tests_dir . '/framework/class-cocart-api-test-case.php';
		require_once $this->tests_dir . '/framework/class-cocart-api-v1-test-case.php';
		require_once $this->tests_dir . '/framework/class-cocart-api-v2-test-case.php';
	}

	/**
	 * Loads plugins.
	 */
	public function load_plugins() {
		// Load WooCommerce.
		require $this->wp_plugins_dir . '/woocommerce/woocommerce.php';

		// Install WooCommerce database tables after post types are registered (priority 20, after WC's priority 10).
		tests_add_filter( 'init', array( 'WC_Install', 'install' ), 20 );

		// Stub Action Scheduler functions not available in test environment.
		if ( ! function_exists( 'as_schedule_single_action' ) ) {
			function as_schedule_single_action() { // phpcs:ignore
				return 0;
			}
		}
		if ( ! function_exists( 'as_has_scheduled_action' ) ) {
			function as_has_scheduled_action() { // phpcs:ignore
				return false;
			}
		}

		// Load CoCart.
		require_once trailingslashit( dirname( $this->tests_dir ) ) . $this->plugin_id;

		if ( ! defined( 'COCART_CART_CACHE_GROUP' ) ) {
			define( 'COCART_CART_CACHE_GROUP', 'cocart_cart_id' );
		}
	}

	/**
	 * Filters the session handler to replace with our own.
	 *
	 * @access public
	 *
	 * @param string $handler WooCommerce Session Handler.
	 *
	 * @return string $handler CoCart Session Handler.
	 */
	public function session_handler( $handler ) {
		if ( class_exists( 'WC_Session_Handler' ) ) {
			require_once COCART_FILE_PATH . '/includes/classes/class-cocart-session-handler.php';
			$handler = 'CoCart_Session_Handler';
		}

		return $handler;
	} // END session_handler()

	/**
	 * Includes WooCommerce tweaks.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function woocommerce() {
		require_once COCART_FILE_PATH . '/includes/classes/class-cocart-woocommerce.php';
	} // END woocommerce()

	/**
	 * Load REST API.
	 *
	 * @access public
	 */
	public function load_rest_api() {
		require_once COCART_FILE_PATH . '/includes/classes/class-cocart-data-exception.php';
		require_once COCART_FILE_PATH . '/includes/classes/rest-api/class-cocart-cart-cache.php';
		require_once COCART_FILE_PATH . '/includes/classes/rest-api/class-cocart-cart-callbacks.php';
		require_once COCART_FILE_PATH . '/includes/classes/rest-api/class-cocart-cart-extension.php';
		require_once COCART_FILE_PATH . '/includes/classes/rest-api/class-cocart-response.php';
		require_once COCART_FILE_PATH . '/includes/classes/rest-api/class-cocart-cart-formatting.php';
		require_once COCART_FILE_PATH . '/includes/classes/rest-api/class-cocart-cart-validation.php';
		require_once COCART_FILE_PATH . '/includes/classes/rest-api/class-cocart-product-validation.php';
		require_once COCART_FILE_PATH . '/includes/classes/rest-api/class-cocart-rest-api.php';
		require_once COCART_FILE_PATH . '/includes/classes/rest-api/class-cocart-security.php';
	} // END load_rest_api()
}

CoCart_Unit_Tests_Bootstrap::instance();
