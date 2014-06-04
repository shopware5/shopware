;(function($, window, document, undefined) {
    "use strict";

    /**
     * Off canvas menu plugin
     *
     * The plugin provides an lightweight way to use an off canvas pattern for all kind of content. The content
     * needs to be positioned off canvas using CSS3 `transform`. All the rest will be handled by the plugin.
     *
     * @example Simple usage
     * ```
     *     <a href="#" data-offcanvas="true">Menu</a>
     * ```
     *
     * @example Show the menu on the right side
     * ```
     *     <a href="#" data-offcanvas="true" data-direction="fromRight">Menu</a>
     * ```
     *
     * @ToDo: Implement swipe gesture control. The old swipe gesture was removed due to a scrolling bug.
     */
    var pluginName = 'offcanvasMenu',
        isTouch = (('ontouchstart' in window) || (navigator.msMaxTouchPoints > 0)),
        clickEvt = 'click',
        defaults = {

            /** @string wrapSelector Selector for the content wrapper */
            wrapSelector: '.page-wrap',

            /** @string offCanvasSelector Selector of the off-canvas element */
            offCanvasSelector: '.sidebar-main',

            /** @string closeButtonSelector Selector for an additional button to close the menu */
            closeButtonSelector: '.entry--close-off-canvas',

            /** @string direction Animation direction, `fromLeft` (default) and `fromRight` are possible */
            direction: 'fromLeft',

            /** @string swipeContainerSelector Container selector which should catch the swipe gestructure */
            swipeContainerSelector: '.page-wrap',

            /** @string leftMoveCls Class for moving the container to the left */
            leftMoveCls: 'is--moved-left',

            /** @string rightMoveCls Class for moving the container to the right */
            rightMoveCls: 'is--moved-right',

            /** @string offCanvasElementCls Additional class for the off-canvas menu for necessary styling */
            offCanvasElementCls: 'off-canvas',

            /** @string leftMenuCls Class which should be added when the menu will be opened on the left side */
            leftMenuCls: 'is--left',

            /** @string rightMenuCls Class which should be added when the menu will be opened on the right side */
            rightMenuCls: 'is--right',

            /** @string activeMenuCls Class which indicates if the off-canvas menu is visible */
            activeMenuCls: 'is--active',

            /** @boolean disableTransitions Decide to either use transitions or not */
            disableTransitions: false,

            /** @string disableTransitionCls Class which disables all transitions for a smoother swiping */
            disableTransitionCls: 'js--no-transition'
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

        // Get the settings which are defined by data attributes
        me.getDataConfig();

        me._defaults = defaults;
        me._name = pluginName;

        me.init();
    }

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
     * Initializes the plugin, sets up event listeners and adds the necessary
     * classes to get the plugin up and running.
     *
     * @returns {Void}
     */
    Plugin.prototype.init = function() {
        var me = this,
            opts = me.opts;

        // Cache the necessary elements
        me.$pageWrap = $(opts.wrapSelector);
        me.$swipe = $(opts.swipeContainerSelector);
        me.$offCanvas = $(opts.offCanvasSelector);
        me.$closeButton = $(opts.closeButtonSelector);

        me.$offCanvas.addClass(opts.offCanvasElementCls)
                     .addClass((opts.direction === 'fromLeft') ? opts.leftMenuCls : opts.rightMenuCls)
                     .removeAttr('style');

        if (opts.disableTransitions) {
            me.$pageWrap.addClass(opts.disableTransitionCls);
            me.$offCanvas.addClass(opts.disableTransitionCls);
        }

        me.registerEventListeners();
    };

    /**
     * Registers all necessary event listeners for the plugin to proper operate. The
     * method contains the event callback methods as well due to the small amount of
     * code.
     *
     * @returns {Boolean}
     */
    Plugin.prototype.registerEventListeners = function() {
        var me = this,
            opts = me.opts;

        // Button click
        me.$el.on(clickEvt + '.' + pluginName, function(event) {
            event.preventDefault();
            (me.$offCanvas.hasClass(opts.activeMenuCls)) ? me.closeMenu() : me.openMenu();
        });

        // Allow the user to close the off canvas menu
        me.$closeButton.on(clickEvt + '.' + pluginName, function(event) {
            event.preventDefault();
            me.closeMenu();
        });

        return true;
    };

    /**
     * Opens the off-canvas menu based on the direction.
     * Also closes all other off-canvas menus.
     */
    Plugin.prototype.openMenu = function() {
        var me = this,
            opts = me.opts;

        // Close all other opened off-canvas menus
        $('.' + opts.offCanvasElementCls).removeClass(opts.activeMenuCls);

        me.$offCanvas.addClass(opts.activeMenuCls);
        me.$pageWrap.addClass((opts.direction === 'fromLeft') ? me.opts.leftMoveCls : me.opts.rightMoveCls);
    };

    /**
     * Closes the menu and slide the content wrapper
     * back to the normal position.
     */
    Plugin.prototype.closeMenu = function() {
        var me = this,
            opts = me.opts;

        me.$offCanvas.removeClass(opts.activeMenuCls);
        me.$pageWrap.removeClass(opts.leftMoveCls + ' ' + opts.rightMoveCls);
    };

    /**
     * Destroyes the initialized plugin completely, so all event listeners will
     * be removed and the plugin data, which is stored in-memory referenced to
     * the DOM node.
     *
     * @returns {Boolean}
     */
    Plugin.prototype.destroy = function() {
        var me = this,
            opts = me.opts;

        me.$offCanvas.removeClass(opts.offCanvasElementCls)
                     .removeClass(opts.disableTransitionCls)
                     .removeAttr('style');

        me.$pageWrap.off(clickEvt + '.' + pluginName)
                    .removeClass(opts.leftMoveCls + ' ' + opts.rightMoveCls)
                    .removeClass(opts.disableTransitionCls)
                    .removeAttr('style');

        me.$closeButton.off(clickEvt + '.' + pluginName);

        me.$el.off(clickEvt + '.' + pluginName).removeData('plugin_' + pluginName);

        return true;
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