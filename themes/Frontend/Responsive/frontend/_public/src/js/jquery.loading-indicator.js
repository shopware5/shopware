;(function ($) {
    'use strict';

    /**
     * jQuery loading indicator component.
     *
     * @type {Object}
     */
    $.loadingIndicator = {
        /**
         * The loader jQuery element.
         * Will be created when opening the indicator.
         * Contains the loading icon.
         *
         * @type {Null|jQuery}
         * @private
         */
        $loader: null,

        /**
         * The default options for the indicator.
         * When certain options were not passed, these will be used instead.
         *
         * @type {Object}
         */
        defaults: {
            loaderCls: 'js--loading-indicator',
            iconCls: 'icon--default',
            delay: 0,
            animationSpeed: 500,
            closeOnClick: true,
            openOverlay: true
        },

        /**
         * The extended options for the current opened overlay.
         *
         * @type {Object}
         */
        options: {},

        /**
         * Opens/Shows the loading indicator along with the overlay.
         * If the loader is not available, it will be created.
         *
         * @param {Object} indicatorOptions
         */
        open: function (indicatorOptions) {
            var me = this;

            me.options = $.extend({}, me.defaults, indicatorOptions);

            if (me.$loader === null) {
                me.$loader = me._createLoader();
                $('body').append(me.$loader);
            }

            me._updateLoader();

            me._timeout = window.setTimeout(function() {
                if (me.options.openOverlay !== false) {
                    $.overlay.open($.extend({}, {
                        closeOnClick: me.options.closeOnClick,
                        onClose: me.close.bind(me)
                    }));
                }

                me.$loader.fadeIn(me.options.animationSpeed, function () {
                    $.publish('plugin/swLoadingIndicator/onOpenFinished', [ me ]);
                });
            }, me.options.delay);

            $.publish('plugin/swLoadingIndicator/onOpen', [ me ]);
        },

        /**
         * Closes the loader element along with the overlay.
         */
        close: function (callback) {
            var me = this,
                opts = me.options;

            callback = callback || function() {};

            if (me.$loader !== null) {
                me.$loader.fadeOut(opts.animationSpeed || me.defaults.animationSpeed, function () {
                    callback.call(me);

                    if (me._timeout) {
                        window.clearTimeout(me._timeout);
                    }

                    if (opts.openOverlay !== false) {
                        $.overlay.close();
                    }

                    $.publish('plugin/swLoadingIndicator/onCloseFinished', [ me ]);
                });
            }

            $.publish('plugin/swLoadingIndicator/onClose', [ me ]);
        },

        /**
         * Updates the loader element.
         * If the current loader/icon classes differentiate with the passed options, they will be set.
         *
         * @private
         */
        _updateLoader: function () {
            var me = this,
                opts = me.options,
                $loader = me.$loader,
                $icon = $($loader.children()[0]);

            if (!$loader.hasClass(opts.loaderCls)) {
                $loader.removeClass('').addClass(opts.loaderCls);
            }

            if (!$icon.hasClass(opts.iconCls)) {
                $icon.removeClass('').addClass(opts.iconCls);
            }
        },

        /**
         * Creates the loader with the indicator icon in it.
         *
         * @returns {jQuery}
         * @private
         */
        _createLoader: function () {
            var me = this,
                loader = $('<div>', {
                    'class': me.options.loaderCls
                }),
                icon = $('<div>', {
                    'class': me.options.iconCls
                });

            loader.append(icon);

            return loader;
        }
    };
})(jQuery);
