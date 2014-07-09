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
                me.$list.find('.last-seen-products--container').append(me.createTemplate(products[i]));
            }

            me.$list.productSlider({
                wrapperClass: 'last-seen-products--slider',
                containerClass: 'last-seen-products--container',
                itemClass: 'last-seen-products--article',
                touchControl: true
            });
        },

        createTemplate: function (article) {
            var item = $('<div>', {
                'class': 'last-seen-products--article'
            });

            $('<a>', {
                'class': 'last-seen-products--thumbnail',
                'href': article.linkDetailsRewritten,
                'html': $('<img>', {
                    'class': 'last-seen-products--thumbnail-image',
                    'src': article.thumbnail
                })
            }).appendTo(item);

            $('<a>', {
                'rel': 'nofollow',
                'class': 'last-seen-products--description',
                'title': article.articleName,
                'href': article.linkDetailsRewritten,
                'html': article.articleName
            }).appendTo(item);

            return item;
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