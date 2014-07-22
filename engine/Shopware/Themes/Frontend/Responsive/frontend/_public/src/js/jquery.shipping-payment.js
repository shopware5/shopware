;(function($) {
    'use strict';

    $.plugin('shippingPayment', {
        init: function () {
            var me = this;

            me.registerEvents();
        },

        registerEvents: function () {
            var me = this;

            me.$el.delegate('input.auto_submit[type=radio]', 'change', $.proxy(me.onInputChanged, me));
        },

        onInputChanged: function () {
            var me = this,
                form = $('#shippingPaymentForm'),
                url = form.attr('action');

            $.loadingIndicator.open();

            $.ajax({
                type: "POST",
                url: url,
                data: $("#shippingPaymentForm").serialize(),
                success: function(res) {
                    me.$el.empty().html(res);
                    $.loadingIndicator.close();
                }
            })
        }
    });
})(jQuery);
