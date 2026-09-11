<?php
/**
 * Footer.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;
?>
	</main>

	<?php if ( ! is_page_template( array( 'page-templates/booking.php', 'page-templates/account.php', 'page-templates/login.php' ) ) ) : ?>
		<?php beautia_section_cta(); ?>
	<?php endif; ?>

	<footer id="colophon" class="site-footer">
		<div class="container footer-grid">
			<div class="footer-col footer-about">
				<?php beautia_branding(); ?>
				<p><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
				<?php beautia_social_links(); ?>
			</div>

			<div class="footer-col">
				<h4 class="widget-title"><?php esc_html_e( 'Contact', 'beautia' ); ?></h4>
				<ul class="contact-list">
					<?php $address = get_theme_mod( 'beautia_address', beautia_get_option( 'address', '' ) ); ?>
					<?php if ( $address ) : ?>
						<li><?php beautia_icon( 'pin', 18 ); ?><span><?php echo esc_html( $address ); ?></span></li>
					<?php endif; ?>
					<?php $phone = get_theme_mod( 'beautia_phone', beautia_get_option( 'phone', '' ) ); ?>
					<?php if ( $phone ) : ?>
						<li><?php beautia_icon( 'phone', 18 ); ?><a href="tel:<?php echo esc_attr( beautia_fa_to_en_digits($phone) ); ?>"><?php echo esc_html( $phone ); ?></a></li>
					<?php endif; ?>
					<?php $email = get_theme_mod( 'beautia_email' ); ?>
					<?php if ( $email ) : ?>
						<li><?php beautia_icon( 'mail', 18 ); ?><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
					<?php endif; ?>
				</ul>
			</div>

			<div class="footer-col">
				<h4 class="widget-title"><?php esc_html_e( 'Opening hours', 'beautia' ); ?></h4>
				<?php echo do_shortcode( '[beautia_hours]' ); ?>
			</div>

			<div class="footer-col">
				<?php if ( is_active_sidebar( 'footer-4' ) ) : ?>
					<?php dynamic_sidebar( 'footer-4' ); ?>
				<?php else : ?>
					<h4 class="widget-title"><?php esc_html_e( 'Customer area', 'beautia' ); ?></h4>
					<ul class="footer-links">
						<li><a href="<?php echo esc_url( beautia_get_page_url( 'booking' ) ); ?>"><?php beautia_icon( 'calendar', 16 ); ?><?php esc_html_e( 'Book an appointment', 'beautia' ); ?></a></li>
						<li><a href="<?php echo esc_url( beautia_get_page_url( 'account' ) ); ?>"><?php beautia_icon( 'user', 16 ); ?><?php esc_html_e( 'My appointments', 'beautia' ); ?></a></li>
						<li><a href="<?php echo esc_url( beautia_get_page_url( 'login' ) ); ?>"><?php beautia_icon( 'mobile', 16 ); ?><?php esc_html_e( 'Login with mobile', 'beautia' ); ?></a></li>
					</ul>
				<?php endif; ?>
			</div>
		</div>

		<div class="footer-bottom">
			<div class="container">
				<p>
					<?php
					$copy = get_theme_mod( 'beautia_copyright' );
					if ( $copy ) {
						echo wp_kses_post( $copy );
					} else {
						printf(
							/* translators: 1: year 2: site name */
							esc_html__( '© %1$s %2$s — Crafted with Beautia.', 'beautia' ),
							esc_html( beautia_en_to_fa_digits( gmdate( 'Y' ) ) ),
							esc_html( get_bloginfo( 'name' ) )
						);
					}
					?>
				</p>
				<?php
				if ( has_nav_menu( 'footer' ) ) {
					wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'menu_class' => 'footer-menu', 'depth' => 1 ) );
				}
				?>
			</div>
		</div>
	<?php if(get_option('beautia_showcase_enabled')): ?><p class="container demo-disclosure">نسخه نمایشی بیوتیا؛ نام‌ها، دیدگاه‌ها و نوبت‌ها برای معرفی قالب ساخته شده‌اند. تصاویر، نمونه‌های معرفی سبک هستند.</p><?php endif; ?>
	</footer>

	<button class="to-top" id="beautia-top" aria-label="<?php esc_attr_e( 'Back to top', 'beautia' ); ?>"><?php beautia_icon( 'chevron', 20 ); ?></button>
	<?php beautia_sticky_cta(); ?>
	<?php beautia_booking_drawer(); ?>
</div>

<div class="lightbox" id="beautia-lightbox" hidden>
	<button class="lightbox-close" aria-label="<?php esc_attr_e( 'Close', 'beautia' ); ?>"><?php beautia_icon( 'close', 24 ); ?></button>
	<div class="lightbox-stage"></div>
</div>

<?php wp_footer(); ?>
</body>
</html>
