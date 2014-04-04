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
            console.log('enter smartphone');
            $('.sidebar-main').insertBefore($('.page-wrap'));
            $('*[data-offcanvas="true"]').offcanvasMenu();
            $('*[data-search-dropdown="true"]').searchFieldDropDown();
            $('*[data-slide-panel="true"]').slidePanel();
        },
        exit: function() {
            console.log('exit smartphone');
            $('.sidebar-main').insertAfter($('.content--breadcrumb'));
            $('*[data-offcanvas="true"]').data('plugin_offcanvasMenu').destroy();
            $('*[data-search-dropdown="true"]').data('plugin_searchFieldDropDown').destroy();
            $('*[data-slide-panel="true"]').data('plugin_slidePanel').destroy();
        }
    }, {
        type:'tablet',
        enter: function() {
            console.log('enter tablet');
        },
        exit: function() {
            console.log('exit tablet');
        }
    }, {
        type:'desktop',
        enter: function() {
            console.log('enter desktop');
        },
        exit: function() {
            console.log('exit desktop');
        }
    }]);

    $('*[data-emotions="true"]').emotions();
});