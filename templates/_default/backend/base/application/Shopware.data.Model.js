
//{block name="backend/component/data/model"}
Ext.define('Shopware.data.Model', {

    /**
     * The parent class that this class extends
     * @type { String }
     */
    extend: 'Ext.data.Model',

    /**
     * Model proxy which defines
     * the urls for the CRUD actions.
     */
    proxy: {
        type: 'ajax',
        api: {
            detail:  '{url controller="base" action="detail"}',
            create:  '{url controller="base" action="create"}',
            update:  '{url controller="base" action="update"}',
            destroy: '{url controller="base" action="delete"}'
        },
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    },


    /**
     * Get the reference to the class from which this object was instantiated.
     * Note that unlike self, this.statics() is scope-independent and it always
     * returns the class from which it was called, regardless of what this points to during run-time
     * @type { Object }
     */
    statics: {

        displayConfig: {
            controller: undefined,

            listing: 'Shopware.grid.Panel',         // oneToMany & own listing view
            detail:  'Shopware.model.Container',    // oneToOne & own detail view
            related: 'Shopware.grid.Association',   // manyToMany
            field:   'Shopware.form.field.Search'   // manyToOne (Combo box to search)
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

    /**
     * Helper function which removes the base controller
     * path of the store api urls.
     * The base controller will be remove with the
     * configured controller name.
     */
    convertProxyApi: function () {
        var me = this, value;

        if (!me.getConfig('controller')) {
            me.proxy = null;
            return;
        }
        Object.keys(me.proxy.api).forEach(function (key) {
            value = me.proxy.api[key] + '';
            value = value.replace(
                '/backend/base/', '/backend/' + me.getConfig('controller').toLowerCase() + '/'
            );
            me.proxy.api[key] = value;
        });
    },


    /**
     * Helper function to load the model detail data.
     * This function creates a temp store with the
     * model proxy to send an ajax request on the controller
     * detailAction.
     * This action is used to load the model detail data.
     *
     * @param options object
     * @returns Shopware.data.Model
     */
    reload: function (options) {
        var me = this, proxy = me.proxy;

        if (!Ext.isString(proxy.api.detail)) {
            if (options && Ext.isFunction(options.callback)) {
                options.callback(me);
            } else {
                return this;
            }
        }
        proxy.api.read = proxy.api.detail;

        var store = Ext.create('Ext.data.Store', {
            model: me.__proto__.$className,
            proxy: me.proxy
        });

        store.getProxy().extraParams.id = me.get('id');

        try {
            store.load({
                callback: function (records, operation) {
                    var record = records[0];
                    if (options && Ext.isFunction(options.callback)) {
                        options.callback(record, operation);
                    }
                }
            });
        } catch (e) {
            return e;
        }
    }
});
//{/block}

