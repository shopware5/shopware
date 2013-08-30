
//{block name="backend/application/data/model"}
Ext.define('Shopware.app.SubApplication', {

    /**
     * The parent class that this class extends
     * @type { String }
     */
    extend: 'Enlight.app.SubApplication',

    loadPath: '{url action=load}',

    bulkLoad: true,


    /**
     * Get the reference to the class from which this object was instantiated.
     * Note that unlike self, this.statics() is scope-independent and it always
     * returns the class from which it was called, regardless of what this points to during run-time
     *
     * @type { Object }
     */
    statics: {

        /**
         * The displayConfig contains the default shopware configuration for
         * this component.
         * To set the shopware configuration, you can set the displayConfig directly
         * as property of the component:
         *
         * @example
         *      Ext.define('Shopware.apps.Product.model.Product', {
         *          extend: 'Shopware.data.Model',
         *          displayConfig: {
         *              listing: 'Shopware.apps.Product.view.list.Product',
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

            if (userOpts && userOpts.displayConfig) {
                config = Ext.apply({ }, config, userOpts.displayConfig);
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
     * Class constructor.
     * Used to convert the proxy api urls.
     *
     * @param config
     */
    constructor: function (config) {
        var me = this;

        me._opts = me.statics().getDisplayConfig(config, this.displayConfig);
        me.convertProxyApi();
        me.callParent(arguments);
    },

    /**
     * Helper function to get config access.
     * @param prop string
     * @returns mixed
     * @constructor
     */
    getConfig: function (prop) {
        var me = this;
        return me._opts[prop];
    },

});
//{/block}

