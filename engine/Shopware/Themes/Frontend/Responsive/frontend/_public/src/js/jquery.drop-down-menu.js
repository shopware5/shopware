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
            var me = this;

            if($(event.target).is('.service--link, .compare--list, .compare--entry, .compare--link, .btn--item-delete, .compare--icon-remove')) {
                return;
            }

            if (me.opts.preventDefault) {
                event.preventDefault();
            }

            me.$el.toggleClass(me.opts.activeCls);

            if (me.opts.closeOnBody) {
                event.stopPropagation();
                $('body').on(me.getEventName('touchstart.dropdownMenu click.dropdownMenu'), $.proxy(me.onClickBody, me));
            }
        },

        onClickBody: function(event) {
            var me = this;

            if($(event.target).is('.service--link, .compare--list, .compare--entry, .compare--link, .btn--item-delete, .compare--icon-remove')) {
                return;
            }

            event.preventDefault();

            $('body').off('touchstart.dropdownMenu click.dropdownMenu');

            me.$el.removeClass(me.opts.activeCls);
        },

        destroy: function () {
            var me = this;

            me._destroy();
        }
    });
})(jQuery);