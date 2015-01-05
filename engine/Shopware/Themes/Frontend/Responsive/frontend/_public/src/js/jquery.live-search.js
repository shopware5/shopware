;(function($, window, undefined) {
    "use strict";

    /**
     * Shopware Live Search Plugin.
     *
     * The plugin fires the ajax search request, render the results inside the modal
     * and controlling the keyboard navigation inside the search results.
     */
    $.plugin('liveSearch', {

        /** Your default options */
        defaults: {
            /** @int minLength minimum term characters which will be needed before the ajax request is triggered */
            minLength: 3,
            /** @int searchDelay time in miliseconds before ajax requests is triggered after last key down  */
            searchDelay: 250,
            /** @string activeCls Class which will be added when the drop down was triggered */
            activeCls: 'is-active',
            /** @string resultsCls Class which will contain the searchresults */
            resultsCls: 'main-search--results'
        },

        /**
         * Initializes the plugin
         *
         * @returns {Plugin}
         */
        init: function () {
            var me = this;

            me.$search = me.$el.closest('.entry--search');
            me.$results = me.$search.find('.main-search--results');
            me.$loader = me.$search.find('.form--ajax-loader');

            me._on(me.$el, 'keyup', $.proxy(me.onKeyUp, me));

            me._on(me.$el, 'blur', $.proxy(me.onBlur, me));

            me._on(me.$el, 'click', $.proxy(me.onClickSearchBar, me));

            me._on(me.$results, 'mousedown', $.proxy(me.onClickSearchResults, me));
        },

        /**
         * onKeyUp event for displaying search results
         * or trigger the keyboard navigation
         *
         * @param event
         */
        onKeyUp: function (event)  {
            var me = this,
                keyCode = event.which;

            // Enable keyboard navigation if results are visible and enter, arrow up or arrow down pressed
            if(me.$results.is(':visible') && (keyCode == 13 || keyCode == 38 || keyCode == 40)) {
                me.keyboardNavigation(event);
            } else {
                me.search();
            }
        },

        /**
         * keyboardNavigation function for navigation
         * inside the search results by keyboard
         *
         * @param event
         */
        keyboardNavigation: function (event) {
            var me = this,
                keyCode = event.which,
                selected = me.$results.find('.' + me.defaults.activeCls);

            // On arrow down
            if(keyCode == 40) {
                if(!selected.length) {
                    me.$results.find('li').first().addClass(me.defaults.activeCls);
                    return;
                }

                me.$results.find('li').removeClass(me.defaults.activeCls);
                if(selected.next().length != 0) {
                    selected.next().addClass(me.defaults.activeCls);
                    return;
                }

                selected.siblings().first().addClass(me.defaults.activeCls);
                return;
            }

            // On arrow up
            if(keyCode == 38) {
                if(!selected.length) {
                    me.$results.find('li').last().addClass(me.defaults.activeCls);
                    return;
                }

                me.$results.find('li').removeClass(me.defaults.activeCls);
                if (selected.prev().length != 0) {
                    selected.prev().addClass(me.defaults.activeCls);
                    return;
                }

                selected.siblings().last().addClass(me.defaults.activeCls);
                return;
            }

            // On enter without result
            if(keyCode == 13) {

                event.preventDefault();
                event.stopPropagation();

                if (selected.length) {
                    window.location.href = selected.find('a').attr('href');
                    return;
                }

                me.$el.closest('form').submit();
            }
        },

        /**
         * search function for ajax search request
         * and rendering the search results
         */
        search: function () {
            var me = this,
                term = me.$el.val(),
                termLength = term.length;

            if(me._timeout) {
                window.clearTimeout(me._timeout);
            }

            me._timeout = window.setTimeout(function() {

                // check for minimum characters
                if(me.defaults.minLength && termLength < me.defaults.minLength) {
                    me.$results.hide();
                    return;
                }

                me.$loader.fadeIn();

                $.ajax({
                    url: $.controller.ajax_search,
                    data: {
                        sSearch: term
                    }
                }).done(function(results) {

                    me.$loader.fadeOut();

                    if(!results) {
                        me.$results.hide();
                        return;
                    }

                    me.$results.html(results).show();
                });

            }, me.defaults.searchDelay);
        },

        /**
         * onBlur event for closing the search results modal
         * if the focus of the input is lost
         *
         * @param event
         */
        onBlur: function (event) {

            var me = this;
            if(!me.$results.is(':visible')) {
                return;
            }

            me.$results.hide();
        },

        /**
         * onClickSearchBar event for opening existing results
         *
         * @param event
         */
        onClickSearchBar: function (event) {
            var me = this,
                term = me.$el.val(),
                termLength = term.length;

            if(termLength) {
                me.$results.show();
            }
        },

        /**
         * onClickSearchResults event to prevent closing
         * the search results
         *
         * @param event
         */
        onClickSearchResults: function (event) {
            var $target = $(event.target);

            if(!$target.is('a')) {
                event.preventDefault();
                return;
            }

            location.href = $target.attr('href');
        },

        /** Destroys the plugin */
        destroy: function () {
            this._destroy();
        }
    });
})(jQuery, window);