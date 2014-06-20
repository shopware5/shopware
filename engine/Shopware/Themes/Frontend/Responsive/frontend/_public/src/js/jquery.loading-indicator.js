;(function ($) {
    "use strict";

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
            animationSpeed: 500,
            closeOnClick: true
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
         * @param {Object} overlayOptions
         */
        open: function (indicatorOptions, overlayOptions) {
            var me = this;

            me.options = $.extend({}, me.defaults, indicatorOptions);

            $.overlay.open($.extend({}, overlayOptions, { closeOnClick: me.options.closeOnClick }));

            if (me.$loader === null) {
                me.$loader = me._createLoader();
                $('body').append(me.$loader);
            }

            me._updateLoader();

            me.$loader.fadeIn(me.options.animationSpeed);
        },

        /**
         * Closes the loader element along with the overlay.
         */
        close: function () {
            var me = this,
                opts = me.options;

            $.overlay.close();

            if (me.$loader !== null) {
                me.$loader.fadeOut(opts.animationSpeed || me.defaults.animationSpeed);
            }
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

            $.overlay.removeListener('click.loadingIndicator');

            if (opts.closeOnClick) {
                $.overlay.addListener('click.loadingIndicator', me.close.bind(me));
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