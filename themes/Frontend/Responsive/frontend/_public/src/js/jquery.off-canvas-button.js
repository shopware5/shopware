(function ($) {
    'use strict';

    /**
     * Shopware Menu Scroller Plugin
     */
    $.plugin('swOffcanvasButton', {

        /**
         * Default options for the offcanvas button plugin
         *
         * @public
         * @property defaults
         * @type {Object}
         */
        defaults: {

            /**
             * CSS selector for the element listing
             *
             * @type {String}
             */
            pluginClass: 'js--off-canvas-button',

            /**
             * CSS class which will be added to the wrapper / this.$el
             *
             * @type {String}
             */
            contentSelector: '.offcanvas--content',

            /**
             * Selector for the closing button
             *
             * @type {String}
             */
            closeButtonSelector: '.close--off-canvas',

            /**
             * CSS class which will be added to the listing
             *
             * @type {Boolean}
             */
            fullscreen: true,

            /**
             * If this is set to true, the plugin will search its content container for an element matching the
             * autoOpenSelector and open the offcanvas menu in case it finds one.
             *
             * @type {Boolean}
             */
            autoOpen: false,

            /**
             * The selector used to automatically open the offcanvas menu.
             *
             * @type {String}
             */
            autoOpenSelector: ''
        },

        /**
         * Default plugin initialisation function.
         * Sets all needed properties, creates the slider template
         * and registers all needed event listeners.
         *
         * @public
         * @method init
         */
        init: function () {
            var me = this,
                $el = me.$el,
                opts = me.opts,
                selector = $el.find(opts.contentSelector);

            if (selector.length === 0) {
                selector = $(opts.contentSelector);
            }

            me.applyDataAttributes();

            $el.addClass(opts.pluginClass);

            $el.swOffcanvasMenu({
                'direction': 'fromRight',
                'offCanvasSelector': selector,
                'fullscreen': opts.fullscreen,
                'closeButtonSelector': opts.closeButtonSelector,
                'autoOpen': opts.autoOpen,
                'autoOpenSelector': opts.autoOpenSelector
            });
        },

        /**
         * Removed all listeners, classes and values from this plugin.
         *
         * @public
         * @method destroy
         */
        destroy: function () {
            var me = this,
                $el = me.$el,
                plugin = $el.data('plugin_swOffcanvasMenu');

            if (plugin) {
                plugin.destroy();
            }

            $el.removeClass(me.opts.pluginClass);

            me._destroy();
        }
    });
}(jQuery));
