$(function() {
    StateManager.init([{
        type: 'smartphone',
        enter: '0em',
        exit: '47.7495em'
    }, {
        type: 'tablet',
        enter: '47.75em',
        exit: '64em'
    }, {
        type: 'desktop',
        enter: '64em',
        exit: '320em'
    }]);

    StateManager.registerListener([{
        type: 'smartphone',
        enter: function() {
            $('.sidebar-main').insertBefore($('.page-wrap'));
            $('*[data-offcanvas="true"]').offcanvasMenu();
            $('*[data-search-dropdown="true"]').searchFieldDropDown();
            $('*[data-slide-panel="true"]').slidePanel();
            $('.product--supplier').appendTo($('.product--info'));
        },
        exit: function() {
            $('.sidebar-main').insertAfter($('.content--breadcrumb'));
            $('*[data-offcanvas="true"]').data('plugin_offcanvasMenu').destroy();
            $('*[data-search-dropdown="true"]').data('plugin_searchFieldDropDown').destroy();
            $('*[data-slide-panel="true"]').data('plugin_slidePanel').destroy();
            $('.product--supplier').appendTo($('.product--header'));
        }
    }, {
        type:'tablet',
        enter: function() {
        },
        exit: function() {
        }
    }]);

    $('*[data-tab-content="true"]').tabContent();
    $('*[data-emotions="true"]').emotions();
    $('*[data-image-slider="true"]').imageSlider();

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
});