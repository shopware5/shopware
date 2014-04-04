$(function() {
    StateManager.init([{
        type: 'smartphone',
        enter: '0px',
        exit: '763px'
    }, {
        type: 'tablet',
        enter: '764px',
        exit: '1023px'
    }, {
        type: 'desktop',
        enter: '1024px',
        exit: '5160px'
    }]);

    StateManager.registerListener([{
        type: 'smartphone',
        enter: function() {
            $('.sidebar-main').insertBefore($('.page-wrap'));
            $('*[data-offcanvas="true"]').offcanvasMenu();
            $('*[data-search-dropdown="true"]').searchFieldDropDown();
            $('*[data-slide-panel="true"]').slidePanel();
        },
        exit: function() {
            $('.sidebar-main').insertAfter($('.content--breadcrumb'));
            $('*[data-offcanvas="true"]').data('plugin_offcanvasMenu').destroy();
            $('*[data-search-dropdown="true"]').data('plugin_searchFieldDropDown').destroy();
            $('*[data-slide-panel="true"]').data('plugin_slidePanel').destroy();
        }
    }, {
        type:'tablet',
        enter: function() {
        },
        exit: function() {
        }
    }]);

    $('*[data-emotions="true"]').emotions();
});