//{block name="backend/component/data/model"}
Ext.define('Shopware.data.Model', {

    extend: 'Ext.data.Model',

    /**
     * Model proxy which defines
     * the urls for the CRUD actions.
     */
    proxy: {
        type: 'ajax',
        api: {
            detail: '{url action="detail"}',
            create: '{url action="create"}',
            update: '{url action="update"}',
            destroy: '{url action="delete"}'
        },
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
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

        Object.keys(me.proxy.api).forEach(function (key) {
            value = me.proxy.api[key] + '';
            value = value.replace(
                '/backend/base/', '/backend/' + me.controller.toLowerCase() + '/'
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

