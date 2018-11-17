<?php
/**
 * Admin View: Upgrade Warning Notice.
 *
 * @since    1.0.6
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="notice notice-warning is-dismissible">
	<p><?php echo sprintf( __( 'Warning! Version 2.0.0 of %4$s%1$s%5$s is coming soon and will provide a new API. Before it is released I require testers. %2$sLearn more about the changes in version 2.0.0 and how you can help&raquo;%3$s', 'cart-rest-api-for-woocommerce' ), esc_html__( 'CoCart', 'cart-rest-api-for-woocommerce' ), '<a href="https://cocart.xyz/cocart-v2.0.0-soon/" aria-label="' . esc_html__( 'Read the changes coming to CoCart in version 2', 'cart-rest-api-for-woocommerce' ) . '" target="_blank">', '</a>', '<strong>', '</strong>' ); ?></p>
</div>
