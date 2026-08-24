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
	private $plugin_id = 'cart-rest-api-for-woocommerce.php';

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

		// Prevent WC session handler from calling setcookie() — headers are already
		// sent by the WP test bootstrap. WC_Session_Handler registers
		// set_customer_session_cookie on the woocommerce_set_cart_cookies action;
		// suppress it by filtering wc_setcookie to a no-op during tests.
		tests_add_filter( 'woocommerce_set_cookie_enabled', '__return_false' );

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
		// Framework (load order: base → rest → api → v1 → v2).
		// These must be loaded before PHPUnit scans test files, since test classes extend them.
		require_once $this->tests_dir . '/framework/class-cocart-unit-test-case.php';
		require_once $this->tests_dir . '/framework/class-cocart-rest-test-case.php';
		require_once $this->tests_dir . '/framework/class-cocart-api-test-case.php';
		require_once $this->tests_dir . '/framework/class-cocart-api-v1-test-case.php';
		require_once $this->tests_dir . '/framework/class-cocart-api-v2-test-case.php';
		require_once $this->tests_dir . '/framework/class-cocart-test-spy-rest-server.php';
	}

	/**
	 * Loads plugins.
	 */
	public function load_plugins() {
		// Load WooCommerce.
		require $this->wp_plugins_dir . '/woocommerce/woocommerce.php';

		// Install WooCommerce database tables after post types are registered (priority 20, after WC's priority 10).
		tests_add_filter( 'init', array( 'WC_Install', 'install' ), 20 );

		// Force Action Scheduler to initialize now so its autoloader is registered before
		// CoCart loads. WooCommerce includes action-scheduler.php unconditionally, which
		// defines a version-suffixed action_scheduler_initialize_X_dot_Y_dot_Z() function and
		// registers it on plugins_loaded. But load_plugins() itself runs during plugins_loaded,
		// so the action-scheduler.php "theme fallback" block (which checks did_action &&
		// !doing_action) is skipped, and the priority-0 registration hook added by requiring
		// the file has already been passed in this same pass.
		//
		// The initializer function name is tied to the bundled Action Scheduler version (e.g.
		// WooCommerce 10.x ships 3.9.3, later releases bump it), so instead of hardcoding a
		// version we discover whichever initializer is actually defined. It guards against
		// double-init with its own class_exists check.
		$action_scheduler_init_fn = null;

		foreach ( get_defined_functions()['user'] as $function_name ) {
			if ( preg_match( '/^action_scheduler_initialize_\d+_dot_\d+_dot_\d+$/', $function_name ) ) {
				$action_scheduler_init_fn = $function_name;
				break;
			}
		}

		if ( $action_scheduler_init_fn ) {
			call_user_func( $action_scheduler_init_fn );
			ActionScheduler_Versions::initialize_latest_version();
		}

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
		if ( ! function_exists( 'as_unschedule_all_actions' ) ) {
			function as_unschedule_all_actions() {} // phpcs:ignore
		}
		if ( ! function_exists( 'as_schedule_recurring_action' ) ) {
			function as_schedule_recurring_action() { // phpcs:ignore
				return 0;
			}
		}
		if ( ! function_exists( 'as_unschedule_action' ) ) {
			function as_unschedule_action() {} // phpcs:ignore
		}
		if ( ! function_exists( 'as_next_scheduled_action' ) ) {
			function as_next_scheduled_action() { // phpcs:ignore
				return false;
			}
		}

		// Load CoCart.
		require_once trailingslashit( dirname( $this->tests_dir ) ) . $this->plugin_id;
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
	 * Explicitly instantiate CoCart_REST_API on every rest_api_init so that routes
	 * are registered into the fresh WP_REST_Server created by each test's setUp().
	 * The class file uses `return new CoCart_REST_API()` which only fires on the
	 * first require_once (via the Composer classmap autoloader); subsequent
	 * setUp() calls need a new instance to re-register.
	 *
	 * This filter is added via tests_add_filter() before the plugin itself is
	 * loaded, so it always runs before CoCart's own `rest_api_init` hook — the
	 * first call here is what triggers the autoload (and file-level
	 * instantiation) of CoCart_REST_API in the first place.
	 *
	 * @access public
	 */
	public function load_rest_api() {
		new CoCart_REST_API();
	} // END load_rest_api()
}

CoCart_Unit_Tests_Bootstrap::instance();
