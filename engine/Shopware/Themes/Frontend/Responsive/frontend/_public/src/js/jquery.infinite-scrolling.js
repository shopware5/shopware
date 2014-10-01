;jQuery(function ($) {
    'use strict';

    /**
     * Parses the given {@link url} parameter and extracts all query parameters. If the parameter is numeric
     * it will automatically based to a {@link Number} instead of a {@link String}.
     * @private
     * @param {String} url - Usually {@link window.location.href}
     * @returns {{}} Object with all extracted parameters
     */
    var parseQueryString = function(url) {
        var qparams = {},
            parts = (url || '').split('?'),
            qparts, qpart,
            i=0;

        if(parts.length <= 1){
            return qparams;
        }

        qparts = parts[1].split('&');
        for (i in qparts) {
            var key, value;

            qpart = qparts[i].split('=');
            key = decodeURIComponent(qpart[0])
            value = decodeURIComponent(qpart[1] || '');
            qparams[key] = ($.isNumeric(value) ? parseFloat(value, 10) : value);
        }

        return qparams;
    };

    $.plugin('infiniteScrolling', {

        defaults: {

            /** @bool enabled - enable or disable infinite scrolling plugin */
            'enabled': true,

            /** @string event - default "scroll" will be used for triggering this plugin */
            'eventName': 'scroll',

            /** @int categoryId - category id is used for generating ajax request */
            'categoryId': 0,

            /** @string pagingSelector - listing paging selector **/
            'pagingSelector': '.listing--paging',

            /** @string defaultPerPageSelector - default per page selector which will be removed **/
            'defaultPerPageSelector': '.action--per-page',

            /** @string defaultChangeLayoutSelector - default change layout selecot which will be get a new margin **/
            'defaultChangeLayoutSelector': '.action--change-layout',

            /** @int threshold - after this threshold reached, auto fetching is disabled and the "load more" button is shown. */
            'threshold': 3,

            /** @string loadMoreCls - this class will be used for fetching further data by button. */
            'loadMoreCls': 'js--load-more',

            /** @string loadPreviousCls - this class  will be used for fetching previous data by button. */
            'loadPreviousCls': 'js--load-previous',

            /** @string Class will be used for load more or previous button */
            'loadBtnCls': 'btn btn--primary',

            /** @string loadMoreSnippet - this snippet will be printed inside the load more button */
            'loadMoreSnippet': 'Weitere Artikel laden',

            /** @string loadPreviousSnippet - this snippet will be printed inside the load previous button */
            'loadPreviousSnippet': 'Vorherige Artikel laden',

            /** @string listingActionsSelector - this class will be used for appending the load more button */
            'listingActionsSelector': '.listing--actions',

            /** @string listingActionsWrapper - this class will be cloned and used as a actions wrapper for the load more and previous button */
            'listingActionsWrapper': 'listing--actions block-group listing--load-more'
        },

        /**
         * Default plugin initialisation function.
         * Handle all logic and events for infinite scrolling
         *
         * @public
         * @method init
         */
        init: function() {
            var me = this,
                $body = $('body');

            // Overwrite plugin configuration with user configuration
            me.applyDataAttributes();

            // Check if plugin is enabled
            if(!me.opts.enabled) {
                return;
            }

            // Check if categoryId is set
            if(!me.opts.categoryId) {
                return;
            }

            // Remove paging
            $(me.opts.pagingSelector).remove();

            $(me.opts.defaultPerPageSelector).remove();

            $(me.opts.defaultChangeLayoutSelector).css('margin-left', '30%');

            // Check max pages by data attribute
            me.maxPages = me.$el.attr('data-pages');
            if(me.maxPages <= 1) {
                return;
            }

            // isLoading state for preventing double fetch same content
            me.isLoading = false;

            // isFinished state for disabling plugin if all pages rendered
            me.isFinished = false;

            // resetting fetch Count to prevent auto fetching after threshold reached
            me.fetchCount = 0;

            // previosPageIndex for loading in other direction
            me.previousPageIndex = 0;

            // use listing actions container for load more button
            me.actions = {
                'top': $(me.opts.listingActionsSelector).first(),
                'bottom': $(me.opts.listingActionsSelector).last()
            };

            // Prepare top and bottom actions containers
            me.buttonWrapperTop = $('<div>', {
                'class': me.opts.listingActionsWrapper
            });

            me.buttonWrapperBottom = $('<div>', {
                'class': me.opts.listingActionsWrapper
            });

            // append load more button
            me.actions.top.after(me.buttonWrapperTop);
            me.actions.bottom.before(me.buttonWrapperBottom);

            // remove bottom pagination
            me.actions.bottom.remove();

            // base url for push state and ajax fetch url
            me.baseUrl = window.location.href.split('?')[0];

            // Ajax configuration
            me.ajax = {
                'url': $.controller.ajax_listing,
                'params': parseQueryString(window.location.href)
            }

            // set page index to one if not assigned
            if(!me.ajax.params.p) {
                me.ajax.params.p = 1;
            }

            // set start page
            me.startPage = me.ajax.params.p;

            // register current push state var
            me.currentPushState;

            // Check if there is/are previous pages
            if(me.ajax.params.p && me.ajax.params.p > 1) {
                me.showLoadPrevious();
            }

            // Ajax first page to load on ul bottom reached
            if(!me.ajax.params.p) me.ajax.params.p = 2;

            // on scrolling event for auto fetching new pages and push state
            me._on(window, me.opts.eventName, $.proxy(me.onScrolling, me));

            // on load more button event for manually fetching further pages
            var loadMoreSelector = '.' + me.opts.loadMoreCls;
            $body.delegate(loadMoreSelector, 'click', $.proxy(me.onLoadMore, me));

            // on load previous button event for manually fetching previous pages
            var loadPreviousSelector = '.' + me.opts.loadPreviousCls;
            $body.delegate(loadPreviousSelector, 'click', $.proxy(me.onLoadPrevious, me));
        },

        /**
         * onScrolling method
         */
        onScrolling: function() {
            var me = this;

            // stop fetch new page if is loading atm
            if(me.isLoading) {
                return;
            }

            // Viewport height
            var $window = $(window),
                docTop = $window.scrollTop() + $window.height(),

                // Get last element in list to get the reference point for fetching new data
                fetchPoint = me.$el.find('li').last(),
                fetchPointOffset = fetchPoint.offset().top,
                bufferSize = fetchPoint.height(),
                triggerPoint = fetchPointOffset - bufferSize;

            if(docTop > triggerPoint) {
                me.fetchNewPage();
            }

            // collect all pages
            var $products = $('*[data-page-index]'),
                visibleProducts = $.grep($products, function(item) {
                return $(item).offset().top <= docTop;
            });

            // First visible Product
            var $firstProduct = $(visibleProducts).last(),
                tmpPageIndex = $firstProduct.attr('data-page-index');

            me.ajax.params.p = me.startPage;
            if(tmpPageIndex.length) {
                me.ajax.params.p = tmpPageIndex;
            }

            var tmpPushState = me.baseUrl + '?' + $.param(me.ajax.params);
            if(me.currentPushState != tmpPushState) {

                me.currentPushState = tmpPushState;

                if(!history || !history.pushState) {
                    return;
                }

                history.pushState('data', '', me.currentPushState);
            }
        },

        /**
         * fetchNewPage method
         */
        fetchNewPage: function() {
            var me = this;

            // Quit here if all pages rendered
            if(me.isFinished || me.ajax.params.p >= me.maxPages) {
                return;
            }

            // Stop automatic fetch if page threshold reached
            if(me.fetchCount >= me.opts.threshold) {
                var button = me.generateButton('next');

                // append load more button
                me.buttonWrapperBottom.html(button);

                // set finished flag
                me.isFinished = true;

                return;
            }

            me.isLoading = true;

            me.openLoadingIndicator();

            // increase page index for further page loading
            me.ajax.params.p++;

            // increase fetch count for preventing auto fetching
            me.fetchCount++;

            // use categoryid by settings if not defined by filters
            if(!me.ajax.params.c) me.ajax.params.c = me.opts.categoryId;

            // generate ajax fefch url by all params
            var url = me.ajax.url + '?' + $.param(me.ajax.params);

            $.get(url, function(data) {

                var template = data.trim();

                // Cancel is no data provided
                if(!template) {
                    me.isFinished = true;

                    me.closeLoadingIndicator();
                    return;
                }

                // append fetched data into listing
                me.$el.append(template);

                // trigger picturefill for regenerating thumbnail sizes
                picturefill();

                me.closeLoadingIndicator();

                // enable loading for further pages
                me.isLoading = false;
            });
        },

        generateButton: function(buttonType) {
            var me = this,
                type = buttonType || 'next',
                cls = (type == 'previous') ? me.opts.loadPreviousCls : me.opts.loadMoreCls,
                snippet = (type == 'previous') ? me.opts.loadPreviousSnippet : me.opts.loadMoreSnippet;

            return $('<div>', {
                'class': 'actions--buttons',
                'html': $('<a>', {
                    'class': me.opts.loadBtnCls + ' ' + cls,
                    'html': snippet + ' <i class="icon--cw"></i>'
                })
            });
        },

        /**
         * onLoadMore method
         *
         * @param event
         */
        onLoadMore: function(event) {
            event.preventDefault();

            var me = this;

            $('.' + me.opts.loadMoreCls).remove();

            // Set finished to false to reanable the fetch method
            me.isFinished = false;

            // Increase threshold for auto fetch next page if there is a next page
            if(me.maxPages >= me.opts.threshold) {
                me.opts.threshold++;
            }

            // Remove load more button
            $(me.opts.loadMoreSelector).remove();

            // fetching new page
            me.fetchNewPage();
        },

        /**
         * showLoadPrevius method
         *
         * Shows the load previous button
         */
        showLoadPrevious: function() {
            var me = this,
                button = me.generateButton('previous');

            // append load previous button
            me.buttonWrapperTop.html(button);
        },

        /**
         * onLoadPrevius method
         *
         * @param event
         *
         * will be triggered by load previous button
         */
        onLoadPrevious: function(event) {
            event.preventDefault();

            var me = this;

            // Remove load more button
            $('.' + me.opts.loadPreviousCls).remove();

            me.previousPageIndex = --me.ajax.params.p;

            // fetching new page
            me.openLoadingIndicator(top);

            // use categoryid by settings if not defined by filters
            if(!me.ajax.params.c) me.ajax.params.c = me.opts.categoryId;

            // generate ajax fefch url by all params
            var url = me.ajax.url + '?' + $.param(me.ajax.params);

            $.get(url, function(data) {
                var template = data.trim();

                // append fetched data into listing
                me.$el.prepend(template);

                picturefill();

                me.closeLoadingIndicator();

                // enable loading for further pages
                me.isLoading = false;

                // Set load previous button if we aren't already on page one
                if(me.ajax.params.p > 1) {
                    me.showLoadPrevious();
                }
            });
        },

        /**
         * openLoadingIndicator method
         *
         * opens the loading indicator relative
         */
        openLoadingIndicator: function(type) {
            var me = this,
                $indicator = $('.js--loading-indicator.indicator--relative');

            if($indicator.length) {
                return;
            }

            $indicator = $('<div>', {
                'class': 'js--loading-indicator indicator--relative',
                'html': $('<i>', {
                    'class': 'icon--default'
                })
            });

            if(!type) {
                me.$el.parent().after($indicator);
                return;
            }

            me.$el.parent().before($indicator);
        },

        /**
         * closeLoadingIndicator method
         *
         * close the relative loading indicator
         */
        closeLoadingIndicator: function() {
            var me = this,
            $indicator = $('.js--loading-indicator.indicator--relative');

            if(!$indicator.length) {
                return;
            }

            $indicator.remove();
        }
    });
});