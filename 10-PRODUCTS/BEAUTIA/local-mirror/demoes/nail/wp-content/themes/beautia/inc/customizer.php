<?php
/**
 * Customizer: demo switcher, colours, typography, header/footer, contact, social.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register settings.
 */
function beautia_customize_register( $wp_customize ) {

	$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

	/* ----------------------------- Demos ------------------------------- */
	$wp_customize->add_panel(
		'beautia_panel',
		array(
			'title'    => __( 'Beautia Theme', 'beautia' ),
			'priority' => 10,
		)
	);

	$wp_customize->add_section(
		'beautia_demo_section',
		array(
			'title' => __( 'Demo / Style', 'beautia' ),
			'panel' => 'beautia_panel',
		)
	);

	$choices = array();
	foreach ( beautia_get_demos() as $slug => $demo ) {
		$choices[ $slug ] = $demo['label'];
	}
	$wp_customize->add_setting( 'beautia_demo', array( 'default' => 'nails', 'sanitize_callback' => 'sanitize_key' ) );
	$wp_customize->add_control( 'beautia_demo', array( 'label' => __( 'Active demo', 'beautia' ), 'section' => 'beautia_demo_section', 'type' => 'select', 'choices' => $choices ) );

	/* ---------------------------- Colours ------------------------------ */
	$wp_customize->add_section( 'beautia_colors', array( 'title' => __( 'Colours', 'beautia' ), 'panel' => 'beautia_panel' ) );

	$colors = array(
		'accent'     => array( __( 'Accent', 'beautia' ), '' ),
		'accent_2'   => array( __( 'Secondary accent', 'beautia' ), '' ),
		'dark'       => array( __( 'Headings / dark', 'beautia' ), '' ),
		'body'       => array( __( 'Body text', 'beautia' ), '' ),
		'bg'         => array( __( 'Page background', 'beautia' ), '' ),
		'soft'       => array( __( 'Soft surface', 'beautia' ), '' ),
	);
	foreach ( $colors as $key => $data ) {
		$wp_customize->add_setting( 'beautia_color_' . $key, array( 'default' => $data[1], 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage' ) );
		$wp_customize->add_control(
			new WP_Customize_Color_Control( $wp_customize, 'beautia_color_' . $key, array( 'label' => $data[0], 'section' => 'beautia_colors' ) )
		);
	}

	/* -------------------------- Typography ----------------------------- */
	$wp_customize->add_section( 'beautia_typography', array( 'title' => __( 'Typography', 'beautia' ), 'panel' => 'beautia_panel' ) );

	$fonts = array(
		'display' => __( 'Display / headings font stack', 'beautia' ),
		'body'    => __( 'Body font stack', 'beautia' ),
	);
	foreach ( $fonts as $key => $label ) {
		$wp_customize->add_setting( 'beautia_font_' . $key, array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'beautia_font_' . $key, array( 'label' => $label, 'section' => 'beautia_typography', 'type' => 'text' ) );
	}
	$wp_customize->add_setting( 'beautia_base_size', array( 'default' => 17, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( 'beautia_base_size', array( 'label' => __( 'Base font size (px)', 'beautia' ), 'section' => 'beautia_typography', 'type' => 'number' ) );

	/* ---------------------------- Header ------------------------------- */
	$wp_customize->add_section( 'beautia_header', array( 'title' => __( 'Header', 'beautia' ), 'panel' => 'beautia_panel' ) );

	$wp_customize->add_setting( 'beautia_header_style', array( 'default' => 'classic', 'sanitize_callback' => 'sanitize_key' ) );
	$wp_customize->add_control(
		'beautia_header_style',
		array(
			'label'   => __( 'Header layout', 'beautia' ),
			'section' => 'beautia_header',
			'type'    => 'select',
			'choices' => array(
				'classic'     => __( 'Classic (logo left)', 'beautia' ),
				'centered'    => __( 'Centered logo', 'beautia' ),
				'split'       => __( 'Split navigation', 'beautia' ),
				'transparent' => __( 'Transparent over hero', 'beautia' ),
				'compact'     => __( 'Compact', 'beautia' ),
			),
		)
	);
	$wp_customize->add_setting( 'beautia_topbar', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control( 'beautia_topbar', array( 'label' => __( 'Show top bar', 'beautia' ), 'section' => 'beautia_header', 'type' => 'checkbox' ) );

	$wp_customize->add_setting( 'beautia_sticky_header', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control( 'beautia_sticky_header', array( 'label' => __( 'Sticky header', 'beautia' ), 'section' => 'beautia_header', 'type' => 'checkbox' ) );

	$wp_customize->add_setting( 'beautia_header_cta', array( 'default' => __( 'Book Now', 'beautia' ), 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'beautia_header_cta', array( 'label' => __( 'Header button label', 'beautia' ), 'section' => 'beautia_header', 'type' => 'text' ) );

	/* ------------------------ Contact & social ------------------------- */
	$wp_customize->add_section( 'beautia_contact', array( 'title' => __( 'Contact & Social', 'beautia' ), 'panel' => 'beautia_panel' ) );

	$contact = array(
		'phone'     => __( 'Phone', 'beautia' ),
		'address'   => __( 'Address', 'beautia' ),
		'email'     => __( 'E-mail', 'beautia' ),
		'hours'     => __( 'Opening hours line', 'beautia' ),
		'map'       => __( 'Google Maps embed URL', 'beautia' ),
		'instagram' => __( 'Instagram', 'beautia' ),
		'telegram'  => __( 'Telegram', 'beautia' ),
		'whatsapp'  => __( 'WhatsApp', 'beautia' ),
		'youtube'   => __( 'YouTube', 'beautia' ),
		'pinterest' => __( 'Pinterest', 'beautia' ),
	);
	foreach ( $contact as $key => $label ) {
		$wp_customize->add_setting( 'beautia_' . $key, array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'beautia_' . $key, array( 'label' => $label, 'section' => 'beautia_contact', 'type' => 'text' ) );
	}

	/* ---------------------------- Effects ------------------------------ */
	$wp_customize->add_section( 'beautia_effects', array( 'title' => __( 'Motion & Effects', 'beautia' ), 'panel' => 'beautia_panel' ) );

	$toggles = array(
		'enable_animations' => array( __( 'Scroll reveal animations', 'beautia' ), true ),
		'enable_cursor'     => array( __( 'Custom magnetic cursor', 'beautia' ), false ),
		'enable_parallax'   => array( __( 'Parallax images', 'beautia' ), true ),
		'enable_marquee'    => array( __( 'Marquee ribbons', 'beautia' ), true ),
		'enable_grain'      => array( __( 'Film grain overlay', 'beautia' ), false ),
		'enable_preloader'  => array( __( 'Preloader', 'beautia' ), true ),
		'enable_sticky_cta' => array( __( 'Mobile sticky booking bar', 'beautia' ), true ),
	);
	foreach ( $toggles as $key => $data ) {
		$wp_customize->add_setting( 'beautia_' . $key, array( 'default' => $data[1], 'sanitize_callback' => 'wp_validate_boolean' ) );
		$wp_customize->add_control( 'beautia_' . $key, array( 'label' => $data[0], 'section' => 'beautia_effects', 'type' => 'checkbox' ) );
	}

	/* ----------------------------- Footer ------------------------------ */
	$wp_customize->add_section( 'beautia_footer', array( 'title' => __( 'Footer', 'beautia' ), 'panel' => 'beautia_panel' ) );
	$wp_customize->add_setting( 'beautia_copyright', array( 'default' => '', 'sanitize_callback' => 'wp_kses_post' ) );
	$wp_customize->add_control( 'beautia_copyright', array( 'label' => __( 'Copyright text', 'beautia' ), 'section' => 'beautia_footer', 'type' => 'textarea' ) );
	$wp_customize->add_setting( 'beautia_footer_cta', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control( 'beautia_footer_cta', array( 'label' => __( 'Show booking CTA band', 'beautia' ), 'section' => 'beautia_footer', 'type' => 'checkbox' ) );
}
add_action( 'customize_register', 'beautia_customize_register' );

/**
 * Inline CSS variables from the customizer.
 */
function beautia_customizer_css() {
	$vars = array(
		'--c-accent'   => get_theme_mod( 'beautia_color_accent' ),
		'--c-accent-2' => get_theme_mod( 'beautia_color_accent_2' ),
		'--c-dark'     => get_theme_mod( 'beautia_color_dark' ),
		'--c-body'     => get_theme_mod( 'beautia_color_body' ),
		'--c-bg'       => get_theme_mod( 'beautia_color_bg' ),
		'--c-soft'     => get_theme_mod( 'beautia_color_soft' ),
		'--font-display' => get_theme_mod( 'beautia_font_display' ),
		'--font-body'    => get_theme_mod( 'beautia_font_body' ),
	);
	$size = (int) get_theme_mod( 'beautia_base_size', 17 );
	$css  = '';
	foreach ( $vars as $key => $value ) {
		if ( $value ) {
			$css .= $key . ':' . $value . ';';
		}
	}
	if ( $size && 17 !== $size ) {
		$css .= '--fs-base:' . $size . 'px;';
	}
	return $css ? ':root{' . $css . '}' : '';
}

/**
 * Customizer live preview script.
 */
function beautia_customize_preview_js() {
	wp_enqueue_script( 'beautia-customizer', BEAUTIA_URI . 'assets/js/customizer.js', array( 'customize-preview' ), BEAUTIA_VERSION, true );
}
add_action( 'customize_preview_init', 'beautia_customize_preview_js' );
