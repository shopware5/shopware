;
(function ($) {

    /*! Tiny Pub/Sub - v0.7.0 - 2013-01-29
     * https://github.com/cowboy/jquery-tiny-pubsub
     * Copyright (c) 2013 "Cowboy" Ben Alman; Licensed MIT */
    var o = $({});
    $.subscribe = function () {
        o.on.apply(o, arguments);
    };

    $.unsubscribe = function () {
        o.off.apply(o, arguments);
    };

    $.publish = function () {
        o.trigger.apply(o, arguments);
    };
}(jQuery));

;(function ($) {
    "use strict";

    /**
     * Constructor method of the PluginBase class. This method will try to
     * call the ```init```-method, where you can place your custom initialization of the plugin.
     *
     * @class PluginBase
     * @constructor
     * @param {String} name - Plugin name that is used for the events suffixes.
     * @param {HTMLElement} element - Element which should be used for the plugin.
     * @param {Object} options - The user settings, which overrides the default settings
     */
    function PluginBase(name, element, options) {
        var me = this;

        /**
         * @property {String} _name - Name of the Plugin
         * @private
         */
        me._name = name;

        /**
         * @property {jQuery} $el - Plugin element wrapped by jQuery
         */
        me.$el = $(element);

        /**
         * @property {Object} opts - Merged plugin options
         */
        me.opts = $.extend({}, me.defaults || {}, options);

        /**
         * @property {string} eventSuffix - Suffix which will be appended to the eventType to get namespaced events
         */
        me.eventSuffix = '.' + name;

        /**
         * @property {Array} _events Registered events listeners. See {@link PluginBase._on} for registration
         * @private
         */
        me._events = [];

        // Create new selector for the plugin
        $.expr[':']['plugin-' + name.toLowerCase()] = function (elem) {
            return !!$.data(elem, 'plugin-' + name);
        };

        // Call the init method of the plugin
        if (typeof me.init === 'function') {
            me.init();
        }

        $.publish('/plugin/' + name + '/init', [ me ]);
    }

    PluginBase.prototype = {

        /**
         * Destroyes the plugin on the {@link HTMLElement}. It removes the instance of the plugin
         * which is bounded to the {@link jQuery} element.
         *
         * If the plugin author has used the {@link PluginBase._on} method, the added event listeners
         * will automatically be cleared.
         *
         * @private
         * @method _destroy
         * @returns {PluginBase}
         */
        _destroy: function () {
            var me = this,
                name = me.getName();

            $.each(me._events, function (i, obj) {
                obj.el.off(obj.event);
            });

            me.$el.removeData('plugin' + name);

            $.publish('/plugin/' + name + '/destroy', [ me ]);

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
        _on: function () {
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
                if (!me._events[id]) {
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
         * @returns {mixed}
         */
        getOption: function (key) {
            return this.opts[key];
        },

        /**
         * Sets a plugin option. Deep linking of the options are now supported.
         * @param {String} key - Option key
         * @param {mixed} value - Option value
         * @returns {PluginBase}
         */
        setOption: function (key, value) {
            var me = this;

            me.opts[key] = value;

            return me;
        },

        /**
         * Fetches the configured options based on the {@link PluginBase.$el}.
         * @returns {mixed} configuration
         */
        getDataAttributes: function () {
            var me = this,
                opts = me.opts,
                attr;

            $.each(opts, function (key) {
                attr = me.$el.attr('data-' + key);
                if (attr !== undefined) {
                    opts[key] = attr;
                }
            });

            $.publish('/plugin/' + me._name + '/data-attributes', [ me.$el, opts ]);

            return opts;
        }
    };

    // Expose the private PluginBase constructor to global jQuery object
    $.PluginBase = PluginBase;

    // Object.create support test, and fallback for browsers without it
    if (typeof Object.create !== 'function') {
        Object.create = function (o) {
            function F() { }
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
     * @param {Object|Function} plugin - Plugin implementation
     * @returns {void}
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
    $.plugin = function (name, plugin) {
        $.fn[name] = function (options) {
            return this.each(function () {
                var element = this;

                if (!$.data(element, 'plugin-' + name)) {
                    if (typeof plugin === 'function') {
                        $.data(element, 'plugin-' + name, new plugin());
                        return;
                    }

                    var Plugin = function() {
                        PluginBase.call(this, name, element, options);
                    };

                    Plugin.prototype = $.extend(Object.create(PluginBase.prototype), { constructor: Plugin }, plugin);

                    $.data(element, 'plugin-' + name, new Plugin());
                }
            });
        };
    };
})(jQuery);