<?php
/**
 * SMS engine — multi gateway, template based, fully self contained.
 *
 * Supported out of the box: Kavenegar, SMS.ir, MeliPayamak, FarazSMS (ippanel),
 * Ghasedak, Twilio and a generic Webhook / custom REST endpoint.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Beautia_SMS
 */
class Beautia_SMS {

	/** @var Beautia_SMS|null */
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
		add_action( 'beautia_sms_event', array( $this, 'handle_event' ), 10, 3 );
		add_action( 'beautia_send_reminders', array( $this, 'send_reminders' ) );
		if ( ! wp_next_scheduled( 'beautia_send_reminders' ) ) {
			wp_schedule_event( time() + 300, 'hourly', 'beautia_send_reminders' );
		}
	}

	/** Available gateways. */
	public static function gateways() {
		return array(
			'kavenegar'   => 'Kavenegar',
			'smsir'       => 'SMS.ir',
			'melipayamak' => 'MeliPayamak',
			'farazsms'    => 'FarazSMS / IPPanel',
			'ghasedak'    => 'Ghasedak',
			'twilio'      => 'Twilio',
			'webhook'     => __( 'Custom Webhook', 'beautia' ),
			'log'         => __( 'Log only (development)', 'beautia' ),
		);
	}

	/** Events that can trigger an SMS. */
	public static function events() {
		return array(
			'otp'                => __( 'Login / register verification code', 'beautia' ),
			'booking_pending'    => __( 'Booking submitted (awaiting confirmation)', 'beautia' ),
			'booking_confirmed'  => __( 'Booking confirmed', 'beautia' ),
			'booking_rescheduled'=> __( 'Booking rescheduled', 'beautia' ),
			'booking_cancelled'  => __( 'Booking cancelled', 'beautia' ),
			'booking_reminder'   => __( 'Reminder before the appointment', 'beautia' ),
			'booking_completed'  => __( 'Service completed / thank you', 'beautia' ),
			'booking_no_show'    => __( 'Marked as no-show', 'beautia' ),
			'staff_new_booking'  => __( 'New booking — notify staff', 'beautia' ),
			'admin_new_booking'  => __( 'New booking — notify admin', 'beautia' ),
			'birthday'           => __( 'Birthday greeting + gift code', 'beautia' ),
		);
	}

	/** Default message templates. */
	public static function default_templates() {
		return array(
			'otp'                 => __( "{site}\nYour verification code: {code}\nValid for {ttl} minutes.", 'beautia' ),
			'waitlist_joined'     => __( "{name} dear,\nYou are on the waiting list for {date}.\nAs soon as a slot opens we will text you.\n{site}", 'beautia' ),
			'waitlist_open'       => __( "{name} dear,\nGood news — a slot has just opened on {date}.\nBook it before someone else does:\n{site}", 'beautia' ),
			'booking_pending'     => __( "{name} dear,\nYour request for {service} on {date} at {time} has been received and is waiting for confirmation.\nTracking code: {code}\n{site}", 'beautia' ),
			'booking_confirmed'   => __( "{name} dear,\nYour appointment for {service} is confirmed.\n{date} at {time}\nSpecialist: {staff}\nAddress: {address}\n{site}", 'beautia' ),
			'booking_rescheduled' => __( "{name} dear,\nYour appointment {code} has been moved to {date} at {time}.\n{site}", 'beautia' ),
			'booking_cancelled'   => __( "{name} dear,\nYour appointment {code} on {date} at {time} has been cancelled.\n{site}", 'beautia' ),
			'booking_reminder'    => __( "{name} dear,\nReminder: {service} tomorrow {date} at {time}.\nPlease arrive 10 minutes earlier.\n{site}", 'beautia' ),
			'booking_completed'   => __( "{name} dear,\nThank you for visiting us. We hope you loved your {service}.\nShare your experience: {review_url}", 'beautia' ),
			'booking_no_show'     => __( "{name} dear,\nWe missed you at {time} on {date}. Contact us to rebook.\n{site}", 'beautia' ),
			'staff_new_booking'   => __( "New booking: {service}\n{date} {time}\nClient: {name} - {mobile}", 'beautia' ),
			'admin_new_booking'   => __( "New booking #{id} {service} for {name} ({mobile}) on {date} {time}.", 'beautia' ),
			'birthday'            => __( "{name} dear, happy birthday! 🎉\nUse code {code} for a {discount} discount this month.\n{site}", 'beautia' ),
		);
	}

	/** Get a template body. */
	public static function get_template( $event ) {
		$templates = get_option( 'beautia_sms_templates', array() );
		$defaults  = self::default_templates();
		$body      = isset( $templates[ $event ] ) && '' !== trim( $templates[ $event ] ) ? $templates[ $event ] : ( isset( $defaults[ $event ] ) ? $defaults[ $event ] : '' );
		return $body;
	}

	/** Is an event enabled? */
	public static function is_enabled( $event ) {
		$enabled = get_option( 'beautia_sms_enabled', array_keys( self::default_templates() ) );
		return in_array( $event, (array) $enabled, true );
	}

	/** Whether a real delivery gateway (rather than the development log) is selected. */
	public static function has_live_gateway() {
		return 'log' !== beautia_get_option( 'sms_gateway', 'log' );
	}

	/**
	 * Replace {placeholders}.
	 */
	public static function parse( $body, $vars ) {
		$vars = wp_parse_args(
			$vars,
			array(
				'site'    => get_bloginfo( 'name' ),
				'address' => beautia_get_option( 'address', '' ),
				'phone'   => beautia_get_option( 'phone', '' ),
			)
		);
		foreach ( $vars as $key => $value ) {
			$body = str_replace( '{' . $key . '}', (string) $value, $body );
		}
		return $body;
	}

	/**
	 * Fire an event.
	 *
	 * @param string $event  Event key.
	 * @param string $mobile Destination.
	 * @param array  $vars   Placeholders.
	 */
	public function handle_event( $event, $mobile, $vars = array() ) {
		if ( ! self::is_enabled( $event ) ) {
			return false;
		}
		$body = self::parse( self::get_template( $event ), $vars );
		return $this->send( $mobile, $body, $event, $vars );
	}

	/**
	 * Send a message.
	 *
	 * @param string $mobile  Destination number.
	 * @param string $message Body.
	 * @param string $event   Event key (used for pattern based gateways).
	 * @param array  $vars    Tokens (pattern gateways send tokens instead of text).
	 */
	public function send( $mobile, $message, $event = '', $vars = array() ) {
		$mobile = beautia_normalize_mobile( $mobile );
		if ( ! $mobile ) {
			return new WP_Error( 'beautia_sms', __( 'Empty destination number.', 'beautia' ) );
		}

		$gateway  = beautia_get_option( 'sms_gateway', 'log' );
		$settings = get_option( 'beautia_sms_settings', array() );
		$result   = null;

		/**
		 * Allow a child theme / snippet to fully take over sending.
		 */
		$pre = apply_filters( 'beautia_pre_send_sms', null, $mobile, $message, $event, $vars );
		if ( null !== $pre ) {
			$this->log( $mobile, $message, $event, 'filtered', $pre );
			return $pre;
		}

		switch ( $gateway ) {
			case 'kavenegar':
				$result = $this->send_kavenegar( $mobile, $message, $event, $vars, $settings );
				break;
			case 'smsir':
				$result = $this->send_smsir( $mobile, $message, $event, $vars, $settings );
				break;
			case 'melipayamak':
				$result = $this->send_melipayamak( $mobile, $message, $settings );
				break;
			case 'farazsms':
				$result = $this->send_farazsms( $mobile, $message, $settings );
				break;
			case 'ghasedak':
				$result = $this->send_ghasedak( $mobile, $message, $settings );
				break;
			case 'twilio':
				$result = $this->send_twilio( $mobile, $message, $settings );
				break;
			case 'webhook':
				$result = $this->send_webhook( $mobile, $message, $event, $vars, $settings );
				break;
			default:
				$result = true; // log gateway.
		}

		$status = is_wp_error( $result ) ? 'failed' : 'sent';
		$this->log( $mobile, $message, $event, $status, $result );

		// Optional e-mail fallback so nothing is lost while a gateway is down.
		if ( 'failed' === $status && beautia_get_option( 'sms_email_fallback', false ) ) {
			add_filter( 'wp_mail_content_type', 'beautia_mail_content_type' );
			wp_mail( get_option( 'admin_email' ), __( 'SMS delivery failed', 'beautia' ) . ' — ' . $event, $mobile . "\n\n" . $message );
			remove_filter( 'wp_mail_content_type', 'beautia_mail_content_type' );
		}

		return $result;
	}

	/** Kavenegar (supports both simple send and verify/lookup patterns). */
	private function send_kavenegar( $mobile, $message, $event, $vars, $s ) {
		$api = isset( $s['kavenegar_key'] ) ? $s['kavenegar_key'] : '';
		if ( ! $api ) {
			return new WP_Error( 'beautia_sms', 'Kavenegar API key missing' );
		}
		$pattern = isset( $s['pattern_' . $event ] ) ? $s[ 'pattern_' . $event ] : '';
		if ( $pattern ) {
			$url  = "https://api.kavenegar.com/v1/{$api}/verify/lookup.json";
			$args = array(
				'receptor' => $mobile,
				'template' => $pattern,
				'token'    => isset( $vars['code'] ) ? $vars['code'] : '',
				'token2'   => isset( $vars['date'] ) ? $vars['date'] : '',
				'token3'   => isset( $vars['time'] ) ? $vars['time'] : '',
			);
		} else {
			$url  = "https://api.kavenegar.com/v1/{$api}/sms/send.json";
			$args = array(
				'receptor' => $mobile,
				'message'  => $message,
				'sender'   => isset( $s['kavenegar_sender'] ) ? $s['kavenegar_sender'] : '',
			);
		}
		return $this->request( add_query_arg( array_filter( $args ), $url ), array(), 'GET' );
	}

	/** SMS.ir v1 bulk send. */
	private function send_smsir( $mobile, $message, $event, $vars, $s ) {
		$key = isset( $s['smsir_key'] ) ? $s['smsir_key'] : '';
		if ( ! $key ) {
			return new WP_Error( 'beautia_sms', 'SMS.ir API key missing' );
		}
		return $this->request(
			'https://api.sms.ir/v1/send/bulk',
			array(
				'headers' => array( 'X-API-KEY' => $key, 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode(
					array(
						'lineNumber'  => isset( $s['smsir_line'] ) ? $s['smsir_line'] : '',
						'messageText' => $message,
						'mobiles'     => array( $mobile ),
					)
				),
			)
		);
	}

	/** MeliPayamak simple REST. */
	private function send_melipayamak( $mobile, $message, $s ) {
		$user = isset( $s['meli_user'] ) ? $s['meli_user'] : '';
		$pass = isset( $s['meli_pass'] ) ? $s['meli_pass'] : '';
		if ( ! $user || ! $pass ) {
			return new WP_Error( 'beautia_sms', 'MeliPayamak credentials missing' );
		}
		return $this->request(
			'https://rest.payamak-panel.com/api/SendSMS/SendSMS',
			array(
				'body' => array(
					'username' => $user,
					'password' => $pass,
					'to'       => $mobile,
					'from'     => isset( $s['meli_from'] ) ? $s['meli_from'] : '',
					'text'     => $message,
					'isflash'  => 'false',
				),
			)
		);
	}

	/** FarazSMS / IPPanel. */
	private function send_farazsms( $mobile, $message, $s ) {
		$key = isset( $s['faraz_key'] ) ? $s['faraz_key'] : '';
		if ( ! $key ) {
			return new WP_Error( 'beautia_sms', 'FarazSMS key missing' );
		}
		return $this->request(
			'https://api2.ippanel.com/api/v1/sms/send/webservice/single',
			array(
				'headers' => array( 'Authorization' => 'AccessKey ' . $key, 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode(
					array(
						'recipient' => array( $mobile ),
						'sender'    => isset( $s['faraz_from'] ) ? $s['faraz_from'] : '',
						'message'   => $message,
					)
				),
			)
		);
	}

	/** Ghasedak. */
	private function send_ghasedak( $mobile, $message, $s ) {
		$key = isset( $s['ghasedak_key'] ) ? $s['ghasedak_key'] : '';
		if ( ! $key ) {
			return new WP_Error( 'beautia_sms', 'Ghasedak key missing' );
		}
		return $this->request(
			'https://api.ghasedak.me/v2/sms/send/simple',
			array(
				'headers' => array( 'apikey' => $key ),
				'body'    => array(
					'message'  => $message,
					'receptor' => $mobile,
					'linenumber' => isset( $s['ghasedak_line'] ) ? $s['ghasedak_line'] : '',
				),
			)
		);
	}

	/** Twilio. */
	private function send_twilio( $mobile, $message, $s ) {
		$sid   = isset( $s['twilio_sid'] ) ? $s['twilio_sid'] : '';
		$token = isset( $s['twilio_token'] ) ? $s['twilio_token'] : '';
		if ( ! $sid || ! $token ) {
			return new WP_Error( 'beautia_sms', 'Twilio credentials missing' );
		}
		return $this->request(
			"https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json",
			array(
				'headers' => array( 'Authorization' => 'Basic ' . base64_encode( $sid . ':' . $token ) ), // phpcs:ignore
				'body'    => array(
					'From' => isset( $s['twilio_from'] ) ? $s['twilio_from'] : '',
					'To'   => $mobile,
					'Body' => $message,
				),
			)
		);
	}

	/** Generic webhook — POST JSON to any endpoint (e.g. n8n, Make, custom panel). */
	private function send_webhook( $mobile, $message, $event, $vars, $s ) {
		$url = isset( $s['webhook_url'] ) ? $s['webhook_url'] : '';
		if ( ! $url ) {
			return new WP_Error( 'beautia_sms', 'Webhook URL missing' );
		}
		return $this->request(
			$url,
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'X-Beautia-Token' => isset( $s['webhook_token'] ) ? $s['webhook_token'] : '',
				),
				'body'    => wp_json_encode(
					array(
						'to'      => $mobile,
						'text'    => $message,
						'event'   => $event,
						'vars'    => $vars,
						'site'    => home_url(),
					)
				),
			)
		);
	}

	/** HTTP helper. */
	private function request( $url, $args = array(), $method = 'POST' ) {
		$args = wp_parse_args(
			$args,
			array(
				'timeout' => 15,
				'method'  => $method,
			)
		);
		$response = wp_remote_request( $url, $args );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$code = wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 300 ) {
			return new WP_Error( 'beautia_sms', 'HTTP ' . $code . ' — ' . wp_remote_retrieve_body( $response ) );
		}
		return wp_remote_retrieve_body( $response );
	}

	/** Store the last 300 messages for the admin SMS log screen. */
	private function log( $mobile, $message, $event, $status, $result ) {
		$log   = get_option( 'beautia_sms_log', array() );
		$entry = array(
			'time'    => current_time( 'mysql' ),
			'mobile'  => beautia_mask_mobile( $mobile ),
			'event'   => $event,
			'status'  => $status,
			'message' => $message,
			'result'  => is_wp_error( $result ) ? $result->get_error_message() : ( is_string( $result ) ? substr( $result, 0, 200 ) : '' ),
		);
		array_unshift( $log, $entry );
		update_option( 'beautia_sms_log', array_slice( $log, 0, 300 ), false );
	}

	/** Hourly reminder cron. */
	public function send_reminders() {
		if ( ! self::is_enabled( 'booking_reminder' ) ) {
			return;
		}
		$hours = (int) beautia_get_option( 'reminder_hours', 24 );
		$rows  = Beautia_Booking::get_upcoming_for_reminder( $hours );
		foreach ( $rows as $row ) {
			do_action( 'beautia_sms_event', 'booking_reminder', $row->customer_mobile, Beautia_Booking::sms_vars( $row ) );
			Beautia_Booking::mark_reminded( $row->id );
		}
	}
}
