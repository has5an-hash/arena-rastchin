<?php
/**
 * Panel: past appointments.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

$rows = Beautia_Booking::query(
	array(
		'user_id' => get_current_user_id(),
		'to'      => current_time( 'mysql' ),
		'limit'   => 100,
	)
);
?>
<h2 class="panel-title"><?php esc_html_e( 'Visit history', 'beautia' ); ?></h2>
<table class="panel-table">
	<thead>
		<tr>
			<th><?php esc_html_e( 'Date', 'beautia' ); ?></th>
			<th><?php esc_html_e( 'Service', 'beautia' ); ?></th>
			<th><?php esc_html_e( 'Specialist', 'beautia' ); ?></th>
			<th><?php esc_html_e( 'Price', 'beautia' ); ?></th>
			<th><?php esc_html_e( 'Status', 'beautia' ); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
	<?php if ( ! $rows ) : ?>
		<tr><td colspan="6"><?php esc_html_e( 'No visits recorded yet.', 'beautia' ); ?></td></tr>
	<?php endif; ?>
	<?php foreach ( $rows as $row ) : ?>
		<tr>
			<td><?php echo esc_html( beautia_format_date( $row->start_at, true ) ); ?></td>
			<td><?php echo esc_html( get_the_title( $row->service_id ) ); ?></td>
			<td><?php echo esc_html( $row->staff_id ? get_the_title( $row->staff_id ) : '—' ); ?></td>
			<td><?php echo esc_html( beautia_price( $row->price ) ); ?></td>
			<td><span class="status is-<?php echo esc_attr( $row->status ); ?>"><?php echo esc_html( Beautia_Booking::statuses()[ $row->status ] ); ?></span></td>
			<td class="cell-actions">
				<a class="btn-link" href="<?php echo esc_url( add_query_arg( 'service', $row->service_id, beautia_get_page_url( 'booking' ) ) ); ?>"><?php esc_html_e( 'Book again', 'beautia' ); ?></a>
				<?php
				$rated = get_option( 'beautia_review_' . $row->id );
				if ( Beautia_Booking::STATUS_COMPLETED === $row->status ) :
					if ( $rated ) :
						?>
						<span class="rated"><?php echo esc_html( beautia_num( $rated ) ); ?>/<?php echo esc_html( beautia_num( 5 ) ); ?><?php beautia_icon( 'star', 14 ); ?></span>
					<?php else : ?>
						<button type="button" class="btn-link js-review" data-id="<?php echo esc_attr( $row->id ); ?>"><?php beautia_icon( 'star', 14 ); ?><?php esc_html_e( 'Rate this visit', 'beautia' ); ?></button>
					<?php endif; ?>
				<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<div class="review-modal" id="beautia-review" hidden>
	<div class="review-box" role="dialog" aria-modal="true" aria-labelledby="review-title">
		<button type="button" class="review-close" aria-label="<?php esc_attr_e( 'Close', 'beautia' ); ?>">&times;</button>
		<h3 id="review-title"><?php esc_html_e( 'How was your visit?', 'beautia' ); ?></h3>
		<p class="review-note"><?php esc_html_e( 'Your score is private; the text is published only after we approve it.', 'beautia' ); ?></p>
		<div class="rating-input" id="review-stars">
			<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
				<button type="button" class="star" data-value="<?php echo esc_attr( $i ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: number of stars. */ __( '%d stars', 'beautia' ), $i ) ); ?>"><?php beautia_icon( 'star', 30 ); ?></button>
			<?php endfor; ?>
		</div>
		<textarea id="review-text" rows="4" placeholder="<?php esc_attr_e( 'Tell us in a sentence or two (optional)', 'beautia' ); ?>"></textarea>
		<button type="button" class="btn btn-primary btn-block" id="review-send"><span><?php esc_html_e( 'Send review', 'beautia' ); ?></span></button>
		<p class="msg" id="review-msg"></p>
	</div>
</div>
