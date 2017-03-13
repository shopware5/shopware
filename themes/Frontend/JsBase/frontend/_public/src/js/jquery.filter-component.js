;(function($, window, document, undefined) {
    'use strict';

    /**
     * An object holding the configuration objects
     * of special component types. The specific
     * configuration objects are getting merged
     * into the original plugin for the corresponding
     * component type. This is used for special components
     * to override some of the base methods to make them
     * work properly and for firing correct change events.
     *
     * @type {}
     */
    var specialComponents = {

        /**
         * Range-Slider component
         */
        'range': {

            compOpts: {
                rangeSliderSelector: '*[data-range-slider="true"]'
            },

            initComponent: function() {
                var me = this;

                me.$rangeSliderEl = me.$el.find(me.opts.rangeSliderSelector);
                me.$rangeInputs = me.$rangeSliderEl.find('input');
                me.rangeSlider = me.$rangeSliderEl.data('plugin_swRangeSlider');

                me.registerComponentEvents();
            },

            registerComponentEvents: function() {
                var me = this;

                me._on(me.$rangeInputs, 'change', $.proxy(me.onChange, me));
            }
        },

        /**
         * Rating component
         */
        'rating': {

            compOpts: {
                starInputSelector: '.rating-star--input'
            },

            initComponent: function() {
                var me = this;

                me.$starInputs = me.$el.find(me.opts.starInputSelector);

                me.registerComponentEvents();
            },

            registerComponentEvents: function() {
                var me = this;

                me._on(me.$starInputs, 'change', function(event) {
                    var $el = $(event.currentTarget);

                    if ($el.is(':checked')) {
                        me.onChange(event);
                    }
                });
            }
        },

        /**
         * Radio component
         */
        'radio': {

            compOpts: {
                radioInputSelector: 'input[type="radio"]'
            },

            initComponent: function() {
                var me = this;

                me.$radioInputs = me.$el.find(me.opts.radioInputSelector);

                me.registerComponentEvents();
            },

            registerComponentEvents: function() {
                var me = this;

                me._on(me.$radioInputs, 'change', function(event) {
                    me.onChange(event);
                });
            }
        }
    };

    /**
     * The actual plugin.
     */
    $.plugin('swFilterComponent', {

        defaults: {
            /**
             * The type of the filter component
             *
             * @String value|range|image|pattern|radio|rating
             */
            type: 'value',

            /**
             * The css class for collapsing the filter component flyout.
             */
            collapseCls: 'is--collapsed',

            /**
             * The css selector for the title element of the filter flyout.
             */
            titleSelector: '.filter-panel--title',

            /**
             * The css selector for checkbox elements in the components.
             */
            checkBoxSelector: 'input[type="checkbox"]'
        },

        /**
         * Initializes the plugin.
         */
        init: function() {
            var me = this;

            me.applyDataAttributes();

            me.type = me.$el.attr('data-filter-type') || me.opts.type;

            me.$title = me.$el.find(me.opts.titleSelector);
            me.$siblings = me.$el.siblings('*[data-filter-type]');

            /**
             * Checks if the type of the component uses
             * any special configuration or methods.
             */
            if (specialComponents[me.type] !== undefined) {
                /**
                 * Extends the plugin object with the
                 * corresponding component object.
                 */
                $.extend(me, specialComponents[me.type]);

                /**
                 * Merges the component options into
                 * the plugin options.
                 */
                $.extend(me.opts, me.compOpts);
            }

            me.initComponent();
            me.registerEvents();
        },

        /**
         * Initializes the component based on the type.
         * This method may be overwritten by special components.
         */
        initComponent: function() {
            var me = this;

            me.$inputs = me.$el.find(me.opts.checkBoxSelector);

            me.registerComponentEvents();

            $.publish('plugin/swFilterComponent/onInitComponent', [ me ]);
        },

        /**
         * Registers all necessary global event listeners.
         */
        registerEvents: function() {
            var me = this;

            if (me.type != 'value') {
                me._on(me.$title, 'click', $.proxy(me.toggleCollapse, me, true));
            }

            $.publish('plugin/swFilterComponent/onRegisterEvents', [ me ]);
        },

        /**
         * Registers all necessary events for the component.
         * This method may be overwritten by special components.
         */
        registerComponentEvents: function() {
            var me = this;

            me._on(me.$inputs, 'change', $.proxy(me.onChange, me));

            $.publish('plugin/swFilterComponent/onRegisterComponentEvents', [ me ]);
        },

        /**
         * Called on the change events of each component.
         * Triggers a custom change event on the component,
         * so that other plugins can listen to changes in
         * the different components.
         *
         * @param event
         */
        onChange: function(event) {
            var me = this,
                $el = $(event.currentTarget);

            me.$el.trigger('onChange', [me, $el]);

            $.publish('plugin/swFilterComponent/onChange', [ me, event ]);
        },

        /**
         * Returns the type of the component.
         *
         * @returns {type|*}
         */
        getType: function() {
            return this.type;
        },

        /**
         * Opens the component flyout panel.
         *
         * @param closeSiblings
         */
        open: function(closeSiblings) {
            var me = this;

            if (closeSiblings) {
                me.$siblings.removeClass(me.opts.collapseCls);
            }

            me.$el.addClass(me.opts.collapseCls);

            $.publish('plugin/swFilterComponent/onOpen', [ me ]);
        },

        /**
         * Closes the component flyout panel.
         */
        close: function() {
            var me = this;

            me.$el.removeClass(me.opts.collapseCls);

            $.publish('plugin/swFilterComponent/onClose', [ me ]);
        },

        /**
         * Toggles the viewed state of the component.
         */
        toggleCollapse: function() {
            var me = this,
                shouldOpen = !me.$el.hasClass(me.opts.collapseCls);

            if (shouldOpen) {
                me.open(true);
            } else {
                me.close();
            }

            $.publish('plugin/swFilterComponent/onToggleCollapse', [ me, shouldOpen ]);
        },

        /**
         * Destroys the plugin.
         */
        destroy: function() {
            var me = this;

            me._destroy();
        }
    });
})(jQuery, window, document, undefined);
