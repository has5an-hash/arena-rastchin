<?php
/**
 * Mobile-number login / registration with SMS one time passwords.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Beautia_OTP
 */
class Beautia_OTP {

	const META_MOBILE   = 'beautia_mobile';
	const META_VERIFIED = 'beautia_mobile_verified';

	/** @var Beautia_OTP|null */
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
		add_action( 'wp_ajax_nopriv_beautia_otp_request', array( $this, 'ajax_request' ) );
		add_action( 'wp_ajax_beautia_otp_request', array( $this, 'ajax_request' ) );
		add_action( 'wp_ajax_nopriv_beautia_otp_verify', array( $this, 'ajax_verify' ) );
		add_action( 'wp_ajax_beautia_otp_verify', array( $this, 'ajax_verify' ) );
		add_action( 'wp_ajax_beautia_logout', array( $this, 'ajax_logout' ) );

		// Allow logging in with the mobile number in the classic wp-login form too.
		add_filter( 'authenticate', array( $this, 'authenticate_by_mobile' ), 20, 3 );
		add_action( 'show_user_profile', array( $this, 'user_profile_field' ) );
		add_action( 'edit_user_profile', array( $this, 'user_profile_field' ) );
		add_action( 'personal_options_update', array( $this, 'save_user_profile_field' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_user_profile_field' ) );
	}

	/* --------------------------------------------------------------------- */

	/** Transient key for a mobile. */
	private function key( $mobile ) {
		return 'beautia_otp_' . md5( $mobile . wp_salt() );
	}

	/** Throttle key. */
	private function throttle_key( $mobile ) {
		return 'beautia_otp_t_' . md5( $mobile . beautia_get_ip() . wp_salt() );
	}

	/**
	 * Create + send a code.
	 *
	 * @param string $mobile Mobile number.
	 * @return true|WP_Error
	 */
	public function request_code( $mobile ) {
		$mobile = beautia_normalize_mobile( $mobile );

		if ( ! beautia_is_valid_mobile( $mobile ) ) {
			return new WP_Error( 'invalid_mobile', __( 'Please enter a valid mobile number.', 'beautia' ) );
		}

		// Blocked numbers.
		$blocked = array_filter( array_map( 'trim', explode( "\n", (string) beautia_get_option( 'blocked_numbers', '' ) ) ) );
		if ( in_array( $mobile, $blocked, true ) ) {
			return new WP_Error( 'blocked', __( 'This number cannot be used.', 'beautia' ) );
		}

		// Throttling: max N requests per hour per number+IP.
		$max      = (int) beautia_get_option( 'otp_max_per_hour', 5 );
		$attempts = (int) get_transient( $this->throttle_key( $mobile ) );
		if ( $attempts >= $max ) {
			return new WP_Error( 'throttled', __( 'Too many attempts. Please try again in an hour.', 'beautia' ) );
		}

		// Cool-down between two requests.
		$cooldown = (int) beautia_get_option( 'otp_cooldown', 120 );
		$existing = get_transient( $this->key( $mobile ) );
		if ( is_array( $existing ) && ( time() - $existing['created'] ) < $cooldown ) {
			return new WP_Error(
				'cooldown',
				sprintf(
					/* translators: %d seconds */
					__( 'Please wait %d seconds before requesting a new code.', 'beautia' ),
					$cooldown - ( time() - $existing['created'] )
				)
			);
		}

		$length = max( 4, min( 8, (int) beautia_get_option( 'otp_length', 5 ) ) );
		$code   = (string) wp_rand( pow( 10, $length - 1 ), pow( 10, $length ) - 1 );
		$ttl    = (int) beautia_get_option( 'otp_ttl', 3 ); // minutes.

		set_transient(
			$this->key( $mobile ),
			array(
				'hash'    => wp_hash_password( $code ),
				'created' => time(),
				'tries'   => 0,
			),
			$ttl * MINUTE_IN_SECONDS
		);
		set_transient( $this->throttle_key( $mobile ), $attempts + 1, HOUR_IN_SECONDS );

		do_action(
			'beautia_sms_event',
			'otp',
			$mobile,
			array(
				'code' => $code,
				'ttl'  => $ttl,
			)
		);

		// In development mode the code is written to the log gateway only.
		do_action( 'beautia_otp_created', $mobile, $code );

		return true;
	}

	/**
	 * Verify a code and log the user in (creating the account when needed).
	 *
	 * @param string $mobile Mobile.
	 * @param string $code   Code.
	 * @param array  $extra  Optional profile data (first_name…).
	 * @return int|WP_Error  User ID.
	 */
	public function verify_code( $mobile, $code, $extra = array() ) {
		$mobile = beautia_normalize_mobile( $mobile );
		$code   = beautia_fa_to_en_digits( trim( $code ) );
		$data   = get_transient( $this->key( $mobile ) );

		if ( ! is_array( $data ) ) {
			return new WP_Error( 'expired', __( 'The code has expired. Please request a new one.', 'beautia' ) );
		}
		if ( $data['tries'] >= 5 ) {
			delete_transient( $this->key( $mobile ) );
			return new WP_Error( 'too_many', __( 'Too many wrong attempts. Please request a new code.', 'beautia' ) );
		}
		if ( ! wp_check_password( $code, $data['hash'] ) ) {
			$data['tries']++;
			set_transient( $this->key( $mobile ), $data, 5 * MINUTE_IN_SECONDS );
			return new WP_Error( 'wrong_code', __( 'The verification code is not correct.', 'beautia' ) );
		}

		delete_transient( $this->key( $mobile ) );

		$user_id = self::get_user_id_by_mobile( $mobile );
		if ( ! $user_id ) {
			$user_id = $this->create_user( $mobile, $extra );
			if ( is_wp_error( $user_id ) ) {
				return $user_id;
			}
			do_action( 'beautia_user_registered', $user_id, $mobile );
		}

		update_user_meta( $user_id, self::META_VERIFIED, 1 );
		update_user_meta( $user_id, 'beautia_last_login', current_time( 'mysql' ) );

		if ( ! empty( $extra['first_name'] ) ) {
			wp_update_user(
				array(
					'ID'           => $user_id,
					'first_name'   => sanitize_text_field( $extra['first_name'] ),
					'display_name' => sanitize_text_field( $extra['first_name'] ),
				)
			);
		}

		wp_clear_auth_cookie();
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true );
		do_action( 'wp_login', get_userdata( $user_id )->user_login, get_userdata( $user_id ) );

		return $user_id;
	}

	/** Create a customer account from a mobile number. */
	private function create_user( $mobile, $extra = array() ) {
		$login = 'u' . $mobile;
		$i     = 1;
		while ( username_exists( $login ) ) {
			$login = 'u' . $mobile . '_' . $i++;
		}
		$user_id = wp_insert_user(
			array(
				'user_login'   => $login,
				'user_pass'    => wp_generate_password( 24, true, true ),
				'user_email'   => ! empty( $extra['email'] ) ? sanitize_email( $extra['email'] ) : $mobile . '@' . wp_parse_url( home_url(), PHP_URL_HOST ),
				'display_name' => ! empty( $extra['first_name'] ) ? sanitize_text_field( $extra['first_name'] ) : $mobile,
				'role'         => 'beautia_customer',
			)
		);
		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}
		update_user_meta( $user_id, self::META_MOBILE, $mobile );
		return $user_id;
	}

	/** Find a user by mobile. */
	public static function get_user_id_by_mobile( $mobile ) {
		$mobile = beautia_normalize_mobile( $mobile );
		$users  = get_users(
			array(
				'meta_key'   => self::META_MOBILE, // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value' => $mobile,           // phpcs:ignore WordPress.DB.SlowDBQuery
				'number'     => 1,
				'fields'     => 'ID',
			)
		);
		return $users ? (int) $users[0] : 0;
	}

	/** Mobile of a user. */
	public static function get_mobile( $user_id ) {
		return (string) get_user_meta( $user_id, self::META_MOBILE, true );
	}

	/* ------------------------------ AJAX --------------------------------- */

	/** Step 1. */
	public function ajax_request() {
		check_ajax_referer( 'beautia_nonce', 'nonce' );

		$mobile = isset( $_POST['mobile'] ) ? sanitize_text_field( wp_unslash( $_POST['mobile'] ) ) : '';
		$result = $this->request_code( $mobile );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'message'  => __( 'Verification code sent.', 'beautia' ),
				'mobile'   => beautia_mask_mobile( $mobile ),
				'cooldown' => (int) beautia_get_option( 'otp_cooldown', 120 ),
				'isNew'    => ! self::get_user_id_by_mobile( $mobile ),
			)
		);
	}

	/** Step 2. */
	public function ajax_verify() {
		check_ajax_referer( 'beautia_nonce', 'nonce' );

		$mobile = isset( $_POST['mobile'] ) ? sanitize_text_field( wp_unslash( $_POST['mobile'] ) ) : '';
		$code   = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';
		$name   = isset( $_POST['first_name'] ) ? beautia_normalize_fa( sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) ) : '';

		$user_id = $this->verify_code( $mobile, $code, array( 'first_name' => $name ) );

		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'message'  => __( 'Welcome!', 'beautia' ),
				'redirect' => apply_filters( 'beautia_after_login_redirect', beautia_get_page_url( 'account' ), $user_id ),
				'user'     => array(
					'id'   => $user_id,
					'name' => get_userdata( $user_id )->display_name,
				),
			)
		);
	}

	/** Ajax logout. */
	public function ajax_logout() {
		check_ajax_referer( 'beautia_nonce', 'nonce' );
		wp_logout();
		wp_send_json_success( array( 'redirect' => home_url( '/' ) ) );
	}

	/* --------------------------- integrations ---------------------------- */

	/** Let people type their mobile number in the default login form. */
	public function authenticate_by_mobile( $user, $username, $password ) {
		if ( $user instanceof WP_User || empty( $username ) || empty( $password ) ) {
			return $user;
		}
		if ( ! beautia_is_valid_mobile( $username ) ) {
			return $user;
		}
		$user_id = self::get_user_id_by_mobile( $username );
		if ( ! $user_id ) {
			return $user;
		}
		return wp_authenticate_username_password( null, get_userdata( $user_id )->user_login, $password );
	}

	/** Mobile field on the admin user profile. */
	public function user_profile_field( $user ) {
		?>
		<h2><?php esc_html_e( 'Beautia', 'beautia' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><label for="beautia_mobile"><?php esc_html_e( 'Mobile number', 'beautia' ); ?></label></th>
				<td>
					<input type="text" name="beautia_mobile" id="beautia_mobile" class="regular-text"
						value="<?php echo esc_attr( get_user_meta( $user->ID, self::META_MOBILE, true ) ); ?>" />
					<p class="description"><?php esc_html_e( 'Used for OTP login and booking notifications.', 'beautia' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/** Save it. */
	public function save_user_profile_field( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}
		if ( isset( $_POST['beautia_mobile'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			update_user_meta( $user_id, self::META_MOBILE, beautia_normalize_mobile( wp_unslash( $_POST['beautia_mobile'] ) ) ); // phpcs:ignore
		}
	}
}

/**
 * Register the customer role once.
 */
function beautia_register_roles() {
	if ( ! get_role( 'beautia_customer' ) ) {
		add_role(
			'beautia_customer',
			__( 'Beautia Customer', 'beautia' ),
			array( 'read' => true )
		);
	}
	if ( ! get_role( 'beautia_staff' ) ) {
		add_role(
			'beautia_staff',
			__( 'Beautia Staff', 'beautia' ),
			array( 'read' => true, 'edit_posts' => false )
		);
	}
}
add_action( 'after_switch_theme', 'beautia_register_roles' );
add_action( 'init', 'beautia_register_roles', 20 );
