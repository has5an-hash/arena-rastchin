<?php
/**
 * Generic card used in loops.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'post-card' ); ?> data-anim="fade-up">
	<a class="post-media" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'beautia-card', array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?>
		<?php else : ?>
			<span class="post-ph"><?php beautia_icon( 'camera', 28 ); ?></span>
		<?php endif; ?>
	</a>
	<div class="post-body">
		<span class="post-date"><?php beautia_icon( 'calendar', 14 ); ?><?php echo esc_html( beautia_format_date( get_the_date( 'Y-m-d' ) ) ); ?></span>
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
		<a class="link-arrow" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read more', 'beautia' ); ?><?php beautia_icon( 'chevron', 16 ); ?></a>
	</div>
</article>
