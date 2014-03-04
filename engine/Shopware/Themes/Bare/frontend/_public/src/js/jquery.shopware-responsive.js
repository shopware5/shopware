$(function() {
    StateManager.init([{
        type: 'smartphone',
        enter: '0em',
        exit: '47.75em'
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
        },
        exit: function() {
            $('.sidebar-main').insertAfter($('.content--breadcrumb'));
            $('*[data-offcanvas="true"]').data('plugin_offcanvasMenu').destroy();
        }
    }]);
});