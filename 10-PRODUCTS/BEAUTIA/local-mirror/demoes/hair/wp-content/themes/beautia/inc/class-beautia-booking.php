<?php
/**
 * Professional appointment / booking engine.
 *
 * Everything (slot generation, capacity, buffers, staff schedules, holidays,
 * statuses, SMS notifications, cancellation policy) is implemented in the theme
 * itself, so no paid booking plugin is required.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Beautia_Booking
 */
class Beautia_Booking {

	/** @var Beautia_Booking|null */
	private static $instance = null;

	/** Statuses. */
	const STATUS_PENDING   = 'pending';
	const STATUS_CONFIRMED = 'confirmed';
	const STATUS_CANCELLED = 'cancelled';
	const STATUS_COMPLETED = 'completed';
	const STATUS_NOSHOW    = 'no_show';

	/** Singleton. */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** Hooks. */
	private function __construct() {
		$ajax = array(
			'beautia_get_slots'      => 'ajax_get_slots',
			'beautia_create_booking' => 'ajax_create_booking',
			'beautia_cancel_booking' => 'ajax_cancel_booking',
			'beautia_reschedule'     => 'ajax_reschedule',
			'beautia_service_staff'  => 'ajax_service_staff',
			'beautia_waitlist'       => 'ajax_waitlist',
			'beautia_submit_review'  => 'ajax_submit_review',
		);
		foreach ( $ajax as $action => $method ) {
			add_action( 'wp_ajax_' . $action, array( $this, $method ) );
			add_action( 'wp_ajax_nopriv_' . $action, array( $this, $method ) );
		}
		add_action( 'beautia_booking_status_changed', array( $this, 'notify_status_change' ), 10, 3 );
		add_action( 'beautia_booking_created', array( $this, 'notify_new_booking' ), 10, 1 );
	}

	/** Table name. */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'beautia_appointments';
	}

	/** Notes/history table. */
	public static function log_table() {
		global $wpdb;
		return $wpdb->prefix . 'beautia_appointment_log';
	}

	/** Create the schema. */
	public static function install() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset = $wpdb->get_charset_collate();
		$table   = self::table();
		$log     = self::log_table();

		$sql = "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			code VARCHAR(20) NOT NULL,
			user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			service_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			staff_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			customer_name VARCHAR(190) NOT NULL DEFAULT '',
			customer_mobile VARCHAR(32) NOT NULL DEFAULT '',
			customer_email VARCHAR(190) NOT NULL DEFAULT '',
			start_at DATETIME NOT NULL,
			end_at DATETIME NOT NULL,
			duration SMALLINT UNSIGNED NOT NULL DEFAULT 60,
			price DECIMAL(14,2) NOT NULL DEFAULT 0,
			deposit DECIMAL(14,2) NOT NULL DEFAULT 0,
			paid TINYINT(1) NOT NULL DEFAULT 0,
			status VARCHAR(20) NOT NULL DEFAULT 'pending',
			note TEXT NULL,
			admin_note TEXT NULL,
			source VARCHAR(30) NOT NULL DEFAULT 'website',
			reminded TINYINT(1) NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY code (code),
			KEY user_id (user_id),
			KEY staff_id (staff_id),
			KEY start_at (start_at),
			KEY status (status)
		) {$charset};";

		$sql2 = "CREATE TABLE {$log} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			appointment_id BIGINT UNSIGNED NOT NULL,
			action VARCHAR(40) NOT NULL,
			detail TEXT NULL,
			author BIGINT UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY appointment_id (appointment_id)
		) {$charset};";

		dbDelta( $sql );
		dbDelta( $sql2 );
		update_option( 'beautia_db_version', BEAUTIA_VERSION );
	}

	/* --------------------------------------------------------------------- */
	/* Configuration                                                          */
	/* --------------------------------------------------------------------- */

	/** Status labels. */
	public static function statuses() {
		return array(
			self::STATUS_PENDING   => __( 'Pending', 'beautia' ),
			self::STATUS_CONFIRMED => __( 'Confirmed', 'beautia' ),
			self::STATUS_COMPLETED => __( 'Completed', 'beautia' ),
			self::STATUS_CANCELLED => __( 'Cancelled', 'beautia' ),
			self::STATUS_NOSHOW    => __( 'No-show', 'beautia' ),
		);
	}

	/** Default business hours (used when a staff member has no schedule). */
	public static function business_hours() {
		$default = array();
		foreach ( array( 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' ) as $d ) {
			$default[ $d ] = array(
				'open'       => in_array( $d, array( 'fri' ), true ) ? 0 : 1,
				'from'       => '10:00',
				'to'         => '20:00',
				'break_from' => '',
				'break_to'   => '',
			);
		}
		$saved = get_option( 'beautia_business_hours', array() );
		return wp_parse_args( $saved, $default );
	}

	/** Global holidays (Y-m-d list). */
	public static function holidays() {
		$raw = (string) beautia_get_option( 'holidays', '' );
		return array_filter( array_map( 'trim', preg_split( '/[\r\n,]+/', $raw ) ) );
	}

	/* --------------------------------------------------------------------- */
	/* Slot engine                                                            */
	/* --------------------------------------------------------------------- */

	/**
	 * Build the available slots of a day.
	 *
	 * @param int    $service_id Service post ID.
	 * @param int    $staff_id   Staff post ID (0 = any).
	 * @param string $date       Y-m-d.
	 * @return array
	 */
	public static function get_slots( $service_id, $staff_id, $date ) {
		$date = gmdate( 'Y-m-d', strtotime( $date ) );
		if ( ! $date ) {
			return array();
		}

		$duration = (int) get_post_meta( $service_id, '_beautia_duration', true );
		$duration = $duration ? $duration : 60;
		$buffer   = (int) get_post_meta( $service_id, '_beautia_buffer', true );
		$capacity = max( 1, (int) get_post_meta( $service_id, '_beautia_capacity', true ) );
		$step     = (int) beautia_get_option( 'slot_step', 15 );
		$step     = $step ? $step : 15;
		$lead     = (int) beautia_get_option( 'min_lead_minutes', 60 );
		$maxdays  = (int) beautia_get_option( 'max_days_ahead', 60 );

		// Outside the booking window?
		$today = current_time( 'Y-m-d' );
		if ( $date < $today || strtotime( $date ) > strtotime( $today . ' +' . $maxdays . ' days' ) ) {
			return array();
		}
		if ( in_array( $date, self::holidays(), true ) ) {
			return array();
		}

		$staff_ids = $staff_id ? array( (int) $staff_id ) : self::get_service_staff( $service_id );
		if ( ! $staff_ids ) {
			$staff_ids = array( 0 ); // salon-wide booking without staff selection.
		}

		$dow    = strtolower( gmdate( 'D', strtotime( $date ) ) ); // mon, tue…
		$dowKey = substr( $dow, 0, 3 );
		$slots  = array();

		foreach ( $staff_ids as $sid ) {
			$schedule = $sid ? get_post_meta( $sid, '_beautia_schedule', true ) : array();
			$hours    = ( is_array( $schedule ) && ! empty( $schedule[ $dowKey ] ) ) ? $schedule[ $dowKey ] : self::business_hours()[ $dowKey ];

			if ( empty( $hours['open'] ) ) {
				continue;
			}
			// Personal days off.
			if ( $sid ) {
				$off = array_filter( array_map( 'trim', preg_split( '/[\r\n,]+/', (string) get_post_meta( $sid, '_beautia_daysoff', true ) ) ) );
				if ( in_array( $date, $off, true ) ) {
					continue;
				}
			}

			$open  = strtotime( $date . ' ' . $hours['from'] );
			$close = strtotime( $date . ' ' . $hours['to'] );
			if ( $close <= $open ) {
				continue;
			}
			$break_from = ! empty( $hours['break_from'] ) ? strtotime( $date . ' ' . $hours['break_from'] ) : 0;
			$break_to   = ! empty( $hours['break_to'] ) ? strtotime( $date . ' ' . $hours['break_to'] ) : 0;

			$busy = self::get_busy_ranges( $sid, $date );
			$now  = current_time( 'timestamp' ); // phpcs:ignore

			for ( $t = $open; $t + ( $duration * 60 ) <= $close; $t += $step * 60 ) {
				$start = $t;
				$end   = $t + ( ( $duration + $buffer ) * 60 );

				if ( $start < $now + ( $lead * 60 ) ) {
					continue;
				}
				if ( $break_from && $break_to && $start < $break_to && $end > $break_from ) {
					continue;
				}

				$overlaps = 0;
				foreach ( $busy as $range ) {
					if ( $start < $range[1] && $end > $range[0] ) {
						$overlaps++;
					}
				}
				if ( $overlaps >= $capacity ) {
					continue;
				}

				$key = gmdate( 'H:i', $start );
				if ( ! isset( $slots[ $key ] ) ) {
					$slots[ $key ] = array(
						'time'     => $key,
						'label'    => beautia_use_persian_digits() ? beautia_en_to_fa_digits( date_i18n( self::time_format(), $start ) ) : date_i18n( self::time_format(), $start ),
						'staff'    => array(),
						'datetime' => gmdate( 'Y-m-d H:i:s', $start ),
					);
				}
				$slots[ $key ]['staff'][] = $sid;
			}
		}

		ksort( $slots );
		return array_values( $slots );
	}

	/** 24h or 12h. */
	public static function time_format() {
		return beautia_get_option( 'time_format', 'H:i' );
	}

	/** Busy ranges of a staff member on a date. */
	public static function get_busy_ranges( $staff_id, $date ) {
		global $wpdb;
		$table  = self::table();
		$start  = $date . ' 00:00:00';
		$end    = $date . ' 23:59:59';
		$states = array( self::STATUS_PENDING, self::STATUS_CONFIRMED );
		$in     = "'" . implode( "','", array_map( 'esc_sql', $states ) ) . "'";

		$sql  = "SELECT start_at, end_at FROM {$table} WHERE status IN ({$in}) AND start_at BETWEEN %s AND %s";
		$args = array( $start, $end );
		if ( $staff_id ) {
			$sql   .= ' AND staff_id = %d';
			$args[] = $staff_id;
		}
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $args ) ); // phpcs:ignore WordPress.DB

		$ranges = array();
		foreach ( (array) $rows as $row ) {
			$ranges[] = array( strtotime( $row->start_at ), strtotime( $row->end_at ) );
		}
		return $ranges;
	}

	/** Staff able to perform a service. */
	public static function get_service_staff( $service_id ) {
		$ids = get_post_meta( $service_id, '_beautia_staff_ids', true );
		$ids = array_filter( array_map( 'absint', (array) $ids ) );
		if ( $ids ) {
			return $ids;
		}
		$all = get_posts(
			array(
				'post_type'      => 'beautia_staff',
				'posts_per_page' => 50,
				'fields'         => 'ids',
			)
		);
		return $all;
	}

	/* --------------------------------------------------------------------- */
	/* CRUD                                                                   */
	/* --------------------------------------------------------------------- */

	/**
	 * Create a booking.
	 *
	 * @param array $data Booking data.
	 * @return int|WP_Error Appointment ID.
	 */
	public static function create( $data ) {
		global $wpdb;

		$defaults = array(
			'service_id' => 0,
			'staff_id'   => 0,
			'date'       => '',
			'time'       => '',
			'name'       => '',
			'mobile'     => '',
			'email'      => '',
			'note'       => '',
			'user_id'    => get_current_user_id(),
			'source'     => 'website',
		);
		$data     = wp_parse_args( $data, $defaults );

		$service = get_post( (int) $data['service_id'] );
		if ( ! $service || 'beautia_service' !== $service->post_type ) {
			return new WP_Error( 'invalid_service', __( 'The selected service does not exist.', 'beautia' ) );
		}
		if ( ! beautia_is_valid_mobile( $data['mobile'] ) ) {
			return new WP_Error( 'invalid_mobile', __( 'Please enter a valid mobile number.', 'beautia' ) );
		}
		if ( empty( $data['name'] ) ) {
			return new WP_Error( 'invalid_name', __( 'Please enter your name.', 'beautia' ) );
		}

		$start_ts = strtotime( $data['date'] . ' ' . $data['time'] );
		if ( ! $start_ts ) {
			return new WP_Error( 'invalid_date', __( 'Please choose a valid date and time.', 'beautia' ) );
		}

		$duration = (int) get_post_meta( $service->ID, '_beautia_duration', true );
		$duration = $duration ? $duration : 60;
		$buffer   = (int) get_post_meta( $service->ID, '_beautia_buffer', true );

		// Re-validate the slot server side (protects against race conditions).
		$slots = self::get_slots( $service->ID, (int) $data['staff_id'], gmdate( 'Y-m-d', $start_ts ) );
		$found = false;
		foreach ( $slots as $slot ) {
			if ( $slot['time'] === gmdate( 'H:i', $start_ts ) ) {
				$found = true;
				if ( ! $data['staff_id'] && ! empty( $slot['staff'] ) ) {
					$data['staff_id'] = (int) $slot['staff'][0]; // auto assign.
				}
				break;
			}
		}
		if ( ! $found ) {
			return new WP_Error( 'slot_taken', __( 'Sorry, this time slot has just been taken. Please pick another one.', 'beautia' ) );
		}

		// Per-user open booking limit.
		$limit = (int) beautia_get_option( 'max_open_bookings', 3 );
		if ( $limit && $data['user_id'] ) {
			$open = self::count_user_open_bookings( $data['user_id'] );
			if ( $open >= $limit ) {
				return new WP_Error(
					'limit',
					sprintf(
						/* translators: %d limit */
						__( 'You already have %d active appointments. Please cancel one before booking again.', 'beautia' ),
						$limit
					)
				);
			}
		}

		$auto    = beautia_get_option( 'auto_confirm', false );
		$status  = $auto ? self::STATUS_CONFIRMED : self::STATUS_PENDING;
		$code    = self::generate_code();
		$now     = current_time( 'mysql' );
		$price   = (float) get_post_meta( $service->ID, '_beautia_price', true );
		$deposit = (float) get_post_meta( $service->ID, '_beautia_deposit', true );

		$inserted = $wpdb->insert( // phpcs:ignore WordPress.DB
			self::table(),
			array(
				'code'            => $code,
				'user_id'         => (int) $data['user_id'],
				'service_id'      => (int) $service->ID,
				'staff_id'        => (int) $data['staff_id'],
				'customer_name'   => beautia_normalize_fa( sanitize_text_field( $data['name'] ) ),
				'customer_mobile' => beautia_normalize_mobile( $data['mobile'] ),
				'customer_email'  => sanitize_email( $data['email'] ),
				'start_at'        => gmdate( 'Y-m-d H:i:s', $start_ts ),
				'end_at'          => gmdate( 'Y-m-d H:i:s', $start_ts + ( ( $duration + $buffer ) * 60 ) ),
				'duration'        => $duration,
				'price'           => $price,
				'deposit'         => $deposit,
				'status'          => $status,
				'note'            => sanitize_textarea_field( $data['note'] ),
				'source'          => sanitize_key( $data['source'] ),
				'created_at'      => $now,
				'updated_at'      => $now,
			),
			array( '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%f', '%f', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( ! $inserted ) {
			return new WP_Error( 'db_error', __( 'Could not save the appointment. Please try again.', 'beautia' ) );
		}

		$id = (int) $wpdb->insert_id;
		self::log( $id, 'created', $status );
		do_action( 'beautia_booking_created', $id );

		return $id;
	}

	/** Unique human friendly code. */
	private static function generate_code() {
		global $wpdb;
		do {
			$code   = 'BK' . strtoupper( wp_generate_password( 6, false, false ) );
			$exists = $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . self::table() . ' WHERE code = %s', $code ) ); // phpcs:ignore
		} while ( $exists );
		return $code;
	}

	/** Get one appointment. */
	public static function get( $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', $id ) ); // phpcs:ignore
	}

	/** Get by tracking code. */
	public static function get_by_code( $code ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE code = %s', $code ) ); // phpcs:ignore
	}

	/**
	 * Query appointments.
	 */
	public static function query( $args = array() ) {
		global $wpdb;
		$args = wp_parse_args(
			$args,
			array(
				'user_id'  => 0,
				'staff_id' => 0,
				'status'   => '',
				'from'     => '',
				'to'       => '',
				'search'   => '',
				'limit'    => 50,
				'offset'   => 0,
				'order'    => 'DESC',
			)
		);

		$where = array( '1=1' );
		$prep  = array();
		$demo = function_exists('beautia_demo_context') ? beautia_demo_context() : '';
		if ($demo && (!is_admin() || wp_doing_ajax())) {
			$where[] = "service_id IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_beautia_demo' AND meta_value = %s)";
			$prep[] = $demo;
		}

		if ( $args['user_id'] ) {
			$where[] = 'user_id = %d';
			$prep[]  = $args['user_id'];
		}
		if ( $args['staff_id'] ) {
			$where[] = 'staff_id = %d';
			$prep[]  = $args['staff_id'];
		}
		if ( $args['status'] ) {
			$where[] = 'status = %s';
			$prep[]  = $args['status'];
		}
		if ( $args['from'] ) {
			$where[] = 'start_at >= %s';
			$prep[]  = $args['from'];
		}
		if ( $args['to'] ) {
			$where[] = 'start_at <= %s';
			$prep[]  = $args['to'];
		}
		if ( $args['search'] ) {
			$where[] = '(customer_name LIKE %s OR customer_mobile LIKE %s OR code LIKE %s)';
			$like    = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$prep[]  = $like;
			$prep[]  = $like;
			$prep[]  = $like;
		}

		$order = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';
		$sql   = 'SELECT * FROM ' . self::table() . ' WHERE ' . implode( ' AND ', $where ) .
			" ORDER BY start_at {$order} LIMIT %d OFFSET %d";
		$prep[] = (int) $args['limit'];
		$prep[] = (int) $args['offset'];

		return $wpdb->get_results( $wpdb->prepare( $sql, $prep ) ); // phpcs:ignore
	}

	/** Count for pagination / stats. */
	public static function count( $args = array() ) {
		global $wpdb;
		$status = isset( $args['status'] ) ? $args['status'] : '';
		if ( $status ) {
			return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . self::table() . ' WHERE status = %s', $status ) ); // phpcs:ignore
		}
		return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . self::table() ); // phpcs:ignore
	}

	/** Open bookings of a user. */
	public static function count_user_open_bookings( $user_id ) {
		global $wpdb;
		return (int) $wpdb->get_var(
			$wpdb->prepare( // phpcs:ignore
				'SELECT COUNT(*) FROM ' . self::table() . " WHERE user_id = %d AND status IN ('pending','confirmed') AND start_at >= %s",
				$user_id,
				current_time( 'mysql' )
			)
		);
	}

	/** Revenue helper for the admin dashboard. */
	public static function revenue( $from, $to ) {
		global $wpdb;
		return (float) $wpdb->get_var(
			$wpdb->prepare( // phpcs:ignore
				'SELECT SUM(price) FROM ' . self::table() . " WHERE status IN ('confirmed','completed') AND start_at BETWEEN %s AND %s",
				$from,
				$to
			)
		);
	}

	/**
	 * Change the status of an appointment (and notify).
	 */
	public static function set_status( $id, $status, $note = '' ) {
		global $wpdb;
		$row = self::get( $id );
		if ( ! $row ) {
			return new WP_Error( 'not_found', __( 'Appointment not found.', 'beautia' ) );
		}
		if ( ! array_key_exists( $status, self::statuses() ) ) {
			return new WP_Error( 'bad_status', __( 'Unknown status.', 'beautia' ) );
		}
		$wpdb->update( // phpcs:ignore
			self::table(),
			array(
				'status'     => $status,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
		self::log( $id, 'status', $row->status . ' → ' . $status . ( $note ? ' (' . $note . ')' : '' ) );
		do_action( 'beautia_booking_status_changed', $id, $status, $row->status );
		return true;
	}

	/** Reschedule. */
	public static function reschedule( $id, $date, $time ) {
		global $wpdb;
		$row = self::get( $id );
		if ( ! $row ) {
			return new WP_Error( 'not_found', __( 'Appointment not found.', 'beautia' ) );
		}
		$start_ts = strtotime( $date . ' ' . $time );
		if ( ! $start_ts ) {
			return new WP_Error( 'invalid_date', __( 'Invalid date.', 'beautia' ) );
		}
		$buffer = (int) get_post_meta( $row->service_id, '_beautia_buffer', true );
		$wpdb->update( // phpcs:ignore
			self::table(),
			array(
				'start_at'   => gmdate( 'Y-m-d H:i:s', $start_ts ),
				'end_at'     => gmdate( 'Y-m-d H:i:s', $start_ts + ( ( (int) $row->duration + $buffer ) * 60 ) ),
				'reminded'   => 0,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $id ),
			array( '%s', '%s', '%d', '%s' ),
			array( '%d' )
		);
		self::log( $id, 'rescheduled', $date . ' ' . $time );
		$fresh = self::get( $id );
		do_action( 'beautia_sms_event', 'booking_rescheduled', $fresh->customer_mobile, self::sms_vars( $fresh ) );
		return true;
	}

	/** Can the customer still cancel? */
	public static function can_cancel( $row ) {
		if ( ! in_array( $row->status, array( self::STATUS_PENDING, self::STATUS_CONFIRMED ), true ) ) {
			return false;
		}
		$hours = (int) beautia_get_option( 'cancel_hours', 6 );
		return strtotime( $row->start_at ) - current_time( 'timestamp' ) > $hours * HOUR_IN_SECONDS; // phpcs:ignore
	}

	/** Write to the history log. */
	public static function log( $appointment_id, $action, $detail = '' ) {
		global $wpdb;
		$wpdb->insert( // phpcs:ignore
			self::log_table(),
			array(
				'appointment_id' => $appointment_id,
				'action'         => $action,
				'detail'         => $detail,
				'author'         => get_current_user_id(),
				'created_at'     => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%d', '%s' )
		);
	}

	/** History of one appointment. */
	public static function get_log( $appointment_id ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . self::log_table() . ' WHERE appointment_id = %d ORDER BY id DESC', $appointment_id ) ); // phpcs:ignore
	}

	/* --------------------------------------------------------------------- */
	/* Notifications                                                          */
	/* --------------------------------------------------------------------- */

	/** Build the SMS placeholders for an appointment row. */
	public static function sms_vars( $row ) {
		$service = get_post( $row->service_id );
		$staff   = $row->staff_id ? get_post( $row->staff_id ) : null;
		return array(
			'id'         => $row->id,
			'code'       => $row->code,
			'name'       => $row->customer_name,
			'mobile'     => $row->customer_mobile,
			'service'    => $service ? $service->post_title : '',
			'staff'      => $staff ? $staff->post_title : __( 'our specialist', 'beautia' ),
			'date'       => beautia_format_date( $row->start_at ),
			'time'       => gmdate( self::time_format(), strtotime( $row->start_at ) ),
			'price'      => beautia_price( $row->price ),
			'deposit'    => beautia_price( $row->deposit ),
			'status'     => isset( self::statuses()[ $row->status ] ) ? self::statuses()[ $row->status ] : $row->status,
			'review_url' => beautia_get_option( 'review_url', home_url( '/' ) ),
			'panel_url'  => beautia_get_page_url( 'account' ),
		);
	}

	/** SMS on creation. */
	public function notify_new_booking( $id ) {
		$row = self::get( $id );
		if ( ! $row ) {
			return;
		}
		$vars = self::sms_vars( $row );

		$event = self::STATUS_CONFIRMED === $row->status ? 'booking_confirmed' : 'booking_pending';
		do_action( 'beautia_sms_event', $event, $row->customer_mobile, $vars );

		// Staff.
		if ( $row->staff_id ) {
			$staff_mobile = get_post_meta( $row->staff_id, '_beautia_mobile', true );
			if ( $staff_mobile ) {
				do_action( 'beautia_sms_event', 'staff_new_booking', $staff_mobile, $vars );
			}
		}
		// Admin.
		$admin_mobile = beautia_get_option( 'admin_mobile', '' );
		if ( $admin_mobile ) {
			do_action( 'beautia_sms_event', 'admin_new_booking', $admin_mobile, $vars );
		}
		// Admin e-mail copy.
		if ( beautia_get_option( 'admin_email_notify', true ) ) {
			wp_mail(
				get_option( 'admin_email' ),
				sprintf( '[%s] %s', get_bloginfo( 'name' ), __( 'New appointment', 'beautia' ) ),
				wp_strip_all_tags( Beautia_SMS::parse( Beautia_SMS::get_template( 'admin_new_booking' ), $vars ) )
			);
		}
	}

	/** SMS on status change. */
	public function notify_status_change( $id, $status, $old ) {
		$row = self::get( $id );
		if ( ! $row ) {
			return;
		}
		$map = array(
			self::STATUS_CONFIRMED => 'booking_confirmed',
			self::STATUS_CANCELLED => 'booking_cancelled',
			self::STATUS_COMPLETED => 'booking_completed',
			self::STATUS_NOSHOW    => 'booking_no_show',
		);
		if ( isset( $map[ $status ] ) ) {
			do_action( 'beautia_sms_event', $map[ $status ], $row->customer_mobile, self::sms_vars( $row ) );
		}

		// A cancellation frees a slot — let the waiting list know.
		if ( self::STATUS_CANCELLED === $status ) {
			self::notify_waitlist( gmdate( 'Y-m-d', strtotime( $row->start_time ) ), (int) $row->service_id );
		}
	}

	/** Rows that need a reminder. */
	public static function get_upcoming_for_reminder( $hours ) {
		global $wpdb;
		$from = current_time( 'mysql' );
		$to   = gmdate( 'Y-m-d H:i:s', current_time( 'timestamp' ) + $hours * HOUR_IN_SECONDS ); // phpcs:ignore
		return $wpdb->get_results(
			$wpdb->prepare( // phpcs:ignore
				'SELECT * FROM ' . self::table() . " WHERE status = 'confirmed' AND reminded = 0 AND start_at BETWEEN %s AND %s",
				$from,
				$to
			)
		);
	}

	/** Flag as reminded. */
	public static function mark_reminded( $id ) {
		global $wpdb;
		$wpdb->update( self::table(), array( 'reminded' => 1 ), array( 'id' => $id ), array( '%d' ), array( '%d' ) ); // phpcs:ignore
	}

	/* --------------------------------------------------------------------- */
	/* AJAX                                                                   */
	/* --------------------------------------------------------------------- */

	/** Slots for a day. */
	public function ajax_get_slots() {
		check_ajax_referer( 'beautia_nonce', 'nonce' );
		$service = isset( $_POST['service_id'] ) ? absint( $_POST['service_id'] ) : 0;
		$staff   = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;
		$date    = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';

		if ( ! $service ) {
			wp_send_json_error( array( 'message' => __( 'Please choose a service first.', 'beautia' ) ) );
		}
		$slots = self::get_slots( $service, $staff, $date );
		wp_send_json_success(
			array(
				'slots' => $slots,
				'date'  => $date,
				'label' => beautia_format_date( $date ),
			)
		);
	}

	/** Staff of a service (for the wizard). */
	public function ajax_service_staff() {
		check_ajax_referer( 'beautia_nonce', 'nonce' );
		$service = isset( $_POST['service_id'] ) ? absint( $_POST['service_id'] ) : 0;
		$out     = array();
		foreach ( self::get_service_staff( $service ) as $sid ) {
			$out[] = array(
				'id'     => $sid,
				'name'   => get_the_title( $sid ),
				'role'   => get_post_meta( $sid, '_beautia_role', true ),
				'avatar' => get_the_post_thumbnail_url( $sid, 'thumbnail' ),
			);
		}
		wp_send_json_success( array( 'staff' => $out ) );
	}

	/** Create. */
	public function ajax_create_booking() {
		check_ajax_referer( 'beautia_nonce', 'nonce' );

		$require_login = beautia_get_option( 'require_login_to_book', true );
		if ( $require_login && ! is_user_logged_in() ) {
			wp_send_json_error(
				array(
					'message'   => __( 'Please verify your mobile number first.', 'beautia' ),
					'needLogin' => true,
				)
			);
		}

		$mobile = isset( $_POST['mobile'] ) ? sanitize_text_field( wp_unslash( $_POST['mobile'] ) ) : '';
		if ( is_user_logged_in() && ! $mobile ) {
			$mobile = Beautia_OTP::get_mobile( get_current_user_id() );
		}

		$id = self::create(
			array(
				'service_id' => isset( $_POST['service_id'] ) ? absint( $_POST['service_id'] ) : 0,
				'staff_id'   => isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0,
				'date'       => isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '',
				'time'       => isset( $_POST['time'] ) ? sanitize_text_field( wp_unslash( $_POST['time'] ) ) : '',
				'name'       => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
				'mobile'     => $mobile,
				'email'      => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
				'note'       => isset( $_POST['note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) ) : '',
			)
		);

		if ( is_wp_error( $id ) ) {
			wp_send_json_error( array( 'message' => $id->get_error_message() ) );
		}

		$row = self::get( $id );
		wp_send_json_success(
			array(
				'message'   => Beautia_SMS::has_live_gateway()
					? __( 'Your appointment has been registered. A confirmation SMS was sent to you.', 'beautia' )
					: __( 'Your appointment has been registered. Connect an SMS gateway to deliver confirmation messages.', 'beautia' ),
				'code'      => $row->code,
				'summary'   => self::sms_vars( $row ),
				'accountUrl'=> beautia_get_page_url( 'account' ),
			)
		);
	}

	/** Customer cancels. */
	public function ajax_cancel_booking() {
		check_ajax_referer( 'beautia_nonce', 'nonce' );
		$id  = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$row = self::get( $id );

		if ( ! $row ) {
			wp_send_json_error( array( 'message' => __( 'Appointment not found.', 'beautia' ) ) );
		}
		if ( (int) $row->user_id !== get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to do this.', 'beautia' ) ) );
		}
		if ( ! self::can_cancel( $row ) && ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => beautia_en_to_fa_digits( sprintf(
						/* translators: %d hours */
						__( 'Appointments can only be cancelled up to %d hours in advance. Please call us.', 'beautia' ),
						(int) beautia_get_option( 'cancel_hours', 6 )
					) ),
				)
			);
		}
		self::set_status( $id, self::STATUS_CANCELLED, __( 'cancelled by customer', 'beautia' ) );
		wp_send_json_success( array( 'message' => __( 'Your appointment has been cancelled.', 'beautia' ) ) );
	}

	/** Customer reschedules. */
	public function ajax_reschedule() {
		check_ajax_referer( 'beautia_nonce', 'nonce' );
		$id   = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
		$time = isset( $_POST['time'] ) ? sanitize_text_field( wp_unslash( $_POST['time'] ) ) : '';
		$row  = self::get( $id );

		if ( ! $row || ( (int) $row->user_id !== get_current_user_id() && ! current_user_can( 'manage_options' ) ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to do this.', 'beautia' ) ) );
		}
		$result = self::reschedule( $id, $date, $time );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => __( 'Your appointment has been moved.', 'beautia' ) ) );
	}

	/* ---------------------------------------------------------------------
	 * Waiting list — when a day has no free slot the customer can leave
	 * their number and is notified by SMS as soon as a slot opens up.
	 * ------------------------------------------------------------------ */

	/** Stored as a theme option so no extra table is needed. */
	public static function waitlist() {
		$list = get_option( 'beautia_waitlist', array() );
		return is_array( $list ) ? $list : array();
	}

	/** AJAX: join the waiting list. */
	public function ajax_waitlist() {
		check_ajax_referer( 'beautia_nonce', 'nonce' );

		$mobile = beautia_normalize_mobile( isset( $_POST['mobile'] ) ? sanitize_text_field( wp_unslash( $_POST['mobile'] ) ) : '' );
		$date   = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
		$svc    = isset( $_POST['service_id'] ) ? absint( $_POST['service_id'] ) : 0;
		$name   = isset( $_POST['name'] ) ? beautia_normalize_fa( sanitize_text_field( wp_unslash( $_POST['name'] ) ) ) : '';

		if ( ! $mobile || ! $date ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid mobile number.', 'beautia' ) ) );
		}

		$list = self::waitlist();
		$key  = md5( $mobile . $date . $svc );
		if ( isset( $list[ $key ] ) ) {
			wp_send_json_success( array( 'message' => __( 'You are already on the waiting list for this day.', 'beautia' ) ) );
		}

		$list[ $key ] = array(
			'mobile'  => $mobile,
			'name'    => $name,
			'date'    => $date,
			'service' => $svc,
			'added'   => current_time( 'mysql' ),
		);
		// Keep the list sane.
		if ( count( $list ) > 500 ) {
			$list = array_slice( $list, -500, null, true );
		}
		update_option( 'beautia_waitlist', $list, false );

		Beautia_SMS::send(
			$mobile,
			'waitlist_joined',
			array(
				'name'    => $name ? $name : __( 'Dear customer', 'beautia' ),
				'date'    => beautia_format_date( $date ),
				'service' => $svc ? get_the_title( $svc ) : '',
				'site'    => get_bloginfo( 'name' ),
			)
		);

		do_action( 'beautia_waitlist_joined', $list[ $key ] );

		wp_send_json_success(
			array(
				'message' => __( 'You are on the list — we will text you the moment a slot opens.', 'beautia' ),
			)
		);
	}


	/**
	 * AJAX: a customer rates a completed visit.
	 * Creates a pending testimonial and stores the score on the appointment.
	 */
	public function ajax_submit_review() {
		check_ajax_referer( 'beautia_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Please sign in first.', 'beautia' ) ) );
		}

		$id     = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$rating = isset( $_POST['rating'] ) ? absint( $_POST['rating'] ) : 0;
		$text   = isset( $_POST['text'] ) ? beautia_normalize_fa( sanitize_textarea_field( wp_unslash( $_POST['text'] ) ) ) : '';
		$rating = max( 1, min( 5, $rating ) );

		$row = self::get( $id );
		if ( ! $row || (int) $row->user_id !== get_current_user_id() ) {
			wp_send_json_error( array( 'message' => __( 'Appointment not found.', 'beautia' ) ) );
		}
		if ( self::STATUS_COMPLETED !== $row->status ) {
			wp_send_json_error( array( 'message' => __( 'You can only review a visit that is finished.', 'beautia' ) ) );
		}
		if ( get_option( 'beautia_review_' . $id ) ) {
			wp_send_json_error( array( 'message' => __( 'You have already reviewed this visit.', 'beautia' ) ) );
		}

		update_option( 'beautia_review_' . $id, $rating, false );

		$user  = wp_get_current_user();
		$title = $user->display_name ? $user->display_name : __( 'Customer', 'beautia' );

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'beautia_testimonial',
				'post_title'   => $title,
				'post_content' => $text,
				'post_status'  => 'pending',
			)
		);
		if ( $post_id && ! is_wp_error( $post_id ) ) {
			update_post_meta( $post_id, '_beautia_rating', $rating );
			update_post_meta( $post_id, '_beautia_author_role', get_the_title( $row->service_id ) );
			update_post_meta( $post_id, '_beautia_appointment', $id );
		}

		self::log( $id, sprintf( /* translators: %d: rating out of five. */ __( 'Customer rated the visit %d/5.', 'beautia' ), $rating ) );
		do_action( 'beautia_review_submitted', $id, $rating, $text );

		wp_send_json_success(
			array(
				'message' => __( 'Thank you! Your review helps us — and the next customer.', 'beautia' ),
			)
		);
	}

	/**
	 * When a booking is cancelled, tell everyone waiting for that day.
	 * Hooked from notify_status_change().
	 */
	public static function notify_waitlist( $date, $service_id = 0 ) {
		$list = self::waitlist();
		if ( ! $list ) {
			return 0;
		}
		$sent = 0;
		foreach ( $list as $key => $row ) {
			if ( $row['date'] !== $date ) {
				continue;
			}
			if ( $service_id && $row['service'] && (int) $row['service'] !== (int) $service_id ) {
				continue;
			}
			Beautia_SMS::send(
				$row['mobile'],
				'waitlist_open',
				array(
					'name'    => $row['name'] ? $row['name'] : __( 'Dear customer', 'beautia' ),
					'date'    => beautia_format_date( $row['date'] ),
					'service' => $row['service'] ? get_the_title( $row['service'] ) : '',
					'site'    => get_bloginfo( 'name' ),
				)
			);
			unset( $list[ $key ] );
			$sent++;
			if ( $sent >= 5 ) { // Never spam the whole list at once.
				break;
			}
		}
		update_option( 'beautia_waitlist', $list, false );
		return $sent;
	}

}
