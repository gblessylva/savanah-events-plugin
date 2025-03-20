<?php
$event_date     = get_post_meta( get_the_ID(), '_event_date', true );
$event_time     = get_post_meta( get_the_ID(), '_event_time', true );
$event_type     = get_post_meta( get_the_ID(), '_event_type', true );
$event_price    = get_post_meta( get_the_ID(), '_event_price', true );
$event_status   = get_post_meta( get_the_ID(), '_event_status', true );
$event_venue    = get_post_meta( get_the_ID(), '_event_venue', true );
$event_location = get_post_meta( get_the_ID(), '_event_location', true );
?>
<div class="event-item">
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="event-thumbnail">
			<a href="<?php the_permalink(); ?>">
				<?php if ( $event_status ) : ?>
					<span class="event-status"><?php echo esc_html( $event_status ); ?></span>
				<?php endif; ?>
				<?php the_post_thumbnail( 'medium' ); ?>
			</a>
		</div>
	<?php endif; ?>
	<div class="event-content">
		<div class="event-header">
			<div class="event-datetime">
				<?php if ( $event_date ) : ?>
					<span class="event-date">
						<?php echo date_i18n( 'D, M d', strtotime( $event_date ) ); ?>
					</span>
				<?php endif; ?>
				<?php if ( $event_time ) : ?>
					<span class="event-time">
						<?php echo date_i18n( 'g:i A', strtotime( $event_time ) ); ?>
					</span>
				<?php endif; ?>
			</div>
		</div>
		
		<h3 class="event-title">
			<a href="<?php the_permalink(); ?>">
				<?php the_title(); ?>
			</a>
		</h3>

		<div class="event-meta">
			<?php if ( $event_venue && $event_location ) : ?>
				<div class="event-location">
					<?php echo esc_html( $event_venue ); ?>, <?php echo esc_html( $event_location ); ?>
				</div>
			<?php endif; ?>
			
			<?php if ( $event_price ) : ?>
				<div class="event-price">
					<?php echo esc_html( $event_price ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>