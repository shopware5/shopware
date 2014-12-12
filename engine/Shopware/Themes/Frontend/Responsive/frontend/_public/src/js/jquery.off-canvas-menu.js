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
        clickEvt = 'click',
        defaults = {

            /**
             * Selector for the content wrapper
             *
             * @property wrapSelector
             * @type {String}
             */
            'wrapSelector': '.page-wrap',

            /**
             * Selector of the off-canvas element
             *
             * @property offCanvasSelector
             * @type {String}
             */
            'offCanvasSelector': '.sidebar-main',

            /**
             * Selector for an additional button to close the menu
             *
             * @property closeButtonSelector
             * @type {String}
             */
            'closeButtonSelector': '.entry--close-off-canvas',

            /**
             * Animation direction, `fromLeft` (default) and `fromRight` are possible
             *
             * @property direction
             * @type {String}
             */
            'direction': 'fromLeft',

            /**
             * Container selector which should catch the swipe gesture
             *
             * @property swipeContainerSelector
             * @type {String}
             */
            'swipeContainerSelector': '.page-wrap',

            /**
             * Class for moving the container to the left
             *
             * @property leftMoveCls
             * @type {String}
             */
            'leftMoveCls': 'is--moved-left',

            /**
             * Class for moving the container to the right
             *
             * @property rightMoveCls
             * @type {String}
             */
            'rightMoveCls': 'is--moved-right',

            /**
             * Additional class for the off-canvas menu for necessary styling
             *
             * @property offCanvasElementCls
             * @type {String}
             */
            'offCanvasElementCls': 'off-canvas',

            /**
             * Class which should be added when the menu will be opened on the left side
             *
             * @property leftMenuCls
             * @type {String}
             */
            'leftMenuCls': 'is--left',

            /**
             * Class which should be added when the menu will be opened on the right side
             *
             * @property rightMenuCls
             * @type {String}
             */
            'rightMenuCls': 'is--right',

            /**
             * Class which indicates if the off-canvas menu is visible
             *
             * @property activeMenuCls
             * @type {String}
             */
            'activeMenuCls': 'is--active',

            /**
             * Flag whether to use transitions or not
             *
             * @property disableTransitions
             * @type {Boolean}
             */
            'disableTransitions': false,

            /**
             * Class which disables all transitions for a smoother swiping
             *
             * @property disableTransitionCls
             * @type {String}
             */
            'disableTransitionCls': 'js--no-transition',

            /**
             * Flag whether to show the offcanvas menu in full screen or not.
             *
             * @property fullscreen
             * @type {Boolean}
             */
            'fullscreen': false,

            /**
             * Class which sets the canvas to full screen
             *
             * @property fullscreenCls
             * @type {String}
             */
            'fullscreenCls': 'js--full-screen',

            /**
             * The mode in which the off canvas menu should be showing.
             *
             * 'local': The given 'offCanvasSelector' will be used as the off canvas menu.
             *
             * 'ajax': The given 'offCanvasSelector' will be used as an URL to
             *         load the content via AJAX.
             *
             * @type {String}
             */
            'mode': 'local'
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
        me.$closeButton = $(opts.closeButtonSelector);
        me.$overlay = $(opts.wrapSelector + ':before');
        me.$body = $('body');

        if (opts.mode === 'ajax') {
            me.$offCanvas = $('<div>', {
                'class': opts.offCanvasElementCls + ' ' + ((opts.direction === 'fromLeft') ? opts.leftMenuCls : opts.rightMenuCls)
            }).appendTo(me.$body);
        } else {
            me.$offCanvas = $(opts.offCanvasSelector);
            me.$offCanvas.addClass(opts.offCanvasElementCls)
                .addClass((opts.direction === 'fromLeft') ? opts.leftMenuCls : opts.rightMenuCls)
                .removeAttr('style');
        }

        if (opts.disableTransitions) {
            me.$pageWrap.addClass(opts.disableTransitionCls);
            me.$offCanvas.addClass(opts.disableTransitionCls);
        }

        if (opts.fullscreen) {
            me.$offCanvas.addClass(opts.fullscreenCls);
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
            if (!me.$offCanvas.hasClass(opts.activeMenuCls)) {
                me.openMenu();
            }
        });

        // Allow the user to close the off canvas menu
        me.$body.delegate(opts.closeButtonSelector, clickEvt + '.' + pluginName, function(event) {
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

        $.overlay.open({
            closeOnClick: true,
            onClick: $.proxy(me.closeMenu, me)
        });

        me.$offCanvas.addClass(opts.activeMenuCls);
        me.$pageWrap.addClass((opts.direction === 'fromLeft') ? me.opts.leftMoveCls : me.opts.rightMoveCls);
        me.$body.addClass((opts.direction === 'fromLeft') ? me.opts.leftMoveCls : me.opts.rightMoveCls);

        $('html, body').addClass('no--scroll');

        me.$pageWrap.on('scroll.' + pluginName, function(e) {
            e.preventDefault();
        });

        if (opts.mode === 'ajax') {
            $.ajax({
                url: opts.offCanvasSelector,
                success: function (result) {
                    me.$offCanvas.html(result);
                }
            })
        }
    };

    /**
     * Closes the menu and slide the content wrapper
     * back to the normal position.
     */
    Plugin.prototype.closeMenu = function() {
        var me = this,
            opts = me.opts;

        $.overlay.close();

        me.$offCanvas.removeClass(opts.activeMenuCls).removeAttr('style');
        me.$pageWrap.removeClass(opts.leftMoveCls + ' ' + opts.rightMoveCls);
        me.$body.removeClass(opts.leftMoveCls + ' ' + opts.rightMoveCls);

        $('html, body').removeClass('no--scroll');

        me.$pageWrap.off('scroll.' + pluginName);
        $.publish('plugin/offCanvasMenu/closeMenu');
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


        // check if overlay exists
        if(me.$offCanvas.hasClass(opts.activeMenuCls)) {
            $.overlay.close();
        }

        me.$offCanvas.removeClass(opts.offCanvasElementCls)
            .removeClass(opts.activeMenuCls)
            .removeClass(opts.disableTransitionCls)
            .removeAttr('style');

        me.$pageWrap.off(clickEvt + '.' + pluginName)
            .removeClass(opts.leftMoveCls + ' ' + opts.rightMoveCls)
            .removeClass(opts.disableTransitionCls)
            .removeAttr('style');

        me.$body.removeClass(opts.leftMoveCls + ' ' + opts.rightMoveCls);

        me.$closeButton.off(clickEvt + '.' + pluginName);

        me.$el.off(clickEvt + '.' + pluginName).removeData('plugin_' + pluginName);

        me.$body.undelegate('.' + pluginName);

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