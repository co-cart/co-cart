<?php
/**
 * This file checks the integrity of the WordPress plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once untrailingslashit( __DIR__ ) . '/class-meshpress-plugin-local-integrity-check.php';

final class CoCart_Integrity_Check {

	use MeshPress\Plugin_Local_Integrity_Check;

	public function __construct( string $plugin_slug, string $plugin_file ) {
		$this->initialize_integrity_check( $plugin_slug, $plugin_file );

		register_activation_hook( $plugin_file, array( $this, 'plugin_activation_checksum_check' ) );
	} // END __construct()
} // END class

new CoCart_Integrity_Check( COCART_SLUG, COCART_FILE );
