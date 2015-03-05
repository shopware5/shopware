;(function ($, window, Modernizr) {
    'use strict';

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
    $.plugin('offcanvasMenu', {

        /**
         * Plugin default options.
         * Get merged automatically with the user configuration.
         */
        defaults: {

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
            'mode': 'local',

            /**
             * The inactive class will be set on the body to disable scrolling
             * while the off canvas is opened.
             *
             * This will be also used to improve the performance of the mobile
             * scroll behaviour of the off canvas and off canvas sub navigation menu.
             *
             * @property inactiveClass
             * @type {String}
             */
            'inactiveClass': 'is--inactive',

            /**
             * This is the animation duration time in ms
             *
             * @porperty animationSpeed
             * @type {Number}
             */
            'animationSpeed': 400,

            /**
             * The animation easing for the menu open action
             *
             * @property easingIn
             * @type {String}
             */
            'easingIn': 'cubic-bezier(.16,.04,.14,1)',

            /**
             * The animation easing for the menu close action
             *
             * @property easingOut
             * @type {String}
             */
            'easingOut': 'cubic-bezier(.2,.76,.5,1)',

            /**
             * The animation easing used when transitions are not supported.
             *
             * @property easingFallback
             * @type {String}
             */
            'easingFallback': 'swing'
        },

        /**
         * Initializes the plugin, sets up event listeners and adds the necessary
         * classes to get the plugin up and running.
         *
         * @public
         * @method init
         */
        init: function () {
            var me = this,
                opts = me.opts,
                transitionSupport = Modernizr.csstransitions;

            me.applyDataAttributes();

            // Cache the necessary elements
            me.$pageWrap = $(opts.wrapSelector);
            me.$swipe = $(opts.swipeContainerSelector);
            me.$overlay = $(opts.wrapSelector + ':before');
            me.$body = $('body');
            me.fadeEffect = transitionSupport && !opts.disableTransitions ? 'transition' : 'animate';
            me.easingEffectIn = transitionSupport ? opts.easingIn : opts.easingFallback;
            me.easingEffectOut = transitionSupport ? opts.easingOut : opts.easingFallback;

            me.opened = false;

            if (opts.mode === 'ajax') {
                me.$offCanvas = $('<div>', {
                    'class': opts.offCanvasElementCls + ' ' + ((opts.direction === 'fromLeft') ? opts.leftMenuCls : opts.rightMenuCls)
                }).appendTo(me.$body).css('display');
            } else {
                me.$offCanvas = $(opts.offCanvasSelector);
                me.$offCanvas.addClass(opts.offCanvasElementCls)
                    .addClass((opts.direction === 'fromLeft') ? opts.leftMenuCls : opts.rightMenuCls)
                    .removeAttr('style');
            }

            if (opts.fullscreen) {
                me.$offCanvas.addClass(opts.fullscreenCls);
            }

            me.offCanvasWidth = me.$offCanvas.width();

            if (!opts.fullscreen) {
                me.$offCanvas.css((opts.direction === 'fromLeft' ? 'left' : 'right'), me.offCanvasWidth * -1);
            }
            me.$offCanvas.addClass(opts.activeMenuCls);

            me.registerEventListeners();
        },

        /**
         * Registers all necessary event listeners for the plugin to proper operate.
         *
         * @public
         * @method onClickElement
         */
        registerEventListeners: function () {
            var me = this,
                opts = me.opts;

            // Button click
            me._on(me.$el, 'click', $.proxy(me.onClickElement, me));

            // Allow the user to close the off canvas menu
            me.$body.on(me.getEventName('click'), opts.closeButtonSelector, $.proxy(me.onClickBody, me));
        },

        /**
         * Called when the plugin element was clicked on.
         * Opens the off canvas menu, if the clicked element is not inside
         * the off canvas menu, prevent its default behaviour.
         *
         * @public
         * @method onClickElement
         * @param {jQuery.Event} event
         */
        onClickElement: function (event) {
            var me = this;

            if (!$.contains(me.$offCanvas[0], (event.target || event.currentTarget))) {
                event.preventDefault();
            }

            me.openMenu();
        },

        /**
         * Called when the body was clicked on.
         * Closes the off canvas menu.
         *
         * @public
         * @method onClickBody
         * @param {jQuery.Event} event
         */
        onClickBody: function (event) {
            event.preventDefault();

            this.closeMenu();
        },

        /**
         * Opens the off-canvas menu based on the direction.
         * Also closes all other off-canvas menus.
         *
         * @public
         * @method openMenu
         */
        openMenu: function () {
            var me = this,
                opts = me.opts,
                fromLeft = opts.direction === 'fromLeft',
                pluginName = me.getName(),
                plugin,
                css,
                left;

            if (me.opened) {
                return;
            }
            me.opened = true;

            // Close all other opened off-canvas menus
            $('.' + opts.offCanvasElementCls).each(function (i, el) {
                if (!(plugin = $(el).data('plugin_' + pluginName))) {
                    return true;
                }

                plugin.closeMenu();
            });

            // Disable scrolling on body
            $('html, body').css('overflow', 'hidden');

            $.overlay.open({
                closeOnClick: true,
                onClick: $.proxy(me.closeMenu, me)
            });

            css = {};
            css[fromLeft ? 'left' : 'right'] = 0;
            me.$offCanvas[me.fadeEffect](css, me.opts.animationSpeed, me.easingEffectIn);

            left = (opts.fullscreen) ? (fromLeft ? '100%' : '-100%') : me.offCanvasWidth * (fromLeft ? 1 : -1);
            me.$pageWrap[me.fadeEffect]({'left': left}, me.opts.animationSpeed, me.easingEffectOut);

            $.publish('plugin/offCanvasMenu/openMenu', me);

            if (opts.mode === 'ajax') {
                $.ajax({
                    url: opts.offCanvasSelector,
                    success: function (result) {
                        me.$offCanvas.html(result);
                    }
                });
            }
        },

        /**
         * Closes the menu and slides the content wrapper
         * back to the normal position.
         *
         * @public
         * @method closeMenu
         */
        closeMenu: function () {
            var me = this,
                opts = me.opts,
                fromLeft = opts.direction === 'fromLeft',
                css;

            if (!me.opened) {
                return;
            }
            me.opened = false;

            $.overlay.close();

            // Disable scrolling on body
            $('html, body').css('overflow', '');

            css = {};
            css[fromLeft ? 'left' : 'right'] = opts.fullscreen ? '-100%' : me.offCanvasWidth * -1;

            me.$offCanvas[me.fadeEffect](css, me.opts.animationSpeed, me.easingEffectOut);
            me.$pageWrap[me.fadeEffect]({'left': 0}, me.opts.animationSpeed, me.easingEffectOut);

            $.publish('plugin/offCanvasMenu/closeMenu', me);
        },

        /**
         * Returns whether or not the off canvas menu is opened.
         *
         * @public
         * @method isOpened
         * @returns {Boolean}
         */
        isOpened: function () {
            return this.opened;
        },

        /**
         * Destroys the initialized plugin completely, so all event listeners will
         * be removed and the plugin data, which is stored in-memory referenced to
         * the DOM node.
         *
         * @public
         * @method destroy
         */
        destroy: function () {
            var me = this,
                opts = me.opts;

            // check if overlay exists
            if (me.opened) {
                $.overlay.close();
            }

            me.$offCanvas.removeClass(opts.offCanvasElementCls)
                .removeClass(opts.activeMenuCls)
                .removeAttr('style');

            me.$pageWrap.removeAttr('style');

            me.$body.off(me.getEventName('click'), opts.closeButtonSelector);

            me._destroy();
        }
    });
})(jQuery, window, Modernizr);