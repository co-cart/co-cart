<?php
/**
 * CoCart - Admin.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin
 * @since   1.2.0 Introduced.
 * @version 5.0.0
 * @license GPL-3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Admin' ) ) {

	class CoCart_Admin {

		/**
		 * Constructor
		 *
		 * @access public
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'includes' ) );

			// Just in case a developer wants to add support for CoCart within a WordPress theme.
			add_filter( 'extra_theme_headers', array( $this, 'enable_cocart_plugin_headers' ) );

			// Plugin Updates.
			add_filter( 'extra_plugin_headers', array( $this, 'enable_cocart_plugin_headers' ) );
			add_filter( 'auto_update_plugin', array( $this, 'cocart_prevent_dangerous_auto_updates' ), 99, 2 );

			// Admin screens.
			add_action( 'current_screen', array( $this, 'conditional_includes' ) );
			add_action( 'admin_init', array( $this, 'admin_redirects' ) );

			// License modal.
			add_action( 'admin_footer', array( $this, 'add_license_modal' ) );
		} // END __construct()

		/**
		 * Include any classes we need within admin.
		 *
		 * @access public
		 *
		 * @since 1.2.0 Introduced.
		 */
		public function includes() {
			// Required files.
			include_once __DIR__ . '/abstract/abstract-class-submenu-page.php';                     // Admin Abstracts.
			require_once __DIR__ . '/class-cocart-admin-assets.php';                                // Admin Assets.
			require_once __DIR__ . '/class-cocart-admin-footer.php';                                // Admin Footer.
			require_once __DIR__ . '/class-cocart-admin-help-tab.php';                              // Admin Help Tab.
			require_once __DIR__ . '/class-cocart-admin-menus.php';                                 // Admin Menus.
			require_once __DIR__ . '/class-cocart-admin-notices.php';                               // Plugin Notices.

			// Plugin identification and updates.
			include_once __DIR__ . '/abstract/abstract-class-plugin-updates.php';                   // Plugin identification.
			require_once __DIR__ . '/class-cocart-admin-updates.php';                               // Plugin Updates.

			// Plugin search and suggestions.
			require_once __DIR__ . '/plugin-suggestions/class-cocart-admin-plugin-suggestions.php'; // Plugin Suggestions.
			require_once __DIR__ . '/plugin-suggestions/class-cocart-admin-plugin-search.php';      // Plugin Search.

			// For WooCommerce.
			include_once __DIR__ . '/woocommerce/class-cocart-wc-admin-notices.php';                // WooCommerce Admin Notices.
			include_once __DIR__ . '/woocommerce/class-cocart-wc-admin-system-status.php';          // WooCommerce System Status.

			// Pages.
			require_once __DIR__ . '/pages/class-cocart-admin-pages-support.php';                   // Support page.
			require_once __DIR__ . '/pages/class-cocart-admin-pages-updates.php';                   // Updates/License Manager page.
			require_once __DIR__ . '/class-cocart-admin-setup-wizard.php';                          // Setup Wizard.
		} // END includes()

		/**
		 * Include admin files conditionally.
		 *
		 * @access public
		 *
		 * @since 3.0.0 Introduced.
		 */
		public function conditional_includes() {
			$screen = get_current_screen();

			if ( ! $screen ) {
				return;
			}

			switch ( $screen->id ) {
				case 'plugins':
					require_once __DIR__ . '/class-cocart-admin-action-links.php';                          // Plugin Action Links.
					require_once __DIR__ . '/plugin-updates/class-cocart-admin-addon-update-watcher.php';   // Add-on Update Watcher.
					require_once __DIR__ . '/plugin-updates/class-cocart-admin-plugin-screen-update.php';   // Plugin Update.
					break;
				case 'update-core':
					require_once __DIR__ . '/plugin-updates/class-cocart-admin-updates-screen-updates.php'; // Screen Updates.
					break;
			}
		} // END conditional_includes()

		/**
		 * Handle redirects to setup/welcome page after install and updates.
		 *
		 * For setup wizard, transient must be present, the user must have access rights, and we must ignore the network/bulk plugin updaters.
		 *
		 * @access public
		 *
		 * @since 3.1.0 Introduced.
		 */
		public function admin_redirects() {
			// If WooCommerce does not exists then do nothing as we require functions from WooCommerce to function!
			if ( ! class_exists( 'WooCommerce' ) ) {
				return;
			}

			// Prevent any further admin redirects if CoCart database failed to create.
			if ( get_transient( '_cocart_db_creation_failed' ) ) {
				return;
			}

			// Setup wizard redirect.
			if ( get_transient( '_cocart_activation_redirect' ) && apply_filters( 'cocart_enable_setup_wizard', true ) ) {
				$do_redirect  = true;
				$current_page = isset( $_GET['page'] ) ? wc_clean( sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				// On these pages, or during these events, postpone the redirect.
				if ( wp_doing_ajax() || is_network_admin() || ! current_user_can( 'manage_options' ) ) {
					$do_redirect = false;
				}

				// On these pages, or during these events, disable the redirect.
				if ( 'cocart-setup' === $current_page || ! CoCart_Admin_Notices::has_notice( 'setup_wizard' ) || apply_filters( 'cocart_prevent_automatic_wizard_redirect', false ) || isset( $_GET['activate-multi'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					CoCart_Utilities_Cache_Helpers::queue_delete_transient( '_cocart_activation_redirect' );
					$do_redirect = false;
				}

				if ( $do_redirect ) {
					CoCart_Utilities_Cache_Helpers::queue_delete_transient( '_cocart_activation_redirect' );
					wp_safe_redirect( admin_url( 'admin.php?page=cocart-setup' ) );
					exit;
				}
			}
		} // END admin_redirects()

		/**
		 * Read in CoCart headers when reading plugin headers.
		 *
		 * @access public
		 *
		 * @since 4.3.0 Introduced.
		 *
		 * @param array $headers Headers.
		 *
		 * @return array
		 */
		public function enable_cocart_plugin_headers( $headers ) {
			if ( ! class_exists( 'CoCart_Admin_Plugin_Updates' ) ) {
				include_once __DIR__ . '/plugin-updates/class-cocart-admin-plugin-updates.php';
			}

			// CoCart requires at least - allows developers to define which version of CoCart the plugin requires to run.
			$headers[] = CoCart_Admin_Plugin_Updates::VERSION_REQUIRED_HEADER;

			// CoCart tested up to - allows developers  to define which version of CoCart they have tested up to.
			$headers[] = CoCart_Admin_Plugin_Updates::VERSION_TESTED_HEADER;

			$headers[] = 'CoCart';

			return $headers;
		} // END enable_cocart_plugin_headers()

		/**
		 * Prevent auto-updating the CoCart plugin on major releases if there are untested extensions active.
		 *
		 * @access public
		 *
		 * @since 4.3.0 Introduced.
		 *
		 * @param bool   $should_update If should update.
		 * @param object $plugin        Plugin data.
		 *
		 * @return bool
		 */
		public function cocart_prevent_dangerous_auto_updates( $should_update, $plugin ) {
			if ( ! isset( $plugin->plugin, $plugin->new_version ) ) {
				return $should_update;
			}

			if ( COCART_SLUG . '/' . COCART_SLUG . '.php' !== $plugin->plugin ) {
				return $should_update;
			}

			if ( ! class_exists( 'CoCart_Admin_Plugin_Updates' ) ) {
				include_once __DIR__ . '/plugin-updates/class-cocart-admin-plugin-updates.php';
			}

			$new_version      = sanitize_text_field( $plugin->new_version );
			$plugin_updates   = new CoCart_Admin_Plugin_Updates();
			$untested_plugins = $plugin_updates->get_untested_plugins( $new_version, 'major' );
			if ( ! empty( $untested_plugins ) ) {
				return false;
			}

			return $should_update;
		} // END cocart_prevent_dangerous_auto_updates()

		/**
		 * Adds the license modal HTML to the admin footer.
		 *
		 * This modal is used to enter a license key for CoCart.
		 *
		 * @access public
		 *
		 * @since 5.0.0 Introduced.
		 */
		public function add_license_modal() {
			$get_license = CoCart_Helpers::build_shortlink( COCART_STORE_URL . 'pricing/' );
			?>
			<div id="cocart-license-modal" style="display:none;">
				<div class="cocart-modal-content">
					<div class="cocart-modal-header">
						<h4><?php esc_html_e( 'License Key', 'cocart-core' ); ?></h4>
					</div>
					<div class="cocart-modal-body">
						<p><?php esc_html_e( 'Please enter your license key below to activate.', 'cocart-core' ); ?></p>
						<label style="display: none;"><?php esc_html_e( 'License Key:', 'cocart-core' ); ?></label>
						<input type="text" id="cocart-license-key" placeholder="<?php esc_attr_e( 'Enter your license key', 'cocart-core' ); ?>">
						<p><?php esc_html_e( 'Not entering a license key does not prevent the plugin from functioning but you will not be able to install any updates made available.', 'cocart-core' ); ?></p>
						<p>
						<?php
						echo wp_kses(
							sprintf(
								/* translators: %s = Link to pricing page. */
								__( 'Don&#8217;t have a license key? <a href="%s" target="_blank">Purchase one</a> from the CoCart website.', 'cocart-core' ),
								$get_license
							),
							array(
								'a' => array(
									'href'   => array(),
									'target' => array(),
								),
							)
						);
						?>
						</p>
					</div>
					<div class="cocart-modal-footer">
						<button class="button button-primary stateful-button" id="cocart-save-license">
							<span class="default-state"><?php esc_html_e( 'Save License', 'cocart-core' ); ?></span>
							<span class="loading-state">
								<svg class="animate-spin" width="16" height="16" viewBox="0 0 16 16" fill="none">
									<path d="M14 8a6 6 0 1 1-1.752-4.248" stroke="currentColor" stroke-width="2" />
								</svg>
								<?php esc_html_e( 'Activating...', 'cocart-core' ); ?>
							</span>
							<span class="success-state">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none">
									<path d="M3 8l3.5 3.5L12 5" stroke="currentColor" stroke-width="2" />
								</svg>
								<?php esc_html_e( 'Activated!', 'cocart-core' ); ?>
							</span>
							<span class="error-state">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none">
									<path d="M8 1a7 7 0 100 14A7 7 0 008 1zm0 10.5a.75.75 0 110 1.5.75.75 0 010-1.5zm0-8a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 018 3.5z" fill="currentColor"/>
								</svg>
								<?php esc_html_e( 'Failed!', 'cocart-core' ); ?>
							</span>
						</button>
						<button class="button" id="cocart-cancel-license"><?php esc_html_e( 'Cancel', 'cocart-core' ); ?></button>
					</div>
				</div>
			</div>
			<script>
			jQuery(document).ready(function($) {
				// Add confetti script
				var confettiScript = document.createElement('script');
				confettiScript.src = 'https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js';
				document.head.appendChild(confettiScript);

				var isProcessing = false;

				$('.cocart-open-license-modal').click(function(e) {
					e.preventDefault();
					var name = $(this).data('plugin-name');
					var plugin = $(this).data('plugin');

					// Position modal in center of screen
					var modal = $('#cocart-license-modal');
					modal.css({
						'background': 'rgba(0,0,0,.6)',
						'display': 'none',
						'height': '100%',
						'overflow': 'auto',
						'position': 'fixed',
						'top': '0',
						'width': '100%',
						'z-index': '100000'
					}).show();

					var content = modal.find('.cocart-modal-content');

					content.css({
						'background': '#fff',
						'border-radius': '5px',
						'box-shadow': '0 0 10px rgba(0,0,0,0.3)',
						'margin': '0 auto',
						'position': 'relative',
						'top': '30%',
						'width': '480px',
						'max-width': '100%',
					});

					var header = content.find('.cocart-modal-header');

					header.css({
						'background': '#fbfbfb',
						'border-bottom': '1px solid #eee',
						'padding': '0.5em 1em',
						'position': 'relative',
					});

					// Set the title based on the plugin.
					header.find('h4').text(
					<?php
					echo wp_json_encode(
						/* translators: %s is the plugin name */
						__( 'License Key for %s', 'cocart-core' )
					);
					?>
					.replace('%s', name));

					header.find('h4').css({
						'font-size': '1.2em',
						'font-weight': '700',
						'letter-spacing': '.6px',
						'margin': '0',
						'padding': '0',
						'text-shadow': '1px 1px 1px #fff',
						'text-transform': 'uppercase',
					});

					body = content.find('.cocart-modal-body');
					footer = content.find('.cocart-modal-footer');

					body.css({
						'background': '#fefefe',
						'border-bottom': '0',
						'padding': '1em',
					});
					footer.css({
						'background': '#fefefe',
						'border-top': '1px solid #eee',
						'padding': '1em',
						'text-align': 'right',
					});

					var license_key = body.find('#cocart-license-key');

					// Clear the input field
					license_key.val('');

					license_key.css({
						'width': '100%',
						'padding': '0.5em',
						'border': '1px solid #ccc',
						'border-radius': '3px',
						'box-sizing': 'border-box',
						'box-shadow': 'none',
					});

					// Add styles for stateful button
					$('<style>')
						.text(`
							@keyframes shake { 0%, 100% { transform: translateX(0); } 20%, 60% { transform: translateX(-10px); } 40%, 80% { transform: translateX(10px); } }
							.cocart-error { border-color: #dc3232 !important; }
							.stateful-button { position: relative; }
							.stateful-button .loading-state,
							.stateful-button .success-state,
							.stateful-button .error-state { display: none; }
							.stateful-button.success[disabled] { background-color: #00c950 !important; border-color: #00c950 !important; color: #fff !important; }
							.stateful-button.error[disabled] { background-color: #fb2c36 !important; border-color: #fb2c36 !important; color: #fff !important; }
							.stateful-button.loading .default-state,
							.stateful-button.success .default-state,
							.stateful-button.error .default-state { display: none; }
							.stateful-button.loading .loading-state,
							.stateful-button.success .success-state,
							.stateful-button.error .error-state { display: inline-flex; align-items: center; gap: 6px; }
							.animate-spin { animation: spin 1s linear infinite; }
							@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
						`)
						.appendTo('head');

					// Handle save with states
					$('#cocart-save-license').off('click').on('click', function() {
						var $button = $(this);
						var $cancelButton = $('#cocart-cancel-license');
						var license = $('#cocart-license-key').val();
						var uuidPattern = /^.*[A-F0-9]{8}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{12}$/;

						if (!license || !uuidPattern.test(license.toUpperCase())) {
							var content = $('.cocart-modal-content');
							content.css('animation', 'shake 0.5s');
							$('#cocart-license-key').addClass('cocart-error');
							$('#cocart-license-key').focus();
							setTimeout(function() {
								content.css('animation', '');
							}, 500);
							return;
						}

						// Show loading state and disable controls
						isProcessing = true;
						$button.addClass('loading');
						$button.prop('disabled', true);
						$cancelButton.prop('disabled', true);

						// Simulate API call - replace with actual license validation
						setTimeout(function() {
							// Simulate random success/failure
							if (Math.random() > 0.5) { // Success
								$button.removeClass('loading').addClass('success');

								// Trigger confetti
								if (window.confetti) {
									// Create confetti
									confetti({
										particleCount: 100,
										spread: 100,
										origin: {
											x: 0.5, // Center horizontally
											y: 0.6  // Above the modal
										},
										zIndex: 100001 // Higher than modal z-index
									});

									setTimeout(() => {
										confetti.reset();
									}, 3000);
								}

								// Close modal after showing success
								setTimeout(function() {
									isProcessing = false;
									$('#cocart-license-modal').hide();
									$button.removeClass('success');
									$button.prop('disabled', false);
									$cancelButton.prop('disabled', false);
								}, 3000);
							} else { // Failure
								$button.removeClass('loading').addClass('error');

								// Reset after showing error
								setTimeout(function() {
									isProcessing = false;
									$button.removeClass('error');
									$button.prop('disabled', false);
									$cancelButton.prop('disabled', false);
								}, 2000);
							}
						}, 1500);
					});

					// Remove error state when user starts typing only if format is valid
					$('#cocart-license-key').on('input', function() {
						var license = $(this).val();
						var uuidPattern = /^.*[A-F0-9]{8}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{12}$/;

						if (license && uuidPattern.test(license.toUpperCase())) {
							$(this).removeClass('cocart-error');
						} else if (license) {
							$(this).addClass('cocart-error');
						}
					});

					// Add shake animation and error style
					$('<style>')
						.text('@keyframes shake { 0%, 100% { transform: translateX(0); } 20%, 60% { transform: translateX(-10px); } 40%, 80% { transform: translateX(10px); } }' +
							'.cocart-error { border-color: #dc3232 !important; }')
						.appendTo('head');

					// Handle cancel
					$('#cocart-cancel-license').off('click').on('click', function() {
						if (isProcessing) return;
						$('#cocart-license-key').removeClass('cocart-error');
						modal.hide();
					});

					// Close on clicking outside - prevent if processing
					$(document).mouseup(function(e) {
						if (isProcessing) return;
						if (!content.is(e.target) && content.has(e.target).length === 0) {
							$('#cocart-license-key').removeClass('cocart-error');
							modal.hide();
						}
					});
				});
			});
			</script>
			<?php
		} // END add_license_modal()
	} // END class

} // END if class exists

return new CoCart_Admin();
