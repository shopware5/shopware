;(function($) {

    /*! Tiny Pub/Sub - v0.7.0 - 2013-01-29
    * https://github.com/cowboy/jquery-tiny-pubsub
    * Copyright (c) 2013 "Cowboy" Ben Alman; Licensed MIT */
    var o = $({});
    $.subscribe = function() {
        o.on.apply(o, arguments);
    };

    $.unsubscribe = function() {
        o.off.apply(o, arguments);
    };

    $.publish = function() {
        o.trigger.apply(o, arguments);
    };
}(jQuery));

;(function($) {

    /**
     * Plugin base class which is the basement of all available jQuery plugins in the store front.
     *
     * @params {Void}
     * @returns {Void}
     * @constructor
     */
    function PluginBase () {}
    PluginBase.prototype = {

        /** @string Suffix which will be appended to the eventType to get namespaced events */
        eventSuffix: '.plugin',

        /** @string Name of the plugin */
        _name: 'plugin',

        /** @array Registered events listeners. See {@link PluginBase._on} for registration */
        _events: [],

        /**
         * Constructor method of the class to set up the event correctly. The method will try to
         * call the ```init```-method, where you can place your custom initialization of the plugin.
         *
         * @private
         * @constructor
         * @param {Object} userOpts - The user settings, which overrides the default settings
         * @param {jQuery} element - Element which should be used for the plugin.
         * @returns {PluginBase}
         */
        _init: function (userOpts, element) {
            var me = this;

            me.$el = $(element);
            me.opts = $.extend({}, me.defaults || {}, userOpts);
            me.eventSuffix = '.' + me._name;

            // Create new selector for the plugin
            $.expr[':']['plugin-' + me._name.toLowerCase()] = function(elem) {
                return !!$.data(elem, 'plugin-' + me._name);
            };

            // Call the init method of the plugin
            if (typeof me.init === 'function') {
                me.init();
            }

            $.publish('/plugin/' + me._name + '/init', [ me ]);
            return me;
        },

        /**
         * Destroyes the plugin on the {@link HTMLElement}. It removes the instance of the plugin
         * which is bounded to the {@link jQuery} element.
         *
         * If the plugin author has used the {@link PluginBase._on} method, the added event listeners
         * will automatically be cleared.
         *
         * @returns {PluginBase}
         * @private
         */
        _destroy: function () {
            var me = this;

            $.each(me._events, function(i, obj) {
                obj.el.off(obj.event);
            });

            me.$el.removeData('plugin' + me._name);
            $.publish('/plugin/' + me._name + '/destroy', [ me ]);

            return me;
        },

        /**
         * Wrapper method for {@link jQuery.on}, which registers in the event in the {@link PluginBase._events} array,
         * so the listeners can automatically be removed using the {@link PluginBase._destroy} method.
         *
         * @params {jQuery} Element, which should be used to add the listener
         * @params {String} Event type, you want to register.
         * @returns {PluginBase}
         * @private
         */
        _on: function() {
            var me = this,
                el = $(arguments[0]),
                event = arguments[1] + me.eventSuffix,
                args = Array.prototype.slice.call(arguments, 2);

            me._events.push({ 'el': el, 'event': event });
            args.unshift(event);
            el.on.apply(el, args);

            $.publish('/plugin/' + me._name + '/on', [ el, event ]);

            return me;
        },

        /**
         * Wrapper method for {@link jQuery.off}, which removes the event listener from the {@link PluginBase._events}
         * arrary.
         * @param {jQuery} el - Element, which contains the listener
         * @param {String} event - Name of the event to remove.
         * @returns {PluginBase}
         * @private
         */
        _off: function (el, event) {
            var me = this,
                events, eventIds = [];

            el = $(el);
            event = event + me.eventSuffix;

            events = $.grep(me._events, function (obj, index) {
                eventIds.push(index);
                return event === obj.event && el[0] === obj.el[0];
            });

            $.each(events, function (event) {
                el.off.apply(el, [ event.event ]);
            });

            $.each(eventIds, function (id) {
                if(!me._events[id]) {
                    return true;
                }
                delete me._events[id];
            });

            $.publish('/plugin/' + me._name + '/off', [ el, event ]);
            return me;
        },

        /**
         * Returns the name of the plugin.
         * @returns {String}
         */
        getName: function () {
            return this._name;
        },

        /**
         * Returns the event name with the event suffix appended.
         * @param {String} event - Event name
         * @returns {String}
         */
        getEventName: function (event) {
            return event + this.eventSuffix;
        },

        /**
         * Returns the element which registered the plugin.
         * @returns {jQuery}
         */
        getElement: function () {
            return this.$el;
        },

        /**
         * Returns the options of the plugin. The method returns a copy of the options object and not a reference.
         * @returns {Object}
         */
        getOptions: function () {
            return $.extend({}, this.opts);
        },

        /**
         * Returns the value of a single option.
         * @param {String} key - Option key.
         * @returns {Mixed}
         */
        getOption: function (key) {
            return this.opts[key];
        },

        /**
         * Sets a plugin option. Deep linking of the options are now supported.
         * @param {String} key - Option key
         * @param {Mixed} value - Option value
         * @returns {PluginBase}
         */
        setOption: function(key, value) {
            this.opts[key] = value;
            return this;
        },

        /**
         * Fetches the configured options based on the {@link PluginBase.$el}.
         * @returns {Mixed} configuration
         */
        getDataAttributes: function() {
            var me = this,
                attr;

            $.each(me.opts, function(key) {
                attr = me.$el.attr('data-' + key);
                if (attr !== undefined) {
                    me.opts[key] = attr;
                }
            });

            $.publish('/plugin/' + me._name + '/data-attributes', [ me.$el, me.opts ]);

            return me.opts;
        }
    };

    // Object.create support test, and fallback for browsers without it
    if (typeof Object.create !== 'function') {
        Object.create = function (o) {
            function F() {}
            F.prototype = o;
            return new F();
        };
    }

    /**
     * Creates a new jQuery plugin based on the {@link PluginBase} object prototype. The plugin will
     * automatically created in {@link jQuery.fn} namespace and will initialized on the fly.
     *
     * The {@link PluginBase} object supports an automatically destruction of the registered events. To
     * do so, please use the {@link PluginBase._on} method to create event listeners.
     *
     * @param {String} name - Name of the plugin
     * @param {Object} clsObj - Plugin implementation
     * @returns {Void}
     *
     * @example
     * // Register your plugin
     * $.plugin('yourName', {
     *    defaults: { key: 'value' },
     *
     *    init: function() {
     *        // ...initialization code
     *    },
     *
     *    destroy: function() {
     *      // ...your destruction code
     *
     *      // Use the force! Use the internal destroy method.
     *      me._destroy();
     *    }
     * });
     *
     * // Call the plugin
     * $('.test').yourName();
     */
    $.plugin = function (name, clsObj) {
        $.fn[name] = function (opts) {
            return this.each(function () {
                if(!$.data(this, 'plugin-' + name)) {
                    clsObj = $.extend({ _name: name }, clsObj);
                    var cls = $.extend({}, PluginBase.prototype, clsObj);
                    $.data(this, 'plugin-' + name, cls._init(opts, this));
                }
            });
        }
    };
})(jQuery);