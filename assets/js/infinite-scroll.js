(function($) {
    'use strict';

    class EventsInfiniteScroll {
        constructor() {
            this.loading = false;
            this.page = 1;
            this.container = $('.upcoming-events-widget[data-pagination="infinite"]');
            
            if (this.container.length) {
                this.grid = this.container.find('.events-grid');
                this.loader = this.container.find('.events-loader');
                this.maxPages = this.container.data('max-pages');
                this.setupLoadMoreButton();
            }
        }

        setupLoadMoreButton() {
            this.loadMoreBtn = $('<button>', {
                class: 'load-more-btn',
                text: 'Load More Events'
            });
            this.container.append(this.loadMoreBtn);
            this.loadMoreBtn.on('click', () => this.loadMoreEvents());
        }

        loadMoreEvents() {
           
            if (this.loading) return;
            
            this.loading = true;
            this.loader.show();
            this.loadMoreBtn.prop('disabled', true).text('Loading...');
            this.page++;
            console.log('events clicked');
            $.ajax({
                url: savanah_event.ajax_url,
                type: 'POST',
                data: {
                    action: 'load_more_events',
                    page: this.page,
                    posts_per_page: this.container.data('posts-per-page'),
                    nonce: savanah_event.nonce
                },
                success: (response) => {
                    console.log( response);
                    if (response) {
                        console.log('response', response);
                        this.grid.append(response);
                    }
                    this.loading = false;
                    this.loader.hide();
                    this.loadMoreBtn.prop('disabled', false).text('Load More Events');
                    
                    if (this.page >= this.maxPages) {
                        this.loadMoreBtn.hide();
                    }
                },
                error: () => {
                    console.log('Error loading more events');
                    this.loading = false;
                    this.loader.hide();
                    this.loadMoreBtn.prop('disabled', false).text('Load More Events');
                }
            });
        }
    }

    $(document).ready(() => {
        new EventsInfiniteScroll();
    });

})(jQuery);