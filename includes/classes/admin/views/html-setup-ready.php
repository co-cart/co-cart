<?php
/**
 * Admin View: Setup Ready.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   3.10.0 Introduced.
 * @version 4.6.0
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<h1><?php esc_html_e( "You're ready!", 'cocart-core' ); ?></h1>

<p>
<?php
printf(
	/* translators: %s: CoCart */
	esc_html__( 'Now that you have %s installed, your ready to start developing your headless store in modern and scalable storefront with confidence independent of WordPress using frameworks like Astro, React, Vue, or Next.js, gaining complete control over your customers experience no matter what your store sells.', 'cocart-core' ),
	'CoCart'
);
?>
</p>

<p>
	<?php
	echo wp_kses_post(
		sprintf(
			/* translators: %1$s: link to frequently asked questions, %2$s: link to contact support */
			__( 'If you have questions, please check out our most <a href="%1$s" target="_blank" rel="noopener noreferrer">frequently asked questions</a>. If you don\'t find the answer your looking for, <a href="%2$s">contact support</a>.', 'cocart-core' ),
			'https://cocartapi.com/faq/',
			'mailto:support@cocartapi.com'
		)
	);
	?>
</p>

<p>
	<?php
	printf(
		/* translators: %1$s: CoCart, %2$s: Discord */
		esc_html__( 'Want to talk to someone about converting your WooCommerce store headless? Come join the %1$s community on %2$s.', 'cocart-core' ),
		'CoCart',
		'Discord'
	);
	?>
</p>

<p><?php esc_html_e( 'Thank you and enjoy!', 'cocart-core' ); ?></p>

<p><?php esc_html_e( 'Regards,', 'cocart-core' ); ?></p>

<div class="founder-row">
	<div class="founder-image">
		<img src="<?php echo esc_url( COCART_URL_PATH . '/assets/images/avatar.jpeg' ); ?>" width="60px" height="60px" alt="Photo of Founder" /><?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
	</div>

	<div class="founder-details">
		<p>Sébastien Dumont<br>
		<?php
		printf(
			/* translators: %s: CoCart Headless, LLC */
			esc_html__( 'Founder of %s', 'cocart-core' ),
			'CoCart Headless, LLC'
		);
		?>
		</p>
	</div>
</div>
