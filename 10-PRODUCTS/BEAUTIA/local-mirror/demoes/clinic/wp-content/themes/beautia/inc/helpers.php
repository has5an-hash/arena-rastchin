<?php
/**
 * Generic helpers.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is the front end right-to-left?
 *
 * The theme ships Persian-first, so RTL is on by default and can be switched
 * off in the Customizer (Beautia Theme → Demo / Style → Text direction).
 */
function beautia_is_rtl() {
	if ( beautia_get_option( 'force_persian', 1 ) ) return true;
	$mode = beautia_get_option( 'direction', get_theme_mod( 'beautia_direction', 'auto' ) );
	if ( 'rtl' === $mode ) {
		return true;
	}
	if ( 'ltr' === $mode ) {
		return false;
	}
	return is_rtl() || in_array( substr( get_locale(), 0, 2 ), array( 'fa', 'ar', 'he', 'ur', 'ps', 'ku' ), true );
}

/**
 * Force dir="rtl" on the html tag when the theme runs in RTL mode.
 */
function beautia_language_attributes( $output ) {
	if ( beautia_is_rtl() && false === strpos( $output, 'dir=' ) ) {
		$output .= ' dir="rtl"';
	}
	return $output;
}
add_filter( 'language_attributes', 'beautia_language_attributes' );

/**
 * Convert latin digits to Persian digits for display.
 */
function beautia_en_to_fa_digits( $string ) {
	$en = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
	$fa = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' );
	return str_replace( $en, $fa, (string) $string );
}

/**
 * Localise a number for output (Persian digits + thousand separators).
 */
function beautia_num( $number, $decimals = 0 ) {
	$value = number_format( (float) $number, $decimals, '.', '،' === '،' ? ',' : ',' );
	if ( beautia_use_persian_digits() ) {
		$value = beautia_en_to_fa_digits( $value );
	}
	return $value;
}

/**
 * Should numbers be printed with Persian digits?
 */
function beautia_use_persian_digits() {
	$opt = beautia_get_option( 'persian_digits', null );
	if ( null !== $opt && '' !== $opt ) {
		return (bool) $opt;
	}
	return beautia_is_rtl() && 0 === strpos( get_locale(), 'fa' );
}

/* -------------------------------------------------------------------------
 * UTF-8 / multibyte safety
 *
 * Persian text is multibyte: every truncation, length check and case change
 * has to be mb_* aware, otherwise words break into mojibake. These helpers
 * degrade gracefully when the mbstring extension is unavailable.
 * ---------------------------------------------------------------------- */

/**
 * Multibyte-safe string length.
 */
function beautia_strlen( $string ) {
	return function_exists( 'mb_strlen' ) ? mb_strlen( (string) $string, 'UTF-8' ) : strlen( (string) $string );
}

/**
 * Multibyte-safe substring.
 */
function beautia_substr( $string, $start, $length = null ) {
	if ( function_exists( 'mb_substr' ) ) {
		return mb_substr( (string) $string, $start, $length, 'UTF-8' );
	}
	return null === $length ? substr( (string) $string, $start ) : substr( (string) $string, $start, $length );
}

/**
 * Multibyte-safe truncation with an ellipsis, never cutting a word in half.
 */
function beautia_truncate( $string, $limit = 120, $more = '…' ) {
	$string = wp_strip_all_tags( (string) $string );
	if ( beautia_strlen( $string ) <= $limit ) {
		return $string;
	}
	$cut   = beautia_substr( $string, 0, $limit );
	$space = function_exists( 'mb_strrpos' ) ? mb_strrpos( $cut, ' ', 0, 'UTF-8' ) : strrpos( $cut, ' ' );
	if ( $space && $space > $limit * 0.6 ) {
		$cut = beautia_substr( $cut, 0, $space );
	}
	return trim( $cut ) . $more;
}

/**
 * Normalise a UTF-8 string coming from a form: strips zero-width characters
 * and the Arabic Yeh/Kaf that Persian keyboards produce, so search, matching
 * and slugs behave.
 */
function beautia_normalize_fa( $string ) {
	$string = (string) $string;
	$map    = array(
		"Ù" => 'ی', // Arabic Yeh  -> Persian Yeh.
		"Ù" => 'ک', // Arabic Kaf  -> Persian Keheh.
		"ï»¿" => '', // BOM.
		"â" => '‌',  // keep ZWNJ, normalise encoding.
		"â" => '', // zero width space.
		"â" => '', // LRM.
		"â" => '', // RLM.
	);
	$string = strtr( $string, $map );
	return trim( $string );
}

/**
 * SMS segment counter. Persian messages are sent as UCS-2: 70 characters for
 * a single part, 67 per part once the message is concatenated.
 */
function beautia_sms_parts( $message ) {
	$len     = beautia_strlen( $message );
	$unicode = preg_match( '/[^\x00-\x7F]/', $message );
	if ( ! $unicode ) {
		return $len <= 160 ? 1 : (int) ceil( $len / 153 );
	}
	return $len <= 70 ? 1 : (int) ceil( $len / 67 );
}

/**
 * Force UTF-8 HTML mail so Persian subjects and bodies never arrive garbled.
 */
function beautia_mail_content_type() {
	return 'text/html; charset=UTF-8';
}

/**
 * Is the dark-mode switch available on the front end?
 */
function beautia_dark_mode_enabled() {
	return (bool) beautia_get_option( 'dark_mode', 1 );
}

/**
 * Print the dark / light switch.
 */
function beautia_theme_toggle() {
	if (get_option('beautia_showcase_enabled')) return;
	if ( ! beautia_dark_mode_enabled() ) {
		return;
	}
	printf(
		'<button class="theme-toggle" id="beautia-theme-toggle" type="button" aria-label="%1$s" title="%1$s">%2$s%3$s</button>',
		esc_attr__( 'Switch between light and dark mode', 'beautia' ),
		beautia_get_icon( 'moon', 19, 'ico-moon' ), // phpcs:ignore
		beautia_get_icon( 'sun', 19, 'ico-sun' )    // phpcs:ignore
	);
}

/**
 * Inline script printed in <head> so the saved theme is applied before the
 * first paint — no white flash for people who chose dark mode.
 */
function beautia_dark_mode_head() {
	if ( get_option('beautia_showcase_enabled') ) {
		echo '<script>document.documentElement.removeAttribute("data-theme");document.documentElement.dataset.beautiaAppearance="pastel";</script>';
		return;
	}
	if ( ! beautia_dark_mode_enabled() ) {
		return;
	}
	$default = beautia_get_option( 'dark_default', 'auto' );
	?>
	<script id="beautia-theme-boot">
	(function(){try{
		var saved = localStorage.getItem('beautia-theme');
		var def = <?php echo wp_json_encode( $default ); ?>;
		var dark = saved ? (saved === 'dark')
			: (def === 'dark' || (def === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches));
		if (dark) { document.documentElement.setAttribute('data-theme','dark'); }
	}catch(e){}})();
	</script>
	<?php
}
add_action( 'wp_head', 'beautia_dark_mode_head', 1 );

/**
 * Localised week-day labels, ordered the Persian way (Saturday first).
 */
function beautia_week_days() {
	return array(
		'sat' => __( 'Saturday', 'beautia' ),
		'sun' => __( 'Sunday', 'beautia' ),
		'mon' => __( 'Monday', 'beautia' ),
		'tue' => __( 'Tuesday', 'beautia' ),
		'wed' => __( 'Wednesday', 'beautia' ),
		'thu' => __( 'Thursday', 'beautia' ),
		'fri' => __( 'Friday', 'beautia' ),
	);
}

/**
 * A clock string (09:30) with the digits localised.
 */
function beautia_num_time( $time ) {
	$time = (string) $time;
	return beautia_use_persian_digits() ? beautia_en_to_fa_digits( $time ) : $time;
}

/**
 * Reading time in minutes. str_word_count() cannot see Persian words, so we
 * count UTF-8 word boundaries ourselves.
 */
function beautia_reading_time( $content = '' ) {
	$content = wp_strip_all_tags( $content ? $content : get_the_content() );
	$words   = preg_split( '/[\s\x{200C}]+/u', trim( $content ), -1, PREG_SPLIT_NO_EMPTY );
	$count   = is_array( $words ) ? count( $words ) : 0;
	return max( 1, (int) ceil( $count / 200 ) );
}

/**
 * Share links for a post (no third-party script, no tracking).
 */
function beautia_share_links( $url = '', $title = '' ) {
	$url   = $url ? $url : get_permalink();
	$title = $title ? $title : get_the_title();
	return array(
		'telegram'  => 'https://t.me/share/url?url=' . rawurlencode( $url ) . '&text=' . rawurlencode( $title ),
		'whatsapp'  => 'https://wa.me/?text=' . rawurlencode( $title . ' ' . $url ),
		'instagram' => '',
	);
}

/**
 * The accent colour currently in use (customizer override or demo default).
 */
function beautia_accent_color() {
	$mod = get_theme_mod( 'beautia_color_accent' );
	if ( $mod ) {
		return $mod;
	}
	$demos = beautia_get_demos();
	$demo  = beautia_get_active_demo();
	return isset( $demos[ $demo ]['accent'] ) ? $demos[ $demo ]['accent'] : '#E8A0BF';
}

/**
 * Available demos.
 */
function beautia_get_demos() {
	return apply_filters(
		'beautia_demos',
		array(
			'nails'  => array(
				'label'   => __( 'Nail Studio', 'beautia' ),
				'accent'  => '#E8A0BF',
				'accent2' => '#B57EDC',
				'desc'    => __( 'Glossy, playful nail art studio with gallery-first layout.', 'beautia' ),
			),
			'clinic' => array(
				'label'   => __( 'Aesthetic & Medical Clinic', 'beautia' ),
				'accent'  => '#2F9C95',
				'accent2' => '#C9A227',
				'desc'    => __( 'Clean, trustworthy clinic demo with doctors and treatments.', 'beautia' ),
			),
			'hair'   => array(
				'label'   => __( 'Hair Salon', 'beautia' ),
				'accent'  => '#C6A15B',
				'accent2' => '#14110F',
				'desc'    => __( 'Dark editorial salon demo with gold typography.', 'beautia' ),
			),
			'spa'    => array(
				'label'   => __( 'Spa & Wellness', 'beautia' ),
				'accent'  => '#7C9070',
				'accent2' => '#D9C5A0',
				'desc'    => __( 'Calm organic spa demo with soft motion.', 'beautia' ),
			),
			'lashes' => array(
				'label'   => __( 'Brows & Lashes Bar', 'beautia' ),
				'accent'  => '#8A5A44',
				'accent2' => '#EBD8C3',
				'desc'    => __( 'Warm nude brow-bar demo with before/after slider.', 'beautia' ),
			),
			'makeup' => array(
				'label'   => __( 'Makeup Artist & Academy', 'beautia' ),
				'accent'  => '#D7263D',
				'accent2' => '#1B1B1E',
				'desc'    => __( 'Bold high-contrast makeup artist / academy demo.', 'beautia' ),
			),
			'barber' => array(
				'label'   => __( 'Barbershop', 'beautia' ),
				'accent'  => '#B8763A',
				'accent2' => '#1C1A17',
				'desc'    => __( 'Masculine barbershop demo with fades, beards and hot-towel shaves.', 'beautia' ),
			),
		)
	);
}

/**
 * Currently active demo slug.
 */
function beautia_get_active_demo() {
	if ( function_exists('beautia_demo_context') && beautia_demo_context() ) return beautia_demo_context();
	$demo = get_theme_mod( 'beautia_demo', 'nails' );

	// Allow live demo switching through ?demo= for the theme preview site.
	if ( isset( $_GET['demo'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$requested = sanitize_key( wp_unslash( $_GET['demo'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( array_key_exists( $requested, beautia_get_demos() ) ) {
			$demo = $requested;
		}
	}

	$page_demo = is_singular() ? get_post_meta( get_the_ID(), '_beautia_demo', true ) : '';
	if ( $page_demo && array_key_exists( $page_demo, beautia_get_demos() ) ) {
		$demo = $page_demo;
	}

	return $demo;
}

/**
 * Theme option getter (stored in a single option array + customizer fallback).
 *
 * @param string $key     Option key.
 * @param mixed  $default Default value.
 */
function beautia_get_option( $key, $default = '' ) {
	if ( function_exists('beautia_demo_context') && empty($GLOBALS['beautia_seeding']) ) {
		$context = beautia_demo_context();
		$map = get_option('beautia_studio_map', array());
		if ($context && isset($map[$context])) {
			if (strpos($key, 'page_') === 0 && isset($map[$context]['pages'][substr($key,5)])) return $map[$context]['pages'][substr($key,5)];
			if (isset($map[$context]['options'][$key])) return $map[$context]['options'][$key];
		}
	}
	$options = get_option( 'beautia_options', array() );
	if ( isset( $options[ $key ] ) && '' !== $options[ $key ] ) {
		return $options[ $key ];
	}
	$mod = get_theme_mod( 'beautia_' . $key, null );
	if ( null !== $mod && '' !== $mod ) {
		return $mod;
	}
	return $default;
}

/**
 * Update a single theme option.
 */
function beautia_update_option( $key, $value ) {
	$options         = get_option( 'beautia_options', array() );
	$options[ $key ] = $value;
	update_option( 'beautia_options', $options );
}

/**
 * Special pages created by the theme.
 */
function beautia_default_pages() {
	return array(
		'booking' => array(
			'title'    => __( 'Book an Appointment', 'beautia' ),
			'template' => 'page-templates/booking.php',
		),
		'account' => array(
			'title'    => __( 'My Account', 'beautia' ),
			'template' => 'page-templates/account.php',
		),
		'login'   => array(
			'title'    => __( 'Login / Register', 'beautia' ),
			'template' => 'page-templates/login.php',
		),
	);
}

/**
 * Create the booking / account / login pages.
 */
function beautia_create_default_pages() {
	foreach ( beautia_default_pages() as $key => $page ) {
		$stored = (int) beautia_get_option( 'page_' . $key );
		if ( $stored && 'publish' === get_post_status( $stored ) ) {
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_title'   => $page['title'],
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '',
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_wp_page_template', $page['template'] );
			beautia_update_option( 'page_' . $key, $id );
		}
	}
}

/**
 * URL of one of the theme pages.
 */
function beautia_get_page_url( $key ) {
	$id = (int) beautia_get_option( 'page_' . $key );
	return $id ? get_permalink( $id ) : home_url( '/' );
}

/**
 * Inline SVG icon.
 *
 * @param string $name  Icon name.
 * @param int    $size  Pixel size.
 * @param string $class Extra class.
 */
function beautia_icon( $name, $size = 24, $class = '' ) {
	echo beautia_get_icon( $name, $size, $class ); // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Return an inline SVG icon from the theme icon set.
 */
function beautia_get_icon( $name, $size = 24, $class = '' ) {
	$icons = beautia_icon_set();
	if ( ! isset( $icons[ $name ] ) ) {
		$name = 'sparkle';
	}
	return sprintf(
		'<svg class="bi bi-%1$s %2$s" width="%3$d" height="%3$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%4$s</svg>',
		esc_attr( $name ),
		esc_attr( $class ),
		(int) $size,
		$icons[ $name ]
	);
}

/**
 * Hand-drawn beauty icon set (no external icon font / paid plugin needed).
 */
function beautia_icon_set() {
	return array(
		'sparkle'    => '<path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9L12 3z"/><path d="M18.5 15.5l.8 2.2 2.2.8-2.2.8-.8 2.2-.8-2.2-2.2-.8 2.2-.8.8-2.2z"/>',
		'nail'       => '<path d="M9 21h6a1 1 0 001-1V9a4 4 0 00-8 0v11a1 1 0 001 1z"/><path d="M8 13h8"/><path d="M10.5 5.5c.6-.8 2.4-.8 3 0"/>',
		'polish'     => '<rect x="8" y="10" width="8" height="11" rx="2"/><path d="M10 10V7h4v3"/><path d="M11 3h2v4h-2z"/><path d="M8 14h8"/>',
		'hand'       => '<path d="M8 13V5.5a1.5 1.5 0 013 0V12"/><path d="M11 12V4.5a1.5 1.5 0 013 0V12"/><path d="M14 12V6.5a1.5 1.5 0 013 0V15a6 6 0 01-6 6h-1a6 6 0 01-6-6v-2.5a1.5 1.5 0 013 0V14"/>',
		'scissors'   => '<circle cx="6" cy="6" r="2.5"/><circle cx="6" cy="18" r="2.5"/><path d="M8 7.5L20 18M20 6L8 16.5"/>',
		'sun'        => '<circle cx="12" cy="12" r="4.2"/><path d="M12 2v2.4M12 19.6V22M2 12h2.4M19.6 12H22M4.9 4.9l1.7 1.7M17.4 17.4l1.7 1.7M19.1 4.9l-1.7 1.7M6.6 17.4l-1.7 1.7"/>',
		'moon'       => '<path d="M21 13.2A8.6 8.6 0 1110.8 3a7 7 0 1010.2 10.2z"/>',
		'razor'      => '<path d="M4 4h9a3 3 0 013 3v2H7a3 3 0 01-3-3V4z"/><path d="M13 9l4 11"/><path d="M15.5 15.5h4"/>',
		'beard'      => '<path d="M5 4v6c0 5 3 10 7 10s7-5 7-10V4"/><path d="M9 9h.01M15 9h.01"/><path d="M10 13c1.2.9 2.8.9 4 0"/>',
		'towel'      => '<rect x="3" y="6" width="18" height="12" rx="3"/><path d="M8 6v12M16 6v12"/><path d="M11 3.5c.8.8.8 1.7 0 2.5M14 3.5c.8.8.8 1.7 0 2.5"/>',
		'dryer'      => '<path d="M3 8a5 5 0 015-5h6a5 5 0 010 10H8a5 5 0 01-5-5z"/><path d="M10 13v5a3 3 0 003 3"/><path d="M6 8h.01"/>',
		'lotus'      => '<path d="M12 20c-4 0-7-2.5-8-6 2.5-.5 4.5 0 6 1"/><path d="M12 20c4 0 7-2.5 8-6-2.5-.5-4.5 0-6 1"/><path d="M12 20c-2.5-2-4-4.5-4-7s1.5-5.5 4-7c2.5 1.5 4 4.5 4 7s-1.5 5-4 7z"/>',
		'stone'      => '<ellipse cx="12" cy="16" rx="8" ry="4"/><ellipse cx="12" cy="11" rx="6" ry="3"/><ellipse cx="12" cy="7" rx="4" ry="2"/>',
		'syringe'    => '<path d="M14 4l6 6"/><path d="M17 7l-9 9-4 1 1-4 9-9z"/><path d="M11 9l4 4"/>',
		'stethoscope'=> '<path d="M5 3v5a4 4 0 008 0V3"/><path d="M9 15v1a5 5 0 0010 0v-2"/><circle cx="19" cy="12" r="2"/><path d="M5 3H3M13 3h-2"/>',
		'shield'     => '<path d="M12 3l8 3v6c0 4.5-3.2 8.3-8 9-4.8-.7-8-4.5-8-9V6l8-3z"/><path d="M9 12l2 2 4-4"/>',
		'lash'       => '<path d="M2 14c3-5 7-7 10-7s7 2 10 7"/><path d="M4 15l-1.5 3M8 12l-.8 3.6M12 11v4M16 12l.8 3.6M20 15l1.5 3"/>',
		'brow'       => '<path d="M3 13c3-4 7-6 11-6s6 1.5 7 3"/><path d="M5 12.5l2 2M9 10l1.5 2.5M13.5 9l1 2.5M17.5 9.5l.8 2"/>',
		'lips'       => '<path d="M12 9c2-2 4.5-2.5 6.5-1.5C21 8.7 21.5 11 20 13c-2.5 3.2-5.5 5-8 5s-5.5-1.8-8-5c-1.5-2-1-4.3 1.5-5.5C7.5 6.5 10 7 12 9z"/><path d="M4 10.5h16"/>',
		'brush'      => '<path d="M7 21c-2 0-3-1.5-3-3 0-1.6 1.2-2 2-3l3 3c-1 .8-1.4 3-2 3z"/><path d="M9 18L6 15l9-9a2.1 2.1 0 013 3l-9 9z"/>',
		'mirror'     => '<ellipse cx="12" cy="9" rx="6" ry="7"/><path d="M12 16v5M9 21h6"/>',
		'calendar'   => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/><path d="M8 14h3v3H8z"/>',
		'clock'      => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'phone'      => '<path d="M6 3h4l2 5-2.5 1.5a12 12 0 005 5L16 12l5 2v4a2 2 0 01-2.2 2A17 17 0 014 5.2 2 2 0 016 3z"/>',
		'mobile'     => '<rect x="7" y="2" width="10" height="20" rx="2.5"/><path d="M11 18h2"/>',
		'message'    => '<path d="M21 12a8 8 0 01-11.6 7.1L4 20l1-4.4A8 8 0 1121 12z"/><path d="M8 11h.01M12 11h.01M16 11h.01"/>',
		'mail'       => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
		'pin'        => '<path d="M12 21s7-6 7-11a7 7 0 10-14 0c0 5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
		'user'       => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0116 0"/>',
		'users'      => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20a7 7 0 0114 0"/><path d="M16 5.2a3.5 3.5 0 010 6.6M17 20h5a6 6 0 00-4-5.7"/>',
		'star'       => '<path d="M12 3.5l2.6 5.4 5.9.8-4.3 4.1 1 5.9-5.2-2.8-5.2 2.8 1-5.9L3.5 9.7l5.9-.8L12 3.5z"/>',
		'heart'      => '<path d="M12 20s-7-4.3-7-9.3A4.2 4.2 0 0112 8a4.2 4.2 0 017 2.7c0 5-7 9.3-7 9.3z"/>',
		'wallet'     => '<rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10h18"/><circle cx="17" cy="14" r="1.3"/>',
		'gift'       => '<rect x="3" y="9" width="18" height="12" rx="2"/><path d="M3 13h18M12 9v12"/><path d="M12 9S9.5 4 7.5 5.5 9 9 12 9zM12 9s2.5-5 4.5-3.5S15 9 12 9z"/>',
		'check'      => '<circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.5 2.5L16 9.5"/>',
		'close'      => '<path d="M6 6l12 12M18 6L6 18"/>',
		'arrow'      => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'chevron'    => '<path d="M9 6l6 6-6 6"/>',
		'grid'       => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
		'settings'   => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.6 1.6 0 00.3 1.8l.1.1a2 2 0 11-2.8 2.8l-.1-.1a1.6 1.6 0 00-2.7 1.1V21a2 2 0 11-4 0v-.1A1.6 1.6 0 006 19.4l-.1.1a2 2 0 11-2.8-2.8l.1-.1A1.6 1.6 0 003 13.9H3a2 2 0 110-4h.1A1.6 1.6 0 004.6 6l-.1-.1a2 2 0 112.8-2.8l.1.1a1.6 1.6 0 002.7-1.1V2a2 2 0 114 0v.1a1.6 1.6 0 002.7 1.1l.1-.1a2 2 0 112.8 2.8l-.1.1a1.6 1.6 0 001.1 2.7H21a2 2 0 110 4h-.1a1.6 1.6 0 00-1.5 1.3z"/>',
		'logout'     => '<path d="M15 4h3a2 2 0 012 2v12a2 2 0 01-2 2h-3"/><path d="M10 8l-4 4 4 4M6 12h10"/>',
		'award'      => '<circle cx="12" cy="9" r="5"/><path d="M8.5 13.5L7 21l5-2.5L17 21l-1.5-7.5"/>',
		'leaf'       => '<path d="M4 20c0-8 5-14 16-14 0 9-5 14-12 14H4z"/><path d="M4 20c4-6 8-8 12-9"/>',
		'droplet'    => '<path d="M12 3s6 6.2 6 10a6 6 0 11-12 0c0-3.8 6-10 6-10z"/>',
		'flame'      => '<path d="M12 22a6 6 0 006-6c0-4-4-5-4-9 0 0-4 1.5-4 5 0 1.5-2 1.5-2 4a6 6 0 006 6z"/>',
		'camera'     => '<rect x="3" y="7" width="18" height="13" rx="3"/><circle cx="12" cy="13.5" r="3.5"/><path d="M9 7l1.5-3h3L15 7"/>',
		'play'       => '<circle cx="12" cy="12" r="9"/><path d="M10 8.5l6 3.5-6 3.5v-7z"/>',
		'quote'      => '<path d="M9 6c-3 1.5-5 4-5 8 0 2.5 1.5 4 3.5 4S11 16.5 11 14.5 9.5 11 7.5 11c-.6 0-1 .1-1.4.3C6.6 9.6 7.6 8.2 9.6 7L9 6z"/><path d="M20 6c-3 1.5-5 4-5 8 0 2.5 1.5 4 3.5 4S22 16.5 22 14.5 20.5 11 18.5 11c-.6 0-1 .1-1.4.3.5-1.7 1.5-3.1 3.5-4.3L20 6z"/>',
		'ticket'     => '<path d="M3 9V7a2 2 0 012-2h14a2 2 0 012 2v2a3 3 0 000 6v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2a3 3 0 000-6z"/><path d="M12 6v12"/>',
		'bell'       => '<path d="M6 9a6 6 0 1112 0c0 5 2 6 2 6H4s2-1 2-6z"/><path d="M10 19a2 2 0 004 0"/>',
		'chart'      => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
		'search'     => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>',
		'instagram'  => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/>',
		'whatsapp'   => '<path d="M21 11.5a8.5 8.5 0 01-12.6 7.4L3 20.5l1.7-5.2A8.5 8.5 0 1121 11.5z"/><path d="M8.8 9c.3-.7 1.4-.6 1.7.1l.5 1.1-.7.9c.5 1 1.3 1.7 2.3 2.2l.9-.7 1.1.5c.7.3.8 1.4.1 1.7-1.6.7-5.9-2.2-5.9-5.8z"/>',
		'telegram'   => '<path d="M21 4L3 11l5 2 2 6 3-4 5 4 3-15z"/><path d="M8 13l10-7-7 9"/>',
		'facebook'   => '<path d="M14 8h3V4h-3a4 4 0 00-4 4v2H7v4h3v8h4v-8h3l1-4h-4V8.8c0-.5.4-.8 1-.8z"/>',
		'youtube'    => '<rect x="2" y="5" width="20" height="14" rx="4"/><path d="M10 9l5 3-5 3V9z"/>',
		'pinterest'  => '<circle cx="12" cy="12" r="9"/><path d="M9.5 20l2-7"/><path d="M8.5 11c0-2.2 1.8-4 4.2-4 2 0 3.5 1.3 3.5 3.3 0 2.4-1.4 4.2-3.2 4.2-1 0-1.8-.8-1.5-1.8"/>',
	);
}

/**
 * Format a price with the configured currency.
 */
function beautia_price( $amount ) {
	$symbol   = beautia_get_option( 'currency_symbol', __( 'Toman', 'beautia' ) );
	$position = beautia_get_option( 'currency_position', 'after' );
	$value    = beautia_num( $amount );
	return 'before' === $position ? $symbol . ' ' . $value : $value . ' ' . $symbol;
}

/**
 * Duration in a human readable format.
 */
function beautia_duration( $minutes ) {
	$minutes = (int) $minutes;
	if ( $minutes < 60 ) {
		/* translators: %d minutes */
		$out = sprintf( _n( '%d min', '%d min', $minutes, 'beautia' ), $minutes );
	} else {
		$hours = floor( $minutes / 60 );
		$rest  = $minutes % 60;
		/* translators: 1: hours 2: minutes */
		$out = $rest ? sprintf( __( '%1$dh %2$dm', 'beautia' ), $hours, $rest ) : sprintf( __( '%dh', 'beautia' ), $hours );
	}
	return beautia_use_persian_digits() ? beautia_en_to_fa_digits( $out ) : $out;
}

/* -------------------------------------------------------------------------
 * Mobile / phone helpers (Iran-friendly, but international tolerant)
 * ---------------------------------------------------------------------- */

/**
 * Convert Persian/Arabic digits to latin digits.
 */
function beautia_fa_to_en_digits( $string ) {
	$fa = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' );
	$ar = array( '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩' );
	$en = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
	return str_replace( $ar, $en, str_replace( $fa, $en, (string) $string ) );
}

/**
 * Normalise a mobile number to a storage format.
 */
function beautia_normalize_mobile( $mobile ) {
	$mobile = beautia_fa_to_en_digits( trim( (string) $mobile ) );
	$mobile = preg_replace( '/[\s\-\(\)\.]/', '', $mobile );

	if ( 'ir' === beautia_get_option( 'phone_region', 'ir' ) ) {
		$mobile = preg_replace( '/^(\+98|0098|98)/', '0', $mobile );
		if ( preg_match( '/^9\d{9}$/', $mobile ) ) {
			$mobile = '0' . $mobile;
		}
	}
	return $mobile;
}

/**
 * Validate a mobile number.
 */
function beautia_is_valid_mobile( $mobile ) {
	$mobile = beautia_normalize_mobile( $mobile );
	if ( 'ir' === beautia_get_option( 'phone_region', 'ir' ) ) {
		return (bool) preg_match( '/^09\d{9}$/', $mobile );
	}
	return (bool) preg_match( '/^\+?\d{8,15}$/', $mobile );
}

/**
 * Mask a mobile for display: 0912***4567.
 */
function beautia_mask_mobile( $mobile ) {
	$mobile = beautia_normalize_mobile( $mobile );
	if ( strlen( $mobile ) < 7 ) {
		return $mobile;
	}
	return substr( $mobile, 0, 4 ) . str_repeat( '*', 3 ) . substr( $mobile, -4 );
}

/**
 * Visitor IP (privacy friendly, only used for OTP rate limiting).
 */
function beautia_get_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
	return $ip;
}

/* -------------------------------------------------------------------------
 * Jalali (Shamsi) date support — zero dependency
 * ---------------------------------------------------------------------- */

/**
 * Gregorian to Jalali.
 */
function beautia_gregorian_to_jalali( $gy, $gm, $gd ) {
	$g_d_m = array( 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334 );
	$gy2   = ( $gm > 2 ) ? ( $gy + 1 ) : $gy;
	$days  = 355666 + ( 365 * $gy ) + ( (int) ( ( $gy2 + 3 ) / 4 ) ) - ( (int) ( ( $gy2 + 99 ) / 100 ) )
		+ ( (int) ( ( $gy2 + 399 ) / 400 ) ) + $gd + $g_d_m[ $gm - 1 ];
	$jy    = -1595 + ( 33 * ( (int) ( $days / 12053 ) ) );
	$days %= 12053;
	$jy   += 4 * ( (int) ( $days / 1461 ) );
	$days %= 1461;
	if ( $days > 365 ) {
		$jy   += (int) ( ( $days - 1 ) / 365 );
		$days  = ( $days - 1 ) % 365;
	}
	if ( $days < 186 ) {
		$jm = 1 + (int) ( $days / 31 );
		$jd = 1 + ( $days % 31 );
	} else {
		$jm = 7 + (int) ( ( $days - 186 ) / 30 );
		$jd = 1 + ( ( $days - 186 ) % 30 );
	}
	return array( $jy, $jm, $jd );
}

/**
 * Jalali month names.
 */
function beautia_jalali_months() {
	return array( 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند' );
}

/**
 * Format a MySQL date/datetime respecting the configured calendar.
 *
 * @param string $datetime Y-m-d or Y-m-d H:i:s.
 * @param bool   $withTime Include the time part.
 */
function beautia_format_date( $datetime, $withTime = false ) {
	$ts = strtotime( $datetime );
	if ( ! $ts ) {
		return '';
	}
	if ( 'jalali' === beautia_get_option( 'calendar_type', 'jalali' ) ) {
		list( $jy, $jm, $jd ) = beautia_gregorian_to_jalali( (int) gmdate( 'Y', $ts ), (int) gmdate( 'n', $ts ), (int) gmdate( 'j', $ts ) );
		$months               = beautia_jalali_months();
		$out                  = sprintf( '%s %s %s', $jd, $months[ $jm - 1 ], $jy );
		if ( $withTime ) {
			$out .= ' - ' . gmdate( 'H:i', $ts );
		}
		return beautia_use_persian_digits() ? beautia_en_to_fa_digits( $out ) : $out;
	}
	return $withTime ? date_i18n( 'j M Y H:i', $ts ) : date_i18n( 'j M Y', $ts );
}

/**
 * Placeholder image URL for demo content.
 */
function beautia_img( $file, $fallback = '' ) {
	// Give the lash studio its own close-up visual instead of sharing the
	// makeup campaign portrait; the two demos should read differently at once.
	if ( 'glam-portrait.jpg' === $file && function_exists( 'beautia_demo_context' ) && 'lashes' === beautia_demo_context() ) {
		$file = 'hero-lashes.jpg';
	}
	// Generated editorial assets are shipped as optimized JPEGs. Keep legacy
	// .png references backwards compatible while avoiding multi-megabyte files.
	if ( $file && '.png' === strtolower( substr( $file, -4 ) ) ) {
		$optimized = substr( $file, 0, -4 ) . '.jpg';
		if ( file_exists( BEAUTIA_DIR . 'assets/img/' . $optimized ) ) {
			$file = $optimized;
		}
	}
	if ( $file && ! file_exists( BEAUTIA_DIR . 'assets/img/' . $file ) && $fallback ) {
		$file = $fallback;
	}
	return BEAUTIA_URI . 'assets/img/' . $file;
}

/**
 * Default hero copy for each bundled demo.
 */
function beautia_demo_hero( $demo = '' ) {
	$demo = $demo ? $demo : beautia_get_active_demo();
	$all  = array(
		'nails'  => array(
			'eyebrow' => __( 'Nail artistry, refined', 'beautia' ),
			'title'   => __( 'Nails that speak<br/>before you do', 'beautia' ),
			'text'    => __( 'A calm studio, sterilised tools and artists obsessed with shape. Book in under a minute — all you need is your mobile number.', 'beautia' ),
		),
		'clinic' => array(
			'eyebrow' => __( 'Physician-led aesthetics', 'beautia' ),
			'title'   => __( 'Science first,<br/>beauty always', 'beautia' ),
			'text'    => __( 'Evidence-based treatments, hospital-grade hygiene and honest advice. Reserve your consultation online in a minute.', 'beautia' ),
		),
		'hair'   => array(
			'eyebrow' => __( 'Colour & cutting house', 'beautia' ),
			'title'   => __( 'Hair with<br/>an attitude', 'beautia' ),
			'text'    => __( 'Balayage, precision cutting and treatments that keep your hair healthy between visits.', 'beautia' ),
		),
		'spa'    => array(
			'eyebrow' => __( 'Spa & wellness', 'beautia' ),
			'title'   => __( 'Breathe out.<br/>You have arrived', 'beautia' ),
			'text'    => __( 'Slow treatments, natural oils and a quiet room away from the city. Reserve your escape online.', 'beautia' ),
		),
		'lashes' => array(
			'eyebrow' => __( 'Brows & lashes bar', 'beautia' ),
			'title'   => __( 'Wake up<br/>ready', 'beautia' ),
			'text'    => __( 'Hand-mapped lash sets and brows that finally behave. Three-week refills, always on time.', 'beautia' ),
		),
		'makeup' => array(
			'eyebrow' => __( 'Makeup artist & academy', 'beautia' ),
			'title'   => __( 'Your face,<br/>only louder', 'beautia' ),
			'text'    => __( 'Bridal, editorial and camera-ready makeup — plus a certified academy for the next generation of artists.', 'beautia' ),
		),
		'barber' => array(
			'eyebrow' => __( 'Barbershop since 2013', 'beautia' ),
			'title'   => __( 'Sharp cuts,<br/>hot towels', 'beautia' ),
			'text'    => __( 'Classic cuts, clean fades and a straight-razor shave. Book your chair from your phone — no queue, no waiting.', 'beautia' ),
		),
	);
	return isset( $all[ $demo ] ) ? $all[ $demo ] : $all['nails'];
}

/**
 * Escaped, kses-safe SVG output allowance.
 */
function beautia_kses_svg() {
	return array_merge(
		wp_kses_allowed_html( 'post' ),
		array(
			'svg'      => array( 'class' => true, 'width' => true, 'height' => true, 'viewbox' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'aria-hidden' => true, 'focusable' => true ),
			'path'     => array( 'd' => true, 'fill' => true, 'stroke' => true ),
			'circle'   => array( 'cx' => true, 'cy' => true, 'r' => true ),
			'ellipse'  => array( 'cx' => true, 'cy' => true, 'rx' => true, 'ry' => true ),
			'rect'     => array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true ),
		)
	);
}
