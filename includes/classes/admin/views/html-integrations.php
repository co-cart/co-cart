<?php
/**
 * Admin View: Integrations page.
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

$integrations = CoCart_Integrations::get_all();
?>
<h1><?php esc_html_e( 'Integrations', 'cart-rest-api-for-woocommerce' ); ?></h1>
<p><?php esc_html_e( 'Manage integrations with supported plugins. Disable any you do not use to reduce unnecessary processing on each request.', 'cart-rest-api-for-woocommerce' ); ?></p>

<div class="cocart-integrations-search-wrap">
	<input
		type="search"
		id="cocart-integrations-search"
		class="cocart-integrations-search regular-text"
		placeholder="<?php esc_attr_e( 'Search integrations...', 'cart-rest-api-for-woocommerce' ); ?>"
		autocomplete="off"
	>
	<span class="spinner" id="cocart-integrations-search-spinner"></span>
</div>

<div id="cocart-integrations-toast" class="cocart-integrations-toast" aria-live="polite"></div>

<div class="cocart-integrations-grid" id="cocart-integrations-grid">
	<?php
	if ( empty( $integrations ) ) {
		echo '<p class="cocart-integrations-no-results">' . esc_html__( 'No integrations available.', 'cart-rest-api-for-woocommerce' ) . '</p>';
	} else {
		foreach ( $integrations as $slug => $args ) {
			$enabled   = CoCart_Integrations::is_enabled( $slug );
			$available = CoCart_Integrations::can_be_enabled( $slug );
			include __DIR__ . '/html-integrations-card.php';
		}
	}
	?>
</div>

<script>
( function( $ ) {
	var config = 
	<?php
	echo wp_json_encode( array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'cocart_integrations_nonce' ),
		'i18n'    => array(
			'enabled'       => __( 'Enabled', 'cart-rest-api-for-woocommerce' ),
			'disabled'      => __( 'Disabled', 'cart-rest-api-for-woocommerce' ),
			'error'         => __( 'Something went wrong. Please try again.', 'cart-rest-api-for-woocommerce' ),
			'toastEnabled'  => __( 'Integration enabled.', 'cart-rest-api-for-woocommerce' ),
			'toastDisabled' => __( 'Integration disabled.', 'cart-rest-api-for-woocommerce' ),
			'toastError'    => __( 'Could not save. Please try again.', 'cart-rest-api-for-woocommerce' ),
		),
	) );
	?>
	;

	var $grid          = $( '#cocart-integrations-grid' );
	var $searchInput   = $( '#cocart-integrations-search' );
	var $searchSpinner = $( '#cocart-integrations-search-spinner' );
	var $toast         = $( '#cocart-integrations-toast' );
	var toastTimer;
	var searchTimer;

	function showToast( message, type ) {
		clearTimeout( toastTimer );
		$toast.removeClass( 'is-success is-error' ).addClass( 'is-active is-' + type ).text( message );
		toastTimer = setTimeout( function() {
			$toast.removeClass( 'is-active' );
		}, 4000 );
	}

	// Toggle integration on/off.
	$grid.on( 'change', '.cocart-integration-toggle', function() {
		var $checkbox = $( this );
		var slug      = $checkbox.data( 'slug' );
		var enabled   = $checkbox.is( ':checked' );
		var $label    = $checkbox.closest( '.cocart-toggle' );
		var $status   = $label.find( '.cocart-integration-status-label' );
		var $footer   = $label.closest( '.cocart-integration-footer-left' );
		var $spinner  = $( '<span class="spinner is-active"></span>' );

		$checkbox.prop( 'disabled', true );
		$status.css( 'visibility', 'hidden' );
		$footer.append( $spinner );

		$.ajax( {
			url:    config.ajaxUrl,
			method: 'POST',
			data:   {
				action:  'cocart_toggle_integration',
				nonce:   config.nonce,
				slug:    slug,
				enabled: enabled ? 'true' : 'false',
			},
			success: function( response ) {
				$spinner.remove();
				$checkbox.prop( 'disabled', false );
				$status.css( 'visibility', '' );

				if ( response.success ) {
					var isEnabled = response.data.enabled;
					var $card     = $checkbox.closest( '.cocart-integration-card' );

					$card.removeClass( 'is-enabled is-disabled' );
					$card.addClass( isEnabled ? 'is-enabled' : 'is-disabled' );

					$label.attr( 'title', isEnabled ? config.i18n.enabled : config.i18n.disabled );
					$status.text( isEnabled ? config.i18n.enabled : config.i18n.disabled );
					showToast( isEnabled ? config.i18n.toastEnabled : config.i18n.toastDisabled, 'success' );
				} else {
					$checkbox.prop( 'checked', ! enabled );
					showToast( config.i18n.toastError, 'error' );
				}
			},
			error: function() {
				$spinner.remove();
				$checkbox.prop( 'disabled', false ).prop( 'checked', ! enabled );
				$status.css( 'visibility', '' );
				showToast( config.i18n.toastError, 'error' );
			},
		} );
	} );

	// Search integrations (debounced, 300 ms).
	$searchInput.on( 'input', function() {
		clearTimeout( searchTimer );
		var query = $( this ).val();

		searchTimer = setTimeout( function() {
			$searchSpinner.addClass( 'is-active' );

			$.ajax( {
				url:    config.ajaxUrl,
				method: 'POST',
				data:   {
					action: 'cocart_search_integrations',
					nonce:  config.nonce,
					query:  query,
				},
				success: function( response ) {
					$searchSpinner.removeClass( 'is-active' );
					if ( response.success ) {
						$grid.html( response.data.html );
					}
				},
				error: function() {
					$searchSpinner.removeClass( 'is-active' );
				},
			} );
		}, 300 );
	} );
} )( jQuery );
</script>
