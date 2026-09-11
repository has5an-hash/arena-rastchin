<?php
/**
 * Template Name: Booking Wizard
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="page-hero small">
	<div class="container">
		<span class="eyebrow"><?php beautia_icon( 'calendar', 16 ); ?><?php esc_html_e( 'Online reservation', 'beautia' ); ?></span>
		<h1><?php the_title(); ?></h1>
		<p><?php esc_html_e( 'Four quick steps: service, specialist, time and mobile verification.', 'beautia' ); ?></p>
	</div>
</div>
<div class="container section">
	<?php beautia_booking_wizard(); ?>
</div>
<?php
get_footer();
