;(function($, window, document, undefined) {
    "use strict";

    var pluginName = 'scrollableList',
        isTouch = (('ontouchstart' in window) || (navigator.msMaxTouchPoints > 0)),
        clickEvt = (isTouch ? (window.navigator.msPointerEnabled ? 'MSPointerDown': 'touchstart') : 'click'),
        defaults = {
            /** @string activeCls Class which will be added when the drop down was triggered */
            baseCls: 'js--scrollable-list',
            activeCls: 'is--active',
            showArrows: true,
            leftArrowText: '<i class="icon--arrow-left"></i>',
            rightArrowText: '<i class="icon--arrow-right"></i>'
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
     * @returns {Boolean}
     */
    Plugin.prototype.init = function() {
        var me = this,
            elWidth = me.$el.width();

        var itemTotalWidth = 0;
        me.$el.children().each(function() {
            itemTotalWidth += $(this).outerWidth();
        });

        if(itemTotalWidth < elWidth) {
            return false;
        }
        me.totalWidth = itemTotalWidth;

        me._$wrapper = me.createWrapper(me.totalWidth);
        me.$el.wrap(me._$wrapper);
        me._$wrapper = me.$el.parent();

        if(me.opts.showArrows) {
            me.createArrows(me._$wrapper);
        }

        me.registerEventListeners();
    };

    Plugin.prototype.registerEventListeners = function() {
        var me = this,
            container = me._$wrapper.parent(),
            containerWidth = container.width();

        me._$wrapper.on('movestart.' + pluginName, function(e) {
            // Allows the normal up and down scrolling from the browser
            if ((e.distX > e.distY && e.distX < -e.distY) || (e.distX < e.distY && e.distX > -e.distY)) {
                e.preventDefault();
                return;
            }

            // Disable click events for the swiping / moving
            me._$wrapper.find('a').on('click.' + pluginName, function(event) { event.preventDefault(); });
        }).on('move.' + pluginName, function(e) {
            var x = e.distX,
                absX = (Math.abs(x) + containerWidth) - 10;

            if(absX > me.totalWidth) return;
            if(x > 0) x = 0;

            me._$wrapper.css({ translate: [ x, 0] });
        }).on('moveend.' + pluginName, function() {
            me._$wrapper.find('a').on('click.' + pluginName);
        });
    };

    Plugin.prototype.createWrapper = function(width) {
        var me = this;

        return $('<div>', {
            'class': me.opts.baseCls + '--container',
            'css': { 'width': width + (isTouch ? 40 : 30) }
        });
    };

    Plugin.prototype.createArrows = function(wrapper) {
        var me = this,
            container = me._$wrapper.parent(),
            containerWidth = container.width();

        me.$leftArrow = $('<a>', {
            'class': me.opts.baseCls + '--arrow-left',
            'href': '#' + me.opts.baseCls + '--arrow-left',
            'html': me.opts.leftArrowText
        }).on(clickEvt + '.' + pluginName, function(event) {
            event.preventDefault();
            me._$wrapper.transition({ x: 0 }, 500);

            me.$rightArrow.show();
            me.$leftArrow.hide();
        }).appendTo(wrapper.parent()).hide();

        me.$rightArrow = $('<a>', {
            'class': me.opts.baseCls + '--arrow-right',
            'href': '#' + me.opts.baseCls + '--arrow-right',
            'html': me.opts.rightArrowText
        }).on(clickEvt + '.' + pluginName, function(event) {
            event.preventDefault();
            var scrollWidth = me.totalWidth - containerWidth;

            if(scrollWidth > containerWidth) {
                scrollWidth = containerWidth;
            }
            me._$wrapper.transition({ x: -scrollWidth }, 500);
            me.$rightArrow.hide();
            me.$leftArrow.show();
        }).appendTo(wrapper.parent());

        return true;
    };

    /**
     * Destroyes the initialized plugin completely, so all event listeners will
     * be removed and the plugin data, which is stored in-memory referenced to
     * the DOM node.
     *
     * @returns {Boolean}
     */
    Plugin.prototype.destroy = function() {
        var me = this;

        me.$el.off(clickEvt + '.' + pluginName);
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