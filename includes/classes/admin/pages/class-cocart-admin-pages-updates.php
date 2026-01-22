<?php
/**
 * Admin Page: Updates/License Manager for CoCart.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Pages
 * @since   5.0.0 Introduced.
 * @license GPL-3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Submenu_Page' ) ) {
	return;
}

class CoCart_Admin_Updates_Page extends CoCart_Submenu_Page {

	/**
	 * Stores the campaign arguments.
	 *
	 * @access public
	 *
	 * @var array
	 */
	public $campaign_args = array();

	/**
	 * Stores the update settings.
	 *
	 * @var array
	 */
	private $cocart_update_settings;

	/**
	 * Helper init method that runs on parent __construct
	 *
	 * @access protected
	 */
	protected function init() {
		$this->campaign_args['utm_medium']  = 'plugin-admin';
		$this->campaign_args['utm_content'] = 'updates-page';

		$this->cocart_update_settings = get_option( 'cocart_update_settings' );

		// Registers the updates page.
		add_filter( 'cocart_register_submenu_page', array( $this, 'register_submenu_page' ), 20 );

		// Enqueue CoCart scripts and styles.
		add_filter(
			'cocart_admin_screens',
			function ( $screens ) {
				$screens[] = 'cocart_page_cocart-updates';

				return $screens;
			}
		);

		add_action( 'admin_init', array( $this, 'cocart_update_settings_page_init' ) );
		add_action( 'update_option_cocart_update_settings', array( __CLASS__, 'handle_license_activation' ), 10, 3 );
	} // END init()

	/**
	 * Callback for the HTML output for the updates/license manager page.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 */
	public function output() {
		$store_url          = CoCart_Helpers::build_shortlink( add_query_arg( CoCart_Helpers::cocart_campaign( $this->campaign_args ), esc_url( COCART_STORE_URL . 'pricing/#paid-plans' ) ) );
		$manage_license_url = esc_url( 'https://cocartapi.com/billing' );

		$license_error = get_option( 'cocart_license_error' );
		?>
		<div class="wrap cocart-wrapped" role="main">
			<?php settings_errors(); ?>

			<div class="cocart-content inner" id="cocart-license-information">
				<form method="post" action="options.php">
					<?php
						settings_fields( 'cocart_update_settings_group' );
						do_settings_sections( 'cocart-plugin-update-settings' );

						$license_status = $this->cocart_license_status();

						$submit_text = __( 'Save changes', 'cocart-core' );

					if ( empty( $this->cocart_update_settings['cocart_license_key'] ) || 'deactivated' === $license_status || 'expired' === $license_status || 'disabled' === $license_status ) {
						$submit_text = __( 'Activate License', 'cocart-core' );
					}

						submit_button( $submit_text );
					?>
					<!--div class="cocart-activation">
						<a href="<?php echo esc_url( $manage_license_url ); ?>" target="_blank" class="button cocart-manage-license-btn"><?php esc_html_e( 'Manage License', 'cocart-core' ); ?><i class="cocart-icon cocart-icon-arrow-up-right"></i></a>
					</div-->
				</form>
				<div class="cocart-license-status-wrap">
					<table class="cocart-license-status-table">
						<tbody><tr>
							<th><?php esc_html_e( 'License Status', 'cocart-core' ); ?></th>
							<td><span class="cocart-license-status <?php echo esc_attr( $this->cocart_license_status() ); ?>"><?php echo esc_attr( $this->cocart_license_status( false ) ); ?></span></td>
						</tr>
						</tbody>
					</table>
					<?php
					if ( empty( $this->cocart_update_settings['cocart_license_key'] ) || 'expired' === $license_status || 'disabled' === $license_status ) {
						?>
					<div class="cocart-no-license-view-pricing">
						<span><?php esc_html_e( 'Don\'t have an license?', 'cocart-core' ); ?> <a href="<?php echo esc_url( $store_url ); ?>" target="_blank"><?php esc_html_e( 'View pricing & purchase', 'cocart-core' ); ?></a></span>
					</div>
					<?php } ?>
				</div>
			</div>
			<div class="cocart-content">
				<h2><?php esc_html_e( 'Plugin Information', 'cocart-core' ); ?></h2>
				<span><?php esc_html_e( 'Last checked', 'cocart-core' ); ?>: <?php echo date_i18n( 'l, j F Y', get_option( 'cocart_updates_last_checked', time() ) ); ?> <a href="#" class="button button-secondary"><?php esc_html_e( 'Pause Updates', 'cocart-core' ); ?></a></span>

				<p><?php esc_html_e( 'Only installed CoCart plugins listed below will display information on a possible update available.', 'cocart-core' ); ?></p>

				<table class="wp-list-table widefat plugins cocart-license-status-table">
					<thead>
						<tr>
							<td><?php esc_html_e( 'Plugin name', 'cocart-core' ); ?></td>
							<th><?php esc_html_e( 'Installed Version', 'cocart-core' ); ?></th>
							<th><?php esc_html_e( 'Latest Version', 'cocart-core' ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php
					$updates = new CoCart_Admin_Updates();
					foreach ( $updates->get_installed_plugins() as $plugin_file => $installed_plugin ) {
						echo '<tr><th>' . esc_html( $installed_plugin['Name'] ) . '</th>';
						echo '<td><span class="cocart-version" style="color:blue;">' . esc_html( $installed_plugin['Version'] ) . '</td>';
						echo '<td><span class="latest-version" style="color:red;">' . esc_html( $installed_plugin['Version'] ) . '</td></tr>';
					}
					?>
					</tbody>
				</table>
			</div>
			<div class="cocart-content">
				<h2><?php esc_html_e( 'Recent Updates', 'cocart-core' ); ?></h2>
				<?php
				$this->cocart_release_feed( 'https://cocart.dev/category/changelog/feed/', 3 );
				?>
			</div>
		</div>
		<div class="clear"></div>
		<?php
	} // END output()

	/**
	 * Register the admin submenu page.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param array $submenu_pages Currently registered submenu pages.
	 *
	 * @return array $submenu_pages All registered submenu pages.
	 */
	public function register_submenu_page( $submenu_pages ) {
		if ( ! is_array( $submenu_pages ) ) {
			return $submenu_pages;
		}

		$count_html = CoCart_Admin_Updates::get_updates_count_html();

		/* translators: %s: updates counter */
		$menu_title = sprintf( __( 'Updates %s', 'cocart-core' ), $count_html );

		$submenu_pages['updates'] = array(
			'class_name' => 'CoCart_Admin_Updates_Page',
			'data'       => array(
				'page_title' => __( 'Updates', 'cocart-core' ),
				'menu_title' => $menu_title,
				'capability' => apply_filters( 'cocart_updates_screen_capability', 'manage_options' ),
				'menu_slug'  => 'cocart-updates',
			),
		);

		return $submenu_pages;
	} // END register_submenu_page()

	/**
	 * CoCart Update Settings.
	 *
	 * @access public
	 */
	public function cocart_update_settings_page_init() {
		register_setting(
			'cocart_update_settings_group',
			'cocart_update_settings',
			array( $this, 'cocart_update_settings_sanitize' )
		);

		add_settings_section(
			'cocart_update_settings_section',
			__( 'License Information', 'cocart-core' ),
			function () {
				return ''; },
			'cocart-plugin-update-settings'
		);

		add_settings_field(
			'cocart_license_key',
			__( 'License Key', 'cocart-core' ),
			array( $this, 'cocart_license_key_callback' ),
			'cocart-plugin-update-settings',
			'cocart_update_settings_section'
		);
	} // END cocart_update_settings_page_init()

	/**
	 * Sanitize license key.
	 *
	 * @access public
	 *
	 * @param string $input License Key.
	 */
	public function cocart_update_settings_sanitize( $input ) {
		$sanitary_values = array();
		if ( isset( $input['cocart_license_key'] ) ) {
			$sanitary_values['cocart_license_key'] = sanitize_text_field( $input['cocart_license_key'] );
		}

		return $sanitary_values;
	} // END cocart_update_settings_sanitize()

	/**
	 * License Key Callback.
	 *
	 * @access public
	 */
	public function cocart_license_key_callback() {
		$license_key   = $this->cocart_update_settings['cocart_license_key'];
		$license_error = json_decode( get_option( 'cocart_license_error' ) );

		if ( CoCart_Status::is_offline_mode() || CoCart_Status::is_staging_site() ) {
			$license_details = json_decode( get_option( 'cocart_license_verified' ) );
		} else {
			$license_details = json_decode( get_option( 'cocart_license_details' ) );
		}

		if ( ! empty( $license_error ) ) {
			echo '<div class="notice notice-warning notice-alt cocart-notice"><p>' . esc_html__( 'There was a problem activating/deactivating your license. Please seek assistance.', 'cocart-core' ) . '</p></div>';
		} elseif ( empty( $license_details ) || false === $license_details->license_key->status || isset( $license_details->deactivated ) || empty( $license_key ) ) {
			echo '<div class="notice cocart-notice"><p>' . esc_html__( 'Activate your license to enable access to updates.', 'cocart-core' ) . '</p></div>';
		} elseif ( CoCart_Status::is_offline_mode() || CoCart_Status::is_staging_site() ) {
			echo '<div class="notice cocart-notice"><p>' . esc_html__( 'Activations are not counted while on a local or staging environments, but you will still receive updates.', 'cocart-core' ) . '</p></div>';
		}

		if ( ! empty( CoCart_Status::get_live_site_url() ) && CoCart_Status::strip_protocol( CoCart_Status::get_live_site_url() ) !== CoCart_Status::strip_protocol( CoCart_Status::get_site_url() ) ) {
			echo '<div class="notice notice-warning notice-alt cocart-notice"><p>' . esc_html__( 'It looks like this site has moved. Updates are disabled until you have reactivated your license.', 'cocart-core' ) . '</p></div>';
		}

		printf(
			'<input class="regular-text" type="text" name="cocart_update_settings[cocart_license_key]" id="cocart_license_key" value="%s" placeholder="%s">',
			isset( $license_key ) && empty( $license_error ) ? esc_attr( $license_key ) : '',
			esc_html__( 'Copy and paste your license key here...', 'cocart-core' )
		);

		$message            = false;
		$license_expires_at = '';
		$meta               = array();

		if ( ! empty( $license_details ) ) {
			$license_status     = $license_details->license_key->status;
			$license_expires_at = ! empty( $license_details->license_key->expires_at ) ? $license_details->license_key->expires_at : 0;
			$meta               = ! empty( $license_details->meta ) ? $license_details->meta : array();
			$limit              = ! empty( $license_details->license_key->activation_limit ) ? $license_details->license_key->activation_limit : '∞';

			if ( 'disabled' === $license_status || 'expired' === $license_status ) {
				$message = '<strong><span class="warning dashicons dashicons-warning"></span></strong> ' . sprintf(
					/* translators: %1$s and %2$s are a link. */
					esc_html__( 'License is expired. %1$sRenew your license%2$s to get updates again.', 'cocart-core' ),
					'<a href="' . esc_url( 'https://cocartapi.com/billing' ) . '">',
					'</a>'
				);
			} elseif ( isset( $license_details->activated ) || isset( $license_details->deactivated ) ) {
				$message = sprintf(
					/* translators: %s Activation limit usage. */
					__( 'Activation Limit Usage: %s', 'cocart-core' ),
					"{$license_details->license_key->activation_usage}/{$limit}"
				);
			} elseif ( isset( $license_details->error_code ) && 'invalid_license_key' === $license_details->error_code ) {
				$message = sprintf(
					/* translators: %1$s and %2$s are a link. */
					__( 'License key is invalid. Please copy and paste %1$syour license key from your account%2$s.', 'cocart-core' ),
					'<a href="' . esc_url( 'https://cocartapi.com/billing' ) . '">',
					'</a>'
				);
			} else {
				$message = sprintf(
					/* translators: %s Activation limit available. */
					__( 'Activation Limit Available: %s', 'cocart-core' ),
					"{$limit}"
				);
			}
		}

		if ( ! empty( $message ) ) {
			echo "<p class='description'>{$message}</p>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		if ( isset( $license_key ) && ! empty( $license_key ) && empty( $license_error ) ) {
			if ( ! empty( $meta ) ) {
				echo '<p>' . sprintf(
					/* translators: Product name associated with license. */
					esc_attr__( 'License for: %s', 'cocart-core' ),
					'<strong>' . esc_attr( $meta->product_name ) . '</strong>'
				) . '</p>';
			}

			$license_expires_at = ! empty( $license_expires_at ) ? strtotime( $license_expires_at ) : 0;
			$current_timestamp  = time();
			$highlight_color    = ( $license_expires_at - $current_timestamp <= 32 * 24 * 60 * 60 ) ? 'red' : 'green';

			if ( $license_expires_at > 0 ) {
				$expiration = '<strong style="color:' . $highlight_color . '">' . gmdate( 'l d F Y', $license_expires_at ) . '</strong>';
			} else {
				$expiration = '<strong style="color:blue">' . __( 'Never', 'cocart-core' ) . '</strong>';
			}
			echo '<p>' . sprintf(
				/* translators: Expiration value */
				esc_html__( 'Expiration Date: %s', 'cocart-core' ),
				$expiration //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			) . '</p>';
		}
	} // END cocart_license_key_callback()

	/**
	 * Handle license activation.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param string $old_value Old license key.
	 * @param string $new_value New license key.
	 * @param string $option    Option name.
	 */
	public static function handle_license_activation( $old_value, $new_value, $option ) {
		$license_details = json_decode( get_option( 'cocart_license_details' ) );

		if ( isset( $new_value['cocart_license_key'] ) && ! empty( $new_value['cocart_license_key'] ) && $old_value['cocart_license_key'] !== $new_value['cocart_license_key'] ) {
			// Deactivate previous license key if any.
			if ( isset( $license_details->instance->id ) && ! empty( $old_value['cocart_license_key'] ) ) {
				CoCart_Admin_Updates::deactivate_license( $old_value['cocart_license_key'], $license_details->instance->id );
			}

			// Verify license key.
			CoCart_Admin_Updates::verify_license( $new_value['cocart_license_key'] );

			// Check we are not handling activation offline or on a staging site.
			if ( ! CoCart_Status::is_offline_mode() && ! CoCart_Status::is_staging_site() ) {
				// Activate new license key.
				CoCart_Admin_Updates::activate_license( $new_value['cocart_license_key'] );
			}

			// Set site url lock key.
			CoCart_Status::set_site_url_lock();
		}

		if ( isset( $new_value['cocart_license_key'] ) && empty( $new_value['cocart_license_key'] ) && ! empty( $old_value['cocart_license_key'] ) ) {
			// Only deactivate license if we are on a production site.
			if ( ! CoCart_Status::is_offline_mode() && ! CoCart_Status::is_staging_site() ) {
				// Deactivate previous license key if any.
				if ( isset( $license_details->instance->id ) ) {
					CoCart_Admin_Updates::deactivate_license( $old_value['cocart_license_key'], $license_details->instance->id );
				}
			}
		}

		// Refresh plugins transient to fetch new update data.
		CoCart_Admin_Updates::refresh_plugins_transient();
	} // END handle_license_activation()

	/**
	 * Returns the license status.
	 *
	 * @access protected
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param boolean $raw True returns raw status, false returns translated.
	 *
	 * @return string License status.
	 */
	protected function cocart_license_status( $raw = true ) {
		$license_verified = json_decode( get_option( 'cocart_license_verified' ) );
		$license_details  = json_decode( get_option( 'cocart_license_details' ) );

		if ( empty( $this->cocart_update_settings['cocart_license_key'] ) ) {
			$license_status = ( ! $raw ) ? __( 'Inactive', 'cocart-core' ) : 'inactive';
		} elseif ( ! empty( $license_details ) ) {
			$license_status = $license_details->license_key->status;

			$license_status = $this->get_license_status( $license_status, $raw );
		} elseif ( ! empty( $license_verified ) ) {
			$license_status = $license_verified->license_key->status;

			$license_status = $this->get_license_status( $license_status, $raw );
		}

		return $license_status;
	} // END cocart_license_status()

	/**
	 * Returns the license status.
	 *
	 * @access protected
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param string  $license_status The status of the license returned from Lemon Squeezy.
	 * @param boolean $raw            True returns raw status, false returns translated.
	 *
	 * @return string License status.
	 */
	protected function get_license_status( $license_status, $raw = true ) {
		$is_offline = ( CoCart_Status::is_offline_mode() || CoCart_Status::is_staging_site() );

		if ( 'disabled' === $license_status ) {
			return ( ! $raw ) ? __( 'Disabled', 'cocart-core' ) : 'disabled';
		} elseif ( 'expired' === $license_status ) {
			return ( ! $raw ) ? __( 'Expired', 'cocart-core' ) : 'expired';
		} elseif ( isset( $license_details->activated ) || 'active' === $license_status ) {
			return ( ! $raw ) ? __( 'Active', 'cocart-core' ) : 'active';
		} elseif ( isset( $license_details->deactivated ) || 'deactivated' === $license_status ) {
			return ( ! $raw ) ? __( 'Deactivated', 'cocart-core' ) : 'deactivated';
		} elseif ( 'inactive' === $license_status ) {
			return ( ! $raw ) ? $is_offline ? __( 'Inactive, but still receives updates.', 'cocart-core' ) : __( 'Inactive', 'cocart-core' ) : 'inactive';
		} else {
			return ( ! $raw ) ? __( 'Invalid', 'cocart-core' ) : 'invalid';
		}
	} // END get_license_status()

	/**
	 * Returns a single release update via RSS feed.
	 *
	 * @access protected
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param string $feed_url  Feed URI.
	 * @param int    $max_items Maximum releases to return. 1 is default.
	 */
	protected function cocart_release_feed( $feed_url, $max_items = 1 ) {
		$feed = fetch_feed( esc_url_raw( $feed_url ) );

		if ( ! is_wp_error( $feed ) ) { // Checks that the object is created correctly.

			// Figure out how many total items there are, but limit it.
			$max_items = $feed->get_item_quantity( $max_items );

			// Build an array of all the items, starting with element 0 (first element).
			$rss_items = $feed->get_items( 0, $max_items );

			echo '<ul>';

			foreach ( $rss_items as $item ) :
				?>
				<li>
					<a href="<?php echo esc_url( $item->get_permalink() ); ?>"
						title="
						<?php
						printf(
							/* translators: %s Date and time */
							esc_html__( 'Posted %s', 'cocart-core' ),
							esc_html( $item->get_date( 'j F Y | g:i a' ) )
						);
						?>
						">
						<?php echo esc_html( $item->get_title() ); ?>
					</a>
					<?php
					// $desc = html_entity_decode( $item->get_description(), ENT_QUOTES, get_option( 'blog_charset' ) );
					// echo esc_attr( wp_trim_words( $desc, 60, ' ...' ) );
					?>
				</li>
				<?php
			endforeach;

			echo '</ul>';

			$feed->__destruct();
			unset( $feed );
		}
	} // END cocart_release_feed()
} // END class

return new CoCart_Admin_Updates_Page();
