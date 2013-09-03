
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

    title: 'Filters',

    /**
     * Override required!
     * This function is used to override the { @link #displayConfig } object of the statics() object.
     *
     * @returns { Object }
     */
    configure: function() {
        return { };
    },

    statics: {
        displayConfig: {

            controller: undefined,
            searchUrl: '{url controller="base" action="searchAssociation"}',

            model: undefined,

            displayFields: [],

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
        return Ext.create('Ext.container.Container', {
            html: 'Aktivieren Sie der verschiedenen Felder über die davor angezeigte Checkbox. Aktivierte Felder werden mit einer UND Bedingung verknüpft.',
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
            text: 'Filter result',
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
            text: 'Reset filters',
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