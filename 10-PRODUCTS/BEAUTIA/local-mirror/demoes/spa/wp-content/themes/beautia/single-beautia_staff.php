<?php
/**
 * Single team member — profile, schedule, skills, portfolio and direct booking.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$id         = get_the_ID();
	$role       = get_post_meta( $id, '_beautia_role', true );
	$experience = get_post_meta( $id, '_beautia_experience', true );
	$schedule   = get_post_meta( $id, '_beautia_schedule', true );
	$socials    = array(
		'instagram' => get_post_meta( $id, '_beautia_instagram', true ),
		'telegram'  => get_post_meta( $id, '_beautia_telegram', true ),
		'whatsapp'  => get_post_meta( $id, '_beautia_whatsapp', true ),
	);

	// Services this specialist performs.
	$services = get_posts(
		array(
			'post_type'      => 'beautia_service',
			'posts_per_page' => 6,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'key'     => '_beautia_staff',
					'value'   => '"' . $id . '"',
					'compare' => 'LIKE',
				),
			),
		)
	);
	if ( ! $services ) {
		$services = get_posts( array( 'post_type' => 'beautia_service', 'posts_per_page' => 3 ) );
	}
	?>

	<div class="page-hero staff-hero">
		<div class="container">
			<nav class="crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'beautia' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'beautia' ); ?></a>
				<?php beautia_icon( 'chevron', 14 ); ?>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'beautia_staff' ) ); ?>"><?php esc_html_e( 'Our team', 'beautia' ); ?></a>
			</nav>
		</div>
	</div>

	<div class="container section staff-single">
		<aside class="staff-side">
			<div class="staff-card-lg">
				<div class="staff-single-photo">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'beautia-portrait', array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?>
					<?php else : ?>
						<div class="ph" style="aspect-ratio:3/4;background:var(--grad);opacity:.7"></div>
					<?php endif; ?>
				</div>
				<div class="staff-card-body">
					<h1><?php the_title(); ?></h1>
					<span class="staff-role"><?php echo esc_html( $role ); ?></span>

					<ul class="staff-facts">
						<?php if ( $experience ) : ?>
							<li><?php beautia_icon( 'award', 16 ); ?><span><?php printf( esc_html__( '%s years of experience', 'beautia' ), esc_html( beautia_num( (float) $experience ) ) ); ?></span></li>
						<?php endif; ?>
						<li><?php beautia_icon( 'sparkle', 16 ); ?><span><?php printf( esc_html__( '%s services', 'beautia' ), esc_html( beautia_num( count( $services ) ) ) ); ?></span></li>
						<li><?php beautia_icon( 'star', 16 ); ?><span><?php echo esc_html( beautia_num( 4.9, 1 ) ); ?> <?php esc_html_e( 'client rating', 'beautia' ); ?></span></li>
					</ul>

					<?php if ( array_filter( $socials ) ) : ?>
						<span class="social staff-social">
							<?php foreach ( $socials as $network => $url ) : ?>
								<?php if ( $url ) : ?>
									<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener nofollow" aria-label="<?php echo esc_attr( $network ); ?>"><?php beautia_icon( $network, 18 ); ?></a>
								<?php endif; ?>
							<?php endforeach; ?>
						</span>
					<?php endif; ?>

					<?php beautia_book_button( __( 'Book with me', 'beautia' ), 'btn btn-primary btn-block', 0, $id ); ?>
				</div>
			</div>

			<?php if ( is_array( $schedule ) && $schedule ) : ?>
				<div class="staff-schedule">
					<h3><?php beautia_icon( 'clock', 18 ); ?><?php esc_html_e( 'Working hours', 'beautia' ); ?></h3>
					<ul class="hours-list">
						<?php foreach ( beautia_week_days() as $key => $label ) : ?>
							<?php
							$row  = isset( $schedule[ $key ] ) ? $schedule[ $key ] : array();
							$open = ! empty( $row['on'] );
							?>
							<li>
								<span><?php echo esc_html( $label ); ?></span>
								<em<?php echo $open ? '' : ' class="is-off"'; ?>>
									<?php
									echo $open
										? esc_html( beautia_num_time( $row['from'] ) . ' – ' . beautia_num_time( $row['to'] ) )
										: esc_html__( 'Off', 'beautia' );
									?>
								</em>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</aside>

		<div class="staff-body entry-content" data-anim="fade-up">
			<span class="eyebrow"><?php beautia_icon( 'award', 16 ); ?><?php esc_html_e( 'Specialist profile', 'beautia' ); ?></span>
			<h2 class="block-title"><?php esc_html_e( 'About me', 'beautia' ); ?></h2>
			<?php the_content(); ?>

			<?php if ( $services ) : ?>
				<h2 class="block-title"><?php esc_html_e( 'Services I perform', 'beautia' ); ?></h2>
				<div class="grid grid-2 service-grid">
					<?php foreach ( $services as $i => $service ) : ?>
						<?php beautia_service_card( $service->ID, $i * 70 ); ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php
			$folio = new WP_Query( array( 'post_type' => 'beautia_portfolio', 'posts_per_page' => 6, 'no_found_rows' => true ) );
			if ( $folio->have_posts() ) :
				?>
				<h2 class="block-title"><?php esc_html_e( 'Selected work', 'beautia' ); ?></h2>
				<div class="service-gallery">
					<?php
					while ( $folio->have_posts() ) :
						$folio->the_post();
						if ( ! has_post_thumbnail() ) {
							continue;
						}
						?>
						<a class="service-shot" href="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'full' ) ); ?>" data-lightbox>
							<?php the_post_thumbnail( 'beautia-card', array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?>
							<span class="shot-zoom"><?php beautia_icon( 'search', 18 ); ?></span>
						</a>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
