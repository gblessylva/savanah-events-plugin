<?php
/**
 * Savanah Event Elementor Integration
 *
 * This file contains the main class for integrating Savanah Event with Elementor.
 *
 * @package    Savanah_Event
 * @subpackage Elementor
 * @since      1.0.0
 */
class Savanah_Event_Elementor {
	/**
	 * Constructor for the Savanah_Event_Elementor class.
	 *
	 * Initializes the class and sets up WordPress hooks for Elementor integration.
	 */
	public function __construct() {
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'add_elementor_widget_categories' ) );
	}

	/**
	 * Register Elementor widgets.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 * @return void
	 */
	public function register_widgets( $widgets_manager ) {
		require_once __DIR__ . '/class-upcoming-events-widget.php';
		$widgets_manager->register( new Upcoming_Events_Widget() );
	}

	/**
	 * Add custom widget categories for Savanah Event to Elementor.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
	 * @return void
	 */
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
