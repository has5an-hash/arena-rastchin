<?php
/**
 * Search results — grouped, with a fresh search box and quick links.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

get_header();

global $wp_query;
$found = (int) $wp_query->found_posts;
?>
<div class="page-hero small">
	<div class="container">
		<h1><?php printf( esc_html__( 'Search results for “%s”', 'beautia' ), esc_html( get_search_query() ) ); ?></h1>
		<p>
			<?php
			printf(
				esc_html( _n( '%s result found', '%s results found', $found, 'beautia' ) ),
				esc_html( beautia_num( $found ) )
			);
			?>
		</p>
		<div class="search-again"><?php get_search_form(); ?></div>
	</div>
</div>

<div class="container section">
	<?php if ( have_posts() ) : ?>
		<div class="grid grid-3">
			<?php
			while ( have_posts() ) :
				the_post();
				if ( 'beautia_service' === get_post_type() ) {
					beautia_service_card( get_the_ID() );
				} else {
					get_template_part( 'template-parts/content/card' );
				}
			endwhile;
			?>
		</div>
		<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
	<?php else : ?>
		<div class="search-empty center">
			<span class="err-mark"><?php beautia_icon( 'search', 44 ); ?></span>
			<h2><?php esc_html_e( 'Nothing matched your search', 'beautia' ); ?></h2>
			<p><?php esc_html_e( 'Try a different word, or jump straight to one of these:', 'beautia' ); ?></p>
			<div class="wizard-actions center">
				<a class="btn btn-primary" href="<?php echo esc_url( get_post_type_archive_link( 'beautia_service' ) ); ?>"><span><?php esc_html_e( 'Our services', 'beautia' ); ?></span></a>
				<a class="btn btn-ghost" href="<?php echo esc_url( beautia_get_page_url( 'booking' ) ); ?>"><span><?php esc_html_e( 'Book an appointment', 'beautia' ); ?></span></a>
				<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span><?php esc_html_e( 'Back home', 'beautia' ); ?></span></a>
			</div>
		</div>
	<?php endif; ?>
</div>
<?php
get_footer();
