<?php
/**
 * Admin View: Setup Ready.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   3.10.0 Introduced.
 * @version 4.3.7
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$docs_url  = esc_url( 'https://cocart.dev/' ); // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$help_text = sprintf( // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	/* translators: %1$s: link to developers resources */
	__( 'Visit CoCart.dev to access <a href="%1$s" target="_blank" rel="noopener noreferrer">developer resources</a>.', 'cart-rest-api-for-woocommerce' ),
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
	echo wp_kses_post(
		sprintf(
			/* translators: %1$s: CoCart, %2$s: GitHub repository link */
			__( 'In the documentation you will find references to the API routes available with some examples. If you want to modify or extend %1$s in any way, there are over 100+ action hooks and filters for developers to use that can customize %1$s to your specific needs, which you can <a href="%2$s" target="_blank" rel="noopener noreferrer">search in the GitHub repository</a>.', 'cart-rest-api-for-woocommerce' ),
			'CoCart',
			COCART_REPO_URL
		)
	);
	?>
</p>

<p>
	<?php
	echo wp_kses_post(
		sprintf(
			/* translators: %1$s: CoCart, %2$s: Developers Hub link */
			__( 'There is also a <a href="%2$s" target="_blank" rel="noopener noreferrer">developers hub</a> where you can find all the resources you need to be productive with %1$s and keep track of everything that is happening with the plugin including development decisions and scoping of future versions.', 'cart-rest-api-for-woocommerce' ),
			'CoCart',
			$docs_url
		)
	);
	?>
</p>

<p>
	<?php
	esc_html_e( 'If in need of help, most common questions are answered and guides are provided on the developers hub. This is best place to look at first before contacting support.', 'cart-rest-api-for-woocommerce' );
	?>
</p>

<p>
	<?php
	printf(
		/* translators: %1$s: CoCart, %2$s: Discord */
		esc_html__( 'Want to talk to someone about converting your WooCommerce store headless? Come join the %1$s community on %2$s.', 'cart-rest-api-for-woocommerce' ),
		'CoCart',
		'Discord'
	);
	?>
</p>

<p><?php esc_html_e( 'Thank you and enjoy!', 'cart-rest-api-for-woocommerce' ); ?></p>

<p><?php esc_html_e( 'Regards,', 'cart-rest-api-for-woocommerce' ); ?></p>

<div class="founder-row">
	<div class="founder-image">
		<img src="<?php echo esc_url( COCART_URL_PATH . '/assets/images/avatar.jpeg' ); ?>" width="60px" height="60px" alt="Photo of Founder" />
	</div>

	<div class="founder-details">
		<p>Sébastien Dumont<br>
		<?php
		printf(
			/* translators: %s: CoCart Headless, LLC */
			esc_html__( 'Founder of %s', 'cart-rest-api-for-woocommerce' ),
			'CoCart Headless, LLC'
		);
		?>
		</p>
	</div>
</div>
