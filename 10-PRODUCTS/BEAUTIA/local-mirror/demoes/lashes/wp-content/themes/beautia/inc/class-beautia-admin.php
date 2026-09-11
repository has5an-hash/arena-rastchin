<?php
/**
 * Admin dashboard: appointments manager, booking settings, SMS settings & log.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Beautia_Admin
 */
class Beautia_Admin {

	/** @var Beautia_Admin|null */
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
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'handle_actions' ) );
		add_action( 'admin_post_beautia_export', array( $this, 'export_csv' ) );
		add_action( 'admin_notices', array( $this, 'welcome_notice' ) );
		add_action( 'wp_ajax_beautia_admin_status', array( $this, 'ajax_status' ) );
		add_action( 'wp_ajax_beautia_test_sms', array( $this, 'ajax_test_sms' ) );
	}

	/** Menus. */
	public function menu() {
		$cap = 'manage_options';

		add_menu_page(
			__( 'Beautia', 'beautia' ),
			__( 'Beautia', 'beautia' ),
			$cap,
			'beautia',
			array( $this, 'page_dashboard' ),
			'dashicons-heart',
			3
		);
		add_submenu_page( 'beautia', __( 'Dashboard', 'beautia' ), __( 'Dashboard', 'beautia' ), $cap, 'beautia', array( $this, 'page_dashboard' ) );
		add_submenu_page( 'beautia', __( 'Appointments', 'beautia' ), __( 'Appointments', 'beautia' ), $cap, 'beautia-appointments', array( $this, 'page_appointments' ) );
		add_submenu_page( 'beautia', __( 'Booking Settings', 'beautia' ), __( 'Booking Settings', 'beautia' ), $cap, 'beautia-booking', array( $this, 'page_booking_settings' ) );
		add_submenu_page( 'beautia', __( 'SMS Gateway', 'beautia' ), __( 'SMS Gateway', 'beautia' ), $cap, 'beautia-sms', array( $this, 'page_sms' ) );
		add_submenu_page( 'beautia', __( 'SMS Log', 'beautia' ), __( 'SMS Log', 'beautia' ), $cap, 'beautia-sms-log', array( $this, 'page_sms_log' ) );
		add_submenu_page( 'beautia', __( 'Demo Importer', 'beautia' ), __( 'Demo Importer', 'beautia' ), $cap, 'beautia-demos', array( 'Beautia_Demo_Importer', 'render_page' ) );
	}

	/** Welcome notice after activation. */
	public function welcome_notice() {
		if ( ! get_transient( 'beautia_activated' ) ) {
			return;
		}
		delete_transient( 'beautia_activated' );
		printf(
			'<div class="notice notice-success is-dismissible"><p><strong>%s</strong> %s <a class="button button-primary" href="%s">%s</a></p></div>',
			esc_html__( 'Beautia is ready!', 'beautia' ),
			esc_html__( 'Import a demo to get a complete website in one click.', 'beautia' ),
			esc_url( admin_url( 'admin.php?page=beautia-demos' ) ),
			esc_html__( 'Open demo importer', 'beautia' )
		);
	}

	/** POST handlers. */
	public function handle_actions() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Booking settings.
		if ( isset( $_POST['beautia_booking_settings_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['beautia_booking_settings_nonce'] ) ), 'beautia_booking_settings' ) ) {
			$keys = array( 'slot_step', 'min_lead_minutes', 'max_days_ahead', 'cancel_hours', 'max_open_bookings', 'reminder_hours', 'currency_symbol', 'currency_position', 'calendar_type', 'time_format', 'direction', 'dark_default', 'address', 'phone', 'admin_mobile', 'holidays', 'blocked_numbers', 'phone_region', 'otp_length', 'otp_ttl', 'otp_cooldown', 'otp_max_per_hour' );
			foreach ( $keys as $k ) {
				if ( isset( $_POST[ $k ] ) ) {
					beautia_update_option( $k, sanitize_textarea_field( wp_unslash( $_POST[ $k ] ) ) );
				}
			}
			foreach ( array( 'auto_confirm', 'require_login_to_book', 'admin_email_notify', 'sms_email_fallback', 'persian_digits', 'force_persian', 'dark_mode', 'booking_drawer' ) as $k ) {
				beautia_update_option( $k, isset( $_POST[ $k ] ) ? 1 : 0 );
			}
			$hours = isset( $_POST['business_hours'] ) ? wp_unslash( $_POST['business_hours'] ) : array(); // phpcs:ignore
			$clean = array();
			foreach ( (array) $hours as $day => $row ) {
				$clean[ sanitize_key( $day ) ] = array(
					'open'       => ! empty( $row['open'] ) ? 1 : 0,
					'from'       => sanitize_text_field( $row['from'] ),
					'to'         => sanitize_text_field( $row['to'] ),
					'break_from' => sanitize_text_field( $row['break_from'] ),
					'break_to'   => sanitize_text_field( $row['break_to'] ),
				);
			}
			update_option( 'beautia_business_hours', $clean );
			add_action( 'admin_notices', array( $this, 'saved_notice' ) );
		}

		// SMS settings.
		if ( isset( $_POST['beautia_sms_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['beautia_sms_nonce'] ) ), 'beautia_sms_settings' ) ) {
			beautia_update_option( 'sms_gateway', isset( $_POST['sms_gateway'] ) ? sanitize_key( wp_unslash( $_POST['sms_gateway'] ) ) : 'log' );

			$settings = isset( $_POST['sms_settings'] ) ? wp_unslash( $_POST['sms_settings'] ) : array(); // phpcs:ignore
			update_option( 'beautia_sms_settings', array_map( 'sanitize_text_field', (array) $settings ) );

			$templates = isset( $_POST['sms_templates'] ) ? wp_unslash( $_POST['sms_templates'] ) : array(); // phpcs:ignore
			update_option( 'beautia_sms_templates', array_map( 'sanitize_textarea_field', (array) $templates ) );

			$enabled = isset( $_POST['sms_enabled'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['sms_enabled'] ) ) : array();
			update_option( 'beautia_sms_enabled', $enabled );
			add_action( 'admin_notices', array( $this, 'saved_notice' ) );
		}

		// Appointment quick actions.
		if ( isset( $_GET['beautia_action'], $_GET['appointment'], $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'beautia_appointment' ) ) {
			$id     = absint( $_GET['appointment'] );
			$action = sanitize_key( wp_unslash( $_GET['beautia_action'] ) );
			if ( 'delete' === $action ) {
				global $wpdb;
				$wpdb->delete( Beautia_Booking::table(), array( 'id' => $id ), array( '%d' ) ); // phpcs:ignore
			} else {
				Beautia_Booking::set_status( $id, $action );
			}
			wp_safe_redirect( remove_query_arg( array( 'beautia_action', 'appointment', '_wpnonce' ) ) );
			exit;
		}
	}

	/** Saved notice. */
	public function saved_notice() {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'beautia' ) . '</p></div>';
	}

	/* ------------------------------- Pages -------------------------------- */

	/** Overview. */
	public function page_dashboard() {
		$today_from = current_time( 'Y-m-d' ) . ' 00:00:00';
		$today_to   = current_time( 'Y-m-d' ) . ' 23:59:59';
		$today      = Beautia_Booking::query( array( 'from' => $today_from, 'to' => $today_to, 'order' => 'ASC', 'limit' => 100 ) );
		$month_from = current_time( 'Y-m-01' ) . ' 00:00:00';
		$revenue    = Beautia_Booking::revenue( $month_from, $today_to );
		?>
		<div class="wrap beautia-admin">
			<h1><?php esc_html_e( 'Beautia Dashboard', 'beautia' ); ?></h1>
			<div class="beautia-cards">
				<div class="beautia-card"><span><?php esc_html_e( 'Today', 'beautia' ); ?></span><strong><?php echo esc_html( count( $today ) ); ?></strong></div>
				<div class="beautia-card"><span><?php esc_html_e( 'Pending', 'beautia' ); ?></span><strong><?php echo esc_html( Beautia_Booking::count( array( 'status' => 'pending' ) ) ); ?></strong></div>
				<div class="beautia-card"><span><?php esc_html_e( 'Confirmed', 'beautia' ); ?></span><strong><?php echo esc_html( Beautia_Booking::count( array( 'status' => 'confirmed' ) ) ); ?></strong></div>
				<div class="beautia-card"><span><?php esc_html_e( 'All time', 'beautia' ); ?></span><strong><?php echo esc_html( Beautia_Booking::count() ); ?></strong></div>
				<div class="beautia-card"><span><?php esc_html_e( 'Revenue this month', 'beautia' ); ?></span><strong><?php echo esc_html( beautia_price( $revenue ) ); ?></strong></div>
			</div>

			<?php $this->revenue_chart(); ?>

			<h2><?php esc_html_e( 'Today’s schedule', 'beautia' ); ?></h2>
			<?php $this->table( $today ); ?>
		</div>
		<?php
	}

	/** Appointments list. */
	public function page_appointments() {
		$status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';    // phpcs:ignore WordPress.Security.NonceVerification
		$paged  = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;              // phpcs:ignore WordPress.Security.NonceVerification
		$rows   = Beautia_Booking::query(
			array(
				'status' => $status,
				'search' => $search,
				'limit'  => 30,
				'offset' => ( $paged - 1 ) * 30,
			)
		);
		?>
		<div class="wrap beautia-admin">
			<h1><?php esc_html_e( 'Appointments', 'beautia' ); ?></h1>
			<ul class="subsubsub">
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=beautia-appointments' ) ); ?>"><?php esc_html_e( 'All', 'beautia' ); ?></a> |</li>
				<?php foreach ( Beautia_Booking::statuses() as $key => $label ) : ?>
					<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=beautia-appointments&status=' . $key ) ); ?>"><?php echo esc_html( $label ); ?> (<?php echo esc_html( Beautia_Booking::count( array( 'status' => $key ) ) ); ?>)</a> |</li>
				<?php endforeach; ?>
			</ul>
			<form method="get" style="margin:12px 0">
				<input type="hidden" name="page" value="beautia-appointments" />
				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Name, mobile or code…', 'beautia' ); ?>" />
				<button class="button"><?php esc_html_e( 'Search', 'beautia' ); ?></button>
				<a class="button button-secondary" style="margin-inline-start:8px" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=beautia_export&status=' . $status ), 'beautia_export' ) ); ?>">
					<?php esc_html_e( 'Export CSV', 'beautia' ); ?>
				</a>
			</form>
			<?php $this->table( $rows ); ?>
			<p class="beautia-pagination">
				<?php if ( $paged > 1 ) : ?>
					<a class="button" href="<?php echo esc_url( add_query_arg( 'paged', $paged - 1 ) ); ?>">&laquo; <?php esc_html_e( 'Previous', 'beautia' ); ?></a>
				<?php endif; ?>
				<?php if ( count( $rows ) === 30 ) : ?>
					<a class="button" href="<?php echo esc_url( add_query_arg( 'paged', $paged + 1 ) ); ?>"><?php esc_html_e( 'Next', 'beautia' ); ?> &raquo;</a>
				<?php endif; ?>
			</p>
		</div>
		<?php
	}

	/** Reusable appointment table. */
	private function table( $rows ) {
		?>
		<table class="widefat striped beautia-appointments">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Code', 'beautia' ); ?></th>
					<th><?php esc_html_e( 'Customer', 'beautia' ); ?></th>
					<th><?php esc_html_e( 'Service', 'beautia' ); ?></th>
					<th><?php esc_html_e( 'Specialist', 'beautia' ); ?></th>
					<th><?php esc_html_e( 'When', 'beautia' ); ?></th>
					<th><?php esc_html_e( 'Price', 'beautia' ); ?></th>
					<th><?php esc_html_e( 'Status', 'beautia' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'beautia' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( ! $rows ) : ?>
				<tr><td colspan="8"><?php esc_html_e( 'No appointments yet.', 'beautia' ); ?></td></tr>
			<?php endif; ?>
			<?php foreach ( $rows as $row ) : ?>
				<tr>
					<td><code><?php echo esc_html( $row->code ); ?></code></td>
					<td>
						<strong><?php echo esc_html( $row->customer_name ); ?></strong><br/>
						<a href="tel:<?php echo esc_attr( $row->customer_mobile ); ?>"><?php echo esc_html( $row->customer_mobile ); ?></a>
					</td>
					<td><?php echo esc_html( get_the_title( $row->service_id ) ); ?></td>
					<td><?php echo esc_html( $row->staff_id ? get_the_title( $row->staff_id ) : '—' ); ?></td>
					<td>
						<?php echo esc_html( beautia_format_date( $row->start_at ) ); ?><br/>
						<span class="beautia-time"><?php echo esc_html( gmdate( 'H:i', strtotime( $row->start_at ) ) ); ?> – <?php echo esc_html( gmdate( 'H:i', strtotime( $row->end_at ) ) ); ?></span>
					</td>
					<td><?php echo esc_html( beautia_price( $row->price ) ); ?></td>
					<td><span class="beautia-status is-<?php echo esc_attr( $row->status ); ?>"><?php echo esc_html( Beautia_Booking::statuses()[ $row->status ] ); ?></span></td>
					<td class="beautia-row-actions">
						<?php
						foreach ( array(
							'confirmed' => __( 'Confirm', 'beautia' ),
							'completed' => __( 'Complete', 'beautia' ),
							'cancelled' => __( 'Cancel', 'beautia' ),
							'no_show'   => __( 'No-show', 'beautia' ),
							'delete'    => __( 'Delete', 'beautia' ),
						) as $action => $label ) :
							if ( $action === $row->status ) {
								continue;
							}
							$url = wp_nonce_url(
								add_query_arg(
									array(
										'beautia_action' => $action,
										'appointment'    => $row->id,
									)
								),
								'beautia_appointment'
							);
							?>
							<a class="button button-small<?php echo 'delete' === $action ? ' beautia-danger' : ''; ?>" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
						<?php endforeach; ?>
					</td>
				</tr>
				<?php if ( $row->note ) : ?>
					<tr class="beautia-note-row"><td colspan="8"><em><?php esc_html_e( 'Note:', 'beautia' ); ?></em> <?php echo esc_html( $row->note ); ?></td></tr>
				<?php endif; ?>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/** Booking settings screen. */
	public function page_booking_settings() {
		$hours = Beautia_Booking::business_hours();
		$days  = array(
			'sat' => __( 'Saturday', 'beautia' ),
			'sun' => __( 'Sunday', 'beautia' ),
			'mon' => __( 'Monday', 'beautia' ),
			'tue' => __( 'Tuesday', 'beautia' ),
			'wed' => __( 'Wednesday', 'beautia' ),
			'thu' => __( 'Thursday', 'beautia' ),
			'fri' => __( 'Friday', 'beautia' ),
		);
		?>
		<div class="wrap beautia-admin">
			<h1><?php esc_html_e( 'Booking Settings', 'beautia' ); ?></h1>
			<form method="post">
				<?php wp_nonce_field( 'beautia_booking_settings', 'beautia_booking_settings_nonce' ); ?>

				<h2><?php esc_html_e( 'Opening hours', 'beautia' ); ?></h2>
				<table class="widefat striped" style="max-width:900px">
					<thead><tr><th><?php esc_html_e( 'Day', 'beautia' ); ?></th><th><?php esc_html_e( 'Open', 'beautia' ); ?></th><th><?php esc_html_e( 'From', 'beautia' ); ?></th><th><?php esc_html_e( 'To', 'beautia' ); ?></th><th><?php esc_html_e( 'Break from', 'beautia' ); ?></th><th><?php esc_html_e( 'Break to', 'beautia' ); ?></th></tr></thead>
					<tbody>
					<?php foreach ( $days as $d => $label ) : $row = $hours[ $d ]; ?>
						<tr>
							<td><?php echo esc_html( $label ); ?></td>
							<td><input type="checkbox" name="business_hours[<?php echo esc_attr( $d ); ?>][open]" value="1" <?php checked( 1, (int) $row['open'] ); ?>></td>
							<td><input type="time" name="business_hours[<?php echo esc_attr( $d ); ?>][from]" value="<?php echo esc_attr( $row['from'] ); ?>"></td>
							<td><input type="time" name="business_hours[<?php echo esc_attr( $d ); ?>][to]" value="<?php echo esc_attr( $row['to'] ); ?>"></td>
							<td><input type="time" name="business_hours[<?php echo esc_attr( $d ); ?>][break_from]" value="<?php echo esc_attr( $row['break_from'] ); ?>"></td>
							<td><input type="time" name="business_hours[<?php echo esc_attr( $d ); ?>][break_to]" value="<?php echo esc_attr( $row['break_to'] ); ?>"></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>

				<h2><?php esc_html_e( 'Rules', 'beautia' ); ?></h2>
				<table class="form-table">
					<?php
					$fields = array(
						'slot_step'         => array( __( 'Slot step (minutes)', 'beautia' ), 15 ),
						'min_lead_minutes'  => array( __( 'Minimum lead time (minutes)', 'beautia' ), 60 ),
						'max_days_ahead'    => array( __( 'How many days ahead can be booked', 'beautia' ), 60 ),
						'cancel_hours'      => array( __( 'Customer can cancel until (hours before)', 'beautia' ), 6 ),
						'max_open_bookings' => array( __( 'Max active bookings per customer', 'beautia' ), 3 ),
						'reminder_hours'    => array( __( 'Send reminder (hours before)', 'beautia' ), 24 ),
						'currency_symbol'   => array( __( 'Currency', 'beautia' ), __( 'Toman', 'beautia' ) ),
						'address'           => array( __( 'Salon address', 'beautia' ), '' ),
						'phone'             => array( __( 'Salon phone', 'beautia' ), '' ),
						'admin_mobile'      => array( __( 'Admin mobile for SMS alerts', 'beautia' ), '' ),
					);
					foreach ( $fields as $key => $f ) :
						?>
						<tr>
							<th><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $f[0] ); ?></label></th>
							<td><input class="regular-text" type="text" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( beautia_get_option( $key, $f[1] ) ); ?>" /></td>
						</tr>
					<?php endforeach; ?>
					<tr>
						<th><?php esc_html_e( 'Calendar', 'beautia' ); ?></th>
						<td>
							<select name="calendar_type">
								<option value="gregorian" <?php selected( beautia_get_option( 'calendar_type', 'jalali' ), 'gregorian' ); ?>><?php esc_html_e( 'Gregorian', 'beautia' ); ?></option>
								<option value="jalali" <?php selected( beautia_get_option( 'calendar_type', 'jalali' ), 'jalali' ); ?>><?php esc_html_e( 'Jalali (Shamsi)', 'beautia' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Language & direction', 'beautia' ); ?></th>
						<td>
							<select name="direction">
								<option value="auto" <?php selected( beautia_get_option( 'direction', 'auto' ), 'auto' ); ?>><?php esc_html_e( 'Automatic (follow site language)', 'beautia' ); ?></option>
								<option value="rtl" <?php selected( beautia_get_option( 'direction', 'auto' ), 'rtl' ); ?>><?php esc_html_e( 'Always right-to-left', 'beautia' ); ?></option>
								<option value="ltr" <?php selected( beautia_get_option( 'direction', 'auto' ), 'ltr' ); ?>><?php esc_html_e( 'Always left-to-right', 'beautia' ); ?></option>
							</select><br>
							<label><input type="checkbox" name="force_persian" value="1" <?php checked( 1, (int) beautia_get_option( 'force_persian', 1 ) ); ?>> <?php esc_html_e( 'Always load the bundled Persian translation', 'beautia' ); ?></label><br>
							<label><input type="checkbox" name="persian_digits" value="1" <?php checked( 1, (int) beautia_get_option( 'persian_digits', 1 ) ); ?>> <?php esc_html_e( 'Display numbers with Persian digits', 'beautia' ); ?></label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Dark mode', 'beautia' ); ?></th>
						<td>
							<label><input type="checkbox" name="booking_drawer" value="1" <?php checked( 1, (int) beautia_get_option( 'booking_drawer', 1 ) ); ?>> <?php esc_html_e( 'Show the floating quick-booking button on every page', 'beautia' ); ?></label><br>
							<label><input type="checkbox" name="dark_mode" value="1" <?php checked( 1, (int) beautia_get_option( 'dark_mode', 1 ) ); ?>> <?php esc_html_e( 'Show the light / dark switch in the header', 'beautia' ); ?></label><br>
							<select name="dark_default">
								<option value="auto" <?php selected( beautia_get_option( 'dark_default', 'auto' ), 'auto' ); ?>><?php esc_html_e( 'Default: follow the visitor’s device', 'beautia' ); ?></option>
								<option value="light" <?php selected( beautia_get_option( 'dark_default', 'auto' ), 'light' ); ?>><?php esc_html_e( 'Default: always light', 'beautia' ); ?></option>
								<option value="dark" <?php selected( beautia_get_option( 'dark_default', 'auto' ), 'dark' ); ?>><?php esc_html_e( 'Default: always dark', 'beautia' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Holidays', 'beautia' ); ?></th>
						<td><textarea name="holidays" rows="4" class="large-text" placeholder="2026-03-20"><?php echo esc_textarea( beautia_get_option( 'holidays', '' ) ); ?></textarea></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Behaviour', 'beautia' ); ?></th>
						<td>
							<label><input type="checkbox" name="auto_confirm" value="1" <?php checked( 1, (int) beautia_get_option( 'auto_confirm', 0 ) ); ?>> <?php esc_html_e( 'Confirm bookings automatically', 'beautia' ); ?></label><br>
							<label><input type="checkbox" name="require_login_to_book" value="1" <?php checked( 1, (int) beautia_get_option( 'require_login_to_book', 1 ) ); ?>> <?php esc_html_e( 'Require mobile verification before booking', 'beautia' ); ?></label><br>
							<label><input type="checkbox" name="admin_email_notify" value="1" <?php checked( 1, (int) beautia_get_option( 'admin_email_notify', 1 ) ); ?>> <?php esc_html_e( 'E-mail the admin on every booking', 'beautia' ); ?></label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'OTP', 'beautia' ); ?></th>
						<td>
							<?php esc_html_e( 'Code length', 'beautia' ); ?> <input type="number" name="otp_length" min="4" max="8" value="<?php echo esc_attr( beautia_get_option( 'otp_length', 5 ) ); ?>" style="width:80px">
							<?php esc_html_e( 'Valid (minutes)', 'beautia' ); ?> <input type="number" name="otp_ttl" min="1" max="30" value="<?php echo esc_attr( beautia_get_option( 'otp_ttl', 3 ) ); ?>" style="width:80px">
							<?php esc_html_e( 'Resend cool-down (s)', 'beautia' ); ?> <input type="number" name="otp_cooldown" min="30" max="600" value="<?php echo esc_attr( beautia_get_option( 'otp_cooldown', 120 ) ); ?>" style="width:80px">
							<?php esc_html_e( 'Max per hour', 'beautia' ); ?> <input type="number" name="otp_max_per_hour" min="1" max="20" value="<?php echo esc_attr( beautia_get_option( 'otp_max_per_hour', 5 ) ); ?>" style="width:80px">
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/** SMS settings. */
	public function page_sms() {
		$gateway   = beautia_get_option( 'sms_gateway', 'log' );
		$settings  = get_option( 'beautia_sms_settings', array() );
		$enabled   = get_option( 'beautia_sms_enabled', array_keys( Beautia_SMS::default_templates() ) );
		$fields    = array(
			'kavenegar'   => array( 'kavenegar_key' => 'API Key', 'kavenegar_sender' => __( 'Sender line', 'beautia' ) ),
			'smsir'       => array( 'smsir_key' => 'API Key', 'smsir_line' => __( 'Line number', 'beautia' ) ),
			'melipayamak' => array( 'meli_user' => __( 'Username', 'beautia' ), 'meli_pass' => __( 'Password', 'beautia' ), 'meli_from' => __( 'Sender', 'beautia' ) ),
			'farazsms'    => array( 'faraz_key' => 'AccessKey', 'faraz_from' => __( 'Sender', 'beautia' ) ),
			'ghasedak'    => array( 'ghasedak_key' => 'API Key', 'ghasedak_line' => __( 'Line number', 'beautia' ) ),
			'twilio'      => array( 'twilio_sid' => 'Account SID', 'twilio_token' => 'Auth Token', 'twilio_from' => __( 'From number', 'beautia' ) ),
			'webhook'     => array( 'webhook_url' => __( 'Endpoint URL', 'beautia' ), 'webhook_token' => __( 'Secret token', 'beautia' ) ),
		);
		?>
		<div class="wrap beautia-admin">
			<h1><?php esc_html_e( 'SMS Gateway', 'beautia' ); ?></h1>
			<form method="post">
				<?php wp_nonce_field( 'beautia_sms_settings', 'beautia_sms_nonce' ); ?>
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'Provider', 'beautia' ); ?></th>
						<td>
							<select name="sms_gateway" id="beautia-gateway">
								<?php foreach ( Beautia_SMS::gateways() as $key => $label ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $gateway, $key ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'All providers are built into the theme — no paid SMS plugin needed.', 'beautia' ); ?></p>
						</td>
					</tr>
					<?php foreach ( $fields as $gw => $rows ) : ?>
						<?php foreach ( $rows as $key => $label ) : ?>
						<tr class="beautia-gw beautia-gw-<?php echo esc_attr( $gw ); ?>" style="<?php echo $gateway === $gw ? '' : 'display:none'; ?>">
							<th><?php echo esc_html( $label ); ?></th>
							<td><input class="regular-text" type="text" name="sms_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( isset( $settings[ $key ] ) ? $settings[ $key ] : '' ); ?>"></td>
						</tr>
						<?php endforeach; ?>
					<?php endforeach; ?>
					<tr>
						<th><?php esc_html_e( 'Test', 'beautia' ); ?></th>
						<td>
							<input type="text" id="beautia-test-mobile" placeholder="09120000000" />
							<button type="button" class="button" id="beautia-test-sms"><?php esc_html_e( 'Send a test SMS', 'beautia' ); ?></button>
							<span id="beautia-test-result"></span>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Message templates', 'beautia' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Available placeholders:', 'beautia' ); ?>
					<code>{name} {mobile} {service} {staff} {date} {time} {code} {price} {deposit} {status} {site} {address} {phone} {panel_url} {review_url} {ttl}</code>
				</p>
				<table class="widefat striped beautia-templates">
					<thead><tr><th style="width:60px"><?php esc_html_e( 'On', 'beautia' ); ?></th><th style="width:260px"><?php esc_html_e( 'Event', 'beautia' ); ?></th><th><?php esc_html_e( 'Message', 'beautia' ); ?></th></tr></thead>
					<tbody>
					<?php foreach ( Beautia_SMS::events() as $key => $label ) : ?>
						<tr>
							<td><input type="checkbox" name="sms_enabled[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, (array) $enabled, true ) ); ?>></td>
							<td><strong><?php echo esc_html( $label ); ?></strong><br><code><?php echo esc_html( $key ); ?></code></td>
							<td><textarea name="sms_templates[<?php echo esc_attr( $key ); ?>]" rows="3" class="large-text"><?php echo esc_textarea( Beautia_SMS::get_template( $key ) ); ?></textarea></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/** SMS log. */
	public function page_sms_log() {
		$log = get_option( 'beautia_sms_log', array() );
		?>
		<div class="wrap beautia-admin">
			<h1><?php esc_html_e( 'SMS Log', 'beautia' ); ?></h1>
			<table class="widefat striped">
				<thead><tr><th><?php esc_html_e( 'Time', 'beautia' ); ?></th><th><?php esc_html_e( 'To', 'beautia' ); ?></th><th><?php esc_html_e( 'Event', 'beautia' ); ?></th><th><?php esc_html_e( 'Status', 'beautia' ); ?></th><th><?php esc_html_e( 'Message', 'beautia' ); ?></th></tr></thead>
				<tbody>
				<?php if ( ! $log ) : ?>
					<tr><td colspan="5"><?php esc_html_e( 'Nothing sent yet.', 'beautia' ); ?></td></tr>
				<?php endif; ?>
				<?php foreach ( $log as $entry ) : ?>
					<tr>
						<td><?php echo esc_html( $entry['time'] ); ?></td>
						<td><?php echo esc_html( $entry['mobile'] ); ?></td>
						<td><?php echo esc_html( $entry['event'] ); ?></td>
						<td><span class="beautia-status is-<?php echo esc_attr( 'sent' === $entry['status'] ? 'confirmed' : 'cancelled' ); ?>"><?php echo esc_html( $entry['status'] ); ?></span></td>
						<td><small><?php echo esc_html( wp_trim_words( $entry['message'], 24 ) ); ?></small></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/* ------------------------------- AJAX --------------------------------- */

	/** Inline status change. */
	public function ajax_status() {
		check_ajax_referer( 'beautia_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}
		$id     = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : '';
		$result = Beautia_Booking::set_status( $id, $status );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success();
	}

	/** Test SMS. */
	public function ajax_test_sms() {
		check_ajax_referer( 'beautia_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}
		$mobile = isset( $_POST['mobile'] ) ? sanitize_text_field( wp_unslash( $_POST['mobile'] ) ) : '';
		$result = Beautia_SMS::instance()->send( $mobile, __( 'Beautia test message ✔', 'beautia' ), 'test' );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => __( 'Sent.', 'beautia' ) ) );
	}

	/**
	 * A dependency-free SVG bar chart of the last 14 days of bookings.
	 */
	public function revenue_chart() {
		global $wpdb;
		$table = $wpdb->prefix . 'beautia_appointments';
		$from  = gmdate( 'Y-m-d', strtotime( '-13 days', current_time( 'timestamp' ) ) ); // phpcs:ignore
		$rows  = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
			$wpdb->prepare(
				"SELECT DATE(start_time) AS d, COUNT(*) AS c, SUM(price) AS total
				 FROM {$table}
				 WHERE start_time >= %s AND status NOT IN ('cancelled','no_show')
				 GROUP BY DATE(start_time) ORDER BY d ASC",
				$from . ' 00:00:00'
			),
			ARRAY_A
		);

		$series = array();
		for ( $i = 13; $i >= 0; $i-- ) {
			$day              = gmdate( 'Y-m-d', strtotime( "-{$i} days", current_time( 'timestamp' ) ) ); // phpcs:ignore
			$series[ $day ] = array( 'c' => 0, 'total' => 0 );
		}
		foreach ( (array) $rows as $row ) {
			if ( isset( $series[ $row['d'] ] ) ) {
				$series[ $row['d'] ] = array( 'c' => (int) $row['c'], 'total' => (float) $row['total'] );
			}
		}

		$max     = max( 1, max( wp_list_pluck( $series, 'c' ) ) );
		$bookings = array_sum( wp_list_pluck( $series, 'c' ) );
		$income   = array_sum( wp_list_pluck( $series, 'total' ) );
		$w        = 100 / count( $series );
		?>
		<div class="beautia-chart">
			<h2><?php esc_html_e( 'Last 14 days', 'beautia' ); ?></h2>
			<p class="beautia-chart-sub"><?php esc_html_e( 'Confirmed and completed appointments per day.', 'beautia' ); ?></p>
			<svg viewBox="0 0 100 40" preserveAspectRatio="none" role="img" aria-label="<?php esc_attr_e( 'Last 14 days', 'beautia' ); ?>">
				<defs>
					<linearGradient id="beautiaGrad" x1="0" y1="1" x2="0" y2="0">
						<stop offset="0%" stop-color="#B57EDC" />
						<stop offset="100%" stop-color="#E8A0BF" />
					</linearGradient>
				</defs>
				<?php $x = 0; ?>
				<?php foreach ( $series as $day => $row ) : ?>
					<?php
					$h = $row['c'] ? max( 1.5, ( $row['c'] / $max ) * 34 ) : 0.6;
					?>
					<rect class="bar" x="<?php echo esc_attr( $x + $w * 0.18 ); ?>" y="<?php echo esc_attr( 36 - $h ); ?>"
						width="<?php echo esc_attr( $w * 0.64 ); ?>" height="<?php echo esc_attr( $h ); ?>">
						<title><?php echo esc_html( beautia_format_date( $day ) . ' — ' . beautia_num( $row['c'] ) ); ?></title>
					</rect>
					<?php $x += $w; ?>
				<?php endforeach; ?>
			</svg>
			<div class="beautia-chart-legend">
				<span><?php esc_html_e( 'Appointments', 'beautia' ); ?> <b><?php echo esc_html( beautia_num( $bookings ) ); ?></b></span>
				<span><?php esc_html_e( 'Value', 'beautia' ); ?> <b><?php echo esc_html( beautia_price( $income ) ); ?></b></span>
				<span><?php esc_html_e( 'Busiest day', 'beautia' ); ?> <b><?php echo esc_html( beautia_num( $max ) ); ?></b></span>
			</div>
		</div>
		<?php
	}

	/**
	 * Export the appointment list as a UTF-8 CSV (opens correctly in Excel
	 * thanks to the BOM, which Persian text needs).
	 */
	public function export_csv() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'beautia' ) );
		}
		check_admin_referer( 'beautia_export' );

		$rows = Beautia_Booking::query(
			array(
				'status' => isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '',
				'limit'  => 5000,
			)
		);

		nocache_headers();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename=beautia-appointments-' . gmdate( 'Y-m-d' ) . '.csv' );

		$out = fopen( 'php://output', 'w' );
		fwrite( $out, "\xEF\xBB\xBF" ); // UTF-8 BOM.
		fputcsv(
			$out,
			array(
				__( 'Code', 'beautia' ),
				__( 'Date', 'beautia' ),
				__( 'Time', 'beautia' ),
				__( 'Service', 'beautia' ),
				__( 'Specialist', 'beautia' ),
				__( 'Customer', 'beautia' ),
				__( 'Mobile', 'beautia' ),
				__( 'Price', 'beautia' ),
				__( 'Status', 'beautia' ),
			)
		);
		foreach ( $rows as $row ) {
			fputcsv(
				$out,
				array(
					$row->code,
					beautia_format_date( gmdate( 'Y-m-d', strtotime( $row->start_time ) ) ),
					gmdate( 'H:i', strtotime( $row->start_time ) ),
					get_the_title( $row->service_id ),
					$row->staff_id ? get_the_title( $row->staff_id ) : __( 'Any specialist', 'beautia' ),
					$row->customer_name,
					$row->customer_mobile,
					$row->price,
					$row->status,
				)
			);
		}
		fclose( $out );
		exit;
	}

}
