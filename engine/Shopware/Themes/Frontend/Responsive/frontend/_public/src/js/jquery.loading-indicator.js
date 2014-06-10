;(function ($) {
    "use strict";

    $.loadingIndicator = {
        $loader: null,

        defaults: {
            loaderCls: 'js--loading-indicator',
            iconCls: 'icon-default',
            animationSpeed: 500,
            overlay: {}
        },

        options: {},

        open: function (options) {
            var me = this;

            me.options = $.extend({}, me.defaults, options);

            $.overlay.open(me.options.overlay);

            if (me.$loader === null) {
                me.$loader = me._createLoader();
                $('body').append(me.$loader);
            }

            me.$loader.fadeIn(me.options.animationSpeed);
        },

        close: function () {
            var me = this;

            $.overlay.close();

            if (me.$loader !== null) {
                me.$loader.fadeOut(me.options.animationSpeed || me.defaults.animationSpeed);
            }
        },

        _createLoader: function () {
            var me = this,
                loader = $('<div>', {
                    class: me.options.loaderCls
                }),
                icon = $('<div>', {
                    class: me.options.iconCls
                });

            loader.append(icon);

            return loader;
        }
    };
})(jQuery);