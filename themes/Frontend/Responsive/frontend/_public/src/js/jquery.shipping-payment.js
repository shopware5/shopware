;(function($) {
    'use strict';

    $.plugin('shippingPayment', {
        init: function () {
            var me = this;

            me.registerEvents();
        },

        registerEvents: function () {
            var me = this,
                isIE = me.isIE();


            me.$el.delegate('input.auto_submit[type=radio]', 'change', $.proxy(me.onInputChanged, me));

            // If the browser supports the feature, we don't need to take action
            if(!isIE) {
                return false;
            }

            me.$el.delegate('input[type=submit]', 'click', $.proxy(me.onSubmitForm, me));
        },

        onInputChanged: function () {
            var me = this,
                form = $('#shippingPaymentForm'),
                url = form.attr('action');

            $.loadingIndicator.open();

            $.ajax({
                type: "POST",
                url: url,
                data: $("#shippingPaymentForm").serialize() + '&isXHR=1',
                success: function(res) {
                    me.$el.empty().html(res);
                    $.loadingIndicator.close();
                }
            })
        },

        /**
         * Checks if we're dealing with the internet explorer.
         *
         * @private
         * @returns {Boolean} Truthy, if the browser supports it, otherwise false.
         */
        isIE: function() {
            var myNav = navigator.userAgent.toLowerCase();
            return myNav.indexOf('msie') != -1 || !!navigator.userAgent.match(/Trident.*rv[ :]*11\./);
        },

        /**
         * Event listener method which is necessary when the browser
         * doesn't support the ```form``` attribute on ```input``` elements.
         * @returns {boolean}
         */
        onSubmitForm: function() {
            var me = this,
                $form = me.$el.find('#shippingPaymentForm');

            // We can't find the form
            if(!$form.length) {
                return false;
            }

            $form.submit();
        }
    });
})(jQuery);
