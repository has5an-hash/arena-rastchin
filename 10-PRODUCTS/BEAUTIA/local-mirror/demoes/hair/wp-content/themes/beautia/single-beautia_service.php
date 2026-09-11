<?php
/**
 * Single service — a full sales page: hero, gallery, specialists, sticky
 * booking box, aftercare, FAQ, related services and Schema.org data.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$id         = get_the_ID();
	$price      = get_post_meta( $id, '_beautia_price', true );
	$old        = get_post_meta( $id, '_beautia_price_old', true );
	$duration   = get_post_meta( $id, '_beautia_duration', true );
	$deposit    = get_post_meta( $id, '_beautia_deposit', true );
	$icon       = get_post_meta( $id, '_beautia_icon', true );
	$highlights = array_filter( array_map( 'trim', explode( "\n", (string) get_post_meta( $id, '_beautia_highlights', true ) ) ) );
	$staff      = Beautia_Booking::get_service_staff( $id );
	$cats       = wp_get_post_terms( $id, 'beautia_service_cat', array( 'fields' => 'all' ) );
	$cat_names  = wp_list_pluck( $cats, 'name' );
	$aftercare  = array_filter( array_map( 'trim', explode( "\n", (string) get_post_meta( $id, '_beautia_aftercare', true ) ) ) );
	?>

	<div class="page-hero has-image service-hero">
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="page-hero-bg" data-parallax="0.14" style="background-image:url('<?php echo esc_url( get_the_post_thumbnail_url( $id, 'beautia-wide' ) ); ?>')"></div>
		<?php endif; ?>
		<div class="container">
			<nav class="crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'beautia' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'beautia' ); ?></a>
				<?php beautia_icon( 'chevron', 14 ); ?>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'beautia_service' ) ); ?>"><?php esc_html_e( 'Services', 'beautia' ); ?></a>
				<?php if ( $cat_names ) : ?>
					<?php beautia_icon( 'chevron', 14 ); ?>
					<span><?php echo esc_html( $cat_names[0] ); ?></span>
				<?php endif; ?>
			</nav>
			<span class="eyebrow"><?php beautia_icon( $icon ? $icon : 'sparkle', 16 ); ?><?php echo esc_html( implode( '، ', $cat_names ) ); ?></span>
			<h1 data-anim="fade-up"><?php the_title(); ?></h1>
			<ul class="service-hero-meta" data-anim="fade-up" data-delay="120">
				<li><?php beautia_icon( 'clock', 16 ); ?><span><?php echo esc_html( beautia_duration( $duration ) ); ?></span></li>
				<li><?php beautia_icon( 'wallet', 16 ); ?><span><?php echo esc_html( beautia_price( $price ) ); ?></span></li>
				<li><?php beautia_icon( 'star', 16 ); ?><span><?php echo esc_html( beautia_num( 4.9, 1 ) ); ?> / <?php echo esc_html( beautia_num( 5 ) ); ?></span></li>
				<li><?php beautia_icon( 'message', 16 ); ?><span><?php esc_html_e( 'SMS at every stage', 'beautia' ); ?></span></li>
			</ul>
		</div>
	</div>

	<div class="container section service-single">
		<div class="service-content entry-content" data-anim="fade-up">
			<?php the_content(); ?>

			<?php if ( $highlights ) : ?>
				<h2 class="block-title"><?php esc_html_e( 'What is included', 'beautia' ); ?></h2>
				<ul class="highlights check-list">
					<?php foreach ( $highlights as $h ) : ?>
						<li><?php beautia_icon( 'check', 20 ); ?><span><?php echo esc_html( $h ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php
			// Gallery: portfolio pieces filed under the same category.
			$gallery = new WP_Query(
				array(
					'post_type'      => 'beautia_portfolio',
					'posts_per_page' => 6,
					'no_found_rows'  => true,
					'tax_query'      => $cat_names ? array( // phpcs:ignore WordPress.DB.SlowDBQuery
						array(
							'taxonomy' => 'beautia_portfolio_cat',
							'field'    => 'name',
							'terms'    => $cat_names,
						),
					) : array(),
				)
			);
			if ( $gallery->have_posts() ) :
				?>
				<h2 class="block-title"><?php esc_html_e( 'Recent results', 'beautia' ); ?></h2>
				<div class="service-gallery">
					<?php
					while ( $gallery->have_posts() ) :
						$gallery->the_post();
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

			<?php if ( $aftercare ) : ?>
				<h2 class="block-title"><?php esc_html_e( 'Aftercare', 'beautia' ); ?></h2>
				<ol class="aftercare-list">
					<?php foreach ( $aftercare as $i => $step ) : ?>
						<li><span class="ac-num"><?php echo esc_html( beautia_num( $i + 1 ) ); ?></span><span><?php echo esc_html( $step ); ?></span></li>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>

			<?php if ( $staff ) : ?>
				<h2 class="block-title"><?php esc_html_e( 'Specialists for this service', 'beautia' ); ?></h2>
				<div class="mini-staff">
					<?php foreach ( array_slice( $staff, 0, 4 ) as $sid ) : ?>
						<a class="mini-staff-item" href="<?php echo esc_url( get_permalink( $sid ) ); ?>">
							<?php echo get_the_post_thumbnail( $sid, 'thumbnail', array( 'alt' => get_the_title( $sid ) ) ); ?>
							<strong><?php echo esc_html( get_the_title( $sid ) ); ?></strong>
							<span><?php echo esc_html( get_post_meta( $sid, '_beautia_role', true ) ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php
			beautia_section_faq(
				array(
					array( __( 'How long does this appointment take?', 'beautia' ), sprintf( /* translators: %s: duration. */ __( 'Plan for about %s, including consultation and preparation. We never overlap appointments, so the time is entirely yours.', 'beautia' ), beautia_duration( $duration ) ) ),
					array( __( 'How do I pay?', 'beautia' ), __( 'You pay at the salon after the service. If a deposit is required for this treatment it will be shown on the booking form before you confirm.', 'beautia' ) ),
					array( __( 'Can I choose my specialist?', 'beautia' ), __( 'Yes — in step two of the booking form you can pick a specific specialist, or leave it on “any available” for the earliest slot.', 'beautia' ) ),
					array( __( 'What if I need to cancel?', 'beautia' ), __( 'Cancel or reschedule free of charge from your customer panel, up to the cancellation window shown on your confirmation SMS.', 'beautia' ) ),
				)
			);
			?>
		</div>

		<aside class="service-aside">
			<div class="booking-box is-sticky">
				<div class="price-row">
					<?php if ( $old ) : ?><del><?php echo esc_html( beautia_price( $old ) ); ?></del><?php endif; ?>
					<strong><?php echo esc_html( beautia_price( $price ) ); ?></strong>
					<?php if ( $old && $price && (float) $old > (float) $price ) : ?>
						<span class="save-chip"><?php printf( esc_html__( 'Save %s', 'beautia' ), esc_html( beautia_price( (float) $old - (float) $price ) ) ); ?></span>
					<?php endif; ?>
				</div>
				<ul class="box-meta">
					<li><?php beautia_icon( 'clock', 16 ); ?><span><?php echo esc_html( beautia_duration( $duration ) ); ?></span></li>
					<?php if ( $deposit ) : ?>
						<li><?php beautia_icon( 'wallet', 16 ); ?><span><?php printf( esc_html__( 'Deposit: %s', 'beautia' ), esc_html( beautia_price( $deposit ) ) ); ?></span></li>
					<?php endif; ?>
					<li><?php beautia_icon( 'message', 16 ); ?><span><?php esc_html_e( 'SMS confirmation & reminder', 'beautia' ); ?></span></li>
					<li><?php beautia_icon( 'shield', 16 ); ?><span><?php esc_html_e( 'Sterilised, single-use tools', 'beautia' ); ?></span></li>
					<li><?php beautia_icon( 'mobile', 16 ); ?><span><?php esc_html_e( 'Book with your mobile number only', 'beautia' ); ?></span></li>
				</ul>
				<?php beautia_book_button( __( 'Book this service', 'beautia' ), 'btn btn-primary btn-block', $id ); ?>
				<button class="btn btn-ghost btn-block beautia-fav" data-id="<?php echo esc_attr( $id ); ?>">
					<?php beautia_icon( 'heart', 18 ); ?><span><?php esc_html_e( 'Save to favourites', 'beautia' ); ?></span>
				</button>
				<?php $phone = beautia_get_option( 'phone', '' ); ?>
				<?php if ( $phone ) : ?>
					<a class="box-call" href="tel:<?php echo esc_attr( $phone ); ?>"><?php beautia_icon( 'phone', 16 ); ?><span><?php echo esc_html( $phone ); ?></span></a>
				<?php endif; ?>
			</div>
		</aside>
	</div>

	<?php
	// Related services from the same category.
	$related = new WP_Query(
		array(
			'post_type'      => 'beautia_service',
			'posts_per_page' => 3,
			'post__not_in'   => array( $id ),
			'no_found_rows'  => true,
			'tax_query'      => $cats ? array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'taxonomy' => 'beautia_service_cat',
					'field'    => 'term_id',
					'terms'    => wp_list_pluck( $cats, 'term_id' ),
				),
			) : array(),
		)
	);
	if ( $related->have_posts() ) :
		?>
		<section class="section related-services">
			<div class="container">
				<?php beautia_heading( __( 'You may also like', 'beautia' ), __( 'Related services', 'beautia' ) ); ?>
				<div class="grid grid-3 service-grid">
					<?php
					while ( $related->have_posts() ) :
						$related->the_post();
						beautia_service_card( get_the_ID() );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
		<?php
	endif;

	// Schema.org — helps the service show up properly in search results.
	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Service',
		'name'        => wp_strip_all_tags( get_the_title() ),
		'description' => beautia_truncate( get_the_excerpt(), 200 ),
		'url'         => get_permalink(),
		'provider'    => array(
			'@type'     => 'HealthAndBeautyBusiness',
			'name'      => get_bloginfo( 'name' ),
			'telephone' => beautia_get_option( 'phone', '' ),
			'address'   => beautia_get_option( 'address', '' ),
		),
	);
	if ( $price ) {
		$schema['offers'] = array(
			'@type'         => 'Offer',
			'price'         => (float) $price,
			'priceCurrency' => beautia_get_option( 'currency_code', 'IRR' ),
			'availability'  => 'https://schema.org/InStock',
		);
	}
	printf( '<script type="application/ld+json">%s</script>', wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ); // phpcs:ignore

endwhile;

get_footer();
