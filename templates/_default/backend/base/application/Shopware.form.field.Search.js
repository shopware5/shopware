
Ext.define('Shopware.form.field.Search', {

    extend: 'Ext.form.field.ComboBox',
    xtype: 'widget.shopware-form-field-search',

    queryMode: 'remote',

    valueField: 'id',

    displayField: 'name',

    minChars: 2,

    displayConfig: { },


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
         * It contains properties for the single elements within this component
         * for example: "addButton" => displays an add button which allows the user
         * to add new row items.
         *
         * To override this property you can use the grid.displayConfig object.
         *
         * @example
         * Ext.define('Shopware.apps.Product.view.list.Grid', {
         *     extend: 'Shopware.grid.Association',
         *     displayConfig: {
         *         toolbar: false,
         *         ...
         *     }
         * });
         */
        displayConfig: {
            associationKey: undefined,
            associationModel: undefined,
            searchController: undefined,
            searchUrl: '{url controller="base" action="searchAssociation"}'
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

    setSearchController: function(controller) {
        var me = this,
            searchUrl = me.statics().displayConfig.searchUrl;

        me._opts['searchUrl'] = searchUrl.replace(
            '/backend/base/', '/backend/' + controller.toLowerCase() + '/'
        );
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

        if (!me.store) {
            me.store = me.createSearchComboStore(
                me.getConfig('associationKey'),
                me.getConfig('searchUrl'),
                me.getConfig('associationModel')
            );
            me.store.load();
        }
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
                return '{literal}<a class="search-item">' +
                    '<h4>{name}</h4><span><br />{[Ext.util.Format.ellipsis(values.description, 150)]}</span>' +
                    '</a>{/literal}';
            }
        }
    },


    /**
     * Creates the Ext.data.Store for the search combo box.
     * The combo box store requires the association definition of the
     * displayed data. The association key will be added as extra parameter.
     *
     * @param associationKey { Object }
     * @param searchUrl { String }
     * @returns { Ext.data.Store }
     */
    createSearchComboStore: function (associationKey, searchUrl, model) {
        var me = this;

        return Ext.create('Ext.data.Store', {
            model: model,
            proxy: {
                type: 'ajax',
                url: searchUrl,
                reader: { type: 'json', root: 'data', totalProperty: 'total' },
                extraParams: { association: associationKey }
            }
        });
    }


});