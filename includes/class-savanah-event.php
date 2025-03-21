<?php

/**
 * Main class for handling event functionality.
 *
 * This class manages event post types, taxonomies, meta boxes,
 * shortcodes and other core event features.
 *
 * @since 1.0.0
 */
class Savanah_Event {



	/**
	 * Initialize the plugin by registering hooks and filters.
	 *
	 * Sets up all the WordPress hooks and filters needed for the plugin functionality,
	 * including post types, taxonomies, scripts, meta boxes, admin columns and shortcodes.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_event_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_event_meta' ) );
		add_filter( 'use_block_editor_for_post_type', array( $this, 'disable_gutenberg' ), 10, 2 );
		add_filter( 'manage_event_posts_columns', array( $this, 'add_event_columns' ) );
		add_action( 'manage_event_posts_custom_column', array( $this, 'manage_event_columns' ), 10, 2 );
		add_filter( 'manage_edit-event_sortable_columns', array( $this, 'make_event_columns_sortable' ) );
		add_action( 'pre_get_posts', array( $this, 'event_custom_orderby' ) );
		add_action( 'wp_ajax_load_more_events', array( $this, 'load_more_events' ) );
		add_action( 'wp_ajax_nopriv_load_more_events', array( $this, 'load_more_events' ) );
		add_shortcode( 'savanah_events', array( $this, 'render_events_shortcode' ) );
	}

	
	/**
	 * Register custom post types for events.
	 *
	 * Creates and registers the 'event' custom post type with appropriate
	 * labels and settings for managing events in WordPress.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_post_types() {
		register_post_type(
			'event',
			array(
				'labels'       => array(
					'name'          => __( 'Events', 'savanah-event' ),
					'singular_name' => __( 'Event', 'savanah-event' ),
				),
				'public'       => true,
				'has_archive'  => true,
				'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
				'menu_icon'    => 'dashicons-calendar-alt',
				'show_in_rest' => false, // Disable Gutenberg
			)
		);
	}

	
	/**
	 * Register custom taxonomies for events.
	 * 
	 * Creates and registers the 'event_category' taxonomy to organize
	 * and categorize events with hierarchical categories.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_taxonomies() {
		register_taxonomy(
			'event_category',
			'event',
			array(
				'labels'       => array(
					'name'          => __( 'Event Categories', 'savanah-event' ),
					'singular_name' => __( 'Event Category', 'savanah-event' ),
				),
				'hierarchical' => true,
				'show_in_rest' => true,
			)
		);
	}

	
	/**
	 * Enqueue frontend scripts and styles.
	 *
	 * Registers and enqueues CSS and JavaScript files needed for the frontend
	 * event display, including infinite scroll functionality and AJAX support.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_style(
			'savanah-event',
			SAVANAH_EVENT_PLUGIN_URL . 'assets/css/savanah-event.css',
			array(),
			SAVANAH_EVENT_VERSION
		);

		wp_enqueue_script(
			'savanah-event-infinite-scroll',
			SAVANAH_EVENT_PLUGIN_URL . 'assets/js/infinite-scroll.js',
			array( 'jquery' ),
			SAVANAH_EVENT_VERSION,
			true
		);

		wp_localize_script(
			'savanah-event-infinite-scroll',
			'savanah_event',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'savanah_event_nonce' ),
			)
		);
	}

	
	
	/**
	 * Enqueue admin scripts and styles.
	 *
	 * Registers and enqueues CSS files needed for the WordPress admin
	 * interface when managing events.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_style(
			'savanah-event-admin',
			SAVANAH_EVENT_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			SAVANAH_EVENT_VERSION
		);
	}

	
/**
 * Disable Gutenberg editor for event post type.
 *
 * Filters whether to use the block editor for a given post type,
 * returning false specifically for the 'event' post type to force
 * the classic editor.
 *
 * @since 1.0.0
 * @param bool   $use_block_editor Whether to use the block editor for this post type
 * @param string $post_type        The post type being checked
 * @return bool Whether to use the block editor
 */
public function disable_gutenberg( $use_block_editor, $post_type ) {
		if ( $post_type === 'event' ) {
			return false;
		}
		return $use_block_editor;
	}

	// Add meta boxes
	/**
	 * Add meta boxes for the event post type.
	 *
	 * Registers meta boxes to display event details like date, time,
	 * venue and other event-specific information.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_event_meta_boxes() {
		add_meta_box(
			'event_details',
			__( 'Event Details', 'savanah-event' ),
			array( $this, 'render_event_meta_box' ),
			'event',
			'normal',
			'high'
		);
	}

	/**
	 * Render the event meta box content.
	 *
	 * Displays form fields for entering event details like date, time,
	 * venue, price and other event-specific information in the admin interface.
	 *
	 * @since 1.0.0
	 * @param WP_Post $post The post object
	 * @return void
	 */
	public function render_event_meta_box( $post ) {
		wp_nonce_field( 'event_meta_box', 'event_meta_box_nonce' );

		$event_date     = get_post_meta( $post->ID, '_event_date', true );
		$event_time     = get_post_meta( $post->ID, '_event_time', true );
		$event_type     = get_post_meta( $post->ID, '_event_type', true );
		$event_price    = get_post_meta( $post->ID, '_event_price', true );
		$event_status   = get_post_meta( $post->ID, '_event_status', true );
		$event_venue    = get_post_meta( $post->ID, '_event_venue', true );
		$event_location = get_post_meta( $post->ID, '_event_location', true );
		?>
		<div class="event-meta-box">
			<p>
				<label for="event_date"><?php _e( 'Event Date:', 'savanah-event' ); ?></label>
				<input type="date" id="event_date" name="event_date"
					value="<?php echo esc_attr( $event_date ); ?>" class="widefat">
			</p>
			<p>
				<label for="event_time"><?php _e( 'Event Time:', 'savanah-event' ); ?></label>
				<input type="time" id="event_time" name="event_time"
					value="<?php echo esc_attr( $event_time ); ?>" class="widefat">
			</p>
			<p>
				<label for="event_type"><?php _e( 'Event Type:', 'savanah-event' ); ?></label>
				<select id="event_type" name="event_type" class="widefat">
					<option value="in-person" <?php selected( $event_type, 'in-person' ); ?>>
						<?php _e( 'In Person', 'savanah-event' ); ?>
					</option>
					<option value="online" <?php selected( $event_type, 'online' ); ?>>
						<?php _e( 'Online', 'savanah-event' ); ?>
					</option>
				</select>
			</p>
			<p>
				<label for="event_status"><?php _e( 'Event Status:', 'savanah-event' ); ?></label>
				<input type="text" id="event_status" name="event_status" 
					value="<?php echo esc_attr( $event_status ); ?>" class="widefat"
					placeholder="e.g., Selling Fast, Sold Out">
			</p>
			<p>
				<label for="event_price"><?php _e( 'Price Range:', 'savanah-event' ); ?></label>
				<input type="text" id="event_price" name="event_price" 
					value="<?php echo esc_attr( $event_price ); ?>" class="widefat"
					placeholder="e.g., Free - $30, $25 - $80">
			</p>
			<p>
				<label for="event_venue"><?php _e( 'Venue:', 'savanah-event' ); ?></label>
				<input type="text" id="event_venue" name="event_venue" 
					value="<?php echo esc_attr( $event_venue ); ?>" class="widefat">
			</p>
			<p>
				<label for="event_location"><?php _e( 'Location:', 'savanah-event' ); ?></label>
				<input type="text" id="event_location" name="event_location" 
					value="<?php echo esc_attr( $event_location ); ?>" class="widefat"
					placeholder="e.g., San Francisco, CA">
			</p>
		</div>
		<?php
	}

	
	/**
	 * Save event meta data when a post is saved.
	 *
	 * Handles validation and saving of event-specific meta data like date, time,
	 * venue, price and other details when an event post is saved or updated.
	 *
	 * @since 1.0.0
	 * @param int $post_id The ID of the post being saved
	 * @return void
	 */
	public function save_event_meta( $post_id ) {
		if ( ! isset( $_POST['event_meta_box_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['event_meta_box_nonce'], 'event_meta_box' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'event_date',
			'event_time',
			'event_type',
			'event_status',
			'event_price',
			'event_venue',
			'event_location',
		);

		foreach ( $fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				update_post_meta(
					$post_id,
					'_' . $field,
					sanitize_text_field( $_POST[ $field ] )
				);
			}
		}
	}
	
	/**
	 * Add custom columns to the events list table.
	 *
	 * Adds event-specific columns like date, time and type to the WordPress admin
	 * events list table for better event management.
	 *
	 * @since 1.0.0
	 * @param array $columns Array of column names
	 * @return array Modified array of column names
	 */
	public function add_event_columns( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $value ) {
			if ( $key === 'date' ) {
				$new_columns['event_date'] = __( 'Event Date', 'savanah-event' );
				$new_columns['event_time'] = __( 'Event Time', 'savanah-event' );
				$new_columns['event_type'] = __( 'Event Type', 'savanah-event' );
			}
			$new_columns[ $key ] = $value;
		}
		return $new_columns;
	}

	/**
	 * Manage content for custom event columns.
	 *
	 * Handles the display of event-specific data like date, time and type
	 * in the custom columns of the WordPress admin events list table.
	 *
	 * @since 1.0.0
	 * @param string $column  The name of the column to display
	 * @param int    $post_id The ID of the current post
	 * @return void
	 */
	public function manage_event_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'event_date':
				$date = get_post_meta( $post_id, '_event_date', true );
				echo esc_html( $date ? date_i18n( get_option( 'date_format' ), strtotime( $date ) ) : '—' );
				break;
			case 'event_time':
				$time = get_post_meta( $post_id, '_event_time', true );
				echo esc_html( $time ? date_i18n( get_option( 'time_format' ), strtotime( $time ) ) : '—' );
				break;
			case 'event_type':
				$type = get_post_meta( $post_id, '_event_type', true );
				echo $type === 'online' ? __( 'Online', 'savanah-event' ) : __( 'In Person', 'savanah-event' );
				break;
		}
	}

	/**
	 * Make event columns sortable in admin list table.
	 *
	 * Adds sorting capability to custom columns in the WordPress admin events
	 * list table, specifically for event date and event type.
	 *
	 * @since 1.0.0
	 * @param array $columns Array of sortable columns
	 * @return array Modified array of sortable columns
	 */
	public function make_event_columns_sortable( $columns ) {
		$columns['event_date'] = 'event_date';
		$columns['event_type'] = 'event_type';
		return $columns;
	}

	/**
	 * Handle custom ordering for event columns.
	 *
	 * Modifies the main query when ordering by custom event columns like
	 * event date and event type in the WordPress admin events list table.
	 *
	 * @since 1.0.0
	 * @param WP_Query $query The WordPress query object
	 * @return void
	 */
	public function event_custom_orderby( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || $query->get( 'post_type' ) !== 'event' ) {
			return;
		}

		$orderby = $query->get( 'orderby' );

		if ( 'event_date' === $orderby ) {
			$query->set( 'meta_key', '_event_date' );
			$query->set( 'orderby', 'meta_value' );
		}

		if ( 'event_type' === $orderby ) {
			$query->set( 'meta_key', '_event_type' );
			$query->set( 'orderby', 'meta_value' );
		}
	}
	/**
	 * Handle AJAX request for loading more events.
	 * 
	 * Processes AJAX requests to load additional event posts for infinite scroll
	 * pagination. Verifies nonce, queries events after current date, and returns
	 * event template markup.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function load_more_events() {
		check_ajax_referer( 'savanah_event_nonce', 'nonce' );

		$page           = absint( $_POST['page'] );
		$posts_per_page = absint( $_POST['posts_per_page'] );

		$args = array(
			'post_type'      => 'event',
			'posts_per_page' => $posts_per_page,
			'paged'          => $page,
			'meta_key'       => '_event_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'     => '_event_date',
					'value'   => date( 'Y-m-d' ),
					'compare' => '>=',
					'type'    => 'DATE',
				),
			),
		);

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				include SAVANAH_EVENT_PLUGIN_DIR . 'templates/event-item.php';
			}
		}

		wp_die();
	}
	// Initialize Elementor integration
	//


	/**
	 * Render the events shortcode output.
	 *
	 * Processes shortcode attributes and displays events in a grid layout with
	 * optional pagination. Supports ordering by event date, post date or title
	 * and different pagination types (none, numbers, infinite scroll).
	 *
	 * @since 1.0.0
	 * @param array $atts Shortcode attributes
	 * @return string HTML output of the events grid
	 */
	public function render_events_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'posts_per_page' => 3,
				'order_by'       => 'event_date',
				'order'          => 'ASC',
				'pagination'     => 'none',
			),
			$atts,
			'savanah_events'
		);

		$paged = absint( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

		$args = array(
			'post_type'      => 'event',
			'posts_per_page' => absint( $atts['posts_per_page'] ),
			'order'          => sanitize_text_field( $atts['order'] ),
			'paged'          => $paged,
		);

		// Set orderby based on selection
		switch ( $atts['order_by'] ) {
			case 'event_date':
				$args['meta_key'] = '_event_date';
				$args['orderby']  = 'meta_value';
				break;
			case 'post_date':
				$args['orderby'] = 'date';
				break;
			case 'title':
				$args['orderby'] = 'title';
				break;
		}

		$query = new WP_Query( $args );
		ob_start();

		if ( $query->have_posts() ) :
			?>
		<div class="upcoming-events-widget" 
			data-pagination="<?php echo esc_attr( $atts['pagination'] ); ?>"
			data-max-pages="<?php echo esc_attr( $query->max_num_pages ); ?>"
			data-posts-per-page="<?php echo esc_attr( $atts['posts_per_page'] ); ?>">
			<div class="events-grid">
					<?php
					while ( $query->have_posts() ) :
						$query->the_post();
						include SAVANAH_EVENT_PLUGIN_DIR . 'templates/event-item.php';
					endwhile;
					?>
			</div>

				<?php if ( $atts['pagination'] === 'numbers' ) : ?>
				<div class="events-pagination">
					<?php
					echo paginate_links(
						array(
							'total'     => $query->max_num_pages,
							'current'   => $paged,
							'prev_text' => __( '&laquo; Previous', 'savanah-event' ),
							'next_text' => __( 'Next &raquo;', 'savanah-event' ),
						)
					);
					?>
				</div>
			<?php endif; ?>

				<?php if ( $atts['pagination'] === 'infinite' ) : ?>
				<div class="events-loader" style="display: none;">
					<div class="loader"></div>
				</div>
				<button class="load-more-btn">Load More Events</button>
			<?php endif; ?>
		</div>
			<?php
	endif;
		wp_reset_postdata();

		return ob_get_clean();
	}
}