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
                $el = me.$el;
            me.url = $el.attr('data-src');
            me.hasError = $el.attr('data-has-error');

            if (!me.url || !me.url.length) {
                return;
            }

            if (typeof me.hasError !== 'undefined') {
                window.setTimeout($.proxy(me.sendRequest, me), 1000);
            }

            me.$form = $el.closest('form');
            me.$formInputs = me.$form.find(':input:not([name="__csrf_token"], select)');
            me._on(me.$formInputs, 'focus', $.proxy(me.onInputFocus, me));
        },

        /**
         * Triggers _sendRequest and deactivates the focus listeners from input elements
         *
         * @private
         * @method onInputFocus
         */
        onInputFocus: function () {
            var me = this;

            me._off(me.$formInputs, 'focus');
            me.sendRequest();
        },

        /**
         * Sends an ajax request to the passed url and sets the result into the plugin's element.
         *
         * @public
         * @method _sendRequest
         */
        sendRequest: function () {
            var me = this,
                $el = me.$el;

            $.ajax({
                url: me.url,
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
