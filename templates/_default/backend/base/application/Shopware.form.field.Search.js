
//{block name="backend/application/form/field/search"}

Ext.define('Shopware.form.field.Search', {

    extend: 'Ext.form.field.ComboBox',

    xtype: 'widget.shopware-form-field-search',

    queryMode: 'remote',

    valueField: 'id',

    displayField: 'name',

    minChars: 2,

    displayConfig: { },

    /**
     * The combo box store have to be set from outside.
     * Normally the store is created over the { @link Shopware.model.Helper:createAssociationSearchStore } function.
     * The { @link Shopware.form.field.Search } component is used from the { @link Shopware.grid.Association } and
     * the { @link Shopware.model.Container }
     * @required
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
         * To set the shopware configuration, you can set the displayConfig directly
         * as property of the component:
         *
         * @example
         *      Ext.define('Shopware.apps.Product.view.detail.SearchField', {
         *          extend: 'Shopware.form.field.Search',
         *          displayConfig: {
         *              ...
         *          }
         *      });
         */
        displayConfig: {

        },

        /**
         * Static function to merge the different configuration values
         * which passed in the class constructor.
         * @param userOpts Object
         * @param displayConfig Object
         * @returns Object
         */
        getDisplayConfig: function (userOpts, displayConfig) {
            var config = { };

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
     * Initials the whole component and the sub elements.
     */
    initComponent: function() {
        var me = this;

        me.listConfig = me.createSearchComboListConfig();

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
            getInnerTpl: function () {
                return '{literal}' +
                    '<a class="search-item">' +
                        '<h4>{name}</h4>' +
                        '<tpl if="values.description">' +
                            '<br /><span>{[Ext.util.Format.ellipsis(values.description, 150)]}</span>' +
                        '</tpl>' +
                    '</a>' +
                '{/literal}';
            }
        }
    }



});
//{/block}
