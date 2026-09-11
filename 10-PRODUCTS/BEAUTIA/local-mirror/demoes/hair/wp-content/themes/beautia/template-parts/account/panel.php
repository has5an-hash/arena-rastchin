<?php
/**
 * Front-end customer panel.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

$user    = wp_get_current_user();
$user_id = $user->ID;
$tab     = Beautia_Account::current_tab();
$stats   = Beautia_Account::stats( $user_id );
$mobile  = Beautia_OTP::get_mobile( $user_id );
?>
<div class="account">
	<div class="container">
		<div class="account-head" data-anim="fade-up">
			<div class="account-user">
				<span class="avatar"><?php echo get_avatar( $user_id, 72 ); ?></span>
				<div>
					<h1><?php echo esc_html( $user->display_name ); ?></h1>
					<p><?php beautia_icon( 'mobile', 15 ); ?><?php echo esc_html( beautia_mask_mobile( $mobile ) ); ?>
						<span class="tier-chip"><?php beautia_icon( 'award', 14 ); ?><?php echo esc_html( $stats['tier']['name'] ); ?></span>
					</p>
				</div>
			</div>
			<?php beautia_book_button( __( 'New appointment', 'beautia' ), 'btn btn-primary' ); ?>
		</div>

		<div class="account-layout">
			<nav class="account-nav">
				<?php foreach ( Beautia_Account::tabs() as $key => $item ) : ?>
					<?php
					$url = 'logout' === $key
						? wp_nonce_url( add_query_arg( 'beautia_logout', 1, home_url( '/' ) ), 'beautia_logout' )
						: Beautia_Account::tab_url( $key );
					?>
					<a class="<?php echo $tab === $key ? 'is-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>">
						<?php beautia_icon( $item['icon'], 18 ); ?><span><?php echo esc_html( $item['label'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</nav>

			<div class="account-content">
				<?php get_template_part( 'template-parts/account/' . $tab ); ?>
			</div>
		</div>
	</div>
</div>
