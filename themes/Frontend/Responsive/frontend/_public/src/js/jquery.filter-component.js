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

        'value': {
            updateFacet: function(data) {
                var me = this;

                if (me.isChecked($(me.$inputs))) {
                    return;
                }
                me.disable(me.$el, data === null);
                me.disable(me.$inputs, data === null);
            }
        },

        'value-list': {
            updateFacet: function(data) {
                this.updateValueList(data);
            }
        },

        'value-list-single': {
            compOpts: {
                checkboxSelector: 'input[type="checkbox"]'
            },

            initComponent: function() {
                var me = this;

                me.$inputs = me.$el.find(me.opts.checkboxSelector);

                me.registerComponentEvents();
            },

            validateComponentShouldBeDisabled: function(data, values, checkedIds) {
                if (checkedIds.length > 0) {
                    return false;
                }
                if (values && values.length <= 1) {
                    return true;
                }
                return data == null;
            },

            registerComponentEvents: function() {
                var me = this;

                me._on(me.$inputs, 'change', function(event) {
                    var $el = $(event.currentTarget);
                    if ($el.is(':checked')) {
                        me.$inputs.not($el).attr('disabled', 'disabled').parent().addClass('is--disabled');
                    }
                    me.onChange(event);
                });
            },

            updateFacet: function(data) {
                this.updateValueList(data);
            },

            validateElementShouldBeDisabled: function($element, activeIds, ids, checkedIds, value) {
                var val = $element.val() * 1;
                if (checkedIds.length > 0) {
                    return checkedIds.indexOf(val) === -1
                }
                if (activeIds.length > 0) {
                    return activeIds.indexOf(val) === -1
                }
                return ids.indexOf(val) === -1;
            }
        },

        'radio': {
            compOpts: {
                radioInputSelector: 'input[type="radio"]'
            },

            initComponent: function() {
                var me = this;
                me.$radioInputs = me.$el.find(me.opts.radioInputSelector);
                me.$inputs = me.$radioInputs;
                me.registerComponentEvents();
            },

            registerComponentEvents: function() {
                var me = this;
                me._on(me.$radioInputs, 'change', function(event) {
                    me.onChange(event);
                });
            },

            updateFacet: function(data) {
                this.updateValueList(data);
            }
        },

        'value-tree': {
            updateFacet: function(data) {
                this.updateValueList(data);
            },

            getValues: function(data, $elements) {
                return this.recursiveGetValues(data.values);
            },

            recursiveGetValues: function(values) {
                var items = [];
                var me = this;

                $(values).each(function (index, value) {
                    items.push(value);
                    if (value.values.length > 0) {
                        items = items.concat(me.recursiveGetValues(value.values));
                    }
                });
                return items;
            }
        },

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

            updateFacet: function(data) {
                var me = this;
                var isFiltered = (me.rangeSlider.minValue != me.rangeSlider.opts.startMin || me.rangeSlider.maxValue != me.rangeSlider.opts.startMax);

                if (isFiltered) {
                    me.disable(me.$el, false);
                    return;
                }

                if (data === null) {
                    me.disable(me.$el, true);
                    return;
                }

                if (data.min == data.max) {
                    me.disable(me.$el, true);
                    return;
                }

                me.disable(me.$el, false);

                me.rangeSlider.opts.rangeMax = data.max;
                me.rangeSlider.opts.rangeMin = data.min;
                me.rangeSlider.opts.startMax = data.activeMax;
                me.rangeSlider.opts.startMin = data.activeMin;
                me.rangeSlider.computeBaseValues();
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
                starInputSelector: '.filter-panel--star-rating input'
            },

            initComponent: function() {
                var me = this;

                me.$starInputs = me.$el.find(me.opts.starInputSelector);
                me.$inputs = me.$starInputs;

                me.registerComponentEvents();
            },

            registerComponentEvents: function() {
                var me = this;

                me._on(me.$starInputs, 'change', function(event) {
                    $(me.$starInputs).parents('.rating-star--outer-container').removeClass('is--active');

                    var $el = $(event.currentTarget);
                    $(me.$starInputs).not($el).prop("checked", false);

                    if ($el.is(":checked")) {
                        $el.parents('.rating-star--outer-container').addClass('is--active');
                        $el.removeAttr('disabled');
                    }

                    me.onChange(event);
                });
            },

            updateFacet: function(data) {
                this.updateValueList(data);
            },

            validateElementShouldBeDisabled: function($element, activeIds, ids, checkedIds, value) {
                var val = $element.val() * 1;

                if (value) {
                    $element.parents('.rating-star--outer-container').find('.rating-star--suffix-count').html('(' + value.label + ')');
                    return false;
                } else if (checkedIds.indexOf(val) === -1) {
                    $element.parents('.rating-star--outer-container').find('.rating-star--suffix-count').html('(' + 0 + ')');
                }

                return checkedIds.indexOf(val) === -1;
            },

            setDisabledClass: function($element, disabled) {
                $element.removeClass('is--disabled');
                $element.parents('.rating-star--outer-container').removeClass('is--disabled');
                if (disabled) {
                    $element.addClass('is--disabled');
                    $element.parents('.rating-star--outer-container').addClass('is--disabled');
                }
            },
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
             * @String value|range|media|pattern|radio|rating|value-list
             */
            type: 'value',

            /**
             * Defines the unique name, required for ajax reload
             * @String
             */
            facetName: null,

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
            me.facetName = me.$el.attr('data-facet-name');

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
            me.subscribeEvents();
        },

        subscribeEvents: function() {
            var me = this;

            $.subscribe('plugin/swListingActions/updateFacets', function(event, plugin, facets) {
                var facet = me.getFacet(facets, me.facetName);
                me.updateFacet(facet);
            });
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
        close: function()  {
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

            if (me.$el.hasClass('is--disabled')) {
                me.close();
                return;
            }

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
        },

        updateFacet: function(data) { },

        updateValueList: function(data) {
            var me = this;
            var $elements = me.convertToElementList(me.$inputs);
            var values = me.getValues(data, $elements);
            var ids = me.getValueIds(values);
            var activeIds = me.getActiveValueIds(values);
            var checkedIds = me.getElementValues(
                me.getCheckedElements($elements)
            );

            if (me.validateComponentShouldBeDisabled(data, values, checkedIds)) {
                me.disableAll($elements);
                return;
            }

            $elements.each(function(index, $element) {
                var value = me.findValue($element.val() * 1, values);

                var disable = me.validateElementShouldBeDisabled($element, activeIds, ids, checkedIds, value);
                me.disable($element, disable);
                me.setDisabledClass($element.parents('.filter-panel--input'), disable);
            });

            me.setDisabledClass(me.$el, me.allDisabled($elements));
        },

        validateComponentShouldBeDisabled: function(data, values, checkedIds) {
            return data == null && checkedIds.length <= 0;
        },

        disableAll: function($elements) {
            var me = this;

            $elements.each(function(index, $element) {
                me.disable($element, true);
                me.setDisabledClass($element.parents('.filter-panel--input'), true);
            });
            me.setDisabledClass(me.$el, true);
        },

        validateElementShouldBeDisabled: function($element, activeIds, ids, checkedIds, value) {
            var val = $element.val() * 1;

            if (activeIds.indexOf(val) >= 0) {
                return false;
            } else if (ids.indexOf(val) >= 0) {
                return false;
            } else if (checkedIds.indexOf(val) >= 0) {
                return false;
            }
            return true;
        },

        getFacet: function(facets, name) {
            for (var key in facets){
                var facet = facets[key];
                if (facet.facetName == name) {
                    return facet;
                }
            }
            return null;
        },

        isChecked: function($element) {
            return $element.is(':checked');
        },

        getCheckedElements: function($elements) {
            var $actives = [], me = this;

            $elements.each(function(index, $element) {
                if (me.isChecked($element)) {
                    $actives.push($element);
                }
            });
            return $actives;
        },

        getElementValues: function($elements) {
            return $elements.map(function($element) {
                return $element.val() * 1;
            });
        },

        findValue: function(val, values) {
            var value = null;
            $(values).each(function(index, item) {
                if (item.id == val) {
                    value = item;
                }
            });
            return value;
        },

        disable: function($element, disabled) {
            this.setDisabledClass($element, disabled);
            this.disableElement($element, disabled);
        },

        disableElement: function($element, disabled) {
            $element.removeAttr('disabled');
            if (disabled) {
                $element.prop('disabled', 'disabled');
            }
        },

        setDisabledClass: function($element, disabled) {
            $element.removeClass('is--disabled');
            if (disabled) {
                $element.addClass('is--disabled');
            }
        },

        allDisabled: function($elements) {
            var me = this, allDisabled = true;
            $elements.each(function(index, $element) {
                if (!me.isDisabled($element)) {
                    allDisabled = false;
                }
            });
            return allDisabled;
        },

        isDisabled: function($element) {
            return $element.hasClass('is--disabled');
        },

        getValueIds: function(values) {
            var ids = [];
            $(values).each(function(index, value) {
                ids.push(value.id);
            });
            return ids;
        },

        getActiveValueIds: function(values) {
            var ids = [];
            $(values).each(function(index, value) {
                if (value.active) {
                    ids.push(value.id);
                }
            });
            return ids;
        },

        convertToElementList: function(elements) {
            var $elements = [];
            $(elements).each(function(index, element) {
                $elements.push($(element));
            });
            return $($elements);
        },

        updateList: function(data, elements, considerActives) {
            var me = this,
                allDisabled = true,
                $elements = $(elements),
                activeItems = [],
                ids = [],
                items = me.getItems(data, $elements);

            var hasActiveElement = me.hasActiveElement($elements);

            if (data === null && !hasActiveElement) {
                $elements.each(function(index, input) {
                    var $input = $(input);
                    me.setDisabledClass($input, true);
                });
                me.setDisabledClass(me.$el, true);
                return;
            }

            $(items).each(function(index, item) {
                if (item.active) {
                    activeItems.push(item.id);
                }
                ids.push(item.id);
            });

            $elements.each(function(index, input) {
                var $input = $(input);
                var val = $input.val() * 1;
                var disabled = (ids.indexOf(val) == -1);

                if (considerActives === true) {
                    disabled = disabled || (activeItems.length > 0 && activeItems.indexOf(val) == -1);
                }

                if (!disabled) {
                    allDisabled = false;
                }

                me.setDisabledClass($input, disabled);
                me.setDisabledClass($input.parents('.filter-panel--input'), disabled);
            });

            me.setDisabledClass(me.$el, allDisabled);
        },

        hasActiveElement: function($elements) {
            var hasActive = false;

            $elements.each(function(index, item) {
                if (item.checked) {
                    hasActive = true;
                }
            });
            return hasActive;
        },

        getValues: function(data, $elements) {
            var me = this;

            if (!data) {
                return [];
            }

            if (data.hasOwnProperty('values')) {
                return data.values;
            }

            var values = [];

            $(data.facetResults).each(function(index, group) {
                $(group.values).each(function(index, item) {
                    if (me.valueExists(item.id, $elements)) {
                        values.push(item);
                    }
                });
            });
            return values;
        },

        valueExists: function(value, $elements) {
            var exists = false;

            $elements.each(function(index, input) {
                if ($(input).val() == value) {
                    exists = true;
                    return false;
                }
            });
            return exists;
        }
    });
})(jQuery, window, document, undefined);
