;(function ($) {
    $.plugin('lastSeenProducts', {

        defaults: {
            productLimit: 20,
            title: '',
            baseUrl: '/',
            shopId: 1,
            currentArticle: { },
            listSelector: '.last-seen-products--slider',
            containerSelector: '.last-seen-products--container',
            itemCls: 'last-seen-products--item product--box',
            titleCls: 'last-seen-products--title product--title',
            imageCls: 'last-seen-products--image product--image',
            noPicture: ''
        },

        init: function () {
            var me = this;

            me.applyDataAttributes();

            me.$list = me.$el.find(me.opts.listSelector);
            me.$container = me.$list.find(me.opts.containerSelector);

            me.productSlider = me.$list.data('plugin_productSlider');

            if (!me.productSlider) {
                return;
            }

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
            me.productSlider.checkActiveState();
            me.productSlider.trackArrows();
            me.productSlider.setSizes();
        },

        createTemplate: function (article) {
            var me = this;

            return $('<div>', {
                'class': me.opts.itemCls,
                'html': [
                    me.createProductImage(article),
                    me.createProductTitle(article)
                ]
            });
        },

        createProductTitle: function (data) {
            var me = this;

            return $('<a>', {
                'rel': 'nofollow',
                'class': me.opts.titleCls,
                'title': data.articleName,
                'href': data.linkDetailsRewritten,
                'html': data.articleName
            });
        },

        createProductImage: function (data) {
            var me = this,
                image = data.images[4] || me.opts.noPicture,
                element,
                imageEl,
                noScript;

            element = $('<a>', {
                'class': me.opts.imageCls,
                'href': data.linkDetailsRewritten
            });

            imageEl = $('<span>', {
                'data-picture': 'true',
                'class': 'image--element',
                'data-alt': data.articleName
            }).appendTo(element);

            $('<span>', {
                'class': 'image--media',
                'data-src': image
            }).appendTo(imageEl);

            $('<span>', {
                'class': 'image--media',
                'data-src': image,
                'data-media': '(min-width: 48em)'
            }).appendTo(imageEl);

            $('<span>', {
                'class': 'image--media',
                'data-src': image,
                'data-media': '(min-width: 78.75em)'
            }).appendTo(imageEl);

            noScript = $('<noscript></noscript>').appendTo(imageEl);

            $('<img>', {
                'src': image,
                'alt': data.articleName
            }).appendTo(noScript);

            return element;
        },

        collectProduct: function (newProduct) {
            var me = this,
                opts = me.opts,
                itemKey = 'lastSeenProducts-' + opts.shopId + '-' + opts.baseUrl,
                productsJson = me.storage.getItem(itemKey),
                products = productsJson ? $.parseJSON(productsJson) : [],
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