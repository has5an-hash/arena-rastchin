<?php
/**
 * Optional Elementor integration.
 *
 * Beautia remains fully functional without Elementor. When Elementor is active,
 * every native section becomes a first-class editor widget and still renders
 * through the same audited template functions used by the theme.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

/** Register Beautia widgets without making Elementor a hard dependency. */
final class Beautia_Elementor {
	/** @var Beautia_Elementor|null */
	private static $instance = null;

	/** @var bool Prevent duplicate hook registration. */
	private $registered = false;

	/** Singleton. */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** Wire the integration only after plugins have loaded. */
	private function __construct() {
		// Themes load after plugins, so Elementor's bootstrap event may already
		// have fired by the time this class is included.
		if ( did_action( 'plugins_loaded' ) ) {
			$this->register_hooks();
		} else {
			add_action( 'plugins_loaded', array( $this, 'register_hooks' ), 20 );
		}
	}

	/** Register Elementor hooks when the supported API is available. */
	public function register_hooks() {
		if ( $this->registered || ! did_action( 'elementor/loaded' ) ) {
			return;
		}
		$this->registered = true;
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
		add_action( 'elementor/theme/register_locations', array( $this, 'register_locations' ) );
	}

	/** Keep optional compatibility with Elementor Theme Builder when installed. */
	public function register_locations( $location_manager ) {
		$location_manager->register_all_core_location();
	}

	/** Add a dedicated editor category. */
	public function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'beautia',
			array(
				'title' => __( 'Beautia Sections', 'beautia' ),
				'icon'  => 'eicon-favorite',
			)
		);
	}

	/**
	 * Register native sections as individual widgets.
	 *
	 * A compact anonymous adapter keeps all widgets consistent and avoids
	 * duplicating rendering logic. Elementor controls are translated to the
	 * public shortcodes, so Gutenberg, Elementor and classic content stay equal.
	 */
	public function register_widgets( $widgets_manager ) {
		if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) {
			return;
		}

		$widgets = array(
			'hero'          => array( __( 'Hero', 'beautia' ), 'beautia_hero', false ),
			'about'         => array( __( 'About', 'beautia' ), 'beautia_about', false ),
			'services'      => array( __( 'Services', 'beautia' ), 'beautia_services', true ),
			'portfolio'     => array( __( 'Portfolio', 'beautia' ), 'beautia_portfolio', true ),
			'team'          => array( __( 'Team', 'beautia' ), 'beautia_team', true ),
			'testimonials'  => array( __( 'Testimonials', 'beautia' ), 'beautia_testimonials', false ),
			'pricing'       => array( __( 'Pricing', 'beautia' ), 'beautia_pricing', false ),
			'features'      => array( __( 'Features', 'beautia' ), 'beautia_features', false ),
			'before-after'  => array( __( 'Before & After', 'beautia' ), 'beautia_before_after', false ),
			'stats'         => array( __( 'Statistics', 'beautia' ), 'beautia_stats', false ),
			'steps'         => array( __( 'How It Works', 'beautia' ), 'beautia_steps', false ),
			'faq'           => array( __( 'FAQ', 'beautia' ), 'beautia_faq', false ),
			'blog'          => array( __( 'Blog', 'beautia' ), 'beautia_blog', true ),
			'cta'           => array( __( 'Call to Action', 'beautia' ), 'beautia_cta', false ),
			'booking'       => array( __( 'Booking Wizard', 'beautia' ), 'beautia_booking', false ),
			'login'         => array( __( 'Mobile Login', 'beautia' ), 'beautia_login', false ),
			'account'       => array( __( 'Customer Account', 'beautia' ), 'beautia_account', false ),
			'opening-hours' => array( __( 'Opening Hours', 'beautia' ), 'beautia_hours', false ),
			'tracking'      => array( __( 'Appointment Tracking', 'beautia' ), 'beautia_track', false ),
			'compare'       => array( __( 'Image Comparison', 'beautia' ), 'beautia_compare', false ),
			'marquee'       => array( __( 'Luxury Marquee', 'beautia' ), 'beautia_marquee', false ),
			'social'        => array( __( 'Social Links', 'beautia' ), 'beautia_social', false ),
			'contact-card'  => array( __( 'Contact Card', 'beautia' ), 'beautia_contact_card', false ),
			'map'           => array( __( 'Location Map', 'beautia' ), 'beautia_map', false ),
			'whatsapp'      => array( __( 'WhatsApp Button', 'beautia' ), 'beautia_whatsapp', false ),
			'opening-status'=> array( __( 'Live Opening Status', 'beautia' ), 'beautia_opening_status', false ),
			'service-search'=> array( __( 'Service Search', 'beautia' ), 'beautia_service_search', false ),
			'quick-book'    => array( __( 'Quick Booking Banner', 'beautia' ), 'beautia_quick_book', false ),
			'availability'  => array( __( 'Live Availability', 'beautia' ), 'beautia_availability', true ),
			'icon-box'      => array( __( 'Luxury Icon Box', 'beautia' ), 'beautia_icon_box', false ),
			'notice'        => array( __( 'Notice', 'beautia' ), 'beautia_notice', false ),
			'navigation'    => array( __( 'Dynamic Navigation', 'beautia' ), 'beautia_navigation', false ),
			'single-service'=> array( __( 'Single Service', 'beautia' ), 'beautia_single_service', false ),
			'single-staff'  => array( __( 'Single Specialist', 'beautia' ), 'beautia_single_specialist', false ),
			'loyalty'       => array( __( 'Customer Loyalty', 'beautia' ), 'beautia_loyalty', false ),
		);

		foreach ( $widgets as $slug => $config ) {
			$widgets_manager->register(
				new class( $slug, $config ) extends \Elementor\Widget_Base {
					private $beautia_slug;
					private $beautia_config;

					public function __construct( $slug, $config, $data = array(), $args = null ) {
						$this->beautia_slug   = $slug;
						$this->beautia_config = $config;
						parent::__construct( $data, $args );
					}

					public function get_name() { return 'beautia-' . $this->beautia_slug; }
					public function get_title() { return $this->beautia_config[0]; }
					public function get_icon() { return 'eicon-favorite'; }
					public function get_categories() { return array( 'beautia' ); }
					public function get_keywords() { return array( 'beautia', 'beauty', 'rtl', 'booking' ); }

					protected function register_controls() {
						if ( empty( $this->beautia_config[2] ) ) {
							return;
						}
						$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'beautia' ) ) );
						$this->add_control(
							'count',
							array(
								'label'   => __( 'Number of items', 'beautia' ),
								'type'    => \Elementor\Controls_Manager::NUMBER,
								'min'     => 1,
								'max'     => 24,
								'default' => 'blog' === $this->beautia_slug ? 3 : 6,
							)
						);
						$this->end_controls_section();
					}

					protected function render() {
						$settings = $this->get_settings_for_display();
						$attrs    = '';
						if ( ! empty( $this->beautia_config[2] ) ) {
							$attrs = ' count="' . max( 1, min( 24, absint( $settings['count'] ) ) ) . '"';
						}
						echo do_shortcode( '[' . esc_attr( $this->beautia_config[1] ) . $attrs . ']' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
				}
			);
		}
	}
}
