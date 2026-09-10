<?php
/**
 * One-click demo importer — builds a complete website (pages, menus, services,
 * team, portfolio, testimonials, options) without any external plugin.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Beautia_Demo_Importer
 */
class Beautia_Demo_Importer {

	/** @var Beautia_Demo_Importer|null */
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
		add_action( 'wp_ajax_beautia_import_demo', array( $this, 'ajax_import' ) );
	}

	/** Demo data file. */
	public static function data( $demo ) {
		if ( function_exists( 'beautia_core_demo_data' ) ) {
			$package = beautia_core_demo_data( sanitize_key( $demo ) );
			if ( $package ) {
				return $package;
			}
		}
		$file = BEAUTIA_DIR . 'demos/' . sanitize_key( $demo ) . '.php';
		return file_exists( $file ) ? include $file : array();
	}

	/** Admin page. */
	public static function render_page() {
		?>
		<div class="wrap beautia-admin beautia-demos">
			<h1><?php esc_html_e( 'Beautia Demo Importer', 'beautia' ); ?></h1>
			<p><?php esc_html_e( 'Pick a demo. The importer creates the pages, menus, services, team, portfolio, testimonials, opening hours and colour scheme for you. You can import several demos into one site — every page can use its own style.', 'beautia' ); ?></p>
			<div class="beautia-demo-grid">
				<?php foreach ( beautia_get_demos() as $slug => $demo ) : ?>
					<div class="beautia-demo-card" style="--accent:<?php echo esc_attr( $demo['accent'] ); ?>;--accent2:<?php echo esc_attr( $demo['accent2'] ); ?>">
						<div class="beautia-demo-thumb">
							<img src="<?php echo esc_url( beautia_img( 'demo-' . $slug . '.jpg' ) ); ?>" alt="<?php echo esc_attr( $demo['label'] ); ?>" onerror="this.style.display='none'" />
							<span class="beautia-demo-swatch"></span>
						</div>
						<h3><?php echo esc_html( $demo['label'] ); ?></h3>
						<p><?php echo esc_html( $demo['desc'] ); ?></p>
						<button class="button button-primary beautia-import" data-demo="<?php echo esc_attr( $slug ); ?>">
							<?php esc_html_e( 'Import this demo', 'beautia' ); ?>
						</button>
						<span class="beautia-import-status"></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/** AJAX entry point. */
	public function ajax_import() {
		check_ajax_referer( 'beautia_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Not allowed.', 'beautia' ) ) );
		}
		$demo   = isset( $_POST['demo'] ) ? sanitize_key( wp_unslash( $_POST['demo'] ) ) : '';
		$result = $this->import( $demo );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => __( 'Demo imported successfully.', 'beautia' ), 'url' => home_url( '/' ) ) );
	}

	/**
	 * Run the import.
	 *
	 * @param string $demo Demo slug.
	 */
	public function import( $demo ) {
		$external = function_exists( 'beautia_core_demo_data' ) ? beautia_core_demo_data( $demo ) : array();
		if (get_option('beautia_showcase_enabled') && ! $external) {
			require_once BEAUTIA_DIR . 'inc/showcase-seed.php';
			return beautia_seed_demo($demo);
		}
		$GLOBALS['beautia_importing_demo'] = sanitize_key( $demo );
		$data = self::data( $demo );
		if ( ! $data ) {
			return new WP_Error( 'no_demo', __( 'Unknown demo.', 'beautia' ) );
		}

		@set_time_limit( 300 ); // phpcs:ignore

		$map = array( 'service' => array(), 'staff' => array(), 'portfolio' => array() );

		// 1. Services.
		foreach ( $data['services'] as $item ) {
			$id = $this->insert_post( 'beautia_service', $item['title'], $item['content'], $item['image'] );
			update_post_meta( $id, '_beautia_price', $item['price'] );
			update_post_meta( $id, '_beautia_duration', $item['duration'] );
			update_post_meta( $id, '_beautia_buffer', isset( $item['buffer'] ) ? $item['buffer'] : 10 );
			update_post_meta( $id, '_beautia_icon', $item['icon'] );
			update_post_meta( $id, '_beautia_bookable', 1 );
			update_post_meta( $id, '_beautia_capacity', 1 );
			if ( ! empty( $item['highlights'] ) ) {
				update_post_meta( $id, '_beautia_highlights', implode( "\n", $item['highlights'] ) );
			}
			if ( ! empty( $item['aftercare'] ) ) {
				update_post_meta( $id, '_beautia_aftercare', implode( "\n", $item['aftercare'] ) );
			}
			if ( ! empty( $item['deposit'] ) ) {
				update_post_meta( $id, '_beautia_deposit', $item['deposit'] );
			}
			if ( ! empty( $item['price_old'] ) ) {
				update_post_meta( $id, '_beautia_price_old', $item['price_old'] );
			}
			if ( ! empty( $item['category'] ) ) {
				wp_set_object_terms( $id, $item['category'], 'beautia_service_cat' );
			}
			$map['service'][] = $id;
		}

		// 2. Team.
		foreach ( $data['staff'] as $item ) {
			$id = $this->insert_post( 'beautia_staff', $item['name'], $item['bio'], $item['image'] );
			update_post_meta( $id, '_beautia_role', $item['role'] );
			update_post_meta( $id, '_beautia_experience', $item['experience'] );
			update_post_meta( $id, '_beautia_schedule', $this->default_schedule() );
			$map['staff'][] = $id;
		}

		// Assign every service to the imported team.
		foreach ( $map['service'] as $sid ) {
			update_post_meta( $sid, '_beautia_staff_ids', $map['staff'] );
		}

		// 3. Portfolio.
		foreach ( $data['portfolio'] as $item ) {
			$id = $this->insert_post( 'beautia_portfolio', $item['title'], isset( $item['content'] ) ? $item['content'] : '', $item['image'] );
			if ( ! empty( $item['category'] ) ) {
				wp_set_object_terms( $id, $item['category'], 'beautia_portfolio_cat' );
			}
			$map['portfolio'][] = $id;
		}

		// 3b. Before / after gallery.
		if ( ! empty( $data['gallery'] ) ) {
			foreach ( $data['gallery'] as $item ) {
				$id     = $this->insert_post( 'beautia_gallery', $item['title'], isset( $item['content'] ) ? $item['content'] : '', $item['after'] );
				$before = $this->sideload( $item['before'], $id );
				$after  = $this->sideload( $item['after'], $id );
				if ( $before ) {
					update_post_meta( $id, '_beautia_before', wp_get_attachment_url( $before ) );
				}
				if ( $after ) {
					update_post_meta( $id, '_beautia_after', wp_get_attachment_url( $after ) );
				}
			}
		}

		// 4. Testimonials.
		foreach ( $data['testimonials'] as $item ) {
			$id = $this->insert_post( 'beautia_testimonial', $item['name'], $item['text'], '' );
			update_post_meta( $id, '_beautia_author_role', $item['role'] );
			update_post_meta( $id, '_beautia_rating', isset( $item['rating'] ) ? $item['rating'] : 5 );
		}

		// 5. Blog posts.
		foreach ( $data['posts'] as $item ) {
			$this->insert_post( 'post', $item['title'], $item['content'], $item['image'] );
		}

		// 6. Pages + front page.
		beautia_create_default_pages();
		$pages = array();
		foreach ( $data['pages'] as $slug => $page ) {
			$id = $this->insert_post( 'page', $page['title'], isset( $page['content'] ) ? $page['content'] : '', isset( $page['image'] ) ? $page['image'] : '' );
			if ( ! empty( $page['template'] ) ) {
				update_post_meta( $id, '_wp_page_template', $page['template'] );
			}
			update_post_meta( $id, '_beautia_demo', $demo );
			$pages[ $slug ] = $id;
		}
		if ( isset( $pages['home'] ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $pages['home'] );
		}
		if ( isset( $pages['blog'] ) ) {
			update_option( 'page_for_posts', $pages['blog'] );
		}

		// 7. Menu.
		$this->build_menu( $data, $pages );

		// 8. Options / theme mods.
		set_theme_mod( 'beautia_demo', $demo );
		foreach ( $data['options'] as $key => $value ) {
			beautia_update_option( $key, $value );
		}
		update_option( 'beautia_business_hours', $this->default_schedule() );
		if ( ! get_option( 'beautia_sms_templates' ) ) {
			update_option( 'beautia_sms_templates', Beautia_SMS::default_templates() );
		}

		Beautia_Booking::install();
		flush_rewrite_rules();
		update_option( 'beautia_imported_demo', $demo );
		unset( $GLOBALS['beautia_importing_demo'] );

		return true;
	}

	/** Default weekly schedule. */
	private function default_schedule() {
		$s = array();
		foreach ( array( 'sat', 'sun', 'mon', 'tue', 'wed', 'thu', 'fri' ) as $d ) {
			$s[ $d ] = array(
				'open'       => 'fri' === $d ? 0 : 1,
				'from'       => '10:00',
				'to'         => '20:00',
				'break_from' => '13:30',
				'break_to'   => '14:30',
			);
		}
		return $s;
	}

	/** Insert (or reuse) a post and attach the featured image. */
	private function insert_post( $type, $title, $content, $image = '' ) {
		$found = get_posts(
			array(
				'post_type'              => $type,
				'title'                  => $title,
				'post_status'            => 'any',
				'posts_per_page'         => 1,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
			)
		);
		$existing = $found ? $found[0] : null;
		if ( $existing ) {
			$id = $existing->ID;
			wp_update_post( array( 'ID' => $id, 'post_content' => $content, 'post_status' => 'publish' ) );
		} else {
			$id = wp_insert_post(
				array(
					'post_type'    => $type,
					'post_title'   => $title,
					'post_content' => $content,
					'post_status'  => 'publish',
					'post_excerpt' => wp_trim_words( wp_strip_all_tags( $content ), 26 ),
				)
			);
		}
		if ( $image && ! has_post_thumbnail( $id ) ) {
			$attachment = $this->sideload( $image, $id );
			if ( $attachment ) {
				set_post_thumbnail( $id, $attachment );
			}
		}
		return $id;
	}

	/** Copy a bundled demo image into the media library. */
	private function sideload( $file, $post_id ) {
		$path = BEAUTIA_DIR . 'assets/img/' . $file;
		if ( function_exists( 'beautia_core_demo_asset' ) && ! empty( $GLOBALS['beautia_importing_demo'] ) ) {
			$package_path = beautia_core_demo_asset( $GLOBALS['beautia_importing_demo'], $file );
			if ( $package_path ) {
				$path = $package_path;
			}
		}
		if ( ! file_exists( $path ) ) {
			return 0;
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$upload = wp_upload_bits( basename( $file ), null, file_get_contents( $path ) ); // phpcs:ignore
		if ( ! empty( $upload['error'] ) ) {
			return 0;
		}
		$type = wp_check_filetype( $upload['file'] );
		$id   = wp_insert_attachment(
			array(
				'post_mime_type' => $type['type'],
				'post_title'     => sanitize_file_name( basename( $file ) ),
				'post_status'    => 'inherit',
			),
			$upload['file'],
			$post_id
		);
		if ( ! $id ) {
			return 0;
		}
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
		return $id;
	}

	/** Create the primary navigation. */
	private function build_menu( $data, $pages ) {
		$name = 'Beautia Primary';
		$menu = wp_get_nav_menu_object( $name );
		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( $name );
		} else {
			$menu_id = $menu->term_id;
			foreach ( wp_get_nav_menu_items( $menu_id ) as $item ) {
				wp_delete_post( $item->ID, true );
			}
		}
		foreach ( $data['menu'] as $item ) {
			$page_id = isset( $pages[ $item['page'] ] ) ? $pages[ $item['page'] ] : 0;
			if ( ! $page_id && isset( $item['option_page'] ) ) {
				$page_id = (int) beautia_get_option( 'page_' . $item['option_page'] );
			}
			if ( ! $page_id ) {
				continue;
			}
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'     => $item['title'],
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $page_id,
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				)
			);
		}
		$locations            = get_theme_mod( 'nav_menu_locations', array() );
		$locations['primary'] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}
}
