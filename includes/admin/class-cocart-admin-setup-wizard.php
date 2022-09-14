<?php
/**
 * CoCart - Setup Wizard.
 *
 * Takes users through some basic steps to setup their headless store.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin
 * @since   3.1.0
 * @version 3.7.5
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart_Admin_Setup_Wizard class.
 */
class CoCart_Admin_Setup_Wizard {

	/**
	 * Current step
	 *
	 * @access private
	 * @var    string
	 */
	private $step = '';

	/**
	 * Steps for the setup wizard
	 *
	 * @access private
	 * @var    array
	 */
	private $steps = array();

	/**
	 * Tweets user can optionally send after setup.
	 *
	 * @access private
	 * @var    array
	 */
	private $tweets = array(
		'Cha ching. I just set up a headless store with @WooCommerce and @cocartapi!',
		'Someone give me high five, I just set up a headless store with @WooCommerce and @cocartapi!',
		'Want to build a fast headless store like me? Checkout @cocartapi - Designed for @WooCommerce.',
		'Build headless stores, without building an API. Checkout @cocartapi - Designed for @WooCommerce.',
	);

	/**
	 * Setup Wizard.
	 *
	 * @access public
	 */
	public function __construct() {
		if ( apply_filters( 'cocart_enable_setup_wizard', true ) && current_user_can( 'manage_woocommerce' ) ) {
			add_action( 'admin_menu', array( $this, 'admin_menus' ) );
			add_action( 'admin_init', array( $this, 'setup_wizard' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		// Run transfer sessions in the background when called.
		add_action( 'cocart_run_transfer_sessions', 'cocart_transfer_sessions' );
	}

	/**
	 * Add admin menus/screens.
	 *
	 * @access public
	 */
	public function admin_menus() {
		add_dashboard_page( '', '', 'manage_options', 'cocart-setup', '' );
	}

	/**
	 * Register/enqueue scripts and styles for the Setup Wizard.
	 *
	 * Hooked onto 'admin_enqueue_scripts'.
	 *
	 * @access public
	 */
	public function enqueue_scripts() {
		$suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$version = COCART_VERSION;

		wp_enqueue_style( 'cocart-setup', COCART_URL_PATH . '/assets/css/admin/cocart-setup.css', array( 'dashicons', 'install' ), $version );
		wp_style_add_data( 'cocart-setup', 'rtl', 'replace' );
		if ( $suffix ) {
			wp_style_add_data( 'cocart-setup', 'suffix', '.min' );
		}
	}

	/**
	 * Show the setup wizard.
	 *
	 * @access public
	 */
	public function setup_wizard() {
		if ( empty( $_GET['page'] ) || 'cocart-setup' !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
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

		ob_start();
		$this->setup_wizard_header();
		$this->setup_wizard_steps();
		$this->setup_wizard_content();
		$this->setup_wizard_footer();
		exit;
	} // END setup_wizard()

	/**
	 * Get the URL for the next step's screen.
	 *
	 * @access public
	 * @param  string $step  slug (default: current step).
	 * @return string       URL for next step if a next step exists.
	 *                      Admin URL if it's the last step.
	 *                      Empty string on failure.
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
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title><?php
			printf(
				/* translators: %s: CoCart */
				esc_html__( '%s &rsaquo; Setup Wizard', 'cart-rest-api-for-woocommerce' ),
				'CoCart'
			);
			?></title>
			<?php do_action( 'admin_enqueue_scripts' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="cocart-setup-wizard wp-core-ui <?php echo esc_attr( 'cocart-setup-step__' . $this->step ); ?> <?php echo esc_attr( $wp_version_class ); ?>">
		<h1 class="cocart-logo">
			<a href="<?php echo esc_url( $store_url ); ?>" target="_blank">
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

		if ( 'store_setup' === $current_step ) :
			?>
				<a class="cocart-setup-wizard-footer-links" href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Not right now', 'cart-rest-api-for-woocommerce' ); ?></a>
			<?php elseif ( 'sessions' === $current_step ) : ?>
				<a class="cocart-setup-wizard-footer-links" href="<?php echo esc_url( $this->get_next_step_link() ); ?>"><?php esc_html_e( 'Skip this step', 'cart-rest-api-for-woocommerce' ); ?></a>
			<?php endif; ?>

			<?php do_action( 'cocart_setup_wizard_footer' ); ?>

			</body>
		</html>
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
		echo '<div class="cocart-setup-wizard-content">';
		if ( ! empty( $this->steps[ $this->step ]['view'] ) ) {
			call_user_func( $this->steps[ $this->step ]['view'], $this );
		}
		echo '</div>';
	} // END setup_wizard_content()

	/**
	 * Initial "store setup" step.
	 *
	 * New Store, Multiple Domains.
	 *
	 * @access public
	 */
	public function cocart_setup_wizard_store_setup() {
		$sessions_transferred = get_transient( 'cocart_setup_wizard_sessions_transferred' );

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
				esc_html__( 'Thank you for choosing %1$s - the #1 REST API that handles the frontend of %2$s.', 'cart-rest-api-for-woocommerce' ),
				'CoCart',
				'WooCommerce'
			);
			?>
			</p>

			<p>
			<?php
			printf(
				/* translators: %s: CoCart */
				esc_html__( '%s focuses on the front-end of the store helping you to manage shopping carts and allows developers to build a headless store in any framework of their choosing. No local storing required.', 'cart-rest-api-for-woocommerce' ),
				'CoCart'
			);
			?>
			</p>

			<p><?php esc_html_e( 'The following wizard will help you configure CoCart for your headless store.', 'cart-rest-api-for-woocommerce' ); ?></p>

			<?php if ( ! $sessions_transferred ) { ?>
			<label for="store_new"><?php esc_html_e( 'Is this a new store?', 'cart-rest-api-for-woocommerce' ); ?></label>
			<select id="store_new" name="store_new" aria-label="<?php esc_attr_e( 'New Store', 'cart-rest-api-for-woocommerce' ); ?>" class="select-input dropdown">
				<option value="no"><?php echo esc_html__( 'No', 'cart-rest-api-for-woocommerce' ); ?></option>
				<option value="yes"><?php echo esc_html__( 'Yes', 'cart-rest-api-for-woocommerce' ); ?></option>
			</select>
			<?php } ?>

			<label for="multiple_domains"><?php esc_html_e( 'Will your headless setup use multiple domains?', 'cart-rest-api-for-woocommerce' ); ?></label>
			<select id="multiple_domains" name="multiple_domains" aria-label="<?php esc_attr_e( 'Multiple Domains', 'cart-rest-api-for-woocommerce' ); ?>" class="select-input dropdown">
				<option value="no"><?php echo esc_html__( 'No', 'cart-rest-api-for-woocommerce' ); ?></option>
				<option value="yes"><?php echo esc_html__( 'Yes', 'cart-rest-api-for-woocommerce' ); ?></option>
			</select>

			<p class="cocart-setup-wizard-actions step">
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

		$is_store_new     = get_transient( 'cocart_setup_wizard_store_new' );
		$store_new        = isset( $_POST['store_new'] ) ? ( 'yes' === wc_clean( wp_unslash( $_POST['store_new'] ) ) ) : $is_store_new;
		$multiple_domains = isset( $_POST['multiple_domains'] ) && ( 'yes' === wc_clean( wp_unslash( $_POST['multiple_domains'] ) ) );

		$next_step = ''; // Next step.

		if ( $store_new ) {
			set_transient( 'cocart_setup_wizard_store_new', 'yes', MINUTE_IN_SECONDS * 10 );
			$next_step = apply_filters( 'cocart_setup_wizard_store_save_next_step_override', 'ready' );
		}

		// If true and CoCart Cors is not already installed then it will be installed in the background.
		if ( $multiple_domains ) {
			$this->install_cocart_cors();
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

			<p><?php esc_html_e( 'Your current WooCommerce sessions will be transferred over to CoCart session table. This will run in the background until completed. Once transferred, all customers carts will be accessible again.', 'cart-rest-api-for-woocommerce' ); ?></p>

			<p class="cocart-setup-wizard-actions step">
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
	 * @param  string $plugin_id  Plugin id used for background install.
	 * @param  array  $plugin_info Plugin info array containing name and repo-slug, and optionally file if different from [repo-slug].php.
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
					'name'      => 'CoCart CORS',
					'repo-slug' => 'cocart-cors',
				)
			);
		}
	} // END install_cocart_cors()

	/**
	 * Helper method to retrieve the current user's email address.
	 *
	 * @access protected
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

		$tweet = array_rand( $this->tweets );

		$user_email = $this->get_current_user_email();
		$docs_url   = 'https://cocart.dev/';
		$help_text  = sprintf(
			/* translators: %1$s: link to docs */
			__( 'Visit CoCart.dev to access <a href="%1$s" target="_blank">developer resources</a>.', 'cart-rest-api-for-woocommerce' ),
			$docs_url
		);
		?>
		<h1><?php esc_html_e( "You're ready!", 'cart-rest-api-for-woocommerce' ); ?></h1>

		<p>
		<?php
		printf(
			/* translators: %s: CoCart */
			esc_html__( 'Now that you have %1$s installed, your ready to start developing your headless store.', 'cart-rest-api-for-woocommerce' ),
			'CoCart'
		);
		?>
		</p>

		<p>
			<?php
			printf(
				/* translators: %s: CoCart */
				esc_html__( 'In the documentation you will find the API routes available along with over 100+ action hooks and filters for developers to customise API responses or change how %1$s operates.', 'cart-rest-api-for-woocommerce' ),
				'CoCart'
			);
			?>
		</p>

		<p>
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %1$s: Developers Hub link */
					__( 'There is also a <a href="%1$s" target="_blank">developers hub</a> where you can find all the resources you need to be productive with CoCart and keep track of everything that is happening with the plugin including development decisions and scoping of future versions.', 'cart-rest-api-for-woocommerce' ),
					$docs_url
				)
			);
			?>
		</p>

		<p>
			<?php
			esc_html_e( 'It also provides answers to most common questions should you find that you need help. This is best place to look at first before contacting for support.', 'cart-rest-api-for-woocommerce' );
			?>
		</p>

		<p>
			<?php
			printf(
				/* translators: %s: CoCart */
				esc_html__( 'If you do need support or simply want to talk to other developers about taking your WooCommerce store headless, come join the %s community.', 'cart-rest-api-for-woocommerce' ),
				'CoCart'
			);
			?>
		</p>

		<p><?php esc_html_e( 'Thank you and enjoy!', 'cart-rest-api-for-woocommerce' ); ?></p>

		<p><?php esc_html_e( 'regards,', 'cart-rest-api-for-woocommerce' ); ?></p>

		<div class="founder-row">
			<div class="founder-image">
				<img src="<?php echo 'https://www.gravatar.com/avatar/' . md5( strtolower( trim( 'mailme@sebastiendumont.com' ) ) ) . '?d=mp&s=60'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" width="60px" height="60px" alt="Photo of Founder" />
			</div>

			<div class="founder-details">
				<p>Sébastien Dumont<br>
				<?php
				echo sprintf(
					/* translators: %s: CoCart */
					esc_html__( 'Founder of %s', 'cart-rest-api-for-woocommerce' ),
					'CoCart'
				);
				?>
				</p>
			</div>
		</div>

		<div class="cocart-newsletter">
			<p><?php esc_html_e( 'Get product updates, tutorials and more straight to your inbox.', 'cart-rest-api-for-woocommerce' ); ?></p>
			<form action="https://xyz.us1.list-manage.com/subscribe/post?u=48ead612ad85b23fe2239c6e3&amp;id=d462357844&amp;SIGNUPPAGE=plugin" method="post" target="_blank" novalidate>
				<div class="newsletter-form-container">
					<input
						class="newsletter-form-email"
						type="email"
						value="<?php echo esc_attr( $user_email ); ?>"
						name="EMAIL"
						placeholder="<?php esc_attr_e( 'Email address', 'cart-rest-api-for-woocommerce' ); ?>"
						required
					>
					<p class="cocart-setup-wizard-actions step newsletter-form-button-container">
						<button
							type="submit"
							value="<?php esc_attr_e( 'Yes please!', 'cart-rest-api-for-woocommerce' ); ?>"
							name="subscribe"
							id="mc-embedded-subscribe"
							class="button button-primary newsletter-form-button"
						><?php esc_html_e( 'Yes please!', 'cart-rest-api-for-woocommerce' ); ?></button>
					</p>
				</div>
			</form>
		</div>

		<ul class="cocart-setup-wizard-next-steps">
			<li class="cocart-setup-wizard-next-step-item">
				<div class="cocart-setup-wizard-next-step-description">
					<p class="next-step-heading"><?php esc_html_e( 'Next step', 'cart-rest-api-for-woocommerce' ); ?></p>
					<h3 class="next-step-description"><?php esc_html_e( 'Start Developing', 'cart-rest-api-for-woocommerce' ); ?></h3>
					<p class="next-step-extra-info"><?php esc_html_e( "You're ready to develop your headless store.", 'cart-rest-api-for-woocommerce' ); ?></p>
				</div>
				<div class="cocart-setup-wizard-next-step-action">
					<p class="cocart-setup-wizard-actions step">
						<a class="button button-primary button-large" href="<?php echo esc_url( CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( 'https://docs.cocart.xyz' ) ) ) ); ?>" target="_blank">
							<?php esc_html_e( 'View Documentation', 'cart-rest-api-for-woocommerce' ); ?>
						</a>
					</p>
				</div>
			</li>
			<li class="cocart-setup-wizard-next-step-item">
				<div class="cocart-setup-wizard-next-step-description">
					<p class="next-step-heading"><?php esc_html_e( 'Need something else?', 'cart-rest-api-for-woocommerce' ); ?></p>
					<h3 class="next-step-description"><?php esc_html_e( 'Install Plugins', 'cart-rest-api-for-woocommerce' ); ?></h3>
					<p class="next-step-extra-info"><?php esc_html_e( 'Checkout plugin suggestions by CoCart.', 'cart-rest-api-for-woocommerce' ); ?></p>
				</div>
				<div class="cocart-setup-wizard-next-step-action">
					<p class="cocart-setup-wizard-actions step">
						<a class="button button-large" href="<?php echo esc_url( admin_url( 'plugin-install.php?tab=cocart' ) ); ?>" target="_blank">
							<?php esc_html_e( 'View Plugin Suggestions', 'cart-rest-api-for-woocommerce' ); ?>
						</a>
					</p>
				</div>
			</li>
			<li class="cocart-setup-wizard-additional-steps">
				<div class="cocart-setup-wizard-next-step-description">
					<p class="next-step-heading"><?php esc_html_e( 'You can also', 'cart-rest-api-for-woocommerce' ); ?></p>
				</div>
				<div class="cocart-setup-wizard-next-step-action">
					<p class="cocart-setup-wizard-actions step">
						<a class="button" href="<?php echo esc_url( admin_url() ); ?>">
							<?php esc_html_e( 'Visit Dashboard', 'cart-rest-api-for-woocommerce' ); ?>
						</a>
						<a class="button" href="<?php echo esc_url( 'https://www.npmjs.com/package/@cocart/cocart-rest-api' ); ?>" target="_blank">
							<?php esc_html_e( 'Download CoCart JS', 'cart-rest-api-for-woocommerce' ); ?>
						</a>
						<a class="button" href="<?php echo esc_url( 'https://marketplace.visualstudio.com/items?itemName=sebastien-dumont.cocart-vscode' ); ?>" target="_blank">
							<?php esc_html_e( 'Install CoCart VSCode Extension', 'cart-rest-api-for-woocommerce' ); ?>
						</a>
						<a class="button" href="<?php echo esc_url( CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( COCART_STORE_URL . 'community/' ) ) ) ); ?>" target="_blank">
							<?php esc_html_e( 'Join Community', 'cart-rest-api-for-woocommerce' ); ?>
						</a>
					</p>
				</div>
			</li>
		</ul>

		<p class="tweet-share">
			<a href="https://twitter.com/share" class="twitter-share-button" data-size="large" data-text="<?php echo esc_html( $this->tweets[ $tweet ] ); ?>" data-url="https://cocart.xyz/" data-hashtags="WooCommerce" data-related="WooCommerce" data-show-count="false">Tweet</a><script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script><?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
		</p>

		<p class="next-steps-help-text"><?php echo wp_kses_post( $help_text ); ?></p>
		<?php
	} // END cocart_setup_wizard_ready()

} // END class

return new CoCart_Admin_Setup_Wizard();
