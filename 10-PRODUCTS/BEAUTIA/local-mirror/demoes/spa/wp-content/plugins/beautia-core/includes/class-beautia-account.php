<?php
/**
 * Front-end customer panel.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Beautia_Account
 */
class Beautia_Account {

	/** @var Beautia_Account|null */
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
		add_action( 'wp_ajax_beautia_update_profile', array( $this, 'ajax_update_profile' ) );
		add_action( 'wp_ajax_beautia_toggle_favorite', array( $this, 'ajax_toggle_favorite' ) );
		add_action( 'init', array( $this, 'maybe_logout' ) );
		add_filter( 'show_admin_bar', array( $this, 'hide_admin_bar' ) );
	}

	/** Panel tabs. */
	public static function tabs() {
		return apply_filters(
			'beautia_account_tabs',
			array(
				'dashboard'    => array( 'label' => __( 'Dashboard', 'beautia' ), 'icon' => 'grid' ),
				'appointments' => array( 'label' => __( 'My Appointments', 'beautia' ), 'icon' => 'calendar' ),
				'history'      => array( 'label' => __( 'History', 'beautia' ), 'icon' => 'clock' ),
				'favorites'    => array( 'label' => __( 'Favorites', 'beautia' ), 'icon' => 'heart' ),
				'loyalty'      => array( 'label' => __( 'Loyalty Club', 'beautia' ), 'icon' => 'gift' ),
				'profile'      => array( 'label' => __( 'Profile', 'beautia' ), 'icon' => 'user' ),
				'logout'       => array( 'label' => __( 'Logout', 'beautia' ), 'icon' => 'logout' ),
			)
		);
	}

	/** Current tab. */
	public static function current_tab() {
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'dashboard'; // phpcs:ignore WordPress.Security.NonceVerification
		return array_key_exists( $tab, self::tabs() ) ? $tab : 'dashboard';
	}

	/** URL of a tab. */
	public static function tab_url( $tab ) {
		return add_query_arg( 'tab', $tab, beautia_get_page_url( 'account' ) );
	}

	/** Stats for the dashboard cards. */
	public static function stats( $user_id ) {
		$all       = Beautia_Booking::query( array( 'user_id' => $user_id, 'limit' => 500 ) );
		$upcoming  = 0;
		$completed = 0;
		$spent     = 0;
		$now       = current_time( 'mysql' );
		foreach ( $all as $row ) {
			if ( in_array( $row->status, array( 'pending', 'confirmed' ), true ) && $row->start_at >= $now ) {
				$upcoming++;
			}
			if ( 'completed' === $row->status ) {
				$completed++;
				$spent += (float) $row->price;
			}
		}
		return array(
			'total'     => count( $all ),
			'upcoming'  => $upcoming,
			'completed' => $completed,
			'spent'     => $spent,
			'points'    => (int) get_user_meta( $user_id, 'beautia_points', true ),
			'tier'      => self::tier( $completed ),
		);
	}

	/** Loyalty tier. */
	public static function tier( $completed ) {
		if ( $completed >= 20 ) {
			return array( 'name' => __( 'Diamond', 'beautia' ), 'discount' => 20, 'next' => 0 );
		}
		if ( $completed >= 10 ) {
			return array( 'name' => __( 'Gold', 'beautia' ), 'discount' => 12, 'next' => 20 - $completed );
		}
		if ( $completed >= 4 ) {
			return array( 'name' => __( 'Silver', 'beautia' ), 'discount' => 7, 'next' => 10 - $completed );
		}
		return array( 'name' => __( 'Bronze', 'beautia' ), 'discount' => 0, 'next' => 4 - $completed );
	}

	/** Favorite service IDs. */
	public static function favorites( $user_id ) {
		$fav = get_user_meta( $user_id, 'beautia_favorites', true );
		return array_filter( array_map( 'absint', (array) $fav ) );
	}

	/** AJAX: profile update. */
	public function ajax_update_profile() {
		check_ajax_referer( 'beautia_nonce', 'nonce' );
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Please log in.', 'beautia' ) ) );
		}
		$user_id = get_current_user_id();
		$first   = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
		$last    = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
		$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$bday    = isset( $_POST['birthday'] ) ? sanitize_text_field( wp_unslash( $_POST['birthday'] ) ) : '';

		$data = array( 'ID' => $user_id );
		if ( $first ) {
			$data['first_name']   = $first;
			$data['display_name'] = trim( $first . ' ' . $last );
		}
		if ( $last ) {
			$data['last_name'] = $last;
		}
		if ( $email && is_email( $email ) ) {
			$data['user_email'] = $email;
		}
		wp_update_user( $data );
		if ( $bday ) {
			update_user_meta( $user_id, 'beautia_birthday', $bday );
		}
		if ( isset( $_POST['sms_optin'] ) ) {
			update_user_meta( $user_id, 'beautia_sms_optin', absint( $_POST['sms_optin'] ) );
		}
		wp_send_json_success( array( 'message' => __( 'Your profile has been updated.', 'beautia' ) ) );
	}

	/** AJAX: favorite toggle. */
	public function ajax_toggle_favorite() {
		check_ajax_referer( 'beautia_nonce', 'nonce' );
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Please log in first.', 'beautia' ), 'needLogin' => true ) );
		}
		$user_id = get_current_user_id();
		$id      = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$fav     = self::favorites( $user_id );
		if ( in_array( $id, $fav, true ) ) {
			$fav   = array_diff( $fav, array( $id ) );
			$state = 'removed';
		} else {
			$fav[] = $id;
			$state = 'added';
		}
		update_user_meta( $user_id, 'beautia_favorites', array_values( $fav ) );
		wp_send_json_success( array( 'state' => $state ) );
	}

	/** Front-end logout link. */
	public function maybe_logout() {
		if ( isset( $_GET['beautia_logout'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'beautia_logout' ) ) {
			wp_logout();
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}
	}

	/** Customers never see the WP admin bar. */
	public function hide_admin_bar( $show ) {
		if ( current_user_can( 'manage_options' ) || current_user_can( 'edit_posts' ) ) {
			return $show;
		}
		return false;
	}
}
