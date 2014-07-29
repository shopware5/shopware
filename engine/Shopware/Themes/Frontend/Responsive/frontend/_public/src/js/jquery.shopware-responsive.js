$(function() {
    StateManager.init([{
        type: 'smartphone',
        enter: '0px',
        exit: '767px'
    }, {
        type: 'tablet',
        enter: '768px',
        exit: '1023px'
    }, {
        type: 'tabletLandscape',
        enter: '1024px',
        exit: '1259px'
    }, {
        type: 'desktop',
        enter: '1260px',
        exit: '5160px'
    }]);

    StateManager.registerListener([{
        type: 'smartphone',
        enter: function() {
            var pageWrap = $('.page-wrap');
            $('.sidebar-main').insertBefore(pageWrap);
            $('.action--filter-options').insertBefore(pageWrap);
            $('.blog--filter-options').insertBefore(pageWrap);
            $('*[data-offcanvas="true"]').offcanvasMenu();
            $('*[data-search-dropdown="true"]').searchFieldDropDown();
            $('*[data-slide-panel="true"]').slidePanel();
            $('.category--teaser .hero--text').collapseText();
            $('#new-customer-action').collapsePanel();
            $('.btn--password').scroll();
            $('.btn--email').scroll();
            $('.blog-filter--trigger').collapsePanel();
            $('*[data-product-slider="true"]').productSlider({
                perPage: 1,
                perSlide: 1,
                touchControl: true
            });
            $('*[data-image-slider="true"]').imageSlider({
                touchControls: true
            });
        },
        exit: function() {
            $('*[data-offcanvas="true"]').each(function() {
                $(this).data('plugin_offcanvasMenu').destroy();
            });
            $('*[data-search-dropdown="true"]').each(function() {
                $(this).data('plugin_searchFieldDropDown').destroy();
            });
            $('*[data-slide-panel="true"]').each(function() {
                $(this).data('plugin_slidePanel').destroy();
            });
            $('*[data-product-slider="true"]').each(function() {
                $(this).data('plugin_productSlider').destroy();
            });
            $('*[data-image-slider="true"]').each(function() {
                $(this).data('plugin_imageSlider').destroy();
            });

            $('.sidebar-main').prependTo($('.content-main--inner'));
            $('.action--filter-options').insertAfter($('.action--per-page'));
            $('.blog--filter-options').insertBefore($('.blog--listing'));
            
            var teaserText = $('.category--teaser .hero--text');
            if (teaserText.length) teaserText.data('plugin_collapseText').destroy();

            var btnPassword = $('.btn--password');
            if (btnPassword.length) btnPassword.data('plugin_scroll').destroy();

            var btnEmail = $('.btn--email');
            if (btnEmail.length) btnEmail.data('plugin_scroll').destroy();

            var btnRegistration = $('#new-customer-action');
            if (btnRegistration.length) btnRegistration.data('plugin_collapsePanel').destroy();

            var blogFilterTrigger = $('.blog-filter--trigger');
            if (blogFilterTrigger.length) blogFilterTrigger.data('plugin_collapsePanel').destroy();
        }
    }, {
        type: 'tablet',
        enter: function() {
            $('*[data-search-dropdown="true"]').searchFieldDropDown();
            $('.filter--trigger').collapsePanel();
            $('.btn--password').collapsePanel();
            $('.btn--email').collapsePanel();
            $('.blog-filter--trigger').collapsePanel();
            $('*[data-product-slider="true"]').productSlider({
                perPage: 3,
                perSlide: 1,
                touchControl: true
            });
            $('*[data-image-slider="true"]').imageSlider({
                touchControls: true
            });
        },
        exit: function() {
            $('*[data-search-dropdown="true"]').each(function() {
                $(this).data('plugin_searchFieldDropDown').destroy();
            });
            $('*[data-product-slider="true"]').each(function() {
                $(this).data('plugin_productSlider').destroy();
            });
            $('*[data-image-slider="true"]').each(function() {
                $(this).data('plugin_imageSlider').destroy();
            });

            var filterTrigger = $('.filter--trigger');
            if (filterTrigger.length) filterTrigger.data('plugin_collapsePanel').destroy();

            var btnPassword = $('.btn--password');
            if (btnPassword.length) btnPassword.data('plugin_collapsePanel').destroy();

            var btnEmail = $('.btn--email');
            if (btnEmail.length) btnEmail.data('plugin_collapsePanel').destroy();

            var blogFilterTrigger = $('.blog-filter--trigger');
            if (blogFilterTrigger.length) blogFilterTrigger.data('plugin_collapsePanel').destroy();
        }
    }, {
        type: 'tabletLandscape',
        enter: function() {
            $('*[data-search-dropdown="true"]').searchFieldDropDown();
            $('.filter--trigger').collapsePanel();
            $('.btn--password').collapsePanel();
            $('.btn--email').collapsePanel();
            $('.blog-filter--trigger').collapsePanel();
            $('*[data-product-slider="true"]').productSlider({
                perPage: 4,
                perSlide: 1,
                touchControl: true
            });
            $('*[data-image-slider="true"]').imageSlider({
                touchControls: true
            });
        },
        exit: function() {
            $('*[data-search-dropdown="true"]').each(function() {
                $(this).data('plugin_searchFieldDropDown').destroy();
            });
            $('*[data-product-slider="true"]').each(function() {
                $(this).data('plugin_productSlider').destroy();
            });
            $('*[data-image-slider="true"]').each(function() {
                $(this).data('plugin_imageSlider').destroy();
            });

            var filterTrigger = $('.filter--trigger');
            if (filterTrigger.length) filterTrigger.data('plugin_collapsePanel').destroy();

            var btnPassword = $('.btn--password');
            if (btnPassword.length) btnPassword.data('plugin_collapsePanel').destroy();

            var btnEmail = $('.btn--email');
            if (btnEmail.length) btnEmail.data('plugin_collapsePanel').destroy();

            var blogFilterTrigger = $('.blog-filter--trigger');
            if (blogFilterTrigger.length) blogFilterTrigger.data('plugin_collapsePanel').destroy();
        }
    }, {
        type: 'desktop',
        enter: function() {
            $('.filter--trigger').collapsePanel();
            $('.btn--password').collapsePanel();
            $('.btn--email').collapsePanel();
            $('.product--image-zoom').imageZoom();
            $('*[data-product-slider="true"]').productSlider({
                perPage: 5,
                perSlide: 1
            });
            $('*[data-image-slider="true"]').imageSlider();
        },
        exit: function() {
            $('*[data-product-slider="true"]').each(function() {
                $(this).data('plugin_productSlider').destroy();
            });
            $('*[data-image-slider="true"]').each(function() {
                $(this).data('plugin_imageSlider').destroy();
            });

            var filterTrigger = $('.filter--trigger');
            if (filterTrigger.length) filterTrigger.data('plugin_collapsePanel').destroy();

            var btnPassword = $('.btn--password');
            if (btnPassword.length) btnPassword.data('plugin_collapsePanel').destroy();

            var btnEmail = $('.btn--email');
            if (btnEmail.length) btnEmail.data('plugin_collapsePanel').destroy();

            var imageZoom = $('.product--image-zoom');
            if (imageZoom.length) imageZoom.data('plugin_imageZoom').destroy();
        }
    }]);

    window.widgets = window.widgets || [];

    // Premium products
    window.widgets.push({
        selector: '.premium-product--content',
        plugin: 'productSlider',
        smartphone: {
            perPage: 1,
            perSlide: 1,
            touchControl: true
        },
        tablet: {
            perPage: 2,
            perSlide: 1,
            touchControl: true
        },
        tabletLandscape: {
            perPage: 3,
            perSlide: 1,
            touchControl: true
        },
        desktop: {
            perPage: 4,
            perSlide: 1
        }
    });

    if (window.widgets !== 'undefined' && window.widgets.length > 0) {

        var exitWidget = function(widget) {
            var $el = $(widget.selector);
            if ($el.length) $el.data('plugin_' + widget.plugin).destroy();
        };

        $.each(window.widgets, function(index, widget) {
            StateManager.registerListener([{
                type: 'smartphone',
                enter: function() {
                    $(widget.selector)[widget.plugin](widget.smartphone);
                },
                exit: function() {
                    exitWidget(widget);
                }
            }, {
                type: 'tablet',
                enter: function() {
                    $(widget.selector)[widget.plugin](widget.tablet);
                },
                exit: function() {
                    exitWidget(widget);
                }
            }, {
                type: 'tabletLandscape',
                enter: function() {
                    $(widget.selector)[widget.plugin](widget.tabletLandscape);
                },
                exit: function() {
                    exitWidget(widget);
                }
            }, {
                type: 'desktop',
                enter: function() {
                    $(widget.selector)[widget.plugin](widget.desktop);
                },
                exit: function() {
                    exitWidget(widget);
                }
            }]);
        });
    }

    $('*[data-tab-content="true"]').tabContent();
    $('*[data-emotions="true"]').emotions();
    $('*[data-collapse-panel="true"]').collapsePanel();
    $('*[data-auto-submit="true"]').autoSubmit();
    $('*[data-drop-down-menu="true"]').dropdownMenu();
    $('*[data-newsletter="true"]').newsletter();
    $('*[data-pseudo-text="true"]').pseudoText();

    $('input[data-form-polyfill="true"], button[data-form-polyfill="true"]').formPolyfill();

    $('select:not([data-no-fancy-select="true"])').selectboxReplacement();

    // Lightbox auto trigger
    $('*[data-lightbox="true"]').on('click.lightbox', function(event) {
        var $el = $(this),
            target = ($el.is('[data-lightbox-target]')) ? $el.attr('data-lightbox-target'): $el.attr('href');

        event.preventDefault();

        if (target.length) {
            $.lightbox.open(target);
        }
    });

    // Start up the placeholder polyfill, see ```jquery.ie-fixes.js```
    $('input, textarea').placeholder();

    // Deferred loading of the captcha
    $('div.captcha--placeholder[data-src]').captcha();

    // Auto submitting form
    $('select[data-auto-submit-form="true"]').on('change', function() {
        $(this).parents('form').submit();
    });

    $('*[data-modal="true"] a').on('click.modal', function() {
        event.preventDefault();

        $.modal.open(this.href, {
            mode: 'ajax'
        });
    });

    $('.add-voucher--checkbox').on('change', function (event) {
        var method = (!$(this).is(':checked')) ? 'addClass' : 'removeClass';
        event.preventDefault();

        $('.add-voucher--panel')[method]('is--hidden');
    });

    $('.table--shipping-costs-trigger').on('click', function (event) {

        event.preventDefault();

        var $this = $(this),
		$next = $this.next(),
		method = ($next.hasClass('is--hidden')) ? 'removeClass' : 'addClass';

        $next[method]('is--hidden');
    });

    // Change the active tab to the customer reviews, if the url param sAction === rating is set.
    if($('.is--ctl-detail').length) {
        var plugin = $('.additional-info--tabs').data('plugin_tabContent');

        $('.product--rating-link').on('click', function(e) {
            e.preventDefault();
            plugin.changeTab(1, true);
        });

        var param = decodeURI((RegExp('sAction' + '=' + '(.+?)(&|$)').exec(location.search) || [,null])[1]);
        if(param === 'rating') {
            plugin.changeTab(1, false);
        }
    }

    $('*[data-ajax-shipping-payment="true"]').shippingPayment();

    // Initialize the registration plugin
    $('div[data-register="true"]').register();

    // Modal on add basket item
    $('.buybox--form').submit(function (event) {
        event.preventDefault();

        var me = $(this),
            ajaxData = me.serialize(),
            ajaxUrl = me.attr('action');

        $.loadingIndicator.open();

        $.ajax({
            data: ajaxData,
            dataType: 'jsonp',
            url: ajaxUrl,

            success: function (result) {
                var modal;

                $.loadingIndicator.close(function() {

                    modal = $.modal.open(result, {
                        width: 750,
                        height: 600
                    });

                    var slider = $('.js--modal').find('.product-slider');

                    StateManager.registerListener([{
                        type: 'smartphone',
                        enter: function () {
                            slider.productSlider({
                                perPage: 1,
                                perSlide: 1,
                                touchControl: true
                            });
                        }
                    }, {
                        type: 'tablet',
                        enter: function () {
                            slider.productSlider({
                                perPage: 2,
                                perSlide: 1,
                                touchControl: true
                            });
                        }
                    }, {
                        type: 'tabletLandscape',
                        enter: function () {
                            slider.productSlider({
                                perPage: 3,
                                perSlide: 1,
                                touchControl: true
                            });
                        }
                    }, {
                        type: 'desktop',
                        enter: function () {
                            slider.productSlider({
                                perPage: 3,
                                perSlide: 1,
                                touchControl: true
                            });
                        }
                    }, {
                        type: '*',
                        enter: function () {
                            setTimeout(function () {
                                modal.find('.product-slider').data('plugin_productSlider').setSizes();
                            }, 10);
                        },
                        exit: function () {
                            modal.find('.product-slider').data('plugin_productSlider').destroy();
                        }
                    }]);
                });
            }
        });
    });

    // Debug mode is enabled
    if($('.debug--panel').length) {
        var $debugPanel = $('.debug--panel'),
            $window = $(window), timer;

        $debugPanel.hide();
        var refreshDebugPanel = function() {
            var device = 'Device: ';
            $debugPanel.find('.debug--width').html($window.width());
            $debugPanel.find('.debug--height').html($window.height());

            $debugPanel.fadeIn('fast');

            if(StateManager.isSmartphone()) {
                device += 'Smartphone';
            }

            if(StateManager.isTablet()) {
                device += 'Tablet';
            }

            if(StateManager.isDesktop()) {
                device += 'Desktop';
            }
            $debugPanel.find('.debug--device').html(device);

            if(timer) {
                window.clearTimeout(timer);
            }
            timer = window.setTimeout(function() {
                $debugPanel.fadeOut('fast');
            }, 1000);
        };

        $window.on('resize', function() {
            window.setTimeout(function() {
                refreshDebugPanel();
            }, 10);
        });
    }

    $('*[data-live-search="true"]').liveSearch();

    $('*[data-last-seen-products="true"]').lastSeenProducts($.extend({}, lastSeenProductsConfig));

    $('body').httpCacheFilters();

    $('*[data-menu-scroller="true"]').menuScroller();

    // Jump to the scroll comments section on the detail-page
    if(window.location.hash === '#content--product-reviews') {
        var tabPanel = $('.additional-info--tabs').data('plugin_tabContent'),
            hash = window.location.hash,
            idx = -1;

        tabPanel.$nav.find('.navigation--link').each(function(i, item) {
            var $item = $(item),
                href = $item.attr('href');

            if(href === hash) {
                idx = i;
                return false;
            }
        });

        tabPanel.changeTab(idx, true);
    }
});
