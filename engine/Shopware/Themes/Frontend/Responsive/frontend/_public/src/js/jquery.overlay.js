;(function ($) {
    "use strict";

    /**
     * jQuery overlay component.
     * Displays/Hides a fullscreen overlay with a certain color and opacity.
     *
     * @type {Object}
     */
    $.overlay = {
        /**
         * The overlay jQuery element.
         * Will be created when opening the overlay.
         *
         * @type {Null|jQuery}
         * @private
         */
        $overlay: null,

        /**
         * The default options for the overlay.
         * When certain options were not passed, these will be used instead.
         *
         * @type {Object}
         */
        defaults: {
            color: '#555555',
            opacity: 0.8,
            animationSpeed: 500,
            closeOnClick: false
        },

        /**
         * The extended options for the current opened overlay.
         *
         * @type {Object}
         */
        options: {},

        /**
         * Opens/Shows the fullscreen overlay element.
         * If it's not available, it will be created and appended to the document body.
         *
         * @param {Object} options
         */
        open: function (options) {
            var me = this;

            me.options = $.extend({}, me.defaults, options);

            if (me.$overlay === null) {
                me.$overlay = $('<div>', {
                    'class': 'js--overlay'
                });

                $('body').append(me.$overlay);
            }

            me._updateOverlay();

            me.$overlay.fadeIn(me.options.animationSpeed);
        },

        /**
         * Closes/Hides the overlay element when it's available
         */
        close: function () {
            var me = this;

            if (me.$overlay !== null) {
                me.$overlay.fadeOut(me.options.animationSpeed || me.defaults.animationSpeed);
            }
        },

        /**
         * Adds an event listener to the overlay element.
         *
         * @param {String} event
         * @param {Function} func
         */
        addListener: function (event, func) {
            var me = this;

            me.$overlay.on(event, func.bind(me));
        },

        /**
         * Removes an event listener from the overlay element.
         *
         * @param {String} event
         */
        removeListener: function (event) {
            var me = this;

            me.$overlay.off(event);
        },

        /**
         * Updates the overlay by setting new style options and listeners.
         *
         * @private
         */
        _updateOverlay: function () {
            var me = this,
                opts = me.options;

            me.$overlay.css({
                background: opts.color,
                opacity: opts.opacity,
                cursor: opts.closeOnClick ? 'pointer' : 'default'
            });

            me.removeListener('click.overlay');

            if (opts.closeOnClick) {
                me.addListener('click.overlay', me.close.bind(me));
            }
        }
    };
})(jQuery);