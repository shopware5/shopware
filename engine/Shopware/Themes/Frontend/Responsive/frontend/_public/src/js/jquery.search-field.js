;(function ($, window) {
    "use strict";

    $.plugin('searchFieldDropDown', {

        defaults: {
            /** @string activeCls Class which will be added when the drop down was triggered */
            activeCls: 'is--active',
            searchFieldCls: 'main-search--field'
        },

        init: function () {
     
            var me = this,
                isTouch = ('ontouchstart' in window) || (navigator.msMaxTouchPoints > 0),
                clickEvt = (isTouch ? (window.navigator.msPointerEnabled ? 'MSPointerDown' : 'touchstart') : 'mousedown');

            StateManager.registerListener([{
                type: 'smartphone',
                enter: function() {
                    me.$el.addClass(me.defaults.activeCls);
                },
                exit: function() {
                    me.$el.removeClass(me.defaults.activeCls);
                }
            }]);

            me._on(me.$el, 'click', $.proxy(me.onClickSearchTrigger, me));
        },

        onClickSearchTrigger: function(event) {
            var me = this;
            var target = $(event.target);
            event.preventDefault();
            event.stopPropagation();

            if(target.hasClass(me.defaults.searchFieldCls) || !StateManager.isSmartphone()) {
                return;
            }

            if(me.$el.hasClass(me.opts.activeCls)) {
                me.$el.removeClass(me.opts.activeCls);
                me.$el.find(me.defaults.searchFieldCls).delay(150).blur();
            } else {
                me.$el.addClass(me.opts.activeCls);
                me.$el.find(me.defaults.searchFieldCls).delay(150).focus();
            }
        }
    });
})(jQuery, window);