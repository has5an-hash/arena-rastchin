<?php
/**
 * Search form.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<?php if (beautia_demo_context()): ?><input type="hidden" name="demo" value="<?php echo esc_attr(beautia_demo_context()); ?>" /><?php endif; ?>
	<label class="screen-reader-text"><?php esc_html_e( 'Search for:', 'beautia' ); ?></label>
	<input type="search" class="search-field" placeholder="<?php esc_attr_e( 'Search services, articles…', 'beautia' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" />
	<button type="submit" class="search-submit"><?php beautia_icon( 'search', 18 ); ?></button>
</form>
