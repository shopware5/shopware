;(function($, window, document, undefined) {
    "use strict";

    var pluginName = 'scroll',
        defaults = {

            // The selector of the container which should be scrolled.
            scrollContainerSelector: '.page-wrap',

            // The selector of the target element or the position in px where the container should be scrolled to.
            scrollTarget: 0,

            // The speed of the scroll animation in ms.
            animationSpeed: 300
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

        me.getDataConfig();

        me.$container = $(me.opts.scrollContainerSelector);

        if (typeof me.opts.scrollTarget == 'string') me.$targetEl = $(me.opts.scrollTarget);

        me.registerEvents();
    };

    /**
     * Loads config settings which are set via data attributes and
     * overrides the old setting with the data attribute of the
     * same name if defined.
     */
    Plugin.prototype.getDataConfig = function() {
        var me = this,
            attr;

        $.each(me.opts, function(key, value) {
            attr = me.$el.attr('data-' + key);
            if ( attr !== undefined ) {
                me.opts[key] = attr;
            }
        });
    };

    /**
     * Registers all necessary event handlers.
     */
    Plugin.prototype.registerEvents = function() {
        var me = this;

        me.$el.on('click.' + pluginName, function(e) {
            e.preventDefault();

            if (typeof me.opts.scrollTarget == 'number') {
                me.scrollToPosition(me.opts.scrollTarget);
            } else if (me.$targetEl.length) {
                me.scrollToElement(me.$targetEl);
            }
        });
    };

    /**
     * Scrolls to a specific element on the page.
     *
     * @param $targetEl - jQuery Element
     * @param aberration
     */
    Plugin.prototype.scrollToElement = function($targetEl, aberration) {
        var me = this,
            ab = aberration || 0,
            offset = $targetEl[0].offsetTop,
            position = offset + ab;

        me.scrollToPosition(position);
    };

    /**
     * Scrolls the page to the given position.
     *
     * @param position
     */
    Plugin.prototype.scrollToPosition = function(position) {
        var me = this;

        me.$container.animate({ scrollTop: position }, me.opts.animationSpeed);
    };

    /**
     * Destroys the initialized plugin completely, so all event listeners will
     * be removed and the plugin data, which is stored in-memory referenced to
     * the DOM node.
     */
    Plugin.prototype.destroy = function() {
        var me = this;

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