
//{namespace name=backend/application/main}

Ext.define('Shopware.listing.FilterPanel', {
    extend: 'Ext.form.Panel',

    alias: 'widget.listing-filter-panel',


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
     * Override required!
     * This function is used to override the { @link #displayConfig } object of the statics() object.
     *
     * @returns { Object }
     */
    configure: function() {
        return { };
    },

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
             *
             *    createProductNameField: function() { ... }
             */
            fields: { },

            /**
             * Contains the text value for the { @link #infoContainer }.
             * This container is displayed at the top of the filter panel.
             * @type { String }
             */
            infoText: '{s name="filter_panel/info_text"}Activate the filter fields over the checkbox which displayed for each field. Activated fields will be joined with an AND condition.{/s}',

            /**
             * Contains the text for the { @link #filterButton }.
             * @type { String }
             */
            filterButtonText: '{s name="filter_panel/filter_button_text"}Filter result{/s}',

            /**
             * Contains the text for the { @link #resetButton }.
             * @type { String }
             */
            resetButtonText: '{s name="filter_panel/reset_button_text"}Reset filters{/s}'
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

        me.gridPanel = me.listingWindow.gridPanel;

        me.items = me.createItems();

        me.dockedItems = me.createDockedItems();

        me.callParent(arguments);
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
            html: me.getConfig('infoText'),
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
        var me = this, items = [], field, config,
            record = Ext.create(me.getConfig('model'));

        //first we have to get all field association for the foreign key fields.
        me.fieldAssociations = me.getAssociations(me.getConfig('model'), [
            { relation: 'ManyToOne' }
        ]);

        var configFields = me.getConfig('fields');

        //iterate all model fields ({ @link #fields } property is checked in the first line of the foreach loop).
        Ext.each(record.fields.items, function(modelField) {
            //check if the fields property is set and if the current model field is configured in this property.
            if (Object.keys(configFields).length > 0 && !(configFields.hasOwnProperty(modelField.name))) {
                //if the field isn't configured, the column won't be displayed in filter panel
                return true;
            }

            //get configuration of the current model field.
            config = configFields[modelField.name];
            if (Ext.isString(config)) config = { fieldLabel: config };

            field = me.createModelField(record, modelField, undefined, config);

            //field wasn't created? Continue with next iteration
            if (!field) return true;

            //create filter field container to add a checkbox for each field.
            var container = Ext.create('Shopware.filter.Field', { field: field });
            field.container = container;

            items.push(container);
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
            dock: 'bottom'
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
            text: me.getConfig('filterButtonText'),
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
            text: me.getConfig('resetButtonText'),
            handler: function() {
                me.getForm().reset();
                me.gridPanel.getStore().clearFilter(true);
                me.gridPanel.getStore().load();
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
        var me = this,
            model = Ext.create(me.getConfig('model')),
            values = me.getForm().getValues();

        me.gridPanel.getStore().clearFilter(true);

        Object.keys(values).forEach(function (key) {
            if (me.getFieldByName(model.fields.items, key) === undefined) {
                return true;
            }

            me.gridPanel.getStore().filters.add(key,
                Ext.create('Ext.util.Filter', {
                    property: key,
                    value: values[key]
                })
            );
        });

        me.gridPanel.getStore().load();
    }


});