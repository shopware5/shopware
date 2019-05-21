
//{namespace name=backend/application/main}

//{block name="backend/application/Shopware.grid.Association"}

Ext.define('Shopware.grid.Association', {
    /**
     * The parent class that this class extends.
     * @type { String }
     */
    extend: 'Shopware.grid.Panel',

    /**
     * Override required!
     * @type { String }
     */
    alias: 'widget.shopware-grid-association',

    /**
     * Instance of the search store.
     * The search store is used for the { @link #searchComboBox }
     * and will be created dynamically.
     * The search store proxy use the { @link #searchUrl } property
     * as read url.
     * @type { Ext.data.Store }
     */
    searchStore: undefined,

    /**
     * Instance of the search combo box.
     * The search combo box is used to add new items
     * to the association grid.
     * The search combo box is created dynamically
     * and uses the { @link #searchStore } as combo box store.
     * @type { Shopware.form.field.Search }
     */
    searchComboBox: undefined,

    /**
     * Field label for the { @link #searchComboBox } , which is displayed in the
     * grid toolbar.
     * @type { String }
     */
    searchComboLabel: '{s name="association_grid/combo_field_label"}Search for{/s}',

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
         *      Ext.define('Shopware.apps.Product.view.detail.Category', {
         *          extend: 'Shopware.grid.Association',
         *          configure: function() {
         *              return {
         *                  associationKey: 'categories',
         *                  controller: 'Product',
         *                  ...
         *              }
         *          }
         *      });
         */
        displayConfig: {

            /**
             * @required
             *
             * Controller name of the php controller.
             * Used for the search request and will be set in the searchUrl.
             *
             * @type { String }
             */
            controller: undefined,

            /**
             * Alphanumeric key of the association.
             * This key is required for the search request.
             * The association key will be set in the default case automatically from the parent component like the
             * { @link Shopware.window.Detail } .
             *
             * @example:
             * You have a base model like this:
             *      Ext.define('Shopware.apps.Product.model.Product', {
             *          extend: 'Shopware.data.Model',
             *          configure: function() {
             *              return {
             *                  controller: 'Product',
             *                  detail: 'Shopware.apps.Product.view.detail.Product'
             *              }
             *          },
             *          fields: [
             *              { name: 'id', type: 'int', useNull: true },
             *              { name: 'name', type: 'string' },
             *              { name: 'description', type: 'string', useNull: true },
             *              ...
             *          ],
             *
             *          associations: [
             *              {
             *                  relation: 'ManyToMany',
             *                  type: 'hasMany',
             *                  model: 'Shopware.apps.Product.model.Category',
             *                  name: 'getCategory',
             *                  associationKey: 'categories'
             *              }
             *          ]
             *      });
             *
             * The Shopware.apps.Product.model.Category association should be displayed
             * in a Shopware.grid.Association, because it is a ManyToMany relation.
             *
             * The definition of the Shopware.grid.Association looks now like this:
             *      Ext.define('Shopware.apps.Product.view.detail.Category', {
             *          extend: 'Shopware.grid.Association',
             *          alias: 'widget.product-view-detail-category',
             *          title: 'Category',
             *
             *          configure: function() {
             *              return {
             *                  controller: 'Product',
             *                  associationKey: 'categories'
             *              }
             *          }
             *      });
             *
             */
            associationKey: undefined,

            /**
             * Url for the search request. The "controller=base" path will be replaced with the
             * { @link #controller } property.
             *
             * @type { String }
             */
            searchUrl: '{url controller="base" action="searchAssociation"}',

            /**
             * Configuration for the search combo box.
             * If the property is set to false, the { @link #createAssociationSearchStore } won't be called.
             *
             * @type { boolean }
             */
            searchCombo: true,

            /**
             * Configuration of the { @link Shopware.grid.Panel }.
             * The association grid don't need a paging bar so we can hide this by using the grid panel config.
             *
             * @@type { boolean }
             */
            pagingbar: false,

            /**
             * Configuration of the { @link Shopware.grid.Panel }.
             * The associated data of a many to many association has no detail view.
             *
             * @type { boolean }
             */
            editColumn: false
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
        var me = this, args;

        me._opts = me.statics().getDisplayConfig(opts, this);
        args = arguments;
        opts.configure = function() {
            return me._opts;
        };
        me.callParent(args);
    },

    /**
     * Helper function which checks all component requirements.
     */
    checkRequirements: function() {
        var me = this;

        if (!(me.store instanceof Ext.data.Store)) {
            me.throwException(me.$className + ": Component requires a configured store, which has to been passed in the class constructor.");
        }
        if (me.alias.length <= 0) {
            me.throwException(me.$className + ": Component requires a configured Ext JS widget alias.");
        }
        if (me.alias.length === 1 && me.alias[0] === 'widget.shopware-grid-association') {
            me.throwException(me.$className + ": Component requires a configured Ext JS widget alias.");
        }
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
        var me = this, items = [];

        if (me.getConfig('searchCombo')) {
            me.searchStore = me.createAssociationSearchStore(
                me.getStore().model,
                me.getConfig('associationKey'),
                me.getConfig('searchUrl')
            );
            me.searchComboBox = me.createSearchCombo(me.searchStore);
            items.push(me.searchComboBox);
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
            subApp: me.subApp,
            fieldLabel: me.searchComboLabel,
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
