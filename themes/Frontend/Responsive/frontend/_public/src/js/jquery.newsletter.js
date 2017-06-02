;(function ($) {
    'use strict';

    $.plugin('swNewsletter', {

        defaults: {
            unsubscribeCaptchaRequired: false
        },

        init: function () {
            var me = this;

            me.applyDataAttributes();

            me.$checkMail = me.$el.find('.newsletter--checkmail');
            me.$addionalForm = me.$el.find('.newsletter--additional-form');
            me.$captchaForm = me.$el.find('.newsletter--captcha-form');
            me.$captchaField = me.$el.find('[name="sCaptcha"]');

            me._on(me.$checkMail, 'change', $.proxy(me.refreshAction, me));

            $.publish('plugin/swNewsletter/onRegisterEvents', [ me ]);

            me.$checkMail.trigger('change');
        },

        refreshAction: function (event) {
            var me = this,
                $el = $(event.currentTarget),
                val = $el.val();

            if (val == -1) {
                me.$addionalForm.hide();
                if (!me.opts.unsubscribeCaptchaRequired) {
                    me.$captchaForm.hide();
                    me.$captchaField.removeAttr('required');
                }
            } else {
                me.$addionalForm.show();
                if (!me.opts.unsubscribeCaptchaRequired) {
                    me.$captchaForm.show();
                    me.$captchaField.attr('required', true);
                }
            }

            $.publish('plugin/swNewsletter/onRefreshAction', [ me ]);
        },

        destroy: function () {
            this._destroy();
        }
    });
}(jQuery));
