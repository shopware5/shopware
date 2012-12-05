;(function($, window, document, undefined) {
    "use strict";

    $(document).ready(function() {
        $('.abo-commerce-container').aboCommerce();
    });
})(jQuery, window, document);

;(function($, window, document, undefined) {
    "use strict";

    /**
     * $.fn.idle
     *
     * This plugin transforms the lame setTimeout()
     * into the beloved jQuery syntax.
     *
     * @param {Integer} time
     * @return {Object}
     */
    $.fn.idle = function(time) {

        var obj = $(this);
        obj.queue(function() {
            window.setTimeout(function() {
                obj.dequeue();
            }, time);
        });

        /** Return this for jQuery's chaining support */
        return this;
    };

    // Default plugin options
    var pluginName = 'aboCommerce',
        defaults = {
            popUpOffsetTop: 32,
            fadeSpeed: 'fast',
            activeCls: 'active',
            hiddenCls: 'hidden'
    };

    /**
     * Private helper method which will convert
     * a string to a floating number
     *
     * @param {String|Number} str - String which needs to be converted
     * @return {Number} - Float based on the passed string
     */
    var toFixed = function(str, defaultVal) {
        defaultVal = defaultVal || 1;
        str = str.replace(',', '.');

        if(isFinite(str)) {
            str = (1 * str);
        }
        return !isNaN(str) ? str : defaultVal;
    };

    /**
     * Plugin constructor
     *
     * @param {Object} element - Selected element
     * @param {Object} options - User defined plugin configuration
     * @constructor
     */
    function AboCommerce( element, options ) {
        var me = this;

        me.$el = $(element);
        me.options = $.extend( {}, defaults, options) ;

        me._defaults = defaults;
        me._name = pluginName;

        me.$deliveryContainer = $('.delivery-interval-container');
        me.$quantityBox = $('#detailCartButton');

        // Get abo prices
        me._prices = $.parseJSON($('.abocommerce-data').html());
        me._prices = me._prices.prices;

        // Get block prices
        me._blockPrices = $.parseJSON($('.block-prices-data').html());

        me.init();
    }

    /**
     * Override the prototype to implement the plugin logic
     * @type {Object}
     */
    AboCommerce.prototype = {

        /**
         * Status of the abo commerce component
         * @string
         */
        _deliveryState: 'open',

        /**
         * Status of the orderlist popup
         * @string
         */
        _orderlistPopupState: 'closed',

        /**
         * Status of the pricelist popup
         * @string
         */
        _pricelistPopupState: 'closed',

        /**
         * Reference to the delivery container jQuery object
         *
         * @default undefined
         * @object
         */
        $deliveryContainer: undefined,

        /**
         * Refernce to the quantity select box jQuery object
         *
         * @default undefined
         * @object
         */
        $quantityBox: undefined,

        /**
         * References to the prices for abo commerce
         *
         * @private
         * @default undefined
         * @object
         */
        _prices: undefined,

        /**
         * Reference to the block prices of the article
         *
         * @private
         * @defautl undefined
         * @object
         */
        _blockPrices: undefined,

        /**
         * Reference to the hidden delivery interval field
         *
         * @private
         * @default undefined
         * @object
         */
        _deliveryInterval: undefined,

        /**
         * Reference to the hidden duration interval field
         *
         * @private
         * @default undefined
         * @object
         */
        _durationInterval: undefined,


        /**
         * Initialize the plugin
         *
         * @return void
         */
        init: function() {
            var me = this,
                aboSelection = me.$el.find('.selection'),
                preSelected = aboSelection.val();

            me[(preSelected === 'single') ? 'hide' : 'show']();

            // Event listener to show / hide the abo commerce components
            aboSelection.bind('change', function() {
                var selected = $(this).val();

                if(selected === preSelected) { return false; }
                me[(selected === 'single') ? 'hide' : 'show']();

                preSelected = selected;
            });

            // Initialize the orderlist popup
            me.initOrderlistPopup();
            me.initPricePopup();

            // Event listener to position the popup
            $(window).bind('resize', { self: me }, me.onWindowResize).trigger('resize');

            // Event listener to recalculate the price based on the user's selection
            me.$el.find('.duration-interval').bind('change', function() {
                var $this = $(this),
                    result = 1 * ~~($this.val());

                me.setDuration(result);
                me.refreshPriceDisplay(result);
            });

            // Event listener which update the price - neccessary for block prices
            $('.abo-delivery .sQuantity').change(function() {
                var duration = (1* me.$el.find('.duration-interval').val());
                me.refreshPriceDisplay(duration);
            });

            // Event listener to update the delivery interval
            me.$el.find('.delivery-interval').bind('change', function() {
                var $this = $(this),
                    result = 1 * ~~($this.val());

                me.setDelivery(result);
            });
        },

        /**
         * Initializes the orderlist popup, binding the
         * neccessary events and position the popup on startup
         *
         * @return {Boolean}
         */
        initOrderlistPopup: function() {
            var me = this;

            me.$orderlistPopup = $('.orderlist-popup').hide();
            me.$orderlistLink = $('.open-orderlist-popup');

            // Event listener to show the popup
            me.$orderlistLink.bind('click', { self: me }, me.onOpenOrderlistPopup);

            // Event listener which saves the article to the selected orderlist
            me.$orderlistPopup.find('.btn-add-to-orderlist').bind('click', { self: me }, me.onSaveToOrderlist);

            return true;
        },

        /**
         * Shows the orderlist popup
         *
         * @return {Boolean}
         */
        showOrderlistPopup: function() {
            var me = this;
            me.$orderlistPopup.fadeIn(me.options.fadeSpeed);
            me._orderlistPopupState = 'open';

            return true;
        },

        /**
         * Hides the orderlist popup
         *
         * @return {Boolean}
         */
        hideOrderlistPopup: function() {
            var me = this;
            me.$orderlistPopup.fadeOut(me.options.fadeSpeed);
            me._orderlistPopupState = 'closed';

            return true;
        },

        /**
         * Toggles the visiblity of the orderlist popup based
         * on the "_orderlistPopupState"
         *
         * @return {Boolean}
         */
        toggleOrderlistPopup: function() {
            var me = this;
            return me[(me._orderlistPopupState === 'open') ? 'hideOrderlistPopup' : 'showOrderlistPopup']();
        },

        /**
         * Initializes the orderlist popup, binding the
         * neccessary events and position the popup on startup
         *
         * @return {Boolean}
         */
        initPricePopup: function() {
            var me = this;

            me.$pricelistPopup = $('.price-separation-popup').hide();
            me.$pricelistLink = $('.link-open-price-separation');

            me.$pricelistLink.bind('click', function() {
                me.togglePricelistPopup();
            });

            return true;
        },

        /**
         * Shows the pricelist popup
         *
         * @return {Boolean}
         */
        showPricelistPopup: function() {
            var me = this;
            me.$pricelistPopup.fadeIn(me.options.fadeSpeed);
            me._pricelistPopupState = 'open';

            var timeout = window.setTimeout(function() {
                window.clearTimeout(timeout);
                timeout = undefined;

                $('body').bind('click.pricelist', function() {
                    me.hidePricelistPopup();
                });
            }, 200);



            return true;
        },

        /**
         * Hides the pricelist popup
         *
         * @return {Boolean}
         */
        hidePricelistPopup: function() {
            var me = this;
            me.$pricelistPopup.fadeOut(me.options.fadeSpeed);
            me._pricelistPopupState = 'closed';

            $('body').unbind('click.pricelist');

            return true;
        },

        /**
         * Toggles the visiblity of the pricelist popup based
         * on the "_pricelistPopupState"
         *
         * @return {Boolean}
         */
        togglePricelistPopup: function() {
            var me = this;
            return me[(me._pricelistPopupState === 'open') ? 'hidePricelistPopup' : 'showPricelistPopup']();
        },

        /**
         * Event listener method which will be fired when the user
         * wants to place an article on a specific order list.
         *
         * Sends an AJAX request and closes the orderlist popup.
         *
         * @param {Object} event - jQuery event object
         * @return {Boolean}
         */
        onSaveToOrderlist: function(event) {
            event.preventDefault();

            var me = event.data.self,
                orderNumber = me.$orderlistLink.attr('data-ordernumber'),
                orderListId = me.$orderlistPopup.find('.orderlist-select :selected').val(),
                $form = me.$orderlistPopup.find('form');

            if(!orderNumber) { return false; }

            var $overlay = $('<div>', {
                'class': 'orderlist-overlay'
            }).hide().prependTo(me.$orderlistPopup).fadeIn(me.options.fadeSpeed);

            $.ajax({
                url: $form.attr('action'),
                type: $form.attr('method'),
                data: {
                    sOrdernumber: orderNumber,
                    sOrderlist: orderListId
                },
                success: function() {
                    var timeout = window.setTimeout(function() {
                        $overlay.hide().remove();
                        me.hideOrderlistPopup();

                        window.clearTimeout(timeout);
                        timeout = undefined;
                    }, 500);
                }
            });
        },

        /**
         * Event listener method which will be fired when the user
         * resizes it's browser window.
         *
         * @event resize
         * @return {Boolean}
         */
        onWindowResize: function(event) {
            var me = event.data.self,
                orderlistOffset = me.$orderlistLink.offset(),
                pricelistOffset = me.$pricelistLink.offset();

            if(orderlistOffset) {
                orderlistOffset.top = orderlistOffset.top + me.options.popUpOffsetTop;
                me.$orderlistPopup.css(orderlistOffset);
            }

            if(pricelistOffset) {
                pricelistOffset.left += me.$pricelistLink.width() + 35;
                pricelistOffset.top -= 40;
                me.$pricelistPopup.css(pricelistOffset);
            }
        },

        /**
         * Event listener method which will be fired when the user
         * clicks on the "Add article to orderlist" link on the
         * detail page.
         *
         * @event click
         * @param {Object} event - jQuery event object
         * @return {Boolean}
         */
        onOpenOrderlistPopup: function(event) {
            event.preventDefault();

            var me = event.data.self,
                $this = $(this),
                orderNumber = $this.attr('data-ordernumber');

            if(!orderNumber) { return false; }

            $this[(!$this.hasClass(me.options.activeCls) ? 'addClass' : 'removeClass')](me.options.activeCls);
            me.toggleOrderlistPopup();
            return true;
        },

        /**
         * Hides the abo commerce components on the detail page
         *
         * @return void
         */
        hide: function() {
            var me = this;

            me.$deliveryContainer.addClass(me.options.hiddenCls);
            me.$quantityBox.find('.abo-delivery').addClass(me.options.hiddenCls).find('.sQuantity').attr('disabled', 'disabled');
            me.$quantityBox.find('.single-delivery').removeClass(me.options.hiddenCls).removeAttr('disabled');

            me._removeHiddenFields();
            me._deliveryState = 'closed';
        },

        /**
         * Shows the abo commerce component on the detail page
         *
         * @return void
         */
        show: function() {
            var me = this;

            me.$deliveryContainer.removeClass(me.options.hiddenCls);
            me.$quantityBox.find('.abo-delivery').removeClass(me.options.hiddenCls).find('.sQuantity').removeAttr('disabled');
            me.$quantityBox.find('.single-delivery').addClass(me.options.hiddenCls).find('.sQuantity').attr('disabled', 'disabled');

            me._injectHiddenFields();
            me._deliveryState = 'open';
        },

        /**
         * Toggles the abo commerce component on the detail page
         *
         * @return {Boolean}
         */
        toggle: function() {
            var me = this;
            return me[(me._deliveryState === 'open') ? 'hide' : 'show']();
        },

        /**
         * Refreshes the price and the percent discount display
         * in the abo commerce component.
         *
         * @param {Integer} duration - Selected abo duration
         * @return void
         */
        refreshPriceDisplay: function(duration) {
            var me = this,
                price = me._getPrice(duration),
                container = me.$el.find('.abo-pseudo-price'),
                percent = price.descountPercentage;

            container.find('span.percent').html($.number_format(percent, 2, ',', '.'));
            container.find('span.price').html($.number_format(price.discountPrice, 2, ',', '.') + '&nbsp;&euro;');
        },

        /**
         * Set the delivery interval into the correspondending hidden field
         *
         * @param {Integer} delivery - User selected delivery interval
         * @return {Boolean}
         */
        setDelivery: function(delivery) {
            var me = this;

            if(!delivery || !me._deliveryInterval) { return false; }

            me._deliveryInterval.val(delivery);

            return true;
        },

        /**
         * Set the duration interval into the correspondending hidden field
         *
         * @param {Integer} duration - User selected duration interval
         * @return {Boolean}
         */
        setDuration: function(duration) {
            var me = this;

            if(!duration || !me._durationInterval) { return false; }

            me._durationInterval.val(duration);

            return true;
        },

        /**
         * Terminates the price for the selected duration
         *
         * @param {Integer} duration - User selected duration
         * @return {Object} - terminated price array
         * @private
         */
        _getPrice: function(duration) {
            var me = this, result, blockPrice;

            // Loop through the price array to terminate the price
            // or percent discount
            for(var i in me._prices) {
                var tmpPrice = me._prices[i];

                if(duration >= tmpPrice.duration) {
                    result = tmpPrice;
                }
            }

            // Check if we're dealing with block prices
            if(me._blockPrices.length) {
                var quantitiy = (1 * $('.abo-delivery .sQuantity :selected').val());

                for(var j in me._blockPrices) {
                    var tmpBlockPrice = me._blockPrices[j];

                    if(quantitiy >= (1 * tmpBlockPrice.from)) {
                        blockPrice = tmpBlockPrice;
                    }
                }

                var price = toFixed(blockPrice.price) - result.discountAbsolute;
                result.discountPrice = price;
            }
            return result;
        },

        /**
         * Injects hidden fields for the delivery and duration interval
         * of the abo.
         *
         * Keep in mind that the method reads the visible fields and places
         * the value of them into the correspondending hidden field.
         *
         * @return {Boolean}
         * @private
         */
        _injectHiddenFields: function() {
            var me = this, basketForm = $('form.basketform');

            if(!me._deliveryState)  { return false; }

            me._durationInterval = $('<input/>', {
                'type': 'hidden',
                'name': 'sDurationInterval',
                'value': me.$el.find('.duration-interval :selected').val()
            }).prependTo(basketForm);

            me._deliveryInterval = $('<input/>', {
                'type': 'hidden',
                'name': 'sDeliveryInterval',
                'value': me.$el.find('.delivery-interval :selected').val()
            }).prependTo(basketForm);

            return true;
        },

        /**
         * Removes the hidden field for the delivery and duration interval.
         *
         * @return {Boolean}
         * @private
         */
        _removeHiddenFields: function() {
            var me = this;

            if(!me._deliveryState)  { return false; }

            if(me._durationInterval) {
                me._durationInterval.remove();
                me._durationInterval = undefined;
            }

            if(me._deliveryInterval) {
                me._deliveryInterval.remove();
                me._deliveryInterval = undefined;
            }

            return true;
        }
    };

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName,
                new AboCommerce( this, options ));
            }
        });
    };

})(jQuery, window, document);