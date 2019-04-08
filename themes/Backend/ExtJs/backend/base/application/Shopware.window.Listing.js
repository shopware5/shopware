
//{namespace name=backend/application/main}
//{block name="backend/application/Shopware.window.Listing"}

Ext.define('Shopware.window.Listing', {
    extend: 'Enlight.app.Window',

    /**
     * List of classes to mix into this class.
     * @type { Object }
     */
    mixins: {
        helper: 'Shopware.model.Helper'
    },

    layout: 'border',

    width: 990,

    height: '50%',

    alias: 'widget.shopware-window-listing',


    /**
     * Contains the generated event alias.
     * If the { @link #configure } function returns an eventAlias
     * property, this property contains the configured alias.
     * Otherwise shopware creates an event alias over the model name.
     *
     * @type { String }
     */
    eventAlias: undefined,

    /**
     * Contains the created { @link Shopware.store.Listing } instance.
     * This store is configured over the { @link #configure } function.
     * The configure function returns the full class name of the listing store
     * which will be created in { @link #createListingStore } function.
     *
     * @example
     *  Ext.define('...', {
     *      extend: 'Shopware.window.Listing',
     *      configure: function() {
     *          return {
     *              listingStore: 'Shopware.apps.Product.store.List',
     *              gridPanel: 'Shopware.apps.Product.view.list.Product'
     *          }
     *      }
     *  });
     *
     * @type { Shopware.store.Listing }
     */
    listingStore: undefined,

    /**
     * Contains the created { @link Shopware.grid.Panel } instance.
     * This grid panel is configured over the { @link #configure } function.
     * The configure function returns the full class name of the grid panel
     * which will be created in the { @link #createGridPanel } function.
     *
     * @example
     *  Ext.define('...', {
     *      extend: 'Shopware.window.Listing',
     *      configure: function() {
     *          return {
     *              listingStore: 'Shopware.apps.Product.store.List',
     *              gridPanel: 'Shopware.apps.Product.view.list.Product'
     *          }
     *      }
     *  });
     *
     * @type { Shopware.grid.Panel }
     */
    gridPanel: undefined,

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
         *      Ext.define('Shopware.apps.Product.view.list.Window', {
         *          extend: 'Shopware.window.Listing',
         *          configure: function() {
         *              return {
         *                  listingGrid: 'Shopware.apps.Product.view.list.Product',
         *                  listingStore: 'Shopware.apps.Product.store.Product'
         *                  ...
         *              }
         *          }
         *      });
         */
        displayConfig: {
            /**
             * @required
             *
             * Class name of the grid which will be displayed in the center
             * region of this window.
             *
             * @type { String }
             */
            listingGrid: undefined,

            /**
             * @required
             *
             * Class name of the grid store. This store will be set in the
             * listingGrid instance as grid store.
             * The store will be loaded over this component so don't set the
             * autoLoad parameter of the store to true.
             *
             * @type { String }
             */
            listingStore: undefined,

            /**
             * Alias for the fired events to prevent a duplicate event name
             * in different modules.
             *
             * @type { String }
             */
            eventAlias: undefined,

            /**
             * Array of listing window extensions.
             * This array will be assigned to the internal { @link #items } property.
             * Each extension becomes the listing window as reference under { @link #listingWindow }.
             *
             * @example
             *  First you have to define the extension view:
             *
             *  Ext.define('Shopware.apps.Product.view.list.extension.Filter', {
             *      extend: 'Shopware.listing.FilterPanel',
             *      alias:  'widget.product-listing-filter-panel',
             *      configure: function() {
             *          return {
             *              controller: 'Product',
             *              model: 'Shopware.apps.Product.model.Product',
             *          };
             *      }
             *  });
             *
             *  Now you can add the extension to the extensions array:
             *  Ext.define('...', {
             *      extend: 'Shopware.window.Listing',
             *      configure: function() {
             *          return {
             *              listingStore: 'Shopware.apps.Product.store.List',
             *              gridPanel: 'Shopware.apps.Product.view.list.Product',
             *              extensions: [
             *                  { xtype: 'product-listing-filter-panel' }
             *              ]
             *          }
             *      }
             *  });
             *
             * Each extension will be pushed as defined into the items array
             * in the { @link #createItems } function.
             * Additionally each extension becomes a reference to the listing window (this component)
             * into the property "extension.listingWindow = me"
             *
             * @type { Array }
             */
            extensions: [ ]
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
     * Override required!
     * This function is used to override the { @link #displayConfig } object of the statics() object.
     *
     * @returns { Object }
     */
    configure: function() {
        return { };
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
    initComponent: function () {
        var me = this;

        me.checkRequirements();

        me.listingStore = me.createListingStore();
        me.eventAlias = me.getConfig('eventAlias');
        if (!me.eventAlias) me.eventAlias = me.getEventAlias(me.listingStore.model.$className);

        me.registerEvents();

        me.fireEvent(me.eventAlias + '-before-init-component', me);

        me.items = me.createItems();

        me.fireEvent(me.eventAlias + '-after-init-component', me);

        me.callParent(arguments);
    },

    /**
     * Helper function which checks all component requirements.
     */
    checkRequirements: function() {
        var me = this;

        if (me.alias.length <= 0) {
            me.throwException(me.$className + ": Component requires a configured Ext JS widget alias.");
        }
        if (me.alias.length === 1 && me.alias[0] === 'widget.shopware-window-listing') {
            me.throwException(me.$className + ": Component requires a configured Ext JS widget alias.");
        }
        if (!me.getConfig('listingGrid')) {
            me.throwException(me.$className + ": Component requires the configured `listingGrid` property in the configure() function.");
        }
        if (!me.getConfig('listingStore')) {
            me.throwException(me.$className + ": Component requires the configured `listingStore` property in the configure() function.");
        }
    },

    /**
     * Registers all required custom events of this component.
     */
    registerEvents: function() {
        var me = this;

        me.addEvents(

            /**
             * Event fired before the window element will be create in the { @link #createItems } function.
             * The listing store is already created at this point and can be access over "window.listingStore".
             *
             * @param { Shopware.window.Listing } window - Instance of this component.
             */
            me.eventAlias + '-before-init-component',

            /**
             * Event fired before the shopware default items of the listing window will be created.
             * This event can be used to insert some elements at the beginning of the items array.
             *
             * @param { Shopware.window.Listing } window - Instance of this component.
             * @param { Array } items - Contains the create window elements.
             */
            me.eventAlias + '-before-create-items',

            /**
             * Event fired after the shopware default items of the listing window created.
             *
             * @param { Shopware.window.Listing } window - Instance of this component.
             * @param { Array } items - Contains the create window elements.
             */
            me.eventAlias + '-after-create-items',

            /**
             * Event fired after the default shopware elements for this component
             * created and all defined extensions loaded.
             *
             * @param { Shopware.window.Listing } window - Instance of this component.
             * @param { Array } items - Contains the created window elements and all defined extensions
             */
            me.eventAlias + '-after-extensions-loaded',

            /**
             * Event fired after the { @link Shopware.grid.Panel } created.
             * This event can be used to modify the grid view or to reposition the grid within the window.
             *
             * @param { Shopware.window.Listing } window - Instance of this component.
             * @param { Shopware.grid.Panel } grid - Instance of the create { @link Shopware.grid.Panel }
             */
            me.eventAlias + '-after-create-grid-panel',

            /**
             * Event fired after the component was initialed. The event is fired before the me.callParent(arguments)
             * function called in the initComponent function.
             *
             * @param { Shopware.window.Listing } window - Instance of this component.
             */
            me.eventAlias + '-after-init-component'
        );
    },

    /**
     * Creates the listing store for the grid panel.
     *
     * @returns { Shopware.store.Listing }
     */
    createListingStore: function() {
        var me = this;

        return Ext.create(this.getConfig('listingStore'));
    },

    /**
     * Creates all required elements for this component.
     *
     * @returns { Array }
     */
    createItems: function () {
        var me = this, items = [];

        me.fireEvent(me.eventAlias + '-before-create-items', me, items);

        items.push(me.createGridPanel());

        me.fireEvent(me.eventAlias + '-after-create-items', me, items);

        //iterate all extensions and add them to the items array.
        Ext.each(me.getConfig('extensions'), function(extension) {
            //extension isn't defined? Continue with next extension
            if (!extension) return true;

            //support for simple extension definition over strings
            if (Ext.isString(extension)) extension = { xtype: extension };

            //set ref
            extension.listingWindow = me;
            items.push(extension);
        });

        me.fireEvent(me.eventAlias + '-after-extensions-loaded', me, items);

        return items;
    },

    /**
     * Creates the grid panel for the listing window.
     * The grid panel requires the listing store which will be set as grid store.
     *
     * @returns { Shopware.grid.Panel }
     */
    createGridPanel: function () {
        var me = this;

        me.listingStore.load();

        me.gridPanel = Ext.create(me.getConfig('listingGrid'), {
            store: me.listingStore,
            flex: 1,
            subApp: me.subApp
        });

        me.fireEvent(me.eventAlias + '-after-create-grid-panel', me, me.gridPanel);

        return me.gridPanel;
    }

});
//{/block}
