<?php
/**
 * Admin View: Settings page.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   4.9.0 Introduced.
 * @license GPL-3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings_page = $cocart_settings_page;
$renderer      = new CoCart_Admin_Settings_Renderer();

$sections        = $settings_page->get_sections();
$fields          = $settings_page->get_fields();
$cocart_settings = get_option( 'cocart_settings', array() );
$filter_info     = $renderer->build_filter_info( $fields );

$upgrade_url = CoCart_Helpers::build_shortlink(
	add_query_arg(
		CoCart_Helpers::cocart_campaign( array( 'utm_content' => 'settings-upgrade-feature' ) ),
		COCART_STORE_URL . 'why-upgrade/'
	)
);

$plus_version      = defined( 'COCART_PLUS_VERSION' ) ? preg_replace( '/-(alpha|beta|rc)\..*/i', '', COCART_PLUS_VERSION ) : '0';
$has_plus          = version_compare( $plus_version, '1.6', '>=' );
$starter_installed = defined( 'COCART_VERSION' ) && version_compare( COCART_VERSION, '4.9.0', '>' );
$show_starter_cta  = $has_plus && ! $starter_installed;
?>
<div id="cocart-settings-toast" class="cocart-toast" aria-live="polite"></div>

<nav class="cocart-settings-tabs" role="tablist">
	<?php $renderer->render_tabs( $sections ); ?>
</nav>

<div class="cocart-content">
	<div class="cocart-settings-panels">
		<?php $renderer->render_panels( $sections, $fields, $cocart_settings ); ?>
	</div><!-- /cocart-settings-panels -->
</div><!-- /cocart-content -->

<p class="cocart-settings-actions">
	<button type="button" id="cocart-save-settings" class="button button-primary cocart-button" disabled="disabled">
		<?php esc_html_e( 'Save Settings', 'cart-rest-api-for-woocommerce' ); ?>
	</button>
	<button type="button" id="cocart-reset-settings" class="button cocart-button-destructive">
		<?php esc_html_e( 'Reset to defaults', 'cart-rest-api-for-woocommerce' ); ?>
	</button>
</p>

<div id="cocart-unsaved-modal" class="cocart-modal" hidden role="dialog" aria-modal="true" aria-labelledby="cocart-unsaved-modal-title">
	<div class="cocart-modal-backdrop"></div>
	<div class="cocart-modal-box">
		<h3 id="cocart-unsaved-modal-title"><?php esc_html_e( 'Unsaved changes', 'cart-rest-api-for-woocommerce' ); ?></h3>
		<p><?php esc_html_e( 'You have unsaved changes. If you leave this page your changes will be lost.', 'cart-rest-api-for-woocommerce' ); ?></p>
		<p style="display:flex;gap:10px;margin-top:20px;">
			<button type="button" id="cocart-unsaved-leave" class="button button-primary"><?php esc_html_e( 'Leave page', 'cart-rest-api-for-woocommerce' ); ?></button>
			<button type="button" id="cocart-unsaved-stay" class="button"><?php esc_html_e( 'Stay', 'cart-rest-api-for-woocommerce' ); ?></button>
		</p>
	</div>
</div>

<div id="cocart-jwt-unsaved-modal" class="cocart-modal" hidden role="dialog" aria-modal="true" aria-labelledby="cocart-jwt-unsaved-modal-title">
	<div class="cocart-modal-backdrop"></div>
	<div class="cocart-modal-box">
		<h3 id="cocart-jwt-unsaved-modal-title"><?php esc_html_e( 'Unsaved changes', 'cart-rest-api-for-woocommerce' ); ?></h3>
		<p><?php esc_html_e( 'You have unsaved changes. We recommend saving before enabling JWT Authentication, otherwise your other changes will be lost.', 'cart-rest-api-for-woocommerce' ); ?></p>
		<p style="display:flex;gap:10px;margin-top:20px;">
			<button type="button" id="cocart-jwt-unsaved-proceed" class="button button-primary"><?php esc_html_e( 'Proceed anyway', 'cart-rest-api-for-woocommerce' ); ?></button>
			<button type="button" id="cocart-jwt-unsaved-cancel" class="button"><?php esc_html_e( 'Cancel', 'cart-rest-api-for-woocommerce' ); ?></button>
		</p>
	</div>
</div>

<div id="cocart-reset-modal" class="cocart-modal" hidden role="dialog" aria-modal="true" aria-labelledby="cocart-reset-modal-title">
	<div class="cocart-modal-backdrop"></div>
	<div class="cocart-modal-box">
		<h3 id="cocart-reset-modal-title"><?php esc_html_e( 'Reset to defaults', 'cart-rest-api-for-woocommerce' ); ?></h3>
		<p><?php esc_html_e( 'This will reset all settings to their default values. Your changes will not be saved until you click "Save Settings".', 'cart-rest-api-for-woocommerce' ); ?></p>
		<p style="display:flex;gap:10px;margin-top:20px;">
			<button type="button" id="cocart-reset-confirm" class="button button-primary"><?php esc_html_e( 'Reset', 'cart-rest-api-for-woocommerce' ); ?></button>
			<button type="button" id="cocart-reset-cancel" class="button"><?php esc_html_e( 'Cancel', 'cart-rest-api-for-woocommerce' ); ?></button>
		</p>
	</div>
</div>

<div id="cocart-filter-modal" class="cocart-modal" hidden role="dialog" aria-modal="true" aria-labelledby="cocart-filter-modal-title">
	<div class="cocart-modal-backdrop"></div>
	<div class="cocart-modal-box">
		<button type="button" class="cocart-modal-close" aria-label="<?php esc_attr_e( 'Close', 'cart-rest-api-for-woocommerce' ); ?>">&times;</button>
		<h3 id="cocart-filter-modal-title"></h3>
		<div id="cocart-filter-modal-body"></div>
	</div>
</div>

<div id="cocart-upgrade-modal" class="cocart-modal" hidden role="dialog" aria-modal="true" aria-labelledby="cocart-upgrade-modal-title">
	<div class="cocart-modal-backdrop"></div>
	<div class="cocart-modal-box">
		<button type="button" class="cocart-modal-close" aria-label="<?php esc_attr_e( 'Close', 'cart-rest-api-for-woocommerce' ); ?>">&times;</button>
		<?php if ( $show_starter_cta ) : ?>
		<h3 id="cocart-upgrade-modal-title"><?php esc_html_e( 'You\'re almost there', 'cart-rest-api-for-woocommerce' ); ?></h3>
		<div id="cocart-upgrade-modal-body">
			<p>
			<?php
			printf(
				/* translators: %s: Plugin name. */
				esc_html__( 'Your %1$s license already includes access to these features. Install the %2$s plugin to unlock them.', 'cart-rest-api-for-woocommerce' ),
				'CoCart Plus',
				'CoCart Starter'
			);
			?>
			</p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'plugin-install.php?tab=upload' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Install CoCart Starter', 'cart-rest-api-for-woocommerce' ); ?></a>
			</p>
		</div>
		<?php else : ?>
		<h3 id="cocart-upgrade-modal-title"><?php esc_html_e( 'You\'ve outgrown the basics', 'cart-rest-api-for-woocommerce' ); ?></h3>
		<div id="cocart-upgrade-modal-body">
			<p><?php esc_html_e( 'These settings are just the tip of the iceberg. Unlock the tools needed for your headless store to go from "it works" to "it scales".', 'cart-rest-api-for-woocommerce' ); ?></p>
			<ul class="cocart-upgrade-feature-list">
				<li><?php esc_html_e( 'Coupon, shipping and fee APIs for a complete checkout experience.', 'cart-rest-api-for-woocommerce' ); ?></li>
				<li><?php esc_html_e( 'Rate limiting to protect your store from abuse and excessive requests.', 'cart-rest-api-for-woocommerce' ); ?></li>
				<li><?php esc_html_e( 'Batch requests to cut round-trips and speed up your storefront.', 'cart-rest-api-for-woocommerce' ); ?></li>
				<li><?php esc_html_e( 'Priority support from the developers who build CoCart.', 'cart-rest-api-for-woocommerce' ); ?></li>
			</ul>
			<p class="cocart-upgrade-offer"><?php esc_html_e( 'Upgrade today and get an exclusive 20% off any plan.', 'cart-rest-api-for-woocommerce' ); ?></p>
			<p>
				<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Claim 20% off', 'cart-rest-api-for-woocommerce' ); ?></a>
			</p>
		</div>
		<?php endif; ?>
	</div>
</div>

<?php
// Pass dynamic data to the enqueued cocart-admin-settings script.
$script_data = array(
	'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
	'nonce'      => wp_create_nonce( 'cocart_settings_nonce' ),
	'filterInfo' => $filter_info,
	'defaults'   => $settings_page->get_defaults(),
	'sections'   => array_keys( $settings_page->get_defaults() ),
	'i18n'       => array(
		'saving'           => __( 'Saving...', 'cart-rest-api-for-woocommerce' ),
		'save'             => __( 'Save Settings', 'cart-rest-api-for-woocommerce' ),
		'toastSaved'       => __( 'Settings saved.', 'cart-rest-api-for-woocommerce' ),
		'toastError'       => __( 'Could not save. Please try again.', 'cart-rest-api-for-woocommerce' ),
		'filterModalTitle' => __( 'Active filter callbacks', 'cart-rest-api-for-woocommerce' ),
		'filterPriority'   => __( 'Priority', 'cart-rest-api-for-woocommerce' ),
		'filterCallback'   => __( 'Callback', 'cart-rest-api-for-woocommerce' ),
		'filterFile'       => __( 'File', 'cart-rest-api-for-woocommerce' ),
		'filterLine'       => __( 'Line', 'cart-rest-api-for-woocommerce' ),
		'filterUnknown'    => __( 'Unknown location', 'cart-rest-api-for-woocommerce' ),
		'filterNone'       => __( 'No external callbacks detected.', 'cart-rest-api-for-woocommerce' ),
		/* translators: %s: Plugin name. */
		'managedBy'        => __( 'Managed by %s', 'cart-rest-api-for-woocommerce' ),
		'enabled'          => __( 'Enabled', 'cart-rest-api-for-woocommerce' ),
		'disabled'         => __( 'Disabled', 'cart-rest-api-for-woocommerce' ),
		'jwtDeactivated'   => __( 'JWT Authentication plugin deactivated.', 'cart-rest-api-for-woocommerce' ),
		'jwtError'         => __( 'Could not update the status of JWT Authentication plugin. Please check your permissions.', 'cart-rest-api-for-woocommerce' ),
		'toastReset'       => __( 'Settings reset to defaults. Save to apply.', 'cart-rest-api-for-woocommerce' ),
	),
);

wp_add_inline_script(
	'cocart-admin-settings',
	'var cocartSettingsConfig = ' . wp_json_encode( $script_data ) . ';',
	'before'
);
