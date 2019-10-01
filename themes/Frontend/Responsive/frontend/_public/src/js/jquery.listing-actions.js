;(function ($, window, StateManager, undefined) {
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
     * <form id="filter" method="get" data-filter-form="true">
     *
     * ** Action Forms **
     * You can apply different category parameters over additional action forms.
     * In most cases these forms are auto submitting forms using the "autoSubmit" plugin,
     * which change just one parameter via a combo- or checkbox. So with these
     * action forms you have the possibility to apply all kind of category parameters
     * like sorting, layout type, number of products per page etc.
     *
     * Example:
     * <form method="get" data-action-form="true">
     *  <select name="{$shortParameters.sSort}" data-auto-submit="true">
     *      {...}
     *  </select>
     * </form>
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
    $.plugin('swListingActions', {

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
             * The selector for the icon inside the filter trigger button.
             */
            filterTriggerIconSelector: '.action--collapse-icon',

            /**
             * The selector for the filter panel element.
             */
            filterContainerSelector: '.action--filter-options',

            /**
             * The selector for the inner filter container which used to for the loading indicator
             * if the off canvas menu is active
             */
            filterInnerContainerSelector: '.filter--container',

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
             * Selector for the element that contains the found product count.
             */
            filterCountSelector: '.filter--count',

            /**
             * Class that will be added to the apply filter button
             * when loading the results.
             */
            loadingClass: 'is--loading',

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
            animationSpeed: 400,

            /** Css class which will be added when the user uses instant filter results */
            instantFilterActiveCls: 'is--instant-filter-active',

            /**
             * class to select the listing div
             */
            listingSelector: '.listing--container > .listing',

            /**
             * class to select the pagination bars
             */
            paginationSelector: '.listing--paging.panel--paging',

            /**
             * data attribute which indicates whether infinite scrolling is used or not
             */
            infiniteScrollingAttribute: 'data-infinite-scrolling',

            /**
             * selector for the page size select box
             */
            paginationBarPerPageSelector: '.per-page--field.action--field',

            /**
             * selector for the hidden input field of the filter form which stores the current page
             */
            pageInputSelector: 'input[name=p]',

            /**
             * selector for the hidden input field of the filter form which stores the current sorting
             */
            sortInputSelector: 'input[name=o]',

            /**
             * selector for the hidden input field of the filter form which stores the current amount of products per page
             */
            perPageInputSelector: 'input[name=n]',

            /**
             * selector for the sorting select box
             */
            sortActionFormSelector: '.action--sort',

            /**
             * selector for the products per page select box
             */
            perPageActionFormSelector: '.action--per-page',

            /**
             * selector for the wrapper of the whole listing
             */
            listingWrapperSelector: '.listing--wrapper',

            /**
             * The selector for the element which get the loading indicator after customer activates a filter
             */
            loadingIndSelector: '.listing--wrapper',

            /**
             * The selector for "no filter result found" container
             */
            noResultContainerSelector: '.listing-no-filter-result .alert',

            /**
             * Class for loading indicator, added and removed on the configurable `listingSelector` element
             */
            isLoadingCls: 'is--loading',

            /**
             * Configuration for the loading indicator
             */
            loadingIndConfig: {
                theme: 'light',
                animationSpeed: 100,
                closeOnClick: false
            },

            /**
             * selector for the filter close button, which is only visible in off canvas
             */
            filterCloseBtnSelector: '.filter--close-btn',

            /**
             * icon for the filter close button
             */
            closeFilterOffCanvasBtnIcon: '<i class="icon--arrow-right"></i>',

            /**
             * selector for the search page headline
             */
            searchHeadlineProductCountSelector: '.search--headline .headline--product-count',

            /**
             * selector for the filter facet container
             */
            filterFacetContainerSelector: '.filter--facet-container',

            /**
             * selector for the filter action button bottom
             */
            filterActionButtonBottomSelector: '.filter--actions.filter--actions-bottom',

            /**
             * selector for the parent of the loading indicator in if the filters in sidebar mode
             */
            sidebarLoadingIndicatorParentSelector: '.content-main--inner',

            /**
             * selector for the jquery.add-article plugin to enable support for the off canvas cart
             */
            addArticleSelector: '*[data-add-article="true"]',

            /**
             * Threshold for the scroll position when the user switches pages (in both modes e.g. infinite scrolling & page change)
             */
            listingScrollThreshold: -10
        },

        /**
         * Initializes the plugin.
         */
        init: function () {
            var me = this,
                filterCount;

            me.applyDataAttributes();

            $('.sidebar-filter--loader').appendTo('.sidebar-filter--content');
            me.$filterForm = $(me.opts.filterFormSelector);
            me.$filterComponents = me.$filterForm.find(me.opts.filterComponentSelector);
            me.$filterTrigger = me.$el.find(me.opts.filterTriggerSelector);
            me.$filterTriggerIcon = me.$filterTrigger.find(me.opts.filterTriggerIconSelector);
            me.$filterCont = $(me.opts.filterContainerSelector);
            me.$actionForms = $(me.opts.actionFormSelector);
            me.$actionLinks = $(me.opts.actionLinkSelector);
            me.$activeFilterCont = me.$filterForm.find(me.opts.activeFilterContSelector);
            me.$applyFilterBtn = me.$filterForm.find(me.opts.applyFilterBtnSelector);
            me.$listing = $(me.opts.listingSelector);
            me.$pageInput = $(me.$filterForm.find(me.opts.pageInputSelector));
            me.$sortInput = $(me.$filterForm.find(me.opts.sortInputSelector));
            me.$perPageInput = $(me.$filterForm.find(me.opts.perPageInputSelector));
            me.$listingWrapper = me.$el.parent(me.opts.listingWrapperSelector);
            me.$closeFilterOffCanvasBtn = $(me.opts.filterCloseBtnSelector);
            me.$filterFacetContainer = me.$filterForm.find(me.opts.filterFacetContainerSelector);
            me.$filterActionButtonBottom = me.$filterForm.find(me.opts.filterActionButtonBottomSelector);
            me.$sidebarModeLoadionIndicator = $(me.opts.sidebarLoadingIndicatorParentSelector);
            me.$noFilterResultContainer = $(me.opts.noResultContainerSelector);

            me.searchHeadlineProductCount = $(me.opts.searchHeadlineProductCountSelector);
            me.listingUrl = me.$filterForm.attr('data-listing-url');
            me.loadFacets = me.$filterForm.attr('data-load-facets') === 'true';
            me.showInstantFilterResult = me.$filterForm.attr('data-instant-filter-result') === 'true';
            me.isInfiniteScrolling = me.$listing.attr(me.opts.infiniteScrollingAttribute);
            me.isFilterpanelInSidebar = me.$filterForm.attr('data-is-in-sidebar') === 'true';

            me.controllerURL = window.location.href.split('?')[0];
            me.resetLabel = me.$activeFilterCont.attr('data-reset-label');
            me.propertyFieldNames = [];
            me.activeFilterElements = {};
            me.categoryParams = {};
            me.urlParams = '';
            me.bufferTimeout = 0;
            me.closeFilterOffCanvasBtnText = me.$closeFilterOffCanvasBtn.html();
            me.closeFilterOffCanvasBtnTextWithProducts = me.$closeFilterOffCanvasBtn.attr('data-show-products-text');

            me.getPropertyFieldNames();
            me.setCategoryParamsFromTopLocation();
            me.createActiveFiltersFromCategoryParams();
            me.createUrlParams();

            filterCount = Object.keys(me.activeFilterElements).length;

            me.updateFilterTriggerButton(filterCount > 1 ? filterCount - 1 : filterCount);
            me.initStateHandling();
            me.registerEvents();

            me.$loadingIndicatorElement = $(me.opts.loadingIndSelector);
            me.$offCanvasLoadingIndicator = $(me.opts.filterInnerContainerSelector);

            $.subscribe('action/fetchListing', $.proxy(me.onSendListingRequest, me));

            me.disableActiveFilterContainer(true);

            var isFiltered = me.$filterForm.attr('data-is-filtered');
            if (isFiltered > 0 && me.loadFacets) {
                me.getFilterResult(me.urlParams, true, false);
            }
        },

        /**
         * Initializes the state manager for specific device options.
         */
        initStateHandling: function () {
            var me = this,
                enterFn = $.proxy(me.onEnterMobile, me),
                exitFn = $.proxy(me.onExitMobile, me);

            StateManager.registerListener([
                {
                    state: 'xs',
                    enter: enterFn,
                    exit: exitFn
                },
                {
                    state: 's',
                    enter: enterFn,
                    exit: exitFn
                }
            ]);

            $.publish('plugin/swListingActions/onInitStateHandling', [me]);
        },

        /**
         * Called when entering the xs or s viewport.
         * Removes/Clears style attributes that were set in higher viewports.
         */
        onEnterMobile: function () {
            var me = this,
                opts = me.opts;

            me.$filterForm.prop('style', '');
            me.$filterFacetContainer.prop('style', '');
            me.$filterActionButtonBottom.prop('style', '');

            me.disableActiveFilterContainer(false);

            me.$filterCont.removeClass(opts.collapsedCls);

            me.$filterTrigger.removeClass(opts.activeCls);

            $.publish('plugin/swListingActions/onEnterMobile', [me]);
        },

        /**
         * @param {boolean} disabled
         */
        disableActiveFilterContainer: function (disabled) {
            var me = this;

            $.publish('plugin/swListingActions/disableActiveFilter', [this, disabled]);

            if (me.showInstantFilterResult || me.isFilterpanelInSidebar) {
                return;
            }

            if (disabled) {
                me.$activeFilterCont.addClass(me.opts.disabledCls);
            } else if (me.$activeFilterCont.hasClass(me.opts.disabledCls)) {
                me.$activeFilterCont.removeClass(me.opts.disabledCls);
            }
        },

        /**
         * Called when exiting the xs or s viewport.
         * Add the disabled class to the active filter container
         * when it has active filter elements.
         */
        onExitMobile: function () {
            if (StateManager.isCurrentState(['xs', 's'])) {
                return;
            }

            if (Object.keys(this.activeFilterElements).length && !this.isFilterpanelInSidebar) {
                this.disableActiveFilterContainer(true);
            }

            $.publish('plugin/swListingActions/onExitMobile', [this]);
        },

        /**
         * Registers all necessary events.
         */
        registerEvents: function () {
            this._on(this.$filterForm, 'submit', $.proxy(this.onFilterSubmit, this));
            this._on(this.$actionForms, 'submit', $.proxy(this.onActionSubmit, this));
            this._on(this.$actionLinks, 'click', $.proxy(this.onActionLink, this));
            this._on(this.$filterComponents, 'onChange', $.proxy(this.onComponentChange, this));
            this._on(this.$filterTrigger, 'click', $.proxy(this.onFilterTriggerClick, this));

            this._on($body, 'click', $.proxy(this.onBodyClick, this));

            this.$activeFilterCont.on(this.getEventName('click'), '.' + this.opts.activeFilterCls, $.proxy(this.onActiveFilterClick, this));
            this.$listingWrapper.on(this.getEventName('submit'), this.opts.actionFormSelector, $.proxy(this.onActionSubmit, this));
            this.$listingWrapper.on(this.getEventName('click'), this.opts.actionLinkSelector, $.proxy(this.onActionLink, this));

            $.publish('plugin/swListingActions/onRegisterEvents', [this]);
        },

        /**
         * Called by event listener on submitting the filter form.
         * Gets the serialized form data and applies it to the category params.
         *
         * @param {Event} event
         */
        onFilterSubmit: function (event) {
            event.preventDefault();

            var formData = this.$filterForm.serializeArray(),
                categoryParams = this.setCategoryParamsFromData(formData, false);

            this.applyCategoryParams(categoryParams);

            $.publish('plugin/swListingActions/onFilterSubmit', [this, event]);
        },

        /**
         * Called by event listener on submitting an action form.
         * Gets the serialized form data and applies it to the category params.
         *
         * Depending on which action is submitted the hidden input fields
         * of the form filter are set to the new value if instantFilterResult is active.
         *
         * @param {Event} event
         */
        onActionSubmit: function (event) {
            event.preventDefault();

            var $form = $(event.currentTarget),
                formData = $form.serializeArray(),
                categoryParams = this.setCategoryParamsFromData(formData, true);

            if (this.showInstantFilterResult) {
                // first array element is always page number
                this.setPageInput(this.getFormValue(formData, 'p'));

                // second array element is always whether sorting or products per pages
                if (this.isSortAction($form)) {
                    this.setSortInput(this.getFormValue(formData, 'o'));
                } else if (this.isPerPageAction($form)) {
                    this.setPerPageInput(this.getFormValue(formData, 'n'));
                }
            }
            this.applyCategoryParams(categoryParams);

            $.publish('plugin/swListingActions/onActionSubmit', [this, event]);
        },

        /**
         * @param {Object} data
         * @param {string} key
         * @returns {string}
         */
        getFormValue: function (data, key) {
            var value = '';
            $.each(data, function (index, item) {
                if (item.name === key) {
                    value = item.value;
                }
            });
            return value;
        },

        /**
         * @param {Object} $form
         * @return {boolean}
         */
        isSortAction: function ($form) {
            return $form.is(this.opts.sortActionFormSelector);
        },

        /**
         *
         * @param {Object} $form
         * @return {boolean}
         */
        isPerPageAction: function ($form) {
            return $form.is(this.opts.perPageActionFormSelector);
        },

        /**
         * Helper method to set the hidden input field for the current page of the filter form
         *
         * @param {int} value
         */
        setPageInput: function (value) {
            this.$pageInput.val(value);
        },

        /**
         * Helper method to set the hidden input field for the current sorting of the filter form
         *
         * @param {int} value
         */
        setSortInput: function (value) {
            this.$sortInput.val(value);
        },

        /**
         * Helper method to set the hidden input field for products per page of the filter form
         *
         * @param {int} value
         */
        setPerPageInput: function (value) {
            this.$perPageInput.val(value);
        },

        /**
         * Called by event listener on clicking on an action link.
         * Reads the parameter in the href attribute and adds it to the
         * category params.
         *
         * @param {Event} event
         */
        onActionLink: function (event) {
            event.preventDefault();

            var me = this,
                $link = $(event.currentTarget),
                linkParams = $link.attr('href').split('?')[1],
                linkParamsArray = linkParams.split('&'),
                paramValue;

            if (me.showInstantFilterResult) {
                // Update page number in web form
                $.each(linkParamsArray, function(index, param) {
                    paramValue = param.split('=');

                    if (paramValue[0] === 'p') {
                        me.setPageInput(paramValue[1]);
                    }
                });
            }

            this.applyCategoryParams(
                this.setCategoryParamsFromUrlParams(linkParams)
            );

            $.publish('plugin/swListingActions/onActionLink', [this, event]);
        },

        /**
         * Called by event listener on clicking the filter trigger button.
         * Opens and closes the filter form panel.
         *
         * @param {Event} event
         */
        onFilterTriggerClick: function (event) {
            event.preventDefault();

            if (StateManager.isCurrentState(['xs', 's'])) {
                return;
            }

            if (this.$filterCont.hasClass(this.opts.collapsedCls)) {
                this.closeFilterPanel();
            } else {
                this.openFilterPanel();
            }

            $.publish('plugin/swListingActions/onFilterTriggerClick', [this, event]);
        },

        /**
         * Closes all filter panels if the user clicks anywhere else.
         *
         * @param {Event} event
         */
        onBodyClick: function (event) {
            var $target = $(event.target);

            if (!$target.is(this.opts.filterComponentSelector + ', ' + this.opts.filterComponentSelector + ' *')) {
                $.each(this.$filterComponents, function (index, item) {
                    $(item).data('plugin_swFilterComponent').close();
                });
            }

            $.publish('plugin/swListingActions/onBodyClick', [this, event]);
        },

        /**
         * Called by event listener on the change event of the
         * single filter components. Applies the changes of the
         * component values to the category params.
         *
         * @param {Event} event
         */
        onComponentChange: function (event) {
            var urlParams,
                formData,
                categoryParams;

            if (this.showInstantFilterResult) {
                this.setPageInput(1);
            }

            formData = this.$filterForm.serializeArray();
            categoryParams = this.setCategoryParamsFromData(formData);

            urlParams = this.createUrlParams(categoryParams);

            this.createActiveFiltersFromCategoryParams(categoryParams);

            this.enableButtonLoading();
            this.buffer($.proxy(this.getFilterResult, this, urlParams, this.loadFacets, this.showInstantFilterResult), this.opts.bufferTime);

            $.publish('plugin/swListingActions/onComponentChange', [this, event]);
        },

        /**
         * Called by event listener on clicking an active filter label.
         * It removes the clicked filter param form the set of active filters
         * and updates the specific component.
         *
         * @param {Event} event
         */
        onActiveFilterClick: function (event) {
            var me = this,
                $activeFilter = $(event.currentTarget),
                param = $activeFilter.attr('data-filter-param'),
                isMobile = StateManager.isCurrentState(['xs', 's']);

            if (param === 'reset') {
                // Reset all facets
                $.each(me.activeFilterElements, function (key) {
                    me.removeActiveFilter(key);
                    me.resetFilterProperty(key);
                });

                // Reset all options inside the facets
                $.each(me.$filterComponents, function (i, component) {
                    var $component = $(component),
                        componentPlugin = $component.data('plugin_swFilterComponent');

                    $.each(componentPlugin.$inputs, function(i, item) {
                        componentPlugin.disable($(item), false);
                        componentPlugin.disableComponent(false);
                    });

                    $component
                        .removeClass(me.opts.disabledCls)
                        .find('.' + me.opts.disabledCls)
                        .removeClass(me.opts.disabledCls);
                });

                if (!isMobile && !me.$filterCont.hasClass(me.opts.collapsedCls)) {
                    me.applyCategoryParams();
                }
            } else if (!me.$activeFilterCont.hasClass(me.opts.disabledCls) || me.$filterCont.is('.off-canvas.is--open')) {
                me.removeActiveFilter(param);
                me.resetFilterProperty(param);
            }

            $.publish('plugin/swListingActions/onActiveFilterClick', [me, event]);
        },

        /**
         * @returns {Array}
         */
        getPropertyFieldNames: function () {
            var me = this;

            $.each(me.$filterComponents, function (index, item) {
                var $comp = $(item),
                    types = ['value-list', 'value-list-single', 'value-tree', 'media', 'value-tree-single', 'date'],
                    type = $comp.attr('data-filter-type'),
                    fieldName = $comp.attr('data-field-name');

                if (types.indexOf(type) >= 0 && me.propertyFieldNames.indexOf(fieldName) === -1) {
                    me.propertyFieldNames.push(fieldName);
                }
            });

            $.publish('plugin/swListingActions/onGetPropertyFieldNames', [me, me.propertyFieldNames]);

            return me.propertyFieldNames;
        },

        /**
         * Converts given form data to the category parameter object.
         * You can choose to either extend or override the existing object.
         *
         * @param {Object} formData
         * @param {boolean} extend
         * @returns {Object}
         */
        setCategoryParamsFromData: function (formData, extend) {
            var tempParams = {};

            $.each(formData, function (index, item) {
                if (item['value']) {
                    tempParams[item['name']] = item['value'];
                }
            });

            if (extend) {
                return $.extend(this.categoryParams, tempParams);
            }

            this.categoryParams = tempParams;

            $.publish('plugin/swListingActions/onSetCategoryParamsFromData', [this, tempParams]);

            return tempParams;
        },

        /**
         * Converts top location parameters to the category parameter object.
         *
         * @returns {Object}
         */
        setCategoryParamsFromTopLocation: function () {
            var urlParams = window.location.search.substr(1),
                categoryParams = this.setCategoryParamsFromUrlParams(urlParams);

            $.publish('plugin/swListingActions/onSetCategoryParamsFromData', [this, categoryParams]);

            return categoryParams;
        },

        /**
         * Converts url parameters to the category parameter object.
         *
         * @param urlParamString
         * @returns {Object}
         */
        setCategoryParamsFromUrlParams: function (urlParamString) {
            var me = this,
                categoryParams,
                params;

            if (urlParamString.length <= 0) {
                categoryParams = {};

                $.publish('plugin/swListingActions/onSetCategoryParamsFromUrlParams', [me, categoryParams]);

                return categoryParams;
            }

            categoryParams = me.categoryParams;
            params = urlParamString.split('&');

            $.each(params, function (index, item) {
                var param = item.split('=');

                param = $.map(param, function (val) {
                    val = val.replace(/\+/g, '%20');

                    return $.PluginBase.prototype.safeURIDecode(val);
                });

                if (param[1] === 'reset') {
                    delete categoryParams[param[0]];
                } else if (me.propertyFieldNames.indexOf(param[0]) !== -1) {
                    var properties = param[1].split('|');

                    $.each(properties, function (index, property) {
                        categoryParams[me.opts.propertyPrefixChar + param[0] + me.opts.propertyPrefixChar + property] = property;
                    });
                } else {
                    categoryParams[param[0]] = param[1];
                }
            });

            $.publish('plugin/swListingActions/onSetCategoryParamsFromUrlParams', [me, categoryParams]);

            return categoryParams;
        },

        /**
         * Converts the category parameter object to url parameters
         * and applies the url parameters to the current top location.
         *
         * @param {Object} categoryParams
         */
        applyCategoryParams: function (categoryParams) {
            var params = categoryParams || this.categoryParams,
                urlParams = this.createUrlParams(params);

            this.applyUrlParams(urlParams);

            $.publish('plugin/swListingActions/onApplyCategoryParams', [this, categoryParams]);
        },

        /**
         * Converts the category parameter object to url parameters.
         *
         * @param {Object} categoryParams
         * @returns {string}
         */
        createUrlParams: function (categoryParams) {
            var catParams = categoryParams || this.categoryParams,
                params = this.cleanParams(catParams),
                filterList = [];

            $.each(params, function (key, value) {
                filterList.push(encodeURIComponent(key) + '=' + encodeURIComponent(value));
            });

            this.urlParams = '?' + filterList.join('&');

            $.publish('plugin/swListingActions/onCreateUrlParams', [this, this.urlParams]);

            return this.urlParams;
        },

        /**
         * @param {Object} params
         * @returns {Object}
         */
        cleanParams: function (params) {
            var me = this,
                propertyParams = {};

            $.each(params, function (key, value) {
                if (key.substr(0, 2) === me.opts.propertyPrefixChar) {
                    var propertyKey = key.split(me.opts.propertyPrefixChar)[1];

                    if (propertyParams[propertyKey] !== undefined) {
                        propertyParams[propertyKey] += '|' + value;
                    } else {
                        propertyParams[propertyKey] = value;
                    }
                } else {
                    propertyParams[key] = value;
                }
            });

            return propertyParams;
        },

        /**
         * Applies given url params to the top location.
         *
         * @param {string} urlParams
         */
        applyUrlParams: function (urlParams) {
            var params = urlParams || this.urlParams,
                formData,
                categoryParams,
                paramsForFilterResult;

            if (this.showInstantFilterResult) {
                formData = this.$filterForm.serializeArray();

                categoryParams = this.setCategoryParamsFromData(formData);

                paramsForFilterResult = this.createUrlParams(categoryParams);

                this.enableButtonLoading();
                this.buffer($.proxy(this.getFilterResult, this, paramsForFilterResult, false, this.showInstantFilterResult), this.opts.bufferTime);
            } else {
                window.location.href = this.getListingUrl(params, false);
            }

            $.publish('plugin/swListingActions/onApplyUrlParams', [this, urlParams]);
        },

        /**
         * Returns the full url path to the listing
         * including all current url params.
         *
         * @param {string} urlParams
         * @param {boolean} encode
         * @returns {string}
         */
        getListingUrl: function (urlParams, encode) {
            var params = urlParams || this.urlParams,
                url;

            if (encode) {
                url = encodeURI(this.controllerURL + params);
            } else {
                url = this.controllerURL + params;
            }

            $.publish('plugin/swListingActions/onGetListingUrl', [this, url, urlParams, encode]);

            return url;
        },

        /**
         * Buffers a function by the given buffer time.
         *
         * @param {function} func
         * @param {int} bufferTime
         */
        buffer: function (func, bufferTime) {
            if (this.bufferTimeout) {
                clearTimeout(this.bufferTimeout);
            }

            this.bufferTimeout = setTimeout(func, bufferTime);

            $.publish('plugin/swListingActions/onBuffer', [this, this.bufferTimeout, func, bufferTime]);
        },

        /**
         * Resets the current buffer timeout.
         */
        resetBuffer: function () {
            this.bufferTimeout = 0;

            $.publish('plugin/swListingActions/onResetBuffer', [this, this.bufferTimeout]);
        },

        /**
         * Event listener which allows to send listing ajax request to load facets, total count and/or listings
         *
         * @param {object} event
         * @param {object} params
         * @param {boolean} loadFacets
         * @param {boolean} loadProducts
         * @param {function} callback
         */
        onSendListingRequest: function (event, params, loadFacets, loadProducts, callback) {
            var formData = this.$filterForm.serializeArray();

            $.each(formData, function (index, item) {
                if (!params.hasOwnProperty(item.name)) {
                    if (!item.value || (typeof item.value === 'string' && item.value.length <= 0)) {
                        return;
                    }

                    params[item.name] = item.value;
                }
            });

            this.sendListingRequest(params, loadFacets, loadProducts, callback, true);
        },

        /**
         * @param {string|object} params
         * @param {boolean} loadFacets
         * @param {boolean} loadProducts
         * @param {function} callback
         * @param {boolean} appendDefaults
         */
        sendListingRequest: function (params, loadFacets, loadProducts, callback, appendDefaults) {
            if (typeof params === 'object') {
                params = '?' + $.param(params);
            }

            this.resetBuffer();

            $.ajax({
                type: 'get',
                url: this.buildListingUrl(params, loadFacets, loadProducts),
                success: $.proxy(callback, this)
            });
            $.publish('plugin/swListingActions/onGetFilterResult', [this, params]);
        },

        /**
         * Gets the counted result of found products
         * with the current applied category parameters.
         * Updates the filter submit button on success.
         *
         * @param {string} urlParams
         * @param {boolean} loadFacets
         * @param {boolean} loadProducts
         */
        getFilterResult: function (urlParams, loadFacets, loadProducts) {
            var me = this,
                params = urlParams || me.urlParams,
                loadingIndicator = me.$loadingIndicatorElement;

            if (me.$filterCont.is('.off-canvas.is--open')) {
                loadingIndicator = me.$offCanvasLoadingIndicator;
            } else if (me.isFilterpanelInSidebar) {
                loadingIndicator = me.$sidebarModeLoadionIndicator;
            }

            me.resetBuffer();
            me.enableLoading(loadingIndicator, loadProducts, function () {
                // send ajax request to load products and facets
                me.sendListingRequest(params, loadFacets, loadProducts, function (response) {
                    me.disableLoading(loadingIndicator, loadProducts, response, function () {
                        me.updateListing(response);
                        // publish finish event to update filter panels
                        $.publish('plugin/swListingActions/onGetFilterResultFinished', [me, response, params]);
                    });
                });
            });
        },

        /**
         * Enables the loading animation in the listing
         *
         * @param {Object} loadingIndicator
         * @param {boolean} loadProducts
         * @param {function} callback
         */
        enableLoading: function (loadingIndicator, loadProducts, callback) {
            callback = $.isFunction(callback) ? callback : $.noop;

            if (loadProducts) {
                this.$listing.addClass(this.opts.isLoadingCls);

                loadingIndicator.setLoading(
                    true,
                    this.opts.loadingIndConfig
                ).then(
                    $.proxy(callback, this)
                );
            } else {
                this.enableButtonLoading();
                callback.call(this);
            }
        },

        /**
         * Enables the button reload animation
         */
        enableButtonLoading: function () {
            if (!this.showInstantFilterResult) {
                this.$applyFilterBtn.addClass(this.opts.loadingClass);
            }
        },

        /**
         * Disables the loading animation for the listing
         *
         * @param {Object} loadingIndicator
         * @param {boolean} loadProducts
         * @param {Object} response
         * @param {function} callback
         */
        disableLoading: function (loadingIndicator, loadProducts, response, callback) {
            callback = $.isFunction(callback) ? callback : $.noop;

            if (loadProducts) {
                // disable loading indicator
                loadingIndicator.setLoading(false).then(
                    $.proxy(callback, this)
                );
            } else {
                this.$applyFilterBtn.removeClass(this.opts.loadingClass);
                this.updateFilterButton(response.totalCount);
                callback.call(this);
            }
        },

        /**
         * Builds the URL by taking the basic path and adding parameters to it.
         *
         * @param {string} formParams
         * @param {boolean} loadProducts
         * @param {boolean} loadFacets
         * @returns {string}
         */
        buildListingUrl: function (formParams, loadFacets, loadProducts) {
            var url = this.listingUrl + formParams;

            if (loadProducts) {
                url += '&loadProducts=1';
            }
            if (loadFacets) {
                url += '&loadFacets=1';
            }

            return url;
        },

        /**
         * Updates the listing with new products
         *
         * @param {Object} response
         */
        updateListing: function (response) {
            var html,
                listing = this.$listing,
                pages;

            if (!response.hasOwnProperty('listing')) {
                listing.removeClass(this.opts.isLoadingCls);
                return;
            }

            this.updateFilterCloseButton(response.totalCount);
            this.updateSearchHeadline(response.totalCount);
            this.updateNoResultContainer(response.totalCount);

            html = response.listing.trim();

            listing.html(html);

            window.picturefill();

            listing.removeClass(this.opts.isLoadingCls);

            window.history.pushState('data', '', window.location.href.split('?')[0] + this.urlParams);

            $.publish('plugin/swListingActions/updateListing', [this, html]);

            StateManager.updatePlugin(this.opts.addArticleSelector, 'swAddArticle');

            if (this.isInfiniteScrolling) {
                pages = Math.ceil(response.totalCount / this.$perPageInput.val());

                // update infinite scrolling plugin and data attributes for infinite scrolling
                listing.attr('data-pages', pages);
                listing.data('plugin_swInfiniteScrolling').destroy();
                StateManager.addPlugin(this.opts.listingSelector, 'swInfiniteScrolling');
                $.publish('plugin/swListingActions/updateInfiniteScrolling', [this, html, pages]);
            } else {
                this.updatePagination(response);
                this.scrollToTopPagination();
            }
        },

        /**
         * Scrolls to the top paging bar
         */
        scrollToTopPagination: function () {
            var $htmlBodyCt = $('html, body'),
                listingScrollThreshold = this.opts.listingScrollThreshold,
                listingActionPos = this.$el.offset().top + listingScrollThreshold,
                scrollTop = $htmlBodyCt.scrollTop();

            // Browser compatibility
            if (scrollTop === 0) {
                scrollTop = $('body').scrollTop();
            }

            if (scrollTop > listingActionPos) {
                $htmlBodyCt.animate({
                    scrollTop: listingActionPos
                }, this.opts.animationSpeed);
            }
        },

        /**
         * Updates the off canvas filter close button with the amount of products
         *
         * @param {int} totalCount
         */
        updateFilterCloseButton: function (totalCount) {
            var filterCount = Object.keys(this.activeFilterElements).length;

            if (filterCount > 0) {
                this.$closeFilterOffCanvasBtn.html(this.closeFilterOffCanvasBtnTextWithProducts.replace('%s', totalCount) + this.opts.closeFilterOffCanvasBtnIcon);

                $.publish('plugin/swListingActions/updateFilterCloseBtnWithProductsCount', [this, totalCount]);
            } else {
                this.$closeFilterOffCanvasBtn.html(this.closeFilterOffCanvasBtnText);

                $.publish('plugin/swListingActions/updateFilterCloseBtnDefault', [this]);
            }

            this.updateFilterTriggerButton(filterCount > 1 ? filterCount - 1 : filterCount);
        },

        /**
         * Updates the headline of the search page with the new total count
         *
         * @param {int} totalCount
         */
        updateSearchHeadline: function (totalCount) {
            if (this.searchHeadlineProductCount.length > 0) {
                this.searchHeadlineProductCount.html(totalCount);
            }
        },

        /**
         * @param {int} totalCount
         */
        updateNoResultContainer: function (totalCount) {
            if (totalCount > 0) {
                if (!this.$noFilterResultContainer.hasClass('is--hidden')) {
                    this.$noFilterResultContainer.addClass('is--hidden');
                }
                return;
            }
            if (this.$noFilterResultContainer.hasClass('is--hidden')) {
                this.$noFilterResultContainer.removeClass('is--hidden');
            }
        },

        /**
         * Updates the html for the listing pagination in case infinite scrolling is disabled
         *
         * @param {Object} response
         */
        updatePagination: function (response) {
            var html = response.pagination.trim();

            $(this.opts.paginationSelector).replaceWith(html);
            StateManager.updatePlugin(this.opts.paginationBarPerPageSelector, 'swAutoSubmit');

            $.publish('plugin/swListingActions/updatePagination', [this, html]);
        },

        /**
         * Updates the layout of the filter submit button
         * with the new count of found products.
         *
         * @param {int} count
         */
        updateFilterButton: function (count) {
            this.$applyFilterBtn.find(this.opts.filterCountSelector).html(count);

            if (count <= 0) {
                this.$applyFilterBtn.attr('disabled', 'disabled');
            } else {
                this.$applyFilterBtn.prop('disabled', false);
            }

            $.publish('plugin/swListingActions/onUpdateFilterButton', [this, count]);
        },

        /**
         * Updates the layout of the filter trigger button
         * on mobile viewports with the current count of active filters.
         *
         * @param activeFilterCount
         */
        updateFilterTriggerButton: function (activeFilterCount) {
            this.$filterTriggerIcon.html(activeFilterCount || '');

            $.publish('plugin/swListingActions/onUpdateFilterTriggerButton', [this, activeFilterCount]);
        },

        /**
         * Creates the labels for active filters from the category params.
         *
         * @param categoryParams
         */
        createActiveFiltersFromCategoryParams: function (categoryParams) {
            var me = this,
                count = 0,
                params = categoryParams || this.categoryParams;

            $.each(this.activeFilterElements, function (key) {
                if (params[key] === undefined || params[key] === 0) {
                    me.removeActiveFilter(key);
                }
            });

            $.each(params, function (key, value) {
                me.createActiveFilter(key, value);
            });

            $.each(this.activeFilterElements, function () {
                count++;
            });

            if (count > 1) {
                this.createActiveFilterElement('reset', this.resetLabel);
            }

            this.$filterCont.toggleClass(this.opts.hasActiveFilterCls, (count > 0));
            if (this.showInstantFilterResult && count > 0) {
                this.$filterCont.addClass(this.opts.instantFilterActiveCls);
            }

            if (!this.opts.isFilterpanelInSidebar) {
                this.$activeFilterCont.toggleClass(
                    this.opts.collapsedCls,
                    this.$filterCont.hasClass(this.opts.collapsedCls)
                );
            }

            $.publish('plugin/swListingActions/onCreateActiveFiltersFromCategoryParams', [this, categoryParams]);
        },

        /**
         * Creates an active filter label for the given parameter.
         * If the label for the given parameter already
         * exists it will be updated.
         *
         * @param {string} param
         * @param {string} value
         */
        createActiveFilter: function (param, value) {
            var label = this.createActiveFilterLabel(param, value);

            if (label !== undefined && label.length) {
                if (this.activeFilterElements[param] !== undefined) {
                    this.updateActiveFilterElement(param, label);
                } else {
                    this.createActiveFilterElement(param, label);
                }
            }

            $.publish('plugin/swListingActions/onCreateActiveFilter', [this, param, value]);
        },

        /**
         * Creates the DOM element for an active filter label.
         *
         * @param {string} param
         * @param {string} label
         */
        createActiveFilterElement: function (param, label) {
            this.activeFilterElements[param] = $('<span>', {
                'class': this.opts.activeFilterCls,
                'html': this.getLabelIcon() + label,
                'data-filter-param': param
            }).appendTo(this.$activeFilterCont);

            $.publish('plugin/swListingActions/onCreateActiveFilterElement', [this, param, label]);
        },

        /**
         * Updates the layout of an existing filter label element.
         *
         * @param param
         * @param {string} label
         */
        updateActiveFilterElement: function (param, label) {
            this.activeFilterElements[param].html(this.getLabelIcon() + label);

            $.publish('plugin/swListingActions/onUpdateActiveFilterElement', [this, param, label]);
        },

        /**
         * Removes an active filter label from the set and from the DOM.
         *
         * @param param
         */
        removeActiveFilter: function (param) {
            this.activeFilterElements[param].remove();

            delete this.activeFilterElements[param];

            $.publish('plugin/swListingActions/onRemoveActiveFilter', [this, param]);
        },

        /**
         * Resets a filter parameter and updates
         * the component based on the component type.
         *
         * @param {string} param
         */
        resetFilterProperty: function (param) {
            var $input,
                rangeSlider;

            if (param === 'rating') {
                $input = this.$filterForm.find('.filter--rating .is--active input[name="rating"]');
                $input.prop('checked', false).trigger('change');
            } else {
                $input = this.$filterForm.find('[name="' + this.escapeDoubleQuotes(param) + '"]');
                if ($input.is('[data-range-input]')) {
                    rangeSlider = $input.parents('[data-range-slider="true"]').data('plugin_swRangeSlider');
                    rangeSlider.reset($input.attr('data-range-input'));
                } else if ($input.is('[data-datepicker="true"]') || $input.is('[data-date-range-input]')) {
                    $input.trigger('clear');
                } else {
                    $input.prop('checked', false).trigger('change');
                }
            }

            $.publish('plugin/swListingActions/onResetFilterProperty', [this, param]);
        },

        /**
         * Creates the correct label content for an active
         * filter label based on the component type.
         *
         * @param {string} param
         * @param {string} value
         * @returns {string}
         */
        createActiveFilterLabel: function (param, value) {
            var $label,
                labelText = '',
                valueString = value + '';

            if (param === 'rating' && value > 0) {
                labelText = this.createStarLabel(value);
            } else {
                $label = this.$filterForm.find('label[for="' + this.escapeDoubleQuotes(param) + '"]');

                if ($label.is('[data-range-label]')) {
                    labelText = $label.prev('span').html() + $label.html();
                } else if ($label.is('[data-date-range-label]')) {
                    labelText = $label.html() + ' ' + $label.next('[data-date-range-input]').attr('data-display-value');
                } else if ($label.find('img').length) {
                    labelText = $label.find('img').attr('alt');
                } else if ($label.closest(this.opts.filterComponentSelector).is('[data-filter-type="radio"]')) {
                    var activeRadioId = $label.closest(this.opts.filterComponentSelector).find('input:checked').attr('id');
                    labelText = this.$filterForm.find('label[for="' + this.escapeDoubleQuotes(activeRadioId) + '"]').html();
                } else if (value > 0 || valueString.length > 0) {
                    labelText = $label.html();
                }
            }

            $.publish('plugin/swListingActions/onCreateActiveFilterLabel', [this, labelText, param, value]);

            return labelText;
        },

        /**
         * Only escapes a " if it's not already escaped
         *
         * @param {string} str
         * @returns string
         */
        escapeDoubleQuotes: function (str) {
            return str.replace(/\\([\s\S])|(")/g, '\\$1$2');
        },

        /**
         * Creates the label content for the special rating component.
         *
         * @param {int} stars
         * @returns {string}
         */
        createStarLabel: function (stars) {
            var label = '',
                i = 0;

            for (i; i < 5; i++) {
                if (i < stars) {
                    label += '<i class="icon--star"></i>';
                } else {
                    label += '<i class="icon--star-empty"></i>';
                }
            }

            $.publish('plugin/swListingActions/onCreateStarLabel', [this, label, stars]);

            return label;
        },

        /**
         * Returns the html string of the delete icon
         * for an active filter label.
         *
         * @returns {string}
         */
        getLabelIcon: function () {
            var icon = '<span class="' + this.opts.activeFilterIconCls + '"></span>';

            $.publish('plugin/swListingActions/onCreateStarLabel', [this, icon]);

            return icon;
        },

        /**
         * Opens the filter form panel based on the current state.
         */
        openFilterPanel: function () {
            if (!this.$filterCont.hasClass(this.opts.hasActiveFilterCls)) {
                this.$activeFilterCont.slideDown(this.opts.animationSpeed);
            }

            this.$filterFacetContainer.slideDown(this.opts.animationSpeed);
            this.$filterActionButtonBottom.slideDown(this.opts.animationSpeed);

            this.disableActiveFilterContainer(false);
            this.$filterCont.addClass(this.opts.collapsedCls);
            this.$filterTrigger.addClass(this.opts.activeCls);

            $.publish('plugin/swListingActions/onOpenFilterPanel', [this]);
        },

        /**
         * Closes the filter form panel based on the current state.
         */
        closeFilterPanel: function () {
            if (!this.$filterCont.hasClass(this.opts.hasActiveFilterCls)) {
                this.$activeFilterCont.slideUp(this.opts.animationSpeed);
            }

            this.$filterFacetContainer.slideUp(this.opts.animationSpeed);
            this.$filterActionButtonBottom.slideUp(this.opts.animationSpeed);

            this.disableActiveFilterContainer(true);
            this.$filterCont.removeClass(this.opts.collapsedCls);
            this.$filterTrigger.removeClass(this.opts.activeCls);

            $.publish('plugin/swListingActions/onCloseFilterPanel', [this]);
        },

        /**
         * Destroys the plugin.
         */
        destroy: function () {
            this.$el.off(this.getEventName('click'), '.' + this.opts.activeFilterCls);
            this.$listingWrapper.off(this.getEventName('submit'), this.opts.actionFormSelector);
            this.$listingWrapper.off(this.getEventName('click'), this.opts.actionLinkSelector);

            this._destroy();
        }
    });
})(jQuery, window, StateManager, undefined);
