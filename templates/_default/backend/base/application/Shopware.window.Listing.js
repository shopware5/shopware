
//{block name="backend/component/window/listing"}
Ext.define('Shopware.window.Listing', {
    extend: 'Enlight.app.Window',

    layout: {
        type: 'hbox',
        align: 'stretch'
    },

    width: 990,

    height: '90%',

    alias : 'widget.shopware-window-listing',

    statics: {
        displayConfig: {
            listingGrid:   'Shopware.grid.Listing',
            listingStore:  ''
        },

        /**
         * Static function to merge the different configuration values
         * which passed in the class constructor.
         * @param userOpts Object
         * @param displayConfig Object
         * @returns Object
         */
        getDisplayConfig: function(userOpts, displayConfig) {
            var config;

            if (userOpts && userOpts.displayConfig) {
                config = Ext.apply({ }, userOpts.displayConfig);
            }

            config = Ext.apply({ }, config, displayConfig);
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
        setDisplayConfig: function(prop, val) {
            var me = this;

            if(!me.displayConfig.hasOwnProperty(prop)) {
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
    constructor: function(opts) {
        var me = this;

        me._opts = me.statics().getDisplayConfig(opts, this.displayConfig);
        me.callParent(arguments);
    },


    /**
     * Helper function to get config access.
     * @param prop string
     * @returns mixed
     * @constructor
     */
    Config: function(prop) {
        var me = this;
        return me._opts[prop];
    },

    initComponent: function() {
        var me = this;

        me.items = me.createItems();
        me.callParent(arguments);
    },

    createItems: function() {
        var me = this, items = [];

        items.push(me.createGridPanel());
        return items;
    },

    createGridPanel: function() {
        var me = this;

        me.listingStore = Ext.create(me.Config('listingStore')).load();
        me.gridPanel = Ext.create(me.Config('listingGrid'), {
            store: me.listingStore,
            flex: 1
        });
        return me.gridPanel;
    }
});
//{/block}
