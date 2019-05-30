;(function ($) {
    'use strict';

    /**
     * Shopware Article Compare Add Plugin.
     *
     * The plugin handles the compare add button on every product box.
     */
    $.plugin('swProductCompareAdd', {

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
            this.$el.on(this.getEventName('click'), '*[data-product-compare-add="true"]', $.proxy(this.onAddArticleCompare, this));

            $.publish('plugin/swProductCompareAdd/onRegisterEvents', [this]);
        },

        /**
         * onAddArticleCompare function for adding articles to
         * the compare menu, which will be refreshed by ajax request.
         *
         * @method onAddArticleCompare
         */
        onAddArticleCompare: function (event) {
            var me = this,
                $target = $(event.target),
                $form = $target.closest('form'),
                addArticleUrl;

            event.preventDefault();

            // @deprecated: Don't use anchors for action links. Use forms with method="post" instead.
            if ($target.attr('href')) {
                addArticleUrl = $target.attr('href');
            } else {
                addArticleUrl = $form.attr('action');
            }

            if (!addArticleUrl) {
                return;
            }

            $.overlay.open({
                closeOnClick: false
            });

            $.loadingIndicator.open({
                openOverlay: false
            });

            $.publish('plugin/swProductCompareAdd/onAddArticleCompareBefore', [me, event]);

            // Ajax request for adding article to compare list
            $.ajax({
                url: addArticleUrl,
                dataType: 'html',
                method: 'POST',
                success: function (data) {
                    var compareMenu = $(me.opts.compareMenuSelector),
                        modal;

                    if (compareMenu.hasClass(me.opts.hiddenCls)) {
                        compareMenu.removeClass(me.opts.hiddenCls);
                    }

                    // Check if error thrown
                    if (data.indexOf('data-max-reached="true"') !== -1) {
                        $.loadingIndicator.close(function () {
                            modal = $.modal.open(data, {
                                sizing: 'content',
                                overlay: false
                            });

                            // Hack necessary to close the overlay upon closing the modal.
                            // The overlay may not be opened by the modal though, since we already have an overlay opened here
                            modal.options.overlay = true;
                        });
                    } else {
                        compareMenu.html(data);

                        // Reload compare menu plugin
                        $('*[data-product-compare-menu="true"]').swProductCompareMenu();

                        // Prevent too fast closing of loadingIndicator and overlay
                        $.loadingIndicator.close(function () {
                            $('html, body').animate({
                                scrollTop: ($('.top-bar').offset().top)
                            }, 'slow');

                            $.overlay.close();
                        });
                    }

                    $.publish('plugin/swProductCompareAdd/onAddArticleCompareSuccess', [me, event, data, compareMenu]);
                }
            });

            $.publish('plugin/swProductCompareAdd/onAddArticleCompare', [me, event]);
        },

        /** Destroys the plugin */
        destroy: function () {
            this.$el.off(this.getEventName('click'));

            this._destroy();
        }
    });
})(jQuery);
