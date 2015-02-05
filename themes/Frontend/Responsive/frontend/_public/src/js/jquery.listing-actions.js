;(function($, StateManager, undefined) {
    'use strict';

    var $body = $('body');

    /**
     * Plugin for handling the filter functionality and
     * all other actions for changing the product listing.
     * It handles the current set of category parameters and applies
     * them to the current top location url when something was
     * changed by the user over the filter form, action forms or
     * the action links.
     *
     * ** Filter Form **
     * The filter form exists of different filter components,
     * the filter submit button and the labels for active filters.
     * Each component is rendered in a single panel and has its own functionality.
     * All single components are handled by the "filterComponent" plugin.
     * The plugin for the components fires correct change events for each type
     * of component, so the "listingActions" plugin can listen on the changes
     * of the user. A filter form has to be a normal form with the selector,
     * which is set in the plugin options, so the form can be found by the plugin.
     * The actual submitting of the form will always be prevented to build the complex
     * category parameters out of the serialized form data.
     *
     * Example:
     * <form id="filter" method="get" action="" data-filter-form="true">
     *
     *
     * ** Action Forms **
     * You can apply different category parameters over additional action forms.
     * In most cases these forms are auto submitting forms using the "autoSubmit" plugin,
     * which change just one parameter via a combo- or checkbox. So with these
     * action forms you have the possibility to apply all kind of category parameters
     * like sorting, layout type, number of products per page etc.
     *
     * Example:
     * <form method="get" data-action-form="true" action="">
     *  <select name="{$shortParameters.sSort}" data-auto-submit="true">
     *      {...}
     *  </select>
     * </form>
     *
     *
     * ** Action Links **
     * You can also apply different category parameter via direct links.
     * Just use the corresponding get parameters in the href attribute of the link.
     * The new parameter will be added to the existing category parameters.
     * If the parameter already exists the value will be updated with the new one.
     *
     * Example:
     * <a href="?p=1&l=list" data-action-link="true">list view</a>
     *
     */
    $.plugin('listingActions', {

        defaults: {

            /**
             * The selector for the filter panel form.
             */
            filterFormSelector: '*[data-filter-form="true"]',

            /**
             * The selector for the single filter components.
             */
            filterComponentSelector: '*[data-filter-type]',

            /**
             * The selector for the button which shows and hides the filter panel.
             */
            filterTriggerSelector: '*[data-filter-trigger="true"]',

            /**
             * The selector for the filter panel element.
             */
            filterContainerSelector: '.action--filter-options',

            /**
             * The selector for additional listing action forms.
             */
            actionFormSelector: '*[data-action-form="true"]',

            /**
             * The selector for additional listing action links.
             */
            actionLinkSelector: '*[data-action-link="true"]',

            /**
             * The selector for the container where the active filters are shown.
             */
            activeFilterContSelector: '.filter--active-container',

            /**
             * The selector for the button which applies the filter changes.
             */
            applyFilterBtnSelector: '.filter--btn-apply',

            /**
             * The css class for active filter labels.
             */
            activeFilterCls: 'filter--active',

            /**
             * The close icon element which is used for the active filter labels.
             */
            activeFilterIconCls: 'filter--active-icon',

            /**
             * The css class for the filter panel when it is completely collapsed.
             */
            collapsedCls: 'is--collapsed',

            /**
             * The css class for the filter container when it shows only the preview of the active filters.
             */
            hasActiveFilterCls: 'is--active-filter',

            /**
             * The css class for active states.
             */
            activeCls: 'is--active',

            /**
             * The css class for disabled states.
             */
            disabledCls: 'is--disabled',

            /**
             * The characters used as a prefix to identify property field names.
             * The properties will be merged in one GET parameter.
             * For example properties with field names beginning with __f__"ID"
             * will be merged to &f=ID1|ID2|ID3|ID4 etc.
             *
             */
            propertyPrefixChar: '__',

            /**
             * The buffer time in ms to wait between each action before firing the ajax call.
             */
            bufferTime: 850,

            /**
             * The time in ms for animations.
             */
            animationSpeed: 300
        },

        /**
         * Initializes the plugin.
         */
        init: function() {
            var me = this;

            me.applyDataAttributes();

            me.$filterForm = $(me.opts.filterFormSelector);
            me.$filterComponents = me.$el.find(me.opts.filterComponentSelector);
            me.$filterTrigger = me.$el.find(me.opts.filterTriggerSelector);
            me.$filterTriggerIcon = me.$filterTrigger.find('.action--collapse-icon');
            me.$filterCont = me.$el.find(me.opts.filterContainerSelector);
            me.$actionForms = $(me.opts.actionFormSelector);
            me.$actionLinks = $(me.opts.actionLinkSelector);
            me.$activeFilterCont = me.$el.find(me.opts.activeFilterContSelector);
            me.$applyFilterBtn = me.$el.find(me.opts.applyFilterBtnSelector);

            me.resultCountURL = me.$filterForm.attr('data-count-ctrl');
            me.controllerURL = top.location.href.split('?')[0];
            me.categoryId = me.$el.attr('data-category-id');
            me.resetLabel = me.$activeFilterCont.attr('data-reset-label');
            me.propertyFieldNames = [];
            me.activeFilterElements = {};
            me.categoryParams = {};
            me.urlParams = '';
            me.bufferTimeout = false;

            me.getPropertyFieldNames();
            me.setCategoryParamsFromTopLocation();
            me.createActiveFiltersFromCategoryParams();
            me.createUrlParams();

            me.initStateHandling();
            me.registerEvents();
        },

        /**
         * Initializes the state manager for specific device options.
         */
        initStateHandling: function() {
            var me = this;

            StateManager.registerListener([{
                state: 'xs',
                enter: function() {
                    me.$filterForm.removeAttr('style');

                    me.$activeFilterCont.removeAttr('style')
                        .removeClass(me.opts.disabledCls);

                    me.$filterCont.removeClass(me.opts.collapsedCls)
                        .removeClass(me.opts.hasActiveFilterCls);

                    me.$filterTrigger.removeClass(me.opts.activeCls);
                },
                exit: function() {
                    me.$filterTriggerIcon.html('').removeAttr('style');
                }
            }]);
        },

        /**
         * Registers all necessary events.
         */
        registerEvents: function() {
            var me = this;

            me._on(me.$filterForm, 'submit',  $.proxy(me.onFilterSubmit, me));
            me._on(me.$actionForms, 'submit',  $.proxy(me.onActionSubmit, me));
            me._on(me.$actionLinks, 'click', $.proxy(me.onActionLink, me));
            me._on(me.$filterComponents, 'onChange', $.proxy(me.onComponentChange, me));
            me._on(me.$filterTrigger, 'click', $.proxy(me.onFilterTriggerClick, me));

            me._on($body, 'click', $.proxy(me.onBodyClick, me));

            me.$el.delegate('.' + me.opts.activeFilterCls, 'click', $.proxy(me.onActiveFilterClick, me));
        },

        /**
         * Called by event listener on submitting the filter form.
         * Gets the serialized form data and applies it to the category params.
         *
         * @param event
         */
        onFilterSubmit: function(event) {
            event.preventDefault();

            var me = this,
                formData = me.$filterForm.serializeArray(),
                categoryParams = me.setCategoryParamsFromData(formData);

            me.applyCategoryParams(categoryParams);
        },

        /**
         * Called by event listener on submitting an action form.
         * Gets the serialized form data and applies it to the category params.
         *
         * @param event
         */
        onActionSubmit: function(event) {
            event.preventDefault();

            var me = this,
                $form = $(event.currentTarget),
                formData = $form.serializeArray(),
                categoryParams = me.setCategoryParamsFromData(formData, true);

            me.applyCategoryParams(categoryParams);
        },

        /**
         * Called by event listener on clicking on an action link.
         * Reads the parameter in the href attribute and adds it to the
         * category params.
         *
         * @param event
         */
        onActionLink: function(event) {
            event.preventDefault();

            var me = this,
                $link = $(event.currentTarget),
                linkParams = $link.attr('href').split('?')[1];

            me.applyCategoryParams(
                me.setCategoryParamsFromUrlParams(linkParams)
            );
        },

        /**
         * Called by event listener on clicking the filter trigger button.
         * Opens and closes the filter form panel.
         *
         * @param event
         */
        onFilterTriggerClick: function(event) {
            event.preventDefault();

            if (StateManager.isCurrentState(['xs', 's'])) {
                return;
            }

            var me = this;

            if (me.$filterCont.hasClass(me.opts.collapsedCls)) {
                me.closeFilterPanel();
            } else {
                me.openFilterPanel();
            }
        },

        /**
         * Closes all filter panels if the user clicks anywhere else.
         *
         * @param event
         */
        onBodyClick: function(event) {
            var me = this,
                $target = $(event.target);

            if (!$target.is(me.opts.filterComponentSelector + ', ' + me.opts.filterComponentSelector + ' *')) {
                $.each(me.$filterComponents, function(index, item) {
                    $(item).data('plugin_filterComponent').close();
                });
            }
        },

        /**
         * Called by event listener on the change event of the
         * single filter components. Applies the changes of the
         * component values to the category params.
         *
         * @param event
         * @param component
         * @param $el
         */
        onComponentChange: function(event, component, $el) {
            var me = this,
                formData = me.$filterForm.serializeArray(),
                categoryParams = me.setCategoryParamsFromData(formData),
                urlParams = me.createUrlParams(categoryParams);

            me.createActiveFiltersFromCategoryParams(categoryParams);

            me.buffer($.proxy(me.getFilterResult, me, urlParams), me.opts.bufferTime)
        },

        /**
         * Called by event listener on clicking an active filter label.
         * It removes the clicked filter param form the set of active filters
         * and updates the specific component.
         *
         * @param event
         */
        onActiveFilterClick: function(event) {
            var me = this,
                $activeFilter = $(event.currentTarget),
                param = $activeFilter.attr('data-filter-param');

            if (param == 'reset') {
                $.each(me.activeFilterElements, function(key) {
                    me.removeActiveFilter(key);
                    me.resetFilterProperty(key);
                });

                me.applyCategoryParams();

            } else if (!me.$activeFilterCont.hasClass(me.opts.disabledCls)) {
                me.removeActiveFilter(param);
                me.resetFilterProperty(param);
            }
        },

        getPropertyFieldNames: function() {
            var me = this;

            $.each(me.$filterComponents, function(index, item) {
                var $comp = $(item),
                    type = $comp.attr('data-filter-type'),
                    fieldName = $comp.attr('data-field-name');

                if ((type == 'value-list' || type == 'media') &&
                    me.propertyFieldNames.indexOf(fieldName) == -1) {
                    me.propertyFieldNames.push(fieldName);
                }
            });

            return me.propertyFieldNames;
        },

        /**
         * Converts given form data to the category parameter object.
         * You can choose to either extend or override the existing object.
         *
         * @param formData
         * @param extend
         * @returns {*}
         */
        setCategoryParamsFromData: function(formData, extend) {
            var me = this,
                tempParams = {};

            $.each(formData, function(index, item) {
                if (item['value']) tempParams[item['name']] = item['value'];
            });

            if (extend) {
                return $.extend(me.categoryParams, tempParams);
            }

            return me.categoryParams = tempParams;
        },

        /**
         * Converts top location parameters to the category parameter object.
         *
         * @returns {*}
         */
        setCategoryParamsFromTopLocation: function() {
            var me = this,
                urlParams = decodeURI(top.location.search).substr(1);

            return me.setCategoryParamsFromUrlParams(urlParams);
        },

        /**
         * Converts url parameters to the category parameter object.
         *
         * @param urlParamString
         * @returns {{}|*}
         */
        setCategoryParamsFromUrlParams: function(urlParamString) {

            if (urlParamString.length <= 0) {
                return {};
            }

            var me = this,
                urlParams = decodeURIComponent(urlParamString),
                params = urlParams.split('&');

            $.each(params, function(index, item) {
                var param = item.split('=');

                if (param[1] == 'reset') {
                    delete me.categoryParams[param[0]];

                } else if (me.propertyFieldNames.indexOf(param[0]) != -1) {
                    var properties = param[1].split('|');

                    $.each(properties, function(index, property) {
                        me.categoryParams[me.opts.propertyPrefixChar + param[0] + me.opts.propertyPrefixChar + property] = property;
                    });

                } else {
                    me.categoryParams[param[0]] = param[1];
                }
            });

            return me.categoryParams;
        },

        /**
         * Converts the category parameter object to url parameters
         * and applies the url parameters to the current top location.
         *
         * @param categoryParams
         */
        applyCategoryParams: function(categoryParams) {
            var me = this,
                params = categoryParams || me.categoryParams,
                urlParams = me.createUrlParams(params);

            me.applyUrlParams(urlParams);
        },

        /**
         * Converts the category parameter object to url parameters.
         *
         * @param categoryParams
         * @returns {string}
         */
        createUrlParams: function(categoryParams) {
            var me = this,
                params = categoryParams || me.categoryParams,
                filterParams = '', propertyParams = {};

            $.each(params, function(key, value) {
                var urlParamChar = (filterParams.length > 0) ? '&' : '?';

                if (key.substr(0, 2) == me.opts.propertyPrefixChar) {
                    var propertyKey = key.split(me.opts.propertyPrefixChar)[1];

                    if (propertyParams[propertyKey] !== undefined) {
                        propertyParams[propertyKey] += '|' + value;
                    } else {
                        propertyParams[propertyKey] = value;
                    }
                } else {
                    filterParams += urlParamChar + key + '=' + value;
                }
            });

            $.each(propertyParams, function(key, value) {
                filterParams += '&' + key + '=' + value;
            });

            return me.urlParams = filterParams;
        },

        /**
         * Applies given url params to the top location.
         *
         * @param urlParams | String
         */
        applyUrlParams: function(urlParams) {
            var me = this,
                params = urlParams || me.urlParams;

            top.location.href = me.getListingUrl(params, true);
        },

        /**
         * Returns the full url path to the listing
         * including all current url params.
         *
         * @param urlParams
         * @param encode | Boolean
         * @returns {*}
         */
        getListingUrl: function(urlParams, encode) {
            var me = this,
                params = urlParams || me.urlParams;

            if (encode) {
                return encodeURI(me.controllerURL + params);
            }

            return me.controllerURL + params;
        },

        /**
         * Buffers a function by the given buffer time.
         *
         * @param func
         * @param bufferTime
         */
        buffer: function(func, bufferTime) {
            var me = this;

            if (me.bufferTimeout) {
                clearTimeout(me.bufferTimeout);
            }

            me.bufferTimeout = setTimeout(func, bufferTime);
        },

        /**
         * Resets the current buffer timeout.
         */
        resetBuffer: function() {
            this.bufferTimeout = false;
        },

        /**
         * Gets the counted result of found products
         * with the current applied category parameters.
         * Updates the filter submit button on success.
         *
         * @param urlParams
         */
        getFilterResult: function(urlParams) {
            var me = this,
                params = urlParams || me.urlParams;

            me.resetBuffer();

            $.ajax({
                type: 'get',
                url: me.resultCountURL + params,
                success: function(response) {
                    me.updateFilterButton(response.totalCount);
                }
            });
        },

        /**
         * Updates the layout of the filter submit button
         * with the new count of found products.
         *
         * @param count
         */
        updateFilterButton: function(count) {
            var me = this;

            me.$applyFilterBtn.find('.filter--count').html(count);

            if (count <= 0) {
                me.$applyFilterBtn.attr('disabled', 'disabled');
            } else {
                me.$applyFilterBtn.removeAttr('disabled');
            }
        },

        /**
         * Updates the layout of the filter trigger button
         * on mobile viewports with the current count of active filters.
         *
         * @param activeFilterCount
         */
        updateFilterTriggerButton: function(activeFilterCount) {
            var me = this;

            if (!StateManager.isCurrentState(['xs', 's'])) {
                return;
            }

            if (activeFilterCount > 0) {
                me.$filterTriggerIcon.html(activeFilterCount).show();
            } else {
                me.$filterTriggerIcon.html('').hide();
            }
        },

        /**
         * Creates the labels for active filters from the category params.
         *
         * @param categoryParams
         * @param checkContainerState
         */
        createActiveFiltersFromCategoryParams: function(categoryParams, checkContainerState) {
            var me = this,
                count = 0,
                params = categoryParams || me.categoryParams;

            $.each(me.activeFilterElements, function(key) {
                if (params[key] === undefined || params[key] == 0) {
                    me.removeActiveFilter(key);
                }
            });

            $.each(params, function(key, value) {
                me.createActiveFilter(key, value);
            });

            $.each(me.activeFilterElements, function() {
                count++;
            });

            if (count > 1) {
                me.createActiveFilterElement('reset', me.resetLabel);
            }

            if (StateManager.isCurrentState(['xs', 's'])) {
                me.updateFilterTriggerButton(count);
            } else {
                me.$filterCont.toggleClass(me.opts.hasActiveFilterCls, (count > 0));
                me.$activeFilterCont.toggleClass(me.opts.disabledCls, !me.$filterCont.hasClass(me.opts.collapsedCls));
            }
        },

        /**
         * Creates an active filter label for the given parameter.
         * If the label for the given parameter already
         * exists it will be updated.
         *
         * @param param
         * @param value
         */
        createActiveFilter: function(param, value) {
            var me = this,
                label = me.createActiveFilterLabel(param, value);

            if (label !== undefined && label.length) {
                if (me.activeFilterElements[param] !== undefined) {
                    me.updateActiveFilterElement(param, label)
                } else {
                    me.createActiveFilterElement(param, label);
                }
            }
        },

        /**
         * Creates the DOM element for an active filter label.
         *
         * @param param
         * @param label
         */
        createActiveFilterElement: function(param, label) {
            var me = this;

            me.activeFilterElements[param] = $('<span>', {
                'class': me.opts.activeFilterCls,
                'html': me.getLabelIcon() + label,
                'data-filter-param': param
            }).appendTo(me.$activeFilterCont);
        },

        /**
         * Updates the layout of an existing filter label element.
         *
         * @param param
         * @param label
         */
        updateActiveFilterElement: function(param, label) {
            var me = this;

            me.activeFilterElements[param].html(me.getLabelIcon() + label);
        },

        /**
         * Removes an active filter label from the set and from the DOM.
         *
         * @param param
         */
        removeActiveFilter: function(param) {
            var me = this;

            me.activeFilterElements[param].remove();
            delete me.activeFilterElements[param];
        },

        /**
         * Resets a filter parameter and updates
         * the component based on the component type.
         *
         * @param param
         */
        resetFilterProperty: function(param) {
            var me = this;

            if (param == 'min' || param == 'max') {
                var rangeSlider = me.$el.find('[data-range-slider="true"]').data('plugin_rangeSlider');
                    rangeSlider.reset(param);

            } else if (param == 'rating') {
                me.$el.find('#star--reset').prop('checked', true).trigger('change');

            } else {
                me.$el.find('[name="'+param+'"]')
                    .removeAttr('checked')
                    .trigger('change');
            }
        },

        /**
         * Creates the correct label content for an active
         * filter label based on the component type.
         *
         * @param param
         * @param value
         * @returns {string}
         */
        createActiveFilterLabel: function(param, value) {
            var me = this,
                $label,
                labelText = '';

            if (param == 'rating' && value > 0) {
                labelText = me.createStarLabel(value);

            } else {
                $label = me.$filterForm.find('label[for="'+param+'"]');

                if (param == 'min' || param == 'max') {
                    labelText = $label.prev('span').html() + $label.html();
                } else if ($label.find('img').length) {
                    labelText = $label.find('img').attr('alt');
                } else if (value > 0) {
                    labelText = $label.html();
                }
            }

            return labelText;
        },

        /**
         * Creates the label content for the special rating component.
         *
         * @param stars | Integer
         * @returns {string}
         */
        createStarLabel: function(stars) {
            var label = '', i = 0;

            for (i; i < 5; i++) {
                if (i < stars) {
                    label += '<i class="icon--star"></i>';
                } else {
                    label += '<i class="icon--star-empty"></i>';
                }
            }

            return label;
        },

        /**
         * Returns the html string of the delete icon
         * for an active filter label.
         *
         * @returns {string}
         */
        getLabelIcon: function() {
            return '<span class="' + this.opts.activeFilterIconCls + '"></span>';
        },

        /**
         * Opens the filter form panel based on the current state.
         */
        openFilterPanel: function() {
            var me = this;

            if (!me.$filterCont.hasClass(me.opts.hasActiveFilterCls)) {
                me.$activeFilterCont.slideDown(me.opts.animationSpeed);
            }

            me.$filterForm.slideDown(me.opts.animationSpeed);
            me.$activeFilterCont.removeClass(me.opts.disabledCls);
            me.$filterCont.addClass(me.opts.collapsedCls);
            me.$filterTrigger.addClass(me.opts.activeCls);
        },

        /**
         * Closes the filter form panel based on the current state.
         */
        closeFilterPanel: function() {
            var me = this;

            if (!me.$filterCont.hasClass(me.opts.hasActiveFilterCls)) {
                me.$activeFilterCont.slideUp(me.opts.animationSpeed);
            }

            me.$filterForm.slideUp(me.opts.animationSpeed);
            me.$activeFilterCont.addClass(me.opts.disabledCls);
            me.$filterCont.removeClass(me.opts.collapsedCls);
            me.$filterTrigger.removeClass(me.opts.activeCls);
        },

        /**
         * Destroys the plugin.
         */
        destroy: function() {
            var me = this;

            me.$el.undelegate('.' + me.opts.activeFilterCls, 'click');

            me._destroy();
        }
    });
})(jQuery, StateManager, undefined);