<?php
/**
 * Default page.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$subtitle = get_post_meta( get_the_ID(), '_beautia_subtitle', true );
	?>
	<div class="page-hero<?php echo has_post_thumbnail() ? ' has-image' : ''; ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="page-hero-bg" data-parallax="0.12" style="background-image:url('<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'beautia-wide' ) ); ?>')"></div>
		<?php endif; ?>
		<div class="container">
			<h1 data-anim="fade-up"><?php the_title(); ?></h1>
			<?php if ( $subtitle ) : ?>
				<p data-anim="fade-up" data-delay="120"><?php echo esc_html( $subtitle ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<div class="container section">
		<div class="entry-content"><?php the_content(); ?></div>
	</div>
	<?php
endwhile;

get_footer();
