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
            $('*[data-offcanvas="true"]').offcanvasMenu();
            $('*[data-search-dropdown="true"]').searchFieldDropDown();
            $('*[data-slide-panel="true"]').slidePanel();
            $('.product--supplier').appendTo($('.product--info'));
            $('.category--teaser .hero--text').collapseText();
            $('#new-customer-action').collapsePanel();
            $('.btn--password').scroll();
            $('.btn--email').scroll();
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

            $('.product--supplier').appendTo($('.product--header'));
            $('.sidebar-main').prependTo($('.content-main--inner'));
            $('.action--filter-options').insertAfter($('.action--per-page'));

            var teaserText = $('.category--teaser .hero--text');
            if (teaserText.length) teaserText.data('plugin_collapseText').destroy();

            var btnPassword = $('.btn--password');
            if (btnPassword.length) btnPassword.data('plugin_scroll').destroy();

            var btnEmail = $('.btn--email');
            if (btnEmail.length) btnEmail.data('plugin_scroll').destroy();

            var btnRegistration = $('#new-customer-action');
            if (btnRegistration.length) btnRegistration.data('plugin_collapsePanel').destroy();
        }
    }, {
        type: 'tablet',
        enter: function() {
            $('*[data-search-dropdown="true"]').searchFieldDropDown();
            $('nav.product--actions').insertBefore($('.additional-info--tabs'));
            $('.filter--trigger').collapsePanel();
            $('.btn--password').collapsePanel();
            $('.btn--email').collapsePanel();
        },
        exit: function() {
            $('*[data-search-dropdown="true"]').each(function() {
                $(this).data('plugin_searchFieldDropDown').destroy();
            });
            $('nav.product--actions').insertAfter($('.buybox--form'));

            var filterTrigger = $('.filter--trigger');
            if (filterTrigger.length) filterTrigger.data('plugin_collapsePanel').destroy();

            var btnPassword = $('.btn--password');
            if (btnPassword.length) btnPassword.data('plugin_collapsePanel').destroy();

            var btnEmail = $('.btn--email');
            if (btnEmail.length) btnEmail.data('plugin_collapsePanel').destroy();
        }
    }, {
        type: 'tabletLandscape',
        enter: function() {
            $('*[data-search-dropdown="true"]').searchFieldDropDown();
            $('nav.product--actions').insertBefore($('.additional-info--tabs'));
            $('.filter--trigger').collapsePanel();
            $('.btn--password').collapsePanel();
            $('.btn--email').collapsePanel();
        },
        exit: function() {
            $('*[data-search-dropdown="true"]').each(function() {
                $(this).data('plugin_searchFieldDropDown').destroy();
            });
            $('nav.product--actions').insertAfter($('.buybox--form'));

            var filterTrigger = $('.filter--trigger');
            if (filterTrigger.length) filterTrigger.data('plugin_collapsePanel').destroy();

            var btnPassword = $('.btn--password');
            if (btnPassword.length) btnPassword.data('plugin_collapsePanel').destroy();

            var btnEmail = $('.btn--email');
            if (btnEmail.length) btnEmail.data('plugin_collapsePanel').destroy();
        }
    }, {
        type: 'desktop',
        enter: function() {
            $('.filter--trigger').collapsePanel();
            $('.btn--password').collapsePanel();
            $('.btn--email').collapsePanel();
        },
        exit: function() {
            var filterTrigger = $('.filter--trigger');
            if (filterTrigger.length) filterTrigger.data('plugin_collapsePanel').destroy();

            var btnPassword = $('.btn--password');
            if (btnPassword.length) btnPassword.data('plugin_collapsePanel').destroy();

            var btnEmail = $('.btn--email');
            if (btnEmail.length) btnEmail.data('plugin_collapsePanel').destroy();
        }
    }]);

    window.widgets = window.widgets || [];

    if (window.widgets !== 'undefined' && window.widgets.length > 0) {
        $.each(window.widgets, function(index, widget) {
            StateManager.registerListener([{
                type: 'smartphone',
                enter: function() {
                    $(widget.selector)[widget.plugin](widget.smartphone);
                },
                exit: function() {
                    $(widget.selector).data('plugin_' + widget.plugin).destroy();
                }
            }, {
                type: 'tablet',
                enter: function() {
                    $(widget.selector)[widget.plugin](widget.tablet);
                },
                exit: function() {
                    $(widget.selector).data('plugin_' + widget.plugin).destroy();
                }
            }, {
                type: 'tabletLandscape',
                enter: function() {
                    $(widget.selector)[widget.plugin](widget.tabletLandscape);
                },
                exit: function() {
                    $(widget.selector).data('plugin_' + widget.plugin).destroy();
                }
            }, {
                type: 'desktop',
                enter: function() {
                    $(widget.selector)[widget.plugin](widget.desktop);
                },
                exit: function() {
                    $(widget.selector).data('plugin_' + widget.plugin).destroy();
                }
            }]);
        });
    }

    $('*[data-tab-content="true"]').tabContent();
    $('*[data-emotions="true"]').emotions();
    $('*[data-image-slider="true"]').imageSlider();
    $('*[data-collapse-panel="true"]').collapsePanel();
    $('*[data-auto-submit="true"]').autoSubmit();
    $('input[data-quantity-field="true"]').quantityField();

    // Start up the placeholder polyfill, see ```jquery.ie-fixes.js```
    $('input, textarea').placeholder();

    // Deferred loading of the captcha
    $("div.captcha--placeholder[data-src]").each(function() {
        var $this = $(this),
            requestURL = $this.attr('data-src') || '';

        if (!requestURL || !requestURL.length) {
            return false;
        }

        // fix bfcache from caching the captcha/whole rendered page
        $(window).unload(function(){ });

        $.ajax({
            url: requestURL,
            cache: false,
            success: $this.html.bind($this)
        });
    });

    // Auto submitting form
    $('select[data-auto-submit-form="true"]').on('change', function() {
        $(this).parents('form').submit();
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

    // Initialize the registration plugin
    if($('body').hasClass('is--ctl-register')) {
        $('div[data-register="true"]').register();
    }

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

});
