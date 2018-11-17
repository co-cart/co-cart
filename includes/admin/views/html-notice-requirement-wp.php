<?php
/**
 * Admin View: WordPress Requirment Notice.
 *
 * @since    2.0.0
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart/Admin/Views
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="notice notice-error">
	<p><?php echo sprintf( __( 'Sorry, <strong>%s</strong> requires WordPress %s or higher. Please upgrade your WordPress setup.', 'cart-rest-api-for-woocommerce' ), esc_html__( 'CoCart', 'cocart' ), COCART_WP_VERSION_REQUIRE ); ?></p>
</div>
