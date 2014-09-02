;(function($, window, document, undefined) {
    "use strict";

    /**
     * Checks if a the given {@link obj} is a numeric (float) value.
     * @private
     * @param {Mixed} obj
     * @returns {Boolean}
     */
    var isNumeric = function(obj) {
        return !$.isArray(obj) && (obj - parseFloat(obj) + 1) >= 0;
    };

    /**
     * Parses the given {@link url} parameter and extracts all query parameters. If the parameter is numeric
     * it will automatically based to a {@link Number} instead of a {@link String}.
     * @private
     * @param {String} url - Usually {@link window.location.href}
     * @returns {{}} Object with all extracted parameters
     */
    var parseQueryString = function(url) {
        var qparams = {},
            parts = (url || '').split('?'),
            qparts, qpart,
            i=0;

        if(parts.length <= 1){
            return qparams;
        }

        qparts = parts[1].split('&');
        for (i in qparts) {
            var key, value;

            qpart = qparts[i].split('=');
            key = decodeURIComponent(qpart[0])
            value = decodeURIComponent(qpart[1] || '');
            qparams[key] = (isNumeric(value) ? parseFloat(value, 10) : value);
        }

        return qparams;
    };

    /** @type {Array} Selectors for the listing part of the plugin  */
    var listingSelectors = [
        '.listing .product--box .box--image',
        '.listing .product--box .product--title',
        '.listing .product--box .product--actions .action--more'
    ];

    /**
     * Ajax Product navigation
     *
     * The jQuery plugin provides the product navigation (= previous / next product and the overview button) using AJAX.
     * The plugin is necessary to fully support the HTTP cache.
     *
     * Please keep in mind that the plugin only works when the url contains the category parameter and the browser
     * needs to support {@link window.sessionStorage}.
     */
    $.plugin('ajaxProductNavigation', {
        /** @type {Object} Default configuration of the plugin */
        defaults: {
            arrowAnimSpeed: 500,
            arrowOffset: -45,
            productBoxSelector: '.product--box',
            productDetailsSelector: '.product--details',
            prevLinkSelector: 'a.navigation--link.link--prev',
            nextLinkSelector: 'a.navigation--link.link--next',
            breadcrumbButtonSelector: '.breadcrumb--button .btn'
        },

        /**
         * Initializes the plugin and checks if we're on listing page or on the detail page.
         * @returns {boolean}
         */
        init: function() {
            var me = this;

            me._mode = (function() {
                if(me.$el.hasClass('is--ctl-listing')) {
                    return 'listing';
                } else if(me.$el.hasClass('is--ctl-detail')) {
                    return 'detail';
                }
                return undefined;
            })();

            if(!me._mode) {
                return false;
            }

            if(me._mode === 'listing') {
                me.registerListingEventListeners(listingSelectors);
            } else {
                var params = parseQueryString(window.location.href);

                // ...the url wasn't called through the listing
                if(!params.hasOwnProperty('c')) {
                    me.clearCurrentProductState();
                    return;
                }

                me.getProductNavigation();
            }
        },

        /**
         * Adds an event listener to the back button which uses {@link window.history.back}.
         */
        mapBackButton: function(response) {
            var me = this,
                backBtn = me.$el.find(me.opts.breadcrumbButtonSelector);

            backBtn.attr('href', response.href);
        },

        /**
         * Registers the event listeners for the listing page.
         * @param {Array} selectors
         * @returns {Void}
         */
        registerListingEventListeners: function(selectors) {
            var me = this;

            selectors = selectors.join(', ');
            me.$el.find(selectors).on(me.getEventName('click'), $.proxy(me.onProductLinkInListing, me));
        },

        /**
         * Event handler method which saves the current listing state like selected sorting and active page
         * into the {@link window.sessionStorage}
         *
         * @event click
         * @param {MouseEvent} event
         */
        onProductLinkInListing: function(event) {
            var me = this,
                params = parseQueryString(window.location.href),
                $target = $(event.target),
                $parent = $target.parents(me.opts.productBoxSelector),
                categoryId = parseInt($parent.attr('data-category-id'), 10),
                orderNumber = $parent.attr('data-ordernumber');

            if(categoryId && isNumeric(categoryId) && !isNaN(categoryId)) {
                params.categoryId = categoryId;
            }

            if(orderNumber && orderNumber.length) {
                params.ordernumber = orderNumber;
            }

            me.saveCurrentProductState(params);
        },

        /**
         * Tries to write the given parameters into the {@link window.sessionStorage}. If the browser
         * doesn't support it, the method will just return false.
         * @param {Object} params
         * @returns {boolean} Truee, if the state was saved, otherwise false.
         */
        saveCurrentProductState: function(params) {
            try {
                window.sessionStorage.setItem('lastProductState', JSON.stringify(params));
                return true;
            } catch(err) {
                return false;
            }
        },

        /**
         * Tries to read out the last saved product state.
         * @returns {Object} The last saved product state or an empty object.
         */
        restoreCurrentProductState: function() {
            try {
                return JSON.parse(window.sessionStorage.getItem('lastProductState'));
            } catch(err) {
                return {};
            }
        },

        /**
         * Tries to refresh the current product state with the information which is given
         * on the detail page.
         * @returns {Object}
         */
        refreshCurrentProductState: function() {
            var me = this,
                orderNumber = me.$el.find(me.opts.productDetailsSelector).attr('data-ordernumber'),
                params = me.restoreCurrentProductState();

            if(orderNumber && orderNumber.length) {
                params.ordernumber = orderNumber;
            }
            me.saveCurrentProductState(params);

            return params;
        },

        /**
         * Tries to remove the product state from the {@link window.sessionStorage}.
         * @returns {boolean} True, if the state could be removed, otherwise false.
         */
        clearCurrentProductState: function() {
            try {
                window.sessionStorage.removeItem('lastProductState');
                return true;
            } catch(err) {
                return false;
            }
        },

        /**
         * Requests the product navigation information from the server side using an AJAX request.
         * @returns {boolean|void} False, if the url is missing, otherwise void
         */
        getProductNavigation: function() {
            var me = this,
                params = me.refreshCurrentProductState(),
                url;

            if($.isEmptyObject(params)) {
                return false;
            }
            url = me.$el.find(me.opts.productDetailsSelector).attr('data-product-navigation');

            if(!url || !url.length) {
                return false;
            }

            $.ajax({
                'url': url,
                'data': params,
                'method': 'GET',
                'dataType': 'json',
                'success': $.proxy(me.setProductNavigation, me)
            })
        },

        /**
         * Sets the requested product navigation information into the DOM and displays the
         * prev and next arrow.
         *
         * @param {Object} response - Server response
         * @returns {boolean}
         */
        setProductNavigation: function(response) {
            var me = this,
                prevLink = me.$el.find(me.opts.prevLinkSelector),
                nextLink = me.$el.find(me.opts.nextLinkSelector);

            if(response.hasOwnProperty('currentListing')) {
                me.mapBackButton(response.currentListing);
            }

            if(response.hasOwnProperty('previousProduct')) {
                var previousProduct = response.previousProduct;

                prevLink
                    .attr('href', previousProduct.href)
                    .attr('title', previousProduct.name)
                    .animate({
                        'opacity': 1
                    }, me.opts.arrowAnimSpeed);
            } else {
                prevLink.remove();
            }

            if(response.hasOwnProperty('nextProduct')) {
                var nextProduct = response.nextProduct;

                nextLink
                    .attr('href', nextProduct.href)
                    .attr('title', nextProduct.name)
                    .animate({
                        'opacity': 1
                    }, me.opts.arrowAnimSpeed);
            } else {
                nextLink.remove();
            }

            return true;
        }
    });

    /** Starts the plugin */
    $(function() {
        $('body').ajaxProductNavigation();
    });
})(jQuery, window, document);