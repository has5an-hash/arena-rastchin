<?php
/**
 * Native visual mega menu — no premium menu plugin required.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

final class Beautia_Mega_Menu {
	/** Hooks. */
	public static function boot() {
		add_action( 'wp_nav_menu_item_custom_fields', array( __CLASS__, 'fields' ), 10, 5 );
		add_action( 'wp_update_nav_menu_item', array( __CLASS__, 'save' ), 10, 2 );
		add_filter( 'nav_menu_css_class', array( __CLASS__, 'classes' ), 10, 4 );
		add_filter( 'walker_nav_menu_start_el', array( __CLASS__, 'decorate' ), 10, 4 );
	}

	/** Menu item controls in Appearance → Menus. */
	public static function fields( $item_id, $item, $depth, $args, $current_object_id ) {
		unset( $item, $depth, $args, $current_object_id );
		$enabled = get_post_meta( $item_id, '_beautia_mega', true );
		$style   = get_post_meta( $item_id, '_beautia_mega_style', true );
		$icon    = get_post_meta( $item_id, '_beautia_menu_icon', true );
		$image   = get_post_meta( $item_id, '_beautia_menu_image', true );
		?>
		<div class="beautia-menu-fields" style="clear:both;padding:10px 0 4px">
			<p><label><input type="checkbox" name="beautia_mega[<?php echo esc_attr( $item_id ); ?>]" value="1" <?php checked( $enabled, '1' ); ?>> <?php esc_html_e( 'Enable Beautia mega menu for this parent item', 'beautia' ); ?></label></p>
			<p><label><?php esc_html_e( 'Mega menu style', 'beautia' ); ?><br><select name="beautia_mega_style[<?php echo esc_attr( $item_id ); ?>]"><option value="grid" <?php selected( $style, 'grid' ); ?>><?php esc_html_e( 'Four-column grid', 'beautia' ); ?></option><option value="editorial" <?php selected( $style, 'editorial' ); ?>><?php esc_html_e( 'Editorial with images', 'beautia' ); ?></option><option value="compact" <?php selected( $style, 'compact' ); ?>><?php esc_html_e( 'Compact icon menu', 'beautia' ); ?></option></select></label></p>
			<p><label><?php esc_html_e( 'Beautia SVG icon name', 'beautia' ); ?><br><input class="widefat" type="text" name="beautia_menu_icon[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $icon ); ?>" placeholder="sparkle, calendar, heart, user"></label></p>
			<p><label><?php esc_html_e( 'Menu image URL', 'beautia' ); ?><br><input class="widefat" type="url" name="beautia_menu_image[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_url( $image ); ?>" placeholder="https://…"></label></p>
		</div>
		<?php
	}

	/** Save allow-listed item metadata. */
	public static function save( $menu_id, $menu_item_db_id ) {
		unset( $menu_id );
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}
		$mega = isset( $_POST['beautia_mega'][ $menu_item_db_id ] ) ? '1' : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_post_meta( $menu_item_db_id, '_beautia_mega', $mega );
		$style = isset( $_POST['beautia_mega_style'][ $menu_item_db_id ] ) ? sanitize_key( wp_unslash( $_POST['beautia_mega_style'][ $menu_item_db_id ] ) ) : 'grid'; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! in_array( $style, array( 'grid', 'editorial', 'compact' ), true ) ) {
			$style = 'grid';
		}
		update_post_meta( $menu_item_db_id, '_beautia_mega_style', $style );
		$icon = isset( $_POST['beautia_menu_icon'][ $menu_item_db_id ] ) ? sanitize_key( wp_unslash( $_POST['beautia_menu_icon'][ $menu_item_db_id ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_post_meta( $menu_item_db_id, '_beautia_menu_icon', $icon );
		$image = isset( $_POST['beautia_menu_image'][ $menu_item_db_id ] ) ? esc_url_raw( wp_unslash( $_POST['beautia_menu_image'][ $menu_item_db_id ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_post_meta( $menu_item_db_id, '_beautia_menu_image', $image );
	}

	/** Parent style classes drive the CSS layout. */
	public static function classes( $classes, $item, $args, $depth ) {
		unset( $args );
		if ( 0 === (int) $depth && get_post_meta( $item->ID, '_beautia_mega', true ) ) {
			$style     = get_post_meta( $item->ID, '_beautia_mega_style', true ) ?: 'grid';
			$classes[] = 'beautia-mega';
			$classes[] = 'beautia-mega-' . sanitize_html_class( $style );
		}
		if ( get_post_meta( $item->ID, '_beautia_menu_image', true ) ) {
			$classes[] = 'has-menu-image';
		}
		return $classes;
	}

	/** Place local SVG icons and optional images inside item links. */
	public static function decorate( $item_output, $item, $depth, $args ) {
		unset( $depth, $args );
		$icon  = sanitize_key( get_post_meta( $item->ID, '_beautia_menu_icon', true ) );
		$image = get_post_meta( $item->ID, '_beautia_menu_image', true );
		$media = '';
		if ( $image ) {
			$media .= '<span class="menu-item-image"><img src="' . esc_url( $image ) . '" alt="" loading="lazy" decoding="async"></span>';
		}
		if ( $icon && array_key_exists( $icon, beautia_icon_set() ) ) {
			$media .= '<span class="menu-item-icon" aria-hidden="true">' . beautia_get_icon( $icon, 20 ) . '</span>';
		}
		if ( $media ) {
			$item_output = preg_replace( '/(<a\b[^>]*>)/', '$1' . $media, $item_output, 1 );
		}
		return $item_output;
	}
}

Beautia_Mega_Menu::boot();
