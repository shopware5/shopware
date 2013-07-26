
//{block name="backend/component/store/listing"}
Ext.define('Shopware.store.Listing', {
    extend:'Ext.data.Store',

    autoLoad:false,

    batch: true,

    remoteSort: true,

    remoteFilter : true,

    pageSize: 20,

    /**
     * Model proxy which defines
     * the urls for the CRUD actions.
     */
    proxy:{
        type:'ajax',
        api: {
            read:    '{url action="list"}'
        },
        reader:{
            type:'json',
            root:'data',
            totalProperty:'total'
        }
    },

    /**
     * Class constructor.
     * Used to convert the proxy api urls.
     *
     * @param config
     */
    constructor: function(config) {
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
    convertProxyApi: function() {
        var me = this, value;

        Object.keys(me.proxy.api).forEach(function(key) {
            value = me.proxy.api[key] + '';
            value = value.replace(
                '/base/', '/' + me.controller.toLowerCase()  + '/'
            );
            me.proxy.api[key] = value;
        });
    }

});
//{/block}
