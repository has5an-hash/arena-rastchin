<?php
/** Local release audit; excluded from the commercial ZIP. */
if ( PHP_SAPI !== 'cli' ) {
	exit;
}

$_SERVER['HTTP_HOST']   = 'localhost';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/beautia/';
require dirname( __DIR__, 3 ) . '/wp-load.php';

$errors = array();
$report = array();
$map    = get_option( 'beautia_studio_map', array() );

foreach ( beautia_studios() as $demo => $studio ) {
	$pages = isset( $map[ $demo ]['pages'] ) ? $map[ $demo ]['pages'] : array();
	foreach ( $pages as $page => $id ) {
		$url      = get_permalink( $id );
		$response = wp_remote_get( $url, array( 'timeout' => 15 ) );
		$code     = is_wp_error( $response ) ? 0 : wp_remote_retrieve_response_code( $response );
		$body     = is_wp_error( $response ) ? '' : wp_remote_retrieve_body( $response );
		$key      = $demo . '/' . $page;
		$report[ $key ] = array( 'status' => $code, 'bytes' => strlen( $body ) );
		if ( 200 !== $code || false === strpos( $body, '<html' ) ) {
			$errors[] = $key . ' did not render successfully';
		}

		if ( 'home' === $page && preg_match_all( '/<img[^>]+src=["\']([^"\']+)["\']/i', $body, $matches ) ) {
			foreach ( array_unique( $matches[1] ) as $image ) {
				$image_url = html_entity_decode( $image, ENT_QUOTES, 'UTF-8' );
				if ( 0 !== strpos( $image_url, home_url() ) ) {
					continue;
				}
				$asset = wp_remote_get( $image_url, array( 'timeout' => 15, 'redirection' => 2 ) );
				if ( is_wp_error( $asset ) || 200 !== wp_remote_retrieve_response_code( $asset ) ) {
					$errors[] = $key . ' broken image: ' . $image_url;
				}
			}
		}
	}

	$portfolio = get_posts(
		array(
			'post_type'      => 'beautia_portfolio',
			'posts_per_page' => 6,
			'meta_key'       => '_beautia_demo',
			'meta_value'     => $demo,
			'orderby'        => 'ID',
			'order'          => 'ASC',
		)
	);
	$images = array_filter( array_map( 'get_post_thumbnail_id', $portfolio ) );
	if ( count( array_unique( $images ) ) < min( 3, count( $portfolio ) ) ) {
		$errors[] = $demo . ' portfolio does not have enough visual variety';
	}
}

$required = array(
	'inc/class-beautia-elementor.php',
	'languages/fa_IR.mo',
	'assets/css/rtl.css',
	'assets/js/booking.js',
	'screenshot.png',
);
foreach ( $required as $file ) {
	if ( ! file_exists( get_template_directory() . '/' . $file ) ) {
		$errors[] = 'missing theme file: ' . $file;
	}
}

echo wp_json_encode(
	array(
		'pages'  => $report,
		'errors' => $errors,
	),
	JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);
exit( $errors ? 1 : 0 );
