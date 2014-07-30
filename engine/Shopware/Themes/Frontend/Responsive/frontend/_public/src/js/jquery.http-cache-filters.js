;(function ($) {
    'use strict';

    /**
     * Shopware Http Cache Filters Plugin.
     *
     * Saves the current listing URL when clicking on a listing article in the session storage.
     * When the user is on a detail page, the listing URL will be set into the back button.
     *
     * @example
     *
     * $('body').httpCacheFilters();
     */
    $.plugin('httpCacheFilters', {
        init: function () {
            var me = this;

            me.storage = StorageManager.getSessionStorage();

            if (me.$el.hasClass('is--ctl-listing')) {
                var productSelectors = [
                    '.product--box .box--image',
                    '.product--box .product--title',
                    '.product--box .action--buynow',
                    '.product--box .action--more'
                ];

                me.storage.removeItem(me.getName() + '-detail');

                me._on($(productSelectors.join(', ')), 'click', $.proxy(me.onOpenDetailPage, me));
                me._on($('.listing--actions .filter--close-btn'), 'click', $.proxy(me.onResetFilterOptions, me));
            }

            if (me.$el.hasClass('is--ctl-detail')) {
                me.restoreState();
            }

            return true;
        },

        /**
         * Called when the user clicks on a listing item.
         * Sets the current listing URL.
         *
         * @public
         * @method onOpenDetailPage
         * @param {jQuery.Event} event
         */
        onOpenDetailPage: function (event) {
            var me = this;

            me.setListingUrl(window.location.href);
        },

        /**
         * Called when the user clicks on the filter close button.
         * Refreshes the listing URL.
         *
         * @public
         * @method onResetFilterOptions
         * @param {jQuery.Event} event
         */
        onResetFilterOptions: function (event) {
            var me = this,
                $this = $(event.currentTarget),
                url = $this.attr('href');

            me.setListingUrl(url);
        },

        /**
         * Sets the given (filtered) listing URL.
         *
         * @public
         * @method setListingUrl
         * @param {String} url
         */
        setListingUrl: function (url) {
            var me = this;

            me.storage.setItem(me.getName(), url + '');
        },

        /**
         * Sets the detail back button URL to the last saved listing URL.
         *
         * @public
         * @method restoreState
         */
        restoreState: function () {
            var me = this,
                name = me.getName(),
                detailName = name + '-detail',
                item = me.storage.getItem(name),
                detailItem;

            if (!item) {
                return;
            }

            detailItem = me.storage.getItem(detailName);

            if (!detailItem) {
                detailItem = window.location.href;
                me.storage.setItem(detailName, detailItem);
            }

            if (detailItem === window.location.href) {
                $('.content--breadcrumb .breadcrumb--button a').attr('href', item);
            } else {
                me.storage.removeItem(name);
                me.storage.removeItem(detailName);
            }
        }
    });
}(jQuery));