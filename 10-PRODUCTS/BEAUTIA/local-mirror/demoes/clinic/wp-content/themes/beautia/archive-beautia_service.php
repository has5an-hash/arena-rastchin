<?php
/**
 * Services archive — live search, category filter, sorting and a price table.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

get_header();

$terms = get_terms(
	array(
		'taxonomy'   => 'beautia_service_cat',
		'hide_empty' => true,
	)
);
?>
<div class="page-hero small">
	<div class="container">
		<nav class="crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'beautia' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'beautia' ); ?></a>
			<?php beautia_icon( 'chevron', 14 ); ?>
			<span><?php esc_html_e( 'Our services', 'beautia' ); ?></span>
		</nav>
		<h1><?php esc_html_e( 'Our services', 'beautia' ); ?></h1>
		<p><?php esc_html_e( 'Choose a treatment and book a free slot in seconds.', 'beautia' ); ?></p>
	</div>
</div>

<div class="container section">

	<div class="svc-toolbar" data-anim="fade-up">
		<label class="svc-search">
			<?php beautia_icon( 'search', 18 ); ?>
			<input type="search" id="svc-search" placeholder="<?php esc_attr_e( 'Search a service…', 'beautia' ); ?>" aria-label="<?php esc_attr_e( 'Search a service…', 'beautia' ); ?>" />
		</label>

		<?php if ( ! is_wp_error( $terms ) && $terms ) : ?>
			<div class="svc-filters filters" role="group" aria-label="<?php esc_attr_e( 'Filter by category', 'beautia' ); ?>">
				<button class="filter is-active" data-filter="*"><?php esc_html_e( 'All', 'beautia' ); ?></button>
				<?php foreach ( $terms as $term ) : ?>
					<button class="filter" data-filter="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<label class="svc-sort">
			<span class="screen-reader-text"><?php esc_html_e( 'Sort', 'beautia' ); ?></span>
			<select id="svc-sort">
				<option value="default"><?php esc_html_e( 'Default order', 'beautia' ); ?></option>
				<option value="price-asc"><?php esc_html_e( 'Cheapest first', 'beautia' ); ?></option>
				<option value="price-desc"><?php esc_html_e( 'Most expensive first', 'beautia' ); ?></option>
				<option value="time-asc"><?php esc_html_e( 'Shortest first', 'beautia' ); ?></option>
			</select>
		</label>
	</div>

	<p class="svc-count" id="svc-count" role="status"></p>

	<div class="grid grid-3 service-grid" id="svc-grid">
		<?php
		$i = 0;
		while ( have_posts() ) :
			the_post();
			$i++;
			$slugs = wp_get_post_terms( get_the_ID(), 'beautia_service_cat', array( 'fields' => 'slugs' ) );
			?>
			<div class="svc-item"
				data-cats="<?php echo esc_attr( implode( ' ', (array) $slugs ) ); ?>"
				data-title="<?php echo esc_attr( wp_strip_all_tags( get_the_title() ) ); ?>"
				data-price="<?php echo esc_attr( (float) get_post_meta( get_the_ID(), '_beautia_price', true ) ); ?>"
				data-time="<?php echo esc_attr( (int) get_post_meta( get_the_ID(), '_beautia_duration', true ) ); ?>">
				<?php beautia_service_card( get_the_ID(), min( $i * 60, 400 ) ); ?>
			</div>
			<?php
		endwhile;
		?>
	</div>

	<p class="svc-empty" id="svc-empty" hidden><?php esc_html_e( 'No service matches your filters.', 'beautia' ); ?></p>

	<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>

	<?php if ( ! is_wp_error( $terms ) && $terms ) : ?>
		<section class="section price-table-section">
			<?php beautia_heading( __( 'Transparent pricing', 'beautia' ), __( 'Full price list', 'beautia' ) ); ?>
			<div class="grid grid-3">
				<?php foreach ( array_slice( $terms, 0, 6 ) as $term ) : ?>
					<?php
					$rows = get_posts(
						array(
							'post_type'      => 'beautia_service',
							'posts_per_page' => 8,
							'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery
								array(
									'taxonomy' => 'beautia_service_cat',
									'field'    => 'term_id',
									'terms'    => $term->term_id,
								),
							),
						)
					);
					if ( ! $rows ) {
						continue;
					}
					?>
					<div class="price-card" data-anim="fade-up">
						<h3><?php echo esc_html( $term->name ); ?></h3>
						<ul>
							<?php foreach ( $rows as $row ) : ?>
								<li>
									<span class="pname"><a href="<?php echo esc_url( get_permalink( $row->ID ) ); ?>"><?php echo esc_html( get_the_title( $row ) ); ?></a></span>
									<span class="pdots"></span>
									<span class="pval"><?php echo esc_html( beautia_price( get_post_meta( $row->ID, '_beautia_price', true ) ) ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
						<?php beautia_book_button( __( 'Reserve a slot', 'beautia' ), 'btn btn-outline btn-block' ); ?>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>
</div>

<script>
/* Live filtering for the services archive — no page reloads, no plugin. */
(function () {
	var grid = document.getElementById('svc-grid');
	if (!grid) { return; }
	var items = Array.prototype.slice.call(grid.querySelectorAll('.svc-item'));
	var search = document.getElementById('svc-search');
	var sort = document.getElementById('svc-sort');
	var count = document.getElementById('svc-count');
	var empty = document.getElementById('svc-empty');
	var filters = Array.prototype.slice.call(document.querySelectorAll('.svc-filters .filter'));
	var state = { cat: '*', q: '' };
	var initialQuery = new URLSearchParams(window.location.search).get('service_search') || '';
	var tpl = <?php echo wp_json_encode( __( '%s services shown', 'beautia' ) ); ?>;
	var fa = <?php echo beautia_use_persian_digits() ? 'true' : 'false'; ?>;

	function num(n) {
		return fa ? String(n).replace(/[0-9]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[d]; }) : n;
	}
	function normalise(s) {
		return String(s).toLowerCase().replace(/\u064A/g, 'ی').replace(/\u0643/g, 'ک').replace(/\u200c/g, ' ');
	}
	function apply() {
		var shown = 0;
		items.forEach(function (el) {
			var okCat = state.cat === '*' || (' ' + el.dataset.cats + ' ').indexOf(' ' + state.cat + ' ') > -1;
			var okQ = !state.q || normalise(el.dataset.title).indexOf(state.q) > -1;
			var show = okCat && okQ;
			el.hidden = !show;
			if (show) { shown++; }
		});
		count.textContent = tpl.replace('%s', num(shown));
		empty.hidden = shown !== 0;
	}
	function sortBy(mode) {
		var sorted = items.slice();
		if (mode === 'price-asc') { sorted.sort(function (a, b) { return a.dataset.price - b.dataset.price; }); }
		else if (mode === 'price-desc') { sorted.sort(function (a, b) { return b.dataset.price - a.dataset.price; }); }
		else if (mode === 'time-asc') { sorted.sort(function (a, b) { return a.dataset.time - b.dataset.time; }); }
		sorted.forEach(function (el) { grid.appendChild(el); });
	}

	filters.forEach(function (b) {
		b.addEventListener('click', function () {
			filters.forEach(function (x) { x.classList.remove('is-active'); });
			b.classList.add('is-active');
			state.cat = b.dataset.filter;
			apply();
		});
	});
	if (search) {
		search.value = initialQuery;
		state.q = normalise(initialQuery.trim());
		search.addEventListener('input', function () { state.q = normalise(search.value.trim()); apply(); });
	}
	if (sort) {
		sort.addEventListener('change', function () { sortBy(sort.value); });
	}
	apply();
})();
</script>
<?php
get_footer();
