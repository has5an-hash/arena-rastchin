<?php
/**
 * Native performance and image controls.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

final class Beautia_Performance {
	/** Boot hooks. */
	public static function boot() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 30 );
		add_action( 'admin_post_beautia_save_performance', array( __CLASS__, 'save' ) );
		add_filter( 'wp_editor_set_quality', array( __CLASS__, 'quality' ) );
		add_filter( 'jpeg_quality', array( __CLASS__, 'quality' ) );
		add_filter( 'wp_lazy_loading_enabled', array( __CLASS__, 'lazy_loading' ), 10, 3 );
		add_filter( 'script_loader_tag', array( __CLASS__, 'defer_theme_scripts' ), 10, 3 );
		add_action( 'init', array( __CLASS__, 'trim_frontend' ) );
		add_action( 'wp_head', array( __CLASS__, 'preload_fonts' ), 1 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'motion_budget' ), 99 );

		if ( beautia_get_option( 'webp_uploads', 0 ) ) {
			add_filter( 'image_editor_output_format', array( __CLASS__, 'webp_format' ) );
		}
	}

	/** Add a dedicated settings screen without Redux or another framework. */
	public static function menu() {
		add_submenu_page(
			'beautia',
			__( 'Performance & Images', 'beautia' ),
			__( 'Performance & Images', 'beautia' ),
			'manage_options',
			'beautia-performance',
			array( __CLASS__, 'page' )
		);
	}

	/** Persist a strict allow-list. */
	public static function save() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'beautia' ) );
		}
		check_admin_referer( 'beautia_performance' );
		$quality = isset( $_POST['image_quality'] ) ? absint( $_POST['image_quality'] ) : 82;
		beautia_update_option( 'image_quality', min( 95, max( 60, $quality ) ) );
		foreach ( array( 'lazy_loading', 'webp_uploads', 'disable_emojis', 'disable_embeds', 'defer_scripts', 'preload_fonts', 'reduce_motion' ) as $key ) {
			beautia_update_option( $key, isset( $_POST[ $key ] ) ? 1 : 0 );
		}
		wp_safe_redirect( add_query_arg( array( 'page' => 'beautia-performance', 'updated' => 1 ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/** Settings UI. */
	public static function page() {
		?>
		<div class="wrap beautia-admin">
			<h1><?php esc_html_e( 'Performance & Images', 'beautia' ); ?></h1>
			<p><?php esc_html_e( 'Native controls for a faster site — no optimisation plugin required.', 'beautia' ); ?></p>
			<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'beautia' ); ?></p></div>
			<?php endif; ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="beautia_save_performance">
				<?php wp_nonce_field( 'beautia_performance' ); ?>
				<table class="form-table" role="presentation">
					<tr><th><label for="image_quality"><?php esc_html_e( 'Generated image quality', 'beautia' ); ?></label></th><td><input id="image_quality" name="image_quality" type="number" min="60" max="95" value="<?php echo esc_attr( beautia_get_option( 'image_quality', 82 ) ); ?>"> <span>%</span><p class="description"><?php esc_html_e( '82 is a strong balance between detail and file size. Existing images are unchanged until regenerated.', 'beautia' ); ?></p></td></tr>
					<?php
					$checks = array(
						'lazy_loading'   => array( __( 'Native lazy loading', 'beautia' ), __( 'Load below-the-fold images and frames only when needed.', 'beautia' ), 1 ),
						'webp_uploads'   => array( __( 'Create WebP for new JPEG uploads', 'beautia' ), __( 'Enabled only when the server image editor supports WebP. Transparent PNG files are not converted.', 'beautia' ), 0 ),
						'disable_emojis' => array( __( 'Remove WordPress emoji assets', 'beautia' ), __( 'Avoid an extra front-end script and stylesheet.', 'beautia' ), 1 ),
						'disable_embeds' => array( __( 'Remove unused embed discovery', 'beautia' ), __( 'Removes oEmbed discovery links and the embed host script.', 'beautia' ), 1 ),
						'defer_scripts'  => array( __( 'Defer Beautia scripts', 'beautia' ), __( 'Keeps parsing unblocked while preserving script order.', 'beautia' ), 1 ),
						'preload_fonts'  => array( __( 'Preload primary local fonts', 'beautia' ), __( 'Preloads the main Persian text and heading files.', 'beautia' ), 1 ),
						'reduce_motion'  => array( __( 'Performance motion budget', 'beautia' ), __( 'Disables decorative grain, cursor, parallax and long-running ambient motion.', 'beautia' ), 0 ),
					);
					foreach ( $checks as $key => $field ) :
						?>
						<tr><th><?php echo esc_html( $field[0] ); ?></th><td><label><input type="checkbox" name="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( 1, (int) beautia_get_option( $key, $field[2] ) ); ?>> <?php echo esc_html( $field[1] ); ?></label></td></tr>
					<?php endforeach; ?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/** Image output quality. */
	public static function quality( $quality ) {
		return (int) beautia_get_option( 'image_quality', 82 );
	}

	/** Optional native lazy loading. */
	public static function lazy_loading( $default, $tag_name, $context ) {
		unset( $tag_name, $context );
		return (bool) beautia_get_option( 'lazy_loading', 1 );
	}

	/** Convert future JPEG derivatives to WebP when supported. */
	public static function webp_format( $formats ) {
		if ( wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
			$formats['image/jpeg'] = 'image/webp';
		}
		return $formats;
	}

	/** Remove optional WordPress front-end payload. */
	public static function trim_frontend() {
		if ( beautia_get_option( 'disable_emojis', 1 ) ) {
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'admin_print_styles', 'print_emoji_styles' );
			remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
			remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		}
		if ( beautia_get_option( 'disable_embeds', 1 ) ) {
			remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
			remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		}
	}

	/** Defer only theme-owned scripts. */
	public static function defer_theme_scripts( $tag, $handle, $src ) {
		unset( $src );
		if ( beautia_get_option( 'defer_scripts', 1 ) && 0 === strpos( $handle, 'beautia-' ) && false === strpos( $tag, ' defer' ) ) {
			$tag = str_replace( '<script ', '<script defer ', $tag );
		}
		return $tag;
	}

	/** Preload only the two primary local WOFF2 files. */
	public static function preload_fonts() {
		if ( ! beautia_get_option( 'preload_fonts', 1 ) ) {
			return;
		}
		printf( "<link rel=\"preload\" href=\"%s\" as=\"font\" type=\"font/woff2\" crossorigin>\n", esc_url( BEAUTIA_URI . 'assets/fonts/Vazirmatn-400.woff2' ) );
		printf( "<link rel=\"preload\" href=\"%s\" as=\"font\" type=\"font/woff2\" crossorigin>\n", esc_url( BEAUTIA_URI . 'assets/fonts/MarkaziText-700.woff2' ) );
	}

	/** Optional low-motion budget independent of visitor OS settings. */
	public static function motion_budget() {
		if ( beautia_get_option( 'reduce_motion', 0 ) ) {
			wp_add_inline_style( 'beautia-style', '.grain,.cursor{display:none!important}*[data-parallax]{transform:none!important}.marquee-track,.fab-pulse{animation:none!important}' );
		}
	}
}

Beautia_Performance::boot();
