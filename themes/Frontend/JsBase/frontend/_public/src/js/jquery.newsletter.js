;(function ($) {
    'use strict';

    $.plugin('swNewsletter', {

        defaults: {
            unsubscribeCaptchaRequired: false,

            captchaSelector: 'input[name="sCaptcha"]',

            captchaFormSelector: '.newsletter--captcha-form',

            checkMailSelector: '.newsletter--checkmail',

            additionalFormSelector: '.newsletter--additional-form'
        },

        init: function () {
            var me = this;

            me.applyDataAttributes();

            me.$checkMail = me.$el.find(me.opts.checkMailSelector);
            me.$addionalForm = me.$el.find(me.opts.additionalFormSelector);
            me.$captchaForm = me.$el.find(me.opts.captchaFormSelector);

            me._on(me.$checkMail, 'change', $.proxy(me.refreshAction, me));
            $.subscribe(me.getEventName('plugin/swCaptcha/onSendRequestSuccess'), $.proxy(me.onCaptchaLoaded, me));

            $.publish('plugin/swNewsletter/onRegisterEvents', [ me ]);

            me.$checkMail.trigger('change');
        },

        refreshAction: function (event) {
            var me = this,
                $el = $(event.currentTarget),
                val = $el.val();

            if (val === '-1') {
                me.$addionalForm.hide();
                if (!me.opts.unsubscribeCaptchaRequired) {
                    me.$captchaForm.hide();
                    if (me.$captchaField) {
                        me.$captchaField.removeAttr('required');
                        me.$captchaField.removeAttr('aria-required');
                    }
                }
            } else {
                me.$addionalForm.show();
                if (!me.opts.unsubscribeCaptchaRequired) {
                    me.$captchaForm.show();
                    if (me.$captchaField) {
                        me.$captchaField.attr('required', true);
                        me.$captchaField.attr('aria-required', true);
                    }
                }
            }

            $.publish('plugin/swNewsletter/onRefreshAction', [ me ]);
        },

        onCaptchaLoaded: function () {
            var me = this;
            me.$captchaField = me.$captchaForm.find(me.opts.captchaSelector);
            me.$checkMail.trigger('change');
        },

        destroy: function () {
            this._destroy();
        }
    });
}(jQuery));
