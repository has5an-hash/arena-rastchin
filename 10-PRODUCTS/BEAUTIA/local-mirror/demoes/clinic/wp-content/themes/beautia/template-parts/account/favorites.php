<?php
/**
 * Panel: favourites.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

$ids = Beautia_Account::favorites( get_current_user_id() );
?>
<h2 class="panel-title"><?php esc_html_e( 'Favourite services', 'beautia' ); ?></h2>
<?php if ( ! $ids ) : ?>
	<div class="panel-empty"><?php beautia_icon( 'heart', 32 ); ?><p><?php esc_html_e( 'You have not saved any service yet.', 'beautia' ); ?></p></div>
<?php else : ?>
	<div class="grid grid-3">
		<?php foreach ( $ids as $id ) : ?>
			<article class="service-card">
				<div class="service-media"><?php echo get_the_post_thumbnail( $id, 'beautia-card', array( 'alt' => get_the_title( $id ) ) ); ?></div>
				<div class="service-body">
					<h3><a href="<?php echo esc_url( get_permalink( $id ) ); ?>"><?php echo esc_html( get_the_title( $id ) ); ?></a></h3>
					<div class="service-meta">
						<span><?php beautia_icon( 'clock', 16 ); ?><?php echo esc_html( beautia_duration( get_post_meta( $id, '_beautia_duration', true ) ) ); ?></span>
						<span class="price"><?php echo esc_html( beautia_price( get_post_meta( $id, '_beautia_price', true ) ) ); ?></span>
					</div>
					<?php beautia_book_button( __( 'Book', 'beautia' ), 'btn btn-sm btn-primary', $id ); ?>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
