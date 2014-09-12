;(function ($, window) {
    "use strict";

    /**
     * Shopware Search Field Plugin.
     *
     * The plugin controlling the search field behaviour in all possible states
     */
    $.plugin('searchFieldDropDown', {

        defaults: {
            /** @string activeCls Class which will be added when the drop down was triggered */
            activeCls: 'is--active',
            /** @string searchFieldCls Class which will be used for generating search results */
            searchFieldCls: 'main-search--field',
            /** @string checking if active class is set by default */
            activeOnStart: false
        },

        /**
         * Initializes the plugin
         *
         * @returns {Plugin}
         */
        init: function() {
            var me = this;

            //cache DOM
            me.$body = $('body');

            me.applyDataAttributes();

            StateManager.registerListener([{
                type: 'smartphone',
                enter: function() {
                    if ( me.opts.activeOnStart ) {
                        me.$el.addClass(me.defaults.activeCls);
                    }
                },
                exit: function() {
                    me.$el.removeClass(me.defaults.activeCls);
                }
            }]);

            if(me.$el.hasClass(me.defaults.activeCls)) {
                me.$body.addClass('is--active-searchfield');
            }

            me._on(me.$el, 'click', $.proxy(me.onClickSearchTrigger, me));
        },

        /**
         * onClickSearchTrigger event for displaying and hiding
         * the search field
         *
         * @param event
         */
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
                me.$body.removeClass('is--active-searchfield');
            } else {
                me.$el.addClass(me.opts.activeCls);
                me.$el.find(me.defaults.searchFieldCls).delay(150).focus();
                me.$body.addClass('is--active-searchfield');
            }
        },

        destroy: function() {
            var me = this;

            me._destroy();
        }
    });
})(jQuery, window);