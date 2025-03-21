<?php
/**
 * Template for displaying individual event items
 *
 * This template is responsible for rendering the event item card layout
 * including event details like date, time, venue, location, price and thumbnail.
 *
 * @package Savanah_Event
 * @since 1.0.0
 */
$event_date     = sanitize_text_field( get_post_meta( get_the_ID(), '_event_date', true ) );
$event_time     = sanitize_text_field( get_post_meta( get_the_ID(), '_event_time', true ) );
$event_status   = sanitize_text_field( get_post_meta( get_the_ID(), '_event_status', true ) );
$event_venue    = sanitize_text_field( get_post_meta( get_the_ID(), '_event_venue', true ) );
$event_location = sanitize_text_field( get_post_meta( get_the_ID(), '_event_location', true ) );
$event_price    = sanitize_text_field( get_post_meta( get_the_ID(), '_event_price', true ) );
$permalink      = get_permalink();
?>

<a href="<?php echo esc_url( $permalink ); ?>" class="event-item-link">
	<div class="event-item">
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="event-thumbnail">
				<?php if ( ! empty( $event_status ) ) : ?>
					<span class="event-status"><?php echo esc_html( $event_status ); ?></span>
				<?php endif; ?>
				<?php the_post_thumbnail( 'medium' ); ?>
			</div>
		<?php endif; ?>
		
		<div class="event-content">
			<div class="event-header">
				<?php if ( $event_date && $event_time ) : ?>
					<div class="event-datetime">
						<?php
						$formatted_date = date_i18n( 'D, M d', strtotime( $event_date ) );
						$formatted_time = date_i18n( 'g:i A', strtotime( $event_time ) );
						echo esc_html( $formatted_date ) . ', ' . esc_html( $formatted_time );
						?>
					</div>
				<?php endif; ?>
			</div>

			<h3 class="event-title"><?php echo esc_html( get_the_title() ); ?></h3>

			<?php if ( $event_venue || $event_location || $event_price ) : ?>
				<div class="event-meta">
					<?php if ( $event_venue && $event_location ) : ?>
						<div class="event-location">
							<?php echo esc_html( $event_venue ) . ', ' . esc_html( $event_location ); ?>
						</div>
					<?php endif; ?>
					
					<?php if ( $event_price ) : ?>
						<div class="event-price">
							<?php echo esc_html( $event_price ); ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</a>
