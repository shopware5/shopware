;(function ($) {
    'use strict';

    /**
     * Shopware Slide Panel Plugin.
     */
    $.plugin('slidePanel', {

        /**
         * Plugin default options.
         * Get merged automatically with the user configuration.
         */
        defaults: {

            /**
             * Class which will be toggled when the drop down was triggered
             *
             * @property activeCls
             * @type {String}
             */
            activeCls: 'is--active'
        },

        /**
         * Initializes the plugin and sets up all needed event listeners.
         *
         * @public
         * @method init
         */
        init: function () {
            var me = this;

            me.applyDataAttributes();

            me._on(me.$el, 'touchstart MSPointerDown click', $.proxy(me.onTriggerEl, me));
        },

        /**
         * Called when the element was triggered by a click / touch.
         * Toggles the active classes on the regarding elements.
         *
         * @public
         * @method onTriggerEl
         */
        onTriggerEl: function (event) {
            var me = this,
                activeClass = me.opts.activeCls,
                $el = me.$el,
                $next = $el.next();

            event.preventDefault();

            if ($next.hasClass(activeClass)) {
                $el.removeClass(activeClass);
                $next.removeClass(activeClass);
            } else {
                $el.addClass(activeClass);
                $next.addClass(activeClass);
            }
        },

        /**
         * Destroys the initialized plugin completely.
         * All registered event listeners and references will be removed.
         *
         * @public
         * @method destroy
         */
        destroy: function () {
            this._destroy();
        }
    });
})(jQuery);