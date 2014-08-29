;(function($, window, undefined) {
    "use strict";

    /**
     * Shopware product Compare Plugin.
     *
     * The plugin controlls the topbar-navigation dropdown menu für product comparisons.
     */
    $.plugin('productCompareMenu', {

        /** Your default options */
        defaults: {
            /** @string compareMenuSelector HTML class for the topbarnavigation menu wrapper */
            compareMenuSelector: '.entry--compare',

            /** @string startCompareSelector HTML class for the start compare button */
            startCompareSelector: '.btn--compare-start',

            /** @string deleteCompareSelector HTML class for the cancel compare button */
            deleteCompareSelector: '.btn--compare-delete',

            /** @string deleteCompareItemSelector HTML class for delete single product from comparison */
            deleteCompareItemSelector: '.btn--item-delete',

            /** @string modalSelector HTML class for modal window */
            modalSelector: '.js--modal',

            /** @string modalContentInnerSelector HTML class for modal inner content */
            modalContentInnerSelector: '.modal--compare'
        },

        /**
         * Initializes the plugin
         *
         * @returns {Plugin}
         */
        init: function () {
            var me = this;

            // on start compare
            me._on(me.opts.startCompareSelector, 'touchstart click', $.proxy(me.onStartCompare, me));

            // On cancel compare
            me._on(me.opts.deleteCompareSelector, 'touchstart click', $.proxy(me.onDeleteCompare, me));

            // On delete single product item from comparison
            me._on(me.opts.deleteCompareItemSelector, 'touchstart click', $.proxy(me.onDeleteItem, me));
        },

        /**
         * Opens the comparison modal by startCompareSelector.
         *
         * @public
         * @method onStartCompare
         */
        onStartCompare: function (event) {
            event.preventDefault();

            var me = this,
                startCompareBtn = me.$el.find(me.opts.startCompareSelector),
                modalUrl = startCompareBtn.attr('href'),
                modalTitle = startCompareBtn.attr('data-modal-title');

            $.loadingIndicator.open({
                closeOverlay: false,
                closeOnClick: false
            });

            // Load compare modal before opening modal box
            $.get(modalUrl, function(template) {

                $.loadingIndicator.close(function() {

                    $.modal.open(template, {
                        title: modalTitle,
                        sizing: 'content'
                    });

                    // Auto sizing for width
                    var templateWidth = $(me.opts.modalSelector).find(me.opts.modalContentInnerSelector).outerWidth();
                    $(me.opts.modalSelector).css('width', templateWidth);

                    picturefill();

                    // Resize every property row height to biggest height in cell
                    var maxRows = 0;
                    $(".entry--property").each(function () {
                        var row = $(this).attr('data-property-row');
                        if(row > maxRows) {
                            maxRows = row;
                        }
                    });

                    var maximumHeight,
                        rowSelector,
                        i = 1;

                    for( ; i <= maxRows; i++) {
                        rowSelector = '.entry--property[data-property-row="' + i + '"]';

                        maximumHeight = 0;
                        $(rowSelector).each(function () {
                            var rowHeight = $(this).height();

                            if (rowHeight > maximumHeight ) {
                                maximumHeight = rowHeight;
                            }
                        });

                        $(rowSelector).height(maximumHeight);
                    }
                });
            });
        },

        /**
         * Cancel the compare
         *
         * @method onDeleteCompare
         */
        onDeleteCompare: function (event) {
            var me = this,
                deleteCompareBtn = me.$el.find(me.opts.deleteCompareSelector),
                deleteUrl = deleteCompareBtn.attr('href');

            event.preventDefault();

            $.get(deleteUrl, function() {
                $(me.opts.compareMenuSelector).empty();
            });
        },

        /**
         * Delete one product item from comparison
         *
         * @method onDeleteItem
         */
        onDeleteItem: function (event) {
            event.preventDefault();

            var me = this,
                $deleteBtn = $(event.currentTarget),
                deleteUrl = $deleteBtn.attr('href');

            $(me.opts.compareMenuSelector).load(deleteUrl, function() {
                // Reload compare menu plugin
                $('*[data-product-compare-menu="true"]').productCompareMenu();
            });
        },

        /** Destroys the plugin */
        destroy: function () {
            this._destroy();
        }
    });
})(jQuery, window);