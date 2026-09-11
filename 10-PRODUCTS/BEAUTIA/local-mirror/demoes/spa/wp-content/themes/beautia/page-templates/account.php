<?php
/**
 * Template Name: Customer Panel
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( ! is_user_logged_in() ) {
	echo '<div class="container section auth-wrap">';
	beautia_login_form();
	echo '</div>';
} else {
	get_template_part( 'template-parts/account/panel' );
}

get_footer();
