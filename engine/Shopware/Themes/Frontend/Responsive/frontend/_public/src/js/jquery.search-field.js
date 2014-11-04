;(function ($, StateManager) {
    'use strict';

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
            me.$mainHeader = $('.header-main');
            me.$toggleSearchBtn = $(".entry--search > .entry--trigger");

            me.applyDataAttributes();

            StateManager.registerListener([{
                state: 'xs',
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
                me.$mainHeader.addClass('is--active-searchfield');
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
            var me = this,
                target = $(event.target),
                $el = me.$el,
                opts = me.opts,
                $searchField = $el.find(opts.searchFieldCls),
                toggleState;

            event.preventDefault();
            event.stopPropagation();

            if(target.hasClass(me.defaults.searchFieldCls) || !StateManager.isCurrentState('xs')) {
                return;
            }

            toggleState = !$el.hasClass(opts.activeCls);

            $el.toggleClass(opts.activeCls, toggleState);
            me.$toggleSearchBtn.toggleClass(opts.activeCls, toggleState);
            me.$mainHeader.toggleClass('is--active-searchfield', toggleState);

            $searchField.delay(150);

            if($el.hasClass(opts.activeCls)) {
                $searchField.blur();
                return;
            }

            $searchField.focus();
        },

        destroy: function() {
            var me = this;

            me._destroy();
        }
    });
})(jQuery, StateManager);