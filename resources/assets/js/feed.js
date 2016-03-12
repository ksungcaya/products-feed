(function () {
   
   var ProductsFeed = {

        init: function() {
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

            this.productModal = $('[data-remodal-id=product-modal]');
            this.productModalText = this.productModal.find('.modal__text');
            this.productModalBody = this.productModal.find('.modal__body');

            this.bindEvents();
            return this;
        },

        bindEvents: function() {
            $(window).bind('beforeunload', $.proxy(function() {
                if ( ! this.feedRequest.done) {
                    return 'Feed is still processing, do you really want to leave the page?';
                }
            }, this));
        },

        process: function() {
            this.toggleLoading(true);
            this.feedRequest.done = false;
            this.feedRequest.xhr = $.post('/products/feed/process', this.feedData, $.proxy(this.processCallback, this));
        },

        processCallback: function(data) {
            var feedData = this.feedData;
            this.toggleLoading(false);

            if (data.error) {
                this.feedRequest.done = true;
                return this.showError(data.error.message);
            }

            if (this.feedDirectoryNotSet()) {
                this.feedData.feed_directory = data.feed_directory;
                this.process();
                return this.getFeedSet(this.onFirstPageLoad);
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

        onFirstPageLoad: function(data) {
            var requestDone = this.feedRequest.done,
                products = data.products;

            this.toggleLoading(false);

            if (requestDone && ! products) {
                return this.showError('No feed available from this url.');
            }

            if (! requestDone && ! products) {
                return this.getFeedSet(this.onFirstPageLoad);
            }

            this.createProducts(products);
            this.initializePagination();
            this.bindProductEvents();
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
            var categories = this.extractCategories(product.categories);

            var html = '<div class="col-sm-6 col-xs-12 col-md-3">' + "\n" +
                       '    <div class="product" data-set="'+ this.feedData.page +'" data-id="'+ product.productId +'">'+ "\n" +
                       '        <a href="'+ product.productUrl +'" class="product__link" target="__blank" title="'+ product.name +'">'+ "\n" +
                       '            <img src="'+ product.imageUrl +'" alt="'+ product.name +'" class="product__image">'+ "\n" +
                       '        </a>'+ "\n" +
                       '        <div class="product__meta">'+ "\n" +
                       '           <a href="'+ product.productUrl +'" class="product__name" target="__blank" title="'+ product.name +'">'+ "\n" +
                       '               '+ product.name + "\n" +
                       '           </a>'+ "\n" +
                       '           <div class="product__price">'+ "\n" +
                       '               <span class="product__price__value">Price: '+ product.price +'</span>'+ "\n" +
                       '               <span class="product__price__currency">'+ product.currency +'</span>'+ "\n" +
                       '           </div>'+ "\n" +
                       '           <span class="product__categories" title="Categories: '+ categories +'">Categories: '+ categories +'</span>'+ "\n" +
                       '      </div>'+ "\n" +
                       '   </div>'+ "\n" +
                       '</div>' + "\n";

            return html;
        },

        extractCategories: function(categories) {
            if (! categories) {
                return 'N/A';
            }

            return categories.join(', ');
        },

        initializePagination: function() {
            if ( ! this.paginationInitialized) {
                this.loadMoreButton = $('<button/>', { 
                    text: 'Load more...',
                    class: 'load-more-btn btn btn-default'
                });

                this.loadMoreButton.initialText = this.loadMoreButton.text();

                this.loader.after(this.loadMoreButton);
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

        bindProductEvents: function() {
            var _this = this;
                params = { feed_directory: this.feedData.feed_directory};

            this.feedContainer.on('click', '.product', function(e) {
                e.preventDefault();

                _this.productModalBody.empty();
                _this.productModalText.text('Fetching product...');
                _this.getProductModal().open();

                params.page = $(this).data('set') || _this.feedData.page;
                params.product_id = $(this).data('id');

                $.post('/products/feed/display/product', params, $.proxy(_this.onProductFetch, _this));
            });
        },

        onProductFetch: function(data) {
            var html = '';
            if (data.error) {
                return this.productModalText
                           .html('<span class="feed__error">' + data.error.message + '</span>');
            }

            this.productModalText.empty();
            return this.productModalBody.html(this.productModalHtml(data));
        },

        getProductModal: function() {
            return this.productModal.remodal();
        },

        productModalHtml: function(product) {
            return '<div class="row">'+
                   '    <div class="col-sm-4 col-xs-12">' + "\n" +
                   '        <a href="" target="__blank">' + "\n" +
                   '            <img src="'+ product.imageUrl +'" class="product__modal__image">' + "\n" +
                   '        </a>' + "\n" +
                   '        <div class="product__modal__meta">' + "\n" +
                   '            <div>' + "\n" +
                   '                <span>Price: '+ product.price +'</span>' + "\n" +
                   '                <span>'+ product.currency +'</span>' + "\n" +
                   '            </div>' + "\n" +
                   '            <span class="product__modal__meta">Categories: '+ this.extractCategories(product.categories) +'</span>' + "\n" +
                   '        </div>' + "\n" +
                   '    </div>' + "\n" +
                   '    <div class="col-sm-8 col-xs-12">' + "\n" +
                   '        <a href="'+ product.productUrl +'" class="product__modal__name" target="__blank">'+ product.name +'</a>' + "\n" +
                   '        <div class="product__modal__description">'+ (this.nl2br(product.description) || 'No description available') +'</div>' + "\n" +
                   '    </div>' + "\n" +
                   '</div>';
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
        },

        nl2br: function(str, is_xhtml) {
            var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>';
              return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
        }
   };

   ProductsFeed.init().process();
   window.ProductsFeed = ProductsFeed;
})();