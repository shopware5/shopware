;(function ($, window) {
    'use strict';

    $.plugin('swPriceGroupCartItemsQuantity', {
        defaults: {
            getPriceGroupCartItemsQuantityUrl: null,
            priceGroupId: null,
        },

        /**
         * Initializes the plugin
         *
         * @returns {Plugin}
         */
        init: function () {
            this.applyDataAttributes();
            this.registerEvents();
            this.onAddArticle();
        },

        /**
         * Registers all necessary event listeners.
         */
        registerEvents: function() {
            $.subscribe(this.getEventName('plugin/swAddArticle/onAddArticle'), $.proxy(this.onAddArticle, this));
        },

        /**
         * Updates the shown PriceGroupCartItemsQuantity after adding an article to the basket.
         */
        onAddArticle: function () {
            var url = this.opts.getPriceGroupCartItemsQuantityUrl;
            var element = this.$el[0];
            var ajaxData = {
                priceGroupId: this.opts.priceGroupId,
            };
            $.ajax({
                data: ajaxData,
                dataType: 'json',
                method: 'GET',
                url: url,
                cache: false,
                success: function (result) {
                    var oldQuantity = element.innerText;
                    var newQuantity = result.priceGroupCartItemsQuantity;
                    var eventData = [
                        this,
                        newQuantity,
                        oldQuantity,
                        element,
                        result,
                        ajaxData
                    ];
                    $.publish('plugin/swPriceGroupCartItemsQuantity/onBeforeUpdatePriceGroupCartItemsQuantity', eventData);
                    element.innerText = newQuantity;
                    $.publish('plugin/swPriceGroupCartItemsQuantity/onAfterUpdatePriceGroupCartItemsQuantity', eventData);
                }.bind(this),
            });
        }
    });
})(jQuery, window);
