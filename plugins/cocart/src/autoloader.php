<?php
/**
 * Includes the composer Autoloader used for packages and classes.
 */

namespace CoCart;

defined( 'ABSPATH' ) || exit;

/**
 * Autoloader class.
 *
 * @since 4.0.0
 */
class Autoloader {

	/**
	 * Static-only class.
	 */
	private function __construct() {}

	/**
	 * Require the autoloader and return the result.
	 *
	 * If the autoloader is not present, let's log the failure and display a nice admin notice.
	 *
	 * @return boolean
	 */
	public static function init() {
		$autoloader = dirname( __DIR__ ) . '/vendor/autoload.php';

		if ( ! is_readable( $autoloader ) ) {
			self::missing_autoloader();
			return false;
		}

		$autoloader_result = require $autoloader;
		if ( ! $autoloader_result ) {
			return false;
		}

		return $autoloader_result;
	}

	/**
	 * If the autoloader is missing, add an admin notice.
	 */
	protected static function missing_autoloader() {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log(  // phpcs:ignore
				esc_html__( 'Your installation of CoCart is incomplete. If you installed CoCart from GitHub, please refer to this document to set up your development environment: https://github.com/co-cart/co-cart#quick-start', 'cart-rest-api-for-woocommerce' )
			);
		}
		add_action(
			'admin_notices',
			function() {
				?>
				<div class="notice notice-error">
					<p>
						<?php
						printf(
							/* translators: 1: is a link to a support document. 2: closing link */
							esc_html__( 'Your installation of CoCart is incomplete. If you installed CoCart from GitHub, %1$splease refer to this document%2$s to set up your development environment.', 'cart-rest-api-for-woocommerce' ),
							'<a href="' . esc_url( 'https://github.com/co-cart/co-cart#quick-start' ) . '" target="_blank" rel="noopener noreferrer">',
							'</a>'
						);
						?>
					</p>
				</div>
				<?php
			}
		);
	}
}
