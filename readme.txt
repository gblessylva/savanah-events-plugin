=== Savanah Event ===
Contributors: sylvanus
Tags: events, calendar, elementor, booking
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A powerful event management plugin with Elementor integration and shortcode support.

== Description ==

Savanah Event is a versatile WordPress plugin that helps you manage and display events on your website. It features both Elementor widget and shortcode support, making it flexible for any WordPress setup.

Features:
* Custom event post type with meta fields
* Event details including date, time, venue, and price
* Elementor widget integration
* Shortcode support
* Multiple display options
* Load more/pagination support
* Sorting capabilities
* Admin columns for easy management

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/savanah-event`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Events menu to configure the plugin

== Usage ==

= Elementor Widget =
1. Edit a page with Elementor
2. Search for "Upcoming Events" widget
3. Drag and drop the widget to your page
4. Configure the following options:
   - Number of events to display
   - Sort order (Event Date, Created Date, Title)
   - Sort direction (Ascending/Descending)
   - Pagination type (None, Numbers, Load More)

= Shortcode =
Use the following shortcode to display events:

[savanah_events posts_per_page="6" order_by="event_date" order="ASC" pagination="infinite"]

Parameters:
* posts_per_page: Number of events to show (default: 3)
* order_by: event_date, post_date, or title (default: event_date)
* order: ASC or DESC (default: ASC)
* pagination: none, numbers, or infinite (default: none)

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release