<?php
/**
 * Plugin Name: Savanah Event
 * Description: A comprehensive event management plugin for WordPress
 * Version: 1.0.0
 * Author: Gbless Sylva
 * Author URI: https://gblessylva.com
 * Text Domain: savanah-event
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SAVANAH_EVENT_VERSION', '1.0.0' );
define( 'SAVANAH_EVENT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SAVANAH_EVENT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include plugin files.
require_once SAVANAH_EVENT_PLUGIN_DIR . 'includes/class-savanah-event.php';


/**
 * Initialize the Savanah Event plugin
 *
 * @return void
 */
function savanah_event_init() {
	$plugin = new Savanah_Event();
	$plugin->init();
}
add_action( 'plugins_loaded', 'savanah_event_init' );

// Load Elementor integration if Elementor is active.
if ( did_action( 'elementor/loaded' ) ) {
	require_once SAVANAH_EVENT_PLUGIN_DIR . 'includes/elementor/elementor.php';
	new Savanah_Event_Elementor();
}

// Creates 20 dummy events when the plugin is activated
add_action(
	'init',
	function () {
		// Only run once
		if ( get_option( 'dummy_events_created' ) ) {
			return;
		}

		$event_titles = array(
			'The Future of AI: Opportunities and Ethics',
			'Empowering Women in Tech: A Global Meetup',
			'The Evolution of EdTech: Tools for Modern Learning',
			'Financial Wellness Bootcamp for Millennials',
			'Code & Connect: Devs Meet Hackers',
			'Women in Tech: Breaking Barriers and Building Futures',
			'The Power of Her Voice: Storytelling for Change',
			'Leading with Confidence: Women in Leadership Summit',
			'Women in Finance: Redefining Wealth & Power',
			'Her Health, Her Power: Wellness & Success',
			'EdTech Unplugged: Innovation in the Classroom',
			'Reimagining Higher Education in a Digital World',
			'Early Childhood Development: Building Strong Foundations',
			'Learning Beyond Borders: Global Education Exchange',
			'Lifelong Learning for the 21st Century',
			'Future of Fintech: Disrupting the Financial Landscape',
			'Financial Freedom Masterclass: Build Wealth with Intention',
			'Crypto & Blockchain Explained',
			'Investing for Beginners: Your Money, Your Future',
			'The Psychology of Money: Mindsets for Wealth',
		);

		$event_types     = array( 'online', 'in-person' );
		$event_prices    = array( 'Free', '15.99', '49.00', '99.99', '200.00' );
		$event_venues    = array( 'Zoom', 'Google Meet', 'Event Hall A', 'Conference Center B', 'Virtual Link Sent After Registration' );
		$event_locations = array( 'New York, NY', 'Los Angeles, CA', 'San Francisco, CA', 'Austin, TX', 'Remote', 'Berlin, Germany' );

		// Fetch all image IDs from the media library
		$images = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'post_status'    => 'inherit',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		foreach ( $event_titles as $title ) {
			$post_id = wp_insert_post(
				array(
					'post_title'   => $title,
					'post_type'    => 'event',
					'post_status'  => 'publish',
					'post_content' => wp_trim_words( $title . ' - Lorem ipsum dolor sit amet, consectetur adipiscing elit. This event offers valuable insights and networking opportunities for attendees looking to grow their knowledge and connect with like-minded individuals.', 50, '...' ),
				)
			);

			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, '_event_type', $event_types[ array_rand( $event_types ) ] );
				update_post_meta( $post_id, '_event_date', date( 'Y-m-d', strtotime( '+' . rand( 1, 60 ) . ' days' ) ) );
				update_post_meta( $post_id, '_event_time', date( 'H:i', strtotime( rand( 8, 20 ) . ':00' ) ) );
				update_post_meta( $post_id, '_event_price', $event_prices[ array_rand( $event_prices ) ] );
				update_post_meta( $post_id, '_event_venue', $event_venues[ array_rand( $event_venues ) ] );
				update_post_meta( $post_id, '_event_location', $event_locations[ array_rand( $event_locations ) ] );

				// Set random featured image
				if ( ! empty( $images ) ) {
					$random_image_id = $images[ array_rand( $images ) ];
					set_post_thumbnail( $post_id, $random_image_id );
				}
			}
		}

		update_option( 'dummy_events_created', true );
	}
);
