
//{namespace name=backend/application/main}
//{block name="backend/application/Shopware.listing.FilterPanel"}

Ext.define('Shopware.listing.FilterPanel', {
    extend: 'Ext.form.Panel',

    alias: 'widget.shopware-listing-filter-panel',

    /**
     * List of classes to mix into this class.
     * @type { Object }
     */
    mixins: {
        helper: 'Shopware.model.Helper',
        container: 'Shopware.model.Container'
    },

    /**
     * Contains the default region.
     * In default case, shopware use a border layout in
     * the { @link Shopware.window.Listing }.
     * @type { String }
     */
    region: 'west',

    width: 300,

    /**
     * Shopware css class for the filter panel.
     */
    cls: 'detail-view',

    collapsible: true,

    /**
     * Defines the component layout and how the
     * elements of this component will be assigned.
     * @type { String }
     */
    layout: 'anchor',

    /**
     * Contains the title of the filter panel.
     * #@type { String }
     */
    title: '{s name="filter_panel/title"}Filters{/s}',

    /**
     * Reference to the { @link Shopware.window.Listing } which contains
     * the filter panel extension definition.
     * This reference is set automatically.
     *
     * IMPORTANT: In the default case shopware expects that the
     * listing window has an own property named "gridPanel" which
     * contains the instance of the { @link Shopware.grid.Panel }.
     * This grid panel is used to filter the store of the listing.
     */
    listingWindow: undefined,

    /**
     * Instance of the { @link Shopware.grid.Panel }.
     * The grid panel property is set with the { @link #listingWindow:gridPanel }
     * property.
     * The shopware grid panel instance of this property is used to assign the
     * filter values.
     *
     * @type { Shopware.grid.Panel }
     */
    gridPanel: undefined,

    /**
     * Contains the info text which displayed at the top of the filter panel.
     * Can be configured over the { @link #configure }.
     *
     * @type { String }
     */
    infoText: undefined,

    /**
     * Instance of the panel toolbar.
     * The toolbar contains the { @link #filterButton } and { @link #resetButton }
     * which allows the user to filter the listing result or to reset all filters
     * which assigned to the listing store.
     *
     * @type { Ext.toolbar.Toolbar }
     */
    toolbar: undefined,

    /**
     * Instance of the filter button.
     * This button filters the listing store with the configured filter fields.
     *
     * @type { Ext.button.Button }
     */
    filterButton: undefined,

    /**
     * Instance of the filter reset button.
     * This button resets all already assigned filters from the listing store
     * and reloads the listing data.
     *
     * @type { Ext.button.Button }
     */
    resetButton: undefined,

    /**
     * Contains all field associations which configured in the model.
     * Fields associations are defined with the `relation: ManyToOne` flag.
     * Additionally to the relation flag, the field associations contains the
     * corresponding field name in the association property "field: shopId".
     * This associations will be stored in this property to display
     * a { @link Shopware.form.field.Search } for human readable values.
     *
     * @type { Array }
     */
    fieldAssociations: [ ],

    /**
     * Contains the text value for the { @link #infoContainer }.
     * This container is displayed at the top of the filter panel.
     * @type { String }
     */
    infoTextSnippet: '{s name="filter_panel/info_text"}Activate the filter by clicking the regarding checkbox. These filters will be linked with an AND condition.{/s}',

    /**
     * Contains the text for the { @link #filterButton }.
     * @type { String }
     */
    filterButtonText: '{s name="filter_panel/filter_button_text"}Filter result{/s}',

    /**
     * Contains the text for the { @link #resetButton }.
     * @type { String }
     */
    resetButtonText: '{s name="filter_panel/reset_button_text"}Reset filters{/s}',

    filterFieldStyle: 'background: #fff',

    /**
     * Get the reference to the class from which this object was instantiated.
     * Note that unlike self, this.statics() is scope-independent and it always
     * returns the class from which it was called, regardless of what this points to during run-time
     *
     * @type { Object }
     */
    statics: {

        /**
         * The displayConfig contains the default shopware configuration for
         * this component.
         * To set the shopware configuration, you can use the configure function and set an object as return value
         *
         * @example
         *      Ext.define('Shopware.apps.Product.view.listing.extension.Filter', {
         *          extend: 'Shopware.listing.FilterPanel',
         *          configure: function() {
         *              return {
         *                  model: 'Shopware.apps.Product.model.Product',
         *                  ...
         *              }
         *          }
         *      });
         */
        displayConfig: {
            /**
             * Suffix alias for the different component events.
             * This alias must the same alias of the { @link Shopware.listing.FilterPanel:eventAlias }  component.
             * If you don't know the alias you can output the alias of the grid panel as follow:
             * console.log("alias", me.eventAlias);
             *
             * If you haven't configured a custom event alias, the { @link Shopware.listing.FilterPanel } creates
             * the event alias over the configured model.
             * @example
             * If you passed a store with an model named: 'Shopware.apps.Product.model.Product'
             * the { @link Shopware.grid.Panel } use "product" as event alias.
             *
             * @type { string }
             */
            eventAlias: undefined,

            /**
             * @required
             *
             * Name of the php controller which loads the store data.
             *
             * @example
             * PHP Controller = Shopware_Controllers_Backend_Article
             * value of this property => 'article'
             *
             * @type { String }
             */
            controller: undefined,

            /**
             * Url for the search request. The "controller=base" path will be replaced with the
             * { @link #controller } property.
             *
             * @type { String }
             */
            searchUrl: '{url controller="base" action="searchAssociation"}',

            /**
             * Contains the full Ext JS model name of the listing records which will be displayed within the panel.
             * @type { String }
             */
            model: undefined,

            /**
             * Contains the definition which model fields will be displayed within the filter panel.
             * If this object contains no field definition, shopware creates for prototyping a filter
             * field for each model field.
             * If the object contains some field definitions, only the configured fields will be displayed.
             *
             * @example
             * You have an model with the following field definition:
             * Ext.define('Shopware.apps.Product.model.Product', {
             *    fields: [
             *        { name: 'name', type: 'string'  },
             *        { name: 'active', type: 'boolean'  },
             *        { name: 'description', type: 'string'  },
             *    ]
             * });
             *
             * If you want to display only the name field, set only the name field into the { @link #fields } property:
             * Ext.define('Shopware.apps.Product.view.list.extension.Filter', {
             *    configure: function() {
             *        return {
             *            fields: {
             *                name: {
             *                    fieldLabel: 'Product name'
             *                }
             *            }
             *        }
             *    }
             * });
             *
             * The name object within the fields object, can contains additional form field configurations
             * which will be assigned to the created filter field.
             *
             * The name property of the fields object, can even contains an function reference.
             * This function will be called to create the form field:
             *
             *    configure: function() {
             *        return {
             *            fields: {
             *                name: this.createProductNameField
             *            }
             *        }
             *    }
             *
             *    createProductNameField: function() { ... }
             */
            fields: { }
        },

        /**
         * Static function to merge the different configuration values
         * which passed in the class constructor.
         * @param { Object } userOpts
         * @param { Object } definition
         * @returns Object
         */
        getDisplayConfig: function (userOpts, definition) {
            var config = { };

            if (userOpts && typeof userOpts.configure == 'function') {
                config = Ext.apply({ }, config, userOpts.configure());
            }
            if (definition && typeof definition.configure === 'function') {
                config = Ext.apply({ }, config, definition.configure());
            }
            config = Ext.apply({ }, config, this.displayConfig);

            if (config.controller) {
                config.searchUrl = config.searchUrl.replace(
                    '/backend/base/', '/backend/' + config.controller + '/'
                );
            }
            return config;
        },

        /**
         * Static function which sets the property value of
         * the passed property and value in the display configuration.
         *
         * @param prop
         * @param val
         * @returns boolean
         */
        setDisplayConfig: function (prop, val) {
            var me = this;

            if (!me.displayConfig.hasOwnProperty(prop)) {
                return false;
            }
            me.displayConfig[prop] = val;
            return true;
        }
    },

    /**
     * Override required!
     * This function is used to override the { @link #displayConfig } object of the statics() object.
     *
     * @returns { Object }
     */
    configure: function() {
        return { };
    },

    /**
     * Class constructor which merges the different configurations.
     * @param opts
     */
    constructor: function (opts) {
        var me = this;

        me._opts = me.statics().getDisplayConfig(opts, this);
        me.callParent(arguments);
    },

    /**
     * Helper function to get config access.
     *
     * @param prop string
     * @returns mixed
     * @constructor
     */
    getConfig: function (prop) {
        var me = this;
        return me._opts[prop];
    },

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first, with each initComponent method up the hierarchy
     * to Ext.Component being called thereafter. This makes it easy to implement and, if needed, override the constructor
     * logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class'
     * initComponent method is also called.
     * All config options passed to the constructor are applied to this before initComponent is called, so you
     * can simply access them with this.someOption.
     */
    initComponent: function() {
        var me = this;

        me.checkRequirements();

        me.eventAlias = me.getConfig('eventAlias');
        if (!me.eventAlias) me.eventAlias = me.getEventAlias(me.getConfig('model'));

        me.registerEvents();

        if (!me.gridPanel) {
            me.gridPanel = me.listingWindow.gridPanel;
        }

        me.items = me.createItems();

        me.dockedItems = me.createDockedItems();

        me.callParent(arguments);
    },

    getStore: function() {
        return this.gridPanel.getStore();
    },

    /**
     * Helper function which checks all component requirements.
     */
    checkRequirements: function() {
        var me = this;

        if (!me.getConfig('controller')) {
            me.throwException(me.$className + ": Component requires the `controller` property in the configure() function");
        }
        if (!me.getConfig('model')) {
            me.throwException(me.$className + ": Component requires the `model` property in the configure() function");
        }
        if (me.alias.length <= 0) {
            me.throwException(me.$className + ": Component requires a configured Ext JS widget alias.");
        }
        if (me.alias.length === 1 && me.alias[0] === 'widget.shopware-listing-filter-panel') {
            me.throwException(me.$className + ": Component requires a configured Ext JS widget alias.");
        }
    },

    /**
     * Registers all custom component events.
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Fired before a single filter value will be
             * assigned as Ext.ux.grid.filter.Filter to the grid panel store.
             *
             * @param { Shopware.listing.FilterPanel } filterPanel - Instance of this component
             * @param { Shopware.grid.Panel } gridPanel - Instance of the listing window grid panel
             * @param { String } key - Name of the filter field
             * @param { Mixed } value - Value of the filter field.
             */
            this.eventAlias + '-before-apply-field-filter',

            /**
             * Fired before the grid panel store will be reloaded.
             * This event can be used to add additionally filters.
             *
             * @param { Shopware.listing.FilterPanel } filterPanel - Instance of this component
             * @param { Shopware.grid.Panel } gridPanel - Instance of the listing window grid panel
             */
            this.eventAlias + '-before-grid-load-filter',

            /**
             * Fired before the grid panel filters will be reset.
             * Return false to prevent the filter process.
             *
             * @param { Shopware.listing.FilterPanel } filterPanel - Instance of this component
             * @param { Shopware.grid.Panel } gridPanel - Instance of the listing window grid panel
             */
            this.eventAlias + '-before-filter-grid',

            /**
             *
             * @param { Shopware.listing.FilterPanel } filterPanel - Instance of this component
             * @param { Shopware.grid.Panel } gridPanel - Instance of the listing window grid panel
             * @param { Array } records - Contains the loaded records
             * @param { Ext.data.Operation } operation - The data operation of the store.load() event.
             */
            this.eventAlias + '-after-filter-grid'
        );
    },

    /**
     * Creates all sub components for this component.
     * Shopware adds as default first the info text container
     * and then the filter fields container.
     * @returns { Array }
     */
    createItems: function() {
        var me = this, items = [];

        items.push(me.createInfoText());

        items.push(me.createFilterFields());

        return items;
    },

    /**
     * Creates the { @link #infoText } container which is displayed
     * at the top of the filter panel.
     *
     * @returns { Ext.container.Container }
     */
    createInfoText: function() {
        var me = this;

        me.infoText = Ext.create('Ext.container.Container', {
            html: me.infoTextSnippet,
            style: 'color: #6c818f; font-size: 11px; line-height: 14px;',
            margin: '0 0 10'
        });
        return me.infoText;
    },

    /**
     * Creates all filter fields for the panel.
     * If the { @link #fields } property contains no fields definition,
     * shopware creates a filter field for each model field.
     * The { @link #model } is a required configuration of this component.
     * If the { @link #fields } property contains different field definitions,
     * only the configured fields will be displayed.
     *
     * @returns { Ext.container.Container }
     */
    createFilterFields: function() {
        var me = this, fields = { }, items = [], field, config,
            record = Ext.create(me.getConfig('model'));

        // First we have to get all field association for the foreign key fields.
        me.fieldAssociations = me.getAssociations(me.getConfig('model'), [
            { relation: 'ManyToOne' }
        ]);

        var configFields = me.getConfig('fields');

        // Iterate all model fields ({ @link #fields } property is checked in the first line of the foreach loop).
        Ext.each(record.fields.items, function(modelField) {
            // Check if the fields property is set and if the current model field is configured in this property.
            if (Object.keys(configFields).length > 0 && !(configFields.hasOwnProperty(modelField.name))) {
                //if the field isn't configured, the column won't be displayed in filter panel
                return true;
            }

            // Get configuration of the current model field.
            config = configFields[modelField.name];
            if (Ext.isString(config)) config = { fieldLabel: config };

            field = me.createModelField(record, modelField, undefined, config);

            // Field wasn't created? Continue with next iteration
            if (!field) return true;

            // Create filter field container to add a checkbox for each field.
            var container = Ext.create('Shopware.filter.Field', {
                field: field,
                style: me.filterFieldStyle,
                subApp: me.subApp
            });
            field.container = container;

            fields[modelField.name] = container;
        });

        var sorting = record.fields.keys;
        if (configFields && Object.keys(configFields).length > 0) {
            sorting = Object.keys(configFields);
        }

        Ext.each(sorting, function(key) {
            if (fields[key]) {
                items.push(fields[key]);
            }
        });

        return Ext.create('Ext.container.Container', {
            items: items,
            layout: 'anchor',
            anchor: '100%',
            defaults: {
                anchor: '100%'
            }
        });
    },

    /**
     * Creates the docked items for this component.
     * Shopware creates as default a toolbar with a { @link #filterButton }
     * and a { @link #resetButton }
     *
     * @returns { Array }
     */
    createDockedItems: function() {
        var me = this;

        return [
            me.createToolbar()
        ];
    },

    /**
     * Creates the toolbar with the { @link #filterButton } and the
     * { @link #resetButton }.
     *
     * @returns { Ext.toolbar.Toolbar }
     */
    createToolbar: function() {
        var me = this;

        me.toolbar =  Ext.create('Ext.toolbar.Toolbar', {
            items: [ me.createFilterButton(), me.createResetButton() ],
            dock: 'bottom',
            margin: '1 0'
        });
        return me.toolbar;
    },

    /**
     * Creates the { @link #filterButton } which filters
     * the listing store with the configured and activated fields.
     * The button click will be passed to the { @link #filterGridStore } function.
     *
     * @returns { Ext.button.Button }
     */
    createFilterButton: function() {
        var me = this;

        me.filterButton = Ext.create('Ext.button.Button', {
            cls: 'secondary small',
            iconCls: 'sprite-funnel',
            text: me.filterButtonText,
            handler: function() {
                me.filterGridStore();
            }
        });
        return me.filterButton;
    },

    /**
     * Creates { @link #resetButton } which removes all activated
     * filter values from the listing store and resets also the
     * form filter fields.
     *
     * @returns { Ext.button.Button }
     */
    createResetButton: function() {
        var me = this;

        me.resetButton = Ext.create('Ext.button.Button', {
            cls: 'secondary small',
            iconCls: 'sprite-funnel--minus',
            text: me.resetButtonText,
            handler: function() {
                me.getForm().reset();
                me.getStore().clearFilter(true);
                me.getStore().load();
            }
        });
        return me.resetButton;
    },

    /**
     * This function filters the listing store if the user
     * clicks on the { @link #filterButton }.
     * The function reads all form field values which activated
     * over the prepended checkbox of each field.
     * The form values will be converted into an { @link Ext.util.Filter }
     */
    filterGridStore: function() {
        var me = this;

        if (!me.fireEvent(me.eventAlias + '-before-filter-grid', me, me.gridPanel)) {
            return false;
        }
        me.getStore().clearFilter(true);

        me.createFilters();

        me.fireEvent(me.eventAlias + '-before-grid-load-filter', me, me.gridPanel);

        me.getStore().load({
            callback: function(records, operation) {
                me.fireEvent(me.eventAlias + '-after-filter-grid', me, me.gridPanel, records, operation);
            }
        });
    },

    /**
     * Wrapper function which can be overwritten to
     * add additional filter values.
     */
    createFilters: function() {
        var me = this, expression, field,
            model = Ext.create(me.getConfig('model')),
            values = me.getForm().getValues();

        Object.keys(values).forEach(function (key) {
            if (me.getFieldByName(model.fields.items, key) === undefined) {
                return true;
            }

            if (!me.fireEvent(me.eventAlias + '-before-apply-field-filter', me, me.gridPanel, key, values[key])) {
                return true;
            }

            expression = '=';
            field = me.getForm().findField(key);
            if (field && field.expression) {
                expression = field.expression;
            }

            me.getStore().filters.add(key,
                Ext.create('Ext.util.Filter', {
                    property: key,
                    expression: expression,
                    value: values[key]
                })
            );
        });
    }
});
//{/block}
