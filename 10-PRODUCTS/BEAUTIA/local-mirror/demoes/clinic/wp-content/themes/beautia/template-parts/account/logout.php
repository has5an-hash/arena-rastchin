<?php
/**
 * Panel: logout fallback.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="panel-empty">
	<?php beautia_icon( 'logout', 32 ); ?>
	<p><?php esc_html_e( 'Log out of your account?', 'beautia' ); ?></p>
	<a class="btn btn-primary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'beautia_logout', 1, home_url( '/' ) ), 'beautia_logout' ) ); ?>"><span><?php esc_html_e( 'Logout', 'beautia' ); ?></span></a>
</div>
