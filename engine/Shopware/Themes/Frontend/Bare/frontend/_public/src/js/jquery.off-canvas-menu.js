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
     */
    var pluginName = 'offcanvasMenu',
        isTouch = (('ontouchstart' in window) || (navigator.msMaxTouchPoints > 0)),
        clickEvt = (isTouch ? (window.navigator.msPointerEnabled ? 'MSPointerDown': 'touchstart') : 'click'),
        defaults = {

            /** @string wrapSelector Selector for the content wrapper */
            wrapSelector: '.page-wrap',

            /** @string direction Animation direction, `fromLeft` (default) and `fromRight` are possible */
            direction: 'fromLeft',

            /** @string swipeContainerSelector Container selector which should catch the swipe gestructure */
            swipeContainerSelector: '.page-wrap',

            /** @string leftMenuOpenCls Class which should be added when the menu will be opened on the left side */
            leftMenuOpenCls: 'js--menu-left--open',

            /** @string rightMenuOpenCls Class which should be added when the menu will be opened on the right side */
            rightMenuOpenCls: 'js--menu-right--open',

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
        var me = this,
            opts = me.opts;

        // Cache the neccessary elements
        me.$pageWrap = $(opts.wrapSelector);
        me.$body = $('body');
        me.$swipe = $(opts.swipeContainerSelector);

        // Parse the direction, which should be used for the animation
        var direction = me.$el.attr('data-direction');
        if(direction && direction.length && direction === 'fromRight') {
            opts.direction = 'fromRight';
        }

        var selector = me.$el.attr('data-selector');
        if(selector && selector.length) {
            opts.offcanvasElement = selector;
        }
        me.$offcanvas = $(opts.offcanvasElement);

        me.registerEventListeners();
    };

    /**
     * Registers all neccessary event listeners for the plugin to proper operate. The
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

            if(me.$body.hasClass((opts.direction === 'fromLeft' ? opts.leftMenuOpenCls : opts.rightMenuOpenCls))) {
                me.$pageWrap.transition({ translate: [0, 0] }, 250, function() {
                    me.$pageWrap.removeAttr('style');
                    me.$body.removeClass((opts.direction === 'fromLeft' ? opts.leftMenuOpenCls : opts.rightMenuOpenCls));
                });
            } else {
                me.$body.addClass((opts.direction === 'fromLeft' ? opts.leftMenuOpenCls : opts.rightMenuOpenCls));
            }
        });

        // Swipe gestructure
        me.$swipe.on((opts.direction === 'fromLeft' ? 'swiperight' : 'swipeleft') + '.' + pluginName, function() {
            me.$body.addClass((opts.direction === 'fromLeft' ? opts.leftMenuOpenCls : opts.rightMenuOpenCls));

        // Allow the user to swipe themself
        }).on('movestart.' + pluginName, function(e) {

            // Allows the normal up and down scrolling from the browser
            if ((e.distX > e.distY && e.distX < -e.distY) || (e.distX < e.distY && e.distX > -e.distY)) {
                e.preventDefault();
                return;
            }
            me.$pageWrap.addClass(opts.disableTransitionCls);
        }).on('move.' + pluginName, function(e) {
            var x = e.distX;

            if(opts.direction === 'fromLeft') {
                x = (x < 0 ? 0 : x);
                x = (x > 300 ? 300 : x);
            } else {
                x =(x > 0 ? 0 : x);
                x = (x < -300 ? -300 : x);
            }
            me.$pageWrap.css({ translate: [ x, 0] });
            me.$body.addClass((opts.direction === 'fromLeft' ? opts.leftMenuOpenCls : opts.rightMenuOpenCls));
        }).on('moveend.' + pluginName, function() {
            me.$pageWrap.removeAttr('style').removeClass(opts.disableTransitionCls);
        });

        // Allow the user to close the off canvas menu
        $('.entry--close-off-canvas').on(clickEvt + '.' + pluginName, function(event) {
            event.preventDefault();

            me.$pageWrap.transition({ translate: [0, 0] }, 250, function() {
                me.$pageWrap.removeAttr('style');
                me.$body.removeClass((opts.direction === 'fromLeft' ? opts.leftMenuOpenCls : opts.rightMenuOpenCls));
            });
        });

        me.$offcanvas.on((opts.direction === 'fromLeft' ? 'swipeleft' : 'swiperight') + '.' + pluginName, function() {
            me.$pageWrap.transition({ translate: [0, 0] }, 250, function() {
                me.$pageWrap.removeAttr('style');
                me.$body.removeClass((opts.direction === 'fromLeft' ? opts.leftMenuOpenCls : opts.rightMenuOpenCls));
            });
        }).on('movestart.' + pluginName, function(e) {

            // Allows the normal up and down scrolling from the browser
            if ((e.distX > e.distY && e.distX < -e.distY) || (e.distX < e.distY && e.distX > -e.distY)) {
                e.preventDefault();
                return;
            }
            me.$pageWrap.addClass(opts.disableTransitionCls);
        }).on('move.' + pluginName, function(e) {
            var x = 300 - Math.abs(e.distX);
            if(opts.direction === 'fromLeft') {
                x = (x < 0 ? 0 : x);
                x = (x > 300 ? 300 : x);
            } else {
                x =(x > 0 ? 0 : x);
                x = (x < -300 ? -300 : x);
            }
            me.$pageWrap.css({ translate: [ x, 0] });
            //me.$body.addClass((opts.direction === 'fromLeft' ? opts.leftMenuOpenCls : opts.rightMenuOpenCls));
        }).on('moveend.' + pluginName, function() {
            me.$pageWrap.removeAttr('style').removeClass(opts.disableTransitionCls);
        });

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
        var me = this,
            opts = me.opts;

        me.$swipe
            .off((opts.direction === 'fromLeft' ? 'swiperight' : 'swipeleft') + '.' + pluginName)
            .off('movestart.' + pluginName)
            .off('move.' + pluginName)
            .off('moveend.' + pluginName);

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