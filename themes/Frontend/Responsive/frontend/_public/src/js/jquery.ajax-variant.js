;(function ($, window) {
    /**
     * Shopware AJAX variant
     *
     * @example
     * HTML:
     * <div data-ajax-variants-container="true"></div>
     *
     * JS:
     * $('*[data-ajax-variants-container="true"]').swAjaxVariant();
     */
    $.plugin('swAjaxVariant', {

        /**
         * Supports the browser the history api
         * @boolean
         */
        hasHistorySupport: Modernizr.history,

        /**
         * Safari specific property which prevent safari to do another request on page load.
         * @boolean
         */
        initialPopState: true,

        /**
         * Default configuration of the plugin
         * @object
         */
        defaults: {
            productDetailsSelector: '.product--detail-upper',
            configuratorFormSelector: '.configurator--form',
            orderNumberSelector: '.entry--sku .entry--content',
            historyIdentifier: 'sw-ajax-variants',
            productDetailsDescriptionSelector: '.content--description',
            footerJavascriptInlineSelector: '#footer--js-inline'
        },

        /**
         * Initializes the plugin and sets up the necessary event handler
         */
        init: function () {
            // Check if we have a variant configurator
            if (!this.$el.find('.product--configurator').length) {
                return;
            }

            this.applyDataAttributes();

            this.$el
                .on(this.getEventName('click'), '*[data-ajax-variants="true"]', $.proxy(this.onChange, this))
                .on(this.getEventName('change'), '*[data-ajax-select-variants="true"]', $.proxy(this.onChange, this))
                .on(this.getEventName('click'), '.reset--configuration', $.proxy(this.onChange, this));

            $(window).on('popstate', $.proxy(this.onPopState, this));

            if (this.hasHistorySupport) {
                this.publishInitialState();
            }
        },

        /**
         * Replaces the most recent history entry, when the user enters the page.
         *
         * @returns void
         */
        publishInitialState: function () {
            var stateObj = this._createHistoryStateObject();

            window.history.replaceState(stateObj.state, stateObj.title);
        },

        /**
         * Requests the HTML structure of the product detail page using AJAX and injects the returned
         * content into the page.
         *
         * @param {Object} values
         * @param {Boolean} pushState
         */
        requestData: function (values, pushState) {
            var me = this,
                stateObj = me._createHistoryStateObject();

            $.loadingIndicator.open({
                closeOnClick: false,
                delay: 100
            });

            $.publish('plugin/swAjaxVariant/onBeforeRequestData', [me, values, stateObj.location]);

            values.template = 'ajax';

            if (stateObj.params.hasOwnProperty('c')) {
                values.c = stateObj.params.c;
            }

            $.ajax({
                url: stateObj.location,
                data: values,
                method: 'GET',
                success: function (response) {
                    var $response = $($.parseHTML(response, document, true)),
                        $productDetails,
                        $productDescription,
                        ordernumber;

                    // Replace the content
                    $productDetails = $response.find(me.opts.productDetailsSelector);
                    $(me.opts.productDetailsSelector).html($productDetails.html());

                    // Replace the description box
                    $productDescription = $response.find(me.opts.productDetailsDescriptionSelector);
                    $(me.opts.productDetailsDescriptionSelector).html($productDescription.html());

                    // Get the ordernumber for the url
                    ordernumber = $.trim(me.$el.find(me.opts.orderNumberSelector).text());

                    // Update global variables
                    window.controller = window.snippets = window.themeConfig = window.lastSeenProductsConfig = window.csrfConfig = null;
                    $(me.opts.footerJavascriptInlineSelector).replaceWith($response.filter(me.opts.footerJavascriptInlineSelector));

                    StateManager.addPlugin('*[data-image-slider="true"]', 'swImageSlider')
                        .addPlugin('.product--image-zoom', 'swImageZoom', 'xl')
                        .addPlugin('*[data-image-gallery="true"]', 'swImageGallery')
                        .addPlugin('*[data-add-article="true"]', 'swAddArticle')
                        .addPlugin('*[data-modalbox="true"]', 'swModalbox');

                    // Replace the async ready to fire the callbacks right after registration
                    if (Object.prototype.hasOwnProperty.call(window, 'replaceAsyncReady') && typeof (window.replaceAsyncReady) === 'function') {
                        window.replaceAsyncReady();
                    }

                    // Plugin developers should subscribe to this event to update their plugins accordingly
                    $.publish('plugin/swAjaxVariant/onRequestData', [me, response, values, stateObj.location]);

                    if (pushState && me.hasHistorySupport) {
                        me.pushState(stateObj, ordernumber);
                        $.publish('plugin/swAjaxVariant/onHistoryChanged', [me, response, values, stateObj.location]);
                    }
                },
                complete: function (response, status) {
                    $.loadingIndicator.close();
                    $.publish('plugin/swAjaxVariant/onRequestDataCompleted', [me, response, status, values, stateObj.location]);
                }
            });
        },

        /**
         * Push state to browser history.
         *
         * @param {Object} stateObj
         * @param {String} ordernumber
         */
        pushState: function(stateObj, ordernumber) {
            var location = stateObj.location + '?number=' + ordernumber;

            if (stateObj.params.hasOwnProperty('c')) {
                location += '&c=' + stateObj.params.c;
            }

            window.history.pushState(stateObj.state, stateObj.title, location);
        },

        /**
         * Event handler method which will be fired when the user click the back button
         * in it's browser.
         *
         * @param {EventObject} event
         */
        onPopState: function (event) {
            var state = event.originalEvent.state;

            if (!state || !state.hasOwnProperty('type') || state.type !== 'sw-ajax-variants') {
                return;
            }

            if ($('html').hasClass('is--safari') && this.initialPopState) {
                this.initialPopState = false;
                return;
            }

            if (!state.values.length) {
                state = '';
            }

            // Prevents the scrolling to top in webkit based browsers
            if (state && state.scrollPos) {
                window.setTimeout(function () {
                    $(window).scrollTop(state.scrollPos);
                }, 10);
            }

            $.publish('plugin/swAjaxVariant/onPopState', [this, state]);

            if (state && state.values) {
                this.requestData(state.values, false);
            }
        },

        /**
         * Event handler which will fired when the user selects a variant in the storefront.
         *
         * @param {EventObject} event
         */
        onChange: function (event) {
            var $target = $(event.target),
                $form = $target.parents('form'),
                values = {};

            $.each($form.serializeArray(), function (i, item) {
                if (item.name === '__csrf_token') {
                    return;
                }

                values[item.name] = item.value;
            });

            event.preventDefault();

            if (!this.hasHistorySupport) {
                $.loadingIndicator.open({
                    closeOnClick: false,
                    delay: 0
                });
                $form.submit();

                return false;
            }

            $.publish('plugin/swAjaxVariant/onChange', [this, values, $target]);

            this.requestData(values, true);
        },

        /**
         * Helper method which returns all available url parameters.
         *
         * @returns {Object}
         * @private
         */
        _getUrlParams: function () {
            var search = window.location.search.substring(1),
                urlParams = search.split('&'),
                params = {};

            $.each(urlParams, function (i, param) {
                param = param.split('=');

                if (param[0].length && param[1] && param[1].length && !params.hasOwnProperty(param[0])) {
                    params[decodeURIComponent(param[0])] = decodeURIComponent(param[1]);
                }
            });

            return params;
        },

        /**
         * Helper method which returns the full URL of the shop.
         *
         * @returns {string}
         * @private
         */
        _getUrl: function () {
            return window.location.protocol + '//' + window.location.host + window.location.pathname;
        },

        /**
         * Provides a state object which can be used with the {@link Window.history} API.
         *
         * The ordernumber will be fetched every time 'cause we're replacing the upper part of the detail page and
         * therefore we have to get the ordernumber using the DOM.
         *
         * @returns {Object} state object including title and location
         * @private
         */
        _createHistoryStateObject: function () {
            var $form = this.$el.find(this.opts.configuratorFormSelector),
                urlParams = this._getUrlParams(),
                location = this._getUrl();

            return {
                state: {
                    type: this.opts.historyIdentifier,
                    values: $form.serialize(),
                    scrollPos: $(window).scrollTop()
                },
                title: document.title,
                location: location,
                params: urlParams
            };
        }
    });
})(jQuery, window);
