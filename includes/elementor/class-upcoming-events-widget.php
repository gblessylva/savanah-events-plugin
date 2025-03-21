<?php
/**
 * Upcoming Events Widget Class
 *
 * Elementor widget that displays upcoming events in a grid layout with filtering
 * and sorting options. Supports features like event date, time, type display
 * and pagination.
 *
 * @package SavanahEvent
 * @since 1.0.0
 */
class Upcoming_Events_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve the widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'upcoming_events';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve the widget title displayed in Elementor editor.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Upcoming Events', 'savanah-event' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve the widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-calendar';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the Upcoming Events widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'general' );
	}

	/**
	 * Register Elementor controls for this widget.
	 *
	 * Adds various controls to customize the widget appearance and behavior
	 * including posts per page, sorting options, and styling controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return void
	 */
	protected function register_controls() {
		// Content Section.
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Content', 'savanah-event' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'posts_per_page',
			array(
				'label'   => __( 'Number of Events', 'savanah-event' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 20,
				'default' => 3,
			)
		);

		$this->add_control(
			'order_by',
			array(
				'label'   => __( 'Sort By', 'savanah-event' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'event_date',
				'options' => array(
					'event_date' => __( 'Event Date', 'savanah-event' ),
					'post_date'  => __( 'Created Date', 'savanah-event' ),
					'title'      => __( 'Title', 'savanah-event' ),
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'   => __( 'Order', 'savanah-event' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'ASC',
				'options' => array(
					'ASC'  => __( 'Ascending', 'savanah-event' ),
					'DESC' => __( 'Descending', 'savanah-event' ),
				),
			)
		);

		$this->add_control(
			'pagination_type',
			array(
				'label' => __( 'Pagination Type', 'savanah-event' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'none',
				'options' => array(
					'none' => __( 'None', 'savanah-event' ),
					'numbers' => __( 'Numbers', 'savanah-event' ),
					'infinite' => __( 'Load More', 'savanah-event' ),
				),
			)
		);

		$this->end_controls_section();

		// Style Section.
		$this->start_controls_section(
			'style_section',
			array(
				'label' => __( 'Style', 'savanah-event' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Title Color', 'savanah-event' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .event-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .event-title',
			)
		);

		$this->add_control(
			'date_color',
			array(
				'label'     => __( 'Date Color', 'savanah-event' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .event-date' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render the upcoming events widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 * Retrieves events based on widget settings and displays them in a grid layout
	 * with event details like date, time, type and excerpt.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$paged    = absint( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

		$args = array(
			'post_type'      => 'event',
			'posts_per_page' => absint( $settings['posts_per_page'] ),
			'order'          => sanitize_text_field( $settings['order'] ),
			'paged'          => $paged,
		);

		// Set orderby based on selection
		switch ( $settings['order_by'] ) {
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

		if ( $query->have_posts() ) : ?>
			<div class="upcoming-events-widget" 
				data-pagination="<?php echo esc_attr( $settings['pagination_type'] ); ?>"
				data-max-pages="<?php echo esc_attr( $query->max_num_pages ); ?>"
				data-posts-per-page="<?php echo esc_attr( $settings['posts_per_page'] ); ?>">
				<div class="events-grid">
					<?php
					while ( $query->have_posts() ) :
						$query->the_post();
						$permalink      = get_permalink();
						$event_date     = sanitize_text_field( get_post_meta( get_the_ID(), '_event_date', true ) );
						$event_time     = sanitize_text_field( get_post_meta( get_the_ID(), '_event_time', true ) );
						$event_type     = sanitize_text_field( get_post_meta( get_the_ID(), '_event_type', true ) );
						$event_venue    = sanitize_text_field( get_post_meta( get_the_ID(), '_event_venue', true ) );
						$event_location = sanitize_text_field( get_post_meta( get_the_ID(), '_event_location', true ) );
						// get event _event_price if prize is epmty or 0 the event is free.
						$event_price = get_post_meta( get_the_ID(), '_event_price', true );
						// if event price is empty or 0 the event is free.
						if ( empty( $event_price ) || $event_price === '0' ) {
							$event_price = __( 'Free', 'savanah-event' );
						} else {
							$event_price = 'N' . $event_price;
						}

						?>
						<a href="<?php echo esc_url( $permalink ); ?>" class="event-item-link">
							<div class="event-item">
								<?php if ( has_post_thumbnail() ) : ?>
									<div class="event-thumbnail">
										<?php the_post_thumbnail( 'medium' ); ?>
									</div>
								<?php endif; ?>
								<div class="event-content">
									<h3 class="event-title"><?php the_title(); ?></h3>
									<div class="event-meta">
										<span class="event-date">
											<span>Date: </span>
											<i class="eicon-calendar"></i>
											<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $event_date ) ) ); ?>
										</span>
										<span class="event-time">
										<span>Time: </span>
											<i class="eicon-clock-o"></i>
											<?php echo esc_html( date_i18n( get_option( 'time_format' ), strtotime( $event_time ) ) ); ?>
										</span>
										<span class="event-type">
										<span>Type: </span>
											<i class="eicon-location"></i>
											<?php echo esc_html( $event_type === 'online' ? __( 'Online', 'savanah-event' ) : __( 'In Person', 'savanah-event' ) ); ?>
										</span>
									</div>
									<div class="event-excerpt">
										<?php the_excerpt(); ?>
									</div>
									<div class="event-meta">
										<span class="event-venue">
											<span>Venue: </span>
											<i class="eicon-location"></i>
											<?php echo esc_html( $event_venue ); ?>
										</span>
										<?php
										if ( ! empty( $event_location ) ) {
											$event_location = $event_location;
										} else {
											$event_location = 'N/A';
										}
										?>
										<span class='event-location'>
											<span> Location: </span >
											<i class ='eicon-location' > </i>
											<?php echo esc_html( $event_location ); ?>
										</span>
										<span class="event-price">
											<span>Price: </span>
											<i class="eicon-price-tag"></i>
											<?php echo esc_html( $event_price ); ?>
										</span>
									</div>

								</div>
							</div>
							</a>
					<?php endwhile; ?>
				</div>

				<?php if ( $settings['pagination_type'] === 'numbers' ) : ?>
					<div class="events-pagination">
						<?php
						echo paginate_links(array(
							'total' => $query->max_num_pages,
							'current' => $paged,
							'prev_text' => __('&laquo; Previous', 'savanah-event'),
							'next_text' => __('Next &raquo;', 'savanah-event'),
						));
						?>
					</div>
				<?php endif; ?>

				<?php if ( $settings['pagination_type'] === 'infinite' ) : ?>
					<div class="events-loader" style="display: none;">
						<div class="loader"></div>
					</div>
					<button class="load-more-btn"><?php echo esc_html__('Load More Events', 'savanah-event'); ?></button>
				<?php endif; ?>
			</div>
			</div>
			<?php
		endif;
		wp_reset_postdata();
	}
}