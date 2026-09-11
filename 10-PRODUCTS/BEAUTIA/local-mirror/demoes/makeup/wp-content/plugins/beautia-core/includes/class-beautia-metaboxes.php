<?php
/**
 * Lightweight meta boxes (no ACF / paid plugin required).
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Beautia_Metaboxes
 */
class Beautia_Metaboxes {

	/** @var Beautia_Metaboxes|null */
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
		add_action( 'add_meta_boxes', array( $this, 'add' ) );
		add_action( 'save_post', array( $this, 'save' ), 10, 2 );
	}

	/** Field schema per post type. */
	public function fields( $post_type ) {
		$schemas = array(
			'beautia_service'     => array(
				'_beautia_price'        => array( 'number', __( 'Price', 'beautia' ) ),
				'_beautia_price_old'    => array( 'number', __( 'Compare-at price (optional)', 'beautia' ) ),
				'_beautia_duration'     => array( 'number', __( 'Duration (minutes)', 'beautia' ), 60 ),
				'_beautia_buffer'       => array( 'number', __( 'Clean-up buffer after service (minutes)', 'beautia' ), 10 ),
				'_beautia_deposit'      => array( 'number', __( 'Required deposit (0 = none)', 'beautia' ), 0 ),
				'_beautia_capacity'     => array( 'number', __( 'Parallel capacity per slot', 'beautia' ), 1 ),
				'_beautia_icon'         => array( 'icon', __( 'Icon', 'beautia' ), 'sparkle' ),
				'_beautia_aftercare'    => array( 'textarea', __( 'Aftercare steps (one per line)', 'beautia' ), '' ),
				'_beautia_staff_ids'    => array( 'posts', __( 'Staff who can perform it', 'beautia' ), 'beautia_staff' ),
				'_beautia_highlights'   => array( 'textarea', __( 'Highlights (one per line)', 'beautia' ) ),
				'_beautia_bookable'     => array( 'checkbox', __( 'Bookable online', 'beautia' ), 1 ),
			),
			'beautia_staff'       => array(
				'_beautia_role'      => array( 'text', __( 'Job title', 'beautia' ) ),
				'_beautia_experience'=> array( 'text', __( 'Years of experience', 'beautia' ) ),
				'_beautia_mobile'    => array( 'text', __( 'Mobile (for SMS notifications)', 'beautia' ) ),
				'_beautia_instagram' => array( 'text', __( 'Instagram URL', 'beautia' ) ),
				'_beautia_whatsapp'  => array( 'text', __( 'WhatsApp number', 'beautia' ) ),
				'_beautia_user_id'   => array( 'number', __( 'Linked WordPress user ID (optional)', 'beautia' ) ),
				'_beautia_schedule'  => array( 'schedule', __( 'Weekly working hours', 'beautia' ) ),
				'_beautia_daysoff'   => array( 'textarea', __( 'Days off (YYYY-MM-DD, one per line)', 'beautia' ) ),
			),
			'beautia_portfolio'   => array(
				'_beautia_client'    => array( 'text', __( 'Client', 'beautia' ) ),
				'_beautia_service_id'=> array( 'posts', __( 'Related service', 'beautia' ), 'beautia_service' ),
				'_beautia_gallery'   => array( 'textarea', __( 'Gallery image URLs (one per line)', 'beautia' ) ),
			),
			'beautia_testimonial' => array(
				'_beautia_author_role' => array( 'text', __( 'Author role / service', 'beautia' ) ),
				'_beautia_rating'      => array( 'number', __( 'Rating 1-5', 'beautia' ), 5 ),
			),
			'beautia_gallery'     => array(
				'_beautia_before' => array( 'text', __( 'Before image URL', 'beautia' ) ),
				'_beautia_after'  => array( 'text', __( 'After image URL', 'beautia' ) ),
			),
			'page'                => array(
				'_beautia_demo'         => array( 'demo', __( 'Force demo style on this page', 'beautia' ) ),
				'_beautia_hide_header'  => array( 'checkbox', __( 'Transparent header', 'beautia' ) ),
				'_beautia_subtitle'     => array( 'text', __( 'Page subtitle', 'beautia' ) ),
			),
		);
		return isset( $schemas[ $post_type ] ) ? $schemas[ $post_type ] : array();
	}

	/** Register boxes. */
	public function add() {
		foreach ( array( 'beautia_service', 'beautia_staff', 'beautia_portfolio', 'beautia_testimonial', 'beautia_gallery', 'page' ) as $pt ) {
			add_meta_box(
				'beautia_meta',
				__( 'Beautia Options', 'beautia' ),
				array( $this, 'render' ),
				$pt,
				'normal',
				'high'
			);
		}
	}

	/** Render the box. */
	public function render( $post ) {
		$fields = $this->fields( $post->post_type );
		if ( ! $fields ) {
			return;
		}
		wp_nonce_field( 'beautia_meta_save', 'beautia_meta_nonce' );
		echo '<div class="beautia-meta-grid">';
		foreach ( $fields as $key => $field ) {
			$type    = $field[0];
			$label   = $field[1];
			$default = isset( $field[2] ) ? $field[2] : '';
			$value   = get_post_meta( $post->ID, $key, true );
			if ( '' === $value ) {
				$value = $default;
			}
			echo '<p class="beautia-field beautia-field-' . esc_attr( $type ) . '"><label for="' . esc_attr( $key ) . '"><strong>' . esc_html( $label ) . '</strong></label><br/>';
			switch ( $type ) {
				case 'textarea':
					echo '<textarea rows="4" style="width:100%" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">' . esc_textarea( $value ) . '</textarea>';
					break;
				case 'checkbox':
					echo '<input type="checkbox" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="1" ' . checked( 1, (int) $value, false ) . ' />';
					break;
				case 'icon':
					echo '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">';
					foreach ( array_keys( beautia_icon_set() ) as $icon ) {
						echo '<option value="' . esc_attr( $icon ) . '" ' . selected( $value, $icon, false ) . '>' . esc_html( $icon ) . '</option>';
					}
					echo '</select>';
					break;
				case 'demo':
					echo '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '"><option value="">' . esc_html__( '— Global —', 'beautia' ) . '</option>';
					foreach ( beautia_get_demos() as $slug => $demo ) {
						echo '<option value="' . esc_attr( $slug ) . '" ' . selected( $value, $slug, false ) . '>' . esc_html( $demo['label'] ) . '</option>';
					}
					echo '</select>';
					break;
				case 'posts':
					$pt      = isset( $field[2] ) ? $field[2] : 'post';
					$items   = get_posts( array( 'post_type' => $pt, 'numberposts' => 200, 'post_status' => 'publish' ) );
					$value   = is_array( $value ) ? $value : array_filter( (array) $value );
					echo '<select multiple size="6" style="min-width:260px" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '[]">';
					foreach ( $items as $item ) {
						echo '<option value="' . esc_attr( $item->ID ) . '" ' . ( in_array( (string) $item->ID, array_map( 'strval', $value ), true ) ? 'selected' : '' ) . '>' . esc_html( $item->post_title ) . '</option>';
					}
					echo '</select>';
					break;
				case 'schedule':
					$this->render_schedule( $key, $value );
					break;
				default:
					echo '<input type="' . esc_attr( $type ) . '" style="width:100%" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
			}
			echo '</p>';
		}
		echo '</div>';
	}

	/** Weekly schedule grid. */
	private function render_schedule( $key, $value ) {
		$days     = array(
			'mon' => __( 'Monday', 'beautia' ),
			'tue' => __( 'Tuesday', 'beautia' ),
			'wed' => __( 'Wednesday', 'beautia' ),
			'thu' => __( 'Thursday', 'beautia' ),
			'fri' => __( 'Friday', 'beautia' ),
			'sat' => __( 'Saturday', 'beautia' ),
			'sun' => __( 'Sunday', 'beautia' ),
		);
		$value = is_array( $value ) ? $value : array();
		echo '<table class="beautia-schedule widefat striped"><thead><tr><th>' . esc_html__( 'Day', 'beautia' ) . '</th><th>' . esc_html__( 'Open', 'beautia' ) . '</th><th>' . esc_html__( 'From', 'beautia' ) . '</th><th>' . esc_html__( 'To', 'beautia' ) . '</th><th>' . esc_html__( 'Break from', 'beautia' ) . '</th><th>' . esc_html__( 'Break to', 'beautia' ) . '</th></tr></thead><tbody>';
		foreach ( $days as $d => $label ) {
			$row   = isset( $value[ $d ] ) ? $value[ $d ] : array();
			$open  = ! empty( $row['open'] );
			$from  = isset( $row['from'] ) ? $row['from'] : '10:00';
			$to    = isset( $row['to'] ) ? $row['to'] : '20:00';
			$bfrom = isset( $row['break_from'] ) ? $row['break_from'] : '';
			$bto   = isset( $row['break_to'] ) ? $row['break_to'] : '';
			echo '<tr>';
			echo '<td style="width:120px">' . esc_html( $label ) . '</td>';
			echo '<td><label><input type="checkbox" name="' . esc_attr( $key ) . '[' . esc_attr( $d ) . '][open]" value="1" ' . checked( true, $open, false ) . '> ' . esc_html__( 'Open', 'beautia' ) . '</label></td>';
			echo '<td><input type="time" name="' . esc_attr( $key ) . '[' . esc_attr( $d ) . '][from]" value="' . esc_attr( $from ) . '"></td>';
			echo '<td><input type="time" name="' . esc_attr( $key ) . '[' . esc_attr( $d ) . '][to]" value="' . esc_attr( $to ) . '"></td>';
			echo '<td><input type="time" name="' . esc_attr( $key ) . '[' . esc_attr( $d ) . '][break_from]" value="' . esc_attr( $bfrom ) . '"></td>';
			echo '<td><input type="time" name="' . esc_attr( $key ) . '[' . esc_attr( $d ) . '][break_to]" value="' . esc_attr( $bto ) . '"></td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}

	/** Save handler. */
	public function save( $post_id, $post ) {
		if ( ! isset( $_POST['beautia_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['beautia_meta_nonce'] ) ), 'beautia_meta_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		foreach ( $this->fields( $post->post_type ) as $key => $field ) {
			$type = $field[0];
			if ( 'checkbox' === $type ) {
				update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? 1 : 0 );
				continue;
			}
			if ( ! isset( $_POST[ $key ] ) ) {
				delete_post_meta( $post_id, $key );
				continue;
			}
			$raw = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			if ( 'posts' === $type ) {
				update_post_meta( $post_id, $key, array_map( 'absint', (array) $raw ) );
			} elseif ( 'schedule' === $type ) {
				$clean = array();
				foreach ( (array) $raw as $day => $row ) {
					$clean[ sanitize_key( $day ) ] = array(
						'open'       => ! empty( $row['open'] ) ? 1 : 0,
						'from'       => sanitize_text_field( isset( $row['from'] ) ? $row['from'] : '' ),
						'to'         => sanitize_text_field( isset( $row['to'] ) ? $row['to'] : '' ),
						'break_from' => sanitize_text_field( isset( $row['break_from'] ) ? $row['break_from'] : '' ),
						'break_to'   => sanitize_text_field( isset( $row['break_to'] ) ? $row['break_to'] : '' ),
					);
				}
				update_post_meta( $post_id, $key, $clean );
			} elseif ( 'textarea' === $type ) {
				update_post_meta( $post_id, $key, sanitize_textarea_field( $raw ) );
			} else {
				update_post_meta( $post_id, $key, sanitize_text_field( $raw ) );
			}
		}
	}
}
