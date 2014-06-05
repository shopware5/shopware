;(function($, window, document, undefined) {
    "use strict";

    var pluginName = 'collapsePanel',
        defaults = {

            // The selector of the target element which should be collapsed.
            collapseTarget: false,

            // Additional class which will be added to the collapse target.
            collapseTargetCls: 'js--collapse-target',

            // Decide if sibling collapse panels should be closed when the target is collapsed.
            closeSiblings: false,

            // The speed of the collapse animation in ms.
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

        if (me.opts.collapseTarget.length) {
            me.$targetEl = $(me.opts.collapseTarget);
        } else {
            me.$targetEl = me.$el.next('.collapse--content');
        }

        me.$targetEl.addClass(me.opts.collapseTargetCls);

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
            me.toggleCollapse();
        });
    };

    /**
     * Changes the collapse state of the element.
     */
    Plugin.prototype.toggleCollapse = function() {
        var me = this,
            siblings = $('.'+me.opts.collapseTargetCls).not(me.$targetEl);

        if (me.$targetEl.hasClass('is--active')) {
            me.$el.removeClass('is--active');
            me.$targetEl.slideUp(me.opts.animationSpeed, function() {
                me.$targetEl.removeClass('is--active');
            });
        } else {
            me.$el.addClass('is--active');
            me.$targetEl.slideDown(me.opts.animationSpeed).addClass('is--active');
            if (me.opts.closeSiblings) siblings.slideUp(me.opts.animationSpeed).removeClass('is--active');
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
        me.$targetEl.removeClass('is--active')
                    .removeClass(me.opts.collapseTargetCls)
                    .removeAttr('style');
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