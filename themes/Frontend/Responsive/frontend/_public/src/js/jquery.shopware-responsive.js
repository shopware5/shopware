(function($, window) {

    window.StateManager.init([
        {
            state: 'xs',
            enter: 0,
            exit: 29.9375   // 479px
        },
        {
            state: 's',
            enter: 30,      // 480px
            exit: 47.9375   // 767px
        },
        {
            state: 'm',
            enter: 48,      // 768px
            exit: 63.9375   // 1023px
        },
        {
            state: 'l',
            enter: 64,      // 1024px
            exit: 78.6875   // 1259px
        },
        {
            state: 'xl',
            enter: 78.75,   // 1260px
            exit: 322.5     // 5160px
        }
    ]);

    $(function($) {

        window.StateManager

            // OffCanvas menu
            .addPlugin('*[data-offcanvas="true"]', 'swOffcanvasMenu', ['xs', 's'])

            // Search field
            .addPlugin('*[data-search="true"]', 'swSearch')

            // Scroll plugin
            .addPlugin('.btn--password, .btn--email', 'swScrollAnimate', ['xs', 's', 'm'])

            // Collapse panel
            .addPlugin('.btn--password, .btn--email', 'swCollapsePanel', ['l', 'xl'])

            // Slide panel
            .addPlugin('.footer--column .column--headline', 'swCollapsePanel', {
                contentSiblingSelector: '.column--content'
            }, ['xs', 's'])

            // Collapse panel
            .addPlugin('#new-customer-action', 'swCollapsePanel', ['xs', 's'])

            // Image slider
            .addPlugin('*[data-image-slider="true"]', 'swImageSlider', { touchControls: true })

            // Image zoom
            .addPlugin('.product--image-zoom', 'swImageZoom', 'xl')

            // Collapse panel
            .addPlugin('.blog-filter--trigger', 'swCollapsePanel', ['xs', 's', 'm', 'l'])

            // Off canvas HTML Panel
            .addPlugin('.category--teaser .hero--text', 'swOffcanvasHtmlPanel', ['xs', 's'])

            // Default product slider
            .addPlugin('*[data-product-slider="true"]', 'swProductSlider')

            // Product slider for premium items
            .addPlugin('.premium-product--content', 'swProductSlider')

            // Detail page tab menus
            .addPlugin('.product--rating-link, .link--publish-comment', 'swScrollAnimate', {
                scrollTarget: '.tab-menu--product'
            })
            .addPlugin('.tab-menu--product', 'swTabMenu', ['s', 'm', 'l', 'xl'])
            .addPlugin('.tab-menu--cross-selling', 'swTabMenu', ['m', 'l', 'xl'])
            .addPlugin('.tab-menu--product .tab--container', 'swOffcanvasButton', {
                titleSelector: '.tab--title',
                previewSelector: '.tab--preview',
                contentSelector: '.tab--content'
            }, ['xs'])
            .addPlugin('.tab-menu--cross-selling .tab--header', 'swCollapsePanel', {
                'contentSiblingSelector': '.tab--content'
            }, ['xs', 's'])
            .addPlugin('body', 'swAjaxProductNavigation')
            .addPlugin('*[data-topseller-slider="true"]', 'swProductSlider');

        $('*[data-collapse-panel="true"]').swCollapsePanel();
        $('*[data-range-slider="true"]').swRangeSlider();
        $('*[data-auto-submit="true"]').swAutoSubmit();
        $('*[data-drop-down-menu="true"]').swDropdownMenu();
        $('*[data-newsletter="true"]').swNewsletter();
        $('*[data-pseudo-text="true"]').swPseudoText();
        $('*[data-preloader-button="true"]').swPreloaderButton();

        $('*[data-filter-type]').swFilterComponent();
        $('*[data-listing-actions="true"]').swListingActions();
        $('*[data-scroll="true"]').swScrollAnimate();
        $('*[data-ajax-wishlist="true"]').swAjaxWishlist();
        $('*[data-image-gallery="true"]').swImageGallery();

        // Emotion Ajax Loader
        $('.emotion--wrapper').swEmotionLoader();

        $('input[type="submit"][form], button[form]').swFormPolyfill();

        $('select:not([data-no-fancy-select="true"])').swSelectboxReplacement();

        // Lightbox auto trigger
        $('*[data-lightbox="true"]').on('click.lightbox', function (event) {
            var $el = $(this),
                target = ($el.is('[data-lightbox-target]')) ? $el.attr('data-lightbox-target') : $el.attr('href');

            event.preventDefault();

            if (target.length) {
                $.lightbox.open(target);
            }
        });

        // Start up the placeholder polyfill, see ```jquery.ie-fixes.js```
        $('input, textarea').placeholder();

        // Deferred loading of the captcha
        $('div.captcha--placeholder[data-src]').swCaptcha();

        $('*[data-modalbox="true"]').swModalbox();

        $('.add-voucher--checkbox').on('change', function (event) {
            var method = (!$(this).is(':checked')) ? 'addClass' : 'removeClass';
            event.preventDefault();

            $('.add-voucher--panel')[method]('is--hidden');
        });

        $('.table--shipping-costs-trigger').on('click touchstart', function (event) {

            event.preventDefault();

            var $this = $(this),
                $next = $this.next(),
                method = ($next.hasClass('is--hidden')) ? 'removeClass' : 'addClass';

            $next[method]('is--hidden');
        });

        // Change the active tab to the customer reviews
        $('.is--ctl-detail, .is--ctl-blog').swJumpToTab();

        $('*[data-ajax-shipping-payment="true"]').swShippingPayment();

        // Initialize the registration plugin
        $('div[data-register="true"]').swRegister();

        $('*[data-last-seen-products="true"]').swLastSeenProducts($.extend({}, lastSeenProductsConfig));

        $('*[data-add-article="true"]').swAddArticle();

        $('*[data-menu-scroller="true"]').swMenuScroller();

        $('*[data-collapse-cart="true"]').swCollapseCart();

        $('*[data-compare-ajax="true"]').swProductCompareAdd();

        $('*[data-product-compare-menu="true"]').swProductCompareMenu();

        $('*[data-infinite-scrolling="true"]').swInfiniteScrolling();

        // Ajax cart amount display
        function cartRefresh() {
            var ajaxCartRefresh = $.controller.ajax_cart_refresh,
                $cartAmount = $('.cart--amount'),
                $cartQuantity = $('.cart--quantity');

            if (!ajaxCartRefresh.length) {
                return;
            }

            $.ajax({
                'url': ajaxCartRefresh,
                'dataType': 'jsonp',
                'success': function (response) {
                    var cart = JSON.parse(response);

                    if(!cart.amount || !cart.quantity) {
                        return;
                    }

                    $cartAmount.html(cart.amount);
                    $cartQuantity.html(cart.quantity).removeClass('is--hidden');

                    if(cart.quantity == 0) {
                        $cartQuantity.addClass('is--hidden');
                    }
                }
            });
        }

        $.subscribe('plugin/swAddArticle/onAddArticle', cartRefresh);
        $.subscribe('plugin/swCollapseCart/onRemoveArticleFinished', cartRefresh);

        StateManager.addPlugin('*[data-subcategory-nav="true"]', 'swSubCategoryNav', ['xs', 's']);

        $('.is--ctl-detail .reset--configuration').on('click', function () {
            $.loadingIndicator.open({
                closeOnClick: false
            });
        });
    });
})(jQuery, window);