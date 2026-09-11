<?php
/**
 * Shortcodes.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

/** Helper to buffer a template tag. */
function beautia_buffer( $callback, $args = array() ) {
	ob_start();
	call_user_func( $callback, $args );
	return ob_get_clean();
}

add_shortcode( 'beautia_hero', function ( $atts ) {
	return beautia_buffer( 'beautia_section_hero', (array) $atts );
} );

add_shortcode( 'beautia_services', function ( $atts ) {
	return beautia_buffer( 'beautia_section_services', shortcode_atts( array( 'count' => 6, 'category' => '' ), $atts ) );
} );

add_shortcode( 'beautia_portfolio', function ( $atts ) {
	return beautia_buffer( 'beautia_section_portfolio', shortcode_atts( array( 'count' => 9 ), $atts ) );
} );

add_shortcode( 'beautia_team', function ( $atts ) {
	return beautia_buffer( 'beautia_section_team', shortcode_atts( array( 'count' => 4 ), $atts ) );
} );

add_shortcode( 'beautia_testimonials', function () {
	ob_start();
	beautia_section_testimonials();
	return ob_get_clean();
} );

add_shortcode( 'beautia_pricing', function () {
	ob_start();
	beautia_section_pricing();
	return ob_get_clean();
} );

add_shortcode( 'beautia_stats', function () {
	ob_start();
	beautia_section_stats();
	return ob_get_clean();
} );

add_shortcode( 'beautia_features', function () {
	ob_start();
	beautia_section_features();
	return ob_get_clean();
} );

add_shortcode( 'beautia_before_after', function () {
	ob_start();
	beautia_section_before_after();
	return ob_get_clean();
} );

add_shortcode( 'beautia_compare', function ( $atts ) {
	$atts = shortcode_atts(
		array(
			'before'       => '',
			'after'        => '',
			'title'        => '',
			'before_label' => __( 'Before', 'beautia' ),
			'after_label'  => __( 'After', 'beautia' ),
			'start'        => 50,
		),
		$atts,
		'beautia_compare'
	);
	ob_start();
	beautia_compare(
		$atts['before'],
		$atts['after'],
		array(
			'title'        => $atts['title'],
			'before_label' => $atts['before_label'],
			'after_label'  => $atts['after_label'],
			'start'        => (int) $atts['start'],
		)
	);
	return ob_get_clean();
} );

add_shortcode( 'beautia_blog', function ( $atts ) {
	$atts = shortcode_atts( array( 'count' => 3 ), $atts );
	ob_start();
	beautia_section_blog( $atts['count'] );
	return ob_get_clean();
} );

add_shortcode( 'beautia_cta', function () {
	ob_start();
	beautia_section_cta();
	return ob_get_clean();
} );

add_shortcode( 'beautia_booking', function () {
	ob_start();
	beautia_booking_wizard();
	return ob_get_clean();
} );

add_shortcode( 'beautia_login', function () {
	ob_start();
	beautia_login_form();
	return ob_get_clean();
} );

add_shortcode( 'beautia_account', function () {
	ob_start();
	get_template_part( 'template-parts/account/panel' );
	return ob_get_clean();
} );

/** Opening hours table. */
add_shortcode( 'beautia_hours', function () {
	$hours = Beautia_Booking::business_hours();
	$names = array(
		'sat' => __( 'Saturday', 'beautia' ),
		'sun' => __( 'Sunday', 'beautia' ),
		'mon' => __( 'Monday', 'beautia' ),
		'tue' => __( 'Tuesday', 'beautia' ),
		'wed' => __( 'Wednesday', 'beautia' ),
		'thu' => __( 'Thursday', 'beautia' ),
		'fri' => __( 'Friday', 'beautia' ),
	);
	ob_start();
	echo '<ul class="hours-list">';
	foreach ( $names as $key => $label ) {
		$row = $hours[ $key ];
		printf(
			'<li><span>%s</span><em>%s</em></li>',
			esc_html( $label ),
			$row['open'] ? esc_html( beautia_en_to_fa_digits($row['from'] . ' تا ' . $row['to']) ) : esc_html__( 'Closed', 'beautia' )
		);
	}
	echo '</ul>';
	return ob_get_clean();
} );

/** Appointment tracking form (public, by code). */
add_shortcode( 'beautia_track', function () {
	$code = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	ob_start();
	?>
	<div class="track-box">
		<form method="get">
			<input type="text" name="code" value="<?php echo esc_attr( $code ); ?>" placeholder="<?php esc_attr_e( 'Tracking code e.g. BK7H2K9A', 'beautia' ); ?>" />
			<button class="btn btn-primary"><span><?php esc_html_e( 'Track', 'beautia' ); ?></span></button>
		</form>
		<?php
		if ( $code ) {
			$row = Beautia_Booking::get_by_code( $code );
			if ( ! $row ) {
				echo '<p class="msg is-error">' . esc_html__( 'No appointment found with this code.', 'beautia' ) . '</p>';
			} else {
				$vars = Beautia_Booking::sms_vars( $row );
				echo '<ul class="track-result">';
				foreach ( array( 'service', 'staff', 'date', 'time', 'status' ) as $k ) {
					printf( '<li><span>%s</span><strong>%s</strong></li>', esc_html( ucfirst( $k ) ), esc_html( $vars[ $k ] ) );
				}
				echo '</ul>';
			}
		}
		?>
	</div>
	<?php
	return ob_get_clean();
} );

add_shortcode( 'beautia_about', function ( $atts ) {
	ob_start();
	beautia_section_about( is_array( $atts ) ? $atts : array() );
	return ob_get_clean();
} );

add_shortcode( 'beautia_steps', function () {
	ob_start();
	beautia_section_steps();
	return ob_get_clean();
} );

add_shortcode( 'beautia_faq', function () {
	ob_start();
	beautia_section_faq();
	return ob_get_clean();
} );
