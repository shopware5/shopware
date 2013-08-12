//{block name="backend/component/store/listing"}
Ext.define('Shopware.store.Listing', {
    extend: 'Ext.data.Store',

    autoLoad: false,

    batch: true,

    remoteSort: true,

    remoteFilter: true,

    pageSize: 20,

    /**
     * Get the reference to the class from which this object was instantiated.
     * Note that unlike self, this.statics() is scope-independent and it always
     * returns the class from which it was called, regardless of what this points to during run-time
     * @type { Object }
     */
    shopware: {

        displayConfig: {
            controller: undefined,

            /**
             * Model proxy which defines
             * the urls for the CRUD actions.
             */
            proxy: {
                type: 'ajax',
                api: {
                    read: '{url action="list"}'
                },
                reader: {
                    type: 'json',
                    root: 'data',
                    totalProperty: 'total'
                }
            }
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
     * Helper function to get config access.
     * @param prop string
     * @returns mixed
     * @constructor
     */
    getConfig: function (prop) {
        var me = this;
        return me._opts[prop];
    },

    /**
     * Class constructor.
     * Used to convert the proxy api urls.
     *
     * @param config
     */
    constructor: function (config) {
        var me = this;
        me._opts = me.shopware.getDisplayConfig(config, this.displayConfig);
        me.convertProxyApi();
        me.callParent(arguments);
    },

    /**
     * Helper function which removes the base controller
     * path of the store api urls.
     * The base controller will be remove with the
     * configured controller name.
     */
    convertProxyApi: function () {
        var me = this, value;

        if (!me.getConfig('controller')) {
            return;
        }

        me.setProxy(me.getConfig('proxy'));

        Object.keys(me.proxy.api).forEach(function (key) {
            value = me.proxy.api[key] + '';
            value = value.replace(
                '/backend/base/', '/backend/' + me.getConfig('controller').toLowerCase() + '/'
            );
            me.proxy.api[key] = value;
        });
    }
});
//{/block}
