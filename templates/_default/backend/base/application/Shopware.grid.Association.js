//{block name="backend/component/grid/association"}

Ext.define('Shopware.grid.Association', {
    extend: 'Shopware.grid.Panel',
    alias: 'widget.shopware-grid-association',

    /**
     * Configuration for the { @link Shopware.grid.Panel }.
     * Disables all none required features.
     */
    displayConfig: {
        pagingbar: false,
        actionColumn: true,
        editColumn: false
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
            association: undefined,
            searchController: undefined,
            searchUrl: '{url action="searchAssociation"}',
            searchCombo: true

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
        var me = this, args;

        me._opts = me.statics().getDisplayConfig(opts, this.displayConfig);
        args = arguments;
        args[0].displayConfig = me._opts;

        me.callParent(args);
    },

    /**
     * Overrides the { @link Shopware.grid.Panel:createToolbar } function.
     * This function adds a white background color for the toolbar.
     * @override
     * @returns { Ext.toolbar.Toolbar }
     */
    createToolbar: function() {
        var me = this, toolbar;

        toolbar = me.callParent(arguments);
        toolbar.style = 'background:#fff';
        return toolbar;
    },

    /**
     * Creates the toolbar items for the grid toolbar.
     *
     * If the configuration { @link #toolbar } is set to
     * false this function isn't called.
     *
     * The function is used from { @link #Shopware.grid.Panel:createToolbar } function and calls the internal
     * functions { @link #createSearchCombo }.
     *
     * To add an own component to the toolbar, you can use the following source code
     * as example:
     * @example
     *  createToolbarItems: function() {
     *     var me = this, items;
     *
     *     items = me.callParent(arguments);
     *
     *     items = Ext.Array.insert(
     *         items, 2, [
     *            { xtype: 'button', text: 'MyButton', handler: function() { ... } }
     *        ]
     *     );
     *
     *     return items;
     *  },
     *
     * @override
     * @returns { Array }
     */
    createToolbarItems: function() {
        var me = this, items = [], combo;

        if (me.getConfig('searchCombo')) {
            combo = me.createSearchCombo(
                me.createSearchComboStore(
                    me.getConfig('association'),
                    me.getConfig('searchUrl')
                )
            );
            items.push(combo);
        }

        return items;
    },


    /**
     * Creates the search combo box item of the toolbar.
     * The combo box allows the user to search new items for the grid panel.
     *
     * @param store { Ext.data.Store }
     * @returns { Ext.form.field.ComboBox }
     */
    createSearchCombo: function (store) {
        var me = this;

        return Ext.create('Ext.form.field.ComboBox', {
            name: 'associationSearchField',
            queryMode: 'remote',
            store: store,
            valueField: 'id',
            pageSize: 20,
            flex: 1,
            displayField: 'name',
            minChars: 2,
            fieldLabel: 'Search for',
            margin: 5,
            listConfig: me.createSearchComboListConfig(),
            listeners: {
                select: function (combo, records) {
                    me.onSelectSearchItem(combo, records);
                }
            }
        });
    },


    /**
     * Creates the Ext.data.Store for the search combo box.
     * The combo box store requires the association definition of the
     * displayed data. The association key will be added as extra parameter.
     *
     * @param association { Object }
     * @param searchUrl { String }
     * @returns { Ext.data.Store }
     */
    createSearchComboStore: function (association, searchUrl) {
        return Ext.create('Ext.data.Store', {
            model: association.associatedName,
            proxy: {
                type: 'ajax',
                url: searchUrl,
                reader: { type: 'json', root: 'data', totalProperty: 'total' },
                extraParams: { association: association.associationKey }
            }
        });
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
     * Event listener function of the combo box.
     * Fired when the user selects a combo box item.
     * In case that the selected item isn't already in the grid store,
     * the item will be added.
     *
     * @param combo
     * @param records
     */
    onSelectSearchItem: function (combo, records) {
        var me = this, inStore;

        Ext.each(records, function (record) {
            inStore = me.getStore().getById(record.get('id'));
            if (inStore === null) {
                me.getStore().add(record);
                combo.setValue('');
            }
        });
    }
});

//{/block}