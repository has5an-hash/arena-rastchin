<?php
/** Additional dynamic blocks shared by Gutenberg, Elementor and classic content. */
defined( 'ABSPATH' ) || exit;

/** Capture template functions that echo. */
function beautia_capture( $callback ) {
	ob_start();
	call_user_func( $callback );
	return ob_get_clean();
}

add_shortcode( 'beautia_marquee', function( $atts ) {
	$atts  = shortcode_atts( array( 'words' => '' ), $atts );
	$words = array_filter( array_map( 'sanitize_text_field', explode( '|', $atts['words'] ) ) );
	return beautia_capture( function() use ( $words ) { beautia_marquee( $words ); } );
} );

add_shortcode( 'beautia_social', function() {
	return beautia_capture( 'beautia_social_links' );
} );

add_shortcode( 'beautia_contact_card', function() {
	$phone   = beautia_get_option( 'phone', get_theme_mod( 'beautia_phone', '' ) );
	$email   = get_theme_mod( 'beautia_email', get_option( 'admin_email' ) );
	$address = beautia_get_option( 'address', get_theme_mod( 'beautia_address', '' ) );
	ob_start(); ?>
	<div class="beautia-dynamic-card contact-card">
		<?php if ( $address ) : ?><p><?php beautia_icon( 'pin', 20 ); ?><span><?php echo esc_html( $address ); ?></span></p><?php endif; ?>
		<?php if ( $phone ) : ?><p><?php beautia_icon( 'phone', 20 ); ?><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></p><?php endif; ?>
		<?php if ( $email ) : ?><p><?php beautia_icon( 'mail', 20 ); ?><a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a></p><?php endif; ?>
	</div><?php return ob_get_clean();
} );

add_shortcode( 'beautia_map', function( $atts ) {
	$atts = shortcode_atts( array( 'url' => get_theme_mod( 'beautia_map', '' ), 'height' => 420 ), $atts );
	$url  = esc_url( $atts['url'] );
	if ( ! $url ) return current_user_can( 'edit_theme_options' ) ? '<div class="beautia-editor-note">' . esc_html__( 'Add the map embed URL in Beautia settings.', 'beautia' ) . '</div>' : '';
	return '<div class="beautia-map"><iframe src="' . $url . '" height="' . esc_attr( min( 720, max( 220, absint( $atts['height'] ) ) ) ) . '" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="' . esc_attr__( 'Location map', 'beautia' ) . '"></iframe></div>';
} );

add_shortcode( 'beautia_whatsapp', function( $atts ) {
	$atts   = shortcode_atts( array( 'number' => get_theme_mod( 'beautia_whatsapp', '' ), 'label' => __( 'Chat on WhatsApp', 'beautia' ), 'message' => '' ), $atts );
	$number = preg_replace( '/\D+/', '', beautia_fa_to_en_digits( $atts['number'] ) );
	if ( ! $number ) return '';
	$url = 'https://wa.me/' . $number . ( $atts['message'] ? '?text=' . rawurlencode( $atts['message'] ) : '' );
	return '<a class="btn btn-primary beautia-whatsapp" href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . beautia_get_icon( 'message', 18 ) . '<span>' . esc_html( $atts['label'] ) . '</span></a>';
} );

add_shortcode( 'beautia_opening_status', function() {
	$days  = array( 'sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat' );
	$hours = Beautia_Booking::business_hours();
	$key   = $days[ (int) current_time( 'w' ) ];
	$row   = isset( $hours[ $key ] ) ? $hours[ $key ] : array( 'open' => 0, 'from' => '', 'to' => '' );
	$open  = ! empty( $row['open'] );
	$text  = $open ? sprintf( __( 'Open today from %1$s to %2$s', 'beautia' ), beautia_num_time( $row['from'] ), beautia_num_time( $row['to'] ) ) : __( 'Closed today', 'beautia' );
	return '<div class="beautia-opening-status ' . ( $open ? 'is-open' : 'is-closed' ) . '"><i></i><span>' . esc_html( $text ) . '</span></div>';
} );

add_shortcode( 'beautia_service_search', function( $atts ) {
	$atts = shortcode_atts( array( 'label' => __( 'Search services', 'beautia' ) ), $atts );
	$url  = get_post_type_archive_link( 'beautia_service' );
	return '<form class="beautia-service-search" role="search" action="' . esc_url( $url ) . '"><label class="screen-reader-text" for="beautia-service-query">' . esc_html( $atts['label'] ) . '</label><input id="beautia-service-query" name="service_search" type="search" placeholder="' . esc_attr( $atts['label'] ) . '"><button class="btn btn-primary" type="submit">' . beautia_get_icon( 'search', 18 ) . '<span>' . esc_html__( 'Search', 'beautia' ) . '</span></button></form>';
} );

add_shortcode( 'beautia_quick_book', function( $atts ) {
	$atts = shortcode_atts( array( 'title' => __( 'Your next appointment is one minute away', 'beautia' ), 'text' => __( 'Choose a service and a genuinely free time, then verify your mobile.', 'beautia' ), 'label' => __( 'Book now', 'beautia' ) ), $atts );
	return '<div class="beautia-dynamic-card quick-book-block"><div><span class="eyebrow">' . esc_html__( 'Online booking', 'beautia' ) . '</span><h3>' . esc_html( $atts['title'] ) . '</h3><p>' . esc_html( $atts['text'] ) . '</p></div><a class="btn btn-primary" href="' . esc_url( beautia_get_page_url( 'booking' ) ) . '">' . esc_html( $atts['label'] ) . beautia_get_icon( 'arrow', 18 ) . '</a></div>';
} );

add_shortcode( 'beautia_availability', function( $atts ) {
	$atts = shortcode_atts( array( 'service' => 0, 'count' => 6 ), $atts );
	$service_id = absint( $atts['service'] );
	if ( ! $service_id ) {
		$services = get_posts( array( 'post_type' => 'beautia_service', 'posts_per_page' => 1, 'orderby' => 'ID', 'order' => 'ASC' ) );
		$service_id = $services ? $services[0]->ID : 0;
	}
	if ( ! $service_id ) return '';
	$date  = gmdate( 'Y-m-d', strtotime( '+1 day', current_time( 'timestamp' ) ) );
	$slots = array_slice( Beautia_Booking::get_slots( $service_id, 0, $date ), 0, min( 12, max( 1, absint( $atts['count'] ) ) ) );
	ob_start(); ?><div class="beautia-availability-widget"><div><span class="eyebrow"><?php esc_html_e( 'Next available times', 'beautia' ); ?></span><h3><?php echo esc_html( get_the_title( $service_id ) ); ?></h3><p><?php echo esc_html( beautia_format_date( $date ) ); ?></p></div><div class="availability-slots"><?php foreach ( $slots as $slot ) : ?><a href="<?php echo esc_url( add_query_arg( array( 'service' => $service_id, 'date' => $date ), beautia_get_page_url( 'booking' ) ) ); ?>"><strong><?php echo esc_html( $slot['label'] ); ?></strong><span><?php esc_html_e( 'Book', 'beautia' ); ?></span></a><?php endforeach; ?></div></div><?php return ob_get_clean();
} );

add_shortcode( 'beautia_icon_box', function( $atts, $content = '' ) {
	$atts = shortcode_atts( array( 'icon' => 'sparkle', 'title' => __( 'A thoughtful detail', 'beautia' ) ), $atts );
	$icon = array_key_exists( sanitize_key( $atts['icon'] ), beautia_icon_set() ) ? sanitize_key( $atts['icon'] ) : 'sparkle';
	return '<div class="beautia-dynamic-card icon-box"><span class="feature-ico">' . beautia_get_icon( $icon, 26 ) . '</span><h3>' . esc_html( $atts['title'] ) . '</h3><p>' . wp_kses_post( $content ?: __( 'Edit this text from Elementor or the shortcode.', 'beautia' ) ) . '</p></div>';
} );

add_shortcode( 'beautia_notice', function( $atts, $content = '' ) {
	$atts  = shortcode_atts( array( 'style' => 'info' ), $atts );
	$style = in_array( $atts['style'], array( 'info', 'success', 'warning' ), true ) ? $atts['style'] : 'info';
	return '<div class="beautia-notice is-' . esc_attr( $style ) . '">' . beautia_get_icon( 'bell', 20 ) . '<p>' . wp_kses_post( $content ?: __( 'A short, editable announcement.', 'beautia' ) ) . '</p></div>';
} );

add_shortcode( 'beautia_navigation', function( $atts ) {
	$atts = shortcode_atts( array( 'location' => 'primary' ), $atts );
	$location = in_array( $atts['location'], array( 'primary', 'footer', 'account' ), true ) ? $atts['location'] : 'primary';
	return wp_nav_menu( array( 'theme_location' => $location, 'container' => 'nav', 'container_class' => 'beautia-dynamic-nav', 'echo' => false, 'fallback_cb' => false ) );
} );

add_shortcode( 'beautia_single_service', function( $atts ) {
	$atts = shortcode_atts( array( 'id' => 0 ), $atts );
	$id = absint( $atts['id'] );
	if ( ! $id ) { $posts = get_posts( array( 'post_type' => 'beautia_service', 'posts_per_page' => 1 ) ); $id = $posts ? $posts[0]->ID : 0; }
	return $id ? beautia_capture( function() use ( $id ) { beautia_service_card( $id, 0 ); } ) : '';
} );

add_shortcode( 'beautia_single_specialist', function( $atts ) {
	$atts = shortcode_atts( array( 'id' => 0 ), $atts );
	$id = absint( $atts['id'] );
	if ( ! $id ) { $posts = get_posts( array( 'post_type' => 'beautia_staff', 'posts_per_page' => 1 ) ); $id = $posts ? $posts[0]->ID : 0; }
	if ( ! $id ) return '';
	$image = get_the_post_thumbnail( $id, 'beautia-portrait', array( 'alt' => get_the_title( $id ) ) );
	return '<article class="staff-card beautia-single-specialist"><div class="staff-photo">' . $image . '</div><h3>' . esc_html( get_the_title( $id ) ) . '</h3><span class="staff-role">' . esc_html( get_post_meta( $id, '_beautia_role', true ) ) . '</span><a class="btn btn-outline" href="' . esc_url( add_query_arg( 'staff', $id, beautia_get_page_url( 'booking' ) ) ) . '">' . esc_html__( 'Book with me', 'beautia' ) . '</a></article>';
} );

add_shortcode( 'beautia_loyalty', function() {
	return '<div class="beautia-dynamic-card loyalty-card"><span class="feature-ico">' . beautia_get_icon( 'heart', 28 ) . '</span><div><span class="eyebrow">' . esc_html__( 'Customer club', 'beautia' ) . '</span><h3>' . esc_html__( 'Every visit brings the next reward closer', 'beautia' ) . '</h3><p>' . esc_html__( 'Four loyalty levels, automatic points and discounts are managed in the customer panel.', 'beautia' ) . '</p></div><a class="text-link" href="' . esc_url( beautia_get_page_url( 'account' ) ) . '">' . esc_html__( 'My customer panel', 'beautia' ) . '</a></div>';
} );
