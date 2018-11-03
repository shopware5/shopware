describe('Plugin base class', function() {
    it('should create a plugin and bind it to the jQuery "fn" namespace', function() {
        $.plugin('yay', {
            init: function() {}
        });

        expect($.fn.yay).toBeDefined();
    });

    it('should be executable on a DOM element', function() {
        var $testElement, data;

        $.plugin('yay', {
            init: function() {}
        });

        $testElement = $('<div>', {
            'class': 'test--element'
        }).appendTo($('body')).yay();
        data = $testElement.data('plugin_yay');

        expect(data._name).toMatch('yay');
        data._destroy();
        $testElement.remove();
    });

    it('should be available in a global scope in the PluginsCollection', function() {
        $.plugin('yay', {
            init: function() {}
        });

        expect(window.PluginsCollection.yay).toBeDefined();
    });

    it('should support a custom config inside the plugin', function() {
        var $testElement, data;
        $.plugin('yay', {
            alias: 'swagYay',
            defaults: {
                testCls: 'test'
            },
            init: function() { }
        });

        $testElement = $('<div>', {
            'class': 'test--element'
        }).appendTo($('body')).yay();
        data = $testElement.data('plugin_yay');

        expect(data.getOptions().testCls).toMatch('test');
        $testElement.remove();
        data._destroy();
    });

    it('should be possible to override a config using data attributes', function() {
        var $testElement, data;

        $.plugin('yay', {
            defaults: {
                testCls: 'test'
            },
            init: function() {
                this.applyDataAttributes();
            }
        });

        $testElement = $('<div>', {
            'class': 'test--element',
            'data-testCls': 'modified-test'
        }).appendTo($('body')).yay();
        data = $testElement.data('plugin_yay');

        expect(data.getOptions().testCls).toMatch('modified-test');
        $testElement.remove();
        data._destroy();
    });

    it('should be possible to destroy the plugin on an element', function() {
        var $testElement, data;

        $.plugin('yay', {
            init: function() {
            },
            destroy: function() {
                this._destroy();
            }
        });

        $testElement = $('<div>', {
            'class': 'test--element',
            'data-testCls': 'modified-test'
        }).appendTo($('body')).yay();
        data = $testElement.data('plugin_yay');
        data.destroy();

        data = $testElement.data('plugin_yay');
        expect(data).not.toBeDefined();

        $testElement.remove();
    });

    it('should return the plugin name', function() {
        var $testElement, data;

        $.plugin('yay', {
            init: function() {}
        });

        $testElement = $('<div>', {
            'class': 'test--element'
        }).appendTo($('body')).yay();
        data = $testElement.data('plugin_yay');

        expect(data.getName()).toMatch('yay');

        $testElement.remove();
        data._destroy();
    });

    it('should be possible to get one option from the plugin configuration', function() {
        var $testElement, data;

        $.plugin('yay', {
            defaults: { test: true },
            init: function() {}
        });

        $testElement = $('<div>', {
            'class': 'test--element'
        }).appendTo($('body')).yay();
        data = $testElement.data('plugin_yay');

        expect(data.getOption('test')).toBe(true);

        $testElement.remove();
        data._destroy();
    });

    it('should be possible to get all options from the plugin configuration', function() {
        var $testElement, data, options;

        $.plugin('yay', {
            defaults: { foo: true, bar: false  },
            init: function() {}
        });

        $testElement = $('<div>', {
            'class': 'test--element'
        }).appendTo($('body')).yay();
        data = $testElement.data('plugin_yay');
        options = data.getOptions();

        expect(options.foo).toBe(true);
        expect(options.bar).toBe(false);

        $testElement.remove();
        data._destroy();
    });

    it('should be possible to get set a option to the plugin configuration', function() {
        var $testElement, data, options;

        $.plugin('yay', {
            defaults: { foo: true  },
            init: function() {}
        });

        $testElement = $('<div>', {
            'class': 'test--element'
        }).appendTo($('body')).yay();
        data = $testElement.data('plugin_yay');
        data.setOption('foo', false);

        expect(data.getOption('foo')).toBe(false);

        $testElement.remove();
        data._destroy();
    });

    it('should be possible to prefix an event name', function() {
        var $testElement, data;

        $.plugin('yay', {
            defaults: { foo: true, bar: false  },
            init: function() {}
        });

        $testElement = $('<div>', {
            'class': 'test--element'
        }).appendTo($('body')).yay();
        data = $testElement.data('plugin_yay');
        expect(data.getEventName('click')).toBe('click.yay');
        expect(data.getEventName('click touch mouseenter')).toBe('click.yay touch.yay mouseenter.yay');

        $testElement.remove();
        data._destroy();
    });

    it('should be possible to check if an element uses a plugin using a custom selector', function() {
        var $testElement;

        $.plugin('yay', {
            defaults: { foo: true, bar: false  },
            init: function() {}
        });

        $testElement = $('<div>', {
            'class': 'test--element'
        }).appendTo($('body')).yay();
        expect($testElement.is(':plugin-yay')).toBe(true);

        $testElement.remove();
    });

    it('should be possbile to override a plugin method', function() {
        var $testElement;

        $.plugin('yay', {
            init: function() {
                this.testMethod();
            },
            testMethod: function() {
                this.$el.addClass('test');
            }
        });

        $.overridePlugin('yay', {
            testMethod: function() {
                this.$el.addClass('testing')
            }
        });

        $testElement = $('<div>', {
            'class': 'test--element'
        }).appendTo($('body')).yay();
        expect($testElement.hasClass('testing')).toBe(true);

        $testElement.remove();
    });

    it('should add event listener to element', function() {
        var $testElement, data, events;

        $.plugin('yay', {
            init: function() {}
        });

        $testElement = $('<div>', {
            'class': 'test--element'
        }).appendTo($('body')).yay();
        data = $testElement.data('plugin_yay');
        data._on($testElement, 'testEvent1', $.noop);
        data._on($testElement, 'testEvent2', $.noop);

        events = $._data($testElement[0]).events;
        expect(Object.keys(events).length).toBe(2);

        $testElement.remove();
        data._destroy();
    });

    it('should remove event listener from element', function() {
        var $testElement, data, events;

        $.plugin('yay', {
            init: function() {}
        });

        $testElement = $('<div>', {
            'class': 'test--element'
        }).appendTo($('body')).yay();
        data = $testElement.data('plugin_yay');
        data._on($testElement, 'testEvent1', $.noop);
        data._on($testElement, 'testEvent2', $.noop);
        data._off($testElement, 'testEvent1', $.noop);

        events = $._data($testElement[0]).events;
        expect(Object.keys(events).length).toBe(1);

        $testElement.remove();
        data._destroy();
    });
});
