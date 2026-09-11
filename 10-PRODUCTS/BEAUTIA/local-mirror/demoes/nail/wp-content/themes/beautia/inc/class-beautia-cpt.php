<?php
/**
 * Custom post types & taxonomies.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Beautia_CPT
 */
class Beautia_CPT {

	/** @var Beautia_CPT|null */
	private static $instance = null;

	/** Singleton. */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** Constructor. */
	private function __construct() {
		add_action( 'init', array( $this, 'register' ) );
		add_filter( 'manage_beautia_service_posts_columns', array( $this, 'service_columns' ) );
		add_action( 'manage_beautia_service_posts_custom_column', array( $this, 'service_column_content' ), 10, 2 );
	}

	/** Register post types and taxonomies. */
	public function register() {
		$types = array(
			'beautia_service'   => array(
				'singular' => __( 'Service', 'beautia' ),
				'plural'   => __( 'Services', 'beautia' ),
				'slug'     => beautia_get_option( 'slug_service', 'services' ),
				'icon'     => 'dashicons-buddicons-topics',
				'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
				'tax'      => array(
					'beautia_service_cat' => array( __( 'Service Category', 'beautia' ), __( 'Service Categories', 'beautia' ), 'service-category', true ),
				),
			),
			'beautia_portfolio' => array(
				'singular' => __( 'Portfolio Item', 'beautia' ),
				'plural'   => __( 'Portfolio', 'beautia' ),
				'slug'     => beautia_get_option( 'slug_portfolio', 'portfolio' ),
				'icon'     => 'dashicons-format-gallery',
				'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
				'tax'      => array(
					'beautia_portfolio_cat' => array( __( 'Portfolio Category', 'beautia' ), __( 'Portfolio Categories', 'beautia' ), 'portfolio-category', true ),
				),
			),
			'beautia_staff'     => array(
				'singular' => __( 'Team Member', 'beautia' ),
				'plural'   => __( 'Team', 'beautia' ),
				'slug'     => beautia_get_option( 'slug_staff', 'team' ),
				'icon'     => 'dashicons-groups',
				'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
				'tax'      => array(
					'beautia_staff_role' => array( __( 'Specialty', 'beautia' ), __( 'Specialties', 'beautia' ), 'specialty', true ),
				),
			),
			'beautia_testimonial' => array(
				'singular' => __( 'Testimonial', 'beautia' ),
				'plural'   => __( 'Testimonials', 'beautia' ),
				'slug'     => 'testimonial',
				'icon'     => 'dashicons-format-quote',
				'supports' => array( 'title', 'editor', 'thumbnail' ),
				'public'   => false,
				'tax'      => array(),
			),
			'beautia_gallery'   => array(
				'singular' => __( 'Before / After', 'beautia' ),
				'plural'   => __( 'Before / After', 'beautia' ),
				'slug'     => 'before-after',
				'icon'     => 'dashicons-images-alt2',
				'supports' => array( 'title', 'thumbnail', 'editor' ),
				'tax'      => array(),
			),
		);

		foreach ( $types as $key => $t ) {
			$public = isset( $t['public'] ) ? $t['public'] : true;
			register_post_type(
				$key,
				array(
					'labels'       => $this->labels( $t['singular'], $t['plural'] ),
					'public'       => $public,
					'show_ui'      => true,
					'show_in_rest' => true,
					'has_archive'  => $public,
					'menu_icon'    => $t['icon'],
					'rewrite'      => array( 'slug' => $t['slug'], 'with_front' => false ),
					'supports'     => $t['supports'],
					'menu_position'=> 24,
				)
			);

			foreach ( $t['tax'] as $tax_key => $tax ) {
				register_taxonomy(
					$tax_key,
					$key,
					array(
						'labels'            => $this->labels( $tax[0], $tax[1] ),
						'hierarchical'      => $tax[3],
						'show_in_rest'      => true,
						'show_admin_column' => true,
						'rewrite'           => array( 'slug' => $tax[2], 'with_front' => false ),
					)
				);
			}
		}
	}

	/** Build label arrays. */
	private function labels( $singular, $plural ) {
		return array(
			'name'               => $plural,
			'singular_name'      => $singular,
			'menu_name'          => $plural,
			/* translators: %s: item */
			'add_new_item'       => sprintf( __( 'Add New %s', 'beautia' ), $singular ),
			'edit_item'          => sprintf( __( 'Edit %s', 'beautia' ), $singular ),
			'new_item'           => sprintf( __( 'New %s', 'beautia' ), $singular ),
			'view_item'          => sprintf( __( 'View %s', 'beautia' ), $singular ),
			'search_items'       => sprintf( __( 'Search %s', 'beautia' ), $plural ),
			'not_found'          => sprintf( __( 'No %s found', 'beautia' ), $plural ),
			'all_items'          => $plural,
		);
	}

	/** Extra admin columns for services. */
	public function service_columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['beautia_price']    = __( 'Price', 'beautia' );
				$new['beautia_duration'] = __( 'Duration', 'beautia' );
			}
		}
		return $new;
	}

	/** Render extra columns. */
	public function service_column_content( $column, $post_id ) {
		if ( 'beautia_price' === $column ) {
			echo esc_html( beautia_price( get_post_meta( $post_id, '_beautia_price', true ) ) );
		}
		if ( 'beautia_duration' === $column ) {
			echo esc_html( beautia_duration( get_post_meta( $post_id, '_beautia_duration', true ) ) );
		}
	}
}
