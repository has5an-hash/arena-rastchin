<?php
/**
 * Panel: dashboard.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

$user_id = get_current_user_id();
$stats   = Beautia_Account::stats( $user_id );
$next    = Beautia_Booking::query(
	array(
		'user_id' => $user_id,
		'from'    => current_time( 'mysql' ),
		'order'   => 'ASC',
		'limit'   => 1,
	)
);
?>
<div class="panel-cards">
	<div class="panel-card" data-anim="fade-up"><?php beautia_icon( 'calendar', 22 ); ?><strong data-count="<?php echo esc_attr( $stats['upcoming'] ); ?>">0</strong><span><?php esc_html_e( 'Upcoming', 'beautia' ); ?></span></div>
	<div class="panel-card" data-anim="fade-up" data-delay="80"><?php beautia_icon( 'check', 22 ); ?><strong data-count="<?php echo esc_attr( $stats['completed'] ); ?>">0</strong><span><?php esc_html_e( 'Completed', 'beautia' ); ?></span></div>
	<div class="panel-card" data-anim="fade-up" data-delay="160"><?php beautia_icon( 'wallet', 22 ); ?><strong><?php echo esc_html( beautia_price( $stats['spent'] ) ); ?></strong><span><?php esc_html_e( 'Total spent', 'beautia' ); ?></span></div>
	<div class="panel-card" data-anim="fade-up" data-delay="240"><?php beautia_icon( 'award', 22 ); ?><strong><?php echo esc_html( $stats['tier']['name'] ); ?></strong><span><?php printf( esc_html__( '%d%% member discount', 'beautia' ), (int) $stats['tier']['discount'] ); ?></span></div>
</div>

<?php if ( $next ) : $row = $next[0]; ?>
	<div class="next-visit" data-anim="fade-up">
		<div class="next-left">
			<span class="eyebrow"><?php beautia_icon( 'sparkle', 15 ); ?><?php esc_html_e( 'Your next visit', 'beautia' ); ?></span>
			<h3><?php echo esc_html( get_the_title( $row->service_id ) ); ?></h3>
			<ul>
				<li><?php beautia_icon( 'calendar', 16 ); ?><?php echo esc_html( beautia_format_date( $row->start_at ) ); ?></li>
				<li><?php beautia_icon( 'clock', 16 ); ?><?php echo esc_html( gmdate( 'H:i', strtotime( $row->start_at ) ) ); ?></li>
				<li><?php beautia_icon( 'user', 16 ); ?><?php echo esc_html( $row->staff_id ? get_the_title( $row->staff_id ) : __( 'Any specialist', 'beautia' ) ); ?></li>
			</ul>
		</div>
		<div class="next-right">
			<span class="status is-<?php echo esc_attr( $row->status ); ?>"><?php echo esc_html( Beautia_Booking::statuses()[ $row->status ] ); ?></span>
			<code><?php echo esc_html( $row->code ); ?></code>
			<?php if ( Beautia_Booking::can_cancel( $row ) ) : ?>
				<button class="btn-link danger beautia-cancel" data-id="<?php echo esc_attr( $row->id ); ?>"><?php esc_html_e( 'Cancel', 'beautia' ); ?></button>
			<?php endif; ?>
		</div>
	</div>
<?php else : ?>
	<div class="panel-empty" data-anim="fade-up">
		<?php beautia_icon( 'calendar', 34 ); ?>
		<h3><?php esc_html_e( 'No upcoming appointment', 'beautia' ); ?></h3>
		<p><?php esc_html_e( 'Treat yourself — book your next visit now.', 'beautia' ); ?></p>
		<?php beautia_book_button( __( 'Book now', 'beautia' ) ); ?>
	</div>
<?php endif; ?>

<div class="loyalty-bar" data-anim="fade-up">
	<div class="loyalty-head">
		<strong><?php esc_html_e( 'Loyalty progress', 'beautia' ); ?></strong>
		<span>
			<?php
			if ( $stats['tier']['next'] ) {
				printf( esc_html__( '%d more visits to the next tier', 'beautia' ), (int) $stats['tier']['next'] );
			} else {
				esc_html_e( 'Top tier reached — enjoy!', 'beautia' );
			}
			?>
		</span>
	</div>
	<div class="loyalty-track"><i style="width:<?php echo esc_attr( min( 100, $stats['completed'] * 5 ) ); ?>%"></i></div>
</div>
