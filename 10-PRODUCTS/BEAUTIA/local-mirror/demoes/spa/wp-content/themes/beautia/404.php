<?php
/**
 * 404.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="section error-404">
	<div class="container center">
		<span class="err-mark"><?php beautia_icon( 'polish', 48 ); ?></span>
		<h1>404</h1>
		<h2><?php esc_html_e( 'This page went for a manicure', 'beautia' ); ?></h2>
		<p><?php esc_html_e( 'The page you are looking for does not exist — but your next appointment does.', 'beautia' ); ?></p>
		<div class="wizard-actions center">
			<?php beautia_book_button( __( 'Book an appointment', 'beautia' ) ); ?>
			<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span><?php esc_html_e( 'Back home', 'beautia' ); ?></span></a>
		</div>
	</div>
</section>
<?php
get_footer();
