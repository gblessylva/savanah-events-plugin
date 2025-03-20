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

// Include plugin files
require_once SAVANAH_EVENT_PLUGIN_DIR . 'includes/class-savanah-event.php';

// Initialize the plugin
function savanah_event_init() {
    $plugin = new Savanah_Event();
    $plugin->init();
}
add_action( 'plugins_loaded', 'savanah_event_init' );