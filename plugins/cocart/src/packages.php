<?php
/**
 * Loads CoCart packages from the /packages directory. These are packages developed outside of CoCart.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Src\Packages
 * @since   4.0.0
 * @license GPL-2.0+
 */

namespace CoCart;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Packages class.
 */
class Packages {

	/**
	 * Constructor.
	 *
	 * @access private
	 */
	private function __construct() {}

	/**
	 * Array of default package names.
	 *
	 * DO NOT EDIT!
	 *
	 * @access protected
	 *
	 * @static
	 *
	 * @var    array
	 */
	protected static $default_packages = array(
		'admin',
		'compatibility',
		'products-api',
		'third-party',
		'session-api'
	);

	/**
	 * Array of package names and their main package classes.
	 *
	 * @access protected
	 *
	 * @static
	 *
	 * @var    array Key is the package name/directory, value is the main package class which handles init.
	 */
	protected static $packages = array(
		'admin'         => '\\CoCart\\Admin\\Package',
		'compatibility' => '\\CoCart\\Compatibility\\Package',
		'products-api'  => '\\CoCart\\ProductsAPI\\Package',
		'third-party'   => '\\CoCart\\ThirdParty\\Package',
		'session-api'   => '\\CoCart\\SessionAPI\\Package',
	);

	/**
	 * Init the package loader.
	 *
	 * @access public
	 *
	 * @static
	 */
	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'on_init' ) );
	}

	/**
	 * Callback for WordPress init hook.
	 *
	 * @access public
	 *
	 * @static
	 */
	public static function on_init() {
		self::check_packages();
		self::load_packages();
	}

	/**
	 * Checks a package exists by looking for it's directory.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param string $package Package name.
	 *
	 * @return boolean
	 */
	public static function package_exists( $package ) {
		return file_exists( dirname( __DIR__ ) . '/packages/' . $package );
	}

	/**
	 * Returns the default packages that should be available.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @return array
	 */
	public static function get_default_packages() {
		return self::$default_packages;
	}

	/**
	 * Returns the packages available.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @return array
	 */
	public static function get_packages() {
		return self::$packages;
	}

	/**
	 * Checks packages after plugins_loaded hook.
	 *
	 * @access protected
	 *
	 * @static
	 */
	protected static function check_packages() {
		$packages = array();

		foreach ( self::get_packages() as $package_name => $package_class ) {
			// Warn user if package is missing!
			if ( ! self::package_exists( $package_name ) ) {
				$packages[] = $package_name;
			}
		}

		if ( count( $packages ) > 0 ) {
			self::missing_packages( $packages );
		}
	} // END check_packages()

	/**
	 * Loads packages after plugins_loaded hook.
	 *
	 * Each package should include an init file which loads the package so it can be used by core.
	 *
	 * @access protected
	 *
	 * @static
	 */
	protected static function load_packages() {
		foreach ( self::get_packages() as $package_name => $package_class ) {
			// Check package is not missing!
			if ( self::package_exists( $package_name ) ) {
				// Load Package.
				include_once dirname( __DIR__ ) . '/packages/' . $package_name . '/load-package.php';

				// Call Package.
				if ( class_exists( $package_class ) ) {
					call_user_func( array( $package_class, 'init' ) );
				}
			}
		}
	} // END load_packages()

	/**
	 * If a package is missing, add an admin notice.
	 *
	 * @access protected
	 *
	 * @param string $packages Package names.
	 */
	protected static function missing_packages( $packages ) {
		$packages = implode( '</code>, <code>', $packages );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log(
				sprintf(
					/* Translators: %s package name. */
					__( 'Your missing the following CoCart packages: %s', 'cart-rest-api-for-woocommerce' ),
					'<code>' . $packages . '</code>'
				) . ' - ' . esc_html__( 'Your installation of CoCart is incomplete. If you installed CoCart from GitHub, please refer to this document to config your packages: https://github.com/co-cart/co-cart#quick-start', 'cart-rest-api-for-woocommerce' )
			);
		}
		add_action(
			'admin_notices',
			function() use ( $packages ) {
				?>
				<div class="notice notice-error">
					<p>
						<strong>
							<?php
							printf(
								/* translators: %s package names. */
								__( 'Your missing the following CoCart packages: %s', 'cart-rest-api-for-woocommerce' ),
								'<code>' . $packages . '</code>'
							);
							?>
						</strong>
						<br>
						<?php
						printf(
							/* translators: 1: is a link to a support document. 2: closing link */
							esc_html__( 'Your installation of CoCart is incomplete. If you installed CoCart from GitHub, %1$splease refer to this document%2$s to config your packages.', 'cart-rest-api-for-woocommerce' ),
							'<a href="' . esc_url( 'https://github.com/co-cart/co-cart#quick-start' ) . '" target="_blank" rel="noopener noreferrer">',
							'</a>'
						);
						?>
					</p>
				</div>
				<?php
			}
		);
	} // END missing_packages()

	/**
	 * Returns the package version.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param string $package_name Package name.
	 *
	 * @return string $version
	 */
	public static function get_package_version( $package_name ) {
		$packages = self::get_packages();

		$version = null;

		foreach ( $packages as $name => $package ) {
			if ( $name === $package_name ) {
				$version = $package::get_version();
			}
		}

		return $version;
	} // END get_package_version()

} // END class
