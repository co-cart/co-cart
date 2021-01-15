<?php
/**
 * Includes CoCart to the plugin install screen.
 *
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart\Admin
 * @since    3.0.0
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Plugins_Install' ) ) {

	class CoCart_Plugins_Install {

		/**
		 * Constructor.
		 *
		 * @access public
		 */
		public function __construct() {
			add_filter( 'install_plugins_tabs', array( $this, 'cocart_plugins_tab' ) );
			add_filter( 'install_plugins_table_api_args_cocart', array( $this, 'cocart_plugin_list_args' ) );
			add_action( 'install_plugins_cocart', 'display_plugins_table' );
		} // END __construct()

		/**
		 * Add CoCart plugin tab.
		 *
		 * @param  array $tabs Default plugin tabs.
		 * @return array $tabs Altered plugin tabs.
		 */
		function cocart_plugins_tab( $tabs ) {
			return array_merge(
				$tabs,
				array(
					'cocart' => 'CoCart',
				)
			);
		}

		/**
		 * Set CoCart tab args.
		 *
		 * @param  object $args
		 * @return object $args
		 */
		public function cocart_plugin_list_args( $args ) {
			$args['author'] = 'cocartforwc';

			return $args;
		}

	} // END class

} // END if class exists

return new CoCart_Plugins_Install();
