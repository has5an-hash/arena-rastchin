<?php
/**
 * Beautia theme bootstrap.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

define( 'BEAUTIA_VERSION', '4.0.0' );
define( 'BEAUTIA_DIR', trailingslashit( get_template_directory() ) );
define( 'BEAUTIA_URI', trailingslashit( get_template_directory_uri() ) );

/** Cache-bust project assets when their contents change. */
function beautia_asset_version( $relative_path ) {
	$file = BEAUTIA_DIR . ltrim( $relative_path, '/\\' );
	return is_file( $file ) ? BEAUTIA_VERSION . '.' . filemtime( $file ) : BEAUTIA_VERSION;
}

/**
 * Theme setup.
 */
function beautia_setup() {
	load_theme_textdomain( 'beautia', BEAUTIA_DIR . 'languages' );

	/*
	 * Beautia is a Persian-first theme: even when WordPress itself runs in
	 * English we still load the bundled fa_IR catalogue so the front end reads
	 * Persian out of the box. Set the "Force Persian" option to 0 (Beautia →
	 * Settings → General) to fall back to the site locale.
	 */
	if ( 0 !== strpos( get_locale(), 'fa' ) && beautia_get_option( 'force_persian', 1 ) ) {
		load_textdomain( 'beautia', BEAUTIA_DIR . 'languages/fa_IR.mo' );
	}

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 90,
			'width'       => 260,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_image_size( 'beautia-card', 720, 540, true );
	add_image_size( 'beautia-portrait', 720, 960, true );
	add_image_size( 'beautia-wide', 1600, 900, true );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'beautia' ),
			'footer'  => __( 'Footer Menu', 'beautia' ),
			'account' => __( 'Customer Panel Menu', 'beautia' ),
		)
	);

	add_editor_style( 'assets/css/editor.css' );
}
add_action( 'after_setup_theme', 'beautia_setup' );

/**
 * Content width.
 */
function beautia_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'beautia_content_width', 1240 );
}
add_action( 'after_setup_theme', 'beautia_content_width', 0 );

/**
 * Sidebars.
 */
function beautia_widgets_init() {
	$areas = array(
		'sidebar-main' => __( 'Main Sidebar', 'beautia' ),
		'footer-1'     => __( 'Footer Column 1', 'beautia' ),
		'footer-2'     => __( 'Footer Column 2', 'beautia' ),
		'footer-3'     => __( 'Footer Column 3', 'beautia' ),
		'footer-4'     => __( 'Footer Column 4', 'beautia' ),
	);
	foreach ( $areas as $id => $name ) {
		register_sidebar(
			array(
				'name'          => $name,
				'id'            => $id,
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h4 class="widget-title">',
				'after_title'   => '</h4>',
			)
		);
	}
}
add_action( 'widgets_init', 'beautia_widgets_init' );

/**
 * Front-end assets.
 */
function beautia_scripts() {
	$demo = beautia_get_active_demo();

	wp_enqueue_style( 'beautia-fonts', BEAUTIA_URI . 'assets/css/fonts.css', array(), beautia_asset_version( 'assets/css/fonts.css' ) );
	wp_enqueue_style( 'beautia-icons', BEAUTIA_URI . 'assets/css/icons.css', array(), BEAUTIA_VERSION );
	wp_enqueue_style( 'beautia-core', BEAUTIA_URI . 'assets/css/beautia.css', array( 'beautia-fonts' ), BEAUTIA_VERSION );
	wp_enqueue_style( 'beautia-demo', BEAUTIA_URI . 'assets/css/demos/' . $demo . '.css', array( 'beautia-core' ), BEAUTIA_VERSION );
	if ( beautia_is_rtl() ) {
		wp_enqueue_style( 'beautia-rtl', BEAUTIA_URI . 'assets/css/rtl.css', array( 'beautia-demo' ), BEAUTIA_VERSION );
	}

	// The polish layer refines depth, rhythm and typography on top of every skin.
	wp_enqueue_style( 'beautia-polish', BEAUTIA_URI . 'assets/css/polish.css', array( 'beautia-demo' ), beautia_asset_version( 'assets/css/polish.css' ) );

	if ( beautia_dark_mode_enabled() ) {
		wp_enqueue_style( 'beautia-dark', BEAUTIA_URI . 'assets/css/dark.css', array( 'beautia-polish' ), BEAUTIA_VERSION );
	}
	wp_enqueue_style( 'beautia-style', get_stylesheet_uri(), array( 'beautia-polish' ), BEAUTIA_VERSION );

	wp_add_inline_style( 'beautia-demo', beautia_customizer_css() );

	wp_enqueue_script( 'beautia-app', BEAUTIA_URI . 'assets/js/beautia.js', array(), beautia_asset_version( 'assets/js/beautia.js' ), true );
	wp_enqueue_script( 'beautia-booking', BEAUTIA_URI . 'assets/js/booking.js', array( 'beautia-app' ), BEAUTIA_VERSION, true );
	wp_enqueue_script( 'beautia-auth', BEAUTIA_URI . 'assets/js/auth.js', array( 'beautia-app' ), BEAUTIA_VERSION, true );

	if ( beautia_get_option( 'booking_drawer', 1 ) ) {
		wp_enqueue_script( 'beautia-drawer', BEAUTIA_URI . 'assets/js/drawer.js', array( 'beautia-app' ), BEAUTIA_VERSION, true );
	}

	wp_localize_script(
		'beautia-app',
		'BeautiaData',
		array(
			'demo'       => beautia_demo_context(),
			'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
			'restUrl'    => esc_url_raw( rest_url( 'beautia/v1/' ) ),
			'nonce'      => wp_create_nonce( 'beautia_nonce' ),
			'restNonce'  => wp_create_nonce( 'wp_rest' ),
			'isLoggedIn' => is_user_logged_in(),
			'accountUrl' => beautia_get_page_url( 'account' ),
			'loginUrl'   => beautia_get_page_url( 'login' ),
			'currency'   => beautia_get_option( 'currency_symbol', __( 'Toman', 'beautia' ) ),
			'weekStart'  => (int) get_option( 'start_of_week', 6 ),
			'calendar'   => beautia_get_option( 'calendar_type', 'jalali' ),
			'rtl'        => beautia_is_rtl(),
			'darkMode'   => beautia_dark_mode_enabled(),
			'darkDefault'=> beautia_get_option( 'dark_default', 'auto' ),
			'faDigits'   => beautia_use_persian_digits(),
			'i18n'       => array(
				'loading'      => __( 'Loading…', 'beautia' ),
				'noSlots'      => __( 'No free time slots on this day.', 'beautia' ),
				'pickService'  => __( 'Please choose a service first.', 'beautia' ),
				'pickSlot'     => __( 'Please choose a time slot.', 'beautia' ),
				'otpSent'      => __( 'Verification code was sent to your mobile.', 'beautia' ),
				'wrongCode'    => __( 'The code is not correct.', 'beautia' ),
				'resendIn'     => __( 'Resend code in %s seconds', 'beautia' ),
				'resend'       => __( 'Resend code', 'beautia' ),
				'genericError' => __( 'Something went wrong, please try again.', 'beautia' ),
				'copied'       => __( 'Copied!', 'beautia' ),
				'waitlistLead' => __( 'This day is fully booked. Leave your number and we will text you the moment a slot opens.', 'beautia' ),
				'waitlistJoin' => __( 'Notify me', 'beautia' ),
				'mobilePlaceholder' => __( '0912 000 0000', 'beautia' ),
				'pickStars'    => __( 'Please pick a score from one to five stars.', 'beautia' ),
			),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'beautia_scripts' );

/** Fast, demo-scoped live search for the header command palette. */
function beautia_live_search() {
	check_ajax_referer( 'beautia_nonce', 'nonce' );
	$query = sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) );
	if ( beautia_strlen( $query ) < 2 ) wp_send_json_success( array() );
	$args = array( 's' => $query, 'post_status' => 'publish', 'posts_per_page' => 8, 'post_type' => array( 'beautia_service', 'beautia_portfolio', 'post', 'page' ), 'orderby' => 'relevance', 'order' => 'DESC' );
	$demo = beautia_demo_context();
	if ( $demo ) $args['meta_query'] = array( array( 'key' => '_beautia_demo', 'value' => $demo ) );
	$labels = array( 'beautia_service' => 'خدمت', 'beautia_portfolio' => 'نمونه‌کار', 'post' => 'مجله', 'page' => 'صفحه' );
	$items = array();
	foreach ( get_posts( $args ) as $post ) $items[] = array( 'title' => get_the_title( $post ), 'url' => get_permalink( $post ), 'type' => $labels[ $post->post_type ] ?? 'محتوا', 'image' => get_the_post_thumbnail_url( $post, 'thumbnail' ) ?: '' );
	wp_send_json_success( $items );
}
add_action( 'wp_ajax_beautia_live_search', 'beautia_live_search' );
add_action( 'wp_ajax_nopriv_beautia_live_search', 'beautia_live_search' );

/**
 * Admin assets.
 */
function beautia_admin_scripts( $hook ) {
	wp_enqueue_style( 'beautia-admin', BEAUTIA_URI . 'assets/css/admin.css', array(), BEAUTIA_VERSION );
	wp_enqueue_script( 'beautia-admin', BEAUTIA_URI . 'assets/js/admin.js', array( 'jquery' ), BEAUTIA_VERSION, true );
	wp_localize_script(
		'beautia-admin',
		'BeautiaAdmin',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'beautia_admin' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'beautia_admin_scripts' );

/** Body classes. */
function beautia_body_classes( $classes ) {
	$classes[] = 'beautia';
	$classes[] = 'demo-' . beautia_get_active_demo();
	$classes[] = 'header-' . beautia_get_option( 'header_style', 'classic' );
	if ( beautia_get_option( 'enable_animations', true ) ) {
		$classes[] = 'has-anim';
	}
	if ( beautia_get_option( 'enable_cursor', false ) ) {
		$classes[] = 'has-cursor';
	}
	if ( ! is_active_sidebar( 'sidebar-main' ) ) {
		$classes[] = 'no-sidebar';
	}
	if ( beautia_is_rtl() ) {
		$classes[] = 'rtl';
	}
	if ( 'jalali' === beautia_get_option( 'calendar_type', 'jalali' ) ) {
		$classes[] = 'cal-jalali';
	}
	return $classes;
}
add_filter( 'body_class', 'beautia_body_classes' );

/* -------------------------------------------------------------------------
 * Includes
 * ---------------------------------------------------------------------- */
require_once BEAUTIA_DIR . 'inc/helpers.php';
require_once BEAUTIA_DIR . 'inc/showcase.php';
require_once BEAUTIA_DIR . 'inc/template-tags.php';
require_once BEAUTIA_DIR . 'inc/class-beautia-performance.php';
require_once BEAUTIA_DIR . 'inc/class-beautia-mega-menu.php';
require_once BEAUTIA_DIR . 'inc/class-beautia-site-settings.php';
require_once BEAUTIA_DIR . 'inc/customizer.php';

/*
 * During the transition to the marketplace Core architecture the theme keeps
 * a bundled compatibility copy. In customer packages Beautia Core is shipped
 * separately and owns these data/business modules, so theme updates remain
 * presentation-only and never put appointments or content types at risk.
 */
if ( ! defined( 'BEAUTIA_CORE_VERSION' ) ) {
	require_once BEAUTIA_DIR . 'inc/class-beautia-cpt.php';
	require_once BEAUTIA_DIR . 'inc/class-beautia-metaboxes.php';
	require_once BEAUTIA_DIR . 'inc/class-beautia-sms.php';
	require_once BEAUTIA_DIR . 'inc/class-beautia-otp.php';
	require_once BEAUTIA_DIR . 'inc/class-beautia-booking.php';
	require_once BEAUTIA_DIR . 'inc/class-beautia-account.php';
	require_once BEAUTIA_DIR . 'inc/class-beautia-rest.php';
	require_once BEAUTIA_DIR . 'inc/class-beautia-admin.php';
	require_once BEAUTIA_DIR . 'inc/class-beautia-demo-importer.php';
	require_once BEAUTIA_DIR . 'inc/shortcodes.php';
	require_once BEAUTIA_DIR . 'inc/shortcodes-plus.php';
	require_once BEAUTIA_DIR . 'inc/blocks.php';
	require_once BEAUTIA_DIR . 'inc/class-beautia-elementor.php';
	require_once BEAUTIA_DIR . 'inc/live-chat.php';
}

/**
 * Boot singletons.
 */
function beautia_boot() {
	Beautia_CPT::instance();
	Beautia_Metaboxes::instance();
	Beautia_SMS::instance();
	Beautia_OTP::instance();
	Beautia_Booking::instance();
	Beautia_Account::instance();
	Beautia_REST::instance();
	Beautia_Admin::instance();
	Beautia_Demo_Importer::instance();
	Beautia_Elementor::instance();
}
add_action( 'after_setup_theme', 'beautia_boot', 5 );

/**
 * Install DB tables + pages on activation.
 */
function beautia_after_switch_theme() {
	Beautia_Booking::install();
	beautia_create_default_pages();
	flush_rewrite_rules();
	set_transient( 'beautia_activated', 1, 60 );
}
add_action( 'after_switch_theme', 'beautia_after_switch_theme' );

/**
 * Keep the schema in sync on upgrades.
 */
function beautia_maybe_upgrade() {
	if ( get_option( 'beautia_db_version' ) !== BEAUTIA_VERSION ) {
		Beautia_Booking::install();
		update_option( 'beautia_db_version', BEAUTIA_VERSION );
	}
}
add_action( 'admin_init', 'beautia_maybe_upgrade' );

/**
 * Performance & accessibility touches:
 * - a theme-colour meta so mobile browsers match the palette
 */
function beautia_head_extras() {
	printf( '<meta name="theme-color" content="%s" />' . "\n", esc_attr( beautia_accent_color() ) );
}
add_action( 'wp_head', 'beautia_head_extras', 2 );

/**
 * Add width/height and lazy loading to content images that are missing them,
 * so the layout does not jump while images load.
 */
function beautia_lazy_content_images( $content ) {
	if ( is_admin() || is_feed() || ! beautia_get_option( 'lazy_loading', 1 ) ) {
		return $content;
	}
	return str_replace( '<img ', '<img loading="lazy" decoding="async" ', str_replace( array( '<img loading="lazy" ', '<img decoding="async" ' ), '<img ', $content ) );
}
add_filter( 'the_content', 'beautia_lazy_content_images', 20 );
