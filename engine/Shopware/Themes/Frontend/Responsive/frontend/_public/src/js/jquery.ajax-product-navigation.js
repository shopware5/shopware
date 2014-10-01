;(function ($, StateManager, Modernizr, location) {
    'use strict';

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
        /**
         * Default configuration of the plugin
         *
         * @type {Object}
         */
        defaults: {

            /**
             * Animation speed in milliseconds of the arrow fading.
             *
             * @type {Number}
             */
            arrowFadeSpeed: 500,

            /**
             * Animation speed in milliseconds of the arrow sliding.
             *
             * @type {Number}
             */
            arrowSlideSpeed: 300,

            /**
             * Default offset of the arrows.
             *
             * @type {Number}
             */
            arrowOffset: 40,

            /**
             * Offset of the arrows in pixel when they get hovered over.
             *
             * @type {Number}
             */
            arrowSlideOffset: 140,

            /**
             * Class to enable the arrow sliding.
             *
             * @type {String}
             */
            arrowSlideClass: 'can--slide',

            /**
             * Selector for the product box in the listing.
             *
             * @type {String}
             */
            productBoxSelector: '.product--box',

            /**
             * Selector for the product details.
             * This element should have data attributes of the ordernumber and product navigation link.
             *
             * @type {String}
             */
            productDetailsSelector: '.product--details',

            /**
             * Selector for the previous button.
             *
             * @type {String}
             */
            prevLinkSelector: '.navigation--link.link--prev',

            /**
             * Selector for the next button.
             *
             * @type {String}
             */
            nextLinkSelector: '.navigation--link.link--next',

            /**
             * Selector for the breadcrumb back button.
             *
             * @type {String}
             */
            breadcrumbButtonSelector: '.breadcrumb--button .btn',

            /**
             * Selector for the image container.
             *
             * @type {String}
             */
            imageContainerSelector: '.image--container',

            /**
             * Selectors of product box childs in the listing.
             *
             * @type {Array}
             */
            listingSelectors: [
                '.listing .product--box .box--image',
                '.listing .product--box .product--title',
                '.listing .product--box .product--actions .action--more'
            ]
        },

        /**
         * Initializes the plugin and registers event listeners depending on
         * whether we are on the listing- or detail page.
         *
         * @public
         * @method init
         */
        init: function () {
            var me = this,
                $el = me.$el,
                isListing = $el.hasClass('is--ctl-listing'),
                isDetail = $el.hasClass('is--ctl-detail'),
                opts = me.opts;

            if (!(isListing || isDetail)) {
                return;
            }

            me.storage = StorageManager.getStorage('session');
            me.urlParams = me.parseQueryString(location.href);

            me.$prevButton = $el.find(opts.prevLinkSelector);
            me.$nextButton = $el.find(opts.nextLinkSelector);
            me.$backButton = $el.find(opts.breadcrumbButtonSelector);
            me.$productDetails = $el.find(opts.productDetailsSelector);

            if (isListing) {
                me.registerListingEventListeners();
            } else {
                if (!me.urlParams.hasOwnProperty('c') && !me.$productDetails.attr('data-category-id')) {
                    me.clearCurrentProductState();
                    return;
                }

                me.registerDetailEventListeners();
            }

            me.getProductNavigation();
        },

        /**
         * Parses the given {@link url} parameter and extracts all query parameters. If the parameter is numeric
         * it will automatically based to a {@link Number} instead of a {@link String}.
         *
         * @private
         * @method parseQueryString
         * @param {String} url - Usually {@link window.location.href}
         * @returns {Object} All extracted URL-parameters
         */
        parseQueryString: function (url) {
            var params = {},
                urlParts = (url + '').split('?'),
                queryParts,
                part,
                key,
                value,
                p;

            if (urlParts.length < 2) {
                return params;
            }

            queryParts = urlParts[1].split('&');

            for (p in queryParts) {
                if (!queryParts.hasOwnProperty(p)) {
                    continue;
                }

                part = queryParts[p].split('=');

                key = decodeURIComponent(part[0]);
                value = decodeURIComponent(part[1] || '');

                params[key] = $.isNumeric(value) ? parseFloat(value) : value;
            }

            return params;
        },

        /**
         * Registers the event listeners for the listing page.
         *
         * @private
         * @method registerListingEventListeners
         */
        registerListingEventListeners: function () {
            var me = this,
                selectors = me.opts.listingSelectors.join(', '),
                $listingEls = me.$el.find(selectors);

            me._on($listingEls, 'click', $.proxy(me.onClickProductInListing, me));
        },

        /**
         * Registers the event listeners for the detail page.
         *
         * @private
         * @method registerDetailEventListeners
         */
        registerDetailEventListeners: function () {
            var me = this;

            me._on(window, 'resize', $.proxy(me.checkPossibleSliding, me));
        },

        /**
         * Checks if it's possible for the arrows to slide to full extend.
         * If so, add the arrow slide class to the arrows.
         *
         * @private
         * @method checkPossibleSliding
         */
        checkPossibleSliding: function () {
            var me = this,
                opts = me.opts,
                offset = opts.arrowOffset,
                slideOffset = opts.arrowSlideOffset,
                $prevBtn = me.$prevButton,
                $nextBtn = me.$nextButton,
                remainingSpacePrev = $prevBtn.offset().left + offset,
                remainingSpaceNext = $(window).width() - $nextBtn.offset().left - $nextBtn.outerWidth() + opts.arrowOffset;

            $prevBtn.toggleClass(opts.arrowSlideClass, remainingSpacePrev >= slideOffset);
            $nextBtn.toggleClass(opts.arrowSlideClass, remainingSpaceNext >= slideOffset);
        },

        /**
         * Event handler method which saves the current listing state like
         * selected sorting and active page into the {@link window.sessionStorage}
         *
         * @event click
         * @param {MouseEvent} event
         */
        onClickProductInListing: function (event) {
            var me = this,
                params = me.urlParams,
                $target = $(event.target),
                $parent = $target.parents(me.opts.productBoxSelector),
                categoryId = parseInt($parent.attr('data-category-id'), 10),
                orderNumber = $parent.attr('data-ordernumber');

            if ($.isNumeric(categoryId)) {
                params.categoryId = categoryId;
            }

            if (orderNumber && orderNumber.length) {
                params.ordernumber = orderNumber;
            }

            me.saveCurrentProductState(params);
        },

        /**
         * Writes the given parameters into the {@link window.sessionStorage}.
         * The key 'lastProductState' will be used.
         *
         * @private
         * @method saveCurrentProductState
         * @param {Object} params
         */
        saveCurrentProductState: function (params) {
            this.storage.setItem('lastProductState', JSON.stringify(params));
        },

        /**
         * Reads the last saved product state by the key 'lastProductState'.
         *
         * @private
         * @method restoreCurrentProductState
         * @returns {Object} The last saved product state or an empty object.
         */
        restoreCurrentProductState: function () {
            return JSON.parse(this.storage.getItem('lastProductState')) || {};
        },

        /**
         * Removes the product state from the {@link window.sessionStorage}.
         *
         * @private
         * @method clearCurrentProductState
         */
        clearCurrentProductState: function () {
            this.storage.removeItem('lastProductState');
        },

        /**
         * Tries to refresh the current product state with the ordernumber that
         * is given from the product detail element by the attribute 'data-ordernumber'.
         *
         * @private
         * @method refreshCurrentProductState
         * @returns {Object}
         */
        refreshCurrentProductState: function () {
            var me = this,
                orderNumber = me.$productDetails.attr('data-ordernumber'),
                categoryId = me.$productDetails.attr('data-category-id'),
                params = me.restoreCurrentProductState();

            if (orderNumber && orderNumber.length) {
                params.ordernumber = orderNumber;
            }

            if (categoryId && categoryId.length) {
                params.categoryId = categoryId;
            }

            me.saveCurrentProductState(params);

            return params;
        },

        /**
         * Requests the product navigation information from the server side
         * using an AJAX request.
         *
         * The url will be fetched from the product details element by
         * the 'data-product-navigation' attribute.
         *
         * @private
         * @method getProductNavigation
         */
        getProductNavigation: function () {
            var me = this,
                params = me.refreshCurrentProductState(),
                url = me.$productDetails.attr('data-product-navigation');

            if ($.isEmptyObject(params) || !url || !url.length) {
                return;
            }

            $.ajax({
                'url': url,
                'data': params,
                'method': 'GET',
                'dataType': 'json',
                'success': $.proxy(me.onProductNavigationLoaded, me)
            });
        },

        /**
         * Animates a given button with a given anim
         *
         * @param $button
         * @param css
         * @param speed
         */
        animateButton: function ($button, css, speed) {
            if (Modernizr.csstransitions) {
                $button.transition(css, speed);
                return;
            }

            $button.animate(css, speed);
        },

        /**
         * Sets the requested product navigation information into the DOM and displays the
         * prev and next arrow.
         *
         * @private
         * @method onProductNavigationLoaded
         * @param {Object} response - Server response
         */
        onProductNavigationLoaded: function (response) {
            var me = this,
                opts = me.opts,
                $prevBtn = me.$prevButton,
                $nextBtn = me.$nextButton,
                listing = response.currentListing,
                prevProduct = response.previousProduct,
                nextProduct = response.nextProduct,
                animSpeed = opts.arrowFadeSpeed,
                animCss = {
                    opacity: 1
                };

            if (listing && listing.href) {
                me.$backButton.attr('href', listing.href);
            }

            if (typeof prevProduct === 'object') {
                $prevBtn.find(opts.imageContainerSelector).css('background-image', 'url(' + prevProduct.image + ')');

                $prevBtn
                    .attr('href', prevProduct.href)
                    .attr('title', prevProduct.name)
                    .show();

                if (Modernizr.csstransitions) {
                    $prevBtn.transition(animCss, animSpeed);
                } else {
                    $prevBtn.animate(animCss, animSpeed);
                }
            }

            if (typeof nextProduct === 'object') {
                $nextBtn.find(opts.imageContainerSelector).css('background-image', 'url(' + nextProduct.image + ')');

                $nextBtn
                    .attr('href', nextProduct.href)
                    .attr('title', nextProduct.name)
                    .show();

                if (Modernizr.csstransitions) {
                    $nextBtn.transition(animCss, animSpeed);
                } else {
                    $nextBtn.animate(animCss, animSpeed);
                }
            }

            me.checkPossibleSliding();
        },

        /**
         * Destroys the plugin by removing all listeners.
         *
         * @public
         * @method destroy
         */
        destroy: function () {
            var me = this;

            me._destroy();
        }
    });
})(jQuery, StateManager, Modernizr, location);
