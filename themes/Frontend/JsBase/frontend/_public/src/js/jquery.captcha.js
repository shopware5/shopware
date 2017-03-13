;(function($, window) {
    'use strict';

    /**
     * Shopware Captcha Plugin.
     *
     * @example
     *
     * Call the plugin on a node with a "data-src" attribute.
     * This attribute should provide the url for retrieving the captcha.
     *
     * HTML:
     *
     * <div data-src="CAPTCHA_REFRESH_URL" data-captcha="true"></div>
     *
     * JS:
     *
     * $('*[data-captcha="true"]').swCaptcha();
     *
     */
    $.plugin('swCaptcha', {

        /**
         * Default plugin initialisation function.
         * Registers all needed event listeners and sends a request to load the captcha image.
         *
         * @public
         * @method init
         */
        init: function () {
            var me = this,
                $el = me.$el,
                url = $el.attr('data-src'),
                $window = $(window);

            if (!url || !url.length) {
                return;
            }

            // fix bfcache from caching the captcha/whole rendered page
            me._on($window, 'unload', function () {});
            me._on($window, 'pageshow', function (event) {
                if (event.originalEvent.persisted) {
                    me.sendRequest(url);
                }
            });

            me.sendRequest(url);
        },

        /**
         * Sends an ajax request to the passed url and sets the result into the plugin's element.
         *
         * @public
         * @method _sendRequest
         * @param {String} url
         */
        sendRequest: function (url) {
            var me = this,
                $el = me.$el;

            $.ajax({
                url: url,
                cache: false,
                success: function (response) {
                    $el.html(response);

                    $.publish('plugin/swCaptcha/onSendRequestSuccess', [ me ]);
                }
            });

            $.publish('plugin/swCaptcha/onSendRequest', [ me ]);
        }
    });
})(jQuery, window);
