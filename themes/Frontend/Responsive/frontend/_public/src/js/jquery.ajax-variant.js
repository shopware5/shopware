;(function($, window) {
    var emptyFn = function() {};

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
            productDetailsSelector: '.product--detail-upper'
        },

        /**
         * Initializes the plugin and sets up the necessary event handler
         */
        init: function() {
            var me = this,
                ie;

            me.applyDataAttributes();

            // Detecting IE version using feature detection (IE7+, browsers prior to IE7 are detected as 7)
            ie = (function (){
                if (window.ActiveXObject === undefined) return null;
                if (!document.querySelector) return 7;
                if (!document.addEventListener) return 8;
                if (!window.atob) return 9;
                if (!document.__proto__) return 10;
                return 11;
            })();

            if (ie && ie <= 9) {
                me.hasHistorySupport = false;
            }
            me.$el
                .on(me.getEventName('click'), '*[data-ajax-variants="true"]', $.proxy(me.onChange, me))
                .on(me.getEventName('change'), '*[data-ajax-select-variants="true"]', $.proxy(me.onChange, me))
                .on(me.getEventName('click'), '.reset--configuration', $.proxy(me.onChange, me));

            $(window).on("popstate", $.proxy(me.onPopState, me));
        },

        /**
         * Requests the HTML structure of the product detail page using AJAX and injects the returned
         * content into the page.
         *
         * @param {String} values
         * @param {Boolean} pushState
         */
        requestData: function(values, pushState) {
            var me = this,
                location;

            // `location.origin` isn't available in IE 11, so we have to create it
            if (!window.location.origin) {
                window.location.origin = window.location.protocol + "//" + window.location.hostname + (window.location.port ? ':' + window.location.port: '');
            }

            location = window.location.origin + window.location.pathname;

            $.loadingIndicator.open({
                closeOnClick: false,
                delay: 100
            });

            $.publish('plugin/swAjaxVariant/onBeforeRequestData', [ me, values, location ]);

            $.ajax({
                url: location + '?template=ajax',
                data: values || '',
                method: 'GET',
                success: function(response) {
                    var $response = $($.parseHTML(response)),
                        $productDetails,
                        orderNumber;

                    $productDetails = $response.find(me.opts.productDetailsSelector);
                    $(me.opts.productDetailsSelector).html($productDetails.html());

                    StateManager.addPlugin('select:not([data-no-fancy-select="true"])', 'swSelectboxReplacement')
                        .addPlugin('*[data-image-slider="true"]', 'swImageSlider', { touchControls: true })
                        .addPlugin('.product--image-zoom', 'swImageZoom', 'xl')
                        .addPlugin('*[data-image-gallery="true"]', 'swImageGallery')
                        .addPlugin('*[data-add-article="true"]', 'swAddArticle')
                        .addPlugin('*[data-modalbox="true"]', 'swModalbox');

                    $.loadingIndicator.close();

                    // Plugin developers should subscribe to this event to update their plugins accordingly
                    $.publish('plugin/swAjaxVariant/onRequestData', [ me, response, values, location ]);

                    if(pushState && me.hasHistorySupport) {
                        orderNumber = $('.entry--sku .entry--content').text();
                        window.history.pushState({
                            type: 'ajaxVariant',
                            values: values,
                            scrollPos: $(window).scrollTop()
                        }, document.title, location + '?number=' + orderNumber);
                    }
                }
            });
        },

        /**
         * Event handler method which will be fired when the user click the back button
         * in it's browser.
         *
         * @param {EventObject} event
         * @returns {boolean}
         */
        onPopState: function(event) {
            var me = this,
                state = event.originalEvent.state;

            if($('html').hasClass('is--safari') && me.initialPopState) {
                me.initialPopState = false;
                return;
            }

            if(!state || !state.hasOwnProperty('type') || state.type !== 'ajaxVariant') {
                me.requestData('', false);
                return false;
            }

            if(!state.values.length) {
                state = '';
            }

            // Prevents the scrolling to top in webkit based browsers
            if(state && state.scrollPos) {
                window.setTimeout(function() {
                    $(window).scrollTop(state.scrollPos);
                }, 10);
            }

            $.publish('plugin/swAjaxVariant/onPopState', [ me, state ]);

            me.requestData(state.values, false);
        },

        /**
         * Event handler which will fired when the user selects a variant in the storefront.
         * @param {EventObject} event
         */
        onChange: function(event) {
            var me = this,
                $target = $(event.target),
                $form = $target.parents('form'),
                values = $form.serialize();

            event.preventDefault();

            if (!me.hasHistorySupport) {
                $.loadingIndicator.open({
                    closeOnClick: false,
                    delay: 0
                });
                $form.submit();

                return false;
            }

            $.publish('plugin/swAjaxVariant/onChange', [ me, values, $target ]);

            me.requestData(values, true);
        }
    });
})(jQuery, window);