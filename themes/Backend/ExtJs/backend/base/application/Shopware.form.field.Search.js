
//{namespace name=backend/application/main}

//{block name="backend/application/Shopware.form.field.Search"}
Ext.define('Shopware.form.field.Search', {

    /**
     * The parent class that this class extends.
     * @type { String }
     */
    extend: 'Ext.form.field.ComboBox',

    /**
     * This property provides a shorter alternative to creating objects than using a full class name. Using xtype is the most common way to define component instances, especially in a container.
     * @type { String }
     */
    alias: 'widget.shopware-form-field-search',

    /**
     * In queryMode: 'remote', the ComboBox loads its Store dynamically based upon user interaction.
     * This is typically used for auto complete type inputs, and after the user finishes typing, the Store is loaded.
     * A parameter containing the typed string is sent in the load request. The default parameter name for the input string is query, but this can be configured using the queryParam config.
     */
    queryMode: 'remote',

    /**
     * The underlying data value name to bind to this ComboBox.
     * @type { String }
     */
    valueField: 'id',

    /**
     * The underlying data field name to bind to this ComboBox.
     * @type { String }
     */
    displayField: 'name',

    /**
     * The minimum number of characters the user must type before auto complete and typeAhead activate.
     * @type { Number }
     */
    minChars: 2,

    /**
     * @required
     *
     * The combo box store have to be set from outside.
     * Normally the store is created over the { @link Shopware.model.Helper:createAssociationSearchStore } function.
     * The { @link Shopware.form.field.Search } component is used from the { @link Shopware.grid.Association } and
     * the { @link Shopware.model.Container }
     */
    store: undefined,

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
         * To set the shopware configuration, you can use the configure function and set an object as return value
         *
         * @example
         *      Ext.define('Shopware.apps.Product.view.detail.SearchField', {
         *          extend: 'Shopware.form.field.Search',
         *          configure: function() {
         *              return {
         *                  ...
         *              }
         *          }
         *      });
         */
        displayConfig: {
            /**
             * Activates or deactivate the listing template function which displays
             * additional information for each record.
             */
            listTemplate: false
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
     * Override required!
     * This function is used to override the { @link #displayConfig } object of the statics() object.
     *
     * @returns { Object }
     */
    configure: function() {
        return { };
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

        me._opts = me.statics().getDisplayConfig(opts, this);
        me.callParent(arguments);
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

        if (me.getConfig('listTemplate')) {
            me.listConfig = me.createSearchComboListConfig();
        }

        me.on('change', function(field, newValue) {
            if (!newValue) {
                me.setValue('');
            }
        });
        me.callParent(arguments);
    },


    /**
     * Creates a listing configuration for the search combo box.
     * The search combo box is used for many to many association components.
     * The association parameter is only passed to allow the override component
     * identify which association search combo will be created.
     *
     * @returns object
     */
    createSearchComboListConfig: function () {
        return {
            getInnerTpl: [
                '{literal}' +
                    '<a class="search-item">' +
                        '<h4>{name}</h4>' +
                        '<tpl if="values.description">' +
                            '<br /><span>{[Ext.util.Format.ellipsis(values.description, 150)]}</span>' +
                        '</tpl>' +
                    '</a>' +
                '{/literal}'
            ].join()
        }
    }
});
//{/block}
