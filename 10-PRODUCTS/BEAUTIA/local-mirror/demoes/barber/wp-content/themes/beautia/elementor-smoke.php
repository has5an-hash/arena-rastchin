<?php
namespace Elementor {
	class Controls_Manager { const NUMBER = 'number'; }
	class Widget_Base {
		public function __construct( $data = array(), $args = null ) {}
		protected function start_controls_section( $id, $args = array() ) {}
		protected function add_control( $id, $args = array() ) {}
		protected function end_controls_section() {}
		protected function get_settings_for_display() { return array( 'count' => 6 ); }
	}
}

namespace {
require dirname( __DIR__, 3 ) . '/wp-load.php';
	do_action( 'elementor/loaded' );
	Beautia_Elementor::instance()->register_hooks();
	$manager = new class {
		public $widgets = array();
		public function register( $widget ) { $this->widgets[] = $widget; }
	};
	do_action( 'elementor/widgets/register', $manager );
	$names = array_map( static function( $widget ) { return $widget->get_name(); }, $manager->widgets );
	if ( 35 !== count( $names ) || 35 !== count( array_unique( $names ) ) ) {
		fwrite( STDERR, 'Elementor widget registration failed.' . PHP_EOL );
		exit( 1 );
	}
	echo 'Elementor smoke PASS: ' . implode( ', ', $names ) . PHP_EOL;
}
