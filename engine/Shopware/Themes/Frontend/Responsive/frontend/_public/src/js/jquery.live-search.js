;(function($, window, undefined) {
    "use strict";

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

        /** Plugin constructor */
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

        /** Event listener method */
        onKeyUp: function (event)  {
            var me = this;

            // Enable keyboard navigation if results are visible and enter, arrow up or arrow down pressed
            if(me.$results.is(':visible') && (event.keyCode == 13 || event.keyCode == 38 || event.keyCode == 40)) {
                me.keyboardNavigation(event);
            } else {
                me.search();
            }
        },
        
        keyboardNavigation: function (event) {
            var me = this;
            var selected = me.$results.find('.' + me.defaults.activeCls);

            // On arrow down
            if(event.keyCode == 40) {
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
            if(event.keyCode == 38) {
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

            // On enter
            if(event.keyCode == 13 && selected.length) {
                event.preventDefault();

                window.location.href = selected.find('a').attr('href');
            }
        },
        
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

        // Close search results if input focus is lost
        onBlur: function (event) {

            var me = this;
            if(!me.$results.is(':visible')) {
                return;
            }

            me.$results.hide();
        },

        onClickSearchBar: function (event) {
            var me = this,
                term = me.$el.val(),
                termLength = term.length;

            if(termLength) {
                me.$results.show();
            }
        },

        // Prevent closing search results
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