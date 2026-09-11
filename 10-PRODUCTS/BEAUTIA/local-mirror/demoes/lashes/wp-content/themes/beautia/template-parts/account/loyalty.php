<?php
/**
 * Panel: loyalty club.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

$stats = Beautia_Account::stats( get_current_user_id() );
$tiers = array(
	array( 'Bronze', 0, 0 ),
	array( 'Silver', 4, 7 ),
	array( 'Gold', 10, 12 ),
	array( 'Diamond', 20, 20 ),
);
?>
<h2 class="panel-title"><?php esc_html_e( 'Loyalty club', 'beautia' ); ?></h2>
<p class="panel-sub"><?php esc_html_e( 'Every completed visit moves you closer to a better tier and a bigger permanent discount.', 'beautia' ); ?></p>

<div class="tiers">
	<?php foreach ( $tiers as $tier ) : ?>
		<div class="tier <?php echo $stats['completed'] >= $tier[1] ? 'is-unlocked' : ''; ?>">
			<?php beautia_icon( 'award', 26 ); ?>
			<strong><?php echo esc_html( $tier[0] ); ?></strong>
			<span><?php printf( esc_html__( 'from %d visits', 'beautia' ), (int) $tier[1] ); ?></span>
			<em><?php printf( esc_html__( '%d%% off', 'beautia' ), (int) $tier[2] ); ?></em>
		</div>
	<?php endforeach; ?>
</div>

<div class="loyalty-bar">
	<div class="loyalty-head">
		<strong><?php printf( esc_html__( 'Completed visits: %d', 'beautia' ), (int) $stats['completed'] ); ?></strong>
		<span><?php echo esc_html( $stats['tier']['name'] ); ?></span>
	</div>
	<div class="loyalty-track"><i style="width:<?php echo esc_attr( min( 100, $stats['completed'] * 5 ) ); ?>%"></i></div>
</div>
