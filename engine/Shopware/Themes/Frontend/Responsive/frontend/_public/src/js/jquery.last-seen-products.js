;(function ($) {
    $.plugin('lastSeenProducts', {

        defaults: {
            productLimit: 20,
            title: '',
            baseUrl: '/',
            shopId: 1,
            currentArticle: { }
        },

        init: function () {
            var me = this;

            me.applyDataAttributes();

            me.$title = $('<h2>', {
                'html': me.opts.title
            }).prependTo(me.$el);

            me.$list = me.$el.find('.last-seen-products--slider');
            me.$container = me.$list.find('.last-seen-products--container');

            me.productSlider = me.$list.data('plugin_productSlider');

            me.storage = StorageManager.getLocalStorage();

            if ($('body').hasClass('is--ctl-detail')) {
                me.collectProduct(me.opts.currentArticle);
            }

            me.createProductList();
        },

        createProductList: function () {
            var me = this,
                opts = me.opts,
                itemKey = 'lastSeenProducts-' + opts.shopId + '-' + opts.baseUrl,
                productsJson = me.storage.getItem(itemKey),
                products = productsJson ? JSON.parse(productsJson) : [],
                len = Math.min(opts.productLimit, products.length),
                i = 0;

            for (; i < len; i++) {
                me.$container.append(me.createTemplate(products[i]));
            }

            me.productSlider.trackItems();
            me.productSlider.setSizes();
        },

        createTemplate: function (article) {
            var me = this, item,
                image = me.createProductImage(article),
                title = me.createProductTitle(article);

            item = $('<div>', {
                'class': 'last-seen-products--item product-slider--item'
            });

            image.appendTo(item);
            title.appendTo(item);

            return item;
        },

        createProductTitle: function(data) {
            return $('<a>', {
                'rel': 'nofollow',
                'class': 'last-seen-products--title product--title',
                'title': data.articleName,
                'href': data.linkDetailsRewritten,
                'html': data.articleName
            });
        },

        createProductImage: function(data) {
            var element, imageEl,
                noScript,
                imageDefault,
                imageMobile,
                imageTablet,
                imageDesktop;

            element = $('<a>', {
                'class': 'last-seen-products--image product--image',
                'href': data.linkDetailsRewritten
            });

            imageEl = $('<span>', {
                'data-picture': 'true',
                'class': 'image--element',
                'data-alt': data.articleName
            }).appendTo(element);

            imageMobile = $('<span>', {
                'class': 'image--media',
                'data-src': data.images[4]
            }).appendTo(imageEl);

            imageTablet = $('<span>', {
                'class': 'image--media',
                'data-src': data.images[3],
                'data-media': '(min-width: 48em)'
            }).appendTo(imageEl);

            imageDesktop = $('<span>', {
                'class': 'image--media',
                'data-src': data.images[2],
                'data-media': '(min-width: 78.75em)'
            }).appendTo(imageEl);

            noScript = $('<noscript></noscript>').appendTo(imageEl);

            imageDefault = $('<img>', {
                'src': data.images[2],
                'alt': data.articleName
            }).appendTo(noScript);

            return element;
        },

        /**
         * Formats a string and replaces the placeholders.
         *
         * @example format('<div class="%0"'>%1</div>, [value for %0], [value for %1], ...)
         */
        format: function(str) {
            for (var i = 1; i < arguments.length; i++) {
                str = str.replace('%' + (i - 1), arguments[i]);
            }
            return str;
        },

        collectProduct: function (newProduct) {
            var me = this,
                opts = me.opts,
                itemKey = 'lastSeenProducts-' + opts.shopId + '-' + opts.baseUrl,
                productsJson = me.storage.getItem(itemKey),
                products = productsJson ? JSON.parse(productsJson) : [],
                len = products.length,
                i = 0,
                url;

            if (!newProduct || $.isEmptyObject(newProduct)) {
                return;
            }

            for (; i < len; i++) {
                if (products[i].articleId === newProduct.articleId) {
                    newProduct = products.splice(i, 1)[0];
                    break;
                }
            }

            url = newProduct.linkDetailsRewritten;

            // Remove query string from article url
            if (url.indexOf('/sCategory') !== -1) {
                newProduct.linkDetailsRewritten = url.substring(0, url.indexOf('/sCategory'));
            } else if (url.indexOf('?') !== -1) {
                newProduct.linkDetailsRewritten = url.substring(0, url.indexOf('?'));
            }

            products.splice(0, 0, newProduct);

            while (products.length > opts.productLimit) {
                products.pop();
            }

            me.storage.setItem(itemKey, JSON.stringify(products));
        }
    });
}(jQuery));