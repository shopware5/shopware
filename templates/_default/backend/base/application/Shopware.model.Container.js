
Ext.define('Shopware.model.Container', {

    extend: 'Ext.container.Container',

    autoScroll: true,
    padding: 20,

    /**
     * List of classes to mix into this class.
     * @type { Object }
     */
    mixins: {
        helper: 'Shopware.model.Helper'
    },

    /**
     * The statics object contains the shopware default configuration for
     * this component.
     *
     * @type { object }
     */
    statics: {
        /**
         * The statics displayConfig is the shopware default configuration for
         * this component.
         * It contains properties for the single elements within this component.
         */
        displayConfig: {

            /**
             * @example
             *  fields: {
             *      name: { fieldLabel: 'OwnLabel' },
             *      attribute_attr1: { fieldLabel: 'OwnLabel' }
             *  }
             */
            fields: { }
        },

        /**
         * Static function to merge the different configuration values
         * which passed in the class constructor.
         *
         * @param { Object } userOpts
         * @param { Object } displayConfig
         * @returns { Object }
         */
        getDisplayConfig: function (userOpts, displayConfig) {
            var config;

            config = Ext.apply({ }, userOpts.displayConfig, displayConfig);
            config = Ext.apply({ }, config, this.displayConfig);

            return config;
        },

        /**
         * Static function which sets the property value of
         * the passed property and value in the display configuration.
         *
         * @param { String } prop - Property which should be in the { @link #displayConfig }
         * @param { String } val - The value of the property (optional)
         * @returns { Boolean }
         */
        setDisplayConfig: function (prop, val) {
            var me = this;

            val = val || '';

            if (!me.displayConfig.hasOwnProperty(prop)) {
                return false;
            }
            me.displayConfig[prop] = val;
            return true;
        }
    },

    /**
     * Helper function to get config access.
     *
     * @param prop string
     * @returns mixed
     */
    getConfig: function (prop) {
        var me = this;
        return me._opts[prop];
    },

    /**
     * Class constructor which merges the different configurations.
     *
     * @param { Object } opts - Passed configuration
     */
    constructor: function (opts) {
        var me = this;

        me._opts = me.statics().getDisplayConfig(opts, this.displayConfig);
        me.callParent(arguments);
    },

    initComponent: function() {
        var me = this;
        me.items = me.createItems();
        me.title = me.getModelName(me.record.$className);
        me.callParent(arguments);
    },

    createItems: function() {
        var me = this, items = [], modelName = me.record.$className;

        items.push(me.createModelFieldSet(modelName, ''));

        return items;
    },


    /**
     * Erstellt für den übergebene Modelnamen ein Ext.form.FieldSet.
     * Die Elemente dieses Fieldsets werden über createModelFields erstellt.
     * Formfelder die zu einer Association gehören müssen einen Alias im Feldnamen
     * besitzen damit die Daten richtig geladen werden können.
     * Beispiel:
     *  -  Hauptmodel: 'Shopware.apps.Product.model.Product'
     *  -  Eine Association:
     *       {
     *           model: 'Shopware.apps.Product.model.Attribute',
     *           associationKey: 'attribute',
     *           ...
     *       }
     *  -> Wenn die Felder des Attribute models nun im selben Formular
     *     angezeigt werden sollen benötigen diese dafür folgenden alias:
     *     'attribute[name]', 'attribute[street]'
     *
     *
     * @param modelName Ext.data.Model
     * @param alias Additional alias for the field names (example: 'attribute' => 'attibute[name]')
     *
     * @return Ext.form.FieldSet
     */
    createModelFieldSet: function (modelName, alias) {
        var me = this, model = Ext.create(modelName), items = [], container, fields;

        fields = me.createModelFields(model, alias);

        container = Ext.create('Ext.container.Container', {
            columnWidth: 0.5,
            padding: '0 20 0 0',
            layout: 'anchor',
            items: fields.slice(0, Math.round(fields.length / 2))
        });
        items.push(container);

        container = Ext.create('Ext.container.Container', {
            columnWidth: 0.5,
            layout: 'anchor',
            items: fields.slice(Math.round(fields.length / 2))
        });
        items.push(container);

        return Ext.create('Ext.form.FieldSet', {
            flex: 1,
            padding: '10 20',
            layout: 'column',
            items: items,
            title: me.getModelName(modelName)
        });
    },



    /**
     * Creates all Ext.form.Fields for the passed model.
     * The alias can be used to prefix the field names.
     * For example: 'attribute[name]'.
     *
     * @return Array
     */
    createModelFields: function (model, alias) {
        var me = this, fields = [], field;

        Ext.each(model.fields.items, function (item) {
            field = me.createModelField(model, item, alias);
            if (field) fields.push(field);
        });

        return fields;
    },

    /**
     * This function creates the form field element
     * for a single model field.
     * This functions use different helper function like
     * 'applyIntegerFieldConfig' to set different shopware
     * default configurations for a form field.
     * The id property of the model won't be displayed.
     *
     * @param model Ext.data.Model
     * @param field Ext.data.Field
     * @param alias string
     * @return Ext.form.field.Field
     */
    createModelField: function (model, field, alias) {
        var me = this, formField = {}, config, customConfig, name;

        if (model.idProperty === field.name) {
            return null;
        }

        formField.xtype = 'displayfield';
        formField.anchor = '100%';
        formField.margin = '0 3 7 0';
        formField.labelWidth = 130;
        formField.name = field.name;

        alias += '';
        if (alias !== undefined && Ext.isString(alias) && alias.length > 0) {
            formField.name = alias + '[' + field.name + ']';
        }
        formField.fieldLabel = me.camelCaseToWord(field.name);

        switch (field.type.type) {
            case 'int':
                formField = me.applyIntegerFieldConfig(formField);
                break;
            case 'string':
                formField = me.applyStringFieldConfig(formField);
                break;
            case 'bool':
                formField = me.applyBooleanFieldConfig(formField);
                break;
            case 'date':
                formField = me.applyDateFieldConfig(formField);
                break;
            case 'float':
                formField = me.applyFloatFieldConfig(formField);
                break;
        }

        name = field.name;
        if (alias) name = alias + '_' + field.name;

        config = me.getConfig('fields');
        if (config) {
            customConfig = config[name] || {};
            formField = Ext.apply(formField, customConfig);
        }

        return formField;
    }
});