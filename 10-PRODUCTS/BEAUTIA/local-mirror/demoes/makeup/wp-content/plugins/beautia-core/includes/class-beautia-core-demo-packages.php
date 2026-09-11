<?php
/** Portable, validated Core demo packages. */
defined( 'ABSPATH' ) || exit;

final class Beautia_Core_Demo_Packages {
	const OPTION = 'beautia_core_demo_packages';

	public static function boot() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 60 );
		add_action( 'admin_post_beautia_core_upload_demo', array( __CLASS__, 'upload' ) );
	}

	public static function menu() {
		add_submenu_page( 'beautia', __( 'Core Demo Packages', 'beautia-core' ), __( 'Core Demo Packages', 'beautia-core' ), 'manage_options', 'beautia-core-demos', array( __CLASS__, 'page' ) );
	}

	public static function page() {
		$packages = get_option( self::OPTION, array() );
		?>
		<div class="wrap beautia-admin"><h1><?php esc_html_e( 'Beautia Core Demo Packages', 'beautia-core' ); ?></h1>
		<p><?php esc_html_e( 'Upload a signed-off Beautia demo ZIP. It becomes available to the native one-click importer; no paid importer is required.', 'beautia-core' ); ?></p>
		<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="beautia_core_upload_demo"><?php wp_nonce_field( 'beautia_core_upload_demo' ); ?>
			<input type="file" name="demo_package" accept=".zip,application/zip" required>
			<?php submit_button( __( 'Install demo package', 'beautia-core' ), 'primary', 'submit', false ); ?>
		</form>
		<h2><?php esc_html_e( 'Installed packages', 'beautia-core' ); ?></h2>
		<table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Demo', 'beautia-core' ); ?></th><th><?php esc_html_e( 'Version', 'beautia-core' ); ?></th><th><?php esc_html_e( 'Source', 'beautia-core' ); ?></th></tr></thead><tbody>
		<?php if ( ! $packages ) : ?><tr><td colspan="3"><?php esc_html_e( 'No external package installed yet. Bundled demos remain available.', 'beautia-core' ); ?></td></tr><?php endif; ?>
		<?php foreach ( $packages as $slug => $package ) : ?><tr><td><?php echo esc_html( $package['name'] ); ?> <code><?php echo esc_html( $slug ); ?></code></td><td><?php echo esc_html( $package['version'] ); ?></td><td><?php esc_html_e( 'Validated Core package', 'beautia-core' ); ?></td></tr><?php endforeach; ?>
		</tbody></table></div>
		<?php
	}

	public static function upload() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( esc_html__( 'You are not allowed to do this.', 'beautia-core' ) );
		check_admin_referer( 'beautia_core_upload_demo' );
		if ( empty( $_FILES['demo_package']['tmp_name'] ) ) wp_die( esc_html__( 'Choose a ZIP package.', 'beautia-core' ) );

		$file = $_FILES['demo_package']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( UPLOAD_ERR_OK !== (int) $file['error'] || 'zip' !== strtolower( pathinfo( sanitize_file_name( $file['name'] ), PATHINFO_EXTENSION ) ) ) wp_die( esc_html__( 'The package must be a valid ZIP file.', 'beautia-core' ) );
		if ( (int) $file['size'] > 80 * MB_IN_BYTES ) wp_die( esc_html__( 'The demo package is larger than 80 MB.', 'beautia-core' ) );

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		$tmp = wp_tempnam( 'beautia-demo.zip' );
		if ( ! move_uploaded_file( $file['tmp_name'], $tmp ) ) wp_die( esc_html__( 'The uploaded file could not be moved.', 'beautia-core' ) );
		$probe = trailingslashit( get_temp_dir() ) . 'beautia-demo-' . wp_generate_uuid4();
		wp_mkdir_p( $probe );
		$result = unzip_file( $tmp, $probe );
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		if ( is_wp_error( $result ) ) wp_die( esc_html( $result->get_error_message() ) );

		$manifest_path = trailingslashit( $probe ) . 'manifest.json';
		$data_path = trailingslashit( $probe ) . 'demo-data.json';
		$manifest = file_exists( $manifest_path ) ? json_decode( file_get_contents( $manifest_path ), true ) : null; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$data = file_exists( $data_path ) ? json_decode( file_get_contents( $data_path ), true ) : null; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$slug = is_array( $manifest ) && isset( $manifest['slug'] ) ? sanitize_key( $manifest['slug'] ) : '';
		if ( ! $slug || ! is_array( $data ) || empty( $data['pages'] ) || empty( $data['services'] ) || 'beautia-core-demo' !== ( $manifest['type'] ?? '' ) ) {
			self::remove_tree( $probe );
			wp_die( esc_html__( 'Invalid Beautia Core demo package.', 'beautia-core' ) );
		}

		$uploads = wp_upload_dir();
		$base = trailingslashit( $uploads['basedir'] ) . 'beautia-core-demos';
		wp_mkdir_p( $base );
		$destination = trailingslashit( $base ) . $slug;
		self::remove_tree( $destination );
		rename( $probe, $destination );
		$packages = get_option( self::OPTION, array() );
		$packages[ $slug ] = array( 'name' => sanitize_text_field( $manifest['name'] ?? $slug ), 'version' => sanitize_text_field( $manifest['version'] ?? '1.0.0' ), 'path' => $destination );
		update_option( self::OPTION, $packages, false );
		wp_safe_redirect( add_query_arg( array( 'page' => 'beautia-core-demos', 'installed' => $slug ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function data( $slug ) {
		$packages = get_option( self::OPTION, array() );
		if ( empty( $packages[ $slug ]['path'] ) ) return array();
		$file = trailingslashit( $packages[ $slug ]['path'] ) . 'demo-data.json';
		return file_exists( $file ) ? (array) json_decode( file_get_contents( $file ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}

	public static function asset( $slug, $file ) {
		$packages = get_option( self::OPTION, array() );
		if ( empty( $packages[ $slug ]['path'] ) ) return '';
		$base = realpath( trailingslashit( $packages[ $slug ]['path'] ) . 'media' );
		$path = $base ? realpath( trailingslashit( $base ) . sanitize_file_name( basename( $file ) ) ) : false;
		return $path && 0 === strpos( $path, $base . DIRECTORY_SEPARATOR ) ? $path : '';
	}

	private static function remove_tree( $path ) {
		if ( ! $path || ! file_exists( $path ) ) return;
		if ( function_exists( 'WP_Filesystem' ) ) { global $wp_filesystem; if ( $wp_filesystem ) { $wp_filesystem->delete( $path, true ); return; } }
	}
}

function beautia_core_demo_data( $slug ) { return Beautia_Core_Demo_Packages::data( $slug ); }
function beautia_core_demo_asset( $slug, $file ) { return Beautia_Core_Demo_Packages::asset( $slug, $file ); }

