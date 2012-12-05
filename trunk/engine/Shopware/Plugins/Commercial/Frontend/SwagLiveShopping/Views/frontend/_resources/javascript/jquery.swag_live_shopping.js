;(function($, window, document, undefined) {
    "use strict";

    function Server(timeNow, timerCallback, scope) {
        this.timeNow = timeNow * 1000;
        this.timerCallback = timerCallback;
        this.callbackScope = scope;
        this.init();
    }

    Server.prototype = {
        timeNow: undefined,
        interval: undefined,
        timerCallback: undefined,

        init: function() {
            var me = this;
            this.interval = window.setInterval(function() {
                me.setTime();
            }, 1000);
        },

        setTime: function() {
            this.timeNow += 1000;
            if ($.isFunction(this.timerCallback)) {
                this.timerCallback.apply(this.callbackScope, [ this.timeNow ]);
            }
        },
        shutdown: function() {
            window.clearInterval(this.interval);
            this.interval = undefined;
        }
    };
    window.timeRunner = Server;

})(jQuery, window, document);

;(function ( $, window, document, undefined ) {

    // Create the defaults once
    var pluginName = 'swagLiveShopping',
        defaults = {
            refreshData: true,
            propertyName: "value"
        };

    // The actual plugin constructor
    function LiveShopping( element, options ) {

        this.element = element;
        this.$element = $(this.element);
        this.validTo = undefined;
        this.parent = $('.ctl_detail');

        this.options = $.extend( {}, defaults, options) ;
        this._defaults = defaults;
        this._name = pluginName;

        this.dayElement = this.$element.find('.counter_top .days');
        this.hourElement = this.$element.find('.counter_top .hours');
        this.minuteElement = this.$element.find('.counter_top .minutes');
        this.secondElement = this.$element.find('.counter_top .seconds');

        this.elapseElement = this.$element.find('.elapse_inner');

        this.iconElement = this.$element.find('.liveshopping_icon img');
        this.startPriceElement = this.$element.find('.liveshopping_price .start_price');
        this.currentPriceElement = this.$element.find('.liveshopping_price .current_price');
        this.discountElement = this.$element.find('.box_right strong');

        this.stockWrapperElement = this.$element.find('.stock_wrapper');
        this.stockLineElement = this.$element.find('.column.stock');

        this.stockTipElement = this.$element.find('.stock_tip.line');

        this.init();
    }

    LiveShopping.prototype.init = function () {
        var me = this;

        me.refreshData();
        me.initStock();

        window.timeNow = window.timeNow || undefined;

        me._timeRunner = new window.timeRunner(
            window.timeNow,
            me.timerCallback,
            me
        );
    };


    LiveShopping.prototype.timerCallback = function(timeNow) {
        var me = this;
        var date = new Date(timeNow);
        var diff = me.getTimestampDiff(me.getValidTo().getTime(), date.getTime());
        me.refreshDates(diff);
    };


    LiveShopping.prototype.refreshDates = function(diff) {
        var me = this,
            percentage = diff.s / 60 * 100,
            liveShoppingType;

        me.dayElement.text(diff.d);
        me.hourElement.text(diff.h);
        me.minuteElement.text(diff.m);
        me.secondElement.text(diff.s);
        me.elapseElement.css('width', percentage + '%');

        if(!me.parent.length) {
            return false;
        }
        liveShoppingType = me.getLiveShoppingType();

        if (liveShoppingType == 1 && diff.s % 20 == 0) {
            me.animateHeart();
            me.refreshData();
        } else if (diff.s == 0) {
            me.animateArrow();
        }
    };

    /**
     * Plugin function for the heart animation.
     *
     * This function is used for "standard" live shopping articles.
     * It simulate a heart beat for the clock item within the live shopping
     * container.
     */
    LiveShopping.prototype.animateHeart = function() {
        var me = this,
            parent = me.iconElement.parents('.liveshopping_icon'),
            easing = 'easeOutBack',
            firstStep = 8,
            secondStep = 10,
            thirdStep = 11,
            fourthStep = 9,
            opacity = .65;

        me.iconElement.animate({
            width: '+=' + firstStep,
            height: '+=' + firstStep,
            opacity: opacity
        }, 175, easing, function() {
            me.iconElement.animate({
                width: '-=' + secondStep,
                height: '-=' + secondStep,
                opacity: .85
            }, 75, 'swing', function() {
                me.iconElement.animate({
                    width: '+=' + thirdStep,
                    height: '+=' + thirdStep,
                    opacity: opacity
                }, 175, easing, function() {
                    me.iconElement.animate({
                        width: '-=' + fourthStep,
                        height: '-=' + fourthStep,
                        opacity: 1
                    }, 75, 'swing');
                });
            });
        });

        parent.animate({
            left: '-=' + (firstStep / 2),
            top: '-=' + (firstStep / 2)
        }, 175, easing, function() {
            parent.animate({
                left: '+=' + (secondStep / 2),
                top: '+=' + + (secondStep / 2)
            }, 75, 'swing', function() {
                parent.animate({
                    left: '-=' + (thirdStep / 2),
                    top: '-=' + (thirdStep / 2)
                }, 175, easing, function() {
                    parent.animate({
                        left: '+=' + (fourthStep / 2),
                        top: '+=' + (fourthStep / 2)
                    }, 75, 'swing');
                });
            });
        });
    };

    /**
     * Plugin function to refresh the live shopping data.
     *
     * This function is used for "discount/surcharge per minute" live shopping
     * articles. It sends an ajax request to the data url which is placed
     * in a hidden input field within the live shopping container.
     *
     * @return boolean
     */
    LiveShopping.prototype.refreshData = function(options) {
        var me = this;

        if (!me.parent.length) {
            return false;
        }
        jQuery.ajax({
            url: me.getDataUrl(),
            dataType: "json",
            type: 'post',
            success: function(record, success) {
                me.currentPriceElement.fadeOut('fast');

                me.currentPriceElement.html(
                    me.formatCurrency(record.data.currentPrice) + me.getStar()
                );

                window.setTimeout(function() {
                    me.currentPriceElement.fadeIn('slow');
                }, 150);


                me.stockWrapperElement.find('.stock_tip_inner strong').text(
                    record.data.quantity
                );

                var parent = me.$element.parents('.liveshopping_wrapper.listing-1col');
                if (parent.length === 0) {
                    var tipWidth = me.stockTipElement.width();
                    var lineWidth = me.stockLineElement.width();

                    me.stockWrapperElement.css({
                        'width': lineWidth,
                        'left': -(tipWidth / 2)
                    });

                    var stockLeftPercentage = record.data.quantity / record.data.sells * 100;
                    me.stockTipElement.animate({
                        left: stockLeftPercentage + '%'
                    }, 300);
                }

                if (options !== undefined && $.isFunction(options.callback)) {
                    options.callback(record, data);
                }
            }
        });

        return true;
    };

    LiveShopping.prototype.initStock = function() {
        var me = this;

        var parent = me.$element.parents('.liveshopping_wrapper.listing-1col');
        if (parent.length > 0) {
            return;
        }

        var tipWidth = me.stockTipElement.width();
        var lineWidth = me.stockLineElement.width();

        me.stockWrapperElement.css({
            'width': lineWidth,
            'left': -(tipWidth / 2)
        });

        var quantity = me.$element.find('.live_shopping_initial_quantity').val();
        var sells = me.$element.find('.live_shopping_initial_sells').val();
        var stockLeftPercentage = quantity / sells * 100;

        me.stockTipElement.animate({
            left: stockLeftPercentage + '%'
        }, 300);
    };

    LiveShopping.prototype.formatCurrency = function (value) {
        var me = this;
        var currencyFormat = me.$element.find('.currency-helper').text();

        value = Math.round(value * 100) / 100;
        value = value.toFixed(2);
        value = currencyFormat.replace('0,00', value);
        value = value.replace('.', ',');
        return value;
    };

    /**
     * Plugin function to animate the live shopping arrow.
     *
     * This function is used for "discount/surcharge per minute" live shopping
     * articles. It resize the arrow icon which displayed in the left box
     * of the live shopping container.
     */
    LiveShopping.prototype.animateArrow = function() {
        var me = this,
            height = 50,
            width = 50,
            parent = me.iconElement.parents('.liveshopping_icon');

        me.iconElement.animate({
            height: '+=' + height,
            width: '+=' + width,
            opacity: 0
        }, 500, function() {
            me.iconElement.hide();
            me.refreshData({
                callback: function() {
                    me.iconElement.css('opacity', 1);
                    me.iconElement.css('width', '-=' + width);
                    me.iconElement.css('height', '-=' + height);
                    parent.css('left', '+=' + (width / 2));
                    parent.css('top', '+=' + (height / 2));
                    me.iconElement.show();
                }
            });
        });

        parent.animate({
            left: '-=' + (width / 2),
            top: '-=' + (height / 2)
        }, 500);
    };


    //Gets the difference between two timestamps
    //which is used by the live shopping module
    LiveShopping.prototype.getTimestampDiff = function(d1, d2) {
        var me = this;
        if (d1 < d2) {
            return false;
        }
        var d = Math.floor((d1 - d2) / (24 * 60 * 60 * 1000));
        var h = Math.floor(((d1 - d2) - (d * 24 * 60 * 60 * 1000)) / (60 * 60 * 1000));
        var m = Math.floor(((d1 - d2) - (d * 24 * 60 * 60 * 1000) - (h * 60 * 60 * 1000)) / (60 * 1000));
        var s = Math.floor(((d1 - d2) - (d * 24 * 60 * 60 * 1000) - (h * 60 * 60 * 1000) - (m * 60 * 1000)) / 1000);

        return {
            'd': me.formatNumber(d),
            'h': me.formatNumber(h),
            'm': me.formatNumber(m),
            's': me.formatNumber(s)
        };
    };

    LiveShopping.prototype.formatNumber = function(number) {
        var tmp = number + '';
        if (tmp.length === 1) {
            return '0' + number;
        } else {
            return number;
        }
    };

    LiveShopping.prototype.getLiveShoppingType = function() {
        var me = this;
        return me.$element.find('.live_shopping_type').val();
    };

    LiveShopping.prototype.getLiveShoppingId = function() {
        var me = this;
        return me.$element.find('.live_shopping_id').val();
    };

    LiveShopping.prototype.getStar = function() {
        var me = this;
        return me.$element.find('.star').val();
    };

    LiveShopping.prototype.getDataUrl = function() {
        var me = this;
        return me.$element.find('.live_shopping_data_url').val();
    };

    LiveShopping.prototype.getValidTo = function() {
        var me = this;
        me.validTo = me.$element.find('.valid_to').val();
        me.validTo = new Date(me.validTo * 1000);
        return me.validTo;
    };

    // A really lightweight plugin wrapper around the constructor, 
    // preventing against multiple instantiations
    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName,
                    new LiveShopping( this, options ));
            }
        });
    };
})( jQuery, window, document );


;(function($, window, undefined) {
    $(document).ready(function() {
        $('.liveshopping.detail').swagLiveShopping();
        $('.liveshopping.listing').swagLiveShopping();
    });
})(jQuery, window);
