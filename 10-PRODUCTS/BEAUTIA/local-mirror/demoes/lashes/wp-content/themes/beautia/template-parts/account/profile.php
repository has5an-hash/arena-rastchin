<?php
/**
 * Panel: profile.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

$user = wp_get_current_user();
?>
<h2 class="panel-title"><?php esc_html_e( 'My profile', 'beautia' ); ?></h2>
<form class="panel-form" id="beautia-profile">
	<div class="form-grid">
		<label><span><?php esc_html_e( 'First name', 'beautia' ); ?></span>
			<input type="text" name="first_name" value="<?php echo esc_attr( $user->first_name ); ?>" />
		</label>
		<label><span><?php esc_html_e( 'Last name', 'beautia' ); ?></span>
			<input type="text" name="last_name" value="<?php echo esc_attr( $user->last_name ); ?>" />
		</label>
		<label><span><?php esc_html_e( 'E-mail', 'beautia' ); ?></span>
			<input type="email" name="email" value="<?php echo esc_attr( $user->user_email ); ?>" />
		</label>
		<label><span><?php esc_html_e( 'Mobile (verified)', 'beautia' ); ?></span>
			<input type="text" value="<?php echo esc_attr( Beautia_OTP::get_mobile( $user->ID ) ); ?>" readonly />
		</label>
		<label><span><?php esc_html_e( 'Birthday (for your gift code)', 'beautia' ); ?></span>
			<input type="text" name="birthday" data-jdate data-value="<?php echo esc_attr( get_user_meta( $user->ID, 'beautia_birthday', true ) ); ?>" placeholder="<?php esc_attr_e( 'Choose a date', 'beautia' ); ?>" />
		</label>
	</div>
	<label class="check">
		<input type="checkbox" name="sms_optin" value="1" <?php checked( 1, (int) get_user_meta( $user->ID, 'beautia_sms_optin', true ) ); ?> />
		<span><?php esc_html_e( 'Send me offers and reminders by SMS', 'beautia' ); ?></span>
	</label>
	<button class="btn btn-primary" type="submit"><span><?php esc_html_e( 'Save changes', 'beautia' ); ?></span></button>
	<div class="msg" id="profile-msg" role="status"></div>
</form>
