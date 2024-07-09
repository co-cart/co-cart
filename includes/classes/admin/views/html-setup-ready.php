<?php
/**
 * Admin View: Setup Ready.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   3.10.0
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$docs_url  = esc_url( 'https://cocart.dev/' ); // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$help_text = sprintf( // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	/* translators: %1$s: link to developers resources */
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

<p><?php esc_html_e( 'Regards,', 'cart-rest-api-for-woocommerce' ); ?></p>

<div class="founder-row">
	<div class="founder-image">
		<img src="<?php echo esc_url( 'https://www.gravatar.com/avatar/' . md5( strtolower( trim( 'mailme@sebastiendumont.com' ) ) ) . '?d=mp&s=60' ); ?>" width="60px" height="60px" alt="Photo of Founder" />
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
