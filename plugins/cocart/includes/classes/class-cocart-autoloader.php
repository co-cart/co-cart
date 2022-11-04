<?php
/**
 * CoCart - Autoloader.
 *
 * @author  Sébastien Dumont
 * @package CoCart\AutoLoader
 * @since   2.0.0 Introduced.
 * @version 4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Autoloader' ) ) {

	class CoCart_Autoloader {

		/**
		 * Path to the includes directory.
		 *
		 * @access private
		 * @var    string
		 */
		private $include_path = '';

		/**
		 * The Constructor.
		 */
		public function __construct() {
			if ( function_exists( '__autoload' ) ) {
				spl_autoload_register( '__autoload' );
			}

			spl_autoload_register( array( $this, 'autoload' ) );

			$this->include_path = untrailingslashit( plugin_dir_path( COCART_FILE ) ) . '/includes/classes/';
		}

		/**
		 * Take a class name and turn it into a file name.
		 *
		 * @access private
		 * @param  string $class Class name.
		 * @return string
		 */
		private function get_file_name_from_class( $class ) {
			return 'class-' . str_replace( '_', '-', $class ) . '.php';
		} // END get_file_name_from_class()

		/**
		 * Include a class file.
		 *
		 * @access private
		 * @param  string $path File path.
		 * @return bool Successful or not.
		 */
		private function load_file( $path ) {
			if ( $path && is_readable( $path ) ) {
				include_once $path;
				return true;
			}
			return false;
		} // END load_file()

		/**
		 * Auto-load CoCart classes on demand to reduce memory consumption.
		 *
		 * @access public
		 * @param  string $class Class name.
		 */
		public function autoload( $class ) {
			$class = strtolower( $class );

			if ( 0 !== strpos( $class, 'cocart_' ) ) {
				return;
			}

			$file = $this->get_file_name_from_class( $class );
			$path = '';

			if ( 0 === strpos( $class, 'cocart_admin' ) ) {
				$path = $this->include_path . 'admin/';
			} elseif ( 0 === strpos( $class, 'cocart_wc_admin_notes_' ) ) {
				$path = $this->include_path . 'admin/notes/';
			}

			if ( empty( $path ) || ! $this->load_file( $path . $file ) ) {
				$this->load_file( $this->include_path . $file );
			}
		} // END autoload()

	} // END class.

} // END if class exists.

new CoCart_Autoloader();
