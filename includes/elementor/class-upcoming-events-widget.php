<?php
class Upcoming_Events_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'upcoming_events';
	}

	public function get_title() {
		return __( 'Upcoming Events', 'savanah-event' );
	}

	public function get_icon() {
		return 'eicon-calendar';
	}

	public function get_categories() {
		return array( 'general' );
	}

	protected function register_controls() {
		// Content Section
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
			'pagination_type',
			array(
				'label'   => __( 'Pagination Type', 'savanah-event' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'none',
				'options' => array(
					'none'     => __( 'None', 'savanah-event' ),
					'numbers'  => __( 'Numbers', 'savanah-event' ),
					'infinite' => __( 'Infinite Scroll', 'savanah-event' ),
				),
			)
		);

		$this->end_controls_section();

		// Style Section
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

	protected function render() {
		$settings = $this->get_settings_for_display();
		$paged    = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

		$args = array(
			'post_type'      => 'event',
			'posts_per_page' => $settings['posts_per_page'],
			'meta_key'       => '_event_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'paged'          => $paged,
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

		if ( $query->have_posts() ) : ?>
			<div class="upcoming-events-widget" data-pagination="<?php echo esc_attr( $settings['pagination_type'] ); ?>">
				<div class="events-grid">
					<?php
					while ( $query->have_posts() ) :
						$query->the_post();
						$event_date = get_post_meta( get_the_ID(), '_event_date', true );
						$event_time = get_post_meta( get_the_ID(), '_event_time', true );
						$event_type = get_post_meta( get_the_ID(), '_event_type', true );
						?>
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
										<i class="eicon-calendar"></i>
										<?php echo date_i18n( get_option( 'date_format' ), strtotime( $event_date ) ); ?>
									</span>
									<span class="event-time">
										<i class="eicon-clock-o"></i>
										<?php echo date_i18n( get_option( 'time_format' ), strtotime( $event_time ) ); ?>
									</span>
									<span class="event-type">
										<i class="eicon-location"></i>
										<?php echo $event_type === 'online' ? __( 'Online', 'savanah-event' ) : __( 'In Person', 'savanah-event' ); ?>
									</span>
								</div>
								<div class="event-excerpt">
									<?php the_excerpt(); ?>
								</div>
							</div>
						</div>
					<?php endwhile; ?>
				</div>

				<?php if ( $settings['pagination_type'] === 'numbers' ) : ?>
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

				<?php if ( $settings['pagination_type'] === 'infinite' ) : ?>
					<div class="events-loader" style="display: none;">
						<div class="loader"></div>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $settings['pagination_type'] === 'infinite' ) : ?>
				<script>
				jQuery(function($) {
					var loading = false;
					var page = 1;
					var container = $('.upcoming-events-widget[data-pagination="infinite"]');
					var grid = container.find('.events-grid');
					var loader = container.find('.events-loader');
					var maxPages = <?php echo $query->max_num_pages; ?>;

					$(window).scroll(function() {
						if (loading || page >= maxPages) return;

						if ($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
							loading = true;
							page++;
							loader.show();

							$.ajax({
								url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
								type: 'POST',
								data: {
									action: 'load_more_events',
									page: page,
									posts_per_page: <?php echo $settings['posts_per_page']; ?>
								},
								success: function(response) {
									if (response) {
										grid.append(response);
										loading = false;
									}
									loader.hide();
								}
							});
						}
					});
				});
				</script>
			<?php endif; ?>
			<?php
		endif;
		wp_reset_postdata();
	}
}