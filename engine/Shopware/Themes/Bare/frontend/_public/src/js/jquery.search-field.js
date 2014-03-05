;(function($, window, document, undefined) {
    "use strict";

    var pluginName = 'searchFieldDropDown',
        isTouch = (('ontouchstart' in window) || (navigator.msMaxTouchPoints > 0)),
        clickEvt = (isTouch ? (window.navigator.msPointerEnabled ? 'MSPointerDown': 'touchstart') : 'click'),
        defaults = {
            /** @string activeCls Class which will be added when the drop down was triggered */
            activeCls: 'is--active'
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

        me.$el.on(clickEvt + '.' + pluginName, function(event) {
            var target = $(event.target);
            event.preventDefault();
            event.stopPropagation();

            if(target.hasClass('main-search--field')) {
                return;
            }

            if(me.$el.hasClass(me.opts.activeCls)) {
                me.$el.removeClass(me.opts.activeCls);
            } else {
                me.$el.addClass(me.opts.activeCls);
                me.$el.find('.main-search--field').delay(150).focus();
            }
        });
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