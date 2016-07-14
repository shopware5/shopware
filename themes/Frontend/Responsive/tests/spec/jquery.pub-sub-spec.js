describe('Global publish-subscribe pattern', function() {
    it('should be possible to subscribe to an event and trigger it', function() {
        $.subscribe('test-event', function(event, name) {
            expect(name).toBe('sometest');
        });

        $.publish('test-event', 'sometest');
    });

    it('should be possible to subscribe to an plugin init event', function() {
        var name = 'yay';

        $.plugin(name, {
            init: function() {}
        });

        $.subscribe('plugin/' + name + '/onInit', function(event, plugin) {
            expect(plugin._name).toBe(name);
        });

        $('<div>', {
            'class': 'some-element'
        })[name]();
    });

    it('should be possible to subscribe to an plugin destroy event', function() {
        var name = 'yay', element, data;

        $.plugin(name, {
            init: function() {}
        });

        $.subscribe('plugin/' + name + '/onDestroy', function(event, plugin) {
            expect(plugin._name).toBe(name);
        });

        element = $('<div>', {
            'class': 'some-element'
        })[name]();

        data = element.data('plugin_' + name);
        data._destroy();
    });

    it('should be possible to subscribe to an plugin addEventListener event', function() {
        var name = 'yay', element, data;

        $.plugin(name, {
            init: function() {}
        });

        $.subscribe('plugin/' + name + '/onRegisterEvent', function(event, element, eventName) {
            expect(eventName).toBe('click.' + name);
        });

        element = $('<div>', {
            'class': 'some-element'
        })[name]();

        data = element.data('plugin_' + name);
        data._on(element, 'click', function() {});
    });

    it('should be possible to subscribe to an plugin removeEventListener event', function() {
        var name = 'yay', element, data;

        $.plugin(name, {
            init: function() {}
        });

        $.subscribe('plugin/' + name + '/onRemoveEvent', function(event, element, eventName) {
            expect(eventName).toBe('click');
        });

        element = $('<div>', {
            'class': 'some-element'
        })[name]();

        data = element.data('plugin_' + name);
        data._on(element, 'click', function() {});
        data._off(element, 'click');
    });
});