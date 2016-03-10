(function () {
   
   var ProductsFeed = {

        init: function() {
            this.processUrl = '/products/feed/process';
            this.feedData = {
                url: $('a#feed-url').text(),
                page: 1
            };
            this.feedContainer = $('.products__feed');
            this.feedRequest = {
                xhr: null,
                done: false
            };
            this.paginationInitialized = false;
            this.loader = $('.loader');
            this.error = null;

            return this;
        },

        process: function() {
            this.toggleLoading(true);
            this.feedRequest.done = false;
            this.feedRequest.xhr = $.post(this.processUrl, this.feedData, $.proxy(this.processCallback, this));
        },

        processCallback: function(data) {
            var feedData = this.feedData;
            this.toggleLoading(false);

            if (data.error) {
                return this.showError(data.error.message);
            }

            if (this.feedDirectoryNotSet()) {
                this.feedData.feed_directory = data.feed_directory;

                this.process();
                return this.getFeedSet(this.onFirstSetLoad);
            }

            this.feedRequest.done = true;
        },

        getFeedSet: function(handler) {
            var feedData = {
                feed_directory: this.feedData.feed_directory,
                page: this.feedData.page
            };

            this.toggleLoading(true);

            $.post('/products/feed/display', feedData, $.proxy(handler, this));
        },

        onFirstSetLoad: function(data) {
            var requestDone = this.feedRequest.done,
                products = data.products;

            this.toggleLoading(false);

            if (requestDone && ! products) {
                return this.showError('No feed available from this url.');
            }

            if (! requestDone && ! products) {
                return this.getFeedSet(this.onFirstSetLoad);
            }

            this.createProducts(products);
            this.initializePagination();
        },

        createProducts: function(products) {
            var html = '',
                rowHtml = '',
                rowLimit = 4,
                columnCount = 0;

            var encloseToRow = function(rowHtml) {
                return '<div class="row">' + rowHtml + '</div>' + "\n";
            };

            for(var i = 0, len = products.length; i < len; i++) {
                rowHtml += this.productHtml(products[i]);
                columnCount++;

                if (columnCount === rowLimit) {
                    html += encloseToRow(rowHtml);
                    rowHtml = '';
                    columnCount = 0;
                }
            }
            // enclose remaining html
            html += encloseToRow(rowHtml);

            this.feedContainer.append(html);
        },

        productHtml: function(product) {
            var categories = 'N/A';
            var html = '<div class="col-sm-6 col-xs-12 col-md-3">' + "\n" +
                       '    <div class="product data-set="'+ this.feedData.page +'">'+ "\n" +
                       '       <a href="'+ product.productUrl +'" class="product__link" target="__blank" title="'+ product.name +'">'+ "\n" +
                       '            <img src="'+ product.imageUrl +'" alt="'+ product.name +'" class="product__image">'+ "\n" +
                       '        </a>'+ "\n" +
                       '         <div class="product__meta">'+ "\n" +
                       '            <a href="'+ product.productUrl +'" class="product__name" target="__blank" title="'+ product.name +'">'+ "\n" +
                       '                '+ product.name + "\n" +
                       '            </a>'+ "\n" +
                       '            <div class="product__price">'+ "\n" +
                       '                <span class="product__price__value">Price: '+ product.price +'</span>'+ "\n" +
                       '                <span class="product__price__currency">'+ product.currency +'</span>'+ "\n" +
                       '            </div>'+ "\n";

                if (product.categories) {
                    categories = product.categories.join(', ');
                }

                html += '           <span class="product__categories" title="Categories: '+ categories +'">Categories: '+ categories +'</span>'+ "\n" +
                        '      </div>'+ "\n" +
                        '   </div>'+ "\n" +
                        '</div>' + "\n";

            return html;
        },

        initializePagination: function() {
            if ( ! this.paginationInitialized) {
                this.loadMoreButton = $('<button/>', { 
                    text: 'Load more...',
                    class: 'load-more-btn btn btn-default'
                });

                this.loadMoreButton.initialText = this.loadMoreButton.text();

                this.feedContainer.after(this.loadMoreButton);
                this.loadMoreButton.on('click', $.proxy(this.loadMoreClick, this));
            }
        },

        loadMoreClick: function() {
            this.feedData.page++;

            this.loadMoreButton.text('Loading...').prop('disabled', true);

            this.getFeedSet(this.onLoadMore);
        },

        onLoadMore: function(data) {
            var loadMoreButton = this.loadMoreButton;

            this.toggleLoading(false);

            // all feed set are loaded.
            if ( ! data.products && this.feedRequest.done) {
                return loadMoreButton.text('No more products to load.')
                         .prop('disabled', true)
                         .unbind('click');
            }

            // if feed request is not yet done and no data returned, attempt to fetch the next page.
            if (! data.products && ! this.feedRequest.done) {
                this.feedData.page++;
                return this.getFeedSet(this.onLoadMore);
            }

            loadMoreButton.text(loadMoreButton.initialText).prop('disabled', false);
            this.createProducts(data.products);
        },

        feedDirectoryNotSet: function() {
            return ! this.feedData.feed_directory;
        },

        showError: function(message) {
            var error = this.error;

            if ( ! error) {
                error = this.error = $('<span/>', { 
                    class: 'feed__error'
                });

                this.feedContainer.append(error);
            }

            error.hide().text(message).fadeIn(100);
        },

        hideError: function() {
            this.error && this.error.fadeOut();
        },

        toggleLoading: function(show) {
            if (show) {
                return this.loader.show();
            }

            this.loader.fadeOut(1000);
        }
   };

   ProductsFeed.init().process();
})();