$(function() {
    StateManager.init([{
        type: 'smartphone',
        enter: '0px',
        exit: '767px'
    }, {
        type: 'tablet',
        enter: '768px',
        exit: '1259px'
    }, {
        type: 'desktop',
        enter: '1260',
        exit: '5160px'
    }]);

    StateManager.registerListener([{
        type: 'smartphone',
        enter: function() {
            $('.sidebar-main').insertBefore($('.page-wrap'));
            $('*[data-offcanvas="true"]').offcanvasMenu();
            $('*[data-search-dropdown="true"]').searchFieldDropDown();
            $('*[data-slide-panel="true"]').slidePanel();
            $('.product--supplier').appendTo($('.product--info'));
            $('.category--teaser .hero--text').collapseText();
        },
        exit: function() {
            $('.sidebar-main').insertAfter($('.content--breadcrumb'));
            $('*[data-offcanvas="true"]').data('plugin_offcanvasMenu').destroy();
            $('*[data-search-dropdown="true"]').data('plugin_searchFieldDropDown').destroy();
            $('*[data-slide-panel="true"]').data('plugin_slidePanel').destroy();
            $('.product--supplier').appendTo($('.product--header'));
            $('.category--teaser .hero--text').data('plugin_collapseText').destroy();
        }
    }, {
        type: 'tablet',
        enter: function() {
            $('*[data-search-dropdown="true"]').searchFieldDropDown();
            $('nav.product--actions').insertBefore($('.additional-info--tabs'));
            $('.filter--trigger').collapsePanel();
        },
        exit: function() {
            $('*[data-search-dropdown="true"]').data('plugin_searchFieldDropDown').destroy();
            $('nav.product--actions').insertAfter($('.buybox--form'));
            $('.filter--trigger').data('plugin_collapsePanel').destroy();
        }
    }, {
        type: 'desktop',
        enter: function() {
            $('.filter--trigger').collapsePanel();
        },
        exit: function() {
            $('.filter--trigger').data('plugin_collapsePanel').destroy();
        }
    }]);

    $('*[data-tab-content="true"]').tabContent();
    $('*[data-emotions="true"]').emotions();
    $('*[data-image-slider="true"]').imageSlider();
    $('*[data-collapse-panel="true"]').collapsePanel();
    $('*[data-collapse-text="true"]').collapseText();
    $('*[data-auto-submit="true"]').autoSubmit();

    // Deferred loading of the captcha
    $("div.captcha--placeholder[data-src]").each(function() {
        var $this = $(this),
            requestURL = $this.attr('data-src') || '';

        if (!requestURL || !requestURL.length) {
            return false;
        }

        $this.load(requestURL);
    });

    // Auto submitting form
    $('select[data-auto-submit-form="true"]').on('change', function() {
        $(this).parents('form').submit();
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
});