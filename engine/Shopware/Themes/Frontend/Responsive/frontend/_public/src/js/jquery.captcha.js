;(function($, window) {
    "use strict";

    $.plugin('captcha', {
        /**
         * Initializes the plugin and adds the necessary
         * event listener for the auto submitting.
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

        sendRequest: function (url) {
            var me = this,
                $el = me.$el;

            $.ajax({
                url: url,
                cache: false,
                success: $el.html.bind($el)
            });
        }
    });
})(jQuery, window);