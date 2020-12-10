<?php
/**
 * Handles support for Pantheon host.
 *
 * @author   Sébastien Dumont
 * @category Classes
 * @package  CoCart\Third Party
 * @since    2.8.1
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Third_Party' ) ) {

	class CoCart_Third_Party {

		/**
		 * Constructor.
		 *
		 * @access public
		 */
		public function __construct() {
			self::include_hosts();
		}

		/**
		 * Load support for third-party hosts.
		 *
		 * @access public
		 * @return string
		 */
		public function include_hosts() {
			include_once COCART_ABSPATH . 'includes/third-party/hosting/pantheon/class-host-pantheon.php'; // Pantheon.io
		}

	} // END class.

} // END if class exists.

return new CoCart_Third_Party();