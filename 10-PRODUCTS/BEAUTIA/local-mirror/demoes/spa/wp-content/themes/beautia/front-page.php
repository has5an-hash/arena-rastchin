<?php
/**
 * Front page — full salon landing built from theme sections.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

if (get_option('beautia_showcase_enabled')) {
	require BEAUTIA_DIR . 'template-parts/collection-hub.php';
	return;
}

/* WordPress gives front-page.php precedence over a page template. Respect the
 * explicit Studio assignment used by standalone demos so their dynamic,
 * demo-specific composition and identity styles actually render. */
if ( 'page-templates/studio.php' === get_page_template_slug( get_queried_object_id() ) ) {
	require BEAUTIA_DIR . 'page-templates/studio.php';
	return;
}

get_header();

if ( have_posts() && get_post() && trim( get_post()->post_content ) ) {
	while ( have_posts() ) {
		the_post();
		the_content();
	}
} else {
	beautia_section_hero();
	beautia_marquee();
	beautia_section_services();
	beautia_section_features();
	beautia_section_portfolio();
	beautia_section_stats();
	beautia_section_team();
	beautia_section_pricing();
	beautia_section_before_after();
	beautia_section_testimonials();
	beautia_section_blog();
}

get_footer();
