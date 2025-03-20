<?php
class Savanah_Event_Elementor {
	public function __construct() {
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'add_elementor_widget_categories' ) );
	}

	public function register_widgets( $widgets_manager ) {
		require_once __DIR__ . '/class-upcoming-events-widget.php';
		$widgets_manager->register( new Upcoming_Events_Widget() );
	}

	public function add_elementor_widget_categories( $elements_manager ) {
		$elements_manager->add_category(
			'savanah-event',
			array(
				'title' => __( 'Savanah Event', 'savanah-event' ),
				'icon'  => 'fa fa-calendar',
			)
		);
	}
}
