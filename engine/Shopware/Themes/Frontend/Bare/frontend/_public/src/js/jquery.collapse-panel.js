;(function($, window, document, undefined) {
    "use strict";

    var pluginName = 'collapsePanel',
        defaults = {
            slideSpeed: 400
        };

    /**
     * Plugin constructor which merges the default settings with the user settings.
     *
     * @param {HTMLElement} element - Element which should be used in the plugin
     * @param {Object} userOpts - User settings for the plugin
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
     * Initializes the plugin and adds the necessary
     * classes to get the plugin up and running.
     */
    Plugin.prototype.init = function() {
        var me = this;

        me.targetElId = me.$el.attr('data-collapse-target');

        if (me.targetElId !== undefined) {
            me.$targetEl = $(me.targetElId);
        } else {
            me.$targetEl = me.$el.next('.collapse--content');
        }

        me.registerEvents();
    };

    /**
     * Registers all necessary event handlers.
     */
    Plugin.prototype.registerEvents = function() {
        var me = this;

        me.$el.on('click.' + pluginName, function(e) {
            e.preventDefault();
            me.toggleCollapse();
        });
    };

    /**
     * Changes the collapse state of the element.
     */
    Plugin.prototype.toggleCollapse = function() {
        var me = this;

        if (me.$targetEl.hasClass('is--active')) {
            me.$el.removeClass('is--active');
            me.$targetEl.slideUp(me.opts.slideSpeed, function() {
                me.$targetEl.removeClass('is--active');
            });
        } else {
            me.$el.addClass('is--active');
            me.$targetEl.slideDown(me.opts.slideSpeed).addClass('is--active');
        }
    };

    /**
     * Destroys the initialized plugin completely, so all event listeners will
     * be removed and the plugin data, which is stored in-memory referenced to
     * the DOM node.
     */
    Plugin.prototype.destroy = function() {
        var me = this;

        me.$el.removeClass('is--active');
        me.$targetEl.removeClass('is--active').hide();
        me.$el.off('click.' + pluginName).removeData('plugin_' + pluginName);
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