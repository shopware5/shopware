$.plugin('shippingPayment', {
    init: function () {
        var me = this;

        me.registerEvents();
    },

    registerEvents: function () {
        var me = this;

        me.$el.find('input.auto_submit[type=radio]').on('change', me.onInputChanged.bind(me));
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
                $('#confirm').empty().html(res);
                $.loadingIndicator.close();
                me.registerEvents();
            }
        })
    }
});