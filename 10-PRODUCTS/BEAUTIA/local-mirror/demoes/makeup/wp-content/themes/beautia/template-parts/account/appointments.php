<?php
/**
 * Panel: upcoming appointments.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

$rows = Beautia_Booking::query(
	array(
		'user_id' => get_current_user_id(),
		'from'    => current_time( 'mysql' ),
		'order'   => 'ASC',
		'limit'   => 50,
	)
);
?>
<h2 class="panel-title"><?php esc_html_e( 'Upcoming appointments', 'beautia' ); ?></h2>

<?php if ( ! $rows ) : ?>
	<div class="panel-empty"><?php beautia_icon( 'calendar', 32 ); ?><p><?php esc_html_e( 'Nothing booked yet.', 'beautia' ); ?></p><?php beautia_book_button( __( 'Book now', 'beautia' ) ); ?></div>
<?php endif; ?>

<div class="appt-list">
	<?php foreach ( $rows as $row ) : ?>
		<article class="appt" data-anim="fade-up">
			<div class="appt-date">
				<strong><?php echo esc_html( gmdate( 'd', strtotime( $row->start_at ) ) ); ?></strong>
				<span><?php echo esc_html( beautia_format_date( $row->start_at ) ); ?></span>
			</div>
			<div class="appt-body">
				<h3><?php echo esc_html( get_the_title( $row->service_id ) ); ?></h3>
				<ul>
					<li><?php beautia_icon( 'clock', 15 ); ?><?php echo esc_html( gmdate( 'H:i', strtotime( $row->start_at ) ) . ' – ' . gmdate( 'H:i', strtotime( $row->end_at ) ) ); ?></li>
					<li><?php beautia_icon( 'user', 15 ); ?><?php echo esc_html( $row->staff_id ? get_the_title( $row->staff_id ) : __( 'Any specialist', 'beautia' ) ); ?></li>
					<li><?php beautia_icon( 'wallet', 15 ); ?><?php echo esc_html( beautia_price( $row->price ) ); ?></li>
					<li><code><?php echo esc_html( $row->code ); ?></code></li>
				</ul>
			</div>
			<div class="appt-side">
				<span class="status is-<?php echo esc_attr( $row->status ); ?>"><?php echo esc_html( Beautia_Booking::statuses()[ $row->status ] ); ?></span>
				<?php if ( Beautia_Booking::can_cancel( $row ) ) : ?>
					<button class="btn-link danger beautia-cancel" data-id="<?php echo esc_attr( $row->id ); ?>"><?php esc_html_e( 'Cancel', 'beautia' ); ?></button>
				<?php else : ?>
					<span class="muted"><?php esc_html_e( 'Call us to change', 'beautia' ); ?></span>
				<?php endif; ?>
			</div>
		</article>
	<?php endforeach; ?>
</div>
