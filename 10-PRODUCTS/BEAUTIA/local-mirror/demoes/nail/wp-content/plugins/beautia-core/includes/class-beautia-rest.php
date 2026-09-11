<?php
/**
 * REST API — allows a mobile app / PWA to use the same booking engine.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Beautia_REST
 */
class Beautia_REST {

	/** @var Beautia_REST|null */
	private static $instance = null;

	/** Singleton. */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** Hooks. */
	private function __construct() {
		add_action( 'rest_api_init', array( $this, 'routes' ) );
	}

	/** Register the routes. */
	public function routes() {
		$ns = 'beautia/v1';

		register_rest_route(
			$ns,
			'/services',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => array( $this, 'services' ),
			)
		);

		register_rest_route(
			$ns,
			'/slots',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => array( $this, 'slots' ),
				'args'                => array(
					'service_id' => array( 'required' => true, 'sanitize_callback' => 'absint' ),
					'staff_id'   => array( 'default' => 0, 'sanitize_callback' => 'absint' ),
					'date'       => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
				),
			)
		);

		register_rest_route(
			$ns,
			'/otp/request',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => array( $this, 'otp_request' ),
			)
		);

		register_rest_route(
			$ns,
			'/otp/verify',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => array( $this, 'otp_verify' ),
			)
		);

		register_rest_route(
			$ns,
			'/bookings',
			array(
				array(
					'methods'             => 'GET',
					'permission_callback' => function () {
						return is_user_logged_in();
					},
					'callback'            => array( $this, 'my_bookings' ),
				),
				array(
					'methods'             => 'POST',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'create_booking' ),
				),
			)
		);

		register_rest_route(
			$ns,
			'/bookings/(?P<code>[A-Za-z0-9]+)',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => array( $this, 'track' ),
			)
		);
	}

	/** GET /services */
	public function services( $request ) {
		$posts = get_posts( array( 'post_type' => 'beautia_service', 'posts_per_page' => 100 ) );
		$out   = array();
		foreach ( $posts as $p ) {
			$out[] = array(
				'id'       => $p->ID,
				'title'    => $p->post_title,
				'excerpt'  => wp_trim_words( $p->post_content, 24 ),
				'price'    => (float) get_post_meta( $p->ID, '_beautia_price', true ),
				'duration' => (int) get_post_meta( $p->ID, '_beautia_duration', true ),
				'image'    => get_the_post_thumbnail_url( $p, 'beautia-card' ),
				'bookable' => (bool) get_post_meta( $p->ID, '_beautia_bookable', true ),
			);
		}
		return rest_ensure_response( $out );
	}

	/** GET /slots */
	public function slots( $request ) {
		return rest_ensure_response(
			Beautia_Booking::get_slots(
				$request->get_param( 'service_id' ),
				$request->get_param( 'staff_id' ),
				$request->get_param( 'date' )
			)
		);
	}

	/** POST /otp/request */
	public function otp_request( $request ) {
		$result = Beautia_OTP::instance()->request_code( $request->get_param( 'mobile' ) );
		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( array( 'success' => false, 'message' => $result->get_error_message() ), 400 );
		}
		return rest_ensure_response( array( 'success' => true ) );
	}

	/** POST /otp/verify */
	public function otp_verify( $request ) {
		$user_id = Beautia_OTP::instance()->verify_code( $request->get_param( 'mobile' ), $request->get_param( 'code' ) );
		if ( is_wp_error( $user_id ) ) {
			return new WP_REST_Response( array( 'success' => false, 'message' => $user_id->get_error_message() ), 400 );
		}
		return rest_ensure_response( array( 'success' => true, 'user_id' => $user_id ) );
	}

	/** GET /bookings */
	public function my_bookings() {
		return rest_ensure_response( Beautia_Booking::query( array( 'user_id' => get_current_user_id(), 'limit' => 100 ) ) );
	}

	/** POST /bookings */
	public function create_booking( $request ) {
		$id = Beautia_Booking::create(
			array(
				'service_id' => $request->get_param( 'service_id' ),
				'staff_id'   => $request->get_param( 'staff_id' ),
				'date'       => $request->get_param( 'date' ),
				'time'       => $request->get_param( 'time' ),
				'name'       => $request->get_param( 'name' ),
				'mobile'     => $request->get_param( 'mobile' ),
				'note'       => $request->get_param( 'note' ),
				'source'     => 'api',
			)
		);
		if ( is_wp_error( $id ) ) {
			return new WP_REST_Response( array( 'success' => false, 'message' => $id->get_error_message() ), 400 );
		}
		$row = Beautia_Booking::get( $id );
		return rest_ensure_response( array( 'success' => true, 'code' => $row->code, 'booking' => $row ) );
	}

	/** GET /bookings/{code} — public tracking. */
	public function track( $request ) {
		$row = Beautia_Booking::get_by_code( $request->get_param( 'code' ) );
		if ( ! $row ) {
			return new WP_REST_Response( array( 'success' => false ), 404 );
		}
		$vars = Beautia_Booking::sms_vars( $row );
		unset( $vars['mobile'] );
		return rest_ensure_response( array( 'success' => true, 'booking' => $vars ) );
	}
}
