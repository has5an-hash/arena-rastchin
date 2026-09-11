<?php
/**
 * Template Name: Login (Mobile + OTP)
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container section auth-wrap">
	<?php beautia_login_form(); ?>
</div>
<?php
get_footer();
