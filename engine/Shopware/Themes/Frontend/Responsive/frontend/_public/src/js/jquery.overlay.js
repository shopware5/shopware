;(function ($, modernizr) {
    'use strict';

    var emptyFn = function () {};

    /**
     * Shopware Overlay Module.
     *
     * Displays/Hides a fullscreen overlay with a certain color and opacity.
     *
     * @example
     *
     * Open th overlay:
     *
     * $.overlay.open({
     *     color: '#FF0000' //red
     *     opacity: 0.5
     * });
     *
     * Closing the overlay:
     *
     * $.overlay.close();
     *
     * @type {Object}
     */
    $.overlay = {
        /**
         * The overlay jQuery element.
         * Will be created when opening the overlay.
         *
         * @private
         * @property _$overlay
         * @type {jQuery}
         */
        _$overlay: null,

        /**
         * The default options for the overlay.
         * When certain options were not passed, these will be used instead.
         *
         * @public
         * @property defaults
         * @type {Object}
         */
        defaults: {
            /**
             * Override for the overlay color.
             * If no color is passed, the default template overlay color will be used.
             *
             * @type {String}
             */
            color: '',

            /**
             * The overlay alpha value ranges from 0 to 1.
             *
             * @type {Number}
             */
            opacity: 0.8,

            /**
             * The animation duration for all animations.
             *
             * @type {Number}
             */
            animationSpeed: 500,

            /**
             * Whether or not the overlay should be closed when clicked on.
             *
             * @type {Boolean}
             */
            closeOnClick: false,

            /**
             * Function that will be called when the overlay was clicked on.
             *
             * @type {Function}
             */
            onClick: emptyFn
        },

        /**
         * The extended options for the current opened overlay.
         *
         * @public
         * @property options
         * @type {Object}
         */
        options: { },

        /**
         * Internal flag that indicates whether or not the overlay is opened.
         * Use {@link isOpen} to get this status.
         *
         * @private
         * @property _opened
         * @type {Boolean}
         */
        _opened: false,

        /**
         * Opens/Shows the fullscreen overlay element.
         * If it's not available, it will be created and appended to the document body.
         *
         * @public
         * @method open
         * @param {Object} options
         */
        open: function (options) {
            var me = this;

            me.options = $.extend({}, me.defaults, options);

            if (me.isOpen()) {
                return;
            }
            me._opened = true;

            if (!me._$overlay) {
                me._$overlay = $('<div>', {
                    'class': 'js--overlay'
                });

                $('body').append(me._$overlay);

                me._$overlay.on('click touchstart MSPointerDown', me.onClick.bind(me));
            }

            me.updateOverlay();

            if (modernizr.csstransitions) {
                me._$overlay.stop(true).transition({
                    opacity: me.options.opacity
                }, me.options.animationSpeed);
            } else {
                me._$overlay.stop(true).animate({
                    opacity: me.options.opacity
                }, me.options.animationSpeed);
            }
        },

        /**
         * Closes/Hides the overlay element when it's available
         *
         * @public
         * @method close
         */
        close: function () {
            var me = this,
                css = {
                    opacity: 0.01
                },
                duration = me.options.animationSpeed || me.defaults.animationSpeed,
                easing = 'ease',
                callback = function () {
                    me._$overlay.css('display', 'none');
                };

            if (!me.isOpen()) {
                return;
            }
            me._opened = false;

            if (me._$overlay !== null) {
                if (modernizr.csstransitions) {
                    me._$overlay.stop(true).transition(css, duration, easing, callback);
                } else {
                    me._$overlay.stop(true).animate(css, {
                        duration: duration,
                        complete: callback
                    });
                }
            }
        },

        /**
         * Returns the status whether or not the overlay is opened.
         *
         * @public
         * @method isOpen
         * @returns {Boolean}
         */
        isOpen: function () {
            return this._opened;
        },

        /**
         * Updates the overlay by setting new style options and listeners.
         *
         * @public
         * @method updateOverlay
         */
        updateOverlay: function () {
            var me = this,
                opts = me.options;

            me._$overlay.css({
                opacity: 0.01,
                display: 'block',
                background: opts.color ? opts.color : '',
                cursor: opts.closeOnClick ? 'pointer' : 'default'
            });
        },

        /**
         * Will be called when the overlay was clicked on.
         * Calls the onClick() function which was passed by the options.
         *
         * @public
         * @method onClick
         */
        onClick: function (event) {
            var me = this;

            event.preventDefault();

            me.options.onClick.call(me);

            if (me.options.closeOnClick) {
                me.close();
            }
        },

        /**
         * Removes the current overlay element from the DOM and destroys it.
         * Also clears the options.
         *
         * @public
         * @method destroy
         */
        destroy: function () {
            var me = this,
                p;

            me._$overlay.remove();

            me._$overlay = null;

            for (p in me.options) {
                if (!me.options.hasOwnProperty(p)) {
                    continue;
                }
                delete me.options[p];
            }
        }
    };
})(jQuery, Modernizr);
