;(function($) {
    "use strict";

    $.plugin('dropdownMenu', {
        defaults: {
            activeCls: 'js--is--dropdown-active',
            preventDefault: true,
            closeOnBody: true
        },

        init: function () {
            var me = this;

            me._on(me.$el, 'click', $.proxy(me.onClickMenu, me));
        },

        onClickMenu: function (event) {
            var me = this;

            if (me.opts.preventDefault) {
                event.preventDefault();
            }

            if (me.$el.hasClass(me.opts.activeCls)) {
                me.$el.removeClass(me.opts.activeCls);
            } else {
                me.$el.addClass(me.opts.activeCls);
            }

            if (me.opts.closeOnBody) {
                event.stopPropagation();
                $('body').one(me.getEventName('click'), $.proxy(me.onClickBody, me));
            }
        },

        onClickBody: function() {
            var me = this;

            me.$el.removeClass(me.opts.activeCls);
        },

        destroy: function () {
            var me = this;

            me._destroy();
        }
    });
})(jQuery);