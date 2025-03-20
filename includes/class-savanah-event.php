<?php

/**
 * Main plugin class
 */
class Savanah_Event {


	/**
	 * Initialize the plugin
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
	}

	/**
	 * Register custom post types
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
	 * Register taxonomies
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
	 * Enqueue frontend scripts and styles
	 */
	public function enqueue_scripts() {
		wp_enqueue_style(
			'savanah-event',
			SAVANAH_EVENT_PLUGIN_URL . 'assets/css/savanah-event.css',
			array(),
			SAVANAH_EVENT_VERSION
		);
	}

	/**
	 * Enqueue admin scripts and styles
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_style(
			'savanah-event-admin',
			SAVANAH_EVENT_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			SAVANAH_EVENT_VERSION
		);
	}

	// Disable Gutenberg for events
	public function disable_gutenberg( $use_block_editor, $post_type ) {
		if ( $post_type === 'event' ) {
			return false;
		}
		return $use_block_editor;
	}

	// Add meta boxes
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

	// Render meta box content
	public function render_event_meta_box( $post ) {
		wp_nonce_field( 'event_meta_box', 'event_meta_box_nonce' );

		$event_date = get_post_meta( $post->ID, '_event_date', true );
		$event_time = get_post_meta( $post->ID, '_event_time', true );
		$event_type = get_post_meta( $post->ID, '_event_type', true );
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
		</div>
		<?php
	}

	// Save meta box data
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

		// Save event date
		if ( isset( $_POST['event_date'] ) ) {
			update_post_meta( $post_id, '_event_date', sanitize_text_field( $_POST['event_date'] ) );
		}

		// Save event time
		if ( isset( $_POST['event_time'] ) ) {
			update_post_meta( $post_id, '_event_time', sanitize_text_field( $_POST['event_time'] ) );
		}

		// Save event type
		if ( isset( $_POST['event_type'] ) ) {
			update_post_meta( $post_id, '_event_type', sanitize_text_field( $_POST['event_type'] ) );
		}
	}

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

	public function manage_event_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'event_date':
				$date = get_post_meta( $post_id, '_event_date', true );
				echo $date ? date_i18n( get_option( 'date_format' ), strtotime( $date ) ) : '—';
				break;
			case 'event_time':
				$time = get_post_meta( $post_id, '_event_time', true );
				echo $time ? date_i18n( get_option( 'time_format' ), strtotime( $time ) ) : '—';
				break;
			case 'event_type':
				$type = get_post_meta( $post_id, '_event_type', true );
				echo $type === 'online' ? __( 'Online', 'savanah-event' ) : __( 'In Person', 'savanah-event' );
				break;
		}
	}

	public function make_event_columns_sortable( $columns ) {
		$columns['event_date'] = 'event_date';
		$columns['event_type'] = 'event_type';
		return $columns;
	}

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
}
