//{block name="backend/application/grid/association"}

Ext.define('Shopware.grid.Association', {
    extend: 'Shopware.grid.Panel',
    alias: 'widget.shopware-grid-association',


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
         *      Ext.define('Shopware.apps.Product.view.detail.Category', {
         *          extend: 'Shopware.grid.Association',
         *          displayConfig: {
         *              associationKey: 'categories',
         *              searchController: 'product',
         *              ...
         *          }
         *      });
         */
        displayConfig: {
            associationKey: undefined,
            searchController: undefined,
            searchUrl: '{url controller="base" action="searchAssociation"}',


            searchCombo: true,
            pagingbar: false,
            actionColumn: true,
            editColumn: false
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
            me.searchStore = me.createAssociationSearchStore(
                me.getStore().model,
                me.getConfig('associationKey'),
                me.getConfig('searchUrl')
            );
            combo = me.createSearchCombo(me.searchStore);
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

        return Ext.create('Shopware.form.field.Search', {
            name: 'associationSearchField',
            store: store,
            pageSize: 20,
            flex: 1,
            fieldLabel: 'Search for',
            margin: 5,
            listeners: {
                select: function (combo, records) {
                    me.onSelectSearchItem(combo, records);
                }
            }
        });
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