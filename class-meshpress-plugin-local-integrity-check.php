<?php
/**
 * This file checks the integrity of the WordPress plugin locally
 * during activation, updates, and at periodic intervals.
 *
 * @package MeshPress
 */

namespace MeshPress;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! trait_exists( 'MeshPress\Plugin_Local_Integrity_Check' ) ) {

	/**
	 * Trait for handling plugin integrity checks.
	 */
	trait Plugin_Local_Integrity_Check {
		/**
		 * The slug of the plugin.
		 *
		 * @var string
		 */
		protected string $plugin_slug;

		/**
		 * The main plugin file path.
		 *
		 * @var string
		 */
		protected string $plugin_file;

		/**
		 * The plugin directory path.
		 *
		 * @var string
		 */
		protected string $plugin_dir;

		/**
		 * The option key for storing integrity notices.
		 *
		 * @var string
		 */
		protected string $plugin_integrity_notice;

		/**
		 * The name of the checksum file.
		 *
		 * @var string
		 */
		protected string $checksum_file_name = 'checksum.md5';

		/**
		 * Initialize the integrity check process.
		 *
		 * @param string $plugin_slug The slug of the plugin.
		 * @param string $plugin_file The main plugin file path.
		 */
		public function initialize_integrity_check( string $plugin_slug, string $plugin_file ) {
			$this->plugin_slug             = $plugin_slug;
			$this->plugin_file             = $plugin_file;
			$this->plugin_dir              = plugin_dir_path( $this->plugin_file );
			$this->plugin_integrity_notice = $this->plugin_slug . '_integrity_notice';

			// Check integrity once update is completed.
			add_action( 'upgrader_process_complete', array( $this, 'check_plugin_integrity_on_update' ), 10, 2 );

			// Display admin notices if issues are found.
			add_action( 'admin_notices', array( $this, 'display_integrity_notices' ) );

			// Perform a scheduled checksum check.
			add_action( 'admin_init', array( $this, 'plugin_scheduled_checksum_check' ) );
		} // END initialize_integrity_check()

		/**
		 * Display integrity notices in the admin area.
		 */
		public function display_integrity_notices() {
			if ( file_exists( $this->plugin_dir . $this->checksum_file_name ) ) {
				$errors = get_option( $this->plugin_integrity_notice, null );

				if ( ! empty( $errors ) ) {
					echo '<div class="notice notice-warning">';
					if ( is_array( $errors ) ) { ?>
						<p><strong><?php echo esc_html__( 'Warning:', 'cocart-core' ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain ?></strong> <?php echo esc_html__( 'The following plugin files are either missing or do not match the expected checksum and may have been modified:', 'cocart-core' ); // phpcs:ignore WordPress.WP.I18n.MissingArgDomain, WordPress.WP.I18n.NonSingularStringLiteralDomain ?></p>
						<ul>
							<?php foreach ( $errors as $file ) : ?>
								<li><?php echo $file; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
							<?php endforeach; ?>
						</ul>
						<?php
					} else {
						echo '<p><strong>' . esc_html__( 'Warning:', 'cocart-core' ) . '</strong> ' . $errors . '</p>'; // phpcs:ignore WordPress.WP.I18n.MissingArgDomain, WordPress.WP.I18n.NonSingularStringLiteralDomain, WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					echo '</div>';
				}
			}
		} // END display_integrity_notices()

		/**
		 * Display a warning if the file is missing or every 6 hours perform a checksum validation.
		 */
		public function plugin_scheduled_checksum_check() {
			if ( ! file_exists( untrailingslashit( $this->plugin_dir ) . '/' . $this->checksum_file_name ) ) {
				add_action( 'admin_notices', array( $this, 'plugin_checksum_missing_warning' ) );
			} else {
				$this->validate_file_integrity();
			}
		} // END plugin_daily_checksum_check()

		/**
		 * Display a warning notice if the checksum file is missing.
		 */
		public function plugin_checksum_missing_warning() {
			$plugin_data = get_plugin_data( $this->plugin_file );
			$plugin_name = $plugin_data['Name'];
			?>
			<div class="notice notice-warning">
				<p><strong><?php echo esc_html__( 'Warning:', 'cocart-core' ); // phpcs:ignore WordPress.WP.I18n.MissingArgDomain, WordPress.WP.I18n.NonSingularStringLiteralDomain ?></strong> 
					<?php
					printf(
						/* translators: %s = Plugin name */
						esc_html__( 'The checksum file for "%s" is missing. This may indicate the plugin has been altered. Please verify your installation.', 'cocart-core' ), // phpcs:ignore WordPress.WP.I18n.MissingArgDomain, WordPress.WP.I18n.NonSingularStringLiteralDomain
						esc_html( $plugin_name )
					);
					?>
				</p>
			</div>
			<?php
		} // END plugin_checksum_missing_warning()

		/**
		 * Check for the checksum file upon activation.
		 */
		public function plugin_activation_checksum_check() {
			// Check if the checksum file exists.
			if ( ! file_exists( untrailingslashit( $this->plugin_dir ) . '/' . $this->checksum_file_name ) ) {
				// Deactivate the plugin.
				deactivate_plugins( plugin_basename( $this->plugin_file ) );

				wp_die( esc_html__( 'Warning: For your security, the plugin did not activate because an important file is missing. This may indicate the plugin has been altered. Please contact support for help.', 'cocart-core' ) ); // phpcs:ignore WordPress.WP.I18n.MissingArgDomain, WordPress.WP.I18n.NonSingularStringLiteralDomain
			}
		} // END plugin_activation_checksum_check()

		/**
		 * Check integrity once plugin updated.
		 *
		 * @param object $upgrader   Instance of the Plugin_Upgrader class.
		 * @param array  $hook_extra An associative array that contains additional information about the upgrade process.
		 */
		public function check_plugin_integrity_on_update( $upgrader, $hook_extra ) {
			// Get the current plugin version and slug from the header.
			$plugin_data     = get_plugin_data( $this->plugin_file );
			$plugin_name     = $plugin_data['Name'];
			$current_version = $plugin_data['Version'];

			// Check if the current version is stable.
			if ( preg_match( '/^(dev|alpha|beta|rc|release candidate|rc\d*)/i', $current_version ) ) {
				return; // Exit if the version is not stable.
			}

			// Verify that the plugin slug matches the expected slug.
			if ( 'plugin' === $hook_extra['type'] && ! empty( $hook_extra['result'] ) && is_array( $hook_extra['result'] ) ) {
				$plugin_found = false;

				foreach ( $hook_extra['result'] as $plugin => $details ) {
					if ( strpos( $plugin ) !== false ) {
						$plugin_found = true;
						break;
					}
				}

				if ( ! $plugin_found ) {
					// If the plugin slug wasn't found, output an error message or handle it here.
					update_option( $this->plugin_integrity_notice,
						sprintf(
							/* translators: %s = Plugin name */
							__( 'There appears to be an issue matching the plugin installed for "%s".', 'cocart-core' ), // phpcs:ignore WordPress.WP.I18n.MissingArgDomain, WordPress.WP.I18n.NonSingularStringLiteralDomain
							$plugin_name
						)
					);
					return;
				}
			}

			// Get the content of the checksum file.
			if ( ! file_exists( $this->plugin_dir . $this->checksum_file_name ) ) {
				update_option(
					$this->plugin_integrity_notice,
					sprintf(
						/* translators: %1$s = Plugin name, %2$s = Plugin name */
						__( 'The checksum file for "%1$s" is missing so we can\'t verify it\'s integrity. Please download "%2$s" from an official source.', 'cocart-core' ), // phpcs:ignore WordPress.WP.I18n.MissingArgDomain, WordPress.WP.I18n.NonSingularStringLiteralDomain
						$plugin_name,
						$plugin_name
					)
				);
				return;
			}

			self::validate_file_integrity();
		} // END check_plugin_integrity_on_update()

		protected function validate_file_integrity() {
			// Prevent redundant checks.
			if ( get_transient( $this->plugin_slug . '_integrity_checked' ) ) {
				return;
			}

			// Set a transient for 6 hours.
			set_transient( $this->plugin_slug . '_integrity_checked', true, HOUR_IN_SECONDS * 6 );

			$checksums = $this->fetch_checksums();
			$errors    = array();

			// Verify each file's checksum.
			foreach ( $checksums as $file_path => $expected_hash ) {
				$md5_file_path = $this->plugin_dir . $file_path;

				if ( strpos( $file_path, './' ) === 0 ) { // Check if './' is at the beginning.
					$file_path = substr( $file_path, 2 ); // Remove the first two characters.
				}

				$file_full_path = $this->plugin_dir . $file_path;

				// Skip if the file doesn't exist.
				if ( ! file_exists( $file_full_path ) ) {
					$errors[] = esc_html__( 'File missing:', 'cocart-core' ) . ' ' . '<u>' . untrailingslashit( $file_full_path ) . '</u>'; // phpcs:ignore WordPress.WP.I18n.MissingArgDomain, WordPress.WP.I18n.NonSingularStringLiteralDomain

					if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
						error_log( esc_html__( 'File missing:', 'cocart-core' ) . ' ' . untrailingslashit( $file_full_path ) ); // phpcs:ignore WordPress.WP.I18n.MissingArgDomain, WordPress.WP.I18n.NonSingularStringLiteralDomain
					}
					continue;
				}

				// Calculate the MD5 hash of the file.
				$file_hash = md5_file( $md5_file_path );

				// Compare hashes.
				if ( $file_hash !== $expected_hash ) {
					$errors[] = sprintf(
						/* translators: 1: File path, 2: Expected hash, 3: Found hash */
						__( 'File doesn\'t verify against checksum: %1$s. Expected: %2$s, got: %3$s.', 'cocart-core' ), // phpcs:ignore WordPress.WP.I18n.MissingArgDomain
						'<u>' . esc_html( untrailingslashit( $file_full_path ) ) . '</u>',
						'<strong style="color:green">' . esc_html( $expected_hash ) . '</strong>',
						'<strong style="color:red">' . esc_html( $file_hash ) . '</strong>'
					);

					if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
						error_log(
							sprintf(
								/* translators: 1 = File path, 2 = Expected hash, 3 = Found hash */
								esc_html__( 'File doesn\'t verify against checksum: %1$s. Expected: %2$s got %3$s.', 'cocart-core' ), // phpcs:ignore WordPress.WP.I18n.MissingArgDomain, WordPress.WP.I18n.NonSingularStringLiteralDomain
								untrailingslashit( $file_full_path ),
								esc_html( $expected_hash ),
								esc_html( $file_hash )
							)
						);
					}
				}
			}

			if ( ! empty( $errors ) ) {
				update_option( $this->plugin_integrity_notice, $errors );
			}
		} // END validate_file_integrity()

		/**
		 * Fetch checksums from the checksum file.
		 *
		 * @return array Parsed checksums as [file => hash].
		 */
		protected function fetch_checksums() {
			$md5_content = file_get_contents( $this->plugin_dir . $this->checksum_file_name ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			$checksums   = array();

			foreach ( explode( "\n", $md5_content ) as $line ) {
				if ( preg_match( '/^([a-f0-9]{32})\s+\*(.+)$/', $line, $matches ) ) {
					$file_path = $matches[2];

					// Skip the checksum file itself.
					if ( $file_path === $this->checksum_file_name ) {
						continue;
					}

					$checksums[ $file_path ] = $matches[1];
				}
			}

			return $checksums;
		} // END fetch_checksums()
	} // END class
}
