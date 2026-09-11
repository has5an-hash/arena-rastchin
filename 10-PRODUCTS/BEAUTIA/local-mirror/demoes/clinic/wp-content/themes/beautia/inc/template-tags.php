<?php
/**
 * Template tags — every front-end section is a function so it can be reused by
 * templates, shortcodes and blocks.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

/** Section heading. */
function beautia_heading( $eyebrow = '', $title = '', $text = '', $align = 'center' ) {
	?>
	<header class="sec-head align-<?php echo esc_attr( $align ); ?>" data-anim="fade-up">
		<?php if ( $eyebrow ) : ?>
			<span class="eyebrow"><?php beautia_icon( 'sparkle', 16 ); ?><?php echo esc_html( $eyebrow ); ?></span>
		<?php endif; ?>
		<?php if ( $title ) : ?>
			<h2 class="sec-title"><?php echo wp_kses_post( $title ); ?></h2>
		<?php endif; ?>
		<?php if ( $text ) : ?>
			<p class="sec-text"><?php echo wp_kses_post( $text ); ?></p>
		<?php endif; ?>
	</header>
	<?php
}

/** Booking button. */
function beautia_book_button( $label = '', $class = 'btn btn-primary', $service_id = 0, $staff_id = 0 ) {
	$label = $label ? $label : __( 'Book Now', 'beautia' );
	$url   = beautia_get_page_url( 'booking' );
	if ( $service_id ) {
		$url = add_query_arg( 'service', $service_id, $url );
	}
	if ( $staff_id ) {
		$url = add_query_arg( 'staff', $staff_id, $url );
	}
	printf(
		'<a class="%1$s" href="%2$s"><span>%3$s</span>%4$s</a>',
		esc_attr( $class ),
		esc_url( $url ),
		esc_html( $label ),
		beautia_get_icon( 'arrow', 18, 'btn-ico' ) // phpcs:ignore
	);
}

/** Top bar. */
function beautia_topbar() {
	if ( ! get_theme_mod( 'beautia_topbar', true ) ) {
		return;
	}
	$phone = get_theme_mod( 'beautia_phone', beautia_get_option( 'phone', '' ) );
	$hours = get_theme_mod( 'beautia_hours', __( 'Sat – Thu: 10:00 – 20:00', 'beautia' ) );
	?>
	<div class="topbar">
		<div class="container topbar-in">
			<div class="topbar-left">
				<?php if ( $phone ) : ?>
					<a href="tel:<?php echo esc_attr( beautia_fa_to_en_digits($phone) ); ?>"><?php beautia_icon( 'phone', 16 ); ?><?php echo esc_html( $phone ); ?></a>
				<?php endif; ?>
				<span class="hide-sm"><?php beautia_icon( 'clock', 16 ); ?><?php echo esc_html( $hours ); ?></span>
			</div>
			<div class="topbar-right">
				<?php beautia_social_links(); ?>
				<?php if ( is_user_logged_in() ) : ?>
					<a class="topbar-user" href="<?php echo esc_url( beautia_get_page_url( 'account' ) ); ?>">
						<?php beautia_icon( 'user', 16 ); ?><?php echo esc_html( wp_get_current_user()->display_name ); ?>
					</a>
				<?php else : ?>
					<a class="topbar-user" href="<?php echo esc_url( beautia_get_page_url( 'login' ) ); ?>">
						<?php beautia_icon( 'mobile', 16 ); ?><?php esc_html_e( 'Login with mobile', 'beautia' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}

/** Social icons. */
function beautia_social_links() {
	$nets = array( 'instagram', 'telegram', 'whatsapp', 'youtube', 'pinterest' );
	echo '<span class="social">';
	foreach ( $nets as $net ) {
		$url = get_theme_mod( 'beautia_' . $net );
		if ( ! $url ) {
			continue;
		}
		printf(
			'<a href="%s" target="_blank" rel="noopener" aria-label="%s">%s</a>',
			esc_url( $url ),
			esc_attr( $net ),
			beautia_get_icon( $net, 18 ) // phpcs:ignore
		);
	}
	echo '</span>';
}

/** Logo / site title. */
function beautia_branding() {
	$demo = function_exists('beautia_demo_context') ? beautia_demo_context() : '';
	if ($demo) {
		$studio = beautia_studios()[$demo];
		echo '<a class="brand" href="'.esc_url(beautia_studio_url($demo)).'"><span class="brand-mark">'.beautia_get_icon('sparkle',26).'</span><span class="brand-text"><strong dir="ltr">'.esc_html($studio['name']).'</strong><em>'.esc_html($studio['fa']).'</em></span></a>';
		return;
	}
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	printf(
		'<a class="brand" href="%s"><span class="brand-mark">%s</span><span class="brand-text"><strong>%s</strong><em>%s</em></span></a>',
		esc_url( home_url( '/' ) ),
		beautia_get_icon( 'sparkle', 26 ), // phpcs:ignore
		esc_html( get_bloginfo( 'name' ) ),
		esc_html( get_bloginfo( 'description' ) )
	);
}

/* -------------------------------------------------------------------------
 * Sections
 * ---------------------------------------------------------------------- */

/** Hero. */
function beautia_section_hero( $args = array() ) {
	$demo = beautia_get_active_demo();
	$copy = beautia_demo_hero( $demo );
	$args = wp_parse_args(
		$args,
		array(
			'eyebrow'  => $copy['eyebrow'],
			'title'    => $copy['title'],
			'text'     => $copy['text'],
			'image'    => beautia_img( 'hero-' . $demo . '.jpg', 'hero-nails.jpg' ),
			'rating'   => '4.9',
			'clients'  => '12k+',
		)
	);
	?>
	<section class="hero hero-<?php echo esc_attr( $demo ); ?>">
		<div class="hero-bg" data-parallax="0.18" style="background-image:url('<?php echo esc_url( $args['image'] ); ?>')"></div>
		<div class="hero-veil"></div>
		<div class="container hero-in">
			<div class="hero-copy">
				<span class="eyebrow" data-anim="fade-up"><?php beautia_icon( 'sparkle', 16 ); ?><?php echo esc_html( $args['eyebrow'] ); ?></span>
				<h1 class="hero-title split" data-anim="chars"><?php echo wp_kses_post( $args['title'] ); ?></h1>
				<p class="hero-text" data-anim="fade-up" data-delay="200"><?php echo esc_html( $args['text'] ); ?></p>
				<div class="hero-actions" data-anim="fade-up" data-delay="320">
					<?php beautia_book_button( __( 'Book an appointment', 'beautia' ) ); ?>
					<a class="btn btn-ghost" href="#services"><span><?php esc_html_e( 'Explore services', 'beautia' ); ?></span></a>
				</div>
				<div class="hero-badges" data-anim="fade-up" data-delay="440">
					<div class="badge"><strong data-count="<?php echo esc_attr( $args['rating'] ); ?>" data-decimals="1">0</strong><span><?php esc_html_e( 'Average rating', 'beautia' ); ?></span></div>
					<div class="badge"><strong><?php echo esc_html( $args['clients'] ); ?></strong><span><?php esc_html_e( 'Happy clients', 'beautia' ); ?></span></div>
					<div class="badge badge-sms"><?php beautia_icon( 'message', 18 ); ?><span><?php esc_html_e( 'SMS confirmation on every step', 'beautia' ); ?></span></div>
				</div>
			</div>
			<div class="hero-card" data-anim="fade-left" data-delay="300">
				<?php beautia_quick_book_widget(); ?>
			</div>
		</div>
		<a class="hero-scroll" href="#services"><span></span><?php esc_html_e( 'Scroll', 'beautia' ); ?></a>
	</section>
	<?php
}

/** Small booking widget used inside the hero. */
function beautia_quick_book_widget() {
	$services = get_posts( array( 'post_type' => 'beautia_service', 'posts_per_page' => 12 ) );
	?>
	<div class="quick-book">
		<h3><?php beautia_icon( 'calendar', 20 ); ?><?php esc_html_e( 'Quick booking', 'beautia' ); ?></h3>
		<form class="quick-book-form" action="<?php echo esc_url( beautia_get_page_url( 'booking' ) ); ?>" method="get">
			<label>
				<span><?php esc_html_e( 'Service', 'beautia' ); ?></span>
				<select name="service">
					<?php foreach ( $services as $service ) : ?>
						<option value="<?php echo esc_attr( $service->ID ); ?>"><?php echo esc_html( $service->post_title ); ?></option>
					<?php endforeach; ?>
					<?php if ( ! $services ) : ?>
						<option value=""><?php esc_html_e( 'Add services in the dashboard', 'beautia' ); ?></option>
					<?php endif; ?>
				</select>
			</label>
			<label>
				<span><?php esc_html_e( 'Preferred date', 'beautia' ); ?></span>
				<span class="field-date"><?php beautia_icon( 'calendar', 18 ); ?><input type="text" name="date" data-jdate data-min="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>" data-value="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>" placeholder="<?php esc_attr_e( 'Pick a day', 'beautia' ); ?>" /></span>
			</label>
			<label>
				<span><?php esc_html_e( 'Mobile number', 'beautia' ); ?></span>
				<input type="tel" name="mobile" inputmode="numeric" placeholder="0912 000 0000" />
			</label>
			<button class="btn btn-primary btn-block" type="submit">
				<span><?php esc_html_e( 'Check availability', 'beautia' ); ?></span><?php beautia_icon( 'arrow', 18, 'btn-ico' ); ?>
			</button>
			<p class="quick-note"><?php beautia_icon( 'shield', 14 ); ?><?php esc_html_e( 'Login with a verification code — no password needed.', 'beautia' ); ?></p>
		</form>
	</div>
	<?php
}

/** Marquee ribbon. */
function beautia_marquee( $words = array() ) {
	if ( ! get_theme_mod( 'beautia_enable_marquee', true ) ) {
		return;
	}
	if ( ! $words ) {
		$words = array( __( 'Nail Art', 'beautia' ), __( 'Skin Care', 'beautia' ), __( 'Lashes', 'beautia' ), __( 'Hair Colour', 'beautia' ), __( 'Massage', 'beautia' ), __( 'Makeup', 'beautia' ) );
	}
	?>
	<div class="marquee" aria-hidden="true">
		<div class="marquee-track">
			<?php for ( $i = 0; $i < 2; $i++ ) : ?>
				<?php foreach ( $words as $word ) : ?>
					<span class="marquee-item"><?php echo esc_html( $word ); ?><?php beautia_icon( 'sparkle', 18 ); ?></span>
				<?php endforeach; ?>
			<?php endfor; ?>
		</div>
	</div>
	<?php
}

/** Services grid. */
function beautia_section_services( $args = array() ) {
	$args  = wp_parse_args( $args, array( 'count' => 6, 'category' => '', 'style' => 'cards' ) );
	$query = new WP_Query(
		array(
			'post_type'      => 'beautia_service',
			'posts_per_page' => (int) $args['count'],
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'tax_query'      => $args['category'] ? array( array( 'taxonomy' => 'beautia_service_cat', 'field' => 'slug', 'terms' => $args['category'] ) ) : array(), // phpcs:ignore
		)
	);
	?>
	<section class="section services" id="services">
		<div class="container">
			<?php
			beautia_heading(
				__( 'What we do', 'beautia' ),
				__( 'Signature services', 'beautia' ),
				__( 'Every treatment is performed with sterilised tools, premium products and a specialist matched to your needs.', 'beautia' )
			);
			?>
			<div class="grid grid-3 service-grid">
				<?php
				$i = 0;
				while ( $query->have_posts() ) :
					$query->the_post();
					$i++;
					?>
					<?php beautia_service_card( get_the_ID(), $i * 80 ); ?>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
				<?php if ( ! $query->have_posts() && 0 === $i ) : ?>
					<p class="empty"><?php esc_html_e( 'No services yet — import a demo or add your first service.', 'beautia' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}

/** Portfolio with filter. */
function beautia_section_portfolio( $args = array() ) {
	$args  = wp_parse_args( $args, array( 'count' => 9 ) );
	$terms = get_terms( array( 'taxonomy' => 'beautia_portfolio_cat', 'hide_empty' => true ) );
	$query = new WP_Query( array( 'post_type' => 'beautia_portfolio', 'posts_per_page' => (int) $args['count'], 'orderby' => 'ID', 'order' => 'ASC' ) );
	?>
	<section class="section portfolio" id="portfolio">
		<div class="container">
			<?php
			beautia_heading(
				__( 'Our work', 'beautia' ),
				__( 'Recent transformations', 'beautia' ),
				'از میان سبک‌ها انتخاب کنید و ایده دلخواهتان را برای جلسه بعد نگه دارید.'
			);
			?>
			<?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
				<div class="filters" data-anim="fade-up">
					<button class="filter is-active" data-filter="*"><?php esc_html_e( 'All', 'beautia' ); ?></button>
					<?php foreach ( $terms as $term ) : ?>
						<button class="filter" data-filter="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<div class="masonry">
				<?php
				$i = 0;
				while ( $query->have_posts() ) :
					$query->the_post();
					$i++;
					$cats = wp_get_post_terms( get_the_ID(), 'beautia_portfolio_cat', array( 'fields' => 'slugs' ) );
					?>
					<figure class="folio" data-cats="<?php echo esc_attr( implode( ' ', (array) $cats ) ); ?>" data-anim="zoom-in" data-delay="<?php echo esc_attr( $i * 60 ); ?>">
						<?php the_post_thumbnail( 'beautia-card', array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?>
						<figcaption>
							<h3><?php the_title(); ?></h3>
							<span><?php echo esc_html( implode( ', ', wp_get_post_terms( get_the_ID(), 'beautia_portfolio_cat', array( 'fields' => 'names' ) ) ) ); ?></span>
							<a class="folio-zoom" href="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'full' ) ); ?>" data-lightbox><?php beautia_icon( 'search', 20 ); ?></a>
						</figcaption>
					</figure>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
	<?php
}

/** Team. */
function beautia_section_team( $args = array() ) {
	$args  = wp_parse_args( $args, array( 'count' => 4 ) );
	$query = new WP_Query( array( 'post_type' => 'beautia_staff', 'posts_per_page' => (int) $args['count'], 'orderby' => 'ID', 'order' => 'ASC' ) );
	?>
	<section class="section team" id="team">
		<div class="container">
			<?php beautia_heading( __( 'Meet the artists', 'beautia' ), __( 'Your specialists', 'beautia' ), __( 'Certified professionals with years of hands-on experience.', 'beautia' ) ); ?>
			<div class="grid grid-4">
				<?php
				$i = 0;
				while ( $query->have_posts() ) :
					$query->the_post();
					$i++;
					?>
					<article class="staff-card" data-anim="fade-up" data-delay="<?php echo esc_attr( $i * 90 ); ?>">
						<div class="staff-photo">
							<?php the_post_thumbnail( 'beautia-portrait', array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?>
							<div class="staff-hover">
								<a class="btn btn-sm btn-light" href="<?php echo esc_url(add_query_arg('staff',get_the_ID(),beautia_get_page_url('booking'))); ?>"><?php esc_html_e('Book with me','beautia'); ?></a>
							</div>
						</div>
						<h3><?php the_title(); ?></h3>
						<span class="staff-role"><?php echo esc_html( get_post_meta( get_the_ID(), '_beautia_role', true ) ); ?></span>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 16 ) ); ?></p>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
	<?php
}

/** Testimonials slider. */
function beautia_section_testimonials() {
	$query = new WP_Query( array( 'post_type' => 'beautia_testimonial', 'posts_per_page' => 9, 'orderby' => 'ID', 'order' => 'ASC' ) );
	if ( ! $query->have_posts() ) {
		return;
	}
	?>
	<section class="section testimonials">
		<div class="container">
			<?php beautia_heading( __( 'Loved by our clients', 'beautia' ), __( 'What people say', 'beautia' ) ); ?>
			<div class="slider" data-slider data-autoplay="6000">
				<div class="slider-track">
					<?php
					while ( $query->have_posts() ) :
						$query->the_post();
						$rating = (int) get_post_meta( get_the_ID(), '_beautia_rating', true );
						?>
						<div class="slide">
							<div class="quote-card">
								<span class="quote-mark"><?php beautia_icon( 'quote', 28 ); ?></span>
								<div class="stars">
									<?php for ( $s = 0; $s < max( 1, $rating ); $s++ ) : ?>
										<?php beautia_icon( 'star', 18 ); ?>
									<?php endfor; ?>
								</div>
								<p><?php echo esc_html( wp_strip_all_tags( get_the_content() ) ); ?></p>
								<footer>
									<strong><?php the_title(); ?></strong>
									<span><?php echo esc_html( get_post_meta( get_the_ID(), '_beautia_author_role', true ) ); ?></span>
								</footer>
							</div>
						</div>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</div>
				<div class="slider-nav">
					<button class="slider-prev" aria-label="<?php esc_attr_e( 'Previous', 'beautia' ); ?>"><?php beautia_icon( 'chevron', 20 ); ?></button>
					<div class="slider-dots"></div>
					<button class="slider-next" aria-label="<?php esc_attr_e( 'Next', 'beautia' ); ?>"><?php beautia_icon( 'chevron', 20 ); ?></button>
				</div>
			</div>
		</div>
	</section>
	<?php
}

/** Numbers. */
function beautia_section_stats( $items = array() ) {
	if ( ! $items ) {
		$items = array(
			array( 'icon' => 'award', 'value' => 14, 'suffix' => '+', 'label' => __( 'Years of experience', 'beautia' ) ),
			array( 'icon' => 'users', 'value' => 12500, 'suffix' => '', 'label' => __( 'Appointments served', 'beautia' ) ),
			array( 'icon' => 'star', 'value' => 4.9, 'suffix' => '', 'label' => __( 'Average rating', 'beautia' ), 'decimals' => 1 ),
			array( 'icon' => 'heart', 'value' => 96, 'suffix' => '%', 'label' => __( 'Return clients', 'beautia' ) ),
		);
	}
	?>
	<section class="section stats">
		<div class="container grid grid-4">
			<?php foreach ( $items as $i => $item ) : ?>
				<div class="stat" data-anim="fade-up" data-delay="<?php echo esc_attr( $i * 90 ); ?>">
					<?php beautia_icon( $item['icon'], 28 ); ?>
					<strong data-count="<?php echo esc_attr( $item['value'] ); ?>" data-decimals="<?php echo esc_attr( isset( $item['decimals'] ) ? $item['decimals'] : 0 ); ?>" data-suffix="<?php echo esc_attr( $item['suffix'] ); ?>">0</strong>
					<span><?php echo esc_html( $item['label'] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
}

/** Pricing table built from service categories. */
function beautia_section_pricing() {
	$terms = get_terms( array( 'taxonomy' => 'beautia_service_cat', 'hide_empty' => true, 'number' => 3 ) );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return;
	}
	?>
	<section class="section pricing" id="pricing">
		<div class="container">
			<?php beautia_heading( __( 'Transparent pricing', 'beautia' ), __( 'Menu & prices', 'beautia' ) ); ?>
			<div class="grid grid-3">
				<?php foreach ( $terms as $i => $term ) : ?>
					<div class="price-card<?php echo 1 === $i ? ' is-featured' : ''; ?>" data-anim="fade-up" data-delay="<?php echo esc_attr( $i * 100 ); ?>">
						<h3><?php echo esc_html( $term->name ); ?></h3>
						<ul>
							<?php
							$services = get_posts(
								array(
									'post_type'      => 'beautia_service',
									'posts_per_page' => 6,
									'tax_query'      => array( array( 'taxonomy' => 'beautia_service_cat', 'field' => 'term_id', 'terms' => $term->term_id ) ), // phpcs:ignore
								)
							);
							foreach ( $services as $service ) :
								?>
								<li>
									<span class="pname"><?php echo esc_html( $service->post_title ); ?></span>
									<span class="pdots"></span>
									<span class="pval"><?php echo esc_html( beautia_price( get_post_meta( $service->ID, '_beautia_price', true ) ) ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
						<?php beautia_book_button( __( 'Reserve a slot', 'beautia' ), 'btn btn-outline btn-block' ); ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/** Feature list explaining the professional booking stack. */
function beautia_section_features() {
	$features = array(
		array( 'mobile', __( 'Login with mobile + OTP', 'beautia' ), __( 'No passwords. Clients enter their number, receive a code by SMS and they are in.', 'beautia' ) ),
		array( 'calendar', __( 'Smart appointment engine', 'beautia' ), __( 'Per-staff schedules, breaks, buffers, capacity, holidays and lead-time rules.', 'beautia' ) ),
		array( 'message', __( 'SMS at every stage', 'beautia' ), __( 'Submitted, confirmed, rescheduled, reminder, completed, no-show — fully editable templates.', 'beautia' ) ),
		array( 'user', __( 'Customer panel', 'beautia' ), __( 'Upcoming visits, history, cancel & reschedule, favourites, loyalty tier and profile.', 'beautia' ) ),
		array( 'chart', __( 'Owner dashboard', 'beautia' ), __( 'Daily agenda, revenue, statuses, SMS log and one-click confirmations.', 'beautia' ) ),
		array( 'shield', __( 'No paid plugins', 'beautia' ), __( 'Booking, OTP, SMS, portfolio and panel are native theme features.', 'beautia' ) ),
	);
	?>
	<section class="section features" id="features">
		<div class="container">
			<?php beautia_heading( __( 'Built in', 'beautia' ), __( 'Professional features, zero add-ons', 'beautia' ) ); ?>
			<div class="grid grid-3">
				<?php foreach ( $features as $i => $f ) : ?>
					<div class="feature" data-anim="fade-up" data-delay="<?php echo esc_attr( $i * 70 ); ?>">
						<span class="feature-ico"><?php beautia_icon( $f[0], 24 ); ?></span>
						<h3><?php echo esc_html( $f[1] ); ?></h3>
						<p><?php echo esc_html( $f[2] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/** Before / after slider. */
/**
 * A single before/after comparison slider.
 *
 * @param string $before Image URL shown as the "before" state.
 * @param string $after  Image URL shown as the "after" state.
 * @param array  $args   title, before_label, after_label, start (0-100).
 */
function beautia_compare( $before, $after, $args = array() ) {
	if ( ! $before || ! $after ) {
		return;
	}
	$args = wp_parse_args(
		$args,
		array(
			'title'        => '',
			'before_label' => __( 'Before', 'beautia' ),
			'after_label'  => __( 'After', 'beautia' ),
			'start'        => 50,
		)
	);
	?>
	<figure class="cmp-figure">
		<div class="cmp" data-compare data-start="<?php echo esc_attr( (int) $args['start'] ); ?>">
			<img class="cmp-before" src="<?php echo esc_url( $before ); ?>" alt="<?php echo esc_attr( $args['before_label'] ); ?>" loading="lazy" />
			<img class="cmp-after" src="<?php echo esc_url( $after ); ?>" alt="<?php echo esc_attr( $args['after_label'] ); ?>" loading="lazy" />
			<span class="cmp-tag cmp-tag-before"><?php echo esc_html( $args['before_label'] ); ?></span>
			<span class="cmp-tag cmp-tag-after"><?php echo esc_html( $args['after_label'] ); ?></span>
			<button type="button" class="cmp-handle" role="slider" aria-label="<?php esc_attr_e( 'Drag to compare', 'beautia' ); ?>"
				aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( (int) $args['start'] ); ?>"></button>
		</div>
		<?php if ( $args['title'] ) : ?>
			<figcaption><?php echo esc_html( $args['title'] ); ?></figcaption>
		<?php endif; ?>
	</figure>
	<?php
}

/** "Before & after" section, built from the gallery post type. */
function beautia_section_before_after() {
	if (function_exists('beautia_demo_context') && beautia_demo_context()==='nails' && get_option('beautia_showcase_enabled')) {
		beautia_nail_comparison();
		return;
	}
	$query = new WP_Query(
		array(
			'post_type'      => 'beautia_gallery',
			'posts_per_page' => 4,
			'no_found_rows'  => true,
			'orderby'        => 'ID',
			'order'          => 'ASC',
		)
	);
	if ( ! $query->have_posts() ) {
		return;
	}
	$items = array();
	while ( $query->have_posts() ) {
		$query->the_post();
		$before = get_post_meta( get_the_ID(), '_beautia_before', true );
		$after  = get_post_meta( get_the_ID(), '_beautia_after', true );
		if ( $before && $after ) {
			$items[] = array(
				'title'  => get_the_title(),
				'before' => $before,
				'after'  => $after,
			);
		}
	}
	wp_reset_postdata();
	if ( ! $items ) {
		return;
	}
	$featured = array_shift( $items );
	?>
	<section class="section beforeafter" id="results">
		<div class="container">
			<div class="cmp-grid">
				<div class="cmp-copy" data-anim="fade-up">
					<span class="eyebrow"><?php beautia_icon( 'sparkle', 16 ); ?><?php esc_html_e( 'Real results', 'beautia' ); ?></span>
					<h2 class="sec-title"><?php esc_html_e( 'Before and after, without retouching', 'beautia' ); ?></h2>
					<p class="sec-text"><?php esc_html_e( 'Drag the handle and judge for yourself. Every photo is taken in the salon, under the same light, with no filter.', 'beautia' ); ?></p>
					<ul class="check-list">
						<li><?php beautia_icon( 'check', 20 ); ?><span><?php esc_html_e( 'Same angle, same light, no filter', 'beautia' ); ?></span></li>
						<li><?php beautia_icon( 'check', 20 ); ?><span><?php esc_html_e( 'Published with the client’s written consent', 'beautia' ); ?></span></li>
						<li><?php beautia_icon( 'check', 20 ); ?><span><?php esc_html_e( 'The full aftercare plan is sent to you by SMS', 'beautia' ); ?></span></li>
					</ul>
					<span class="cmp-hint"><?php beautia_icon( 'chevron', 16 ); ?><?php esc_html_e( 'Drag the handle left and right', 'beautia' ); ?></span>
				</div>
				<div data-anim="fade-left" data-delay="120">
					<?php beautia_compare( $featured['before'], $featured['after'], array( 'title' => $featured['title'] ) ); ?>
				</div>
			</div>

			<?php if ( $items ) : ?>
				<div class="grid grid-3 cmp-more">
					<?php
					$i = 0;
					foreach ( $items as $item ) {
						$i++;
						echo '<div data-anim="fade-up" data-delay="' . esc_attr( $i * 80 ) . '">';
						beautia_compare( $item['before'], $item['after'], array( 'title' => $item['title'] ) );
						echo '</div>';
					}
					?>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/** Blog teaser. */
function beautia_section_blog( $count = 3 ) {
	$query = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => (int) $count ) );
	if ( ! $query->have_posts() ) {
		return;
	}
	?>
	<section class="section blog-teaser" id="journal">
		<div class="container">
			<?php beautia_heading( __( 'Journal', 'beautia' ), __( 'Beauty notes & guides', 'beautia' ) ); ?>
			<div class="grid grid-3">
				<?php
				$i = 0;
				while ( $query->have_posts() ) :
					$query->the_post();
					$i++;
					?>
					<article class="post-card" data-anim="fade-up" data-delay="<?php echo esc_attr( $i * 80 ); ?>">
						<a class="post-media" href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'beautia-card', array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?></a>
						<div class="post-body">
							<span class="post-date"><?php beautia_icon( 'calendar', 14 ); ?><?php echo esc_html( beautia_format_date( get_the_date( 'Y-m-d' ) ) ); ?></span>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
							<a class="link-arrow" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read more', 'beautia' ); ?><?php beautia_icon( 'chevron', 16 ); ?></a>
						</div>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
	<?php
}

/** Closing CTA band. */
function beautia_section_cta() {
	if ( ! get_theme_mod( 'beautia_footer_cta', true ) ) {
		return;
	}
	$sms_note = Beautia_SMS::has_live_gateway()
		? __( 'Choose a service, pick a free slot, verify your mobile — done. You will receive an SMS at every step.', 'beautia' )
		: __( 'Choose a service, pick a free slot, verify your mobile — done. SMS notifications start as soon as you connect your gateway.', 'beautia' );
	?>
	<section class="cta-band">
		<div class="container cta-in" data-anim="fade-up">
			<div>
				<span class="eyebrow"><?php beautia_icon( 'sparkle', 16 ); ?><?php esc_html_e( 'Ready when you are', 'beautia' ); ?></span>
				<h2><?php esc_html_e( 'Book your next appointment in 60 seconds', 'beautia' ); ?></h2>
				<p><?php echo esc_html( $sms_note ); ?></p>
			</div>
			<div class="cta-actions">
				<?php beautia_book_button( __( 'Book now', 'beautia' ), 'btn btn-primary btn-lg' ); ?>
				<?php $phone = get_theme_mod( 'beautia_phone', beautia_get_option( 'phone', '' ) ); ?>
				<?php if ( $phone ) : ?>
					<a class="btn btn-ghost btn-lg" href="tel:<?php echo esc_attr( beautia_fa_to_en_digits($phone) ); ?>"><?php beautia_icon( 'phone', 18 ); ?><span><?php echo esc_html( $phone ); ?></span></a>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}

/** Mobile sticky bar. */
function beautia_sticky_cta() {
	if ( ! get_theme_mod( 'beautia_enable_sticky_cta', true ) ) {
		return;
	}
	$phone = get_theme_mod( 'beautia_phone', beautia_get_option( 'phone', '' ) );
	?>
	<div class="sticky-cta">
		<?php if ( $phone ) : ?>
			<a href="tel:<?php echo esc_attr( beautia_fa_to_en_digits($phone) ); ?>"><?php beautia_icon( 'phone', 20 ); ?><span><?php esc_html_e( 'Call', 'beautia' ); ?></span></a>
		<?php endif; ?>
		<a href="<?php echo esc_url( beautia_get_page_url( 'account' ) ); ?>"><?php beautia_icon( 'user', 20 ); ?><span><?php esc_html_e( 'Panel', 'beautia' ); ?></span></a>
		<a class="primary" href="<?php echo esc_url( beautia_get_page_url( 'booking' ) ); ?>"><?php beautia_icon( 'calendar', 20 ); ?><span><?php esc_html_e( 'Book', 'beautia' ); ?></span></a>
	</div>
	<?php
}

/* -------------------------------------------------------------------------
 * Booking wizard, login form and account panel
 * ---------------------------------------------------------------------- */

/** Multi-step booking wizard. */
function beautia_booking_wizard() {
	$services   = get_posts( array( 'post_type' => 'beautia_service', 'posts_per_page' => 60 ) );
	$preselect  = isset( $_GET['service'] ) ? absint( $_GET['service'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
	$user       = wp_get_current_user();
	$user_name  = is_user_logged_in() ? $user->display_name : '';
	$user_phone = is_user_logged_in() ? Beautia_OTP::get_mobile( $user->ID ) : '';
	?>
	<div class="booking-wizard" id="beautia-booking"
		data-require-login="<?php echo esc_attr( beautia_get_option( 'require_login_to_book', true ) ? '1' : '0' ); ?>">

		<ol class="wizard-steps">
			<li class="is-active" data-step="1"><span>۱</span><?php esc_html_e( 'Service', 'beautia' ); ?></li>
			<li data-step="2"><span>۲</span><?php esc_html_e( 'Specialist', 'beautia' ); ?></li>
			<li data-step="3"><span>۳</span><?php esc_html_e( 'Date & time', 'beautia' ); ?></li>
			<li data-step="4"><span>۴</span><?php esc_html_e( 'Verify', 'beautia' ); ?></li>
			<li data-step="5"><span>۵</span><?php esc_html_e( 'Done', 'beautia' ); ?></li>
		</ol>

		<div class="wizard-body">

			<!-- Step 1 -->
			<section class="wizard-pane is-active" data-pane="1">
				<h3><?php esc_html_e( 'Which service would you like?', 'beautia' ); ?></h3>
				<div class="service-picker">
					<?php foreach ( $services as $service ) : ?>
						<label class="pick <?php echo $preselect === $service->ID ? 'is-selected' : ''; ?>">
							<input type="radio" name="service_id" value="<?php echo esc_attr( $service->ID ); ?>" <?php checked( $preselect, $service->ID ); ?> />
							<span class="pick-ico"><?php beautia_icon( get_post_meta( $service->ID, '_beautia_icon', true ), 22 ); ?></span>
							<span class="pick-body">
								<strong><?php echo esc_html( $service->post_title ); ?></strong>
								<em><?php echo esc_html( beautia_duration( get_post_meta( $service->ID, '_beautia_duration', true ) ) ); ?> · <?php echo esc_html( beautia_price( get_post_meta( $service->ID, '_beautia_price', true ) ) ); ?></em>
							</span>
							<span class="pick-check"><?php beautia_icon( 'check', 20 ); ?></span>
						</label>
					<?php endforeach; ?>
					<?php if ( ! $services ) : ?>
						<p class="empty"><?php esc_html_e( 'No bookable services found. Import a demo or create services first.', 'beautia' ); ?></p>
					<?php endif; ?>
				</div>
				<div class="wizard-actions">
					<button class="btn btn-primary" data-next="2"><span><?php esc_html_e( 'Continue', 'beautia' ); ?></span><?php beautia_icon( 'arrow', 18, 'btn-ico' ); ?></button>
				</div>
			</section>

			<!-- Step 2 -->
			<section class="wizard-pane" data-pane="2">
				<h3><?php esc_html_e( 'Choose your specialist', 'beautia' ); ?></h3>
				<div class="staff-picker" id="beautia-staff-list">
					<label class="pick is-selected">
						<input type="radio" name="staff_id" value="0" checked />
						<span class="pick-ico"><?php beautia_icon( 'users', 22 ); ?></span>
						<span class="pick-body"><strong><?php esc_html_e( 'Any available specialist', 'beautia' ); ?></strong><em><?php esc_html_e( 'Fastest availability', 'beautia' ); ?></em></span>
						<span class="pick-check"><?php beautia_icon( 'check', 20 ); ?></span>
					</label>
				</div>
				<div class="wizard-actions">
					<button class="btn btn-ghost" data-prev="1"><span><?php esc_html_e( 'Back', 'beautia' ); ?></span></button>
					<button class="btn btn-primary" data-next="3"><span><?php esc_html_e( 'Continue', 'beautia' ); ?></span><?php beautia_icon( 'arrow', 18, 'btn-ico' ); ?></button>
				</div>
			</section>

			<!-- Step 3 -->
			<section class="wizard-pane" data-pane="3">
				<h3><?php esc_html_e( 'Pick a date and a free slot', 'beautia' ); ?></h3>
				<div class="datepicker" id="beautia-datepicker">
					<div class="dp-head">
						<button class="dp-prev" type="button" aria-label="ماه قبل"><?php beautia_icon( 'chevron', 18 ); ?></button>
						<strong class="dp-title"></strong>
						<button class="dp-next" type="button" aria-label="ماه بعد"><?php beautia_icon( 'chevron', 18 ); ?></button>
					</div>
					<div class="dp-days"></div>
				</div>
				<div class="slots" id="beautia-slots">
					<p class="hint"><?php esc_html_e( 'Select a day to see the available times.', 'beautia' ); ?></p>
				</div>
				<div class="wizard-actions">
					<button class="btn btn-ghost" data-prev="2"><span><?php esc_html_e( 'Back', 'beautia' ); ?></span></button>
					<button class="btn btn-primary" data-next="4"><span><?php esc_html_e( 'Continue', 'beautia' ); ?></span><?php beautia_icon( 'arrow', 18, 'btn-ico' ); ?></button>
				</div>
			</section>

			<!-- Step 4 -->
			<section class="wizard-pane" data-pane="4">
				<div class="verify-grid">
					<div class="verify-form">
						<h3><?php esc_html_e( 'Confirm your details', 'beautia' ); ?></h3>
						<label><span><?php esc_html_e( 'Full name', 'beautia' ); ?></span>
							<input type="text" id="bk-name" value="<?php echo esc_attr( $user_name ); ?>" placeholder="<?php esc_attr_e( 'e.g. Sara Ahmadi', 'beautia' ); ?>" />
						</label>
						<label><span><?php esc_html_e( 'Mobile number', 'beautia' ); ?></span>
							<input type="tel" id="bk-mobile" inputmode="numeric" value="<?php echo esc_attr( $user_phone ); ?>" placeholder="0912 000 0000" <?php echo $user_phone ? 'readonly' : ''; ?> />
						</label>
						<label><span><?php esc_html_e( 'Notes (optional)', 'beautia' ); ?></span>
							<textarea id="bk-note" rows="3" placeholder="<?php esc_attr_e( 'Allergies, inspiration photos, preferences…', 'beautia' ); ?>"></textarea>
						</label>

						<?php if ( ! is_user_logged_in() ) : ?>
							<div class="otp-box" id="bk-otp">
								<button class="btn btn-outline btn-block" type="button" id="bk-send-code">
									<?php beautia_icon( 'message', 18 ); ?><span><?php esc_html_e( 'Send verification code', 'beautia' ); ?></span>
								</button>
								<div class="otp-inputs" hidden>
									<p class="otp-hint"></p>
									<div class="otp-digits" data-length="<?php echo esc_attr( beautia_get_option( 'otp_length', 5 ) ); ?>"></div>
									<button class="btn btn-primary btn-block" type="button" id="bk-verify-code"><span><?php esc_html_e( 'Verify', 'beautia' ); ?></span></button>
									<button class="btn-link" type="button" id="bk-resend" disabled></button>
								</div>
							</div>
						<?php endif; ?>

						<div class="msg" id="bk-msg" role="status"></div>
						<div class="wizard-actions">
							<button class="btn btn-ghost" data-prev="3"><span><?php esc_html_e( 'Back', 'beautia' ); ?></span></button>
							<button class="btn btn-primary" id="bk-submit"><span><?php esc_html_e( 'Confirm booking', 'beautia' ); ?></span><?php beautia_icon( 'check', 18, 'btn-ico' ); ?></button>
						</div>
					</div>
					<aside class="verify-summary" id="bk-summary">
						<h4><?php esc_html_e( 'Your appointment', 'beautia' ); ?></h4>
						<ul>
							<li><?php beautia_icon( 'sparkle', 16 ); ?><span data-sum="service">—</span></li>
							<li><?php beautia_icon( 'user', 16 ); ?><span data-sum="staff">—</span></li>
							<li><?php beautia_icon( 'calendar', 16 ); ?><span data-sum="date">—</span></li>
							<li><?php beautia_icon( 'clock', 16 ); ?><span data-sum="time">—</span></li>
							<li class="total"><?php beautia_icon( 'wallet', 16 ); ?><span data-sum="price">—</span></li>
						</ul>
						<p class="policy">
							<?php
							echo esc_html( beautia_en_to_fa_digits( sprintf(
								/* translators: %d hours */
								__( 'Free cancellation up to %d hours before your appointment.', 'beautia' ),
								(int) beautia_get_option( 'cancel_hours', 6 )
							) ) );
							?>
						</p>
					</aside>
				</div>
			</section>

			<!-- Step 5 -->
			<section class="wizard-pane wizard-done" data-pane="5">
				<div class="done-mark"><?php beautia_icon( 'check', 44 ); ?></div>
				<h3><?php esc_html_e( 'Your appointment is registered!', 'beautia' ); ?></h3>
				<p id="bk-done-text"></p>
				<div class="done-code"><?php esc_html_e( 'Tracking code:', 'beautia' ); ?> <strong id="bk-code"></strong></div>
				<div class="wizard-actions center">
					<a class="btn btn-primary" href="<?php echo esc_url( beautia_get_page_url( 'account' ) ); ?>"><span><?php esc_html_e( 'Open my panel', 'beautia' ); ?></span></a>
					<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span><?php esc_html_e( 'Back to home', 'beautia' ); ?></span></a>
				</div>
			</section>
		</div>
	</div>
	<?php
}

/** OTP login form. */
function beautia_login_form() {
	if ( is_user_logged_in() ) {
		?>
		<div class="auth-card">
			<h2><?php esc_html_e( 'You are logged in', 'beautia' ); ?></h2>
			<p><?php echo esc_html( wp_get_current_user()->display_name ); ?></p>
			<a class="btn btn-primary" href="<?php echo esc_url( beautia_get_page_url( 'account' ) ); ?>"><span><?php esc_html_e( 'Go to my panel', 'beautia' ); ?></span></a>
		</div>
		<?php
		return;
	}
	?>
	<div class="auth-card" id="beautia-auth">
		<span class="auth-ico"><?php beautia_icon( 'mobile', 30 ); ?></span>
		<h2><?php esc_html_e( 'Login or sign up', 'beautia' ); ?></h2>
		<p class="auth-sub"><?php esc_html_e( 'Enter your mobile number, we will text you a one-time code. No password required.', 'beautia' ); ?></p>

		<div class="auth-step" data-auth-step="mobile">
			<label>
				<span><?php esc_html_e( 'Mobile number', 'beautia' ); ?></span>
				<input type="tel" id="auth-mobile" inputmode="numeric" placeholder="0912 000 0000" autocomplete="tel" />
			</label>
			<button class="btn btn-primary btn-block" id="auth-send"><span><?php esc_html_e( 'Send code', 'beautia' ); ?></span><?php beautia_icon( 'arrow', 18, 'btn-ico' ); ?></button>
		</div>

		<div class="auth-step" data-auth-step="code" hidden>
			<p class="auth-sent"></p>
			<div class="otp-digits" data-length="<?php echo esc_attr( beautia_get_option( 'otp_length', 5 ) ); ?>"></div>
			<label class="auth-name" hidden>
				<span><?php esc_html_e( 'Your name', 'beautia' ); ?></span>
				<input type="text" id="auth-name" placeholder="<?php esc_attr_e( 'e.g. Sara', 'beautia' ); ?>" />
			</label>
			<button class="btn btn-primary btn-block" id="auth-verify"><span><?php esc_html_e( 'Verify & continue', 'beautia' ); ?></span></button>
			<div class="auth-foot">
				<button class="btn-link" id="auth-resend" disabled></button>
				<button class="btn-link" id="auth-change"><?php esc_html_e( 'Change number', 'beautia' ); ?></button>
			</div>
		</div>

		<div class="msg" id="auth-msg" role="status"></div>
		<p class="auth-terms"><?php beautia_icon( 'shield', 14 ); ?><?php esc_html_e( 'Your number is only used for booking notifications.', 'beautia' ); ?></p>
	</div>
	<?php
}

/**
 * About / why-us split with an image collage and a checklist.
 */
function beautia_section_about( $args = array() ) {
	$demo = beautia_get_active_demo();
	$args = wp_parse_args(
		$args,
		array(
			'eyebrow' => __( 'About the studio', 'beautia' ),
			'title'   => __( 'A team obsessed with the details', 'beautia' ),
			'text'    => __( 'We are a small, specialised team. Every tool is sterilised, every product is professional grade, and every appointment gets the time it actually needs — never a rushed queue.', 'beautia' ),
			'points'  => array(
				__( 'Hospital-grade sterilisation and single-use tools', 'beautia' ),
				__( 'Specialists matched to your treatment, not whoever is free', 'beautia' ),
				__( 'Transparent pricing — the price you see is the price you pay', 'beautia' ),
				__( 'A written aftercare plan sent to you by SMS', 'beautia' ),
			),
			'image'   => beautia_img( 'hero-' . $demo . '.jpg', 'hero-nails.jpg' ),
			'image2'  => beautia_img( 'svc-nail-2.jpg' ),
			'years'   => '14',
		)
	);
	?>
	<section class="section about-split" id="about">
		<div class="container about-grid">
			<div class="about-media" data-anim="fade-up">
				<div class="about-img about-img-main"><img src="<?php echo esc_url( $args['image'] ); ?>" alt="" loading="lazy" decoding="async" /></div>
				<div class="about-img about-img-sub"><img src="<?php echo esc_url( $args['image2'] ); ?>" alt="" loading="lazy" decoding="async" /></div>
				<div class="about-chip">
					<strong><?php echo esc_html( beautia_use_persian_digits() ? beautia_en_to_fa_digits( $args['years'] ) : $args['years'] ); ?>+</strong>
					<span><?php esc_html_e( 'years of experience', 'beautia' ); ?></span>
				</div>
			</div>
			<div class="about-copy" data-anim="fade-up" data-delay="120">
				<span class="eyebrow"><?php beautia_icon( 'sparkle', 16 ); ?><?php echo esc_html( $args['eyebrow'] ); ?></span>
				<h2 class="sec-title"><?php echo esc_html( $args['title'] ); ?></h2>
				<p class="sec-text"><?php echo esc_html( $args['text'] ); ?></p>
				<ul class="check-list">
					<?php foreach ( $args['points'] as $point ) : ?>
						<li><?php beautia_icon( 'check', 20 ); ?><span><?php echo esc_html( $point ); ?></span></li>
					<?php endforeach; ?>
				</ul>
				<a class="btn btn-primary" href="<?php echo esc_url( beautia_get_page_url( 'booking' ) ); ?>">
					<span><?php esc_html_e( 'Book an appointment', 'beautia' ); ?></span><?php beautia_icon( 'arrow', 18, 'btn-ico' ); ?>
				</a>
			</div>
		</div>
	</section>
	<?php
}

/**
 * "How it works" — three steps from phone to chair.
 */
function beautia_section_steps() {
	$steps = array(
		array( 'calendar', __( 'Pick a service and a free slot', 'beautia' ), __( 'Real availability, calculated from each specialist’s own schedule.', 'beautia' ) ),
		array( 'mobile', __( 'Verify your mobile number', 'beautia' ), __( 'A one-time code by SMS — no account, no password to remember.', 'beautia' ) ),
		array( 'check', __( 'Get your confirmation SMS', 'beautia' ), __( 'Plus a reminder the day before, and everything visible in your panel.', 'beautia' ) ),
	);
	?>
	<section class="section steps-band" id="how-it-works">
		<div class="container">
			<?php beautia_heading( __( 'Booking in 60 seconds', 'beautia' ), __( 'How it works', 'beautia' ) ); ?>
			<div class="grid grid-3 steps-grid">
				<?php foreach ( $steps as $i => $step ) : ?>
					<div class="step-card" data-anim="fade-up" data-delay="<?php echo esc_attr( $i * 110 ); ?>">
						<span class="step-num"><?php echo esc_html( beautia_use_persian_digits() ? beautia_en_to_fa_digits( $i + 1 ) : $i + 1 ); ?></span>
						<span class="step-ico"><?php beautia_icon( $step[0], 24 ); ?></span>
						<h3><?php echo esc_html( $step[1] ); ?></h3>
						<p><?php echo esc_html( $step[2] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * FAQ accordion (details/summary — no JavaScript required).
 */
function beautia_section_faq( $items = array() ) {
	if ( empty( $items ) ) {
		$items = array(
			array( __( 'How do I book an appointment?', 'beautia' ), __( 'Choose your service, your specialist and a free slot, then confirm your mobile number with the code we text you. The whole flow takes about a minute and needs no account.', 'beautia' ) ),
			array( __( 'Do I need a password to log in?', 'beautia' ), __( 'No. Beautia uses your mobile number and a one-time SMS code. Your number is your identity, and the code is valid for a few minutes only.', 'beautia' ) ),
			array( __( 'Can I cancel or move my appointment?', 'beautia' ), __( 'Yes — from your customer panel, up to the cancellation window set by the salon. You will receive an SMS confirming the change immediately.', 'beautia' ) ),
			array( __( 'Will I be reminded before my visit?', 'beautia' ), __( 'Yes. An automatic reminder is sent the day before, and a follow-up message after the visit with aftercare advice.', 'beautia' ) ),
			array( __( 'What if I arrive late?', 'beautia' ), __( 'Please call us. We hold your slot for 10 minutes; after that the appointment may need to be shortened or moved so the next client is not delayed.', 'beautia' ) ),
		);
	}
	?>
	<section class="section faq" id="faq">
		<div class="container faq-grid">
			<div class="faq-intro" data-anim="fade-up">
				<span class="eyebrow"><?php beautia_icon( 'message', 16 ); ?><?php esc_html_e( 'Good to know', 'beautia' ); ?></span>
				<h2 class="sec-title"><?php esc_html_e( 'Frequently asked questions', 'beautia' ); ?></h2>
				<p class="sec-text"><?php esc_html_e( 'Still unsure about something? Call us — we would rather answer a question than have you guess.', 'beautia' ); ?></p>
				<a class="btn btn-outline" href="tel:<?php echo esc_attr( beautia_fa_to_en_digits(beautia_get_option( 'phone', '' )) ); ?>">
					<?php beautia_icon( 'phone', 18 ); ?><span><?php echo esc_html( beautia_get_option( 'phone', '' ) ); ?></span>
				</a>
			</div>
			<div class="faq-list" data-anim="fade-up" data-delay="120">
				<?php foreach ( $items as $i => $item ) : ?>
					<details class="faq-item"<?php echo 0 === $i ? ' open' : ''; ?>>
						<summary><span><?php echo esc_html( $item[0] ); ?></span><?php beautia_icon( 'chevron', 18 ); ?></summary>
						<div class="faq-body"><p><?php echo esc_html( $item[1] ); ?></p></div>
					</details>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * A single reusable service card (used by the services grid and by the
 * "related services" block on a single service page).
 */
function beautia_service_card( $post_id = 0, $delay = 0 ) {
	$post_id  = $post_id ? $post_id : get_the_ID();
	$icon     = get_post_meta( $post_id, '_beautia_icon', true );
	$price    = get_post_meta( $post_id, '_beautia_price', true );
	$duration = get_post_meta( $post_id, '_beautia_duration', true );
	?>
	<article class="service-card tilt" data-anim="fade-up" data-delay="<?php echo esc_attr( $delay ); ?>">
		<div class="service-media">
			<?php if ( has_post_thumbnail( $post_id ) ) : ?>
				<?php echo get_the_post_thumbnail( $post_id, 'beautia-card', array( 'alt' => get_the_title( $post_id ) ) ); ?>
			<?php endif; ?>
			<span class="service-icon"><?php beautia_icon( $icon ? $icon : 'sparkle', 22 ); ?></span>
		</div>
		<div class="service-body">
			<h3><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h3>
			<p><?php echo esc_html( wp_trim_words( get_the_excerpt( $post_id ), 18 ) ); ?></p>
			<div class="service-meta">
				<span><?php beautia_icon( 'clock', 16 ); ?><?php echo esc_html( beautia_duration( $duration ) ); ?></span>
				<span class="price"><?php echo esc_html( beautia_price( $price ) ); ?></span>
			</div>
			<div class="service-actions">
				<?php beautia_book_button( __( 'Book', 'beautia' ), 'btn btn-sm btn-primary', $post_id ); ?>
				<a class="link-arrow" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php esc_html_e( 'Details', 'beautia' ); ?><?php beautia_icon( 'chevron', 16 ); ?></a>
			</div>
		</div>
	</article>
	<?php
}

/**
 * Floating booking drawer — available on every page of the site.
 * Opens with the round button in the corner and posts through the same
 * AJAX endpoints the full wizard uses.
 */
function beautia_booking_drawer() {
	if ( ! beautia_get_option( 'booking_drawer', 1 ) ) {
		return;
	}
	$services = get_posts(
		array(
			'post_type'      => 'beautia_service',
			'posts_per_page' => 20,
			'meta_key'       => '_beautia_bookable', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => '1',                 // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	if ( ! $services ) {
		$services = get_posts( array( 'post_type' => 'beautia_service', 'posts_per_page' => 20 ) );
	}
	?>
	<button class="fab-book" id="beautia-fab" type="button" aria-controls="beautia-drawer" aria-expanded="false">
		<?php beautia_icon( 'calendar', 22 ); ?>
		<span class="fab-label"><?php esc_html_e( 'Quick booking', 'beautia' ); ?></span>
		<span class="fab-pulse" aria-hidden="true"></span>
	</button>

	<div class="drawer" id="beautia-drawer" hidden>
		<div class="drawer-backdrop" data-drawer-close></div>
		<aside class="drawer-panel" role="dialog" aria-modal="true" aria-labelledby="beautia-drawer-title">
			<header class="drawer-head">
				<h2 id="beautia-drawer-title"><?php beautia_icon( 'calendar', 20 ); ?><?php esc_html_e( 'Quick booking', 'beautia' ); ?></h2>
				<button class="drawer-close" type="button" data-drawer-close aria-label="<?php esc_attr_e( 'Close', 'beautia' ); ?>"><?php beautia_icon( 'close', 22 ); ?></button>
			</header>

			<div class="drawer-body">
				<p class="drawer-note"><?php beautia_icon( 'shield', 15 ); ?><?php esc_html_e( 'Choose a service and a day — we will show you the free times immediately.', 'beautia' ); ?></p>

				<label>
					<span><?php esc_html_e( 'Service', 'beautia' ); ?></span>
					<select id="drawer-service">
						<?php foreach ( $services as $service ) : ?>
							<option value="<?php echo esc_attr( $service->ID ); ?>">
								<?php echo esc_html( get_the_title( $service ) ); ?> — <?php echo esc_html( beautia_price( get_post_meta( $service->ID, '_beautia_price', true ) ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</label>

				<label>
					<span><?php esc_html_e( 'Date', 'beautia' ); ?></span>
					<span class="field-date"><?php beautia_icon( 'calendar', 18 ); ?><input type="text" id="drawer-date" data-jdate data-min="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>" data-value="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>" /></span>
				</label>

				<div class="drawer-slots" id="drawer-slots">
					<p class="hint"><?php esc_html_e( 'Select a day to see the available times.', 'beautia' ); ?></p>
				</div>

				<a class="btn btn-primary btn-block" id="drawer-continue" href="<?php echo esc_url( beautia_get_page_url( 'booking' ) ); ?>">
					<span><?php esc_html_e( 'Continue to booking', 'beautia' ); ?></span><?php beautia_icon( 'arrow', 18, 'btn-ico' ); ?>
				</a>

				<?php $phone = beautia_get_option( 'phone', '' ); ?>
				<?php if ( $phone ) : ?>
					<a class="drawer-call" href="tel:<?php echo esc_attr( beautia_fa_to_en_digits($phone) ); ?>"><?php beautia_icon( 'phone', 16 ); ?><span><?php esc_html_e( 'Or call us', 'beautia' ); ?> <?php echo esc_html( $phone ); ?></span></a>
				<?php endif; ?>
			</div>
		</aside>
	</div>
	<?php
}
