;(function ($) {
    "use strict";

    $.overlay = {
        $overlay: null,

        defaults: {
            color: '#555555',
            opacity: 0.8,
            animationSpeed: 500,
            closeOnClick: false
        },

        options: {},

        open: function (options) {
            var me = this;

            me.options = $.extend({}, me.defaults, options);

            if (me.$overlay === null) {
                me.$overlay = $('<div>', {
                    class: 'js--overlay'
                });

                $('body').append(me.$overlay);
            }

            me._updateOverlay();

            me.$overlay.fadeIn(me.options.animationSpeed);
        },

        close: function () {
            var me = this;

            if (me.$overlay !== null) {
                me.$overlay.fadeOut(me.options.animationSpeed || me.defaults.animationSpeed);
            }
        },

        _updateOverlay: function () {
            var me = this,
                opts = me.options;

            me.$overlay.css({
                background: opts.color,
                opacity: opts.opacity,
                cursor: opts.closeOnClick ? 'pointer' : 'default'
            });

            if (opts.closeOnClick) {
                me.$overlay.on('click', me.close.bind(me));
            } else {
                me.$overlay.off('click');
            }
        }
    };
})(jQuery);