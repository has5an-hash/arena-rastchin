<?php
/**
 * Blog index.
 *
 * @package Beautia
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'WP_USE_THEMES', true );
	require __DIR__ . '/wordpress/wp-blog-header.php';
	return;
}

get_header();
?>
<div class="page-hero small">
	<div class="container">
		<h1><?php echo esc_html( is_home() && ! is_front_page() ? get_the_title( get_option( 'page_for_posts' ) ) : __( 'Journal', 'beautia' ) ); ?></h1>
		<p><?php esc_html_e( 'Guides, aftercare tips and behind the scenes of the studio.', 'beautia' ); ?></p>
	</div>
</div>

<div class="container section">
	<div class="content-sidebar">
		<div class="content-main">
			<div class="grid grid-2">
				<?php
				if ( have_posts() ) :
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/content/card' );
					endwhile;
				else :
					get_template_part( 'template-parts/content/none' );
				endif;
				?>
			</div>
			<?php
			the_posts_pagination(
				array(
					'prev_text' => beautia_get_icon( 'chevron', 18 ),
					'next_text' => beautia_get_icon( 'chevron', 18 ),
				)
			);
			?>
		</div>
		<?php get_sidebar(); ?>
	</div>
</div>
<?php
get_footer();
