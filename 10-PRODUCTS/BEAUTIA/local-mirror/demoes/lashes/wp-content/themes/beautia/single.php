<?php
/**
 * Single post.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'single-post' ); ?>>
		<div class="page-hero<?php echo has_post_thumbnail() ? ' has-image' : ''; ?>">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="page-hero-bg" data-parallax="0.12" style="background-image:url('<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'beautia-wide' ) ); ?>')"></div>
			<?php endif; ?>
			<div class="container">
				<span class="post-cats"><?php the_category( ' · ' ); ?></span>
				<h1 data-anim="fade-up"><?php the_title(); ?></h1>
				<div class="post-meta">
					<span><?php beautia_icon( 'user', 15 ); ?><?php the_author(); ?></span>
					<span><?php beautia_icon( 'calendar', 15 ); ?><?php echo esc_html( beautia_format_date( get_the_date( 'Y-m-d' ) ) ); ?></span>
					<span><?php beautia_icon( 'clock', 15 ); ?><?php printf( esc_html__( '%s min read', 'beautia' ), esc_html( beautia_num( beautia_reading_time() ) ) ); ?></span>
				</div>
			</div>
		</div>

		<div class="container section">
			<div class="content-sidebar">
				<div class="content-main">
					<div class="entry-content"><?php the_content(); ?></div>
					<?php if ( has_tag() ) : ?>
						<div class="entry-tags"><?php the_tags( '', '' ); ?></div>
					<?php endif; ?>
					<div class="entry-share">
						<span><?php beautia_icon( 'sparkle', 16 ); ?><?php esc_html_e( 'Share this article', 'beautia' ); ?></span>
						<?php foreach ( beautia_share_links() as $network => $link ) : ?>
							<?php if ( $link ) : ?>
								<a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener nofollow" aria-label="<?php echo esc_attr( $network ); ?>"><?php beautia_icon( $network, 18 ); ?></a>
							<?php endif; ?>
						<?php endforeach; ?>
						<button class="btn-link" type="button" data-copy="<?php the_permalink(); ?>"><?php esc_html_e( 'Copy link', 'beautia' ); ?></button>
					</div>

					<div class="author-box">
						<?php echo get_avatar( get_the_author_meta( 'ID' ), 78 ); ?>
						<div>
							<span class="eyebrow"><?php esc_html_e( 'Written by', 'beautia' ); ?></span>
							<h3><?php the_author(); ?></h3>
							<p><?php echo esc_html( get_the_author_meta( 'description' ) ); ?></p>
						</div>
					</div>

					<?php
					$related = new WP_Query(
						array(
							'post_type'      => 'post',
							'posts_per_page' => 3,
							'post__not_in'   => array( get_the_ID() ),
							'no_found_rows'  => true,
							'category__in'   => wp_get_post_categories( get_the_ID() ),
						)
					);
					if ( $related->have_posts() ) :
						?>
						<h2 class="block-title"><?php esc_html_e( 'Keep reading', 'beautia' ); ?></h2>
						<div class="grid grid-3 related-posts">
							<?php
							while ( $related->have_posts() ) :
								$related->the_post();
								?>
								<article class="post-card">
									<a class="post-media" href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'beautia-card', array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?></a>
									<div class="post-body">
										<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
										<span class="post-date"><?php echo esc_html( beautia_format_date( get_the_date( 'Y-m-d' ) ) ); ?></span>
									</div>
								</article>
								<?php
							endwhile;
							wp_reset_postdata();
							?>
						</div>
						<?php
					endif;
					?>

					<?php
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
					?>
				</div>
				<?php get_sidebar(); ?>
			</div>
		</div>
	</article>
	<?php
endwhile;

get_footer();
