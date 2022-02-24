describe('Tab menu plugin tests', function() {
    it('should add is--active class to tab navigation', function() {
        var $testElement, data, html;

        html = '<div class="tab-menu--product">' +
            '<div class="tab--navigation">' +
            '<a href="#" class="tab--link" title="Test1" data-tabName="description">Test1</a>' +
            '<a href="#" class="tab--link" title="Test2" data-tabName="description">Test2</a>' +
            '</div>' +
            '</div>';

        $testElement = $(html).appendTo($('body')).swTabMenu();
        data = $testElement.data('plugin_swTabMenu');

        expect(data._name).toMatch('swTabMenu');

        data.changeTab(1);

        expect($testElement.html()).toContain('class="tab--link is--active" title="Test2"');
    });

    it('should add is--active class to tab content', function() {
        var $testElement, data, html;

        html = '<div class="tab-menu--product">' +
            '<div class="tab--container-list">' +
            '<div class="tab--container test1">' +
            '<div class="tab--content">' +
            '</div>' +
            '<div class="tab--container test2">' +
            '<div class="tab--content">' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';

        $testElement = $(html).appendTo($('body')).swTabMenu();
        data = $testElement.data('plugin_swTabMenu');

        expect(data._name).toMatch('swTabMenu');

        data.changeTab(1);

        expect($testElement.html()).toContain('class="tab--container test2 is--active"');
    });

    it('should not remove is--active class from inner tab content', function() {
        var $testElement, data, html;

        html = '<div class="tab-menu--product">' +
            '<div class="tab--container-list">' +
            '<div class="tab--container test1">' +
            '<div class="tab--content test1 is--active">' +
            '</div>' +
            '<div class="tab--container test2">' +
            '<div class="tab--content">' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';

        $testElement = $(html).appendTo($('body')).swTabMenu();
        data = $testElement.data('plugin_swTabMenu');

        expect(data._name).toMatch('swTabMenu');

        data.changeTab(1);

        expect($testElement.html()).toContain('class="tab--container test2 is--active"');
        expect($testElement.html()).toContain('class="tab--content test1 is--active"');
    });
});
