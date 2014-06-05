;(function($, window, document, undefined) {
    "use strict";

    var pluginName = 'quantityField',
        isTouch = (('ontouchstart' in window) || (navigator.msMaxTouchPoints > 0)),
        clickEvt = (isTouch ? (window.navigator.msPointerEnabled ? 'MSPointerDown': 'touchstart') : 'click'),
        defaults = {
            /** @string activeCls Class which will be added when the drop down was triggered */
            activeCls: 'is--active',
            wrapCls: 'js--quantity-field',
            plusBtnCls: 'js--quantity-button-plus btn btn--secondary',
            minusBtnCls: 'js--quantity-button-minus btn btn--secondary',
            buttonCls: 'js--quantity-button',
            plusBtnText: '+',
            minusBtnText: '-'
        };

    /**
     * Plugin constructor which merges the default settings with the user settings
     * and parses the `data`-attributes of the incoming `element`.
     *
     * @param {HTMLElement} element - Element which should be used in the plugin
     * @param {Object} userOpts - User settings for the plugin
     * @returns {Void}
     * @constructor
     */
    function Plugin(element, userOpts) {
        var me = this;

        me.$el = $(element);
        me.opts = $.extend({}, defaults, userOpts);

        me._defaults = defaults;
        me._name = pluginName;

        me.init();
    }

    /**
     * Initializes the plugin, sets up event listeners and adds the necessary
     * classes to get the plugin up and running.
     *
     * @returns {Void}
     */
    Plugin.prototype.init = function() {
        var me = this;

        me._minSize = parseInt(me.$el.attr('min'), 10);
        me._maxSize = parseInt(me.$el.attr('max'), 10);
        me._stepSize = parseInt(me.$el.attr('data-step'), 10);

        // Set the field ```readonly```
        me.$el.attr('readonly', 'readonly');

        // Wrap the field with a new element
        me.$wrap = me.getWrapElement();
        me.$el.wrap(me.$wrap);

        me.$buttonContainer = me.getButtons();
        me.$buttonContainer.insertAfter(me.$el);
    };

    Plugin.prototype.getWrapElement = function() {
        return $('<div>', {
            'class': this.opts.wrapCls
        });
    };

    Plugin.prototype.getButtons = function() {
        var me = this,
            opts = me.opts,
            $container;

        $container = $('<div>', {
            'class': opts.buttonCls
        });

        me.$plusButton = $('<a>', {
            'class': opts.plusBtnCls,
            'href': '#',
            'html': opts.plusBtnText
        }).on('click.' + pluginName, function(e) {
            me.increase(e);
        }).appendTo($container)

        me.$minusButton = $('<a>', {
            'class': opts.minusBtnCls,
            'href': '#',
            'html': opts.minusBtnText
        }).on('click.' + pluginName, function(e) {
            me.decrease(e);
        }).appendTo($container);

        return $container;
    };

    Plugin.prototype.increase = function(e) {
        var me = this;
        e.preventDefault();

        var val = parseInt(me.$el.val(), 10);

        val += me._stepSize;
        if(val > me._maxSize) {
            val = me._maxSize;
        }

        me.$el.val(val);
    };

    Plugin.prototype.decrease = function(e) {
        var me = this;
        e.preventDefault();

        var val = parseInt(me.$el.val(), 10);

        val -= me._stepSize;
        if(val < me._minSize) {
            val = me._minSize;
        }

        me.$el.val(val);
    };

    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName,
                new Plugin( this, options ));
            }
        });
    };
})(jQuery, window, document);