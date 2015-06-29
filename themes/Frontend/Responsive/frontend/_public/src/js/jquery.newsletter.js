;(function ($) {
    "use strict";

    $.plugin('newsletter', {
        init: function () {
            var me = this;

            me.$checkMail = me.$el.find('.newsletter--checkmail');
            me.$addionalForm = me.$el.find('.newsletter--additional-form');

            me._on(me.$checkMail, 'change', $.proxy(me.refreshAction, me));
            me.$checkMail.trigger('change');
        },

        refreshAction: function (event) {
            var me = this,
                $el = $(event.currentTarget),
                val = $el.val();

            if (val == -1) {
                me.$addionalForm.hide();
            } else {
                me.$addionalForm.show();
            }

            $.publish('plugin/newsletter/onRefreshAction', me);
        },

        destroy: function () {
            this._destroy();
        }
    });
}(jQuery));