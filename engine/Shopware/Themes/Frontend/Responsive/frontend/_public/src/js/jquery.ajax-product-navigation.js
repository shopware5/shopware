;(function($, window, document, undefined) {
   "use strict";

    var isNumeric = function(obj) {
        return !$.isArray(obj) && (obj - parseFloat(obj) + 1) >= 0;
    };

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
            qparams[key] = (isNumeric(value) ? parseFloat(value, 10) : value);
        }

        return qparams;
    };

    var listingSelectors = [
        '.listing .product--box .box--image',
        '.listing .product--box .product--title',
        '.listing .product--box .product--actions .action--more'
    ];

    $.plugin('ajaxProductNavigation', {
        defaults: {
            arrowAnimSpeed: 500,
            arrowOffset: -45
        },

        init: function() {
            var me = this;

            me._mode = (function() {
                if(me.$el.hasClass('is--ctl-listing')) {
                    return 'listing';
                } else if(me.$el.hasClass('is--ctl-detail')) {
                    return 'detail';
                }
                return undefined;
            })();

            if(!me._mode) {
                return false;
            }

            if(me._mode === 'listing') {
                me.registerListingEventListeners(listingSelectors);
            } else {
                var params = parseQueryString(window.location.href);

                // ...the url wasn't called through the listing
                if(!params.hasOwnProperty('c')) {
                    me.clearCurrentProductState();
                    return;
                }

                me.getProductNavigation();
                me.mapBackButton();
            }
        },

        mapBackButton: function() {
            var me = this,
                backBtn = me.$el.find('.breadcrumb--button .btn');

            if(!window.history.length) {
                backBtn.remove();
                return;
            }

            backBtn.on(me.getEventName('click'), function() {
                event.preventDefault();
                window.history.back();
            })
        },

        registerListingEventListeners: function(selectors) {
            var me = this;

            selectors = selectors.join(', ');
            me.$el.find(selectors).on(me.getEventName('click'), $.proxy(me.onProductLinkInListing, me));
        },

        onProductLinkInListing: function(event) {
            var me = this,
                params = parseQueryString(window.location.href),
                $target = $(event.target),
                $parent = $target.parents('.product--box'),
                categoryId = parseInt($parent.attr('data-category-id'), 10),
                orderNumber = $parent.attr('data-ordernumber');

            if(categoryId && isNumeric(categoryId) && !isNaN(categoryId)) {
                params.categoryId = categoryId;
            }

            if(orderNumber && orderNumber.length) {
                params.ordernumber = orderNumber;
            }

            me.saveCurrentProductState(params);
        },

        saveCurrentProductState: function(params) {
            try {
                window.sessionStorage.setItem('lastProductState', JSON.stringify(params));
                return true;
            } catch(err) {
                return false;
            }
        },

        restoreCurrentProductState: function() {
            try {
                return JSON.parse(window.sessionStorage.getItem('lastProductState'));
            } catch(err) {
                return {};
            }
        },

        refreshCurrentProductState: function() {
            var me = this,
                orderNumber = me.$el.find('.product--details').attr('data-ordernumber'),
                params = me.restoreCurrentProductState();

            if(orderNumber && orderNumber.length) {
                params.ordernumber = orderNumber;
            }
            me.saveCurrentProductState(params);

            return params;
        },

        clearCurrentProductState: function() {
            try {
                window.sessionStorage.removeItem('lastProductState');
                return true;
            } catch(err) {
                return false;
            }
        },

        getProductNavigation: function() {
            var me = this,
                params = me.refreshCurrentProductState(),
                url;

            if($.isEmptyObject(params)) {
                return false;
            }
            url = me.$el.find('.product--details').attr('data-product-navigation');

            if(!url || !url.length) {
                return false;
            }

            $.ajax({
                'url': url,
                'data': params,
                'method': 'GET',
                'dataType': 'json',
                'success': $.proxy(me.setProductNavigation, me)
            })
        },

        setProductNavigation: function(response) {
            var me = this,
                prevLink = me.$el.find('a.navigation--link.link--prev'),
                nextLink = me.$el.find('a.navigation--link.link--next');

            if(response.hasOwnProperty('previousProduct')) {
                var previousProduct = response.previousProduct;

                prevLink
                    .attr('href', previousProduct.href)
                    .attr('title', previousProduct.name)
                    .animate({
                        'opacity': 1
                    }, me.opts.arrowAnimSpeed);
            } else {
                prevLink.remove();
            }

            if(response.hasOwnProperty('nextProduct')) {
                var nextProduct = response.nextProduct;

                nextLink
                    .attr('href', nextProduct.href)
                    .attr('title', nextProduct.name)
                    .animate({
                        'opacity': 1
                    }, me.opts.arrowAnimSpeed);
            } else {
                nextLink.remove();
            }

            return true;
        }
    });

    $(function() {
        $('body').ajaxProductNavigation();
    });
})(jQuery, window, document);