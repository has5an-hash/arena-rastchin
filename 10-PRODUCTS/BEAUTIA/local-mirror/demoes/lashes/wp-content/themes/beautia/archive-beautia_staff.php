<?php
/**
 * Team archive — the whole crew, with role, experience and direct booking.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="page-hero small">
	<div class="container">
		<nav class="crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'beautia' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'beautia' ); ?></a>
			<?php beautia_icon( 'chevron', 14 ); ?>
			<span><?php esc_html_e( 'Our team', 'beautia' ); ?></span>
		</nav>
		<h1><?php esc_html_e( 'Our team', 'beautia' ); ?></h1>
		<p><?php esc_html_e( 'Every specialist, their experience and the services they perform.', 'beautia' ); ?></p>
	</div>
</div>

<div class="container section">
	<div class="grid grid-3 team-grid">
		<?php
		$i = 0;
		while ( have_posts() ) :
			the_post();
			$i++;
			$role = get_post_meta( get_the_ID(), '_beautia_role', true );
			$exp  = get_post_meta( get_the_ID(), '_beautia_experience', true );
			?>
			<article class="staff-card" data-anim="fade-up" data-delay="<?php echo esc_attr( min( $i * 70, 400 ) ); ?>">
				<a class="staff-photo" href="<?php the_permalink(); ?>">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'beautia-portrait', array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?>
					<?php else : ?>
						<span class="ph" style="aspect-ratio:3/4;display:block;background:var(--grad);opacity:.7"></span>
					<?php endif; ?>
					<span class="staff-hover">
						<span class="btn btn-sm btn-light"><span><?php esc_html_e( 'View profile', 'beautia' ); ?></span></span>
					</span>
				</a>
				<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
				<span class="staff-role"><?php echo esc_html( $role ); ?></span>
				<?php if ( $exp ) : ?>
					<span class="staff-exp"><?php beautia_icon( 'award', 15 ); ?><?php printf( esc_html__( '%s years of experience', 'beautia' ), esc_html( beautia_num( (float) $exp ) ) ); ?></span>
				<?php endif; ?>
				<?php beautia_book_button( __( 'Book with me', 'beautia' ), 'btn btn-sm btn-outline', 0, get_the_ID() ); ?>
			</article>
			<?php
		endwhile;
		?>
	</div>
	<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
</div>
<?php
get_footer();
