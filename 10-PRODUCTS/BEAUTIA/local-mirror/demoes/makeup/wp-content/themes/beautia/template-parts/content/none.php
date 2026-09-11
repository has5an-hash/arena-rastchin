<?php
/**
 * Nothing found.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="no-results">
	<?php beautia_icon( 'search', 32 ); ?>
	<h3><?php esc_html_e( 'Nothing found', 'beautia' ); ?></h3>
	<p><?php esc_html_e( 'Try another keyword or browse our services.', 'beautia' ); ?></p>
	<?php get_search_form(); ?>
</div>
