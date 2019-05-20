<?php
/**
 * Admin View: WooCommerce Requirment Notice.
 *
 * @since    1.2.0
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
	<p><?php echo sprintf( __( '%1$s requires at least %2$s v%3$s or higher.', 'cart-rest-api-for-woocommerce' ), esc_html__( 'CoCart', 'cart-rest-api-for-woocommerce' ), esc_html__( 'WooCommerce', 'cart-rest-api-for-woocommerce' ), CoCart::$required_woo ); ?></p>
</div>
