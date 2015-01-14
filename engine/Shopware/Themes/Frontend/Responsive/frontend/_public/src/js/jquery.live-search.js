;(function($, window, undefined) {
    "use strict";

    /** @object keyMap Maps key codes to the associated function */
    var keyMap = {
        'UP': 38,
        'DOWN': 40,
        'ENTER': 13
    };

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

        /** @object Plugin default configuration */
        defaults: {
            /** @int minLength minimum term characters which will be needed before the ajax request is triggered */
            minLength: 3,

            /** @int searchDelay time in milliseconds before ajax requests is triggered after last key down  */
            searchDelay: 250,

            /** @string activeCls Class which will be added when the drop down was triggered */
            activeCls: 'js--is-active',

            /** @string resultsSelector Selector which will contain the search results */
            resultsSelector: '.main-search--results',

            /** @string loadingIndicatorSelector Selector of the ajax loading indicator */
            loadingIndicatorSelector: '.form--ajax-loader',

            /** @string requestUrl The endpoint which will be triggered by the live search */
            requestUrl: '',

            /** @boolean Truthy to enable keyboard navigation, if falsy the keyboard navigation will be disabled */
            enableKeyboardNavigation: true,

            /** @string|integer animationSpeed The speed of the fading animation */
            animationSpeed: 'fast'
        },

        /**
         * Initializes the plugin
         *
         * @returns {Plugin}
         */
        init: function () {
            var me = this;

            me.opts.requestUrl = $.controller.ajax_search || '';

            if (!me.opts.requestUrl.length) {
                throw new Error('Parameter "requestUrl" needs to be set.');
                return false;
            }

            me._lastSearchTerm = '';
            me.$parent = me.$el.parent('form');
            me.$results = me.$parent.next(me.opts.resultsSelector).hide();
            me.$loader = me.$parent.find(me.opts.loadingIndicatorSelector);

            me._on(me.$el, 'keyup', $.proxy(me.onKeyUp, me));
            me._on(me.$el, 'keydown', $.proxy(me.onKeyDown, me));
            me._on(me.$el, 'blur', $.proxy(me.onBlur, me));
        },

        onBlur: function (event) {
            var me = this,
                target = event.target || event.currentTarget;

            if($.contains(me.$results[0], target)) {
                return;
            }
            me.closeResult();
        },

        onKeyUp: function (event) {
            var me = this,
                term = me.$el.val();
            
            if (me._keyupTimeout) {
                window.clearTimeout(me._keyupTimeout);
            }

            if (me.opts.minLength && term.length < me.opts.minLength) {
                me._lastSearchTerm = '';
                me.closeResult();
                return;
            }

            if(term === me._lastSearchTerm) {
                return;
            }

            me._keyupTimeout = window.setTimeout($.proxy(me.triggerSearchRequest, me, term), me.opts.searchDelay);
        },

        /**
         * Event handler method which will be fired when the user presses a key when
         * focusing the field.
         *
         * @param event {jQuery.Event}
         */
        onKeyDown: function (event) {
            var me = this,
                keyCode = event.which,
                shouldPrevent = me.opts.enableKeyboardNavigation && (keyCode === keyMap.UP || keyCode === keyMap.DOWN || keyCode === keyMap.ENTER);

            if (shouldPrevent) {
                event.preventDefault();
            }

            if(me.$results.hasClass(me.opts.activeCls)) {
                me.onKeyboardNavigation(keyCode);
            }

            return !shouldPrevent;
        },

        /**
         * Triggers an AJAX request with the given {@param searchTerm}.
         *
         * @param {String} searchTerm
         */
        triggerSearchRequest: function (searchTerm) {
            var me = this;

            searchTerm = me._sanitizeSearchTerm(searchTerm);

            me.$loader.fadeIn('fast');

            me._lastSearchTerm = searchTerm;

            $.ajax({
                'url': me.opts.requestUrl,
                'data': {
                    'sSearch': searchTerm
                },
                'success': $.proxy(me.showResult, me)
            });
        },

        /**
         * Shows the {@param response} of the AJAX request and slides down the results container.
         *
         * @param {Object} response
         */
        showResult: function (response) {
            var me = this;

            me.$loader.fadeOut('fast');
            me.$results.empty().html(response).slideDown().addClass(me.opts.activeCls);
        },

        /**
         * Closes the results container.
         *
         * @returns {Void}
         */
        closeResult: function() {
            var me = this;

            me.$results.removeClass(me.opts.activeCls).fadeOut(me.opts.animationSpeed, function() {
                me.$results.empty();
            });
        },

        /**
         * Adds support to navigate using the keyboard.
         *
         * @param {Number} key - Keycode number
         */
        onKeyboardNavigation: function(key) {
            var me = this,
                selected = me.$results.find('.' + me.defaults.activeCls),
                form = me.$el.closest('form');

            if (key === keyMap.DOWN) {
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

            if (key === keyMap.UP) {
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

            if(key == keyMap.ENTER) {

                if (selected.length) {
                    
                    window.location.href = selected.find('a').attr('href');
                    return;
                }

                form.submit();
            }
        },

        /**
         * Sanitize the search result like trimming the search term.
         *
         * @param {String} term - String which should be sanitized
         * @returns {String} Sanitized string
         * @private
         */
        _sanitizeSearchTerm: function (term) {
            return $.trim(term);
        },

        /**
         * Destroys the plugin.
         *
         * @returns {Void}
         */
        destroy: function () {
            this._destroy();
        }
    });
})(jQuery, window);