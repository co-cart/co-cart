<?php
/**
 * Admin View: Single integration card.
 *
 * Used by both the integrations page (initial render) and the AJAX search handler.
 *
 * Expected variables in scope:
 *   string $slug      Integration slug.
 *   array  $args      Integration metadata from CoCart_Integrations::get_all().
 *   bool   $enabled   Whether the integration is currently enabled by the user.
 *   bool   $available Whether the required plugin is installed/active.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   5.0.0 Introduced.
 * @license GPL-3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$upgrade_required = ! empty( $args['upgrade_required'] );
$coming_soon      = ! empty( $args['coming_soon'] );

$card_classes = array( 'cocart-integration-card' );
if ( $upgrade_required ) {
	$card_classes[] = 'upgrade-required';
} elseif ( ! $available ) {
	$card_classes[] = 'is-unavailable';
} elseif ( $enabled ) {
	$card_classes[] = 'is-enabled';
} else {
	$card_classes[] = 'is-disabled';
}

$upgrade_url = CoCart_Helpers::build_shortlink(
	add_query_arg(
		CoCart_Helpers::cocart_campaign( array( 'utm_content' => 'integrations-upgrade' ) ),
		COCART_STORE_URL . 'pricing/'
	)
);
?>
<div class="<?php echo esc_attr( implode( ' ', $card_classes ) ); ?>" data-slug="<?php echo esc_attr( $slug ); ?>">

	<div class="cocart-integration-card-header">
		<div class="cocart-integration-icon">
			<?php if ( ! empty( $args['icon'] ) ) : ?>
				<img src="<?php echo esc_url( $args['icon'] ); ?>" alt="<?php echo esc_attr( $args['name'] ); ?>" width="48" height="48">
			<?php else : ?>
				<span class="cocart-integration-icon-placeholder dashicons dashicons-admin-plugins"></span>
			<?php endif; ?>
		</div>
		<strong class="cocart-integration-name"><?php echo esc_html( $args['name'] ); ?></strong>
	</div>

	<div class="cocart-integration-card-body">
		<p class="cocart-integration-description"><?php echo esc_html( $args['description'] ); ?></p>
	</div>

	<div class="cocart-integration-card-footer">
		<div class="cocart-integration-footer-left">
			<?php if ( $upgrade_required ) : ?>
				<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button cocart-integration-upgrade" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Upgrade', 'cart-rest-api-for-woocommerce' ); ?>
				</a>
			<?php elseif ( ! $available ) : ?>
				<span class="dashicons dashicons-info-outline cocart-integration-not-installed-icon"></span>
				<em class="cocart-integration-not-installed-label">
					<?php echo esc_html__( 'Plugin is not installed.', 'cart-rest-api-for-woocommerce' ); ?>
				</em>
			<?php else : ?>
				<label class="cocart-toggle" title="<?php echo $enabled ? esc_attr__( 'Enabled', 'cart-rest-api-for-woocommerce' ) : esc_attr__( 'Disabled', 'cart-rest-api-for-woocommerce' ); ?>">
					<input
						type="checkbox"
						class="cocart-integration-toggle"
						data-slug="<?php echo esc_attr( $slug ); ?>"
						<?php checked( $enabled ); ?>
					>
					<span class="cocart-toggle-slider"></span>
					<span class="cocart-integration-status-label">
						<?php echo $enabled ? esc_html__( 'Enabled', 'cart-rest-api-for-woocommerce' ) : esc_html__( 'Disabled', 'cart-rest-api-for-woocommerce' ); ?>
					</span>
				</label>
			<?php endif; ?>
		</div>

		<?php if ( $coming_soon || ! empty( $args['doc_url'] ) ) : ?>
		<div class="cocart-integration-footer-right">
			<?php if ( $coming_soon ) : ?>
				<span class="cocart-integration-coming-soon"><?php esc_html_e( 'Coming soon', 'cart-rest-api-for-woocommerce' ); ?></span>
			<?php elseif ( ! empty( $args['doc_url'] ) ) : ?>
				<a href="<?php echo esc_url( $args['doc_url'] ); ?>" class="cocart-integration-learn-more" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Learn more', 'cart-rest-api-for-woocommerce' ); ?> &#8599;
				</a>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>

</div>
