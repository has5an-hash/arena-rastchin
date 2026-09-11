<?php
/**
 * Portfolio archive.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="page-hero small">
	<div class="container">
		<h1><?php esc_html_e( 'Portfolio', 'beautia' ); ?></h1>
		<p><?php esc_html_e( 'A selection of our favourite transformations.', 'beautia' ); ?></p>
	</div>
</div>
<?php beautia_section_portfolio( array( 'count' => 24 ) ); ?>
<?php
get_footer();
