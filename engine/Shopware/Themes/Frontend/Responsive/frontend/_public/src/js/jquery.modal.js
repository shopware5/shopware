;(function ($) {
    $.plugin('modal', {
        defaults: {
            animationSpeed: 500,
            closeableOverlay: true,
            overlay: {}
        },

        init: function () {
            var me = this;

            me._on(me.$el, 'click', function (event) {
                event.preventDefault();

                $.modal.open(me.$el.clone(true), me.opts);
            });
        }
    });

    $.modal = {
        $modalBox: null,

        defaults: {
            animationSpeed: 500,
            closeableOverlay: true,
            overlay: {}
        },

        options: {},

        open: function (content, options) {
            var me = this;

            me.options = $.extend({}, me.defaults, options);

            if(me.options.closeableOverlay) {
                me.options.overlay.closeOnClick = true;
            }

            $.overlay.open(me.options.overlay);

            if(me.options.closeableOverlay) {
                $.overlay.$overlay.click(me.close.bind(me));
            }

            if (me.$modalBox === null) {
                me.$modalBox = $('<div>', {
                    class: 'js--modal'
                });
                $('body').append(me.$modalBox);
            }

            me.$modalBox.html(content);

            me.$modalBox.css({
                marginLeft: (me.$modalBox.width() / 2) * -1,
                marginTop: (me.$modalBox.height() / 2) * -1
            });

            me.$modalBox.fadeIn(me.options.animationSpeed);
        },

        load: function (ajaxUrl, modalOptions, data, settingsOverride) {
            var me = this,
                settings = $.extend({
                    data: data || {},
                    success: function (response, status, xhr) {
                        me.open(response, modalOptions);
                    }
                }, settingsOverride);

            $.ajax(ajaxUrl, settings);
        },

        close: function () {
            var me = this;

            $.overlay.close();

            if (me.$modalBox !== null) {
                me.$modalBox.fadeOut(me.options.animationSpeed);
            }
        }
    }
})(jQuery);

