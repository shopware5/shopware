;(function($) {
    'use strict';

    $.plugin('dropdownMenu', {
        defaults: {
            activeCls: 'js--is--dropdown-active',
            preventDefault: true,
            closeOnBody: true
        },

        init: function () {
            var me = this;

            me._on(me.$el, 'touchstart click', $.proxy(me.onClickMenu, me));
        },

        onClickMenu: function (event) {
            var me = this,
                touchEvent = me.getEventName('touchstart'),
                clickEvent = me.getEventName('click');

            if ($(event.target).hasClass('service--link')) {
                return;
            }

            if (me.opts.preventDefault) {
                event.preventDefault();
            }

            me.$el.toggleClass(me.opts.activeCls);

            if (me.opts.closeOnBody) {
                event.stopPropagation();
                $('body').one(clickEvent + ' ' + touchEvent, $.proxy(me.onClickBody, me));
            }
        },

        onClickBody: function(event) {
            var me = this;

            if ($(event.target).hasClass('service--link')) {
                return;
            }

            event.preventDefault();

            me.$el.removeClass(me.opts.activeCls);
        },

        destroy: function () {
            var me = this;

            me._destroy();
        }
    });
})(jQuery);