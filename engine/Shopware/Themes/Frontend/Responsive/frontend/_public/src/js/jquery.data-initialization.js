;(function($, undefined) {

    /**
     * Data initialization of jQuery plugins
     *
     * The following component provides an easy-to-use way to initialize jQuery plugins using ```data``` attributes.
     * It's support defining different jQuery plugins for different viewport sizes with a different configuration. The
     * other way to use the component is to define a jQuery plugin for all possible viewport sizes but with different
     * configuration for example to initialize the "product slider" jQuery plugin to show a different amount of products
     * on one slide through the different viewports.
     *
     * The configuration can be passed in multiple formats:
     * ```
     *   <div data-my-plugin='{"propertyFoo": "foo", "propertyBar": "bar"}'></div>
     *   <div data-my-plugin="{propertyFoo: 'foo', propertyBar: 'bar'}"></div>
     *   <div data-my-plugin="propertyFoo: foo, propertyBar: bar"></div>
     * ```
     *
     * This will be transfered into the options object:
     * ```
     *    {
     *       propertyFoo: "foo",
     *       propertyBar: "bar"
     *    }
     * ```
     *
     * Here's an overview of the supported viewports and it's corresponding ```data```-attributes:
     *
     * - Viewport "XS"
     *   Used for mobile devices like the iPhone, Galaxy series or Nexus series as well as phalets.
     *
     *   Starting: 0px / 0rem
     *   Ending: 767px / 47.9375rem
     *
     * - Viewport "M"
     *   Used for tablets mostly in portrait mode like the iPad, Galaxy Tab series or the Kindle Fire.
     *
     *   LESS variable: @tabletViewportWidth
     *   Starting: 768px / 48rem
     *   Ending: 1023px / 63.9375rem
     *
     * - Viewport "L"
     *   Used for tablets in landscape mode, netbooks or normal sized desktop computers.
     *
     *   LESS variable: @tabletLandscapeViewportWidth
     *   Starting: 1024px / 64rem
     *   Ending: 1259px / 78.6875rem
     *
     * - Viewport "XL"
     *   Used for large displays or smart tvs.
     *
     *   LESS variable: @desktopViewportWidth
     *   Starting: 1260px / 78.75rem
     *   Ending: 5160px / 322.5rem
     *
     * @example Initialize the product slider for mobile devices`
     * ```
     *    <div data-xs="productSlider" data-xs-config="{ perPage: 1, perSlide: 1, touchControl: true }"></div>
     * ```
     *
     * @example Initizalize a plugin with different content for different viewport sizes.
     * ```
     *    <div data-all="productSlider" data-xs-config="{ perPage: 1, perSlide: 1, touchControl: true }" data-m-config="data-xs-config="{ perPage: 2, perSlide: 1 }""
     * ```
     *
     * @example Different plugins for the different viewports without using a custom configuration
     * ```
     *    <div data-xs="productSlider" data-m="collapsePanel" data-l="modal"></div>
     * ```
     */
    var selectors = [ '*[data-all]', '*[data-xs]', '*[data-s]', '*[data-m]', '*[data-l]', '*[data-xl]' ],
        configSelectors = [],
    // Pre-compiled regular expression
        _jsonize_brace = /^[{\[]/,         // check `{`ã€`[` at the beginning
        _jsonize_token = /[^,:{}\[\]]+/g,  // retrieve token based on the delimiter
        _jsonize_quote = /^['"](.*)['"]$/, // remove quotes at the top end
        _jsonize_escap = /(["])/g;         // characters to be escaped

    /**
     * Convert JSON like literals to valid JSON
     * Numeric or String literal will be converted to strings.
     * The `undefined` will be converted to `{}`.
     * @source: <https://github.com/tokkonopapa/jQuery-parseData>
     * @license: MIT
     *
     * @param {String} str - The JSON like literal which needs to be converted
     * @returns {Object} JSON object
     */
    var jsonize = function(str) {
        // Wrap with `{}` if not JavaScript object literal
        str = $.trim(str);
        if (_jsonize_brace.test(str) === false) {
            str = '{' + str + '}';
        }

        // Retrieve token and convert to JSON
        return str.replace(_jsonize_token, function (a) {
            a = $.trim(a);

            // Keep some special strings as they are
            if ('' === a ||
                'true' === a || 'false' === a || 'null' === a ||
                (!isNaN(parseFloat(a)) && isFinite(a))) {
                return a;
            }

            // For string literal,
            // 1. remove quotes at the top end
            // 2. escape double quotes in the middle
            // 3. wrap token with double quotes
            else {
                return '"'
                + a.replace(_jsonize_quote, '$1')
                    .replace(_jsonize_escap, '\\$1')
                + '"';
            }
        });
    };

    /**
     * Sanitize the committed argument so we can use the selector for ```$.fn.attr()```.
     *
     * @example Simple usage
     * ```
     *    sanitizeSelector('*[data-xs]'); // returns "data-xs"
     * ```
     *
     * @param {String} selector - Selector which needs to be sanitize
     * @returns {String} Sanitized selector
     */
    var sanitizeSelector = function(selector) {
        selector = selector.substr(2);
        selector = selector.substring(0, selector.length -1);

        return selector;
    };

    /**
     * Tries to read out the committed selector on the committed item.
     *
     * @example Simple usage
     * ```
     *    getViewportStateConfig($('.test'), '*[data-xs]');
     * ```
     *
     * @param {jQuery} $item - jQueryized HTMLNode
     * @param {String} selector - Viewport selector
     * @returns {Object} If the selector isn't found it returns a empty object, otherwise a jsonized object of the
     *          configuration string for the committed selector.
     */
    var getViewportStateConfig = function($item, selector) {
        var idx = selectors.indexOf(selector) -1,
            configSelector = idx !== -1 ? configSelectors[idx] : undefined,
            attr, config;

        // Config selector wasn't found
        if(configSelector === undefined) {
            return {};
        }

        configSelector = sanitizeSelector(configSelector);
        attr = $item.attr(configSelector);

        // Config attribute wasn't found on the element
        if(!attr) {
            return {};
        }

        // A try-catch block is necessary due to the fact that the user can enter invalid data into the html attribute.
        try {
            config = $.parseJSON(jsonize(attr));
        } catch(err) {
            throw new Error('The configuration in the attribute "' + configSelector + '" is invalid');
            return {};
        }

        // Parsed json object wasn't valid
        if(config === null) {
            return {};
        }

        return config;
    };

    /**
     * Creates different queues for the selectors which are defined in {@link selectors}. The queues are used to
     * save the different jQuery plugins and it's configuration for the different viewport sizes.
     *
     * @param {Array} selectors - Array of selectors which needs a queue.
     * @returns {Array} Array of queues based on the committed selectors.
     */
    var createQueueBasedOnSelectors = function(selectors) {
        var queues = {};

        $.each(selectors, function(i, selector) {
            // Continue first iteration
            if(!i) return;

            selector = sanitizeSelector(selector).substr(5);
            queues[selector] = [];
        });

        return queues;
    };

    /**
     * Creates selectors which are needed to get the configuration for a specific viewport size. The selectors are in
     * most cases based on the {@link selectors} array.
     *
     * @param {Array} selectors - Selectors which are needing a configuration selector.
     * @returns {Array} Generated configuration selectors.
     */
    var createConfigSelectors = function(selectors) {
        $.each(selectors, function(i, selector) {
            if(!i) return;
            selector = sanitizeSelector(selector);

            configSelectors.push('*[' + selector + '-config]');
        });

        return configSelectors;
    };

    /**
     * Registers the discovered jQuery plugins and it's configuration into the {@link StateManager}, so we get fully
     * advantage of it's functionality.
     *
     * @param {Array} queues - Array of queues for the different viewport sizes.
     * @returns {Void}
     */
    var registerPluginsInStateManager = function(queues) {
        $.each(selectors, function(i, selector) {
            if(!i) return;

            selector = sanitizeSelector(selector).substr(5);
            if(!queues[selector] || !queues[selector].length) {
                return;
            }

            $.each(queues[selector], function() {
                var pluginConfig = this,
                    $item = pluginConfig._item,
                    pluginName = pluginConfig._plugin;

                delete pluginConfig._item;
                delete pluginConfig._plugin;

                StateManager.registerListener({
                    'type': selector,
                    'enter': function() {
                        $item[pluginName](pluginConfig);
                    },
                    'exit': function() {
                        $item.data('plugin_' + pluginName).destroy();
                    }
                });
            })
        });
    };

    /**
     * Starts the component on document ready.
     */
    $(function() {
        var queues = createQueueBasedOnSelectors(selectors);
        createConfigSelectors(selectors);

        $(selectors.join(', ')).each(function() {
            var $item = $(this);

            // Find the plugin and the viewport
            $.each(selectors, function(i, origSelector) {
                var selector = sanitizeSelector(origSelector);

                if($item.attr(selector)) {
                    if(selector === 'data-all') {
                        $.each(selectors, function(j, localOrigSelector) {
                            if(!j) return;

                            var localSelector = sanitizeSelector(localOrigSelector),
                                config = $.extend({}, getViewportStateConfig($item, localOrigSelector));

                            if($.isEmptyObject(config)) {
                                return;
                            }
                            config._plugin = $item.attr(selector);
                            config._item = $item;
                            queues[localSelector.substr(5)].push(config);
                        });
                    } else {
                        queues[selector.substr(5)].push($.extend({}, getViewportStateConfig($item, origSelector), {
                            _plugin: $item.attr(selector),
                            _item: $item
                        }));
                    }
                }
            });
        });

        registerPluginsInStateManager(queues);
    });
})(jQuery);