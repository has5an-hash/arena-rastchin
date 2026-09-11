<?php
/**
 * Gutenberg support: block patterns, block styles and a block category so the
 * theme sections can be composed in the editor without a page builder.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

/** Custom block category. */
function beautia_block_category( $categories ) {
	array_unshift(
		$categories,
		array(
			'slug'  => 'beautia',
			'title' => __( 'Beautia', 'beautia' ),
			'icon'  => 'heart',
		)
	);
	return $categories;
}
add_filter( 'block_categories_all', 'beautia_block_category' );

/** Register patterns built from the theme shortcodes. */
function beautia_register_patterns() {
	if ( ! function_exists( 'register_block_pattern_category' ) ) {
		return;
	}
	register_block_pattern_category( 'beautia', array( 'label' => __( 'Beautia', 'beautia' ) ) );

	$patterns = array(
		'hero'         => array( __( 'Hero with quick booking', 'beautia' ), '[beautia_hero]' ),
		'services'     => array( __( 'Services grid', 'beautia' ), '[beautia_services count="6"]' ),
		'portfolio'    => array( __( 'Filterable portfolio', 'beautia' ), '[beautia_portfolio count="9"]' ),
		'team'         => array( __( 'Team members', 'beautia' ), '[beautia_team count="4"]' ),
		'pricing'      => array( __( 'Price menu', 'beautia' ), '[beautia_pricing]' ),
		'testimonials' => array( __( 'Testimonial slider', 'beautia' ), '[beautia_testimonials]' ),
		'stats'        => array( __( 'Animated counters', 'beautia' ), '[beautia_stats]' ),
		'features'     => array( __( 'Feature list', 'beautia' ), '[beautia_features]' ),
		'beforeafter'  => array( __( 'Before / after sliders', 'beautia' ), '[beautia_before_after]' ),
		'blog'         => array( __( 'Journal teaser', 'beautia' ), '[beautia_blog count="3"]' ),
		'about'        => array( __( 'About split with checklist', 'beautia' ), '[beautia_about]' ),
		'steps'        => array( __( 'How it works — three steps', 'beautia' ), '[beautia_steps]' ),
		'faq'          => array( __( 'FAQ accordion', 'beautia' ), '[beautia_faq]' ),
		'hours'        => array( __( 'Opening hours table', 'beautia' ), '[beautia_hours]' ),
		'track'        => array( __( 'Track my appointment', 'beautia' ), '[beautia_track]' ),
		'cta'          => array( __( 'Booking CTA band', 'beautia' ), '[beautia_cta]' ),
		'booking'      => array( __( 'Booking wizard', 'beautia' ), '[beautia_booking]' ),
		'landing'      => array(
			__( 'Full salon landing page', 'beautia' ),
			"[beautia_hero]\n[beautia_about]\n[beautia_services count=\"6\"]\n[beautia_features]\n[beautia_portfolio count=\"9\"]\n[beautia_stats]\n[beautia_team count=\"4\"]\n[beautia_pricing]\n[beautia_steps]\n[beautia_testimonials]\n[beautia_faq]\n[beautia_blog count=\"3\"]\n[beautia_cta]",
		),
	);

	foreach ( $patterns as $slug => $pattern ) {
		register_block_pattern(
			'beautia/' . $slug,
			array(
				'title'      => $pattern[0],
				'categories' => array( 'beautia' ),
				'content'    => '<!-- wp:shortcode -->' . $pattern[1] . '<!-- /wp:shortcode -->',
			)
		);
	}
}
add_action( 'init', 'beautia_register_patterns' );

/** Extra block styles. */
function beautia_block_styles() {
	if ( ! function_exists( 'register_block_style' ) ) {
		return;
	}
	register_block_style( 'core/button', array( 'name' => 'beautia-pill', 'label' => __( 'Beautia pill', 'beautia' ) ) );
	register_block_style( 'core/image', array( 'name' => 'beautia-arch', 'label' => __( 'Arch frame', 'beautia' ) ) );
	register_block_style( 'core/quote', array( 'name' => 'beautia-quote', 'label' => __( 'Beautia quote card', 'beautia' ) ) );
	register_block_style( 'core/group', array( 'name' => 'beautia-soft', 'label' => __( 'Soft surface', 'beautia' ) ) );
}
add_action( 'init', 'beautia_block_styles' );
