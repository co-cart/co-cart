<?php
/**
 * Admin Page: Settings page for CoCart.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Pages
 * @since   4.9.0 Introduced.
 * @license GPL-3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CoCart_Admin_Settings_Page extends CoCart_Submenu_Page {

	/**
	 * Helper init method that runs on parent __construct
	 *
	 * @access protected
	 */
	protected function init() {
		add_filter( 'cocart_register_submenu_page', array( $this, 'register_submenu_page' ), 10 );
		add_action( 'wp_ajax_cocart_save_settings', array( $this, 'ajax_save_settings' ) );
		add_action( 'wp_ajax_cocart_install_jwt', array( $this, 'ajax_install_jwt' ) );
		add_action( 'wp_ajax_cocart_deactivate_jwt', array( $this, 'ajax_deactivate_jwt' ) );
	} // END init()

	/**
	 * Returns registered settings sections, merged with any registered by add-ons.
	 *
	 * Each section: [ 'id' => string, 'label' => string, 'priority' => int ]
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function get_sections() {
		$sections = array(
			'general'  => array(
				'label'    => __( 'General', 'cart-rest-api-for-woocommerce' ),
				'priority' => 10,
				'preview'  => true,
			),
			'cors'     => array(
				'label'    => __( 'API &amp; CORS', 'cart-rest-api-for-woocommerce' ),
				'priority' => 20,
			),
			'auth'     => array(
				'label'    => __( 'Auth', 'cart-rest-api-for-woocommerce' ),
				'priority' => 30,
			),
			'session'  => array(
				'label'    => __( 'Session', 'cart-rest-api-for-woocommerce' ),
				'priority' => 40,
			),
			'features' => array(
				'label'    => __( 'Features', 'cart-rest-api-for-woocommerce' ),
				'priority' => 50,
			),
		);

		/**
		 * Filter: cocart_settings_sections
		 *
		 * Add-ons use this filter to register new settings tabs.
		 *
		 * @since 4.9.0 Introduced.
		 *
		 * @param array<string, array<string, mixed>> $sections Keyed by section ID.
		 */
		$sections = apply_filters( 'cocart_settings_sections', $sections );

		uasort( $sections, static function ( $a, $b ) {
			return ( $a['priority'] ?? 10 ) <=> ( $b['priority'] ?? 10 );
		} );

		return $sections;
	} // END get_sections()

	/**
	 * Returns registered settings fields, merged with any registered by add-ons.
	 *
	 * Each field must include at minimum: id, section, label, type.
	 * Supported types: text, url, password, textarea, number, checkbox, select, readonly, custom.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function get_fields() {
		$fields = array(
			'general'  => $this->get_general_fields(),
			'cors'     => $this->get_cors_fields(),
			'auth'     => $this->get_auth_fields(),
			'session'  => $this->get_session_fields(),
			'features' => $this->get_features_fields(),
		);

		/**
		 * Filter: cocart_settings_fields
		 *
		 * Add-ons use this filter to register fields in any section (including their own).
		 *
		 * @since 4.9.0 Introduced.
		 *
		 * @param array<string, array<string, array<string, mixed>>> $fields Keyed by section ID, then field ID.
		 */
		return apply_filters( 'cocart_settings_fields', $fields );
	} // END get_fields()

	/**
	 * Returns the default values for all registered fields, keyed by section then field ID.
	 *
	 * Used to populate the Reset to defaults flow on the settings page.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function get_defaults() {
		$defaults = array();

		foreach ( $this->get_fields() as $section => $fields ) {
			foreach ( $fields as $id => $field ) {
				if ( ! in_array( $field['type'], array( 'readonly', 'custom' ), true ) && empty( $field['disabled'] ) ) {
					$defaults[ $section ][ $id ] = $field['default'] ?? '';
				}
			}
		}

		return $defaults;
	} // END get_defaults()

	/**
	 * Fields for the General section.
	 *
	 * None of these fields are functional in CoCart Community — they are
	 * shown as a disabled preview of functionality available in CoCart Plus/Pro.
	 *
	 * @access private
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_general_fields() {
		return array(
			'frontend_url'        => array(
				'label'       => __( 'Front-end site URL', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'url',
				'default'     => '',
				'placeholder' => 'https://',
				'description' => __( 'The full URL to your headless front-end, including https://. This is used for rewriting product permalinks to point to your front-end site.', 'cart-rest-api-for-woocommerce' ),
				'disabled'    => true,
			),
			'disable_wp_access'   => array(
				'label'       => __( 'Disable WordPress Access?', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => __( 'If enabled, users who are not administrators cannot access the WordPress site. Will redirect users to "Front-end site URL" instead if set above.', 'cart-rest-api-for-woocommerce' ),
				'disabled'    => true,
			),
			'accessible_page_ids' => array(
				'label'       => __( 'Accessible Pages', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'custom',
				'default'     => array(),
				'description' => __( 'Pages that remain accessible to all users when "Disable WordPress Access" is enabled. Cart and Checkout are always accessible.', 'cart-rest-api-for-woocommerce' ),
				'render_cb'   => array( $this, 'render_accessible_pages_field' ),
				'save_cb'     => null,
				'disabled'    => true,
			),
		);
	} // END get_general_fields()

	/**
	 * Fields for the CORS section.
	 *
	 * @access private
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_cors_fields() {
		return array(
			'enable_cors'    => array(
				'label'       => __( 'CORS support', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => __( 'Allow cross-origin requests from your headless frontend. Allows cross-origin requests from the origin set below.', 'cart-rest-api-for-woocommerce' ),
				'docs_url'    => 'https://docs.cocartapi.com/documentation/cors',
				'filter'      => array(
					'hook'   => 'cocart_disable_all_cors',
					'invert' => true,
					'locked' => true,
				),
			),
			'allowed_origin' => array(
				'label'       => __( 'Allowed Origin', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'url',
				'default'     => '',
				'placeholder' => 'https://my-frontend.example.com',
				'description' => __( 'The full URL of your headless frontend.', 'cart-rest-api-for-woocommerce' ),
				'docs_url'    => 'https://docs.cocartapi.com/documentation/cors#allowed-origins',
				'filter'      => array(
					'hook'   => 'cocart_allow_origin',
					'locked' => false,
				),
			),
		);
	} // END get_cors_fields()

	/**
	 * Fields for the Auth section.
	 *
	 * @access private
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_auth_fields() {
		return array(
			'jwt'                => array(
				'label'       => __( 'JWT Authentication', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'custom',
				'default'     => '',
				'description' => __( 'Installs &amp; enables or deactivates the JWT Authentication plugin for token-based authentication.', 'cart-rest-api-for-woocommerce' ),
				'render_cb'   => array( $this, 'render_jwt_field' ),
				'save_cb'     => null,
			),
			'auth_header'        => array(
				'label'       => __( 'Authorization Header', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'text',
				'default'     => '',
				'placeholder' => 'HTTP_AUTHORIZATION',
				'description' => __( 'The server variable used to read the authorization header. Change this if your server uses a non-standard variable.', 'cart-rest-api-for-woocommerce' ),
				'filter'      => array(
					'hook'     => 'cocart_auth_header',
					'docs_url' => 'https://docs.cocartapi.com/documentation/authentication',
					'locked'   => false,
				),
			),
			'allowed_safe_ports' => array(
				'label'       => __( 'Allowed Safe Ports', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'text',
				'default'     => '80, 443, 8080',
				'placeholder' => '80, 443, 8080',
				'description' => __( 'Comma-separated list of ports considered safe for cross-origin requests.', 'cart-rest-api-for-woocommerce' ),
				'disabled'    => true,
			),
		);
	} // END get_auth_fields()

	/**
	 * Fields for the Session section.
	 *
	 * @access private
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_session_fields() {
		return array(
			'guest_expiration_days'    => array(
				'label'       => __( 'Guest Session Expiry (days)', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'number',
				'default'     => 2,
				'min'         => 1,
				'max'         => 365,
				'step'        => 1,
				'description' => __( 'Days before a guest cart session expires. Default: 2.', 'cart-rest-api-for-woocommerce' ),
				'filter'      => array(
					'hook'   => 'cocart_cart_expiration',
					'locked' => false,
				),
			),
			'loggedin_expiration_days' => array(
				'label'       => __( 'Logged-in Session Expiry (days)', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'number',
				'default'     => 7,
				'min'         => 1,
				'max'         => 365,
				'step'        => 1,
				'description' => __( 'Days before a logged-in user cart session expires. Default: 7.', 'cart-rest-api-for-woocommerce' ),
				'filter'      => array(
					'hook'   => 'cocart_cart_expiration',
					'locked' => false,
					'notice' => false,
				),
			),
		);
	} // END get_session_fields()

	/**
	 * Fields for the Features section.
	 *
	 * @access private
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_features_fields() {
		return array(
			'load_cart_from_session' => array(
				'label'       => __( 'Load Cart from Session', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'checkbox',
				'default'     => 'yes',
				'description' => __( 'Allows loading a cart session via the native store. Disable if not using this feature.', 'cart-rest-api-for-woocommerce' ),
				'filter'      => array(
					'hook'   => 'cocart_disable_load_cart',
					'invert' => true,
					'locked' => true,
				),
			),
			'name_your_price'        => array(
				'label'       => __( 'Name Your Price', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => __( 'Allows customers to supply a custom price when adding items to the cart. Enable only if your store intentionally supports this pricing model.', 'cart-rest-api-for-woocommerce' ),
				'filter'      => array(
					'hook'   => 'cocart_does_product_allow_price_change',
					'locked' => true,
				),
			),
		);
	} // END get_features_fields()

	/**
	 * Render callback for the accessible pages custom field.
	 *
	 * This is a static preview only — "Disable WordPress Access" is not a
	 * feature available in CoCart Community.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param array<string, mixed> $field Field definition.
	 * @param mixed                $value Saved value (unused — preview only).
	 */
	public function render_accessible_pages_field( $field, $value ) {
		?>
		<div class="cocart-page-select cocart-page-select--disabled">
			<div class="cocart-page-select-tags">
				<span class="cocart-page-tag"><?php esc_html_e( 'Cart', 'cart-rest-api-for-woocommerce' ); ?> <span class="cocart-page-tag-remove">&times;</span></span>
				<span class="cocart-page-tag"><?php esc_html_e( 'Checkout', 'cart-rest-api-for-woocommerce' ); ?> <span class="cocart-page-tag-remove">&times;</span></span>
			</div>
			<input
				type="text"
				class="regular-text"
				placeholder="<?php esc_attr_e( 'Search for a page…', 'cart-rest-api-for-woocommerce' ); ?>"
				disabled
			>
		</div>
		<p class="description"><?php esc_html_e( 'Pages that remain accessible to all users when "Disable WordPress Access" is enabled. Cart and Checkout are always accessible.', 'cart-rest-api-for-woocommerce' ); ?></p>
		<?php
	} // END render_accessible_pages_field()

	/**
	 * Render callback for the JWT Authentication custom field.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param array<string, mixed> $field Field definition.
	 * @param mixed                $value Saved value (unused — JWT state is live plugin status).
	 */
	public function render_jwt_field( $field, $value ) {
		$jwt_is_active = is_plugin_active( 'cocart-jwt-authentication/cocart-jwt-authentication.php' );
		?>
		<label class="cocart-toggle" title="<?php echo $jwt_is_active ? esc_attr__( 'Enabled', 'cart-rest-api-for-woocommerce' ) : esc_attr__( 'Disabled', 'cart-rest-api-for-woocommerce' ); ?>">
			<input
				type="checkbox"
				id="auth-jwt-toggle"
				value="yes"
				<?php checked( $jwt_is_active, true ); ?>
			>
			<span class="cocart-toggle-slider"></span>
			<span class="cocart-status-label"><?php echo $jwt_is_active ? esc_html__( 'Enabled', 'cart-rest-api-for-woocommerce' ) : esc_html__( 'Disabled', 'cart-rest-api-for-woocommerce' ); ?></span>
		</label>
		<p class="description"><?php esc_html_e( 'Installs &amp; enables or deactivates the JWT Authentication plugin for token-based authentication.', 'cart-rest-api-for-woocommerce' ); ?></p>
		<?php
	} // END render_jwt_field()

	/**
	 * Callback for the HTML output for the settings page.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 */
	public function output() {
		$cocart_settings_page = $this;
		?>
		<div class="wrap cocart-wrapped cocart-wrapped--wide" role="main">
			<?php
			include_once COCART_ABSPATH . 'includes/classes/admin/views/html-settings.php';
			?>
		</div>
		<?php
	} // END output()

	/**
	 * Register the admin submenu page.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param array $submenu_pages Currently registered submenu pages.
	 *
	 * @return array $submenu_pages All registered submenu pages.
	 */
	public function register_submenu_page( $submenu_pages ) {
		if ( ! is_array( $submenu_pages ) ) {
			return $submenu_pages;
		}

		$submenu_pages['settings'] = array(
			'class_name' => 'CoCart_Admin_Settings_Page',
			'data'       => array(
				'page_title' => __( 'Settings', 'cart-rest-api-for-woocommerce' ),
				'menu_title' => __( 'Settings', 'cart-rest-api-for-woocommerce' ),
				/**
				 * Filters the capability required to access the CoCart settings page.
				 *
				 * @since 4.9.0 Introduced.
				 */
				'capability' => apply_filters( 'cocart_screen_capability', 'manage_options' ),
				'menu_slug'  => 'cocart-settings',
			),
		);

		return $submenu_pages;
	} // END register_submenu_page()

	/**
	 * AJAX handler: save settings.
	 *
	 * Loops through all registered fields from get_fields() and saves each one.
	 * Custom fields with a save_cb are delegated to their callback.
	 * Fields of type 'custom' or marked 'disabled' are skipped.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 */
	public function ajax_save_settings() {
		check_ajax_referer( 'cocart_settings_nonce', 'nonce' );

		/**
		 * Filters the capability required to save CoCart settings via AJAX.
		 *
		 * @since 4.9.0 Introduced.
		 */
		if ( ! current_user_can( apply_filters( 'cocart_screen_capability', 'manage_options' ) ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'cart-rest-api-for-woocommerce' ) ) );
		}

		$settings = get_option( 'cocart_settings', array() );

		foreach ( $this->get_fields() as $section => $fields ) {
			foreach ( $fields as $id => $field ) {
				$type = $field['type'];

				// Disabled preview fields are never saved.
				if ( ! empty( $field['disabled'] ) ) {
					continue;
				}

				// Custom fields with a save callback handle their own persistence.
				if ( 'custom' === $type ) {
					if ( ! empty( $field['save_cb'] ) && is_callable( $field['save_cb'] ) ) {
						call_user_func_array( $field['save_cb'], array( $field, &$settings ) );
					}
					continue;
				}

				// Readonly fields are never saved.
				if ( 'readonly' === $type ) {
					continue;
				}

				$post_key = $section . '_' . $id;

				$settings[ $section ][ $id ] = $this->sanitize_field( $field, $post_key, $section, $id );
			}
		}

		update_option( 'cocart_settings', $settings );

		wp_send_json_success( array( 'message' => __( 'Settings saved.', 'cart-rest-api-for-woocommerce' ) ) );
	} // END ajax_save_settings()

	/**
	 * Sanitize a single field value from $_POST.
	 *
	 * @access protected
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param array<string, mixed> $field    Field definition.
	 * @param string               $post_key Key to read from $_POST.
	 * @param string               $section  Section ID.
	 * @param string               $id       Field ID.
	 *
	 * @return mixed Sanitized value.
	 */
	protected function sanitize_field( $field, $post_key, $section = '', $id = '' ) {
		$type    = $field['type'];
		$default = $field['default'] ?? '';

		switch ( $type ) {
			case 'checkbox':
				return ( isset( $_POST[ $post_key ] ) && 'yes' === $_POST[ $post_key ] ) ? 'yes' : 'no'; // phpcs:ignore WordPress.Security.NonceVerification.Missing

			case 'url':
				return esc_url_raw( wp_unslash( isset( $_POST[ $post_key ] ) ? $_POST[ $post_key ] : '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			case 'number':
				$raw = isset( $_POST[ $post_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) ) : $default; // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$val = (int) $raw;
				if ( isset( $field['min'] ) ) {
					$val = max( $field['min'], $val );
				}
				if ( isset( $field['max'] ) ) {
					$val = min( $field['max'], $val );
				}
				return $val;

			case 'textarea':
				return sanitize_textarea_field( wp_unslash( isset( $_POST[ $post_key ] ) ? $_POST[ $post_key ] : '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

			case 'text':
			default:
				return sanitize_text_field( wp_unslash( isset( $_POST[ $post_key ] ) ? $_POST[ $post_key ] : $default ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
	} // END sanitize_field()

	/**
	 * AJAX handler: install JWT Authentication plugin in the background.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 */
	public function ajax_install_jwt() {
		check_ajax_referer( 'cocart_settings_nonce', 'nonce' );

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'cart-rest-api-for-woocommerce' ) ) );
		}

		if ( is_plugin_active( 'cocart-jwt-authentication/cocart-jwt-authentication.php' ) ) {
			wp_send_json_success( array( 'message' => __( 'Already installed.', 'cart-rest-api-for-woocommerce' ) ) );
		}

		WC_Install::background_installer(
			'cocart-jwt-authentication',
			array(
				'name'      => 'CoCart - JWT Authentication',
				'repo-slug' => 'cocart-jwt-authentication',
			)
		);

		wp_send_json_success( array( 'message' => __( 'Installing in background.', 'cart-rest-api-for-woocommerce' ) ) );
	} // END ajax_install_jwt()

	/**
	 * AJAX handler: deactivate JWT Authentication plugin.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 */
	public function ajax_deactivate_jwt() {
		check_ajax_referer( 'cocart_settings_nonce', 'nonce' );

		if ( ! current_user_can( 'deactivate_plugins' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'cart-rest-api-for-woocommerce' ) ) );
		}

		deactivate_plugins( 'cocart-jwt-authentication/cocart-jwt-authentication.php' );

		wp_send_json_success( array( 'message' => __( 'Plugin deactivated.', 'cart-rest-api-for-woocommerce' ) ) );
	} // END ajax_deactivate_jwt()
} // END class

return new CoCart_Admin_Settings_Page();
