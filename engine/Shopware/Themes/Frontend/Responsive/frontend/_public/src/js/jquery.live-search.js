;(function($, window, undefined) {
    "use strict";

    $.plugin('liveSearch', {

        /** Your default options */
        defaults: {
            minLength: 3,
            searchDelay: 250,
            keyEnter: 13,
            keyArrowUp: 38,
            keyArrowDown: 40,
            activeCls: 'is-active',
            resultsCls: 'main-search--results'
        },

        /** Plugin constructor */
        init: function () {
            var me = this;

            me.$search = me.$el.closest('.entry--search');
            me.$results = me.$search.find('.main-search--results');
            me.$loader = me.$search.find('.form--ajax-loader');

            /** Register event listener */
            me._on(me.$el, 'keyup', $.proxy(me.onKeyUp, me));

            me._on(me.$el, 'blur', $.proxy(me.onBlur, me));

            me._on(me.$el, 'click', $.proxy(me.onClickSearchBar, me));

            me._on(me.$results, 'mousedown', $.proxy(me.onClickSearchResults, me));
        },

        /** Event listener method */
        onKeyUp: function (event)  {
            var me = this,
                term = me.$el.val(),
                termLength = term.length;

            if(me.$results.is(':visible')) {

                if(event.keyCode == me.defaults.keyArrowDown) {
                    var selected = me.$results.find('.' + me.defaults.activeCls);

                    $("." + me.defaults.resultsCls + " li").removeClass(me.defaults.activeCls);
                    if (selected.next().length == 0) {
                        selected.siblings().first().addClass(me.defaults.activeCls);
                    } else {
                        selected.next().addClass(me.defaults.activeCls);
                    }

                    return;
                }

                if(event.keyCode == me.defaults.keyArrowUp) {
                    var selected = me.$results.find('.' + me.defaults.activeCls);

                    $("." + me.defaults.resultsCls + " li").removeClass(me.defaults.activeCls);
                    if (selected.prev().length == 0) {
                        selected.siblings().last().addClass(me.defaults.activeCls);
                    } else {
                        selected.prev().addClass(me.defaults.activeCls);
                    }

                    return;
                }

                if(event.keyCode == me.defaults.keyEnter) {
                    var selected = me.$results.find('.' + me.defaults.activeCls);

                    event.preventDefault();

                    location.href = selected.find('a').attr('href');
                    return;
                }
            }

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