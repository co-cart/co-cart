<?php
/**
 * Handles support for Third Party.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Third Party
 * @since   2.8.1
 * @version 4.4.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Third_Party' ) ) {

	/**
	 * Third Party Support.
	 */
	class CoCart_Third_Party {

		/**
		 * Constructor.
		 *
		 * @access public
		 */
		public function __construct() {
			self::include_plugins();
		}

		/**
		 * Load support for third-party plugins.
		 *
		 * @access public
		 */
		public function include_plugins() {
			include_once __DIR__ . '/plugin/jwt-auth-by-useful-team/class-cocart-plugin-jwt-auth.php'; // JWT Auth.
			include_once __DIR__ . '/plugin/litespeed-cache/class-cocart-plugin-litespeed-cache.php'; // LiteSpeed Cache.
			include_once __DIR__ . '/plugin/taxjar/class-cocart-plugin-taxjar.php'; // TaxJar.
		}
	} // END class.

} // END if class exists.

return new CoCart_Third_Party();
