<?php
/**
 * CoCart - Setup Wizard.
 *
 * Takes users through some basic steps to setup their headless store.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin
 * @since   3.1.0 Introduced.
 * @version 4.0.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart_Admin_Setup_Wizard class.
 */
class CoCart_Admin_Setup_Wizard extends CoCart_Submenu_Page {

	/**
	 * Current step
	 *
	 * @access private
	 *
	 * @var string
	 */
	private $step = '';

	/**
	 * Steps for the setup wizard
	 *
	 * @access private
	 *
	 * @var array
	 */
	private $steps = array();

	/**
	 * Setup Wizard.
	 *
	 * @access public
	 */
	protected function init() {
		add_filter( 'cocart_register_submenu_page', array( $this, 'register_submenu_page' ), 15 );

		add_filter( 'admin_body_class', array( $this, 'cocart_admin_body_class_setup_wizard' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Run transfer sessions in the background when called.
		add_action( 'cocart_run_transfer_sessions', 'cocart_transfer_sessions' );
	}

	/**
	 * Register the admin submenu page.
	 *
	 * @access public
	 *
	 * @since 3.10.0 Introduced.
	 *
	 * @param array $submenu_pages Currently registered submenu pages.
	 *
	 * @return array $submenu_pages All registered submenu pages.
	 */
	public function register_submenu_page( $submenu_pages ) {
		if ( ! is_array( $submenu_pages ) ) {
			return $submenu_pages;
		}

		if ( apply_filters( 'cocart_enable_setup_wizard', true ) ) {
			$submenu_pages['setup-wizard'] = array(
				'class_name' => 'CoCart_Admin_Setup_Wizard',
				'data'       => array(
					'page_title' => __( 'Setup Wizard', 'cart-rest-api-for-woocommerce' ),
					'menu_title' => __( 'Setup Wizard', 'cart-rest-api-for-woocommerce' ),
					'capability' => apply_filters( 'cocart_screen_capability', 'manage_options' ),
					'menu_slug'  => 'cocart-setup',
				),
			);
		}

		return $submenu_pages;
	} // END register_submenu_page()

	/**
	 * Register/enqueue scripts and styles for the Setup Wizard.
	 *
	 * Hooked onto 'admin_enqueue_scripts'.
	 *
	 * @access public
	 *
	 * @param string $hook The current admin page.
	 */
	public function enqueue_scripts( $hook ) {
		if ( strpos( $hook, 'cocart-setup' ) !== false || ( isset( $_GET['page'] ) && strpos( sanitize_text_field( wp_unslash( $_GET['page'] ) ), 'cocart-setup' ) === 0 ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$suffix     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			$style_path = 'assets/css/admin/cocart-setup.css';

			wp_enqueue_style( 'cocart-setup', COCART_URL_PATH . '/' . $style_path, array( 'dashicons' ), CoCart::get_file_version( COCART_ABSPATH . $style_path ) );
			wp_style_add_data( 'cocart-setup', 'rtl', 'replace' );
			if ( $suffix ) {
				wp_style_add_data( 'cocart-setup', 'suffix', '.min' );
			}
		}
	} // END enqueue_scripts()

	/**
	 * Show the setup wizard.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 */
	public function output() {
		$default_steps = array(
			'store_setup' => array(
				'name'    => __( 'Store setup', 'cart-rest-api-for-woocommerce' ),
				'view'    => array( $this, 'cocart_setup_wizard_store_setup' ),
				'handler' => array( $this, 'cocart_setup_wizard_store_setup_save' ),
			),
			'sessions'    => array(
				'name'    => __( 'Sessions', 'cart-rest-api-for-woocommerce' ),
				'view'    => array( $this, 'cocart_setup_wizard_sessions' ),
				'handler' => array( $this, 'cocart_setup_wizard_sessions_save' ),
			),
			'ready'       => array(
				'name'    => __( 'Ready!', 'cart-rest-api-for-woocommerce' ),
				'view'    => array( $this, 'cocart_setup_wizard_ready' ),
				'handler' => '',
			),
		);

		$this->steps = apply_filters( 'cocart_setup_wizard_steps', $default_steps );
		$this->step  = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			call_user_func( $this->steps[ $this->step ]['handler'], $this );
		}

		$this->setup_wizard_header();
		$this->setup_wizard_steps();
		$this->setup_wizard_content();
		$this->setup_wizard_footer();
	} // END setup_wizard()

	/**
	 * Get the URL for the next step's screen.
	 *
	 * @access public
	 *
	 * @param string $step slug (default: current step).
	 *
	 * @return string URL for next step if a next step exists.
	 *                Admin URL if it's the last step.
	 *                Empty string on failure.
	 */
	public function get_next_step_link( $step = '' ) {
		if ( ! $step ) {
			$step = $this->step;
		}

		$keys = array_keys( $this->steps );

		if ( end( $keys ) === $step ) {
			return add_query_arg( 'step', end( $keys ) );
		}

		$step_index = array_search( $step, $keys, true );
		if ( false === $step_index ) {
			return '';
		}

		return add_query_arg( 'step', $keys[ $step_index + 1 ] );
	} // END get_next_step_link()

	/**
	 * Setup Wizard Header.
	 *
	 * @access public
	 */
	public function setup_wizard_header() {
		// Same as default WP from wp-admin/admin-header.php.
		$wp_version_class = 'branch-' . str_replace( array( '.', ',' ), '-', floatval( get_bloginfo( 'version' ) ) );

		$campaign_args = CoCart_Helpers::cocart_campaign(
			array(
				'utm_content' => 'setup-wizard',
			)
		);
		$store_url     = CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, COCART_STORE_URL ) );

		set_current_screen( 'cocart-setup-wizard' );
		?>
		<div class="wrap cocart-wrapped cocart-setup-wizard <?php echo esc_attr( 'cocart-setup-step__' . $this->step ); ?>">
			<h1 class="cocart-logo">
				<a href="<?php echo esc_url( $store_url ); ?>" target="_blank" rel="noopener noreferrer">
					<img src="<?php echo esc_url( COCART_URL_PATH . '/assets/images/brand/header-logo.png' ); ?>" alt="CoCart Logo" />
				</a>
			</h1>
		<?php
	} // END setup_wizard_header

	/**
	 * Setup Wizard Footer.
	 *
	 * @access public
	 */
	public function setup_wizard_footer() {
		$current_step = $this->step;

		if ( 'sessions' === $current_step ) :
			?>
			<a class="cocart-setup-wizard-footer-links" href="<?php echo esc_url( $this->get_next_step_link() ); ?>"><?php esc_html_e( 'Skip this step', 'cart-rest-api-for-woocommerce' ); ?></a>
		<?php endif; ?>

		<?php do_action( 'cocart_setup_wizard_footer' ); ?>

		</div>
		<?php
	} // END setup_wizard_footer()

	/**
	 * Output the steps.
	 *
	 * @access public
	 */
	public function setup_wizard_steps() {
		$output_steps = $this->steps;
		?>
		<ol class="cocart-setup-wizard-steps">
			<?php
			foreach ( $output_steps as $step_key => $step ) {
				$is_completed = array_search( $this->step, array_keys( $this->steps ), true ) > array_search( $step_key, array_keys( $this->steps ), true );

				if ( $step_key === $this->step ) {
					?>
					<li class="active"><?php echo esc_html( $step['name'] ); ?></li>
					<?php
				} elseif ( $is_completed ) {
					?>
					<li class="done"><?php echo esc_html( $step['name'] ); ?></li>
					<?php
				} else {
					?>
					<li><?php echo esc_html( $step['name'] ); ?></li>
					<?php
				}
			}
			?>
		</ol>
		<?php
	} // END setup_wizard_steps()

	/**
	 * Output the content for the current step.
	 *
	 * @access public
	 */
	public function setup_wizard_content() {
		echo '<div class="cocart-content">';
		if ( ! empty( $this->steps[ $this->step ]['view'] ) ) {
			call_user_func( $this->steps[ $this->step ]['view'], $this );
		}
		echo '</div>';
	} // END setup_wizard_content()

	/**
	 * Initial "store setup" step.
	 *
	 * New Store, Multiple Domains, JWT Authentication.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 * @since 4.3.0 Added option to install JWT Authentication.
	 */
	public function cocart_setup_wizard_store_setup() {
		$sessions_transferred = get_transient( 'cocart_setup_wizard_sessions_transferred' );

		$product_count = array_sum( (array) wp_count_posts( 'product' ) );

		$new_store = ( 0 === $product_count ) ? 'yes' : 'no';

		// If setup wizard has nothing left to setup, redirect to ready step.
		if ( $sessions_transferred && class_exists( 'CoCart_CORS' ) ) {
			wp_safe_redirect( esc_url_raw( $this->get_next_step_link( 'ready' ) ) );
			exit;
		}
		?>
		<form method="post" class="store-step">
			<input type="hidden" name="save_step" value="store_setup" />
			<?php wp_nonce_field( 'cocart-setup' ); ?>

			<p>
			<?php
			printf(
				/* translators: 1: CoCart, 2: WooCommerce */
				esc_html__( 'Thank you for choosing %1$s - the #1 REST API that makes it easy to decouple %2$s.', 'cart-rest-api-for-woocommerce' ),
				'CoCart',
				'WooCommerce'
			);
			?>
			</p>

			<p>
			<?php
			printf(
				/* translators: 1: CoCart */
				esc_html__( 'The setup wizard is completely optional as %1$s is already ready to start using. The wizard is here to help you configure %1$s to your needs.', 'cart-rest-api-for-woocommerce' ),
				'CoCart'
			);
			?>
			</p>

			<p><?php esc_html_e( 'If you don’t want to go through the wizard right now, you can skip it and come back anytime if you change your mind!', 'cart-rest-api-for-woocommerce' ); ?></p>

			<?php if ( ! $sessions_transferred ) { ?>
			<label for="store_new">
				<?php
				printf(
					/* translators: %s WooCommerce */
					esc_html__( 'Is this a new %s store?', 'cart-rest-api-for-woocommerce' ),
					'WooCommerce'
				);
				?>
			</label>
			<select id="store_new" name="store_new" aria-label="<?php esc_attr_e( 'New Store', 'cart-rest-api-for-woocommerce' ); ?>" class="select-input dropdown">
				<option value="no"<?php selected( $new_store, 'no' ); ?>><?php echo esc_html__( 'No', 'cart-rest-api-for-woocommerce' ); ?></option>
				<option value="yes"<?php selected( $new_store, 'yes' ); ?>><?php echo esc_html__( 'Yes', 'cart-rest-api-for-woocommerce' ); ?></option>
			</select>
			<span>
				<?php
				printf(
				/* translators: %s: CoCart */
					esc_html__( 'If no, %s will transfer all cart sessions to our database table to prevent duplicate cart session data.', 'cart-rest-api-for-woocommerce' ),
					'CoCart'
				);
				?>
			</span>
			<?php } ?>

			<label for="multiple_domains"><?php esc_html_e( 'Will your headless setup use multiple domains?', 'cart-rest-api-for-woocommerce' ); ?></label>
			<select id="multiple_domains" name="multiple_domains" aria-label="<?php esc_attr_e( 'Multiple Domains', 'cart-rest-api-for-woocommerce' ); ?>" class="select-input dropdown">
				<option value="no"><?php echo esc_html__( 'No', 'cart-rest-api-for-woocommerce' ); ?></option>
				<option value="yes"><?php echo esc_html__( 'Yes', 'cart-rest-api-for-woocommerce' ); ?></option>
			</select>

			<span><?php esc_html_e( 'If you are using multiple domains for your headless setup, installing support for CORS is recommended.', 'cart-rest-api-for-woocommerce' ); ?> <a href="<?php echo esc_url( 'https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'What is CORS?', 'cart-rest-api-for-woocommerce' ); ?></a></span>

			<label for="jwt_authentication"><?php esc_html_e( 'Do you require support for JWT Authentication?', 'cart-rest-api-for-woocommerce' ); ?></label>
			<select id="jwt_authentication" name="jwt_authentication" aria-label="<?php esc_attr_e( 'JWT Authentication', 'cart-rest-api-for-woocommerce' ); ?>" class="select-input dropdown">
				<option value="no"><?php echo esc_html__( 'No', 'cart-rest-api-for-woocommerce' ); ?></option>
				<option value="yes"><?php echo esc_html__( 'Yes', 'cart-rest-api-for-woocommerce' ); ?></option>
			</select>

			<p class="cocart-actions step">
				<button class="button button-primary button-large" value="<?php esc_attr_e( "Let's go!", 'cart-rest-api-for-woocommerce' ); ?>" name="save_step"><?php esc_html_e( "Let's go!", 'cart-rest-api-for-woocommerce' ); ?></button>
			</p>
		</form>
		<?php
	} // END cocart_setup_wizard_store_setup()

	/**
	 * Determine the next step to take based on the choices made.
	 *
	 * @access public
	 */
	public function cocart_setup_wizard_store_setup_save() {
		check_admin_referer( 'cocart-setup' );

		$is_store_new       = get_transient( 'cocart_setup_wizard_store_new' );
		$store_new          = isset( $_POST['store_new'] ) ? ( 'yes' === wc_clean( sanitize_text_field( wp_unslash( $_POST['store_new'] ) ) ) ) : $is_store_new;
		$multiple_domains   = isset( $_POST['multiple_domains'] ) && ( 'yes' === wc_clean( sanitize_text_field( wp_unslash( $_POST['multiple_domains'] ) ) ) );
		$jwt_authentication = isset( $_POST['jwt_authentication'] ) && ( 'yes' === wc_clean( sanitize_text_field( wp_unslash( $_POST['jwt_authentication'] ) ) ) );

		$next_step = ''; // Next step.

		if ( $store_new ) {
			set_transient( 'cocart_setup_wizard_store_new', 'yes', MINUTE_IN_SECONDS * 10 );
			$next_step = apply_filters( 'cocart_setup_wizard_store_save_next_step_override', 'ready' );
		}

		// If true and CoCart Cors is not already installed then it will be installed in the background.
		if ( $multiple_domains ) {
			$this->install_cocart_cors();
		}

		// If true and CoCart JWT Authentication is not already installed then it will be installed in the background.
		if ( $jwt_authentication ) {
			$this->install_cocart_jwt();
		}

		// Redirect to next step.
		wp_safe_redirect( esc_url_raw( $this->get_next_step_link( $next_step ) ) );
		exit;
	} // END cocart_setup_wizard_store_setup_save()

	/**
	 * Sessions step.
	 *
	 * For those who are not installing a fresh WooCommerce store,
	 * this step will transfer any current sessions.
	 *
	 * @access public
	 */
	public function cocart_setup_wizard_sessions() {
		?>
		<form method="post" class="session-step">
			<input type="hidden" name="save_step" value="session_setup" />
			<?php wp_nonce_field( 'cocart-setup' ); ?>

			<p>
			<?php
			printf(
				/* translators: 1: WooCommerce, 2: CoCart */
				esc_html__( 'Your current %1$s sessions will be transferred over to %2$s session table. This will run in the background until completed. Once transferred, all customers carts will be accessible again.', 'cart-rest-api-for-woocommerce' ),
				'WooCommerce',
				'CoCart'
			);
			?>
			</p>

			<p class="cocart-actions step">
				<button class="button button-primary button-large" value="<?php esc_attr_e( 'Transfer Sessions', 'cart-rest-api-for-woocommerce' ); ?>" name="save_step"><?php esc_html_e( 'Transfer Sessions', 'cart-rest-api-for-woocommerce' ); ?></button>
			</p>
		</form>
		<?php
	} // END cocart_setup_wizard_sessions()

	/**
	 * Triggers in the background transferring of sessions and redirects to the next step.
	 *
	 * @access public
	 */
	public function cocart_setup_wizard_sessions_save() {
		check_admin_referer( 'cocart-setup' );

		// Add transfer sessions to queue.
		WC()->queue()->schedule_single( time(), 'cocart_run_transfer_sessions', array(), 'cocart-transfer-sessions' );

		// Redirect to next step.
		wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	} // END cocart_setup_wizard_sessions_save()

	/**
	 * Helper method to queue the background install of a plugin.
	 *
	 * @access protected
	 *
	 * @param string $plugin_id   Plugin id used for background install.
	 * @param array  $plugin_info Plugin info array containing name and repo-slug,
	 *                            and optionally file if different from [repo-slug].php.
	 */
	protected function install_plugin( $plugin_id, $plugin_info ) {
		$plugin_file = isset( $plugin_info['file'] ) ? $plugin_info['file'] : $plugin_info['repo-slug'] . '.php';
		if ( is_plugin_active( $plugin_info['repo-slug'] . '/' . $plugin_file ) ) {
			return;
		}

		WC_Install::background_installer( $plugin_id, $plugin_info );
	} // END install_plugin()

	/**
	 * Helper method to install CoCart CORS.
	 *
	 * @access protected
	 */
	protected function install_cocart_cors() {
		// Only those who can install plugins will be able to install CoCart Cors.
		if ( current_user_can( 'install_plugins' ) ) {
			$this->install_plugin(
				'cocart-cors',
				array(
					'name'      => 'CoCart - CORS Support',
					'repo-slug' => 'cocart-cors',
				)
			);
		}
	} // END install_cocart_cors()

	/**
	 * Helper method to install CoCart JWT Authentication.
	 *
	 * @access protected
	 *
	 * @since 4.3.0 Introduced.
	 */
	protected function install_cocart_jwt() {
		// Only those who can install plugins will be able to install CoCart JWT Authentication.
		if ( current_user_can( 'install_plugins' ) ) {
			$this->install_plugin(
				'cocart-jwt-authentication',
				array(
					'name'      => 'CoCart - JWT Authentication',
					'repo-slug' => 'cocart-jwt-authentication',
				)
			);
		}
	} // END install_cocart_jwt()

	/**
	 * Helper method to retrieve the current user's email address.
	 *
	 * @access protected
	 *
	 * @return string Email address
	 */
	protected function get_current_user_email() {
		$current_user = wp_get_current_user();
		$user_email   = $current_user->user_email;

		return $user_email;
	} // END get_current_user_email()

	/**
	 * Final step.
	 *
	 * @access public
	 */
	public function cocart_setup_wizard_ready() {
		// We've made it! Don't prompt the user to run the wizard again.
		CoCart_Admin_Notices::remove_notice( 'setup_wizard', true );

		$campaign_args = CoCart_Helpers::cocart_campaign(
			array(
				'utm_content' => 'setup-wizard',
			)
		);

		include_once COCART_ABSPATH . 'includes/classes/admin/views/html-setup-ready.php';
		include_once COCART_ABSPATH . 'includes/classes/admin/views/html-next-steps.php';
	} // END cocart_setup_wizard_ready()

	/**
	 * Adds the "cocart-setup" class to the <body> when viewing the Setup Wizard.
	 *
	 * @access public
	 *
	 * @since 3.10.0 Introduced.
	 *
	 * @param string $classes Previous body classes.
	 *
	 * @return string
	 */
	public function cocart_admin_body_class_setup_wizard( $classes ) {
		if ( empty( $_GET['page'] ) || 'cocart-setup' !== sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $classes;
		}

		return $classes . ' cocart-setup ';
	} // END cocart_admin_body_class_setup_wizard()
} // END class

return new CoCart_Admin_Setup_Wizard();
