
//{block name="backend/application/model/container"}

Ext.define('Shopware.model.Container', {

    extend: 'Ext.container.Container',
    autoScroll: true,

    /**
     * Internal property which contains all created association components.
     * This array is used to reload the association data in the component when
     * the data is reloaded.
     * @type { Array }
     */
    associationComponents: [],

    /**
     * List of classes to mix into this class.
     * @type { Object }
     */
    mixins: {
        helper: 'Shopware.model.Helper'
    },

    /**
     * Get the reference to the class from which this object was instantiated. Note that unlike self, this.statics()
     * is scope-independent and it always returns the class from which it was called, regardless of what
     * this points to during run-time.
     *
     * The statics object contains the shopware default configuration for
     * this component. The different shopware configurations are stored
     * within the displayConfig object.
     *
     * @type { object }
     */
    statics: {
        /**
         * The statics displayConfig contains the default shopware configuration for
         * this component.
         * To set the shopware configuration, you can set the displayConfig directly
         * as property of the component:
         *
         * @example
         *      Ext.define('Shopware.apps.Product.view.detail.Product', {
         *          extend: 'Shopware.model.Container',
         *          displayConfig: {
         *              searchController: 'product',
         *              fields: {
         *                  name: { fieldLabel: 'Product name' }
         *              },
         *              ...
         *          }
         *      });
         */
        displayConfig: {

            /**
             * The searchController property is used for manyToOne associations.
             * This controller will be requested to load the associated data.
             * In the default case, this controller is the backend php application controller
             * name like 'Article', 'Banner', etc.
             *
             * @type { String }
             * @required
             */
            searchController: undefined,

            /**
             * The searchUrl property is used to request the associated data
             * of the base model.
             * Shopware requests the association data as default from the
             * application php backend controller.
             * The searchUrl requires an configured { @link #searchController }.
             *
             * @type { String }
             */
            searchUrl: '{url controller="base" action="searchAssociation"}',

            /**
             * The fields property can contains custom form field configurations.
             * It allows to customize the different form fields without overriding the
             * createFormField function.
             * The field configuration will be applied at least to the form field, so it
             * allows to override the each field configuration like listeners, validation or something
             * else.
             *
             * @example
             *  fields: {
             *      name: { fieldLabel: 'OwnLabel' },
             *  }
             */
            fields: { },

            /**
             * The association property can contains the association which has to be displayed
             * within this container.
             * To add an association to this component, the association key has to be added to this array.
             *
             * @example:
             *  Model of this container:
             *  Ext.define('Shopware.apps.Product.model.Product', {
             *      extend: 'Shopware.data.Model',
             *      fields: [ 'id', ... ]
             *      associations: [
             *          {
             *              relation: 'OneToOne',
             *              type: 'hasMany',
             *              model: 'Shopware.apps.Product.model.Attribute',
             *              name: 'getAttribute',
             *              associationKey: 'attribute'
             *          },
             *      ]
             *  });
             *
             *  To display the 'Shopware.apps.Product.model.Attribute' model within
             *  this component, add the associationKey property 'attribute' to the
             *  { @link #associations } property:
             *
             *  Ext.define('Shopware.apps.Product.view.detail.Product', {
             *      extend: 'Shopware.model.Container',
             *      displayConfig: {
             *          associations: [ 'attribute' ]
             *      }
             *  });
             *  
             *  The attribute association component will be created in the { @link #createAssociationComponent }
             *
             * @type { Array }
             */
            associations: [  ],


            /**
             * The fieldAlias property is used to prefix the form fields with the
             * associationKey of the associated model.
             * To display different models in the same Ext.form.Panel, the association
             * model fields uses the associationKey as field name prefix.
             * Additionally the association model field names are surrounded with
             * square brackets:
             *
             * @example
             *  Base model of this container:
             *  Ext.define('Shopware.apps.Product.model.Product', {
             *      extend: 'Shopware.data.Model',
             *      fields: [ 'id', 'name', ... ]
             *      associations: [
             *          {
             *              relation: 'OneToOne',
             *              type: 'hasMany',
             *              model: 'Shopware.apps.Product.model.Attribute',
             *              name: 'getAttribute',
             *              associationKey: 'attribute'
             *          },
             *      ]
             *  });
             *
             *  The fields of this model are created normally:
             *      -   field.name = name;
             *
             *  To display the 'Shopware.apps.Product.model.Attribute' model within
             *  this component, add the associationKey property 'attribute' to the
             *  { @link #associations } property:
             *
             *  Ext.define('Shopware.apps.Product.view.detail.Product', {
             *      extend: 'Shopware.model.Container',
             *      displayConfig: {
             *          associations: [ 'attribute' ]
             *      }
             *  });
             *
             *  The fields of this model are prefixed with the association key
             *  and surrounded with square brackets:
             *      -   field.name = attribute['name']
             *
             *
             * @optional - For the base model
             * @required - For associated models
             * @type { String }
             */
            fieldAlias: undefined
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

            if (config.searchController) {
                config.searchUrl = config.searchUrl.replace(
                    '/backend/base/', '/backend/' + config.searchController.toLowerCase() + '/'
                );
            }
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

    /**
     * Initialisation of this component.
     * Creates all required elements which has to be displayed within this component.
     */
    initComponent: function() {
        var me = this;

        me.fieldAssociations = me.getAssociations(me.record.$className, [
            { relation: 'ManyToOne' }
        ]);

        me.associationComponents = [];
        me.items = me.createItems();
        me.title = me.getModelName(me.record.$className);
        me.callParent(arguments);
    },


    /**
     * Creates all components for this container.
     * Shopware creates as default only a field set with the model
     * fields.
     * To display additional association in this component
     * you can add the associationKey to the { @link #associations } property within the displayConfig.
     *
     * Each additional association component is created over the { @link #createAssociationComponent } function.
     *
     * @returns { Array }
     */
    createItems: function() {
        var me = this, items = [], item,
            associations;

        items.push(
            me.createModelFieldSet(
                me.record.$className,
                me.getConfig('fieldAlias')
            )
        );

        //get all record associations, which defined in the display config.
        associations = me.getAssociations(
            me.record.$className,
            { associationKey: me.getConfig('associations') }
        );

        //the associations will be displayed within this component.
        Ext.each(associations, function(association) {
            item = me.createAssociationComponent(
                me.getComponentTypeOfAssociation(association),
                Ext.create(association.associatedName),
                me.getAssociationStore(me.record, association)
            );
            //check if the component creation was canceled, or throws an exception
            if(item) {
                items.push(item);
                me.associationComponents[association.associationKey] = item;
            }
        });

        return items;
    },

    /**
     * Helper function which creates a single association components.
     *
     * @param type { String } - Possible values: field, detail, listing, related
     * @param model { Shopware.data.Model } - Contains the model instance of the association
     * @param store { Ext.data.Store } - Ext.data.Store of the association
     * @returns { Object }
     */
    createAssociationComponent: function(type, model, store) {
        var componentType = model.getConfig(type);

        return Ext.create(componentType, {
            record: model,
            store: store,
            flex: 1
        });
    },


    /**
     * Creates an Ext.form.FieldSet for the passed model.
     * The fields are created in the { @link #createModelFields } function.
     * The fields array will be split in two arrays to display them in two
     * column layout containers.
     * If the model is an associated model of the main record, the function requires the alias parameter
     * to prefix the field name with the association key and surround the original field name with square
     * brackets.
     *
     * @param modelName { String } - Full class name of the model. Used to create a model instance.
     * @param alias { String } - Additional alias for the field names (example: 'attribute' => 'attribute[name]')
     *
     * @return Ext.form.FieldSet
     */
    createModelFieldSet: function (modelName, alias) {
        var me = this, model = Ext.create(modelName), items = [], container, fields;

        //convert all model fields to form fields.
        fields = me.createModelFields(model, alias);

        //create a column container to display the columns in a two column layout
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
     * This functions use different helper function like { @link Shopware.model.Helper:applyBooleanFieldConfig }
     * to set different shopware default configurations for a form field.
     * The id property of the model won't be displayed.
     *
     * @param model { Ext.data.Model } - Instance of the model which fields should be displayed
     * @param field { Ext.data.Field } - The model field which will be used for the form field creation.
     * @param alias { string } - Field alias for associations. See { @link #fieldAlias }
     *
     * @return { Ext.form.field.Field }
     */
    createModelField: function (model, field, alias) {
        var me = this, formField = {},
            config, customConfig, name,
            fieldModel, fieldComponent, xtype;

        //don't display the id property
        if (model.idProperty === field.name) {
            return null;
        }

        //add default configuration for a form field.
        formField.xtype = 'displayfield';
        formField.anchor = '100%';
        formField.margin = '0 3 7 0';
        formField.labelWidth = 130;
        formField.name = field.name;

        //if an alias was passed, the form field name will be surround with square bracket
        if (alias !== undefined && Ext.isString(alias) && alias.length > 0) {
            formField.name = alias + '[' + field.name + ']';
        }

        //convert the model field name to a human readable word
        formField.fieldLabel = me.camelCaseToWord(field.name);

        //check if the field is configured as association field.
        var fieldAssociation = me.getFieldAssociation(field.name);

        if (fieldAssociation === undefined) {
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

        //association fields are used for manyToOne association like article > supplier.
        //this fields will be displayed as default with an { @link Shopware.form.field.Search }
        } else {
            //first create a model instance to get the merged display config of the model.
            fieldModel = Ext.create(fieldAssociation.associatedName);

            //after the display config merged, we can get the field component.
            fieldComponent = fieldModel.getConfig('field');

            //the field component are defined with the full class name, but we need the xtype for this component
            xtype = Ext.ClassManager.getAliasesByName(fieldComponent);
            formField.xtype = xtype[0].replace('widget.', '');

            //if no custom field configured, we have to configure the display config of the component
            if (fieldComponent === 'Shopware.form.field.Search') {
                formField.store = me.createAssociationSearchStore(
                    fieldAssociation.associatedName,
                    fieldAssociation.associationKey,
                    me.getConfig('searchUrl')
                ).load();
            }
        }

        //get the component field configuration. This configuration contains custom field configuration.
        config = me.getConfig('fields');
        if (config) {
            //check if the current field is defined in the fields configuration. Otherwise use an empty object which will be applied.
            customConfig = config[field.name] || {};
            formField = Ext.apply(formField, customConfig);
        }
        
        return formField;
    },

    /**
     * Helper function which checks if an many to one association is configured for
     * the passed field.
     *
     * @param fieldName { String }
     * @returns { undefined|Ext.data.association.Association }
     */
    getFieldAssociation: function(fieldName) {
        var me = this, fieldAssociation = undefined;

        Ext.each(me.fieldAssociations, function(association) {
            if (association.field === fieldName) {
                fieldAssociation = association;
                return false;
            }
        });
        return fieldAssociation;
    },

    /**
     * Interface to reload the component data.
     * Used from the { @link Shopware.detail.Controller }.
     *
     * @param store { Ext.data.Store }
     * @param record { Shopware.data.Model }
     */
    reloadData: function(store, record) {
        var me = this, association, component, associationStore;

        Object.keys(me.associationComponents).forEach(function(key) {
            component = me.associationComponents[key];

            if (component && typeof component.reloadData === 'function') {
                association = me.getAssociations(record.$className, [ { associationKey: [ key ] } ]);
                associationStore = me.getAssociationStore(record, association[0]);

                component.reloadData(
                    associationStore,
                    record
                );
            }
        });
    }
});
//{/block}
