;(function ($, window) {
    'use strict';

    /**
     * Maps key codes to specific key names.
     *
     * @type {Object}
     **/
    var keyMap = {
            'UP': 38,
            'DOWN': 40,
            'ENTER': 13
        },
        msPointerEnabled = window.navigator.msPointerEnabled;

    /**
     * Shopware Live Search Plugin.
     *
     * The plugin fires the ajax search request, render the results inside the modal
     * and controlling the keyboard navigation inside the search results.
     *
     * @example
     *  <input type="search" data-live-search="true">
     */
    $.plugin('liveSearch', {

        /**
         * Plugin default configuration
         *
         * @type {Object}
         */
        defaults: {
            /**
             * Minimum amount of characters needed to trigger the search request
             *
             * @type {Number}
             */
            minLength: 3,

            /**
             * Time in milliseconds to wait after each key down event before
             * before starting the search request.
             * If a key was pressed in this time, the last request will be aborted.
             *
             * @type {Number}
             */
            searchDelay: 250,

            /**
             * Class that will be used to determine if the result list or a result item is active.
             *
             * @type {String}
             */
            activeCls: 'js--is-active',

            /**
             * Selector for the search result list.
             *
             * @type {String}
             */
            resultsSelector: '.main-search--results',

            /**
             * Selector for the ajax loading indicator.
             *
             * @type {String}
             */
            loadingIndicatorSelector: '.form--ajax-loader',

            /**
             * Selector for a single result entry.
             *
             * @type {String}
             */
            resultItemSelector: '.result--item',

            /**
             * Selector for the main search form.
             * Has to be the parent of the plugin element.
             *
             * @type {String}
             */
            formSelector: '.main-search--form',

            /**
             * Selector for the link in a result entry.
             *
             * @type {String}
             */
            resultLinkSelector: '.search-result--link',

            /**
             * The URL used for the search request.
             * This option has to be set or an error will be thrown.
             *
             * @type {String}
             */
            requestUrl: '',

            /**
             * Flag whether or not the keyboard navigation is enabled
             *
             * @type {Boolean}
             */
            keyBoardNavigation: true,

            /**
             * The speed of all animations.
             *
             * @type {String|Number}
             */
            animationSpeed: 200
        },

        /**
         * Initializes the plugin and registers all needed events.
         */
        init: function () {
            var me = this,
                opts = me.opts,
                $el = me.$el;

            me.applyDataAttributes();

            me.requestURL = opts.requestUrl || $.controller.ajax_search;

            if (!me.requestURL) {
                throw new Error('Parameter "requestUrl" needs to be set.');
            }

            me.$parent = $el.parents(opts.formSelector);
            me.$results = me.$parent.next(opts.resultsSelector).hide();
            me.$loader = me.$parent.find(opts.loadingIndicatorSelector);
            me.lastSearchTerm = '';

            me._on($el, 'keyup', $.proxy(me.onKeyUp, me));
            me._on($el, 'keydown', $.proxy(me.onKeyDown, me));
            me._on($el, 'focusout', $.proxy(me.onBlur, me));
            me._on(me.$results, 'focusout', $.proxy(me.onBlur, me));

            if (msPointerEnabled) {
                me.$results.on('click', opts.resultLinkSelector, function (event) {
                    window.location.href = $(event.currentTarget).attr('href');
                });
            }
        },

        /**
         * Will be called when search field lost its focus.
         *
         * @param {jQuery.Event} event
         */
        onBlur: function (event) {
            var me = this;

            if ($.contains(me.$results[0], event.relatedTarget)) {
                return;
            }

            me.closeResult();
        },

        /**
         * Will be called when a key was released on the search field.
         */
        onKeyUp: function () {
            var me = this,
                term = me.$el.val() + '',
                timeout = me._keyupTimeout;

            if (timeout) {
                window.clearTimeout(timeout);
            }

            if (term.length < ~~(me.opts.minLength)) {
                me.lastSearchTerm = '';
                me.closeResult();
                return;
            }

            if (term === me.lastSearchTerm) {
                return;
            }

            me._keyupTimeout = window.setTimeout($.proxy(me.triggerSearchRequest, me, term), me.opts.searchDelay);
        },

        /**
         * Event handler method which will be fired when the user presses a key when
         * focusing the field.
         *
         * @param {jQuery.Event} event
         */
        onKeyDown: function (event) {
            var me = this,
                opts = me.opts,
                keyCode = event.which,
                navKeyPressed = opts.keyBoardNavigation && (keyCode === keyMap.UP || keyCode === keyMap.DOWN || keyCode === keyMap.ENTER);

            if (navKeyPressed && me.$results.hasClass(opts.activeCls)) {
                me.onKeyboardNavigation(keyCode);
                event.preventDefault();
                return false;
            }

            return true;
        },

        /**
         * Triggers an AJAX request with the given search term.
         *
         * @param {String} searchTerm
         */
        triggerSearchRequest: function (searchTerm) {
            var me = this;

            me.$loader.fadeIn(me.opts.animationSpeed);

            me.lastSearchTerm = $.trim(searchTerm);

            $.ajax({
                'url': me.requestURL,
                'data': {
                    'sSearch': me.lastSearchTerm
                },
                'success': function (response) {
                    me.showResult(response);
                    $.publish('plugin/liveSearch/onResponseSearchRequest', [ me, searchTerm ]);
                }
            });
        },

        /**
         * Clears the result list and appends the given (AJAX) response to it.
         *
         * @param {String} response
         */
        showResult: function (response) {
            var me = this,
                opts = me.opts;

            me.$loader.fadeOut(opts.animationSpeed);
            me.$results.empty().html(response).addClass(opts.activeCls).show();

            picturefill();

            $.publish('plugin/liveSearch/showResult', me);
        },

        /**
         * Closes the result list and removes all its items.
         */
        closeResult: function () {
            var me = this,
                opts = me.opts,
                $results = me.$results;

            $results.removeClass(opts.activeCls).hide().empty();

            $.publish('plugin/liveSearch/closeResult', me);
        },

        /**
         * Adds support to navigate using the keyboard.
         *
         * @param {Number} keyCode
         */
        onKeyboardNavigation: function (keyCode) {
            var me = this,
                opts = me.opts,
                $results = me.$results,
                activeClass = opts.activeCls,
                $selected = $results.find('.' + activeClass),
                $resultItems,
                $nextSibling,
                firstLast;

            if (keyCode === keyMap.UP || keyCode === keyMap.DOWN) {
                $resultItems = $results.find(opts.resultItemSelector);
                firstLast = (keyCode === keyMap.DOWN) ? 'first' : 'last';

                if (!$selected.length) {
                    $resultItems[firstLast]().addClass(activeClass);
                    return;
                }

                $resultItems.removeClass(activeClass);

                $nextSibling = $selected[(keyCode === keyMap.DOWN) ? 'next' : 'prev']();

                if ($nextSibling.length) {
                    $nextSibling.addClass(activeClass);
                    return;
                }

                $selected.siblings()[firstLast]().addClass(activeClass);
            }

            if (keyCode === keyMap.ENTER) {
                if ($selected.length) {
                    window.location.href = $selected.find(opts.resultLinkSelector).attr('href');
                    return;
                }

                me.$parent.submit();
            }
        },

        /**
         * Destroys the plugin.
         */
        destroy: function () {
            this._destroy();
        }
    });
})(jQuery, window);