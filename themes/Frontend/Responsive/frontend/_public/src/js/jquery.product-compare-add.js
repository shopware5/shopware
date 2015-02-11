;(function($, window, undefined) {
    "use strict";

    /**
     * Shopware Article Compare Add Plugin.
     *
     * The plugin handles the compare add button on every product box.
     */
    $.plugin('productCompareAdd', {

        /** Your default options */
        defaults: {
            /** @string compareMenuSelector Listener Class for compare button */
            compareMenuSelector: '.entry--compare',

            /** @string hiddenCls Class which indicates that the element is hidden */
            hiddenCls: 'is--hidden'
        },

        /**
         * Initializes the plugin
         *
         * @returns {Plugin}
         */
        init: function () {
            var me = this;

            // On add article to compare button
            me._on(me.$el, 'click', $.proxy(me.onAddArticleCompare, me));
        },

        /**
         * onAddArticleCompare function for adding articles to
         * the compare menu, which will be refreshed by ajax request.
         *
         * @method onAddArticleCompare
         */
        onAddArticleCompare: function (event) {
            var me = this,
                addArticleUrl = me.$el.attr('href');

            event.preventDefault();

            if(!addArticleUrl) {
                return;
            }

            $.loadingIndicator.open({
                closeOverlay: false,
                closeOnClick: false
            });

            // Ajax request for adding article to compare list
            $.ajax({
                'url': addArticleUrl,
                'dataType': 'jsonp',
                'success': function(data) {
                    var compareMenu = $(me.opts.compareMenuSelector);

                    if (compareMenu.hasClass(me.opts.hiddenCls)) {
                        compareMenu.removeClass(me.opts.hiddenCls);
                    }

                    // Check if error thrown
                    if (data.indexOf('data-max-reached="true"') !== -1) {

                        $.loadingIndicator.close(function() {

                            $.modal.open(data, {
                                sizing: 'content'
                            });
                        });

                        return;
                    }

                    compareMenu.html(data);

                    // Reload compare menu plugin
                    $('*[data-product-compare-menu="true"]').productCompareMenu();

                    // Prevent too fast closing of loadingIndicator and overlay
                    $.loadingIndicator.close(function() {
                        $('html, body').animate({ scrollTop: ($('.top-bar').offset().top)}, 'slow');
                        $.overlay.close();
                    })
                }
            });
        },

        /** Destroys the plugin */
        destroy: function () {
            this._destroy();
        }
    });
})(jQuery, window);