
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

    region: 'west',
    width: 300,
    cls: 'detail-view',
    collapsible: true,
    layout: 'anchor',

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

            controller: undefined,
            searchUrl: '{url controller="base" action="searchAssociation"}',

            model: undefined,

            displayFields: [],

            fields: { },

            infoText: '{s name="filter_panel/info_text"}Aktivieren Sie der verschiedenen Felder über die davor angezeigte Checkbox. Aktivierte Felder werden mit einer UND Bedingung verknüpft.{/s}',
            filterButtonText: '{s name="filter_panel/filter_button_text"}Filter result{/s}',
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

    createItems: function() {
        var me = this, items = [];

        items.push(me.createInfoText());

        items.push(me.createFilterFields());

        return items;
    },

    createInfoText: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            html: me.getConfig('infoText'),
            style: 'color: #6c818f; font-size: 11px; line-height: 14px;',
            margin: '0 0 10'
        });
    },

    createFilterFields: function() {
        var me = this, items = [], field, config,
            record = Ext.create(me.getConfig('model'));

        me.fieldAssociations = me.getAssociations(me.getConfig('model'), [
            { relation: 'ManyToOne' }
        ]);

        var configFields = me.getConfig('fields');

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


    createDockedItems: function() {
        var me = this;

        return [
            me.createToolbar()
        ];
    },

    createToolbar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            items: [ me.createFilterButton(), me.createResetButton() ],
            dock: 'bottom'
        });
    },

    createFilterButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            cls: 'secondary small',
            iconCls: 'sprite-funnel',
            text: me.getConfig('filterButtonText'),
            handler: function() {
                me.filterGridStore();
            }
        });
    },

    createResetButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            cls: 'secondary small',
            iconCls: 'sprite-funnel--minus',
            text: me.getConfig('resetButtonText'),
            handler: function() {
                me.getForm().reset();
                me.gridPanel.getStore().clearFilter(true);
                me.gridPanel.getStore().load();
            }
        });
    },

    filterGridStore: function() {
        var me = this,
            model = Ext.create(me.getConfig('model')),
            values = me.getForm().getValues();

        me.gridPanel.getStore().clearFilter(true);

        Object.keys(values).forEach(function (key) {
            if (!me.hasModelField(me.getConfig('model'), key)) {
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
    },


    hasModelField: function(modelName, fieldName) {
        var model = Ext.create(modelName),
            result = false;

        Ext.each(model.fields.items, function(field) {
             if (field.name == fieldName) {
                 result = true;
                 return false;
             }
        });

        return result;
    }


});