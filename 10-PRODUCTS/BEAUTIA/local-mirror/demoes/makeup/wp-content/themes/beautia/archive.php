<?php
/**
 * Generic archive (also used by services / portfolio / team when no specific
 * template exists).
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="page-hero small">
	<div class="container">
		<h1><?php the_archive_title(); ?></h1>
		<?php the_archive_description( '<p>', '</p>' ); ?>
	</div>
</div>
<div class="container section">
	<div class="grid grid-3">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content/card' );
			endwhile;
		else :
			get_template_part( 'template-parts/content/none' );
		endif;
		?>
	</div>
	<?php the_posts_pagination(); ?>
</div>
<?php
get_footer();
